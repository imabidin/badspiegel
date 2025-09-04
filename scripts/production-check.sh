#!/bin/bash

# BSAwesome Theme - Production Readiness Automation Script
# This script helps automate various checks and improvements for production readiness

echo "=== BSAwesome Theme Production Readiness Check ==="
echo "Date: $(date)"
echo "=============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Theme directory (adjust path as needed)
THEME_DIR="z:/data/docker/volumes/DevKinsta/public/badspiegel/wp-content/themes/bsawesome"

# Function to print colored output
print_status() {
    local status=$1
    local message=$2
    case $status in
        "success") echo -e "${GREEN}✓${NC} $message" ;;
        "warning") echo -e "${YELLOW}⚠${NC} $message" ;;
        "error") echo -e "${RED}✗${NC} $message" ;;
        "info") echo -e "${BLUE}ℹ${NC} $message" ;;
    esac
}

# Function to count files
count_files() {
    local pattern=$1
    local description=$2
    local count=$(find "$THEME_DIR" -name "$pattern" -type f | wc -l)
    print_status "info" "$description: $count files"
    return $count
}

echo
echo "=== File Inventory ==="
count_files "*.php" "PHP files"
count_files "*.css" "CSS files"
count_files "*.js" "JavaScript files"
count_files "*.scss" "SCSS files"

echo
echo "=== Documentation Check ==="

# Check for files without proper headers
echo "Checking PHP file headers..."
php_files_without_headers=0
while IFS= read -r -d '' file; do
    if ! grep -q "@package BSAwesome" "$file"; then
        print_status "warning" "Missing proper header: $(basename "$file")"
        ((php_files_without_headers++))
    fi
done < <(find "$THEME_DIR" -name "*.php" -type f -print0)

if [ $php_files_without_headers -eq 0 ]; then
    print_status "success" "All PHP files have proper headers"
else
    print_status "warning" "$php_files_without_headers PHP files need header updates"
fi

echo
echo "=== Security Check ==="

# Check for common security issues
echo "Checking for security issues..."

# Check for ABSPATH protection
php_files_without_abspath=0
while IFS= read -r -d '' file; do
    if ! grep -q "defined('ABSPATH')" "$file"; then
        print_status "error" "Missing ABSPATH check: $(basename "$file")"
        ((php_files_without_abspath++))
    fi
done < <(find "$THEME_DIR" -name "*.php" -type f -print0)

if [ $php_files_without_abspath -eq 0 ]; then
    print_status "success" "All PHP files have ABSPATH protection"
else
    print_status "error" "$php_files_without_abspath PHP files missing ABSPATH protection"
fi

# Check for unescaped output
echo "Checking for potential unescaped output..."
unescaped_outputs=$(grep -r "echo \$" "$THEME_DIR" --include="*.php" | wc -l)
if [ $unescaped_outputs -gt 0 ]; then
    print_status "warning" "Found $unescaped_outputs potential unescaped outputs - review needed"
else
    print_status "success" "No obvious unescaped outputs found"
fi

# Check for $_POST, $_GET usage without sanitization
unsanitized_inputs=$(grep -r "\$_\(POST\|GET\)" "$THEME_DIR" --include="*.php" | grep -v "sanitize" | wc -l)
if [ $unsanitized_inputs -gt 0 ]; then
    print_status "error" "Found $unsanitized_inputs potential unsanitized inputs"
else
    print_status "success" "No obvious unsanitized inputs found"
fi

echo
echo "=== Text Domain Check ==="

# Check for inconsistent text domains
echo "Checking text domain consistency..."
incorrect_textdomains=$(grep -r "__\|_e\|esc_html__\|esc_attr__" "$THEME_DIR" --include="*.php" | grep -v "'bsawesome'" | grep -E "'[^']+'" | wc -l)
if [ $incorrect_textdomains -gt 0 ]; then
    print_status "warning" "Found $incorrect_textdomains instances of non-'bsawesome' text domains"
    echo "Common incorrect text domains found:"
    grep -r "__\|_e\|esc_html__\|esc_attr__" "$THEME_DIR" --include="*.php" | grep -v "'bsawesome'" | grep -oE "'[^']+'" | sort | uniq -c | sort -nr | head -5
else
    print_status "success" "All translation functions use correct text domain"
fi

echo
echo "=== Function Naming Check ==="

# Check for functions without theme prefix
echo "Checking function naming conventions..."
functions_without_prefix=$(grep -r "^function " "$THEME_DIR" --include="*.php" | grep -v "bsawesome_\|__construct\|__destruct" | wc -l)
if [ $functions_without_prefix -gt 0 ]; then
    print_status "warning" "Found $functions_without_prefix functions without 'bsawesome_' prefix"
else
    print_status "success" "All functions follow naming conventions"
fi

echo
echo "=== German Content Check ==="

# Check for hardcoded German text
echo "Checking for untranslated German content..."
german_content=$(grep -r -i "deutsch\|german\|rabatt\|preis\|versand\|kontakt\|datenschutz\|impressum\|zahlung" "$THEME_DIR" --include="*.php" | grep -v "__\|_e\|esc_html__\|esc_attr__" | wc -l)
if [ $german_content -gt 0 ]; then
    print_status "warning" "Found $german_content instances of potentially hardcoded German content"
else
    print_status "success" "No obvious hardcoded German content found"
fi

echo
echo "=== File Organization Check ==="

# Check for proper file organization
required_dirs=("inc" "assets" "woocommerce" "languages")
for dir in "${required_dirs[@]}"; do
    if [ -d "$THEME_DIR/$dir" ]; then
        print_status "success" "Directory exists: $dir"
    else
        print_status "error" "Missing directory: $dir"
    fi
done

echo
echo "=== WordPress Standards Check ==="

# Check for WordPress function usage
echo "Checking WordPress function usage..."

# Check for deprecated functions (basic check)
deprecated_functions=("mysql_query" "wp_get_http" "get_settings")
for func in "${deprecated_functions[@]}"; do
    count=$(grep -r "$func" "$THEME_DIR" --include="*.php" | wc -l)
    if [ $count -gt 0 ]; then
        print_status "error" "Found deprecated function '$func': $count instances"
    fi
done

echo
echo "=== Performance Check ==="

# Check for potential performance issues
echo "Checking for potential performance issues..."

# Check for queries in loops
queries_in_loops=$(grep -A 10 -B 5 "while\|foreach\|for" "$THEME_DIR" --include="*.php" | grep -E "get_posts\|WP_Query\|query_posts" | wc -l)
if [ $queries_in_loops -gt 0 ]; then
    print_status "warning" "Found $queries_in_loops potential queries in loops"
fi

# Check for missing wp_reset_postdata
missing_reset=$(grep -r "WP_Query\|get_posts" "$THEME_DIR" --include="*.php" | wc -l)
reset_count=$(grep -r "wp_reset_postdata" "$THEME_DIR" --include="*.php" | wc -l)
if [ $missing_reset -gt $reset_count ]; then
    print_status "warning" "Potential missing wp_reset_postdata() calls"
fi

echo
echo "=== Summary ==="
echo "=============================================="

# Generate summary
total_php_files=$(find "$THEME_DIR" -name "*.php" -type f | wc -l)
files_with_proper_headers=$(grep -r "@package BSAwesome" "$THEME_DIR" --include="*.php" | wc -l)
completion_percentage=$((files_with_proper_headers * 100 / total_php_files))

print_status "info" "Documentation completion: $completion_percentage% ($files_with_proper_headers/$total_php_files files)"

if [ $completion_percentage -ge 90 ]; then
    print_status "success" "Theme is nearly ready for production!"
elif [ $completion_percentage -ge 70 ]; then
    print_status "warning" "Good progress, but more work needed"
else
    print_status "error" "Significant work required before production"
fi

echo
echo "=== Next Steps ==="
echo "1. Review and fix any security issues identified above"
echo "2. Complete documentation for remaining PHP files"
echo "3. Standardize text domains to 'bsawesome'"
echo "4. Review function naming conventions"
echo "5. Translate hardcoded German content"
echo "6. Run WordPress Theme Check plugin"
echo "7. Test thoroughly before deployment"

echo
echo "For detailed guidelines, see:"
echo "- PRODUCTION_READINESS_PLAN.md"
echo "- PHP_DOCUMENTATION_STANDARDS.md"

echo
echo "=== Check Complete ==="
echo "Date: $(date)"
echo "=============================================="
