import os
import pandas as pd
import logging
from datetime import datetime
import sys
import re

# ----------------------------------------
# Logging configuration
# ----------------------------------------

def setup_logging():
    """Setup logging configuration."""
    script_dir = os.path.dirname(os.path.abspath(__file__))
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    log_filename = f"extract_base_prices_log_{timestamp}.log"
    log_path = os.path.join(script_dir, log_filename)
    
    logging.basicConfig(
        level=logging.INFO,
        format='%(asctime)s - %(levelname)s - %(message)s',
        handlers=[
            logging.FileHandler(log_path, encoding='utf-8')
        ],
        force=True
    )
    
    print(f"============================================================")
    print(f"✓ Basispreis-Extraktion gestartet um {datetime.now().strftime('%H:%M:%S')}")
    
    return log_path

logger = logging.getLogger(__name__)

# ----------------------------------------
# Configuration
# ----------------------------------------

script_dir = os.path.dirname(os.path.abspath(__file__))
PRODUKTPALETTE_PATH = os.path.join(script_dir, 'Produktpalette.xlsx')
PRICEMATRIX_DIR = os.path.join(script_dir, 'pricematrices', 'php')
OUTPUT_PATH = os.path.join(script_dir, 'Basispreise_Produktpalette.xlsx')

# ----------------------------------------
# Helper functions
# ----------------------------------------

def read_produktpalette():
    """Liest alle Arbeitsblätter der Produktpalette.xlsx ein und extrahiert Name und Preismatrix."""
    if not os.path.exists(PRODUKTPALETTE_PATH):
        logger.error(f"Produktpalette.xlsx nicht gefunden: {PRODUKTPALETTE_PATH}")
        return None
    
    try:
        # Hole alle verfügbaren Sheet-Namen
        excel_file = pd.ExcelFile(PRODUKTPALETTE_PATH)
        all_sheets = excel_file.sheet_names
        logger.info(f"Gefundene Arbeitsblätter: {all_sheets}")
        
        all_products = []
        processed_sheets = []
        
        for sheet_name in all_sheets:
            logger.info(f"Verarbeite Arbeitsblatt: '{sheet_name}'")
            
            try:
                df = pd.read_excel(PRODUKTPALETTE_PATH, sheet_name=sheet_name, dtype=str).fillna('')
                
                # Prüfe ob das Sheet gültige Daten hat (mindestens 2 Zeilen)
                if len(df) < 1:
                    logger.warning(f"Sheet '{sheet_name}' ist leer - überspringe")
                    continue
                
                # Finde die richtigen Spalten (case-insensitive)
                name_col = None
                preismatrix_col = None
                
                for col in df.columns:
                    col_lower = str(col).lower()
                    if 'name' in col_lower and name_col is None:
                        name_col = col
                    elif 'preismatrix' in col_lower or 'preis' in col_lower:
                        preismatrix_col = col
                
                if not name_col or not preismatrix_col:
                    logger.warning(f"Sheet '{sheet_name}': Benötigte Spalten nicht gefunden (Name: {name_col}, Preismatrix: {preismatrix_col})")
                    logger.info(f"Verfügbare Spalten in '{sheet_name}': {list(df.columns)}")
                    continue
                
                # Filtere leere Zeilen und bereite Daten vor
                sheet_data = df[[name_col, preismatrix_col]].copy()
                sheet_data.columns = ['Name', 'Preismatrix']
                sheet_data = sheet_data[sheet_data['Name'].str.strip() != '']
                sheet_data = sheet_data[sheet_data['Preismatrix'].str.strip() != '']
                
                # Füge Sheet-Information hinzu
                sheet_data['Arbeitsblatt'] = sheet_name
                
                if len(sheet_data) > 0:
                    all_products.append(sheet_data)
                    processed_sheets.append(sheet_name)
                    logger.info(f"Sheet '{sheet_name}': {len(sheet_data)} Produkte gefunden")
                else:
                    logger.warning(f"Sheet '{sheet_name}': Keine gültigen Produktdaten gefunden")
                    
            except Exception as e:
                logger.error(f"Fehler beim Lesen von Sheet '{sheet_name}': {e}")
                continue
        
        if not all_products:
            logger.error("Keine gültigen Produktdaten in keinem Arbeitsblatt gefunden")
            return None
        
        # Kombiniere alle Produkte aus allen Sheets
        result = pd.concat(all_products, ignore_index=True)
        
        logger.info(f"Gesamt verarbeitete Arbeitsblätter: {len(processed_sheets)} von {len(all_sheets)}")
        logger.info(f"Verarbeitete Sheets: {processed_sheets}")
        logger.info(f"Produktpalette gelesen: {len(result)} Produkte aus {len(processed_sheets)} Arbeitsblättern")
        
        print(f"✓ Verarbeitete Arbeitsblätter: {len(processed_sheets)} ({', '.join(processed_sheets)})")
        
        return result
        
    except Exception as e:
        logger.error(f"Fehler beim Lesen der Produktpalette: {e}")
        return None

def extract_base_price_from_php(php_filepath):
    """Extrahiert den Basispreis aus einer PHP-Preismatrix-Datei."""
    if not os.path.exists(php_filepath):
        return None, "Datei nicht gefunden"
    
    try:
        with open(php_filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Suche nach dem Basispreis-Kommentar
        base_price_match = re.search(r'Basispreis \(wird abgezogen\): (\d+)', content)
        if base_price_match:
            base_price = int(base_price_match.group(1))
            logger.debug(f"Basispreis aus Kommentar extrahiert: {base_price}")
            return base_price, "Aus Kommentar"
        
        # Alternative: Suche nach dem ersten Eintrag mit price => 0
        zero_price_match = re.search(r"'(\d+x\d+)'\s*=>\s*array\([^}]*'price'\s*=>\s*0", content)
        if zero_price_match:
            dimensions = zero_price_match.group(1)
            logger.debug(f"Startmaß gefunden: {dimensions}")
            
            # Suche nach dem nächsten Eintrag mit einem Preis > 0
            # Extrahiere alle Preise aus der Datei
            price_matches = re.findall(r"'(\d+x\d+)'\s*=>\s*array\([^}]*'price'\s*=>\s*(\d+)", content)
            
            if price_matches:
                # Finde den kleinsten Preis > 0
                prices = [(dims, int(price)) for dims, price in price_matches if int(price) > 0]
                if prices:
                    prices.sort(key=lambda x: x[1])
                    smallest_price = prices[0][1]
                    logger.debug(f"Kleinster Preis > 0: {smallest_price}")
                    
                    # Rekonstruiere den Basispreis (approximation)
                    # Der Basispreis ist ungefähr der Preis des Startmaßes vor Abzug
                    estimated_base = smallest_price
                    return estimated_base, "Geschätzt aus kleinstem Preis"
        
        logger.warning(f"Kein Basispreis in {php_filepath} gefunden")
        return None, "Kein Basispreis gefunden"
        
    except Exception as e:
        logger.error(f"Fehler beim Lesen von {php_filepath}: {e}")
        return None, f"Fehler: {str(e)}"

def round_to_90_ending(price):
    """Rundet einen Preis auf die nächste ,90-Endung."""
    if price is None:
        return None
    
    # Runde auf den nächsten 10er-Bereich und setze auf ,90
    base = (price // 10) * 10
    
    # Wenn der ursprüngliche Preis bereits nahe bei x,90 ist, behalte es
    if price % 10 >= 5:
        base += 10
    
    return base - 0.10  # x,90 statt x,00

def process_products(df_products):
    """Verarbeitet alle Produkte und extrahiert die Basispreise."""
    results = []
    
    for index, row in df_products.iterrows():
        name = row['Name']
        preismatrix = row['Preismatrix']
        arbeitsblatt = row.get('Arbeitsblatt', 'Unbekannt')
        
        # Füge .php hinzu falls nicht vorhanden
        if not preismatrix.lower().endswith('.php'):
            preismatrix_file = preismatrix + '.php'
        else:
            preismatrix_file = preismatrix
        
        php_path = os.path.join(PRICEMATRIX_DIR, preismatrix_file)
        
        logger.info(f"Verarbeite: {name} -> {preismatrix_file} (aus '{arbeitsblatt}')")
        
        base_price, method = extract_base_price_from_php(php_path)
        
        if base_price is not None:
            rounded_price = round_to_90_ending(base_price)
            status = "Erfolgreich"
        else:
            rounded_price = None
            status = method
        
        result = {
            'Name': name,
            'Preismatrix': preismatrix,
            'Arbeitsblatt': arbeitsblatt,
            'Basispreis_Original': base_price,
            'Basispreis_90er': rounded_price,
            'Extraktionsmethode': method,
            'Status': status
        }
        
        results.append(result)
        
        if base_price is not None:
            print(f"✓ {name} ({arbeitsblatt}): {base_price} -> {rounded_price}")
        else:
            print(f"✗ {name} ({arbeitsblatt}): {method}")
    
    return results

def save_results(results):
    """Speichert die Ergebnisse in eine Excel-Datei mit separaten Sheets pro Arbeitsblatt."""
    try:
        df_results = pd.DataFrame(results)
        
        # Erstelle verschiedene Sheets
        with pd.ExcelWriter(OUTPUT_PATH, engine='openpyxl') as writer:
            # Hauptsheet mit allen Daten
            df_results.to_excel(writer, sheet_name='Alle_Basispreise', index=False)
            
            # Sheet nur mit Name und Basispreis (für einfache Nutzung)
            df_simple = df_results[['Name', 'Preismatrix', 'Arbeitsblatt', 'Basispreis_90er']].copy()
            df_simple = df_simple[df_simple['Basispreis_90er'].notna()]
            df_simple.to_excel(writer, sheet_name='Name_Basispreis', index=False)
            
            # Separate Sheets pro Arbeitsblatt
            if 'Arbeitsblatt' in df_results.columns:
                unique_sheets = df_results['Arbeitsblatt'].unique()
                for sheet_name in unique_sheets:
                    if pd.isna(sheet_name):
                        continue
                    
                    sheet_data = df_results[df_results['Arbeitsblatt'] == sheet_name].copy()
                    # Entferne die Arbeitsblatt-Spalte für die einzelnen Sheets
                    if len(sheet_data) > 0:
                        sheet_safe_name = str(sheet_name)[:30]  # Excel Sheet-Namen max 31 Zeichen
                        sheet_safe_name = sheet_safe_name.replace('/', '_').replace('\\', '_')  # Ungültige Zeichen ersetzen
                        try:
                            sheet_data.to_excel(writer, sheet_name=f'Sheet_{sheet_safe_name}', index=False)
                        except Exception as e:
                            logger.warning(f"Fehler beim Erstellen von Sheet für '{sheet_name}': {e}")
            
            # Sheet mit Fehlern
            df_errors = df_results[df_results['Basispreis_90er'].isna()]
            if not df_errors.empty:
                df_errors.to_excel(writer, sheet_name='Fehler', index=False)
        
        logger.info(f"Ergebnisse gespeichert: {OUTPUT_PATH}")
        return True
        
    except Exception as e:
        logger.error(f"Fehler beim Speichern: {e}")
        return False

def generate_summary(results):
    """Erstellt eine Zusammenfassung der Ergebnisse mit Aufschlüsselung nach Arbeitsblättern."""
    total = len(results)
    successful = len([r for r in results if r['Basispreis_90er'] is not None])
    failed = total - successful
    
    print(f"\n{'='*60}")
    print(f"ZUSAMMENFASSUNG BASISPREIS-EXTRAKTION")
    print(f"{'='*60}")
    print(f"Gesamte Produkte: {total}")
    print(f"Erfolgreich: {successful}")
    print(f"Fehlgeschlagen: {failed}")
    
    # Aufschlüsselung nach Arbeitsblättern
    if 'Arbeitsblatt' in pd.DataFrame(results).columns:
        df_results = pd.DataFrame(results)
        sheets_summary = df_results.groupby('Arbeitsblatt').agg({
            'Name': 'count',
            'Basispreis_90er': lambda x: x.notna().sum()
        }).rename(columns={'Name': 'Gesamt', 'Basispreis_90er': 'Erfolgreich'})
        
        print(f"\nAufschlüsselung nach Arbeitsblättern:")
        for sheet_name, row in sheets_summary.iterrows():
            erfolg_rate = (row['Erfolgreich'] / row['Gesamt'] * 100) if row['Gesamt'] > 0 else 0
            print(f"  - {sheet_name}: {row['Erfolgreich']}/{row['Gesamt']} ({erfolg_rate:.1f}%)")
    
    if failed > 0:
        print(f"\nFehlgeschlagene Produkte:")
        for result in results:
            if result['Basispreis_90er'] is None:
                arbeitsblatt_info = f" ({result.get('Arbeitsblatt', 'Unbekannt')})" if result.get('Arbeitsblatt') else ""
                print(f"  - {result['Name']}{arbeitsblatt_info}: {result['Status']}")
    
    print(f"\n✓ Ergebnisse gespeichert in: {OUTPUT_PATH}")
    
    logger.info(f"Zusammenfassung: {successful}/{total} erfolgreich verarbeitet")

def main():
    """Hauptfunktion."""
    log_path = setup_logging()
    
    # 1. Produktpalette einlesen
    print("✓ Lade Produktpalette...")
    df_products = read_produktpalette()
    if df_products is None:
        print("✗ Fehler beim Laden der Produktpalette")
        return
    
    # 2. Preismatrix-Verzeichnis prüfen
    if not os.path.exists(PRICEMATRIX_DIR):
        logger.error(f"Preismatrix-Verzeichnis nicht gefunden: {PRICEMATRIX_DIR}")
        print(f"✗ Preismatrix-Verzeichnis nicht gefunden")
        return
    
    print(f"✓ {len(df_products)} Produkte geladen")
    
    # 3. Basispreise extrahieren
    print("✓ Extrahiere Basispreise...")
    results = process_products(df_products)
    
    # 4. Ergebnisse speichern
    print("✓ Speichere Ergebnisse...")
    if save_results(results):
        print("✓ Excel-Datei erstellt")
    else:
        print("✗ Fehler beim Speichern")
        return
    
    # 5. Zusammenfassung
    generate_summary(results)
    
    print(f"\n📄 Vollständiges Log: {log_path}")
    print(f"{'='*60}")

if __name__ == "__main__":
    main()
