# Script PowerShell per validare la sintassi di tutti i file PHP nel plugin
# Utilizzo: .\scripts\validate-php-syntax.ps1

param(
    [string]$PluginDir = $PSScriptRoot + "\.."
)

$ErrorActionPreference = "Stop"

# Colori per output
function Write-ColorOutput($ForegroundColor, $Message) {
    $fc = $host.UI.RawUI.ForegroundColor
    $host.UI.RawUI.ForegroundColor = $ForegroundColor
    Write-Output $Message
    $host.UI.RawUI.ForegroundColor = $fc
}

Write-Output "🔍 Validazione sintassi PHP..."
Write-Output "Directory: $PluginDir"
Write-Output ""

$errors = 0
$filesChecked = 0

# Trova tutti i file PHP escludendo vendor e node_modules
$phpFiles = Get-ChildItem -Path $PluginDir -Filter "*.php" -Recurse -File | 
    Where-Object { 
        $_.FullName -notmatch "\\vendor\\" -and 
        $_.FullName -notmatch "\\node_modules\\" -and 
        $_.FullName -notmatch "\\.git\\" 
    }

foreach ($file in $phpFiles) {
    $filesChecked++
    $relativePath = $file.FullName.Replace($PluginDir, "").TrimStart("\")
    
    # Valida sintassi PHP usando php -l
    $result = & php -l $file.FullName 2>&1
    
    if ($LASTEXITCODE -eq 0) {
        Write-ColorOutput Green "✓ $relativePath"
    } else {
        Write-ColorOutput Red "✗ $relativePath"
        $result | Select-Object -First 5 | ForEach-Object { Write-Output $_ }
        $errors++
    }
}

Write-Output ""
Write-Output "─────────────────────────────────────"
if ($errors -eq 0) {
    Write-ColorOutput Green "✓ Tutti i file PHP hanno sintassi valida"
    Write-Output "File controllati: $filesChecked"
    exit 0
} else {
    Write-ColorOutput Red "✗ Trovati $errors errori di sintassi"
    Write-Output "File controllati: $filesChecked"
    exit 1
}

