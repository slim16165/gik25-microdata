# Setup Composer su Windows

## Opzione 1: Installazione Composer (Consigliata) ⭐

### Installazione Rapida

1. **Scarica Composer-Setup.exe**
   - Vai su: https://getcomposer.org/download/
   - Scarica `Composer-Setup.exe`
   - Esegui l'installer (rileva automaticamente PHP se installato)

2. **Verifica installazione**
   ```powershell
   composer --version
   ```

3. **Aggiorna composer.lock**
   ```powershell
   cd "C:\Users\g.salvi\Dropbox\Siti internet\Altri siti\Principali\TotalDesign.it\wp-content\plugins\gik25-microdata"
   composer update --no-interaction --prefer-dist
   git add composer.lock
   git commit -m "Update composer.lock"
   git push
   ```

### Se non hai PHP installato

1. **Installa PHP** (XAMPP o standalone):
   - XAMPP: https://www.apachefriends.org/download.html
   - PHP standalone: https://windows.php.net/download/
   - Aggiungi PHP al PATH di sistema

2. **Installa Composer** (vedi sopra)

## Opzione 2: Usa GitHub Actions (Automatico) 🤖

### Aggiornamento Automatico

Quando modifichi `composer.json` e fai push:

1. **GitHub Actions aggiorna automaticamente composer.lock**
   - Vai su: https://github.com/slim16165/gik25-microdata/actions
   - Cerca il workflow "Update composer.lock"
   - Se fallisce, esegui manualmente: Actions → "Update composer.lock" → Run workflow

2. **Oppure esegui manualmente**:
   - Vai su: https://github.com/slim16165/gik25-microdata/actions/workflows/update-composer-lock.yml
   - Clicca "Run workflow" → "Run workflow"

### Workflow Manuale

Puoi anche triggerare l'aggiornamento da GitHub:
- Vai su Actions → "Update composer.lock" → Run workflow

## Opzione 3: Usa Docker (Avanzato) 🐳

```bash
# Se hai Docker installato
docker run --rm -v ${PWD}:/app composer update --no-interaction --prefer-dist
```

## Best Practice

1. ✅ **Mai eseguire `composer update` in produzione**
2. ✅ **Sempre committare `composer.lock` nel repository**
3. ✅ **In produzione usare `composer install --no-dev`** (non update)
4. ✅ **Aggiornare composer.lock quando modifichi composer.json**

## Troubleshooting

### Composer non trovato
```powershell
# Verifica se Composer è nel PATH
where.exe composer
```

### Errore "PHP not found"
```powershell
# Verifica PHP
php --version
# Se non trovato, aggiungi PHP al PATH di sistema
```

### Errore "composer.lock out of sync"
```powershell
# Aggiorna composer.lock
composer update --no-interaction --prefer-dist
git add composer.lock
git commit -m "Update composer.lock"
git push
```

