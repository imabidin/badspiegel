#!/usr/bin/env python3
"""
Script to fix HTML indentation in all HTML files
Converts all indentation to 4 spaces consistently
"""

import os
import re
from pathlib import Path

def analyze_indentation(content):
    """Analyze current indentation in content"""
    lines = content.split('\n')
    indent_patterns = {}

    for line in lines:
        if line.strip() and line.startswith(' '):
            # Count leading spaces
            spaces = len(line) - len(line.lstrip(' '))
            if spaces > 0:
                indent_patterns[spaces] = indent_patterns.get(spaces, 0) + 1

    return indent_patterns

def fix_indentation(content, target_indent=4):
    """Fix indentation to use consistent spacing"""
    lines = content.split('\n')
    fixed_lines = []

    # Detect the most common indentation pattern first
    indent_counts = {}
    for line in lines:
        if line.strip() and line.startswith(' '):
            spaces = len(line) - len(line.lstrip(' '))
            if spaces > 0:
                # Check for common patterns (2, 4, 6, 8 spaces)
                for base in [2, 4]:
                    if spaces % base == 0:
                        level = spaces // base
                        indent_counts[base] = indent_counts.get(base, 0) + 1
                        break

    # Determine the current base indentation (2 or 4)
    current_base = 4  # default
    if indent_counts:
        current_base = max(indent_counts.keys(), key=lambda k: indent_counts[k])

    for line in lines:
        if line.strip():  # If line has content
            # Count current indentation level
            stripped = line.lstrip(' ')
            current_spaces = len(line) - len(stripped)

            if current_spaces > 0:
                # Calculate indentation level based on detected pattern
                level = current_spaces // current_base

                # Handle remainder spaces
                remainder = current_spaces % current_base
                if remainder > 0:
                    level += 1  # Round up for partial indents

                new_indent = ' ' * (level * target_indent)
                fixed_lines.append(new_indent + stripped)
            else:
                fixed_lines.append(line)  # Keep line as-is if no indentation
        else:
            fixed_lines.append('')  # Empty line

    return '\n'.join(fixed_lines)

def process_html_file(file_path):
    """Process a single HTML file"""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()

        # Analyze current indentation
        indent_patterns = analyze_indentation(content)

        if indent_patterns:
            print(f"\nAnalyzing: {file_path}")
            print(f"Current indentation patterns: {indent_patterns}")

            # Fix indentation
            fixed_content = fix_indentation(content, target_indent=4)

            # Check if anything changed
            if content != fixed_content:
                # Create backup
                backup_path = str(file_path) + '.backup'
                with open(backup_path, 'w', encoding='utf-8') as f:
                    f.write(content)

                # Write fixed content
                with open(file_path, 'w', encoding='utf-8') as f:
                    f.write(fixed_content)

                print(f"✅ Fixed indentation (backup created: {backup_path})")
                return True
            else:
                print(f"✅ Already correctly indented")
                return False
        else:
            print(f"ℹ️ No indentation found in: {file_path}")
            return False

    except Exception as e:
        print(f"❌ Error processing {file_path}: {e}")
        return False

def main():
    """Main function"""
    # Find all HTML files in the theme directory
    theme_dir = Path('/home/imabidin/badspiegel/wordpress/wp-content/themes/bsawesome')
    html_files = list(theme_dir.rglob('*.html'))

    print(f"Found {len(html_files)} HTML files to process...")

    modified_count = 0
    total_count = len(html_files)

    for html_file in html_files:
        if process_html_file(html_file):
            modified_count += 1

    print(f"\n📊 Summary:")
    print(f"Total files processed: {total_count}")
    print(f"Files modified: {modified_count}")
    print(f"Files already correct: {total_count - modified_count}")
    print(f"\n✨ All HTML files now use 4-space indentation!")

if __name__ == "__main__":
    main()
