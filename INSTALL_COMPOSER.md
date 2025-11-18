# Installazione Dipendenze Composer

## Problema
Le dipendenze Composer non sono state installate. Il file `vendor/composer/autoload_real.php` manca.

## Soluzione: Eseguire composer install

### Metodo 1: Tramite Local by Flywheel (CONSIGLIATO)

1. Apri **Local by Flywheel**
2. Seleziona il sito **"prova"**
3. Clicca su **"Open Site Shell"** o **"Open Terminal"** (icona terminale)
4. Nel terminale che si apre, esegui:
   ```bash
   cd wp-content/plugins/gik25-microdata
   composer install --no-dev
   ```

### Metodo 2: Se hai PHP installato globalmente

Apri PowerShell o CMD nella directory del plugin e esegui:
```powershell
php composer.phar install --no-dev
```

Oppure se hai composer installato globalmente:
```powershell
composer install --no-dev
```

## Verifica

Dopo l'esecuzione, verifica che esista:
- `vendor/composer/autoload_real.php`
- `vendor/composer/autoload_static.php`
- Directory `vendor/` con tutte le dipendenze

## Note

- Il file `composer.phar` è già presente nella directory
- L'opzione `--no-dev` installa solo le dipendenze di produzione (più veloce)
- Se vuoi anche le dipendenze di sviluppo, rimuovi `--no-dev`

