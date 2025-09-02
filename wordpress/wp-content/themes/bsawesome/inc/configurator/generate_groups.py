import pandas as pd
import os

# Verzeichnis des Skripts
script_dir = os.path.dirname(os.path.abspath(__file__))

# Pfade definieren
GROUPS_CSV_PATH = os.path.join(script_dir, 'groups.csv')
MAIN_GROUPS_PHP = os.path.join(script_dir, 'groups.php')

def generate_main_groups_php(groups):
    """
    Generiert die Haupt-PHP-Datei mit dem statischen Array für Gruppen.
    """
    php_code = """<?php
if (! defined('ABSPATH')) {
    exit;
}

function get_all_product_option_groups()
{
    static $groups_cache = null;
    if (!is_null($groups_cache)) {
        return $groups_cache;
    }

    $groups_cache = array(
"""

    for group in groups:
        # Sanitize and escape single quotes in strings to prevent PHP syntax errors
        label = group['label'].replace("'", "\\'")
        description = group['description'].replace("'", "\\'")
        description_file = group['description file'].replace("'", "\\'")

        php_code += f"        '{group['group']}' => array(\n"
        php_code += f"            'order' => {group['order']},\n"
        php_code += f"            'class' => '{group['class']}',\n"
        php_code += f"            'label' => __('{label}', 'my-product-configurator'),\n"
        php_code += f"            'description' => '{description}',\n"
        php_code += f"            'description_file' => '{description_file}',\n"
        php_code += "        ),\n"

    php_code += "    );\n\n    return $groups_cache;\n}\n"

    with open(MAIN_GROUPS_PHP, 'w', encoding='utf-8') as f:
        f.write(php_code)

def main():
    # Prüfe, ob die groups.csv existiert
    if not os.path.exists(GROUPS_CSV_PATH):
        print(f"Fehler: Die Datei {GROUPS_CSV_PATH} wurde nicht gefunden.")
        return

    try:
        # Lade die CSV-Datei und ersetze NaN-Werte durch leere Strings
        groups_df = pd.read_csv(GROUPS_CSV_PATH, delimiter=';', dtype=str).fillna('')
    except Exception as e:
        print(f"Fehler beim Laden der CSV-Datei: {e}")
        return

    # Überprüfe, ob alle erforderlichen Spalten vorhanden sind
    required_columns = {'group', 'order', 'label', 'class', 'description', 'description file'}
    if not required_columns.issubset(groups_df.columns):
        missing = required_columns - set(groups_df.columns)
        print(f"Fehler: Fehlende Spalten in CSV-Datei: {', '.join(missing)}")
        return

    # Konvertiere die Daten in eine Liste von Dictionaries
    groups = groups_df.to_dict(orient='records')

    # Generiere die Haupt-PHP-Datei
    generate_main_groups_php(groups)
    print(f"Gruppen erfolgreich generiert in {MAIN_GROUPS_PHP}")

if __name__ == "__main__":
    main()
