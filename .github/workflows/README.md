# GitHub Actions Workflows

## Workflow Disponibili

### 1. CI (Continuous Integration)
**File**: `.github/workflows/ci.yml`

**Trigger**:
- Push su `master` (solo file PHP, composer, config)
- Pull Request su `master` (solo file PHP, composer, config)
- **Skip**: Commit con messaggio contenente `[skip ci]`

**Jobs**:
- ✅ Validazione sintassi PHP
- ✅ Validazione Composer
- ✅ Security Audit
- ✅ PHPStan Static Analysis
- ✅ Psalm Static Analysis
- ✅ PHP CS Fixer Code Style
- ✅ Test Suite (se configurata)

**Nota**: Il workflow si attiva SOLO quando vengono modificati file rilevanti (PHP, composer.json/lock, file di configurazione). Modifiche a file come README.md, documentazione, ecc. non triggerano il CI.

### 2. Update composer.lock
**File**: `.github/workflows/update-composer-lock.yml`

**Trigger**:
- ⚙️ Solo esecuzione manuale (workflow_dispatch)
- **NON** si attiva automaticamente quando cambia `composer.json`

**Uso**:
1. Vai su: https://github.com/slim16165/gik25-microdata/actions/workflows/update-composer-lock.yml
2. Clicca "Run workflow" → "Run workflow"
3. Il workflow aggiorna `composer.lock` e fa commit automatico con `[skip ci]`

**Nota**: Il commit usa `[skip ci]` per evitare di triggerare il workflow CI.

## Best Practice

1. ✅ **Usa `[skip ci]`** nei commit che non richiedono test (es. aggiornamenti documentazione)
2. ✅ **Esegui Update composer.lock manualmente** quando necessario
3. ✅ **Il CI si attiva automaticamente** solo su file rilevanti
4. ✅ **Evita push multipli** per ridurre i workflow runs

## Ridurre Workflow Runs

Per evitare troppi workflow runs:

1. **Committa insieme** file correlati invece di commit multipli
2. **Usa `[skip ci]`** per commit di documentazione/configurazione
3. **Esegui Update composer.lock** solo quando necessario (non ad ogni push)
4. **Fai push solo quando necessario** (non ad ogni modifica minore)

## Troubleshooting

### Workflow non si attiva
- Verifica che i file modificati siano nella lista `paths` del trigger
- Verifica che il commit non contenga `[skip ci]`

### Troppi workflow runs
- Controlla se stai facendo push troppo frequentemente
- Usa `[skip ci]` per commit non critici
- Considera di fare commit più grandi invece di molti piccoli

