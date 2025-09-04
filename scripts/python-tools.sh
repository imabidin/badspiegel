#!/bin/bash

# Badspiegel Python Tools Wrapper
# Vereinfacht die Verwendung der Python-Tools im Docker-Container

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

usage() {
    echo -e "${BLUE}Badspiegel Python Tools${NC}"
    echo ""
    echo "Usage: $0 [COMMAND]"
    echo ""
    echo "Commands:"
    echo "  build     - Build Python container"
    echo "  shell     - Start interactive Python shell"
    echo "  run SCRIPT - Run a Python script"
    echo "  install PKG - Install Python package"
    echo "  jupyter   - Start Jupyter notebook"
    echo "  help      - Show this help"
    echo ""
    echo "Examples:"
    echo "  $0 run analyze_data.py"
    echo "  $0 shell"
    echo "  $0 install matplotlib"
}

build_container() {
    echo -e "${YELLOW}Building Python container...${NC}"
    cd "$PROJECT_ROOT"
    docker-compose build python-tools
    echo -e "${GREEN}✅ Container built successfully${NC}"
}

start_shell() {
    echo -e "${YELLOW}Starting Python shell...${NC}"
    cd "$PROJECT_ROOT"
    docker-compose run --rm python-tools python
}

run_script() {
    if [ -z "$1" ]; then
        echo -e "${RED}❌ Error: No script specified${NC}"
        echo "Usage: $0 run SCRIPT_NAME"
        exit 1
    fi

    echo -e "${YELLOW}Running Python script: $1${NC}"
    cd "$PROJECT_ROOT"
    docker-compose run --rm python-tools python "$1"
}

install_package() {
    if [ -z "$1" ]; then
        echo -e "${RED}❌ Error: No package specified${NC}"
        echo "Usage: $0 install PACKAGE_NAME"
        exit 1
    fi

    echo -e "${YELLOW}Installing package: $1${NC}"
    cd "$PROJECT_ROOT"

    # Install in container and update requirements.txt
    docker-compose run --rm python-tools pip install "$1"
    echo -e "${GREEN}✅ Package installed${NC}"
    echo -e "${BLUE}💡 To persist this package, add it to requirements.txt${NC}"
}

start_jupyter() {
    echo -e "${YELLOW}Starting Jupyter notebook...${NC}"
    cd "$PROJECT_ROOT"

    # Check if jupyter is installed
    if ! docker-compose run --rm python-tools pip show jupyter >/dev/null 2>&1; then
        echo -e "${YELLOW}Installing Jupyter...${NC}"
        docker-compose run --rm python-tools pip install jupyter
    fi

    echo -e "${GREEN}🚀 Starting Jupyter on http://localhost:8888${NC}"
    docker-compose run --rm -p 8888:8888 python-tools jupyter notebook --ip=0.0.0.0 --port=8888 --no-browser --allow-root
}

# Main command dispatcher
case "${1:-help}" in
    "build")
        build_container
        ;;
    "shell")
        start_shell
        ;;
    "run")
        run_script "$2"
        ;;
    "install")
        install_package "$2"
        ;;
    "jupyter")
        start_jupyter
        ;;
    "help"|"-h"|"--help")
        usage
        ;;
    *)
        echo -e "${RED}❌ Unknown command: $1${NC}"
        echo ""
        usage
        exit 1
        ;;
esac
