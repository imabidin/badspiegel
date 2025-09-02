import pandas as pd
import os
import re

# ------------------------------------------------------------------------
# 1) Vars
# ------------------------------------------------------------------------

script_dir = os.path.dirname(os.path.abspath(__file__))

CONFIG_XLSX_PATH = os.path.join(script_dir, 'options.xlsx')
MAIN_OPTIONS_PHP = os.path.join(script_dir, 'options.php')

PRICE_MULTIPLIER = 1.0

# ------------------------------------------------------------------------
# 2) Mapping
#
# Column => (Key, Type)
# ------------------------------------------------------------------------
OPTION_COLUMNS = {
    "option":               ("option", "string"),
    "type":                 ("type", "string"),
    "key":                  ("key", "string"),
    "price":                ("price", "float"),
    "apply products":       ("apply_products", "array"),
    "exclude products":     ("exclude_products", "array"),
    "apply categories":     ("apply_categories", "array"),
    "exclude categories":   ("exclude_categories", "array"),
    "apply attributes":     ("apply_attributes", "array"),
    "exclude attributes":   ("exclude_attributes", "array"),
    "group":                ("group", "string"),
    "order":                ("order", "int"),
    "label":                ("label", "string"),
    "description":          ("description", "string"),
    "description file":     ("description_file", "string"),
    "aria":                 ("aria", "string"),
    "placeholder":          ("placeholder", "string"),
    "placeholder image":    ("placeholder_image", "string"),
    "placeholder description":      ("placeholder_description", "string"),
    "placeholder description file": ("placeholder_description_file", "string"),  # Fixed typo
    "required":             ("required", "bool"),
    "min":                  ("min", "string"),
    "max":                  ("max", "string"),
}

VALUE_COLUMNS = {
    "apply option":         ("apply_option", "string"),
    "order":                ("order", "int"),
    "value":                ("value", "string"),
    "price":                ("price", "float"),
    "image":                ("image", "string"),
    "description":          ("description", "string"),
    "description file":     ("description_file", "string"),
    "exclude categories":   ("excluded_categories", "array"),
    "exclude attributes":   ("excluded_attributes", "array"),
}

# ------------------------------------------------------------------------
# 3) Functions
# ------------------------------------------------------------------------

def parse_cell_value(raw_value: str, field_type: str):
    """
    - "string" => unchanged (after .strip())
    - "bool"   => True/False
    - "int"    => integer "0"
    - "float"  => float "0.0"
    - "array"  => comma separated values
    """
    raw_value = raw_value.strip()
    
    # Default values
    if not raw_value:
        if field_type == "array":
            return []
        elif field_type == "float":
            return 0.0
        elif field_type == "int":
            return 0
        elif field_type == "bool":
            return False
        elif field_type in ["string", ""]:
            return ""
        else:
            # Fallback for unknown types
            return ""
    
    # Conversion
    if field_type == "string":
        return raw_value
    
    elif field_type == "bool":
        return (raw_value.upper() == "TRUE") 
    
    elif field_type == "int":
        try:
            return int(float(raw_value.replace(",", ".")))
        except ValueError:
            return 0
    
    elif field_type == "float":
        try:
            return float(raw_value.replace(",", "."))
        except ValueError:
            return 0.0
    
    elif field_type == "array":  # "123,abc,  45" -> [123, "abc", 45]
        results = [] 
        for item in raw_value.split(","):
            item = item.strip()
            if not item:
                continue
            # Integer check
            try:
                as_int = int(float(item.replace(",", ".")))
                results.append(as_int)
                continue
            except ValueError:
                pass
            
            # String fallback
            results.append(item)
        return results
    
    else:
        # Fallback for unknown types
        return raw_value


def generate_option_key(option_name: str) -> str:
    """
    Generates a "clean" key: replaces umlauts, removes special characters,
    replaces spaces with underscores, etc.
    """
    replacements = {
        "ä": "ae",
        "ö": "oe",
        "ü": "ue",
        "ß": "ss",
        "Ä": "Ae",
        "Ö": "Oe",
        "Ü": "Ue",
    }
    for old, new in replacements.items():
        option_name = option_name.replace(old, new)

    name_clean = re.sub(r'[^\w\s-]', '', option_name) # Remove special characters
    name_clean = re.sub(r'[\s-]+', '_', name_clean) # Replace spaces with underscores

    return name_clean.lower()

def convert_to_php_value(item):
    """
    Converts a single value to the appropriate PHP expression.
    - Boolean => true/false
    - Number (int/float) => without quotes
    - String => in single quotes
    """
    if isinstance(item, bool):
        return str(item).lower()
    elif isinstance(item, (int, float)):
        return str(item)
    else:
        return f"'{item}'"

def format_php_array(values):
    """
    Converts Python lists or single values to PHP array syntax.
    """
    if not isinstance(values, list):
        return convert_to_php_value(values)

    formatted_items = [convert_to_php_value(v) for v in values]
    return ", ".join(formatted_items)

def adjust_price(base_price: float, multiplier: float) -> int:
    """
    Multiply price and round to the nearest integer.
    """
    new_price = base_price * multiplier
    return int(round(new_price))

# Globales Set für fehlende Dateien
missing_files = set()

def check_description_file_exists(filename: str) -> str:
    """
    Prüft, ob eine description_file existiert. Fügt .html hinzu falls nötig.
    Sucht in verschiedenen Unterordnern der html-Struktur.
    Gibt den korrekten Dateinamen zurück oder leeren String falls nicht gefunden.
    """
    global missing_files
    
    if not filename or not filename.strip():
        return ""
    
    original_filename = filename
    
    # .html anhängen falls keine Erweiterung vorhanden
    if not filename.endswith('.html'):
        filename = filename + '.html'
    
    # Basis-Pfad für HTML-Dateien
    html_base_dir = os.path.join(script_dir, '..', '..', 'html')
    
    # Verschiedene mögliche Pfade durchsuchen
    search_paths = [
        os.path.join(html_base_dir, 'configurator', 'newers', filename),
    ]
    
    # Prüfen ob Datei in einem der Pfade existiert
    for full_path in search_paths:
        if os.path.exists(full_path):
            return filename
    
    # Datei nicht gefunden - zu Set hinzufügen
    missing_files.add(original_filename)
    return ""

# ------------------------------------------------------------------------
# 4) Generate PHP
# ------------------------------------------------------------------------

def generate_main_options_php(options_list, values_dict):
    php_code = """<?php
if (! defined('ABSPATH')) {
    exit;
}

function get_all_product_options()
{
    static $options_cache = null;

    if (!is_null($options_cache)) {
    return $options_cache;
}

    $options_cache = array(
                """

    for opt in options_list:
        raw_option = opt.get('option', '')
        option_type = opt.get('type', '')
        
        if not raw_option or not option_type:
            # Keine Option oder kein Type => Überspringen
            continue

        # Array-Key im PHP: Slug aus "option"
        array_key = generate_option_key(raw_option)

        # 'key' = slug aus "key"-Feld, wenn vorhanden
        custom_key_str = opt.get('key', '')
        if custom_key_str:
            internal_key = generate_option_key(custom_key_str)
        else:
            internal_key = array_key

        order_val = opt.get('order', 0)           # int
        required_flag = opt.get('required', False)  # bool

        php_code += f"        '{array_key}' => array(\n"
        php_code += f"            'key' => '{internal_key}',\n"
        php_code += f"            'order' => {order_val},\n"
        if required_flag:
            php_code += "            'required' => true,\n"

        # Felder, die separat behandelt werden:
        skip_fields = {
            'option', 'key', 'order', 'required',
            'apply_products', 'exclude_products',
            'apply_categories', 'exclude_categories',
            'apply_attributes', 'exclude_attributes'
        }

        # Alle anderen Felder dynamisch ausgeben (including placeholder_description_file)
        for field_name, value in opt.items():
            if field_name in skip_fields:
                continue
            if value == "" or value == [] or value is None:
                continue  # Leere Felder überspringen

            # Spezielle Behandlung für description_file Felder
            if field_name in ['description_file', 'placeholder_description_file']:
                checked_file = check_description_file_exists(str(value))
                if checked_file:
                    php_code += f"            '{field_name}' => '{checked_file}',\n"
                continue

            if field_name == 'price':
                # float => multiplizieren + runden
                new_price = adjust_price(float(value), PRICE_MULTIPLIER)
                php_code += f"            'price' => {new_price},\n"
            elif isinstance(value, bool):
                php_code += f"            '{field_name}' => {str(value).lower()},\n"
            elif isinstance(value, (int, float)):
                php_code += f"            '{field_name}' => {value},\n"
            elif isinstance(value, list):
                php_code += f"            '{field_name}' => array({format_php_array(value)}),\n"
            else:
                # String - escape single quotes for PHP
                escaped_value = value.replace("'", "\\'")
                php_code += f"            '{field_name}' => '{escaped_value}',\n"

        # Applies-To (Listen/Arrays)
        apply_products   = opt.get('apply_products', [])
        apply_categories = opt.get('apply_categories', [])
        exclude_products = opt.get('exclude_products', [])
        exclude_categories = opt.get('exclude_categories', [])
        apply_attributes = opt.get('apply_attributes', [])
        exclude_attributes = opt.get('exclude_attributes', [])

        php_code += "            'applies_to' => array(\n"
        php_code += f"                'products'           => array({format_php_array(apply_products)}),\n"
        php_code += f"                'categories'         => array({format_php_array(apply_categories)}),\n"
        php_code += f"                'attributes'         => array({format_php_array(apply_attributes)}),\n"
        php_code += f"                'excluded_products'  => array({format_php_array(exclude_products)}),\n"
        php_code += f"                'excluded_categories'=> array({format_php_array(exclude_categories)}),\n"
        php_code += f"                'excluded_attributes'=> array({format_php_array(exclude_attributes)}),\n"
        php_code += "            ),\n"

        # Sub-Optionen aus values_dict
        current_values = values_dict.get(raw_option, [])
        php_code += "            'options' => array(\n"
        for val_item in current_values:
            val_value = val_item.get('value', '')
            if not val_value:
                continue

            val_key = generate_option_key(val_value)
            base_price = val_item.get('price', 0.0)
            final_price = adjust_price(base_price, PRICE_MULTIPLIER)

            php_code += f"                '{val_key}' => array(\n"
            php_code += f"                    'key' => '{val_key}',\n"
            php_code += f"                    'price' => {final_price},\n"
            php_code += f"                    'label' => '{val_value}',\n"

            # Add order support
            val_order = val_item.get('order', 0)
            if val_order:
                php_code += f"                    'order' => {val_order},\n"

            val_img = val_item.get('image', '')
            if val_img:
                php_code += f"                    'image' => '{val_img}',\n"

            val_desc = val_item.get('description', '')
            if val_desc:
                escaped_desc = val_desc.replace("'", "\\'")
                php_code += f"                    'description' => '{escaped_desc}',\n"

            # Add description file support mit Existenzprüfung
            val_desc_file = val_item.get('description_file', '')
            if val_desc_file:
                checked_val_file = check_description_file_exists(val_desc_file)
                if checked_val_file:
                    php_code += f"                    'description_file' => '{checked_val_file}',\n"

            exc_cat_list = val_item.get('excluded_categories', [])
            if exc_cat_list:
                php_code += f"                    'excluded_categories' => array({format_php_array(exc_cat_list)}),\n"

            exc_attr_list = val_item.get('excluded_attributes', [])
            if exc_attr_list:
                php_code += f"                    'excluded_attributes' => array({format_php_array(exc_attr_list)}),\n"

            php_code += "                ),\n"
        php_code += "            ),\n"

        php_code += "        ),\n"

    php_code += """    );

    return $options_cache;
}
"""

    # In Datei schreiben
    with open(MAIN_OPTIONS_PHP, "w", encoding="utf-8") as f:
        f.write(php_code)

# ------------------------------------------------------------------------
# 5) Main
# ------------------------------------------------------------------------

def main():
    global missing_files
    missing_files = set()  # Reset für jeden Durchlauf
    
    if not os.path.exists(CONFIG_XLSX_PATH):
        print(f"Fehler: {CONFIG_XLSX_PATH} nicht gefunden.")
        return

    try:
        # Excel-Dateien einlesen, Zellen als Strings
        df_options = pd.read_excel(CONFIG_XLSX_PATH, sheet_name='options', dtype=str).fillna('')
        df_values = pd.read_excel(CONFIG_XLSX_PATH, sheet_name='values', dtype=str).fillna('')
        df_pxd = pd.read_excel(CONFIG_XLSX_PATH, sheet_name='pxd', dtype=str).fillna('')
        df_pxt = pd.read_excel(CONFIG_XLSX_PATH, sheet_name='pxt', dtype=str).fillna('')
    except Exception as e:
        print(f"Fehler beim Laden von {CONFIG_XLSX_PATH}: {e}")
        return

    # 5a) options_list (typkonvertiert)
    options_list = []
    for _, row in df_options.iterrows():
        opt_dict = {}
        for xlsx_col, (internal_key, field_type) in OPTION_COLUMNS.items():
            raw_value = row.get(xlsx_col, '')
            opt_dict[internal_key] = parse_cell_value(raw_value, field_type)
        options_list.append(opt_dict)

    # 5b) values_dict (typkonvertiert)
    values_dict = {}
    
    # Erst normale values verarbeiten
    for _, row in df_values.iterrows():
        val_dict = {}
        for xlsx_col, (internal_key, field_type) in VALUE_COLUMNS.items():
            raw_value = row.get(xlsx_col, '')
            val_dict[internal_key] = parse_cell_value(raw_value, field_type)

        apply_option = val_dict.get("apply_option", '')
        if not apply_option:
            continue

        if apply_option not in values_dict:
            values_dict[apply_option] = []
        values_dict[apply_option].append(val_dict)
    
    # Dann PXD values verarbeiten (überschreibt normale values für PXD Optionen)
    for _, row in df_pxd.iterrows():
        val_dict = {}
        for xlsx_col, (internal_key, field_type) in VALUE_COLUMNS.items():
            raw_value = row.get(xlsx_col, '')
            val_dict[internal_key] = parse_cell_value(raw_value, field_type)

        apply_option = val_dict.get("apply_option", '')
        if not apply_option:
            continue

        # Nur PXD Optionen aus diesem Sheet verarbeiten
        if apply_option.startswith("PXD"):
            if apply_option not in values_dict:
                values_dict[apply_option] = []
            values_dict[apply_option].append(val_dict)
    
    # Dann PXT values verarbeiten (überschreibt normale values für PXT Optionen)
    for _, row in df_pxt.iterrows():
        val_dict = {}
        for xlsx_col, (internal_key, field_type) in VALUE_COLUMNS.items():
            raw_value = row.get(xlsx_col, '')
            val_dict[internal_key] = parse_cell_value(raw_value, field_type)

        apply_option = val_dict.get("apply_option", '')
        if not apply_option:
            continue

        # Nur PXT Optionen aus diesem Sheet verarbeiten
        if apply_option.startswith("PXT"):
            if apply_option not in values_dict:
                values_dict[apply_option] = []
            values_dict[apply_option].append(val_dict)

    # 5c) Erzeuge die PHP-Datei
    generate_main_options_php(options_list, values_dict)
    
    # Log fehlende Dateien (falls vorhanden)
    if missing_files:
        print(f"⚠️  {len(missing_files)} description file(s) not found:")
        for filename in sorted(missing_files):
            print(f"   • {filename}")
        print()
    
    print(f"✅ Optionen erfolgreich generiert: {MAIN_OPTIONS_PHP}")

if __name__ == "__main__":
    main()
