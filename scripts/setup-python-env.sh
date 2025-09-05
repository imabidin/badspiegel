#!/bin/bash

# Python Environment Setup Script for Badspiegel Project
# This script ensures portable Python dependencies across different systems

set -e  # Exit on any error

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VENV_PATH="$PROJECT_ROOT/.venv"

echo "🐍 Setting up Python environment for Badspiegel project..."

# Check if Python 3 is available
if ! command -v python3 &> /dev/null; then
    echo "❌ Error: Python 3 is not installed. Please install Python 3.8+ first."
    exit 1
fi

# Check Python version (minimum 3.8)
PYTHON_VERSION=$(python3 -c "import sys; print('.'.join(map(str, sys.version_info[:2])))")
REQUIRED_VERSION="3.8"

if [ "$(printf '%s\n' "$REQUIRED_VERSION" "$PYTHON_VERSION" | sort -V | head -n1)" != "$REQUIRED_VERSION" ]; then
    echo "❌ Error: Python $PYTHON_VERSION found, but Python $REQUIRED_VERSION+ is required."
    exit 1
fi

echo "✅ Python $PYTHON_VERSION found"

# Create virtual environment if it doesn't exist
if [ ! -d "$VENV_PATH" ]; then
    echo "📦 Creating virtual environment..."
    python3 -m venv "$VENV_PATH"
else
    echo "📦 Virtual environment already exists"
fi

# Activate virtual environment
echo "🔧 Activating virtual environment..."
source "$VENV_PATH/bin/activate"

# Upgrade pip
echo "⬆️  Upgrading pip..."
pip install --upgrade pip

# Install requirements
if [ -f "$PROJECT_ROOT/requirements.txt" ]; then
    echo "📋 Installing Python dependencies from requirements.txt..."
    pip install -r "$PROJECT_ROOT/requirements.txt"
else
    echo "⚠️  No requirements.txt found, installing basic packages..."
    pip install pandas numpy openpyxl
fi

echo "✅ Python environment setup complete!"
echo ""
echo "To activate the environment manually, run:"
echo "  source $VENV_PATH/bin/activate"
echo ""
echo "To deactivate, run:"
echo "  deactivate"
