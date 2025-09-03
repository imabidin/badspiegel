#!/bin/bash

# OPcache Management Script für WordPress Development
# Autor: BadSpiegel Development

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Farben
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

info() { echo -e "${BLUE}ℹ${NC} $1"; }
success() { echo -e "${GREEN}✓${NC} $1"; }
warning() { echo -e "${YELLOW}⚠${NC} $1"; }

# OPcache Status anzeigen
show_status() {
    info "OPcache Status:"
    curl -s http://localhost/opcache-test.php || echo "Website nicht erreichbar"
}

# OPcache komplett leeren
clear_cache() {
    info "Leere OPcache komplett..."

    # Via WordPress
    docker exec wordpress-app php -r "if(function_exists('opcache_reset')) { opcache_reset(); echo 'Cache geleert\n'; } else { echo 'OPcache nicht verfügbar\n'; }"

    # Container restart als Backup
    warning "Starte WordPress Container neu für vollständige Cache-Löschung..."
    cd "$PROJECT_ROOT"
    docker compose restart wordpress

    success "OPcache komplett geleert!"
}

# Development-Modus (häufigere Updates)
dev_mode() {
    info "Aktiviere Development-Modus (1s revalidate)..."

    # Temporär auf 1 Sekunde setzen
    sed -i 's/opcache.revalidate_freq = 2/opcache.revalidate_freq = 1/' "$PROJECT_ROOT/config/php/php.ini"

    cd "$PROJECT_ROOT"
    docker compose restart wordpress

    success "Development-Modus aktiviert (1s Cache-Check)"
    warning "Für Production: ./scripts/opcache-control.sh production"
}

# Production-Modus (weniger häufige Updates)
prod_mode() {
    info "Aktiviere Production-Modus (60s revalidate)..."

    sed -i 's/opcache.revalidate_freq = [0-9]*/opcache.revalidate_freq = 60/' "$PROJECT_ROOT/config/php/php.ini"

    cd "$PROJECT_ROOT"
    docker compose restart wordpress

    success "Production-Modus aktiviert (60s Cache-Check)"
}

# Standard Development-Modus wiederherstellen
reset_mode() {
    info "Setze Standard Development-Modus (2s revalidate)..."

    sed -i 's/opcache.revalidate_freq = [0-9]*/opcache.revalidate_freq = 2/' "$PROJECT_ROOT/config/php/php.ini"

    cd "$PROJECT_ROOT"
    docker compose restart wordpress

    success "Standard Development-Modus wiederhergestellt"
}

# Haupt-Logik
case "${1:-status}" in
    "status"|"")
        show_status
        ;;
    "clear"|"reset")
        clear_cache
        ;;
    "dev"|"development")
        dev_mode
        ;;
    "prod"|"production")
        prod_mode
        ;;
    "restore"|"default")
        reset_mode
        ;;
    "help"|"-h"|"--help")
        echo "OPcache Control Script"
        echo ""
        echo "Usage: $0 [COMMAND]"
        echo ""
        echo "Commands:"
        echo "  status       Zeigt OPcache Status (Standard)"
        echo "  clear        Leert OPcache komplett"
        echo "  dev          Development-Modus (1s revalidate)"
        echo "  prod         Production-Modus (60s revalidate)"
        echo "  restore      Standard-Modus (2s revalidate)"
        echo "  help         Zeigt diese Hilfe"
        ;;
    *)
        error "Unbekannter Befehl: $1"
        echo "Verwende '$0 help' für Hilfe"
        exit 1
        ;;
esac
