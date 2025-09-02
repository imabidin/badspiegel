# 🚀 Badspiegel WordPress Scripts

Automatisierte Scripts für WordPress-Import, Backup und Wartung in Docker-Umgebung.

## 📁 Script-Übersicht

### 🎯 Import-Scripts

- **`complete-import.sh`** - Vollständiger Kinsta-Import (Files + Database)
- **`import-files.sh`** - Nur WordPress-Dateien importieren
- **`import-database.sh`** - Nur Datenbank importieren

### 💾 Backup-Scripts

- **`create-backup.sh`** - Erstellt umfassende Backups
- **`restore-backup.sh`** - Stellt Backups wieder her

### 🧪 Test & Fix Scripts

- **`test-wordpress.sh`** - Vollständiger WordPress-Funktionstest
- **`fix-wordpress.sh`** - Automatische Problem-Behebung

## 🎯 Häufige Anwendungsfälle

### Erstmaliger Kinsta-Import

```bash
# Vollständiger Import (empfohlen)
./scripts/complete-import.sh

# Oder einzeln:
./scripts/import-files.sh
./scripts/import-database.sh
```

### Backup erstellen

```bash
# Vor wichtigen Änderungen
./scripts/create-backup.sh --pre-update

# Tägliches Backup
./scripts/create-backup.sh --daily

# Mit Beschreibung
./scripts/create-backup.sh --description "Vor Plugin-Update"
```

### WordPress-Probleme beheben

```bash
# Schnelle Diagnose
./scripts/test-wordpress.sh

# Automatische Reparatur
./scripts/fix-wordpress.sh
```

### Backup wiederherstellen

```bash
# Verfügbare Backups anzeigen
./scripts/restore-backup.sh

# Bestimmtes Backup wiederherstellen
./scripts/restore-backup.sh backups/backup_20250902_165334
```

## 🔧 Was die Scripts automatisch beheben

### import-files.sh Verbesserungen:

- ✅ Container werden automatisch gestoppt für sauberen Import
- ✅ Berechtigungen werden korrekt gesetzt (www-data:www-data)
- ✅ Docker-kompatible wp-config.php wird automatisch erstellt
- ✅ Container werden nach Import neu gestartet
- ✅ Funktionstest am Ende

### complete-import.sh Verbesserungen:

- ✅ Automatischer WordPress-Funktionstest nach Import
- ✅ Detaillierte Fehler-Diagnose bei Problemen
- ✅ HTTP-Status-Prüfung

### Neue Scripts:

- ✅ **test-wordpress.sh** - Umfassende Diagnose
- ✅ **fix-wordpress.sh** - Automatische Problemlösung

## 🚨 Häufige Probleme & Lösungen

### Leere WordPress-Seite

```bash
./scripts/fix-wordpress.sh
```

### HTTP 500 Fehler

```bash
# Diagnose
./scripts/test-wordpress.sh

# Reparatur
./scripts/fix-wordpress.sh
```

### Permission Denied Fehler

```bash
# Scripts sind automatisch mit sudo erweitert
# Berechtigungen werden automatisch korrekt gesetzt
```

### Falsche Datenbank-Verbindung

```bash
# wp-config.php wird automatisch für Docker erstellt
./scripts/fix-wordpress.sh
```

## 📊 Script-Parameter

### create-backup.sh

- `--pre-import` - Backup vor Import
- `--daily` - Tägliches Backup
- `--pre-update` - Backup vor Updates
- `--description "Text"` - Benutzerdefinierte Beschreibung

### restore-backup.sh

- `--force` - Ohne Bestätigung wiederherstellen

## 🎯 Workflow-Empfehlungen

### Neuer Import

1. `./scripts/create-backup.sh --pre-import`
2. `./scripts/complete-import.sh`
3. `./scripts/test-wordpress.sh`

### Nach Problemen

1. `./scripts/test-wordpress.sh` (Diagnose)
2. `./scripts/fix-wordpress.sh` (Reparatur)
3. `./scripts/test-wordpress.sh` (Verifikation)

### Regelmäßige Wartung

1. `./scripts/create-backup.sh --daily` (täglich)
2. `./scripts/test-wordpress.sh` (wöchentlich)

## 🔗 URLs nach erfolgreichem Import

- **WordPress Frontend:** https://www.badspiegel.local
- **WordPress Admin:** https://www.badspiegel.local/wp-admin
- **phpMyAdmin:** https://db.badspiegel.local

## 💡 Tipps

- Alle Scripts loggen ihre Aktionen ausführlich
- Bei SSL-Zertifikat-Warnungen: "Erweitert" → "Trotzdem fortfahren"
- Scripts können beliebig oft ausgeführt werden
- Backups werden automatisch bereinigt (behalten nur die letzten 5-8)
