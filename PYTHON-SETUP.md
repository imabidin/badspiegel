# Python Environment Setup - Badspiegel Project

Dieses Projekt verwendet Python für Datenverarbeitung und Automatisierung. Das Python-Environment ist vollständig in das Docker-Setup integriert.

## 🚀 Schnellstart

### Methode 1: Docker Integration (Empfohlen)
```bash
# Container bauen
docker-compose build python-tools

# Python-Shell starten
./scripts/python-tools.sh shell

# Python-Skript ausführen
./scripts/python-tools.sh run my_script.py

# Jupyter Notebook starten
./scripts/python-tools.sh jupyter
```

### Methode 2: Lokale Entwicklung
```bash
./scripts/setup-python-env.sh
source .venv/bin/activate
```

## 🐳 Docker-Integration

Der Python-Container ist vollständig in das Badspiegel-Ecosystem integriert:

- **Zugriff auf WordPress-Dateien** (`/app/wordpress`)
- **Datenbankverbindung** (über `db` Service)
- **Shared Volumes** für Scripts, Logs, Backups
- **Gleiche Netzwerk** wie alle anderen Services

### Verfügbare Services:
```bash
# Normale Services starten
docker-compose up -d

# Python-Tools nutzen
docker-compose run --rm python-tools python your_script.py

# Oder mit dem Wrapper:
./scripts/python-tools.sh run your_script.py
```

## 📦 Installierte Pakete

- **pandas** (2.3.2) - Datenmanipulation und -analyse
- **numpy** (2.3.2) - Numerische Berechnungen
- **openpyxl** (3.1.5) - Excel-Dateien lesen/schreiben
- **python-dateutil** - Erweiterte Datums-/Zeitfunktionen

## 🔧 Verwendung

### Umgebung aktivieren
```bash
source .venv/bin/activate
```

### Skript ausführen
```bash
python your_script.py
```

### Umgebung deaktivieren
```bash
deactivate
```

## 📋 Neue Abhängigkeiten hinzufügen

1. Umgebung aktivieren
2. Paket installieren: `pip install package_name`
3. Requirements aktualisieren: `pip freeze > requirements.txt`

## 🐳 Docker Integration

Für maximale Portabilität kann die Python-Umgebung auch in deine bestehende Docker-Compose-Setup integriert werden:

```yaml
  python-tools:
    build:
      context: .
      dockerfile: Dockerfile.python
    volumes:
      - ./scripts:/app/scripts
      - ./data:/app/data
    networks:
      - badspiegel_network
```

## ⚠️ Systemanforderungen

- Python 3.8 oder höher
- pip (normalerweise mit Python installiert)
- Für Docker: Docker & Docker Compose

## 🔍 Troubleshooting

### "python3: command not found"
```bash
# Ubuntu/Debian
sudo apt update && sudo apt install python3 python3-pip python3-venv

# CentOS/RHEL
sudo yum install python3 python3-pip
```

### Berechtigungsprobleme
```bash
chmod +x scripts/setup-python-env.sh
```

### Virtual Environment Probleme
```bash
# Umgebung komplett neu erstellen
rm -rf .venv
./scripts/setup-python-env.sh
```
