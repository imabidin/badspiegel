import os
import shutil

# Verzeichnis mit den HTML-Dateien
html_directory = '.'

# Alle Dateien im Verzeichnis durchgehen
for filename in os.listdir(html_directory):
    # Nur HTML-Dateien ohne _de oder _en im Namen verarbeiten
    if filename.endswith('.html') and not ('_de.html' in filename or '_en.html' in filename):
        # Basisname ohne Erweiterung
        basename, _ = os.path.splitext(filename)

        # Neue Dateinamen mit Suffixen
        de_file = f"{basename}_de.html"
        en_file = f"{basename}_en.html"

        # Originaldatei zu _de.html umbenennen
        os.rename(os.path.join(html_directory, filename), os.path.join(html_directory, de_file))

        # _de.html als _en.html kopieren
        shutil.copy(os.path.join(html_directory, de_file), os.path.join(html_directory, en_file))
