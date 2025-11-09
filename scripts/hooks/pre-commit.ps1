# Git pre-commit hook per validare sintassi PHP (PowerShell)
# Installazione: Copy-Item scripts/hooks/pre-commit.ps1 .git/hooks/pre-commit.ps1
# Oppure usa: composer install-pre-commit-windows

# Trova la root del repository Git
$RepoRoot = & git rev-parse --show-toplevel
if (-not $RepoRoot) {
    Write-Error "Errore: questa directory non è un repository Git"
    exit 1
}
Set-Location $RepoRoot

$ErrorActionPreference = "Stop"

# Colori per output
function Write-ColorOutput($ForegroundColor, $Message) {
    $fc = $host.UI.RawUI.ForegroundColor
    $host.UI.RawUI.ForegroundColor = $ForegroundColor
    Write-Output $Message
    $host.UI.RawUI.ForegroundColor = $fc
}

# Ottieni file PHP staged (escludendo vendor, node_modules)
$stagedFiles = & git diff --cached --name-only --diff-filter=ACMR | 
    Where-Object { $_ -match '\.php$' -and $_ -notmatch '^vendor/' -and $_ -notmatch '^node_modules/' }

if (-not $stagedFiles) {
    exit 0
}

Write-ColorOutput Yellow "🔍 Validazione sintassi PHP (pre-commit hook)..."
Write-Output ""

$errors = 0
$filesChecked = 0

foreach ($file in $stagedFiles) {
    $filePath = Join-Path $RepoRoot $file
    if (Test-Path $filePath) {
        $filesChecked++
        $result = & php -l $filePath 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-ColorOutput Green "✓ $file"
        } else {
            Write-ColorOutput Red "✗ $file"
            $result | Select-Object -First 5 | ForEach-Object { Write-Output $_ }
            $errors++
        }
    }
}

Write-Output ""
if ($errors -gt 0) {
    Write-ColorOutput Red "❌ Commit bloccato: trovati $errors errori di sintassi PHP"
    Write-ColorOutput Red "Correggi gli errori prima di committare."
    exit 1
}

Write-ColorOutput Green "✅ Tutti i file PHP hanno sintassi valida ($filesChecked file controllati)"
exit 0

