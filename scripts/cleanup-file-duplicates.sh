#!/bin/bash

# Script zur Bereinigung physischer Datei-Duplikate im WordPress Upload-Verzeichnis
# Findet und entfernt echte Duplikate basierend auf MD5-Hashes

set -e

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}===========================================${NC}"
echo -e "${BLUE}  Physische Datei-Duplikate Bereinigung  ${NC}"
echo -e "${BLUE}===========================================${NC}"
echo

UPLOAD_DIR="wordpress/wp-content/uploads"
BACKUP_DIR="backups/file_cleanup_$(date +%Y%m%d_%H%M%S)"
TEMP_DIR="/tmp/duplicate_analysis"

# Backup-Verzeichnis erstellen
mkdir -p "$BACKUP_DIR"
mkdir -p "$TEMP_DIR"

echo -e "${YELLOW}📊 Analysiere physische Dateien...${NC}"

# Zähle alle Dateien
TOTAL_FILES=$(find "$UPLOAD_DIR" -type f \( -name "*.jpg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | wc -l)
echo "📁 Gesamte Bilddateien: $TOTAL_FILES"

# Finde nur Original-Dateien (keine Thumbnails)
ORIGINAL_FILES=$(find "$UPLOAD_DIR" -type f \( -name "*.jpg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) -not -name "*-[0-9]*x[0-9]*.*" | wc -l)
echo "🖼️  Original-Dateien (ohne Thumbnails): $ORIGINAL_FILES"

echo
echo -e "${YELLOW}🔍 Suche nach echten Duplikaten (identische MD5-Hashes)...${NC}"

# Erstelle MD5-Hash Liste für Original-Dateien
echo "   - Erstelle Hash-Liste..."
find "$UPLOAD_DIR" -type f \( -name "*.jpg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) -not -name "*-[0-9]*x[0-9]*.*" -exec md5sum {} \; > "$TEMP_DIR/all_hashes.txt"

# Finde doppelte Hashes
echo "   - Analysiere Duplikate..."
cut -d' ' -f1 "$TEMP_DIR/all_hashes.txt" | sort | uniq -d > "$TEMP_DIR/duplicate_hashes.txt"

DUPLICATE_HASH_COUNT=$(wc -l < "$TEMP_DIR/duplicate_hashes.txt")
echo "🔄 Gefundene doppelte Hash-Werte: $DUPLICATE_HASH_COUNT"

if [ "$DUPLICATE_HASH_COUNT" -eq 0 ]; then
    echo -e "${GREEN}✅ Keine echten Datei-Duplikate gefunden!${NC}"
    echo "Alle Dateien sind eindeutig basierend auf ihrem Inhalt."
    rm -rf "$TEMP_DIR"
    exit 0
fi

# Detailanalyse der Duplikate
echo
echo -e "${CYAN}📋 Duplikat-Details:${NC}"
echo "Hash                             | Anzahl | Beispiel-Dateien"
echo "================================ | ====== | ==============="

while read -r hash; do
    files=($(grep "^$hash" "$TEMP_DIR/all_hashes.txt" | cut -d' ' -f2-))
    count=${#files[@]}
    example_file=$(basename "${files[0]}")
    printf "%-32s | %-6s | %s" "$hash" "$count" "$example_file"
    if [ $count -gt 2 ]; then
        printf " (+%d weitere)" $((count-1))
    fi
    echo
    
    # Speichere Duplikat-Info für später
    echo "$hash:${files[*]}" >> "$TEMP_DIR/duplicate_details.txt"
done < "$TEMP_DIR/duplicate_hashes.txt"

# Berechne Speicherplatz-Einsparung
echo
echo -e "${YELLOW}💾 Berechne potentielle Speicherplatz-Einsparung...${NC}"

TOTAL_DUPLICATE_SIZE=0
TOTAL_SAVINGS=0

while read -r line; do
    hash=$(echo "$line" | cut -d':' -f1)
    files_str=$(echo "$line" | cut -d':' -f2-)
    files=($files_str)
    
    # Größe der ersten Datei (die behalten wird)
    first_file_size=$(stat -c%s "${files[0]}" 2>/dev/null || echo 0)
    
    # Größe aller Duplikate (die gelöscht werden)
    duplicate_size=0
    for ((i=1; i<${#files[@]}; i++)); do
        size=$(stat -c%s "${files[i]}" 2>/dev/null || echo 0)
        duplicate_size=$((duplicate_size + size))
    done
    
    TOTAL_DUPLICATE_SIZE=$((TOTAL_DUPLICATE_SIZE + duplicate_size))
done < "$TEMP_DIR/duplicate_details.txt"

SAVINGS_MB=$((TOTAL_DUPLICATE_SIZE / 1024 / 1024))
echo "💰 Potentielle Einsparung: ${SAVINGS_MB} MB durch Entfernung von Duplikaten"

# Erstelle Backup der zu löschenden Dateien
echo
echo -e "${YELLOW}📦 Erstelle Backup der Duplikate...${NC}"
mkdir -p "$BACKUP_DIR/removed_files"

# Liste der zu löschenden Dateien
echo "Folgende Dateien werden entfernt (Duplikate):" > "$BACKUP_DIR/removed_files_list.txt"

echo
echo -e "${CYAN}📋 Dateien die entfernt werden:${NC}"
while read -r line; do
    files_str=$(echo "$line" | cut -d':' -f2-)
    files=($files_str)
    
    # Zeige welche Datei behalten wird
    keep_file=$(basename "${files[0]}")
    echo "   📁 Behalte: $keep_file"
    
    # Zeige welche Dateien entfernt werden
    for ((i=1; i<${#files[@]}; i++)); do
        remove_file="${files[i]}"
        remove_basename=$(basename "$remove_file")
        echo "   🗑️  Entferne: $remove_basename"
        echo "$remove_file" >> "$BACKUP_DIR/removed_files_list.txt"
        
        # Kopiere zu löschende Datei ins Backup
        cp "$remove_file" "$BACKUP_DIR/removed_files/" 2>/dev/null || echo "   ⚠️  Warnung: Konnte $remove_file nicht sichern"
    done
    echo
done < "$TEMP_DIR/duplicate_details.txt"

echo
read -p "Möchtest du die Duplikate wirklich entfernen? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}❌ Bereinigung abgebrochen.${NC}"
    rm -rf "$TEMP_DIR"
    exit 1
fi

echo -e "${YELLOW}🗑️  Entferne Duplikate...${NC}"

REMOVED_COUNT=0
REMOVED_SIZE=0

while read -r line; do
    files_str=$(echo "$line" | cut -d':' -f2-)
    files=($files_str)
    
    # Entferne alle Duplikate (außer dem ersten)
    for ((i=1; i<${#files[@]}; i++)); do
        remove_file="${files[i]}"
        if [ -f "$remove_file" ]; then
            file_size=$(stat -c%s "$remove_file" 2>/dev/null || echo 0)
            rm "$remove_file"
            REMOVED_COUNT=$((REMOVED_COUNT + 1))
            REMOVED_SIZE=$((REMOVED_SIZE + file_size))
            echo "   ✅ Entfernt: $(basename "$remove_file")"
            
            # Entferne auch zugehörige Thumbnails
            base_name=$(basename "$remove_file" | sed 's/\.[^.]*$//')
            dir_name=$(dirname "$remove_file")
            find "$dir_name" -name "${base_name}-[0-9]*x[0-9]*.*" -delete 2>/dev/null
        fi
    done
done < "$TEMP_DIR/duplicate_details.txt"

# Bereinige verwaiste Thumbnails
echo
echo -e "${YELLOW}🧹 Bereinige verwaiste Thumbnails...${NC}"
ORPHANED_THUMBNAILS=0

# Finde Thumbnails deren Original-Datei nicht mehr existiert
find "$UPLOAD_DIR" -name "*-[0-9]*x[0-9]*.*" | while read -r thumbnail; do
    # Extrahiere Original-Dateiname
    base_name=$(basename "$thumbnail" | sed 's/-[0-9]*x[0-9]*\././')
    dir_name=$(dirname "$thumbnail")
    original_file="$dir_name/$base_name"
    
    if [ ! -f "$original_file" ]; then
        rm "$thumbnail" 2>/dev/null
        ORPHANED_THUMBNAILS=$((ORPHANED_THUMBNAILS + 1))
    fi
done

echo "   ✅ $ORPHANED_THUMBNAILS verwaiste Thumbnails entfernt"

# Finale Statistiken
FINAL_SAVINGS_MB=$((REMOVED_SIZE / 1024 / 1024))

echo
echo -e "${GREEN}🎉 Datei-Bereinigung abgeschlossen!${NC}"
echo
echo -e "${CYAN}📊 Zusammenfassung:${NC}"
echo "   🗑️  Entfernte Duplikate: $REMOVED_COUNT Dateien"
echo "   💾 Eingesparter Speicherplatz: ${FINAL_SAVINGS_MB} MB"
echo "   📦 Backup erstellt in: $BACKUP_DIR"
echo "   🧹 Verwaiste Thumbnails bereinigt: $ORPHANED_THUMBNAILS"

# Neue Datei-Statistiken
FINAL_ORIGINAL_FILES=$(find "$UPLOAD_DIR" -type f \( -name "*.jpg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) -not -name "*-[0-9]*x[0-9]*.*" | wc -l)
FINAL_TOTAL_FILES=$(find "$UPLOAD_DIR" -type f \( -name "*.jpg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | wc -l)

echo
echo -e "${CYAN}📈 Neue Datei-Statistiken:${NC}"
echo "   📁 Original-Dateien: $FINAL_ORIGINAL_FILES (vorher: $ORIGINAL_FILES)"
echo "   📁 Gesamte Dateien: $FINAL_TOTAL_FILES (vorher: $TOTAL_FILES)"

# Backup-Info erstellen
cat > "$BACKUP_DIR/cleanup_info.txt" << EOF
Physische Datei-Duplikate Bereinigung
=====================================
Datum: $(date)
Verzeichnis: $UPLOAD_DIR

Entfernte Duplikate: $REMOVED_COUNT Dateien
Eingesparter Speicherplatz: ${FINAL_SAVINGS_MB} MB
Verwaiste Thumbnails bereinigt: $ORPHANED_THUMBNAILS

Vorher:
- Original-Dateien: $ORIGINAL_FILES
- Gesamte Dateien: $TOTAL_FILES

Nachher:
- Original-Dateien: $FINAL_ORIGINAL_FILES
- Gesamte Dateien: $FINAL_TOTAL_FILES

Backup-Verzeichnis: $BACKUP_DIR
Liste der entfernten Dateien: removed_files_list.txt
EOF

echo
echo -e "${YELLOW}💡 Empfehlungen:${NC}"
echo "   - WordPress-Cache leeren"
echo "   - Website auf fehlende Bilder prüfen"
echo "   - Backup-Verzeichnis aufbewahren: $BACKUP_DIR"

# Aufräumen
rm -rf "$TEMP_DIR"

echo -e "${GREEN}✅ Bereinigung erfolgreich abgeschlossen!${NC}"
