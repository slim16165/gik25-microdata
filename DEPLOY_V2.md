# Deploy v2.0.1 - Istruzioni Installazione

## ✅ Modifiche Completate

Tutti i file sono stati rinominati per rispettare la convenzione PSR-4:
- `QuestionSchema.class.php` → `QuestionSchema.php`
- Tutti gli shortcode da minuscolo a PascalCase (es: `progressbar.php` → `Progressbar.php`)
- `composer.json` aggiornato con versione e descrizione

## 🚀 Comandi da Eseguire su Staging/Produzione

### 1. Pull delle modifiche
```bash
git pull origin main
# o
git pull origin master
```

### 2. Rigenera Autoloader Composer (IMPORTANTE)
```bash
cd wp-content/plugins/gik25-microdata
composer dump-autoload -o
```

Oppure se stai installando da zero:
```bash
composer install --no-dev --prefer-dist -o
```

### 3. Verifica
Controlla che non ci siano errori e che l'autoloader sia stato rigenerato:
```bash
ls -la vendor/composer/autoload_classmap.php
```

## 📋 Note

- Il flag `-o` (--optimize) genera una classmap ottimizzata per migliori performance
- `--no-dev` esclude le dipendenze di sviluppo (PHPStan, PHPUnit, ecc.)
- `--prefer-dist` usa le versioni distribuite invece di clonare i repository Git

## 🔍 Verifica Post-Deploy

Dopo il deploy, verifica:
1. Health Check: **WordPress Admin → Revious Microdata → Health Check**
2. Verifica che tutti gli shortcode funzionino correttamente
3. Controlla i log per eventuali errori "Class not found"

## ⚠️ Troubleshooting

Se vedi errori "Class not found":
1. Verifica che `composer dump-autoload -o` sia stato eseguito
2. Controlla che `vendor/autoload.php` esista
3. Verifica i permessi della directory `vendor/`

## 📝 Changelog v2.0.1

- ✅ Fix PSR-4: tutti i file rinominati per rispettare la convenzione
- ✅ Composer.json aggiornato con versione e descrizione
- ✅ Eliminati warning Composer su autoload

