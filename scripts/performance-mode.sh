#!/bin/bash

# Performance Mode Toggle Script für WordPress Development
# Autor: BadSpiegel Development
# Version: 1.0

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Hilfsfunktionen
info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

success() {
    echo -e "${GREEN}✓${NC} $1"
}

warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

error() {
    echo -e "${RED}✗${NC} $1"
}

# Performance-Modus aktivieren
enable_performance_mode() {
    info "Aktiviere Performance-Modus..."

    # OPcache aktivieren (bereits gemacht)
    success "OPcache ist aktiviert"

    # Xdebug deaktivieren
    sed -i 's/xdebug.mode=debug/xdebug.mode=off/' "$PROJECT_ROOT/config/php/xdebug.ini"
    success "Xdebug deaktiviert"

    # Redis Memory erhöhen
    export REDIS_MAXMEMORY=512mb

    # Container neu starten
    cd "$PROJECT_ROOT"
    docker compose restart wordpress redis

    success "Performance-Modus aktiviert! 🚀"
    warning "Für Debugging verwende: ./scripts/performance-mode.sh debug"
}

# Debug-Modus aktivieren
enable_debug_mode() {
    info "Aktiviere Debug-Modus..."

    # Xdebug aktivieren
    sed -i 's/xdebug.mode=off/xdebug.mode=debug/' "$PROJECT_ROOT/config/php/xdebug.ini"
    success "Xdebug aktiviert"

    # Container neu starten
    cd "$PROJECT_ROOT"
    docker compose restart wordpress

    success "Debug-Modus aktiviert! 🐛"
    warning "Für Performance verwende: ./scripts/performance-mode.sh performance"
}

# Status anzeigen
show_status() {
    info "Aktueller Status:"

    # OPcache Status
    opcache_status=$(grep "opcache.enable = " "$PROJECT_ROOT/config/php/php.ini" | cut -d'=' -f2 | tr -d ' ')
    if [[ "$opcache_status" == "1" ]]; then
        success "OPcache: Aktiviert"
    else
        warning "OPcache: Deaktiviert"
    fi

    # Xdebug Status
    xdebug_status=$(grep "xdebug.mode=" "$PROJECT_ROOT/config/php/xdebug.ini" | cut -d'=' -f2)
    if [[ "$xdebug_status" == "debug" ]]; then
        warning "Xdebug: Aktiviert (Debug-Modus)"
    else
        success "Xdebug: Deaktiviert (Performance-Modus)"
    fi

    # Docker Stats
    info "Docker Container Ressourcen:"
    docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}" | head -7
}

# Haupt-Logik
case "${1:-status}" in
    "performance")
        enable_performance_mode
        ;;
    "debug")
        enable_debug_mode
        ;;
    "status"|"")
        show_status
        ;;
    "help"|"-h"|"--help")
        echo "WordPress Performance Mode Controller"
        echo ""
        echo "Usage: $0 [COMMAND]"
        echo ""
        echo "Commands:"
        echo "  performance  Aktiviert Performance-Modus (Xdebug aus, OPcache an)"
        echo "  debug        Aktiviert Debug-Modus (Xdebug an)"
        echo "  status       Zeigt aktuellen Status (Standard)"
        echo "  help         Zeigt diese Hilfe"
        ;;
    *)
        error "Unbekannter Befehl: $1"
        echo "Verwende '$0 help' für Hilfe"
        exit 1
        ;;
esac
