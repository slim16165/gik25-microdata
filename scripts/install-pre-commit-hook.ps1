# Script PowerShell per installare il pre-commit hook Git
# Utilizzo: .\scripts\install-pre-commit-hook.ps1

param(
    [string]$RepoRoot = $PSScriptRoot + "\.."
)

$ErrorActionPreference = "Stop"

$HookSource = Join-Path $RepoRoot "scripts\hooks\pre-commit.ps1"
$HookTarget = Join-Path $RepoRoot ".git\hooks\pre-commit.ps1"

if (-not (Test-Path (Join-Path $RepoRoot ".git"))) {
    Write-Error "Errore: questa directory non è un repository Git"
    exit 1
}

if (-not (Test-Path $HookSource)) {
    Write-Error "Errore: file hook non trovato: $HookSource"
    exit 1
}

# Copia l'hook PowerShell
Copy-Item -Path $HookSource -Destination $HookTarget -Force

# Crea anche un wrapper .bat per Windows
$HookTargetBat = Join-Path $RepoRoot ".git\hooks\pre-commit.bat"
$BatContent = @"
@echo off
REM Git pre-commit hook wrapper per Windows
setlocal
set "REPO_ROOT=%~dp0.."
set "HOOK_SCRIPT=%REPO_ROOT%\scripts\hooks\pre-commit.ps1"
if not exist "%HOOK_SCRIPT%" (
    echo Errore: script hook non trovato: %HOOK_SCRIPT%
    exit /b 1
)
powershell -ExecutionPolicy Bypass -File "%HOOK_SCRIPT%"
exit /b %ERRORLEVEL%
"@
Set-Content -Path $HookTargetBat -Value $BatContent -Encoding ASCII

Write-Host "✅ Pre-commit hook installato correttamente" -ForegroundColor Green
Write-Host "   PowerShell: $HookTarget" -ForegroundColor Gray
Write-Host "   Batch wrapper: $HookTargetBat" -ForegroundColor Gray
Write-Host ""
Write-Host "Il hook validerà automaticamente la sintassi PHP prima di ogni commit." -ForegroundColor Cyan
Write-Host ""
Write-Host "Per disinstallare:" -ForegroundColor Yellow
Write-Host "   Remove-Item $HookTarget" -ForegroundColor Gray
Write-Host "   Remove-Item $HookTargetBat" -ForegroundColor Gray

