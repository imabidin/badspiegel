#!/bin/bash

# Enhanced Media Duplicate Analyzer - Detailed Analysis
# Zeigt exakte Duplikatmuster und physische Dateizuordnung

echo "🔍 DETAILLIERTE DUPLIKAT-ANALYSE"
echo "================================="
echo "Analysiert: $(date)"
echo ""

# Database connection
DB_CONTAINER="wordpress-db"
DB_NAME="wordpress"
DB_USER="root"
DB_PASS="root_password"

# Function to execute SQL
exec_sql() {
    docker-compose exec db mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "$1" 2>/dev/null
}

# Function for detailed duplicate analysis
analyze_detailed_duplicates() {
    local title="$1"
    echo "🔍 Detailanalyse für: '$title'"
    echo "----------------------------------------"
    
    # Get all entries with this title
    local result=$(exec_sql "
        SELECT 
            p.ID,
            p.post_title,
            p.post_name,
            p.post_date,
            pm_file.meta_value as file_path,
            CASE 
                WHEN pm_file.meta_value IS NOT NULL THEN
                    CONCAT('wp-content/uploads/', pm_file.meta_value)
                ELSE 'KEINE DATEI'
            END as full_path
        FROM wp_posts p
        LEFT JOIN wp_postmeta pm_file ON p.ID = pm_file.post_id AND pm_file.meta_key = '_wp_attached_file'
        WHERE p.post_type = 'attachment' 
        AND p.post_title = '$title'
        ORDER BY p.post_date, p.ID;
    ")
    
    echo "$result" | while IFS=$'\t' read -r id post_title post_name post_date file_path full_path; do
        if [[ "$id" != "ID" ]]; then
            # Check if physical file exists
            local file_exists="❌ FEHLT"
            if [[ -f "/home/imabidin/badspiegel/wordpress/$full_path" ]]; then
                file_exists="✅ VORHANDEN"
            fi
            
            echo "  📄 ID: $id | Datum: $post_date | Datei: $file_exists"
            echo "     Post-Name: $post_name"
            echo "     Dateipfad: $file_path"
            echo ""
        fi
    done
    
    # Count physical files
    local base_filename=$(echo "$title" | sed 's/[^a-zA-Z0-9-]/-/g')
    local file_count=$(find /home/imabidin/badspiegel/wordpress/wp-content/uploads -name "*$base_filename*" -type f | wc -l)
    echo "  📊 Physische Dateien gefunden: $file_count"
    echo ""
}

# Function to find problematic duplicates
find_problematic_duplicates() {
    echo "🚨 PROBLEMATISCHE DUPLIKATE"
    echo "============================"
    
    # Get titles with multiple entries but same file path
    local problematic=$(exec_sql "
        SELECT 
            p1.post_title,
            COUNT(*) as total_entries,
            COUNT(DISTINCT pm1.meta_value) as unique_files
        FROM wp_posts p1
        LEFT JOIN wp_postmeta pm1 ON p1.ID = pm1.post_id AND pm1.meta_key = '_wp_attached_file'
        WHERE p1.post_type = 'attachment'
        GROUP BY p1.post_title
        HAVING total_entries > 1 AND unique_files = 1
        ORDER BY total_entries DESC
        LIMIT 20;
    ")
    
    echo "Titel mit mehreren DB-Einträgen aber nur EINER physischen Datei:"
    echo "----------------------------------------------------------------"
    echo "$problematic"
    echo ""
    
    # Get the actual titles for detailed analysis
    local titles=$(exec_sql "
        SELECT DISTINCT p1.post_title
        FROM wp_posts p1
        LEFT JOIN wp_postmeta pm1 ON p1.ID = pm1.post_id AND pm1.meta_key = '_wp_attached_file'
        WHERE p1.post_type = 'attachment'
        GROUP BY p1.post_title
        HAVING COUNT(*) > 1 AND COUNT(DISTINCT pm1.meta_value) = 1
        ORDER BY COUNT(*) DESC
        LIMIT 5;
    " | tail -n +2)
    
    # Detailed analysis for top problematic titles
    echo "📋 DETAILANALYSE der Top 5 Probleme:"
    echo "====================================="
    while IFS= read -r title; do
        if [[ -n "$title" ]]; then
            analyze_detailed_duplicates "$title"
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        fi
    done <<< "$titles"
}

# Function to show file path duplicates
show_file_path_duplicates() {
    echo "📁 DATEIPFAD-DUPLIKATE"
    echo "======================"
    
    local file_duplicates=$(exec_sql "
        SELECT 
            pm.meta_value as file_path,
            COUNT(*) as db_entries,
            GROUP_CONCAT(p.ID ORDER BY p.ID) as all_ids,
            GROUP_CONCAT(p.post_title ORDER BY p.ID SEPARATOR ' | ') as all_titles
        FROM wp_posts p
        JOIN wp_postmeta pm ON p.ID = pm.post_id 
        WHERE p.post_type = 'attachment' 
        AND pm.meta_key = '_wp_attached_file'
        GROUP BY pm.meta_value
        HAVING COUNT(*) > 1
        ORDER BY COUNT(*) DESC
        LIMIT 10;
    ")
    
    echo "Top 10 Dateipfade mit mehreren DB-Einträgen:"
    echo "--------------------------------------------"
    echo "$file_duplicates" | while IFS=$'\t' read -r file_path db_entries all_ids all_titles; do
        if [[ "$file_path" != "file_path" ]]; then
            echo "📄 Datei: $file_path"
            echo "   🔢 DB-Einträge: $db_entries"
            echo "   🆔 IDs: $all_ids"
            echo "   📝 Titel: $all_titles"
            echo ""
        fi
    done
}

# Function to create safe deletion script
create_safe_deletion_script() {
    echo "💾 SICHERE LÖSCHUNGS-SKRIPT GENERIERUNG"
    echo "======================================="
    
    # Get safe duplicates (multiple DB entries, same file)
    local safe_duplicates=$(exec_sql "
        SELECT 
            p2.ID,
            p2.post_title,
            pm2.meta_value as file_path
        FROM wp_posts p1
        JOIN wp_postmeta pm1 ON p1.ID = pm1.post_id 
        JOIN wp_posts p2 ON pm1.meta_value = pm2.meta_value
        JOIN wp_postmeta pm2 ON p2.ID = pm2.post_id
        WHERE p1.post_type = 'attachment' 
        AND p2.post_type = 'attachment'
        AND pm1.meta_key = '_wp_attached_file'
        AND pm2.meta_key = '_wp_attached_file'
        AND p1.ID < p2.ID
        ORDER BY pm1.meta_value, p2.ID;
    ")
    
    # Count safe duplicates
    local safe_count=$(echo "$safe_duplicates" | tail -n +2 | wc -l)
    echo "🗑️  Sichere Duplikate zum Löschen identifiziert: $safe_count"
    
    if [[ $safe_count -gt 0 ]]; then
        echo ""
        echo "Die folgenden DB-Einträge können SICHER gelöscht werden:"
        echo "(Sie zeigen auf dieselben physischen Dateien wie andere Einträge)"
        echo "--------------------------------------------------------"
        echo "$safe_duplicates" | tail -n +2 | while IFS=$'\t' read -r id title file_path; do
            echo "❌ ID $id: $title → $file_path"
        done
    fi
}

# Main execution
echo "🚀 Starte erweiterte Duplikat-Analyse..."
echo ""

# Run detailed analysis
find_problematic_duplicates
show_file_path_duplicates
create_safe_deletion_script

echo ""
echo "✅ Detaillierte Analyse abgeschlossen!"
echo ""
echo "💡 ZUSAMMENFASSUNG:"
echo "- Problematische Duplikate: Mehrere DB-Einträge für eine Datei"
echo "- Dateipfad-Duplikate: Gleiche Datei, verschiedene IDs"
echo "- Sichere Löschung: Nur redundante DB-Einträge entfernen"
echo ""
echo "📋 Nächste Schritte:"
echo "1. Überprüfe die detaillierten Ergebnisse"
echo "2. Führe sichere Bereinigung durch: ./scripts/clean-safe-media-duplicates.sh"
echo "3. Teste Website-Funktionalität nach Bereinigung"
