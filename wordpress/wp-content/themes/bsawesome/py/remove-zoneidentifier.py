import os

# Definiere den Pfad
path = r'Z:\data\docker\volumes\DevKinsta\public\badspiegel\wp-content\themes\bsawesome'

# Durchlaufe alle Dateien im Verzeichnis
for root, dirs, files in os.walk(path):
    for file in files:
        if "Zone.Identifier" in file:
            file_path = os.path.join(root, file)
            try:
                os.remove(file_path)
                print(f'Datei gelöscht: {file_path}')
            except Exception as e:
                print(f'Fehler beim Löschen der Datei {file_path}: {e}')
