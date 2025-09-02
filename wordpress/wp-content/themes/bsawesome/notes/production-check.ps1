# BSAwesome Theme - Production Readiness Check (PowerShell)
# This script helps automate various checks and improvements for production readiness

Write-Host "=== BSAwesome Theme Production Readiness Check ===" -ForegroundColor Blue
Write-Host "Date: $(Get-Date)" -ForegroundColor Blue
Write-Host "=============================================="

# Theme directory path
$ThemeDir = "z:\data\docker\volumes\DevKinsta\public\badspiegel\wp-content\themes\bsawesome"

# Function to print colored output
function Write-Status {
    param(
        [string]$Status,
        [string]$Message
    )
    switch ($Status) {
        "success" { Write-Host "✓ $Message" -ForegroundColor Green }
        "warning" { Write-Host "⚠ $Message" -ForegroundColor Yellow }
        "error" { Write-Host "✗ $Message" -ForegroundColor Red }
        "info" { Write-Host "ℹ $Message" -ForegroundColor Cyan }
    }
}

# Function to count files
function Count-Files {
    param(
        [string]$Pattern,
        [string]$Description
    )
    $Count = (Get-ChildItem -Path $ThemeDir -Recurse -Filter $Pattern -File).Count
    Write-Status "info" "$Description : $Count files"
    return $Count
}

Write-Host ""
Write-Host "=== File Inventory ===" -ForegroundColor Blue
Count-Files "*.php" "PHP files"
Count-Files "*.css" "CSS files"
Count-Files "*.js" "JavaScript files"
Count-Files "*.scss" "SCSS files"

Write-Host ""
Write-Host "=== Documentation Check ===" -ForegroundColor Blue

# Check for files without proper headers
Write-Host "Checking PHP file headers..."
$PhpFiles = Get-ChildItem -Path $ThemeDir -Recurse -Filter "*.php" -File
$FilesWithoutHeaders = 0

foreach ($File in $PhpFiles) {
    $Content = Get-Content $File.FullName -Raw
    if ($Content -notmatch "@package BSAwesome") {
        Write-Status "warning" "Missing proper header: $($File.Name)"
        $FilesWithoutHeaders++
    }
}

if ($FilesWithoutHeaders -eq 0) {
    Write-Status "success" "All PHP files have proper headers"
} else {
    Write-Status "warning" "$FilesWithoutHeaders PHP files need header updates"
}

Write-Host ""
Write-Host "=== Security Check ===" -ForegroundColor Blue

# Check for ABSPATH protection
Write-Host "Checking for security issues..."
$FilesWithoutAbspath = 0

foreach ($File in $PhpFiles) {
    $Content = Get-Content $File.FullName -Raw
    if ($Content -notmatch "defined\('ABSPATH'\)") {
        Write-Status "error" "Missing ABSPATH check: $($File.Name)"
        $FilesWithoutAbspath++
    }
}

if ($FilesWithoutAbspath -eq 0) {
    Write-Status "success" "All PHP files have ABSPATH protection"
} else {
    Write-Status "error" "$FilesWithoutAbspath PHP files missing ABSPATH protection"
}

# Check for unescaped output
Write-Host "Checking for potential unescaped output..."
$UnescapedOutputs = 0
foreach ($File in $PhpFiles) {
    $Content = Get-Content $File.FullName
    $UnescapedLines = $Content | Select-String "echo \$" 
    $UnescapedOutputs += $UnescapedLines.Count
}

if ($UnescapedOutputs -gt 0) {
    Write-Status "warning" "Found $UnescapedOutputs potential unescaped outputs - review needed"
} else {
    Write-Status "success" "No obvious unescaped outputs found"
}

# Check for unsanitized inputs
Write-Host "Checking for unsanitized inputs..."
$UnsanitizedInputs = 0
foreach ($File in $PhpFiles) {
    $Content = Get-Content $File.FullName
    $InputLines = $Content | Select-String "\$_(POST|GET)" | Where-Object { $_ -notmatch "sanitize" }
    $UnsanitizedInputs += $InputLines.Count
}

if ($UnsanitizedInputs -gt 0) {
    Write-Status "error" "Found $UnsanitizedInputs potential unsanitized inputs"
} else {
    Write-Status "success" "No obvious unsanitized inputs found"
}

Write-Host ""
Write-Host "=== Text Domain Check ===" -ForegroundColor Blue

# Check for inconsistent text domains
Write-Host "Checking text domain consistency..."
$IncorrectTextDomains = 0
foreach ($File in $PhpFiles) {
    $Content = Get-Content $File.FullName
    $TranslationLines = $Content | Select-String "__\(|_e\(|esc_html__\(|esc_attr__\(" | Where-Object { $_ -notmatch "'bsawesome'" -and $_ -match "'[^']+'" }
    $IncorrectTextDomains += $TranslationLines.Count
}

if ($IncorrectTextDomains -gt 0) {
    Write-Status "warning" "Found $IncorrectTextDomains instances of non-'bsawesome' text domains"
} else {
    Write-Status "success" "All translation functions use correct text domain"
}

Write-Host ""
Write-Host "=== Function Naming Check ===" -ForegroundColor Blue

# Check for functions without theme prefix
Write-Host "Checking function naming conventions..."
$FunctionsWithoutPrefix = 0
foreach ($File in $PhpFiles) {
    $Content = Get-Content $File.FullName
    $FunctionLines = $Content | Select-String "^function " | Where-Object { $_ -notmatch "bsawesome_|__construct|__destruct" }
    $FunctionsWithoutPrefix += $FunctionLines.Count
}

if ($FunctionsWithoutPrefix -gt 0) {
    Write-Status "warning" "Found $FunctionsWithoutPrefix functions without 'bsawesome_' prefix"
} else {
    Write-Status "success" "All functions follow naming conventions"
}

Write-Host ""
Write-Host "=== German Content Check ===" -ForegroundColor Blue

# Check for hardcoded German text
Write-Host "Checking for untranslated German content..."
$GermanContent = 0
$GermanWords = @("deutsch", "german", "rabatt", "preis", "versand", "kontakt", "datenschutz", "impressum", "zahlung")

foreach ($File in $PhpFiles) {
    $Content = Get-Content $File.FullName -Raw
    foreach ($Word in $GermanWords) {
        $Matches = ([regex]::Matches($Content, $Word, [System.Text.RegularExpressions.RegexOptions]::IgnoreCase))
        foreach ($Match in $Matches) {
            # Check if it's not in a translation function
            $Context = $Content.Substring([Math]::Max(0, $Match.Index - 50), [Math]::Min(100, $Content.Length - [Math]::Max(0, $Match.Index - 50)))
            if ($Context -notmatch "__\(|_e\(|esc_html__\(|esc_attr__\(") {
                $GermanContent++
            }
        }
    }
}

if ($GermanContent -gt 0) {
    Write-Status "warning" "Found $GermanContent instances of potentially hardcoded German content"
} else {
    Write-Status "success" "No obvious hardcoded German content found"
}

Write-Host ""
Write-Host "=== File Organization Check ===" -ForegroundColor Blue

# Check for proper file organization
$RequiredDirs = @("inc", "assets", "woocommerce", "languages")
foreach ($Dir in $RequiredDirs) {
    $DirPath = Join-Path $ThemeDir $Dir
    if (Test-Path $DirPath) {
        Write-Status "success" "Directory exists: $Dir"
    } else {
        Write-Status "error" "Missing directory: $Dir"
    }
}

Write-Host ""
Write-Host "=== WordPress Standards Check ===" -ForegroundColor Blue

# Check for deprecated functions
Write-Host "Checking WordPress function usage..."
$DeprecatedFunctions = @("mysql_query", "wp_get_http", "get_settings")
foreach ($Func in $DeprecatedFunctions) {
    $Count = 0
    foreach ($File in $PhpFiles) {
        $Content = Get-Content $File.FullName
        $FuncLines = $Content | Select-String $Func
        $Count += $FuncLines.Count
    }
    if ($Count -gt 0) {
        Write-Status "error" "Found deprecated function '$Func': $Count instances"
    }
}

Write-Host ""
Write-Host "=== Performance Check ===" -ForegroundColor Blue

# Check for potential performance issues
Write-Host "Checking for potential performance issues..."

# Check for queries in loops (simplified check)
$QueriesInLoops = 0
foreach ($File in $PhpFiles) {
    $Content = Get-Content $File.FullName
    for ($i = 0; $i -lt $Content.Count; $i++) {
        if ($Content[$i] -match "while|foreach|for") {
            # Check next 10 lines for queries
            for ($j = $i; $j -lt [Math]::Min($i + 10, $Content.Count); $j++) {
                if ($Content[$j] -match "get_posts|WP_Query|query_posts") {
                    $QueriesInLoops++
                    break
                }
            }
        }
    }
}

if ($QueriesInLoops -gt 0) {
    Write-Status "warning" "Found $QueriesInLoops potential queries in loops"
}

Write-Host ""
Write-Host "=== Summary ===" -ForegroundColor Blue
Write-Host "=============================================="

# Generate summary
$TotalPhpFiles = $PhpFiles.Count
$FilesWithProperHeaders = 0
foreach ($File in $PhpFiles) {
    $Content = Get-Content $File.FullName -Raw
    if ($Content -match "@package BSAwesome") {
        $FilesWithProperHeaders++
    }
}

$CompletionPercentage = [Math]::Round(($FilesWithProperHeaders / $TotalPhpFiles) * 100)

Write-Status "info" "Documentation completion: $CompletionPercentage% ($FilesWithProperHeaders/$TotalPhpFiles files)"

if ($CompletionPercentage -ge 90) {
    Write-Status "success" "Theme is nearly ready for production!"
} elseif ($CompletionPercentage -ge 70) {
    Write-Status "warning" "Good progress, but more work needed"
} else {
    Write-Status "error" "Significant work required before production"
}

Write-Host ""
Write-Host "=== Next Steps ===" -ForegroundColor Blue
Write-Host "1. Review and fix any security issues identified above"
Write-Host "2. Complete documentation for remaining PHP files"
Write-Host "3. Standardize text domains to 'bsawesome'"
Write-Host "4. Review function naming conventions"
Write-Host "5. Translate hardcoded German content"
Write-Host "6. Run WordPress Theme Check plugin"
Write-Host "7. Test thoroughly before deployment"

Write-Host ""
Write-Host "For detailed guidelines, see:" -ForegroundColor Cyan
Write-Host "- PRODUCTION_READINESS_PLAN.md"
Write-Host "- PHP_DOCUMENTATION_STANDARDS.md"

Write-Host ""
Write-Host "=== Check Complete ===" -ForegroundColor Blue
Write-Host "Date: $(Get-Date)" -ForegroundColor Blue
Write-Host "=============================================="

# Pause to allow reading results
Read-Host "Press Enter to continue..."
