import os
import csv
import pandas as pd
import logging
from datetime import datetime
from collections import Counter
import sys
import json
import hashlib

# ----------------------------------------
# Configuration
# ----------------------------------------

# Set to True to generate log files, False to disable log file generation
ENABLE_LOG_FILE = False

# Set to True to force regeneration of all matrices, bypassing change detection
FORCE_REGENERATION = True

# ----------------------------------------
# Logging configuration
# ----------------------------------------

def setup_logging():
    """Setup logging configuration with optional file output and console output."""
    script_dir = os.path.dirname(os.path.abspath(__file__))
    log_path = None
    
    # Clear any existing handlers
    for handler in logging.root.handlers[:]:
        logging.root.removeHandler(handler)
    
    handlers = []
    
    # Conditionally add file handler
    if ENABLE_LOG_FILE:
        # Create log filename with timestamp in the script's directory
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        log_filename = f"generate_pricematrices_log_{timestamp}.log"
        log_path = os.path.join(script_dir, log_filename)
        handlers.append(logging.FileHandler(log_path, encoding='utf-8'))
    
    # Configure logging
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s - %(levelname)s - %(message)s',
        handlers=handlers,
        force=True
    )
    
    # Console output with print statements
    print(f"============================================================")
    now = datetime.now()
    current_time = now.strftime("%H:%M:%S")
    print(f"✓ Preismatrix generierung gestartet um {current_time}")
    
    if ENABLE_LOG_FILE:
        print(f"✓ Log-Datei aktiviert: {log_filename}")
    else:
        print("✓ Log-Datei deaktiviert")
    
    return log_path

# Global logging variables
logger = logging.getLogger(__name__)
processing_stats = {
    'total_configs': 0,
    'successful_configs': 0,
    'failed_configs': 0,
    'warnings': 0,
    'errors': 0,
    'duplicates_found': 0,
    'missing_data_points': 0,
    'config_details': []
}

# ----------------------------------------
# Excel configuration
# ----------------------------------------

script_dir = os.path.dirname(os.path.abspath(__file__))
CONFIG_XLSX_PATH = os.path.join(script_dir, 'pricematrices.xlsx')

# ----------------------------------------
# Default global values (absolute fallback - only used when Excel is not available)
# ----------------------------------------
DEFAULT_GLOBALS = {
    "global_csv_width_start": 400,
    "global_csv_height_start": 400,
    "global_csv_width_end": 2500,
    "global_csv_height_end": 2500,
    "global_csv_width_step": 50,
    "global_csv_height_step": 50,
    "global_input_width_start": 400,
    "global_input_height_start": 400,
    "global_input_width_end": 2500,
    "global_input_height_end": 2500,
    "global_input_width_step": 100,
    "global_input_height_step": 100,
    "global_price_is_netto": False,
    "global_vat": 19.0,
    "global_s21_mark": 0.0,
    "global_bsd_margin": 25.0,
    "global_shipping_threshold_width": None,
    "global_shipping_threshold_height": None,
    "global_shipping_surcharge": 0.0,
    "global_template_key": "",
    "global_template_order": 31,
    "global_template_group": "masse",
    "global_template_label": "Aufpreis Breite und Höhe",
}

# ----------------------------------------
# Default column mapping (can be overridden by Excel)
# ----------------------------------------
DEFAULT_COLUMN_MAPPING = {
    "input_file": "input_file",
    "output_file": "output_file", 
    "price_is_netto": "price_is_netto",
    "csv_width_start": "csv_width_start",
    "csv_height_start": "csv_height_start",
    "csv_width_end": "csv_width_end",
    "csv_height_end": "csv_height_end",
    "csv_width_step": "csv_width_step",
    "csv_height_step": "csv_height_step",
    "input_width_start": "input_width_start",
    "input_height_start": "input_height_start",
    "input_width_end": "input_width_end",
    "input_height_end": "input_height_end",
    "input_width_step": "input_width_step",
    "input_height_step": "input_height_step",
    "vat": "vat",
    "s21_mark": "s21_mark",
    "bsd_margin": "bsd_margin",
    "shipping_threshold_width": "shipping_threshold_width",
    "shipping_threshold_height": "shipping_threshold_height",
    "shipping_surcharge": "shipping_surcharge",
    "template_key": "template_key",
    "template_order": "template_order",
    "template_group": "template_group",
    "template_label": "template_label",
    "transponieren": "transponieren",
}

# ----------------------------------------
# Control sizes for comments
# ----------------------------------------
control_sizes = [
    (200, 200),  
    (400, 400), 
    (800, 600), 
    (1200, 800),
    (2500, 1500)
]

# ----------------------------------------
# Directory structure
# ----------------------------------------
output_subdir = "pricematrices"
base_output_dir = os.path.join(script_dir, output_subdir)
php_output_dir = os.path.join(base_output_dir, "php")
csv_dir = os.path.join(base_output_dir, "csv")

# ----------------------------------------
# Excel reading functions
# ----------------------------------------

def load_defaults_from_excel():
    """
    Load global defaults from Excel defaults sheet.
    
    Priority order:
    1. Excel 'defaults' sheet (primary source)
    2. Python DEFAULT_GLOBALS (absolute fallback)
    
    The Excel sheet should contain all variables with 'global_' prefix.
    """
    # Start with empty dict to ensure Excel takes full priority
    defaults = {}
    
    if not os.path.exists(CONFIG_XLSX_PATH):
        logger.warning(f"Excel config file not found: {CONFIG_XLSX_PATH}")
        logger.warning("Using Python fallback defaults - this should only happen during development!")
        return DEFAULT_GLOBALS.copy()
    
    try:
        df_defaults = pd.read_excel(CONFIG_XLSX_PATH, sheet_name='defaults', dtype=str).fillna('')
        logger.info("Loading defaults from Excel 'defaults' sheet")
        
        # Support both orientations: key-value pairs in columns or rows
        if 'key' in df_defaults.columns and 'value' in df_defaults.columns:
            # Column format: key | value
            logger.info("Using column format (key|value) for defaults")
            for _, row in df_defaults.iterrows():
                key = str(row['key']).strip()
                value = str(row['value']).strip()
                if key and value and value.lower() != 'nan':
                    defaults[key] = parse_value(value)
                    logger.debug(f"Loaded default: {key} = {defaults[key]}")
        else:
            # Try transposed format - first row = keys, second row = values
            logger.info("Using transposed format for defaults")
            if len(df_defaults) >= 2:
                keys = df_defaults.iloc[0].values
                values = df_defaults.iloc[1].values
                for key, value in zip(keys, values):
                    key_str = str(key).strip()
                    value_str = str(value).strip()
                    if key_str and value_str and key_str != 'nan' and value_str.lower() != 'nan':
                        defaults[key_str] = parse_value(value_str)
                        logger.debug(f"Loaded default: {key_str} = {defaults[key_str]}")
        
        # Validate that we have essential defaults from Excel
        essential_keys = [
            'global_csv_width_start', 'global_csv_height_start', 'global_csv_width_end', 'global_csv_height_end',
            'global_csv_width_step', 'global_csv_height_step', 'global_input_width_start', 'global_input_height_start',
            'global_input_width_end', 'global_input_height_end', 'global_input_width_step', 'global_input_height_step',
            'global_price_is_netto', 'global_vat', 'global_s21_mark', 'global_bsd_margin'
        ]
        
        missing_keys = [key for key in essential_keys if key not in defaults]
        if missing_keys:
            logger.warning(f"Missing essential defaults in Excel: {missing_keys}")
            logger.warning("Adding missing defaults from Python fallback")
            
            # Add only missing keys from Python fallback
            for key in missing_keys:
                if key in DEFAULT_GLOBALS:
                    defaults[key] = DEFAULT_GLOBALS[key]
                    logger.warning(f"Added fallback default: {key} = {defaults[key]}")
        
        logger.info(f"Successfully loaded {len(defaults)} defaults from Excel")
        
        # Log summary of loaded defaults
        logger.info("=== LOADED DEFAULTS SUMMARY ===")
        for key, value in sorted(defaults.items()):
            logger.info(f"  {key}: {value} ({type(value).__name__})")
        
        return defaults
    
    except Exception as e:
        logger.error(f"Error reading defaults from Excel: {e}")
        logger.error("Falling back to Python defaults")
        return DEFAULT_GLOBALS.copy()

def parse_value(value_str):
    """Parse string value to appropriate type."""
    value_str = str(value_str).strip()
    
    # Boolean
    if value_str.upper() in ['TRUE', 'FALSE']:
        return value_str.upper() == 'TRUE'
    
    # Try integer
    try:
        if '.' not in value_str:
            return int(value_str)
    except ValueError:
        pass
    
    # Try float
    try:
        return float(value_str.replace(',', '.'))
    except ValueError:
        pass
    
    # Return as string
    return value_str

def detect_excel_orientation(df):
    """Detect if Excel data is transposed by checking for input_file in first column vs first row."""
    # Check if 'input_file' appears in first column (normal orientation)
    if len(df) > 0 and 'input_file' in df.iloc[:, 0].astype(str).str.lower().values:
        return 'transposed'
    
    # Check if 'input_file' appears in column headers (normal orientation)
    if 'input_file' in [str(col).lower() for col in df.columns]:
        return 'normal'
    
    # Default to normal if unclear
    return 'normal'

def load_inputs_from_excel(defaults):
    """
    Load input configurations from Excel with flexible column ordering and transposition support.
    
    Priority order for each configuration parameter:
    1. Input row value (highest priority)
    2. Global default from Excel/Python (fallback)
    
    This ensures input-specific overrides work while maintaining global defaults.
    """
    if not os.path.exists(CONFIG_XLSX_PATH):
        logger.error(f"Excel config file not found: {CONFIG_XLSX_PATH}")
        return []
    
    try:
        df_inputs = pd.read_excel(CONFIG_XLSX_PATH, sheet_name='inputs', dtype=str).fillna('')
        logger.info("Loading inputs from Excel 'inputs' sheet")
        
        # Detect orientation
        orientation = detect_excel_orientation(df_inputs)
        logger.info(f"Detected Excel orientation: {orientation}")
        
        if orientation == 'transposed':
            # Transpose the dataframe
            df_inputs = df_inputs.set_index(df_inputs.iloc[:, 0]).T
            df_inputs.columns = df_inputs.iloc[0]
            df_inputs = df_inputs.drop(df_inputs.index[0])
        
        inputs = []
        
        for row_idx, row in df_inputs.iterrows():
            config = {}
            
            # Map Excel columns to config keys
            for excel_col in df_inputs.columns:
                excel_col_clean = str(excel_col).strip().lower()
                value = str(row[excel_col]).strip()
                
                if not value or value.lower() == 'nan':
                    continue
                
                # Find matching config key
                config_key = None
                for key, mapped_col in DEFAULT_COLUMN_MAPPING.items():
                    if excel_col_clean == mapped_col.lower() or excel_col_clean == key.lower():
                        config_key = key
                        break
                
                if config_key:
                    config[config_key] = parse_value(value)
                    logger.debug(f"Row {row_idx}: {config_key} = {config[config_key]} (from input)")
                else:
                    # Store unknown columns as-is (for future flexibility)
                    config[excel_col_clean] = parse_value(value)
                    logger.debug(f"Row {row_idx}: {excel_col_clean} = {config[excel_col_clean]} (unknown column)")
            
            # Only add if we have at least input_file and output_file
            if 'input_file' in config and 'output_file' in config:
                # Add file extensions if missing
                if not config['input_file'].lower().endswith('.csv'):
                    config['input_file'] += '.csv'
                if not config['output_file'].lower().endswith('.php'):
                    config['output_file'] += '.php'
                
                # Apply defaults for missing values - IMPROVED PRIORITY LOGIC
                for key, default_value in defaults.items():
                    if key.startswith('global_'):
                        local_key = key[7:]  # Remove 'global_' prefix
                        
                        # Only apply default if the local key is not already set
                        if local_key not in config:
                            config[local_key] = default_value
                            logger.debug(f"Row {row_idx}: {local_key} = {default_value} (from default {key})")
                        else:
                            logger.debug(f"Row {row_idx}: {local_key} = {config[local_key]} (input override)")
                
                # Validate essential parameters
                essential_params = [
                    'csv_width_start', 'csv_height_start', 'csv_width_end', 'csv_height_end',
                    'csv_width_step', 'csv_height_step', 'input_width_start', 'input_height_start',
                    'input_width_end', 'input_height_end', 'input_width_step', 'input_height_step'
                ]
                
                missing_params = [param for param in essential_params if param not in config]
                if missing_params:
                    logger.error(f"Row {row_idx}: Missing essential parameters: {missing_params}")
                    continue  # Skip this configuration
                
                # NEU: transponieren als bool speichern (TRUE/FALSE)
                transponieren_val = config.get('transponieren', False)
                if isinstance(transponieren_val, str):
                    config['transponieren'] = transponieren_val.strip().upper() == 'TRUE'
                else:
                    config['transponieren'] = bool(transponieren_val)
                
                logger.info(f"Row {row_idx}: Configuration loaded successfully - {config['input_file']} -> {config['output_file']}")
                inputs.append(config)
            # else:
                # logger.warning(f"Row {row_idx}: Skipping - missing input_file or output_file")

        logger.info(f"Successfully loaded {len(inputs)} configurations from Excel")
        return inputs
    
    except Exception as e:
        logger.error(f"Error reading inputs from Excel: {e}")
        return []

# ----------------------------------------
# Validation and Logging functions
# ----------------------------------------

def validate_excel_data(inputs):
    """Validate Excel data for duplicates and missing required fields."""
    # logger.info("=== VALIDIERUNG ===")
    
    validation_issues = []
    
    # Check for duplicates based on input_file + output_file combination
    file_combinations = []
    for i, config in enumerate(inputs):
        combination = (config.get('input_file', ''), config.get('output_file', ''))
        file_combinations.append((i + 1, combination))
    
    # Find duplicates
    combination_counts = Counter([combo[1] for combo in file_combinations])
    duplicates = {combo: count for combo, count in combination_counts.items() if count > 1}
    
    if duplicates:
        processing_stats['duplicates_found'] = len(duplicates)
        logger.warning(f"DUPLIKATE GEFUNDEN: {len(duplicates)} doppelte Konfigurationen")
        for (input_file, output_file), count in duplicates.items():
            duplicate_rows = [str(row) for row, combo in file_combinations if combo == (input_file, output_file)]
            logger.warning(f"  - {input_file} -> {output_file} (Zeilen: {', '.join(duplicate_rows)}, {count}x gefunden)")
            validation_issues.append(f"Duplikat: {input_file} -> {output_file} in Zeilen {', '.join(duplicate_rows)}")
    else:
        logger.info("Keine Duplikate gefunden")
    
    # Check for missing required fields
    required_fields = ['input_file', 'output_file']
    for i, config in enumerate(inputs):
        missing_fields = [field for field in required_fields if not config.get(field)]
        if missing_fields:
            logger.error(f"FEHLENDE PFLICHTFELDER in Zeile {i + 1}: {', '.join(missing_fields)}")
            validation_issues.append(f"Zeile {i + 1}: Fehlende Felder: {', '.join(missing_fields)}")
            processing_stats['errors'] += 1
    
    # Check for potentially problematic values
    for i, config in enumerate(inputs):
        row_num = i + 1
        config_issues = []
        
        # Check numeric fields
        numeric_fields = ['csv_width_start', 'csv_height_start', 'csv_width_end', 'csv_height_end',
                         'csv_width_step', 'csv_height_step', 'input_width_start', 'input_height_start',
                         'input_width_end', 'input_height_end', 'input_width_step', 'input_height_step',
                         'vat', 's21_mark', 'bsd_margin']
        
        for field in numeric_fields:
            value = config.get(field)
            if value is not None and not isinstance(value, (int, float)):
                config_issues.append(f"{field}: '{value}' ist nicht numerisch")
        
        # Check step sizes
        if config.get('csv_width_step', 1) <= 0:
            config_issues.append("csv_width_step muss > 0 sein")
        if config.get('csv_height_step', 1) <= 0:
            config_issues.append("csv_height_step muss > 0 sein")
        
        if config_issues:
            logger.warning(f"PROBLEMATISCHE WERTE in Zeile {row_num}: {'; '.join(config_issues)}")
            validation_issues.extend([f"Zeile {row_num}: {issue}" for issue in config_issues])
            processing_stats['warnings'] += 1
    
    # logger.info(f"Validierung abgeschlossen: {len(validation_issues)} Probleme gefunden")
    return validation_issues

def log_config_details(config, row_num):
    """Log detailed configuration for a single row."""
    details = {
        'row': row_num,
        'input_file': config.get('input_file'),
        'output_file': config.get('output_file'),
        'dimensions': f"{config.get('input_width_start')}x{config.get('input_height_start')} bis {config.get('input_width_end')}x{config.get('input_height_end')}",
        'status': 'pending',
        'warnings': [],
        'notes': []
    }
    processing_stats['config_details'].append(details)
    return details

def update_config_status(details, status, message=None):
    """Update the status of a configuration."""
    details['status'] = status
    if message:
        details['message'] = message

# ----------------------------------------
# New: Change tracking and metadata
# ----------------------------------------

def calculate_config_hash(config):
    """Calculate hash of configuration parameters to detect changes."""
    # Only include parameters that affect the price matrix generation
    relevant_params = {
        'csv_width_start': config.get('csv_width_start'),
        'csv_height_start': config.get('csv_height_start'),
        'csv_width_end': config.get('csv_width_end'),
        'csv_height_end': config.get('csv_height_end'),
        'csv_width_step': config.get('csv_width_step'),
        'csv_height_step': config.get('csv_height_step'),
        'input_width_start': config.get('input_width_start'),
        'input_height_start': config.get('input_height_start'),
        'input_width_end': config.get('input_width_end'),
        'input_height_end': config.get('input_height_end'),
        'input_width_step': config.get('input_width_step'),
        'input_height_step': config.get('input_height_step'),
        'price_is_netto': config.get('price_is_netto'),
        'vat': config.get('vat'),
        's21_mark': config.get('s21_mark'),
        'bsd_margin': config.get('bsd_margin'),
        'shipping_threshold_width': config.get('shipping_threshold_width'),
        'shipping_threshold_height': config.get('shipping_threshold_height'),
        'shipping_surcharge': config.get('shipping_surcharge'),
        'template_key': config.get('template_key'),
        'template_order': config.get('template_order'),
        'template_group': config.get('template_group'),
        'template_label': config.get('template_label'),
    }
    
    # Create hash from sorted parameters
    param_string = json.dumps(relevant_params, sort_keys=True)
    return hashlib.md5(param_string.encode()).hexdigest()

def calculate_defaults_hash(defaults):
    """Calculate hash of default parameters to detect changes in global defaults."""
    # Include all global defaults that affect price matrix generation
    relevant_defaults = {
        'global_csv_width_start': defaults.get('global_csv_width_start'),
        'global_csv_height_start': defaults.get('global_csv_height_start'),
        'global_csv_width_end': defaults.get('global_csv_width_end'),
        'global_csv_height_end': defaults.get('global_csv_height_end'),
        'global_csv_width_step': defaults.get('global_csv_width_step'),
        'global_csv_height_step': defaults.get('global_csv_height_step'),
        'global_input_width_start': defaults.get('global_input_width_start'),
        'global_input_height_start': defaults.get('global_input_height_start'),
        'global_input_width_end': defaults.get('global_input_width_end'),
        'global_input_height_end': defaults.get('global_input_height_end'),
        'global_input_width_step': defaults.get('global_input_width_step'),
        'global_input_height_step': defaults.get('global_input_height_step'),
        'global_price_is_netto': defaults.get('global_price_is_netto'),
        'global_vat': defaults.get('global_vat'),
        'global_s21_mark': defaults.get('global_s21_mark'),
        'global_bsd_margin': defaults.get('global_bsd_margin'),
        'global_shipping_threshold_width': defaults.get('global_shipping_threshold_width'),
        'global_shipping_threshold_height': defaults.get('global_shipping_threshold_height'),
        'global_shipping_surcharge': defaults.get('global_shipping_surcharge'),
        'global_template_key': defaults.get('global_template_key'),
        'global_template_order': defaults.get('global_template_order'),
        'global_template_group': defaults.get('global_template_group'),
        'global_template_label': defaults.get('global_template_label'),
    }
    
    # Create hash from sorted parameters
    param_string = json.dumps(relevant_defaults, sort_keys=True)
    return hashlib.md5(param_string.encode()).hexdigest()

def get_csv_file_hash(csv_path):
    """Get hash of CSV file to detect changes in source data."""
    if not os.path.exists(csv_path):
        return None
    
    try:
        with open(csv_path, 'rb') as f:
            return hashlib.md5(f.read()).hexdigest()
    except Exception:
        return None

def load_previous_metadata():
    """Load metadata from previous run."""
    metadata_path = os.path.join(base_output_dir, 'generation_metadata.json')
    
    if not os.path.exists(metadata_path):
        return {}
    
    try:
        with open(metadata_path, 'r', encoding='utf-8') as f:
            return json.load(f)
    except Exception as e:
        logger.warning(f"Fehler beim Laden der Metadaten: {e}")
        return {}

def save_metadata(metadata):
    """Save metadata for future runs."""
    metadata_path = os.path.join(base_output_dir, 'generation_metadata.json')
    
    try:
        with open(metadata_path, 'w', encoding='utf-8') as f:
            json.dump(metadata, f, indent=2, ensure_ascii=False)
    except Exception as e:
        logger.error(f"Fehler beim Speichern der Metadaten: {e}")

def check_for_changes(config, previous_metadata, defaults_hash):
    """Check if configuration, CSV file, or defaults have changed."""
    input_file = config.get('input_file')
    output_file = config.get('output_file')
    csv_path = os.path.join(csv_dir, input_file)
    
    # Force regeneration if configured
    if FORCE_REGENERATION:
        config_hash = calculate_config_hash(config)
        csv_hash = get_csv_file_hash(csv_path)
        return True, "Neugenerierung forciert", config_hash, csv_hash
    
    # Calculate current hashes
    config_hash = calculate_config_hash(config)
    csv_hash = get_csv_file_hash(csv_path)
    
    # Check if this output file was generated before
    if output_file not in previous_metadata:
        return True, "Neue Konfiguration", config_hash, csv_hash
    
    prev_data = previous_metadata[output_file]
    
    # Check for changes
    changes = []
    
    if prev_data.get('config_hash') != config_hash:
        changes.append("Konfiguration geändert")
    
    if prev_data.get('csv_hash') != csv_hash:
        changes.append("CSV-Datei geändert")
    
    # NEW: Check for changes in global defaults
    if prev_data.get('defaults_hash') != defaults_hash:
        changes.append("Globale Defaults geändert")
    
    if not os.path.exists(os.path.join(php_output_dir, output_file)):
        changes.append("PHP-Datei fehlt")
    
    if changes:
        return True, "; ".join(changes), config_hash, csv_hash
    else:
        return False, "Keine Änderungen", config_hash, csv_hash

def generate_overview_report(current_metadata, defaults_hash):
    """Generate overview report of all matrices."""
    overview_path = os.path.join(base_output_dir, 'matrices_overview.json')
    
    # Enhanced overview with statistics
    overview = {
        'generation_time': datetime.now().isoformat(),
        'total_matrices': len(current_metadata),
        'defaults_hash': defaults_hash,  # NEW: Store current defaults hash in overview
        'statistics': {
            'total_configs': processing_stats['total_configs'],
            'successful_configs': processing_stats['successful_configs'],
            'failed_configs': processing_stats['failed_configs'],
            'skipped_configs': len([d for d in processing_stats['config_details'] if d['status'] == 'skipped']),
            'warnings': processing_stats['warnings'],
            'errors': processing_stats['errors']
        },
        'matrices': current_metadata
    }
    
    try:
        with open(overview_path, 'w', encoding='utf-8') as f:
            json.dump(overview, f, indent=2, ensure_ascii=False)
        
        logger.info(f"Übersichtsbericht erstellt: {overview_path}")
        print(f"✓ Übersichtsbericht erstellt: matrices_overview.json")
    except Exception as e:
        logger.error(f"Fehler beim Erstellen des Übersichtsberichts: {e}")

# ----------------------------------------
# Original processing functions (unchanged)
# ----------------------------------------

def read_csv(filepath):
    """
    Liest die CSV-Datei ein und liefert eine Liste von Zeilen (jede Zeile ist eine Liste von Strings).
    Das Semikolon wird als Trennzeichen verwendet.
    """
    with open(filepath, "r", encoding="utf-8") as f:
        reader = csv.reader(f, delimiter=";")
        return list(reader)

def compute_raw_csv_price(rows, width, height,
                          csv_width_start, csv_height_start,
                          csv_width_step, csv_height_step):
    """
    Liefert nur den float-Wert aus der CSV (reiner Basiswert),
    ohne weitere Zuschläge oder Rabatte.
    Gibt None zurück, wenn (width, height) nicht exakt in der CSV vorkommt.
    """
    if (width - csv_width_start) % csv_width_step != 0:
        processing_stats['missing_data_points'] += 1
        return None
    if (height - csv_height_start) % csv_height_step != 0:
        processing_stats['missing_data_points'] += 1
        return None

    col_index = (width - csv_width_start) // csv_width_step
    row_index = (height - csv_height_start) // csv_height_step

    if row_index < 0 or row_index >= len(rows):
        processing_stats['missing_data_points'] += 1
        return None
    row_data = rows[row_index]

    if col_index < 0 or col_index >= len(row_data):
        processing_stats['missing_data_points'] += 1
        return None

    cell_value_str = row_data[col_index].strip().replace(",", ".")
    try:
        return float(cell_value_str)
    except ValueError:
        processing_stats['missing_data_points'] += 1
        return None

def build_control_comments(rows,
                           csv_width_start, csv_height_start, csv_width_step, csv_height_step,
                           input_width_start, input_height_start,
                           price_is_netto, vat,
                           shipping_threshold_width, shipping_threshold_height, shipping_surcharge,
                           s21_mark, bsd_margin,
                           generation_info=None):
    """
    Erzeugt detaillierte Kommentare für die Kontrollgrößen mit S21- und BSD-Preisen.
    """
    comments = []
    
    comments.append("// ---- Kontrollpreise ----")
    
    # 1. Basis-Preisberechnung für das Startmaß (ohne Versand)
    basis_csv_val = compute_raw_csv_price(rows,
                                          input_width_start, input_height_start,
                                          csv_width_start, csv_height_start,
                                          csv_width_step, csv_height_step)
    if basis_csv_val is None:
        comments.append(f"// ACHTUNG: Basismaß {input_width_start}x{input_height_start} nicht in CSV gefunden.")
        return comments

    basis_price_s21 = basis_csv_val
    if price_is_netto:
        basis_price_s21 *= (1 + vat / 100.0)
    basis_price_s21 += s21_mark
    
    basis_price_bsd_gross = basis_price_s21
    if bsd_margin != 0.0:
        basis_price_bsd_gross *= (1 + bsd_margin / 100.0)
    
    # NEU: Basispreis wird vom gerundeten Brutto-Preis des Startmaßes abgeleitet
    basis_price_bsd = round(basis_price_bsd_gross)

    comments.append("// S21 = (CSV-Preis + MwSt) + S21-Aufschlag + Versand")
    comments.append("// BSD = round(S21-Preis * BSD-Marge)")
    comments.append(f"// Basispreis (wird abgezogen): {basis_price_bsd} (gerundet von {basis_price_bsd_gross:.2f} für {input_width_start}x{input_height_start})")
    comments.append("// Endpreis = BSD - Basispreis")

    # 2. Für jede Kontrollgröße den Endpreis berechnen
    for (cw, ch) in control_sizes:
        comments.append("// ------------------------")
        raw_val = compute_raw_csv_price(rows,
                                        cw, ch,
                                        csv_width_start, csv_height_start,
                                        csv_width_step, csv_height_step)
        if raw_val is None:
            comments.append(f"// {cw}x{ch} => nicht in CSV gefunden")
            continue

        comments.append(f"// {cw}x{ch}:" )

        # S21-Preis berechnen
        price_s21 = raw_val
        if price_is_netto:
            price_s21 *= (1 + vat / 100.0)
        price_s21 += s21_mark
        
        # Versandzuschlag zum S21-Preis addieren
        has_shipping_surcharge = False
        if (shipping_threshold_width and shipping_threshold_height and shipping_surcharge and
                ((cw >= shipping_threshold_width and ch >= shipping_threshold_height) or
                 (cw >= shipping_threshold_height and ch >= shipping_threshold_width))):
            price_s21 += shipping_surcharge
            has_shipping_surcharge = True

        shipping_info = f" (+{shipping_surcharge} Versand)" if has_shipping_surcharge else ""
        comments.append(f"//   S21-Preis: {price_s21:.2f}{shipping_info}")

        # BSD-Preis (vor Abzug) berechnen
        price_bsd_gross = price_s21
        if bsd_margin != 0.0:
            price_bsd_gross *= (1 + bsd_margin / 100.0)
        
        # NEU: Erst runden, dann subtrahieren
        price_bsd_rounded = round(price_bsd_gross)
        comments.append(f"//   BSD-Preis: {price_bsd_rounded} (gerundet von {price_bsd_gross:.2f})")

        # BSD-Preis (final) berechnen
        price_bsd_final = price_bsd_rounded - basis_price_bsd

        # Beim Startmaß => Endpreis = 0 (sollte automatisch passieren)
        if (cw == input_width_start) and (ch == input_height_start):
            price_bsd_final = 0

        final_price_rounded = int(price_bsd_final)
        
        comments.append(f"//   Endpreis: {final_price_rounded}")

    comments.append("// ------------------------")
    return comments

def print_progress_bar(current, total, prefix='Progress', suffix='Complete', length=50):
    """Print a progress bar to terminal."""
    percent = ("{0:.1f}").format(100 * (current / float(total)))
    filled_length = int(length * current // total)
    bar = '█' * filled_length + '-' * (length - filled_length)
    
    # Truncate suffix to prevent overflow
    max_suffix_length = 50
    if len(suffix) > max_suffix_length:
        suffix = suffix[:max_suffix_length-3] + '...'
    
    # Build the line with fixed width
    line = f'{prefix} |{bar}| {percent}% {suffix}'
    
    # Clear the line completely and print new content
    print(f'\r{" " * 120}', end='', flush=True)  # Clear line first
    print(f'\r{line}', end='', flush=True)       # Then print new content
    
    # Add newline for final call
    if current >= total:
        print()

def process_file(conf, row_num, total_configs, previous_metadata, current_metadata, defaults_hash):
    """
    Liest eine CSV-Datei, bestimmt den Basispreis (Startmaß) und wendet
    die neue Reihenfolge auf alle Maße im gewünschten Bereich an.
    """
    details = log_config_details(conf, row_num)
    
    input_file = conf.get("input_file")
    output_file = conf.get("output_file")
    csv_path = os.path.join(csv_dir, input_file)
    
    # Check for changes - NOW INCLUDING DEFAULTS
    has_changes, change_reason, config_hash, csv_hash = check_for_changes(conf, previous_metadata, defaults_hash)
    
    if not has_changes:
        # Skip generation, but update metadata with current defaults hash
        current_metadata[output_file] = previous_metadata[output_file].copy()
        current_metadata[output_file]['last_checked'] = datetime.now().isoformat()
        current_metadata[output_file]['defaults_hash'] = defaults_hash  # Update defaults hash
        
        # Truncate filename for display
        display_name = output_file
        if len(display_name) > 25:
            display_name = display_name[:22] + '...'
        
        print_progress_bar(row_num, total_configs, 
                          f'Übersprungen ({row_num}/{total_configs})', 
                          f'{display_name} (unverändert)')
        processing_stats['successful_configs'] += 1
        update_config_status(details, 'skipped', 'Keine Änderungen erkannt')
        return
    
    # Update progress bar
    display_name = output_file
    if len(display_name) > 25:
        display_name = display_name[:22] + '...'
    
    # Truncate change reason
    display_reason = change_reason
    if len(display_reason) > 20:
        display_reason = display_reason[:17] + '...'
    
    print_progress_bar(row_num, total_configs, 
                      f'Verarbeitung ({row_num}/{total_configs})', 
                      f'{display_name} ({display_reason})')
    
    # Only log start of processing for potential errors
    has_errors = False

    if not os.path.exists(csv_path):
        error_msg = f"CSV-Datei nicht gefunden: {csv_path}"
        logger.error(f"Zeile {row_num}: {error_msg}")
        update_config_status(details, 'error', error_msg)
        processing_stats['failed_configs'] += 1
        processing_stats['errors'] += 1
        return

    try:
        rows = read_csv(csv_path)
        # NEU: CSV transponieren falls gewünscht
        if conf.get('transponieren', False):
            rows = list(map(list, zip(*rows)))
    except Exception as e:
        error_msg = f"Fehler beim Lesen der CSV: {str(e)}"
        logger.error(f"Zeile {row_num}: {error_msg}")
        update_config_status(details, 'error', error_msg)
        processing_stats['failed_configs'] += 1
        processing_stats['errors'] += 1
        return

    # Get parameters from config (Excel-loaded or defaults)
    csv_width_start = conf.get("csv_width_start")
    csv_height_start = conf.get("csv_height_start")
    csv_width_end = conf.get("csv_width_end")
    csv_height_end = conf.get("csv_height_end")
    csv_width_step = conf.get("csv_width_step")
    csv_height_step = conf.get("csv_height_step")

    input_width_start = conf.get("input_width_start")
    input_height_start = conf.get("input_height_start")
    input_width_end = conf.get("input_width_end")
    input_height_end = conf.get("input_height_end")
    input_width_step = conf.get("input_width_step")
    input_height_step = conf.get("input_height_step")

    price_is_netto = conf.get("price_is_netto")
    vat = conf.get("vat")
    s21_mark = conf.get("s21_mark")
    bsd_margin = conf.get("bsd_margin")

    shipping_surcharge = conf.get("shipping_surcharge", 0.0)
    shipping_threshold_width = conf.get("shipping_threshold_width", None)
    shipping_threshold_height = conf.get("shipping_threshold_height", None)

    # Template configuration
    template_config = {
        'template_key': conf.get("template_key", ""),
        'template_order': conf.get("template_order", 30),
        'template_group': conf.get("template_group", "masse"),
        'template_label': conf.get("template_label", "Aufpreis Breite und Höhe"),
        # NEW: Add input ranges to template config for PHP comment generation
        'input_width_start': input_width_start,
        'input_width_end': input_width_end,
        'input_height_start': input_height_start,
        'input_height_end': input_height_end,
        'csv_width_start': csv_width_start,
        'csv_width_end': csv_width_end,
        'csv_height_start': csv_height_start,
        'csv_height_end': csv_height_end,
    }

    # Track missing data points for this file
    initial_missing_count = processing_stats['missing_data_points']

    # 1) Basiswert berechnen für das Startmaß
    basis_csv_val = compute_raw_csv_price(rows,
                                          input_width_start, input_height_start,
                                          csv_width_start, csv_height_start,
                                          csv_width_step, csv_height_step)
    if basis_csv_val is None:
        error_msg = f"Basismaß {input_width_start}x{input_height_start} nicht in CSV gefunden!"
        logger.error(f"Zeile {row_num} ({input_file}): {error_msg}")
        update_config_status(details, 'error', error_msg)
        processing_stats['failed_configs'] += 1
        processing_stats['errors'] += 1
        return

    # Basis-Preisberechnung (Brutto-Preis für Startmaß)
    basis_price_gross = basis_csv_val
    
    # Schritt 2: falls Netto => MwSt draufrechnen
    if price_is_netto:
        basis_price_gross *= (1 + vat / 100.0)
    
    # Schritt 3: S21 Mark (kann positiv oder negativ sein)
    basis_price_gross += s21_mark
    
    # Schritt 4: BSD Margin anwenden
    if bsd_margin != 0.0:
        basis_price_gross *= (1 + bsd_margin / 100.0)

    # NEU: Der finale Basispreis ist der gerundete Brutto-Preis des Startmaßes
    basis_price_final = round(basis_price_gross)

    # 2) Alle Maße im gewünschten Bereich berechnen
    options = {}
    calculated_count = 0
    skipped_count = 0
    
    for w in range(input_width_start, input_width_end + 1, input_width_step):
        if w < csv_width_start or w > csv_width_end:
            continue
        if (w - csv_width_start) % csv_width_step != 0:
            continue

        for h in range(input_height_start, input_height_end + 1, input_height_step):
            if h < csv_height_start or h > csv_height_end:
                continue
            if (h - csv_height_start) % csv_height_step != 0:
                continue

            # CSV-Rohwert
            raw_val = compute_raw_csv_price(rows, w, h,
                                            csv_width_start, csv_height_start,
                                            csv_width_step, csv_height_step)
            if raw_val is None:
                skipped_count += 1
                continue

            calculated_count += 1

            # Neue Preisberechnung-Reihenfolge (vereinfacht):
            # 1) CSV-Wert (Preis-Matrix)
            price = raw_val
            
            # 2) falls Netto => MwSt draufrechnen (19%)
            if price_is_netto:
                price *= (1 + vat / 100.0)

            # 3) S21 Mark addieren (kann positiv oder negativ sein)
            price += s21_mark

            # 4) Versandzuschlag (NEUE POSITION)
            if (shipping_threshold_width and shipping_threshold_height and
                    shipping_surcharge and
                    ((w >= shipping_threshold_width and h >= shipping_threshold_height) or
                     (w >= shipping_threshold_height and h >= shipping_threshold_width))):
                price += shipping_surcharge

            # 5) BSD Margin anwenden
            if bsd_margin != 0.0:
                price *= (1 + bsd_margin / 100.0)

            # 6) Runden auf ganze Zahl (NEUE POSITION)
            price = round(price)

            # 7) Basispreis abziehen
            price -= basis_price_final

            # 8) Beim Startmaß => Endpreis = 0 (passiert jetzt automatisch)
            if w == input_width_start and h == input_height_start:
                price = 0

            # Speichern (ist bereits ein Integer durch die Subtraktion)
            key = f"{w}x{h}"
            label = f"{w}mm x {h}mm"
            options[key] = {"label": label, "price": int(price)}

    file_missing_count = processing_stats['missing_data_points'] - initial_missing_count
    
    # Add skipped count as a note, not a warning, to reduce noise in the summary.
    if skipped_count > 0:
        note_msg = f"Datenpunkte in CSV nicht gefunden (übersprungen): {skipped_count}"
        details['notes'].append(note_msg)

    # 3) Kontroll-Kommentare erzeugen
    generation_info = {
        'timestamp': datetime.now().strftime('%d.%m.%Y %H:%M:%S'),
        'status': 'Neu generiert' if 'Neue Konfiguration' in change_reason else 'Aktualisiert',
        'change_reason': change_reason
    }
    
    control_comments = build_control_comments(rows,
                                             csv_width_start, csv_height_start,
                                             csv_width_step, csv_height_step,
                                             input_width_start, input_height_start,
                                             price_is_netto, vat,
                                             shipping_threshold_width, shipping_threshold_height,
                                             shipping_surcharge,
                                             s21_mark, bsd_margin,
                                             generation_info)

    # 4) After successful processing, update metadata - NOW INCLUDING DEFAULTS HASH
    current_metadata[output_file] = {
        'input_file': input_file,
        'output_file': output_file,
        'config_hash': config_hash,
        'csv_hash': csv_hash,
        'defaults_hash': defaults_hash,  # NEW: Store defaults hash
        'last_generated': datetime.now().isoformat(),
        'last_checked': datetime.now().isoformat(),
        'change_reason': change_reason,
        'dimensions': f"{input_width_start}x{input_height_start} bis {input_width_end}x{input_height_end}",
        'price_count': len(options),
        'template_config': template_config,
        'parameters': {
            'csv_range': f"{csv_width_start}x{csv_height_start} bis {csv_width_end}x{csv_height_end}",
            'input_range': f"{input_width_start}x{input_height_start} bis {input_width_end}x{input_height_end}",
            'steps': f"{csv_width_step}x{csv_height_step}",
            'price_is_netto': price_is_netto,
            'vat': vat,
            's21_mark': s21_mark,
            'bsd_margin': bsd_margin,
            'shipping_surcharge': shipping_surcharge
        }
    }

    # 5) Output-PHP schreiben
    php_path = os.path.join(php_output_dir, output_file)
    
    if write_php_file(php_path, options, control_comments, template_config):
        success_msg = f"PHP-Datei erfolgreich erstellt: {output_file} ({calculated_count} Preise)"
        update_config_status(details, 'success', success_msg)
        processing_stats['successful_configs'] += 1
    else:
        error_msg = f"Fehler beim Schreiben der PHP-Datei: {output_file}"
        logger.error(f"Zeile {row_num}: {error_msg}")
        update_config_status(details, 'error', error_msg)
        processing_stats['failed_configs'] += 1
        processing_stats['errors'] += 1

def write_php_file(filepath, options, control_comments, template_config):
    """
    Schreibt das PHP-Array in eine Datei im neuen Format.
    Erweitert um strukturierte Input-Range Kommentare für dynamische Min/Max Werte.
    """
    # Ensure directory exists
    os.makedirs(os.path.dirname(filepath), exist_ok=True)
    
    # Get filename without extension for key
    filename = os.path.splitext(os.path.basename(filepath))[0]
    
    # Use template_key if provided, otherwise use filename
    array_key = template_config.get('template_key', filename)
    if not array_key:
        array_key = filename
    
    # =================================================================
    # ERWEITERTE HEADER-KOMMENTARE MIT INPUT-RANGES
    # =================================================================
    
    lines = []
    
    # Generierungsinformationen
    lines.append("<?php")
    lines.append("// ============================================================")
    lines.append(f"// Date: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    lines.append(f"// Key: {array_key}")
    lines.append(f"// File: {os.path.basename(filepath)}")
    
    # Kontrollpreise (falls vorhanden)
    if control_comments:
        lines.append("//")
        for line in control_comments:
            lines.append(line)
    
    # Input Range Informationen
    lines.append("//")
    lines.append("// ============================================================")
    
    # Input Width Range
    input_width_start = template_config.get('input_width_start', 400)
    input_width_end = template_config.get('input_width_end', 2500)
    lines.append("// Frontend Input Information:")
    lines.append(f"// Input Width Start: {input_width_start}")
    lines.append(f"// Input Width End: {input_width_end}")
    
    # Input Height Range
    input_height_start = template_config.get('input_height_start', 400)
    input_height_end = template_config.get('input_height_end', 2500)
    lines.append(f"// Input Height Start: {input_height_start}")
    lines.append(f"// Input Height End: {input_height_end}")
    
    # CSV Range Informationen (für Referenz)
    lines.append("//")
    lines.append("// CSV Matrix Information:")
    csv_width_start = template_config.get('csv_width_start', input_width_start)
    csv_width_end = template_config.get('csv_width_end', input_width_end)
    csv_height_start = template_config.get('csv_height_start', input_height_start)
    csv_height_end = template_config.get('csv_height_end', input_height_end)
    lines.append(f"// CSV Width Start: {csv_width_start}")
    lines.append(f"// CSV Width End: {csv_width_end}")
    lines.append(f"// CSV Height Start: {csv_height_start}")
    lines.append(f"// CSV Height End: {csv_height_end}")
    
    # Template Konfiguration
    lines.append("//")
    lines.append("// Template Configuration:")
    lines.append(f"// Order: {template_config.get('template_order', 30)}")
    lines.append(f"// Group: {template_config.get('template_group', 'masse')}")
    lines.append(f"// Label: {template_config.get('template_label', 'Aufpreis Breite und Höhe')}")
    
    # Matrix Statistiken
    if options:
        sizes = list(options.keys())
        price_values = [opt['price'] for opt in options.values() if isinstance(opt.get('price'), (int, float))]
        lines.append("//")
        lines.append("// Matrix Statistics:")
        lines.append(f"// Total Entries: {len(options)}")
        lines.append(f"// Size Range: {sizes[0]} - {sizes[-1] if sizes else 'N/A'}")
        if price_values:
            lines.append(f"// Price Range: €{min(price_values)} - €{max(price_values)}")
    
    lines.append("// ============================================================")
    lines.append("")
    lines.append("// Generated price matrix")
    lines.append("return array(")
    lines.append(f"    '{array_key}' => array(")
    lines.append(f"        'key' => '{array_key}',")
    lines.append(f"        'order' => {template_config.get('template_order', 30)},")
    lines.append(f"        'group' => '{template_config.get('template_group', 'masse')}',")
    lines.append(f"        'label' => '{template_config.get('template_label', 'Aufpreis Breite und Höhe')}',")
    lines.append(f"        'options' => array(")

    # Sortiere die Schlüssel (z. B. numerisch nach Breite x Höhe)
    def sort_key(item):
        k = item[0]
        w, h = k.split("x")
        return (int(w), int(h))

    for key, opt in sorted(options.items(), key=sort_key):
        lines.append(f"            '{key}' => array('label' => '{opt['label']}', 'price' => {opt['price']}),")
    
    lines.append("        ),")
    lines.append("    ),")
    lines.append(");")

    try:
        with open(filepath, "w", encoding="utf-8") as f:
            f.write("\n".join(lines))
        return True
    except Exception as e:
        return False

def clean_orphaned_php_files(current_output_files):
    """
    Intelligente Bereinigung: Entfernt nur PHP-Dateien, die nicht mehr in der 
    aktuellen Excel-Konfiguration stehen (verwaiste Dateien).
    
    Args:
        current_output_files: Set der aktuellen output_file Namen aus Excel
    """
    if not os.path.exists(php_output_dir):
        return
    
    # Alle existierenden PHP-Dateien im Verzeichnis finden
    existing_files = set()
    for filename in os.listdir(php_output_dir):
        if filename.endswith('.php'):
            existing_files.add(filename)
    
    # Verwaiste Dateien identifizieren (existieren, aber nicht in Excel)
    orphaned_files = existing_files - current_output_files
    
    if orphaned_files:
        logger.info(f"Gefunden: {len(orphaned_files)} verwaiste PHP-Dateien")
        print(f"🧹 Bereinigte verwaiste Dateien: {len(orphaned_files)}")
        
        for filename in orphaned_files:
            file_path = os.path.join(php_output_dir, filename)
            try:
                os.remove(file_path)
                logger.info(f"Verwaiste Datei entfernt: {filename}")
            except Exception as e:
                logger.warning(f"Fehler beim Entfernen von {filename}: {e}")
    else:
        logger.info("Keine verwaisten PHP-Dateien gefunden")
        print("✓ Keine verwaisten Dateien gefunden")

def generate_summary_report():
    """Generate a comprehensive summary report."""
    print("\n" + "=" * 60)
    print("ZUSAMMENFASSUNG DER PREISMATRIX-GENERIERUNG")
    print("=" * 60)
    
    logger.info("=" * 60)
    logger.info("ZUSAMMENFASSUNG DER PREISMATRIX-GENERIERUNG")
    logger.info("=" * 60)
    
    # Simplified summary for the terminal
    summary_lines = [
        f"Verarbeitete Konfigurationen: {processing_stats['total_configs']}",
        f"  - Erfolgreich: {processing_stats['successful_configs']}",
        f"  - Fehlgeschlagen: {processing_stats['failed_configs']}",
    ]
    
    # Add warnings and duplicates only if they exist
    if processing_stats['warnings'] > 0:
        warned_configs_count = len([d for d in processing_stats['config_details'] if d['warnings']])
        if warned_configs_count > 0:
            summary_lines.append(f"  - Konfigurationen mit Warnungen: {warned_configs_count}")
    if processing_stats['duplicates_found'] > 0:
        summary_lines.append(f"Gefundene Duplikate in Excel: {processing_stats['duplicates_found']}")

    # Add processing statistics to summary
    skipped_configs = [d for d in processing_stats['config_details'] if d['status'] == 'skipped']
    if skipped_configs:
        summary_lines.append(f"  - Übersprungen (unverändert): {len(skipped_configs)}")
    
    # Log full details to file
    full_summary_for_log = [
        f"Gesamtanzahl Konfigurationen: {processing_stats['total_configs']}",
        f"Erfolgreich verarbeitet: {processing_stats['successful_configs']}",
        # f"Fehlgeschlagen: {processing_stats['failed_configs']}",
        f"Warnungen (gesamt): {processing_stats['warnings']}",
        f"Fehler (gesamt): {processing_stats['errors']}",
        f"Duplikate (gesamt): {processing_stats['duplicates_found']}",
        f"Fehlende Datenpunkte (gesamt): {processing_stats['missing_data_points']}"
    ]
    
    for line in full_summary_for_log:
        logger.info(line)

    # Print simplified summary to console
    for line in summary_lines:
        print(line)
    
    # Separate configs by status
    failed_configs = [d for d in processing_stats['config_details'] if d['status'] == 'error']
    # A config has a "real" warning if its warning list is not empty.
    # Notes about skipped points are handled separately and don't clutter the warning summary.
    warned_configs = [d for d in processing_stats['config_details'] if d['warnings']]
    noted_configs = [d for d in processing_stats['config_details'] if d['status'] == 'success' and d['notes']]

    if failed_configs:
        logger.info("-" * 40)
        logger.info("FEHLERHAFTE KONFIGURATIONEN (KRITISCH):")
        logger.info("-" * 40)
        
        for details in failed_configs:
            status_symbol = "✗"
            detail_lines = [
                f"{status_symbol} Zeile {details['row']}: {details['input_file']} -> {details['output_file']}",
                f"    Status: {details['status'].upper()}"
            ]
            
            if 'message' in details:
                detail_lines.append(f"    Ursache: {details['message']}")
            
            for line in detail_lines:
                logger.info(line)
            logger.info("")

    if warned_configs:
        logger.info("\nKONFIGURATIONEN MIT WARNUNGEN (NICHT-KRITISCH):")
        logger.info("-" * 40)
        
        for details in warned_configs:
            status_symbol = "⚠"
            detail_lines = [
                f"{status_symbol} Zeile {details['row']}: {details['input_file']} -> {details['output_file']}"
            ]
            
            if details['warnings']:
                for warning in details['warnings']:
                    detail_lines.append(f"    Warnung: {warning}")

            for line in detail_lines:
                logger.info(line)
            logger.info("")

    if noted_configs:
        logger.info("-" * 40)
        logger.info("HINWEISE ZU KONFIGURATIONEN:")
        logger.info("-" * 40)
        
        for details in noted_configs:
            status_symbol = "ℹ"
            detail_lines = [
                f"{status_symbol} Zeile {details['row']}: {details['input_file']} -> {details['output_file']}"
            ]
            
            if details['notes']:
                for note in details['notes']:
                    detail_lines.append(f"    Hinweis: {note}")

            for line in detail_lines:
                logger.info(line)
            logger.info("")

    # if not failed_configs and not warned_configs:
        print("\n")
        logger.info("\n")
    
    # Final status
    if processing_stats['failed_configs'] == 0 and processing_stats['errors'] == 0:
        final_msg = "🎉 ALLE PREISMATRIZEN ERFOLGREICH GENERIERT!"
    elif processing_stats['successful_configs'] > 0:
        final_msg = f"⚠️ TEILWEISE ERFOLGREICH: {processing_stats['successful_configs']} von {processing_stats['total_configs']} Konfigurationen verarbeitet"
    else:
        final_msg = "❌ GENERIERUNG FEHLGESCHLAGEN: Keine Preismatrizen erstellt"
    
    print(final_msg)
    logger.info(final_msg)

def main():
    """
    Hauptfunktion: Lädt Konfiguration aus Excel und verarbeitet alle Einträge.
    
    IMPROVED LOADING PRIORITY:
    1. Load defaults from Excel 'defaults' sheet (with Python fallback)
    2. Calculate defaults hash for change detection
    3. Load inputs from Excel 'inputs' sheet
    4. Apply defaults to inputs where values are missing
    5. Process each configuration with defaults change detection
    """
    log_path = setup_logging()
    
    # Ausgabe-Verzeichnis erzeugen
    os.makedirs(php_output_dir, exist_ok=True)
    
    # Load previous metadata
    previous_metadata = load_previous_metadata()
    current_metadata = {}
    
    # CSV-Verzeichnis prüfen
    if not os.path.exists(csv_dir):
        error_msg = f"CSV-Verzeichnis nicht gefunden: {csv_dir}"
        print(f"✗ {error_msg}")
        logger.error(error_msg)
        return

    # STEP 1: Load defaults (Excel primary, Python fallback)
    print("✓ Lade globale Defaults aus Excel")
    defaults = load_defaults_from_excel()
    
    if not defaults:
        error_msg = "Keine Default-Werte geladen!"
        print(f"✗ {error_msg}")
        logger.error(error_msg)
        return

    # STEP 2: Calculate defaults hash for change detection
    defaults_hash = calculate_defaults_hash(defaults)
    logger.info(f"Defaults hash calculated: {defaults_hash}")
    
    # Log if defaults have changed from previous run
    if previous_metadata:
        # Try to get previous defaults hash from overview file
        overview_path = os.path.join(base_output_dir, 'matrices_overview.json')
        previous_defaults_hash = None
        if os.path.exists(overview_path):
            try:
                with open(overview_path, 'r', encoding='utf-8') as f:
                    overview_data = json.load(f)
                    previous_defaults_hash = overview_data.get('defaults_hash')
            except Exception as e:
                logger.warning(f"Could not read previous defaults hash: {e}")
        
        if previous_defaults_hash and previous_defaults_hash != defaults_hash:
            logger.info("! Globale Defaults haben sich geändert - alle Matrizen werden neu generiert!")
            print("! Globale Defaults geändert - alle Matrizen werden aktualisiert")
        elif previous_defaults_hash == defaults_hash:
            logger.info("✓ Globale Defaults unverändert")

    # STEP 3: Load input configurations and apply defaults
    print("✓ Lade Konfigurationen aus Excel")
    inputs = load_inputs_from_excel(defaults)
    
    if not inputs:
        error_msg = "Keine gültigen Konfigurationen in Excel-Datei gefunden!"
        print(f"✗ {error_msg}")
        logger.error(error_msg)
        return

    processing_stats['total_configs'] = len(inputs)
    print(f"✓ {len(inputs)} Konfigurationen gefunden")
    
    # Show summary of defaults used
    print(f"✓ {len(defaults)} Default-Werte aus Excel geladen")
    
    # Check for changes and show summary - NOW INCLUDING DEFAULTS
    changes_summary = {'new': 0, 'changed': 0, 'unchanged': 0}
    
    for conf in inputs:
        output_file = conf.get('output_file')
        has_changes, change_reason, _, _ = check_for_changes(conf, previous_metadata, defaults_hash)
        
        if not has_changes:
            changes_summary['unchanged'] += 1
        elif 'Neue Konfiguration' in change_reason:
            changes_summary['new'] += 1
        else:
            changes_summary['changed'] += 1
    
    print(f"✓ Änderungen: {changes_summary['new']} neu, {changes_summary['changed']} geändert, {changes_summary['unchanged']} unverändert")
    
    # Intelligente Bereinigung: Entferne verwaiste PHP-Dateien
    current_output_files = set(conf.get('output_file') for conf in inputs if conf.get('output_file'))
    clean_orphaned_php_files(current_output_files)
    
    # Validate Excel data
    validate_excel_data(inputs)
    
    # Process each configuration
    logger.info("=" * 60)
    logger.info("VERARBEITUNG STARTET")
    logger.info("=" * 60)
    
    for i, conf in enumerate(inputs, 1):
        process_file(conf, i, len(inputs), previous_metadata, current_metadata, defaults_hash)
    
    # Clear progress bar line completely before showing final status
    print(f'\r{" " * 120}', end='', flush=True)
    
    # Show final status
    if processing_stats['failed_configs'] > 0:
        print(f"\r✓ Verarbeitung abgeschlossen mit {processing_stats['failed_configs']} Fehlern.")
    else:
        print(f"\r✓ Verarbeitung erfolgreich abgeschlossen.")
    
    # Save metadata and generate overview - NOW INCLUDING DEFAULTS HASH
    save_metadata(current_metadata)
    generate_overview_report(current_metadata, defaults_hash)
    
    # Generate summary report
    generate_summary_report()
    
    # Update summary to include skipped files
    skipped_count = changes_summary['unchanged']
    if skipped_count > 0:
        print(f"✓ {skipped_count} Dateien übersprungen (unverändert)")
    
    # Conditionally show log file path
    if ENABLE_LOG_FILE and log_path:
        print(f"\n📄 Vollständiges Log: {log_path}")
    print(f"============================================================")

if __name__ == "__main__":
    main()
