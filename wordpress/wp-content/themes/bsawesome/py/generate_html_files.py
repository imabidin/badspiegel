import os

# Absoluter Pfad zur Datei mit der Liste
input_file = "Z:/data/docker/volumes/DevKinsta/public/badspiegel/wp-content/themes/bsawesome/html/configurator/files to create.txt"
# Zielordner für die HTML-Dateien
output_dir = "Z:/data/docker/volumes/DevKinsta/public/badspiegel/wp-content/themes/bsawesome/html/configurator/"

# Sicherstellen, dass der Zielordner existiert
os.makedirs(output_dir, exist_ok=True)

# Datei lesen und HTML-Dateien erstellen
try:
    with open(input_file, "r", encoding="utf-8") as file:
        for line in file:
            filename = line.strip()
            if filename:  # Leere Zeilen überspringen
                html_file_path = os.path.join(output_dir, f"{filename}.html")
                if os.path.exists(html_file_path):
                    print(f"Überspringe: {html_file_path} existiert bereits.")
                    continue
                try:
                    with open(html_file_path, "w", encoding="utf-8") as html_file:
                        html_file.write(f"<div class=\"d-inline-block border mb-last-0\">\n    <?php echo do_shortcode('[img id=\"\" size=\"thumbnail\"]'); ?>\n</div>")
                    print(f"Erstellt: {html_file_path}")
                except Exception as e:
                    print(f"Fehler beim Erstellen von {html_file_path}: {e}")
except FileNotFoundError:
    print(f"Die Datei {input_file} wurde nicht gefunden. Bitte überprüfen Sie den Pfad.")

print("HTML-Dateien wurden erfolgreich erstellt.")
