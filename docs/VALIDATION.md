# Validazione Sintassi PHP

Questo documento descrive i metodi disponibili per validare la sintassi PHP prima di committare o fare deploy.

## Script di Validazione

### Linux/Mac (Bash)

```bash
./scripts/validate-php-syntax.sh
```

Oppure usando Composer:

```bash
composer validate-syntax
```

### Windows (PowerShell)

```powershell
.\scripts\validate-php-syntax.ps1
```

Oppure usando Composer:

```powershell
composer validate-syntax-windows
```

## Git Pre-commit Hook

Per impedire automaticamente commit con errori di sintassi, installa il pre-commit hook:

```bash
# Linux/Mac
cp .git/hooks/pre-commit.example .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
```

Il hook validará automaticamente tutti i file PHP staged prima di permettere il commit.

## GitHub Actions

Il workflow `.github/workflows/php.yml` include automaticamente la validazione della sintassi PHP su ogni push o pull request. Se ci sono errori, il workflow fallirà e bloccherà il merge.

## Validazione Manuale

Per validare un singolo file PHP:

```bash
php -l path/to/file.php
```

## Best Practices

1. **Sempre validare prima di committare**: Esegui `composer validate-syntax` prima di ogni commit
2. **Installa il pre-commit hook**: Previene commit accidentali con errori
3. **Verifica GitHub Actions**: Controlla che il workflow passi prima di fare merge
4. **Editor con validazione**: Configura il tuo editor per evidenziare errori di sintassi in tempo reale

## Errori Comuni

### Parentesi non bilanciate
```php
// ❌ Errato
SafeExecution::safe_execute(function() {
    // codice
}
// Manca la parentesi di chiusura

// ✅ Corretto
SafeExecution::safe_execute(function() {
    // codice
});
```

### Virgolette non chiuse
```php
// ❌ Errato
$message = "Testo non chiuso;

// ✅ Corretto
$message = "Testo chiuso";
```

### Punti e virgola mancanti
```php
// ❌ Errato
$var = "test"

// ✅ Corretto
$var = "test";
```

