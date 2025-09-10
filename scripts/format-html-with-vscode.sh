#!/bin/bash

# Script to format all HTML files using VS Code settings
# This uses VS Code's built-in formatter with the settings.json configuration

echo "🔧 Formatting HTML files with VS Code..."

# Find all HTML files in the theme directory (excluding node_modules)
html_files=$(find /home/imabidin/badspiegel/wordpress/wp-content/themes/bsawesome -name "*.html" -not -path "*/node_modules/*")

total_files=$(echo "$html_files" | wc -l)
echo "📁 Found $total_files HTML files to format"

count=0
for file in $html_files; do
    count=$((count + 1))
    echo "[$count/$total_files] Formatting: $(basename "$file")"

    # Use VS Code CLI to format the file
    # This applies the settings from .vscode/settings.json
    code --wait "$file" --command "editor.action.formatDocument" 2>/dev/null || {
        echo "⚠️  Could not format $file with VS Code command"
        echo "💡 You can manually format by opening the file in VS Code and pressing Shift+Alt+F"
    }
done

echo ""
echo "✨ HTML formatting complete!"
echo "📋 Next steps:"
echo "   1. Open any HTML file in VS Code"
echo "   2. Press Shift+Alt+F to format"
echo "   3. Or enable 'editor.formatOnSave' in settings for automatic formatting"
echo ""
echo "🔧 Current settings in .vscode/settings.json:"
echo "   - HTML files use 4 spaces for indentation"
echo "   - Format on save enabled for HTML"
echo "   - Preserve newlines and proper HTML structure"
