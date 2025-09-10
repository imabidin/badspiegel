# PowerShell-Script zum Ersetzen verbleibender console-Aufrufe
param(
    [string]$FilePath = "z:\data\docker\volumes\DevKinsta\public\badspiegel\wp-content\themes\bsawesome\assets\js\configurator\dependencies\philips-hue.js"
)

Write-Host "Replacing remaining console calls in: $FilePath"

# Lese die Datei Zeile für Zeile
$lines = Get-Content -Path $FilePath

# Verarbeite jede Zeile
for ($i = 0; $i -lt $lines.Length; $i++) {
    $line = $lines[$i]
    
    # Überspringe Zeilen in den Debug-Helper-Funktionen (Zeilen 67-93)
    if ($i -ge 66 -and $i -le 92) {
        continue
    }
    
    # Ersetze verbleibende console-Aufrufe
    if ($line -match "console\.debug\(") {
        $lines[$i] = $line -replace "console\.debug\(", "debugLog("
        Write-Host "Replaced console.debug on line $($i+1)"
    }
    
    if ($line -match "console\.warn\(") {
        $lines[$i] = $line -replace "console\.warn\(", "debugWarn("
        Write-Host "Replaced console.warn on line $($i+1)"
    }
    
    if ($line -match "console\.info\(") {
        $lines[$i] = $line -replace "console\.info\(", "debugInfo("
        Write-Host "Replaced console.info on line $($i+1)"
    }
}

# Schreibe die Datei zurück
$lines | Set-Content -Path $FilePath

Write-Host "Replacement completed successfully!"
