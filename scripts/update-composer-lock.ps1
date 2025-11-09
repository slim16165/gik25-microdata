# Script PowerShell per aggiornare composer.lock
# Eseguire questo script sul server o in un ambiente con PHP/Composer

Write-Host "🔄 Aggiornamento composer.lock..." -ForegroundColor Cyan

# Verifica che composer sia disponibile
$composerPath = Get-Command composer -ErrorAction SilentlyContinue
if (-not $composerPath) {
    Write-Host "❌ Composer non trovato. Installare Composer prima di continuare." -ForegroundColor Red
    exit 1
}

# Valida composer.json
Write-Host "✅ Validazione composer.json..." -ForegroundColor Green
composer validate --strict

# Aggiorna composer.lock
Write-Host "🔄 Aggiornamento dipendenze..." -ForegroundColor Cyan
composer update --no-interaction --prefer-dist

# Verifica che tutto sia sincronizzato
Write-Host "✅ Verifica sincronizzazione..." -ForegroundColor Green
composer validate --strict

Write-Host "✅ composer.lock aggiornato con successo!" -ForegroundColor Green
Write-Host "📝 Eseeguire: git add composer.lock && git commit -m 'Update composer.lock' && git push" -ForegroundColor Yellow

