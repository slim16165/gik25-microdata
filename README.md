# Revious Microdata

[![Build Status](https://scrutinizer-ci.com/g/slim16165/gik25-microdata/badges/build.png?b=master)](https://scrutinizer-ci.com/g/slim16165/gik25-microdata/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/slim16165/gik25-microdata/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/slim16165/gik25-microdata/?branch=master)
[![static analysis](https://github.com/yiisoft/html/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/html/actions?query=workflow%3A%22static+analysis%22)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/slim16165/gik25-microdata/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)

[![WordPress rating](https://img.shields.io/wordpress/plugin/r/gik25-quotes.svg?maxAge=3600&label=wordpress%20rating)](https://wordpress.org/support/view/plugin-reviews/gik25-quotes)
[![WordPress](https://img.shields.io/wordpress/plugin/dt/gik25-quotes.svg?maxAge=3600)](https://downloads.wordpress.org/plugin/gik25-quotes.latest-stable.zip)
[![WordPress](https://img.shields.io/wordpress/v/gik25-quotes.svg?maxAge=3600)](https://wordpress.org/plugins/gik25-quotes/)
[![WordPress plugin](https://img.shields.io/wordpress/plugin/v/gik25-quotes.svg?maxAge=3600)](https://wordpress.org/plugins/gik25-quotes/)
[![license](https://img.shields.io/github/license/adamdehaven/gik25-quotes.svg?maxAge=3600)](https://raw.githubusercontent.com/adamdehaven/gik25-quotes/master/LICENSE)

Plugin WordPress multipiattaforma per gestione shortcode, microdata, ottimizzazioni SEO e widget interattivi.

**Siti supportati**: TotalDesign.it, SuperInformati.com, NonSoloDieti.it, ChieCosa.it, Prestinforma.it

## Caratteristiche Principali

- 🎨 **Shortcode Base**: Quote, Pullquote, Box Info, Progress Bar, Sliding Box, Flipbox, Blinking Button
- 🎨 **Sistema Caroselli Generico**: Caroselli/liste/griglie configurabili via database WordPress (`[carousel collection="..."]`)
- 🏠 **Widget Cucine**: Kitchen Finder con wizard 4-step e generazione lead
- 🧭 **Navigazione App-like**: Widget navigazione multi-livello con varianti mobile/desktop
- 🎯 **Widget TotalDesign**: 18 widget specializzati (Color Hub, IKEA Hub, Palette, Archistar, Grafica 3D, ecc.)
- 🤖 **MCP Server**: Server Model Context Protocol per interrogazione sito WordPress da Cursor/AI
- 🤖 **Widget Contestuali**: Inserimento automatico widget basati su keywords articoli
- 🔍 **Health Check**: Sistema verifica funzionalità plugin dopo deploy (pagina admin + REST API)
- 📊 **Parser Log Cloudways**: Analisi automatica log server per rilevare problemi (errori 5xx, PHP fatal, slow queries)
- 🛡️ **Protezione Globale**: Sistema SafeExecution che previene blocchi WordPress in caso di errori
- 🎯 **SEO**: Schema markup, microdata, ottimizzazioni RankMath/Yoast
- 🎨 **Color Widget**: Caroselli e selezioni colori dinamici
- ⚡ **Performance**: Caricamento condizionale CSS/JS, cache, ottimizzazioni
- 🔒 **Sicurezza**: Sanitizzazione XSS, validazione input, nonce protection
- 📱 **Mobile-first**: Design responsive con touch targets ottimizzati
- ♿ **Accessibilità**: ARIA labels, keyboard navigation, screen reader support 

## INSTALLATION

### Development
<pre>php composer.phar update --no-dev --lock</pre>

### Production
<pre>composer install --no-dev</pre>

**IMPORTANTE**: Dopo qualsiasi deploy su staging/produzione, assicurati di eseguire `composer install --no-dev` nella directory del plugin per rigenerare le dipendenze.

Aggiornare anche la versione del plugin su revious-microdata.php

### Aggiornamento composer.lock

Se `composer.lock` è desincronizzato con `composer.json` (errore GitHub Actions):

**⭐ Opzione 1: GitHub Actions (Automatico - Consigliato)**
1. Vai su: https://github.com/slim16165/gik25-microdata/actions/workflows/update-composer-lock.yml
2. Clicca "Run workflow" → "Run workflow"
3. GitHub Actions aggiorna automaticamente `composer.lock` e fa il commit

**💻 Opzione 2: Localmente (se hai Composer installato)**
```bash
# Windows (PowerShell)
composer update --no-interaction --prefer-dist
git add composer.lock
git commit -m "Update composer.lock"
git push

# Linux/Mac
bash scripts/update-composer-lock.sh
```

**📚 Opzione 3: Installa Composer su Windows**
Vedi [docs/COMPOSER_SETUP.md](docs/COMPOSER_SETUP.md) per istruzioni complete.

**Nota**: `composer.lock` deve essere committato e sincronizzato con `composer.json` per evitare errori in GitHub Actions. **Mai eseguire `composer update` in produzione** - usa sempre `composer install --no-dev`.

### Validazione Sintassi PHP

Prima di committare, valida la sintassi PHP:

**Linux/Mac:**
```bash
composer validate-syntax
```

**Windows:**
```bash
composer validate-syntax-windows
```

### Git Pre-commit Hook (Opzionale)

Per validare automaticamente la sintassi PHP prima di ogni commit:

**Linux/Mac:**
```bash
composer install-pre-commit
# oppure manualmente:
bash scripts/install-pre-commit-hook.sh
```

**Windows:**
```powershell
composer install-pre-commit-windows
# oppure manualmente:
powershell -ExecutionPolicy Bypass -File scripts/install-pre-commit-hook.ps1
```

Il hook validerà automaticamente tutti i file PHP staged prima di permettere il commit.

### GitHub Actions CI/CD

Il workflow `.github/workflows/ci.yml` include automaticamente:
- ✅ Validazione sintassi PHP
- ✅ Validazione Composer
- ✅ Security Audit
- ✅ PHPStan Static Analysis (level 9)
- ✅ Psalm Static Analysis
- ✅ PHP CS Fixer Code Style
- ✅ Test Suite con Code Coverage
- ✅ CodeQL Security Analysis
- ✅ Dependency Review
- ✅ SLSA Attestations
- ✅ Dependabot (aggiornamenti automatici dipendenze)

I controlli vengono eseguiti in parallelo per performance ottimali e solo sui file modificati (path filtering).

Vedi [docs/GITHUB_SETUP.md](docs/GITHUB_SETUP.md) per la configurazione completa GitHub.

## USAGE

### Kitchen Finder Widget
Inserisci lo shortcode in qualsiasi post o pagina:

```
[kitchen_finder title="Trova la cucina perfetta per te"]
```

Il widget caricherà automaticamente CSS e JS solo sulla pagina che contiene lo shortcode.

### Altri Shortcode Disponibili

**Shortcode Base (tutti i siti)**:
- `[md_quote]` - Citazioni stile quote
- `[boxinfo title="Titolo"]` - Box informativi
- `[md_progressbar]` - Barre di progresso
- `[md_slidingbox]` - Box scorrevoli
- `[md_flipbox]` - Box con effetto flip
- `[md_blinkingbutton]` - Pulsanti animati

**Shortcode TotalDesign** (vedi `docs/TOTALDESIGN_WIDGETS.md` per lista completa):
- `[kitchen_finder]` - Wizard cucine 4-step
- `[app_nav]` - Navigazione app-like multi-livello
- `[link_colori]` - Carosello articoli colori (50+ colori)
- `[grafica3d]` - Carosello programmi 3D
- `[archistar]` - Carosello architetti famosi
- `[td_colori_hub]` - Hub colori programmatico
- `[td_ikea_hub]` - Hub IKEA programmatico
- `[td_palette_correlate color="bianco"]` - Palette correlate
- `[td_abbinamenti_colore color="verde"]` - Abbinamenti colore
- `[td_lead_box type="color|ikea"]` - Box CTA lead generation
- E altri 8 widget programmatici...

**Widget Automatici**:
- Contextual Widgets: inserimento automatico basato su keywords articoli

# Changelog

## 2.3.0 (2025-11-09) - Setup Test Automatici e Miglioramenti Qualità Codice

### 🧪 Test Automatici
- ✅ **PHPUnit Setup**: Configurazione completa per test unitari e integrazione
  - `phpunit.xml.dist` configurato per coverage e report
  - `tests/bootstrap.php` per ambiente test WordPress
  - Struttura test organizzata (Unit/Integration)
- ✅ **Test Base**: Test iniziali per SafeExecution e ShortcodeRegistry
  - Test unitari per gestione errori e sicurezza
  - Test integrazione per shortcode registration
  - Placeholder per test futuri
- ✅ **Code Coverage**: Integrazione Codecov nel CI
  - Report coverage automatico in GitHub Actions
  - Target coverage: 60-70% (da incrementare gradualmente)
- ✅ **Composer Scripts**: Nuovi script per test
  - `composer test`: Esegue test suite
  - `composer test-coverage`: Genera report coverage HTML

### 🚀 CI/CD Miglioramenti
- ✅ **Test in CI**: Test suite eseguita automaticamente in GitHub Actions
- ✅ **Coverage Upload**: Upload automatico coverage a Codecov
- ✅ **Workflow Ottimizzati**: CI si attiva solo su file rilevanti (PHP, composer, config)
- ✅ **Update composer.lock**: Workflow manuale per aggiornamento dipendenze

### 🔒 Sicurezza e Qualità
- ✅ **CodeQL Security Analysis**: Analisi automatica vulnerabilità (JavaScript, PHP)
- ✅ **Dependency Review**: Review automatica dipendenze nelle PR
- ✅ **Dependabot**: Aggiornamenti automatici dipendenze (Composer, GitHub Actions)
- ✅ **SLSA Attestations**: Attestazioni di provenienza codice per sicurezza supply chain
- ✅ **Security Policy**: Policy di sicurezza e procedure per segnalazione vulnerabilità

### 📋 Templates e Documentazione
- ✅ **Pull Request Template**: Template standardizzato per PR con checklist
- ✅ **Issue Templates**: Template per bug report e feature request
- ✅ **Release Workflow**: Creazione automatica release GitHub da tag
- ✅ **GITHUB_SETUP.md**: Guida completa configurazione GitHub
- ✅ **.gitignore**: Aggiunti file coverage e cache test

---

## 2.2.0 (2025-11-09) - Separazione Analisi Log e Ottimizzazioni

### 🔍 Separazione Analisi Log Cloudways
- ✅ **Riepilogo Separato**: Analisi log Cloudways ora separata dagli health check normali
  - Riepilogo Health Check: statistiche separate (totale, successi, warning, errori)
  - Riepilogo Analisi Log: stato, messaggio, conteggio errori PHP critici
  - Sezioni visivamente distinte con stili diversi
- ✅ **Dettagli Separati**: Due sezioni distinte nella tab "Dettagli"
  - Dettagli Health Check: tutti gli health check normali
  - Dettagli Analisi Log Cloudways: analisi log completa con errori PHP, tail errori, dettagli completi (tutto collapsed)
  - Separazione visiva con bordi e titoli distinti

### ⚡ Ottimizzazioni Parser Log
- ✅ **Esclusione File .gz**: File compressi (.gz) esclusi di default (troppo pesanti, file rotati vecchi)
- ✅ **Lettura Coda Efficiente**: Per file grandi, lettura solo degli ultimi 2MB (dove ci sono gli errori più recenti)
  - Anche file giganti vengono analizzati (solo coda), garantendo sempre accesso agli errori più recenti
  - Nessun limite di dimensione assoluto, ma lettura intelligente della parte finale
- ✅ **Struttura Collapsed**: Tutte le sezioni analisi log ora collapsed di default per interfaccia più pulita
  - Errori PHP Critici (collapsed)
  - Ultimi errori dai log (tail, collapsed)
  - Dettagli completi (collapsed)

---

## 2.1.1 (2025-11-09) - Miglioramenti Parser Log Cloudways

### 🔍 Parser Log Cloudways Migliorato
- ✅ **Supporto File Rotati (.gz)**: Aggiunto supporto per file log compressi e ruotati
  - Metodi `collect_log_files()` e `tail_from_files()` per gestire file plain e `.gz`
  - Pattern glob estesi per file ruotati (`*.log.*.gz`, `*.log.*`)
  - Lettura efficiente da file compressi con `gzopen()`/`gzgets()`
- ✅ **Correzione Mappatura File**: `php_error` ora punta correttamente a `php-app.error.log` (non più `php-app.access.log`)
  - `php-access.log` separato e usato solo per HTTP 5xx (access log)
  - Mappatura corretta per tutti i tipi di log Cloudways
- ✅ **Unificazione Access Log**: HTTP 5xx ora include nginx, apache e php access log
  - Sezione "HTTP 5xx (Nginx/Apache/PHP Access)" unificata
  - Pattern robusto per catturare status code da tutti i formati
- ✅ **Pattern Migliorati**: Aggiornati pattern per tutti i tipi di log
  - Access log: pattern `/"\s+(\d{3})\s+/` per status code HTTP
  - PHP slow: mostra tutte le righe non vuote (blocchi con `script_filename` e stack)
  - WP-Cron: filtra per `WordPress database error|error|warn|Executed the cron event`
- ✅ **Ottimizzazioni Performance**: Limite dimensione file (50MB), lettura efficiente (ultimi 5MB per file grandi), gestione memoria ottimizzata

## 2.1.0 (2025-11-09) - Documentazione Snellita

### 📚 Pulizia Documentazione
- ✅ **Rimossi File Duplicati**: Eliminati 6 file documentazione MCP duplicati (MCP_ARCHITECTURE.md, MCP_SETUP.md, DEPLOY_MCP.md, TEST_MCP.md, MCP_SERVER_README.md, QUERY_SQL_NAVIGATOR.md)
- ✅ **MCP.md Consolidato**: Documentazione MCP unificata in un solo file snello (~141 righe, -40%)
- ✅ **Piani Snelliti**: ADMIN_UI_ENHANCEMENT.md (-70%), TESTING_STRATEGY.md (-70%), CAROUSEL_GENERALIZATION_PLAN.md (-67%)
- ✅ **VALIDATION.md Integrato**: Contenuto integrato nel README principale, file separato rimosso
- ✅ **docs/README.md Aggiornato**: Struttura snella con solo link essenziali
- ✅ **Riferimenti Aggiornati**: Tutti i riferimenti a file eliminati rimossi da README.md e mcp-server/README.md

**Risultato**: Documentazione ridotta da ~2000+ righe a ~800 righe (-60%), più utile e fruibile per umani e AI.

## 2.0.1 (2025-11-09) - Bug Fixes

### 🐛 Fix Critici
- ✅ **Fix Fatal Error Shortcode**: Risolto errore `Perfectpullquote/Prezzo/Youtube does not have method "scripts"`
  - Metodo `scripts()` ora opzionale in `ShortcodeBase` (verifica `method_exists()` prima di chiamare)
  - Non tutti gli shortcode hanno JavaScript, solo alcuni (blinkingbutton, appnav, kitchenfinder)
- ✅ **Fix Null Pointer**: Risolto errore `Attempt to read property "ID" on null` in `totaldesign_specific.php`
  - Aggiunto check `is_a($post, 'WP_Post') && isset($post->ID)` prima di usare `$post->ID`
  - Previene errori in archive pages, search pages, ecc. dove `$post` può essere null

### 📋 Note
- Errori Action Scheduler (tabella database mancante) non sono del plugin, ma del plugin Action Scheduler/ImageOptimization
- Query Monitor memory exhaustion causato da errori Action Scheduler
- Media.php warnings (undefined array key) sono di WordPress core o altri plugin

---

## 2.0.0 (2025-11-09) - Major Release 🎉

### 🎉 Nuove Funzionalità Major
- ✅ **Parser Log Avanzato**: Estrazione stack trace completo, raggruppamento intelligente, prioritizzazione errori critici
- ✅ **REST API Health Check**: Nuovi endpoint `/wp-json/wp-mcp/v1/health/errors` e `/wp-json/wp-mcp/v1/health/errors/critical` per interrogare errori via MCP
- ✅ **Visualizzazione Errori PHP Rinnovata**: Sezione dedicata, stack trace espandibile, evidenza visiva
- ✅ **Toggle Debug Mode**: Endpoint REST `/wp-json/wp-mcp/v1/health/debug` per abilitare/disabilitare debug mode

### 🔄 Breaking Changes
- ⚠️ **Dipendenze Aggiornate**: `yiisoft/html` ^3.0, `illuminate/collections` ^10.0 (richiedono aggiornamento composer)
- ⚠️ **Namespace Riorganizzati**: 
  - `ColorWidget` → `gik25microdata\Widgets\ColorWidget`
  - `TagHelper` → `gik25microdata\Utility\TagHelper`
  - `ReviousMicrodataSettingsPage` → `gik25microdata\Admin\SettingsPage`
  - `AdminHelper` → `gik25microdata\Admin\AdminHelper`
  - `ExcludePostFrom` → `gik25microdata\WPSettings\ExcludePostFrom`
- ⚠️ **Riorganizzazione Directory**: File spostati in struttura più organizzata (retrocompatibilità mantenuta durante transizione)

### 🏗️ Riorganizzazione Codebase
- ✅ **Riorganizzazione Directory Fase 1**: 
  - `ColorWidget.php` → `Widgets/ColorWidget.php`
  - `TagHelper.php` → `Utility/TagHelper.php`
  - `revious-microdata-settings.php` → `Admin/SettingsPage.php`
  - `AdminHelper.class.php` → `Admin/AdminHelper.php`
  - `ExcludePostFrom.php` → `WPSettings/ExcludePostFrom.php`
- ✅ **File Analyze-Log Spostati**: Script debug spostati in `scripts/debug/` per migliore organizzazione
- ✅ **Namespace Aggiornati**: Aggiornati tutti i riferimenti per usare nuovi namespace
- ✅ **Retrocompatibilità**: Mantenuta retrocompatibilità per ColorWidget durante transizione

### 📊 Miglioramenti Errori PHP
- ✅ **Visualizzazione Errori PHP Migliorata**: Sezione dedicata con evidenza visiva, stack trace completo espandibile, file e riga evidenziati
- ✅ **Parser Log Avanzato**: Estrazione stack trace completo (fino a 20 righe), raggruppamento intelligente per file/riga, prioritizzazione errori critici
- ✅ **Raggruppamento Intelligente**: Errori simili raggruppati per tipo/file/riga con conteggio occorrenze
- ✅ **Contesto Esecuzione**: Identificazione contesto (AJAX, WP-CRON, frontend, backend, REST API) per ogni errore

### 📋 Documentazione
- ✅ **HEALTH_CHECK.md Aggiornato**: Aggiunta documentazione completa per parser log e visualizzazione errori PHP
- ✅ **MCP.md Consolidato**: Creato file consolidato `docs/MCP.md` con tutta la documentazione MCP
- ✅ **Script Debug Documentati**: Aggiornati commenti in `scripts/debug/analyze-log-page.php` con nuove istruzioni

### 🛠️ Composer e Dependencies
- ✅ **Versioni Aggiornate**: `yiisoft/html` aggiornato a `^3.0`, `illuminate/collections` a `^10.0`
- ✅ **Monolog Aggiunto**: Aggiunto `monolog/monolog ^3.0` per logging strutturato
- ✅ **Dev Dependencies**: Aggiunti `phpunit/phpunit ^10.0`, `rector/rector ^0.19`, `friendsofphp/php-cs-fixer ^3.0`
- ✅ **Scripts Composer**: Aggiunti script per `phpstan`, `psalm`, `cs-fix`, `rector`, `test`

### 🚀 GitHub Actions
- ✅ **Test Estesi**: Aggiunti step per PHPStan, Psalm, PHP CS Fixer, Security Audit, PHPUnit
- ✅ **Validazione Migliorata**: Test suite configurabile con fallback graceful se non configurata

### 📋 Note Migrazione
- **Composer**: Eseguire `composer update` per aggiornare dipendenze a versioni major
- **Namespace**: I namespace sono stati riorganizzati ma la retrocompatibilità è mantenuta durante la transizione
- **Breaking Changes**: Verificare compatibilità con `yiisoft/html` ^3.0 e `illuminate/collections` ^10.0

---

## 1.16.8 (2025-11-09)

### Miglioramenti Errori PHP
- ✅ **Visualizzazione Errori PHP Migliorata**: Sezione dedicata con evidenza visiva, stack trace completo espandibile, file e riga evidenziati
- ✅ **Parser Log Avanzato**: Estrazione stack trace completo (fino a 20 righe), raggruppamento intelligente per file/riga, prioritizzazione errori critici
- ✅ **REST API Health Check**: Nuovi endpoint `/wp-json/wp-mcp/v1/health/errors` e `/wp-json/wp-mcp/v1/health/errors/critical` per interrogare errori via MCP
- ✅ **Toggle Debug Mode**: Endpoint REST `/wp-json/wp-mcp/v1/health/debug` per abilitare/disabilitare debug mode
- ✅ **Raggruppamento Intelligente**: Errori simili raggruppati per tipo/file/riga con conteggio occorrenze
- ✅ **Contesto Esecuzione**: Identificazione contesto (AJAX, WP-CRON, frontend, backend, REST API) per ogni errore

### Riorganizzazione Codebase
- ✅ **File Analyze-Log Spostati**: Script debug spostati in `scripts/debug/` per migliore organizzazione
- ✅ **Riorganizzazione Directory Fase 1**: `ColorWidget.php` spostato in `Widgets/`, `TagHelper.php` spostato in `Utility/`
- ✅ **Namespace Aggiornati**: Aggiornati tutti i riferimenti per usare nuovi namespace (`gik25microdata\Widgets\ColorWidget`, `gik25microdata\Utility\TagHelper`)
- ✅ **Retrocompatibilità**: Mantenuta retrocompatibilità per ColorWidget durante transizione

### Documentazione
- ✅ **HEALTH_CHECK.md Aggiornato**: Aggiunta documentazione completa per parser log e visualizzazione errori PHP
- ✅ **MCP.md Consolidato**: Creato file consolidato `docs/MCP.md` con tutta la documentazione MCP
- ✅ **Script Debug Documentati**: Aggiornati commenti in `scripts/debug/analyze-log-page.php` con nuove istruzioni

### Composer e Dependencies
- ✅ **Versioni Aggiornate**: `yiisoft/html` aggiornato a `^3.0`, `illuminate/collections` a `^10.0`
- ✅ **Monolog Aggiunto**: Aggiunto `monolog/monolog ^3.0` per logging strutturato
- ✅ **Dev Dependencies**: Aggiunti `phpunit/phpunit ^10.0`, `rector/rector ^0.19`, `friendsofphp/php-cs-fixer ^3.0`
- ✅ **Scripts Composer**: Aggiunti script per `phpstan`, `psalm`, `cs-fix`, `rector`, `test`

### GitHub Actions
- ✅ **Test Estesi**: Aggiunti step per PHPStan, Psalm, PHP CS Fixer, Security Audit, PHPUnit
- ✅ **Validazione Migliorata**: Test suite configurabile con fallback graceful se non configurata

##### 1.16.7 _(2025-11-09)_
* **Riorganizzazione Menu Admin**: Dashboard, Impostazioni e Strumenti unificati in una pagina con tab. Shortcodes e Utilizzo Shortcode unificati in una pagina con tab. Menu più pulito e organizzato.
* **Fix TinyMCE Buttons**: Corretto errore `array_push(): Argument #1 ($array) must be of type array, null given` in tutti gli shortcode che registrano bottoni TinyMCE (boxinfo, flipbox, blinkingbutton, prezzo, telefono, slidingbox, youtube, perfectpullquote). Aggiunto controllo per verificare che il parametro sia un array prima di usare `array_push()`.

##### 1.16.6 _(2025-11-09)_
* **Fix UI Health Check**: Ripristinato funzionamento tab dettagli e interazioni
  * Script JavaScript completo per gestione tab Riepilogo/Dettagli
  * Richiesta AJAX con nonce e stato caricamento
  * Pulsante "Copia negli appunti" funzionante con feedback visivo
  * Formattazione testo per clipboard migliorata
  * File: `include/class/HealthCheck/HealthChecker.php`
* **Migliorata Formattazione Log Parser**: Esempi più leggibili
  * Esempi su più righe con elenco puntato per maggiore chiarezza
  * Indicatore (+X altri) su nuova riga separata
  * Formattazione HTML migliorata per visualizzazione dettagli
  * File: `include/class/HealthCheck/CloudwaysLogParser.php`

##### 1.16.5 _(2025-11-09)_
* **Miglioramenti Parser Log**: Sistema esempi migliorato per maggiore chiarezza
  * Aumentato numero esempi raccolti: errori PHP/Apache (10), Nginx 5xx (8), PHP slow (8), WP cron (5)
  * Sistema dinamico esempi mostrati: 3-5 esempi in base alle occorrenze (50+ = 4, 100+ = 5)
  * Indicatore (+X altri) quando ci sono più esempi disponibili
  * Migliorata varietà: rimozione duplicati, esempi più informativi
  * PHP Slow Requests: include timestamp e prima riga stack trace
  * Formato più informativo: script | timestamp | stack_info
  * File: `include/class/HealthCheck/CloudwaysLogParser.php`
* **Fix Caricamento SafeExecution**: Risolto errore 'Class not found'
  * Caricamento manuale SafeExecution.php prima di PluginBootstrap
  * Aggiunti controlli function_exists() per funzioni WordPress
  * Protezione contro chiamate premature durante inizializzazione
  * File: `revious-microdata.php`, `include/class/Utility/SafeExecution.php`
* **Sistema Validazione Sintassi PHP**: Prevenzione errori di sintassi
  * Script validazione bash (Linux/Mac) e PowerShell (Windows)
  * Composer scripts: validate-syntax e validate-syntax-windows
  * Git pre-commit hook opzionale
  * Validazione automatica in GitHub Actions
  * File: `scripts/validate-php-syntax.sh`, `scripts/validate-php-syntax.ps1`, `.github/workflows/php.yml`

##### 1.16.4 _(2025-11-08)_
* **Parser Log Cloudways**: Aggiunto sistema di analisi log server per rilevare problemi
  * Parser specifico per Cloudways che analizza Nginx, Apache, PHP e WordPress cron logs
  * Rilevamento automatico errori critici (5xx, PHP Fatal, timeout, etc.)
  * Tracciamento contesto di esecuzione (AJAX, WP-CRON, WP-CLI, Frontend, Backend, REST API)
  * Ignorati completamente errori Action Scheduler (tabelle opzionali mancanti)
  * Integrato nell'Health Check con riepilogo per contesto
  * File: `include/class/HealthCheck/CloudwaysLogParser.php`
* **Sistema Protezione Globale**: Aggiunto sistema SafeExecution per proteggere tutto il plugin
  * Classe `SafeExecution` per eseguire codice in modo sicuro senza bloccare WordPress
  * Protezione automatica di tutti gli hook WordPress (add_action, add_filter)
  * Protezione AJAX e REST API handlers
  * Disabilitazione logging durante operazioni critiche per evitare loop infiniti
  * Ripristino automatico stato originale dopo esecuzione
  * File: `include/class/Utility/SafeExecution.php`
* **Protezione Plugin Completa**: Tutto il plugin ora gestisce errori senza bloccare WordPress
  * PluginBootstrap completamente protetto (init, database, admin, frontend)
  * HealthChecker completamente protetto (tutti i check, AJAX, REST API)
  * Tutti gli hook WordPress protetti con SafeExecution
  * Gestione errori silenziosa per prevenire loop infiniti di log
  * File modificati: `include/class/PluginBootstrap.php`, `include/class/HealthCheck/HealthChecker.php`
* **Miglioramenti Parser Log**:
  * Limiti sicurezza: file max 100MB, chunk max 5MB, timeout 30s
  * Protezione memoria: limite 256MB durante analisi
  * Gestione errori silenziosa senza generare log infiniti
  * Rilevamento contesto: identifica dove viene eseguito il codice (AJAX, WP-CRON, etc.)
  * Riepilogo contesti: mostra distribuzione errori per tipo di esecuzione

##### 1.16.3 _(2025-01-XX)_
* **Fix Critici OptimizationHelper**: Corretti errori PHP fatali in `OptimizationHelper.php`
  * Fix errore `foreach() argument must be of type array|object, bool given` quando shortcode non configurati
  * Aggiunto controllo tipo array prima di foreach con validazione completa
  * Creato metodo mancante `load_css_js_on_posts_which_contain_enabled_shortcodes()` per evitare errore callback
  * Migliorata validazione opzioni shortcode con pulizia array (rimozione elementi vuoti)
  * Aggiunta validazione contenuto post prima di accesso a `post_content`
  * File: `include/class/Utility/OptimizationHelper.php`
* **Fix Database Prefix**: Corretto uso prefisso tabelle hardcoded in `TagHelper.php`
  * Sostituito prefisso hardcoded `wp_` con `$wpdb->prefix` dinamico per supportare qualsiasi prefisso
  * Aggiunti prepared statements per prevenire SQL injection
  * Corretto bug in `tagWithOnePostRedirect()` che passava `term_id` invece di `tag->name`
  * Migliorata gestione errori con logging invece di exit
  * File: `include/class/TagHelper.php`
* **Miglioramenti Database**: Aggiornato commento SQL in `CarouselCollections.php`
  * Rimosso riferimento a prefisso hardcoded nel commento SQL (solo documentazione)
  * File: `include/class/Database/CarouselCollections.php`
* **Tool Analisi Log**: Aggiunto script Python per analisi log Apache/WordPress
  * Script `analyze_log.py` per analisi errori nei file di log
  * Pattern matching per errori comuni (PHP Fatal, Warning, Database, etc.)
  * Supporto file grandi (>2GB) con lettura efficiente ultime righe
  * File: `analyze_log.py`

##### 1.15.1 _(2025-11-08)_
* **Fix Dashboard MCP Status**: Corretto controllo stato MCP REST API
  * Rinominato da "MCP Server" a "MCP REST API" per chiarezza
  * Verifica corretta delle route REST API registrate (`/wp-mcp/v1/...`)
  * Aggiunta nota esplicativa: verifica solo backend WordPress, non server Node.js locale
  * Migliorato metodo `is_mcp_api_enabled()` per verificare direttamente le route registrate
  * File: `include/class/Admin/AdminMenu.php`
* **Documentazione MCP**: Chiarito architettura e flusso
  * Documentazione aggiornata per chiarire differenza tra REST API (Cloudways) e server Node.js (locale)
  * File: `docs/MCP.md`

##### 1.16.2 _(2025-11-08)_
* **Miglioramento Messaggi Errore**: Messaggi di errore dettagliati per creazione collezione di test
  * Validazione template esistente prima di creare collezione
  * Validazione display_type con valori ammessi esplicitati
  * Gestione eccezioni con messaggi informativi
  * Suggerimenti per risoluzione problemi database
  * File: `include/class/Admin/CarouselsPage.php`

##### 1.16.1 _(2025-01-XX)_
* **Unificazione Pagina Caroselli**: Creata pagina unificata con tab per Gestione Collezioni, Anteprima Migrazione e Test Caroselli. Rimossi menu separati
* **Miglioramenti Utilizzo Shortcode**: Aggiunto filtro "solo usati" e validazione per shortcode lista/carousel/grid che verifica presenza items
  * File: `include/class/Admin/CarouselsPage.php`, `include/class/Admin/ShortcodesUsagePage.php`

##### 1.16.0 _(2025-01-XX)_
* **Miglioramento Pagina Utilizzo Shortcode**: Scansione automatica di tutti gli shortcode utilizzati nel sito con cache (1 ora). Layout griglia tipo card con statistiche, filtri e dettagli per ogni shortcode. Rimossa combobox, ora mostra tutti gli shortcode utilizzati in una griglia visuale
* **Fix CarouselTester**: Migliorata gestione errori nella creazione collezioni di test con logging dettagliato. Aggiunto supporto per `template_id` e `template_config` in `upsert_collection`
* **Unificazione Pagine Shortcode**: Creata pagina unificata `ShortcodesManagerPage` che combina gestione e visualizzazione shortcode con layout tipo Elementor (1 shortcode per riga), filtri per categoria, ricerca e toggle switch
  * File: `include/class/Admin/ShortcodesManagerPage.php`, `include/class/Admin/ShortcodesUsagePage.php`

##### 1.15.0 _(2025-11-08)_
* **Sistema Template Configurabili per Caroselli**: Template CSS/DOM/JS configurabili via database
  * Nuova tabella `wp_carousel_templates` per template riutilizzabili
  * Template di sistema predefiniti: `thumbnail-list`, `simple-list`, `grid-modern`
  * Supporto variabili CSS configurabili (es: `{{css.tile-size}}`, `{{css.gap}}`)
  * Template engine con parsing variabili e rendering dinamico
  * File: `include/class/Database/CarouselTemplates.php`, `include/class/Carousel/CarouselTemplateEngine.php`
* **Estensione GenericCarousel**: Integrazione template system
  * `GenericCarousel` ora usa template configurabili per `list` e `grid` display types
  * Recupero automatico immagini dai post WordPress se non specificate
  * Fallback a placeholder se immagine non disponibile
  * Retrocompatibilità: caroselli esistenti continuano a funzionare
  * File: `include/class/Shortcodes/GenericCarousel.php`
* **Pagina Admin "Test Caroselli"**: Interfaccia per testare collezioni e shortcode
  * Creazione collezione di test con selezione template
  * Aggiunta items di test tramite URL (recupero automatico titolo/immagine)
  * Anteprima rendering in tempo reale
  * Shortcode pronti all'uso con varianti (display, limit, title)
  * Gestione completa collezione di test (crea/elimina)
  * File: `include/class/Admin/CarouselTester.php`
* **Estensione Database Collezioni**: Supporto template nelle collezioni
  * Aggiunto campo `template_id` a `wp_carousel_collections`
  * Aggiunto campo `template_config` (JSON) per configurazione template
  * Migration automatica per tabelle esistenti
  * File: `include/class/Database/CarouselCollections.php`

##### 1.14.0 _(2025-11-08)_
* **Anteprima Migrazione Dati**: Nuova pagina admin per visualizzare dati migrabili da codice hardcoded
  * Mostra tutte le collezioni migrabili (Colori, Programmi 3D, Architetti)
  * Anteprima completa con dettagli items, categorie e shortcode risultante
  * Link diretto alla migrazione effettiva
  * File: `include/class/Admin/MigrationPreview.php`
* **Miglioramenti Pagina Settings**: UI completamente rinnovata per selezione shortcode
  * Shortcode mostrati con card individuali e hover effects
  * Microdocumentazione integrata per ogni shortcode (descrizione, uso, alias)
  * Design più pulito e professionale con CSS migliorato
  * Lista scrollabile con max-height 500px
* **Fix Shortcode Registration**: Correzioni per shortcode mancanti
  * Aggiunto `$asset_path` property a `ShortcodeBase` per risolvere errori "Undefined property"
  * Corretti alias mancanti per `telefono`, `progressbar`, `slidingbox`, `flipbox`, `blinkingbutton`, `prezzo`, `flexlist`
  * Corretti nomi metodi (`ShortcodeHandler` invece di `shortcode()`)
  * Implementato `ShortcodeHandler()` per `progressbar` (ritorna HTML comment)
  * Shortcode ora correttamente caricati nel backend per health check

##### 1.13.0 _(2025-11-08)_
* **Menu Admin Principale**: Creata voce primaria "Revious Microdata" nel menu admin
  * Dashboard con informazioni plugin, statistiche e link rapidi
  * Impostazioni spostate da "Impostazioni" al menu principale
  * Health Check spostato da "Strumenti" al menu principale
  * Rimossa configurazione percorso PHP per Composer (rilevamento automatico)
  * Health Check: esportazione risultati negli appunti invece che in file
  * Miglioramenti UI pagina settings e dashboard
* **Fix Health Check**: Correzione check shortcode, AJAX e classi PHP
  * Check più robusti che gestiscono correttamente shortcode opzionali
  * Verifica AJAX endpoints più accurata
  * Verifica classi PHP con autoloader

##### 1.12.0 _(2025-11-07)_
* **Sistema Generico Caroselli/Liste Configurabile**: Sistema flessibile per creare caroselli, liste e griglie via database
  * Tabelle database: `wp_carousel_collections`, `wp_carousel_items`
  * Shortcode generico: `[carousel collection="colori"]`, `[list]`, `[grid]`
  * Supporto parametri: `collection`, `category`, `limit`, `display`, `title`, `css_class`
  * Rendering: carousel, list, grid
  * Generico per tutti i siti, non solo TotalDesign
  * Migrazione automatica da codice hardcoded: `CarouselCollections::migrate_from_hardcoded()`
  * File: `include/class/Database/CarouselCollections.php`, `include/class/Shortcodes/GenericCarousel.php`
  * Documentazione: `docs/GENERIC_CAROUSEL.md`
* **Sistema Health Check Completo**: Verifica automatica funzionalità plugin dopo deploy
  * 7 check programmatici: shortcode, REST API, AJAX, file, database, assets, classi
  * Pagina admin: **Strumenti → Health Check** con riepilogo e dettagli
  * Pulsanti: "Esegui Health Check" (AJAX), "Esporta Risultati" (HTML)
  * REST API endpoint: `/wp-json/gik25/v1/health-check`
  * File: `include/class/HealthCheck/HealthChecker.php`
  * Documentazione: `docs/HEALTH_CHECK.md`
* **Documentazione Migliorata**: Descrizioni più chiare e dettagliate
  * `docs/TOTALDESIGN_WIDGETS.md`: Descrizioni dettagliate widget (es. Lead Box con esempi concreti)

##### 1.11.0 _(2025-11-07)_
* **Semplificazione MCP Server**: Rimossa complessità database multi-sito
  * Rimosso `MCPConfig` e tabelle database (over-engineering)
  * Route estese (color, ikea, room, pantone) ora opzionali via filter WordPress
  * Abilitazione semplice: `add_filter('wp_mcp_enable_extended_routes', '__return_true')`
  * Sistema più semplice: niente database, solo codice PHP
  * Namespace generico: `wp-mcp/v1` (funziona su qualsiasi WordPress)
  * Documentazione: `docs/MCP.md`

##### 1.10.0 _(2025-11-07)_
* **MCP Server**: Integrazione Model Context Protocol per interrogazione WordPress da Cursor/AI
  * Server Node.js locale che espone tools e risorse per analizzare contenuti WordPress
  * REST API WordPress (`/wp-json/wp-mcp/v1/...`) per lettura dati (categorie, post, ricerca, pattern)
  * Tool per analisi contenuti e suggerimenti widget basati su pattern (cucine, colori, IKEA, stanze)
  * Tool per modifica articoli (titolo, contenuto, categorie, tag) con autenticazione Application Password
  * Gestione tag completa: lista, ricerca, creazione, aggiunta a post (creazione automatica se non esistono)
  * Route estese opzionali per siti specifici (TotalDesign: colors, ikea, rooms, pantone)
  * Query vault opzionale per ricerca in file markdown locali (Obsidian)
  * File: `include/class/REST/MCPApi.php`, `mcp-server/server.js`, `mcp-server/package.json`
  * Documentazione: `docs/MCP.md`

##### 1.9.0 _(2025-11-06)_
* App-like Navigator: nuovo shortcode `[app_nav]` con tabs (Scopri, Colori, IKEA, Stanze, Trend) e card responsive
  * File: `include/class/Shortcodes/appnav.php`, CSS `assets/css/app-nav.css`, JS `assets/js/app-nav.js`
* Widget contestuali automatici sugli articoli
  * File: `include/class/Widgets/ContextualWidgets.php` + attivazione in `include/site_specific/totaldesign_specific.php`
  * Inietta automaticamente Kitchen Finder in articoli su cucine/IKEA e Palette correlate negli articoli colore
* Kitchen Finder – UX e accessibilità
  * Ordine wizard: prima layout, poi misure; validazione adattiva (0 ammesso quando non serve)
  * SVG pulite monocromatiche, testo hover fix, microcopy unità (1 m = 100 cm)
  * Toggle “cucina piccola” calcolato automaticamente (non editabile)
  * Errori AJAX più chiari lato client
* Kitchen Finder – Backend
  * Verifica nonce robusta con risposta JSON e `nocache_headers()`
  * Log payload in `WP_DEBUG` per diagnosi
* Bootstrap & AJAX
  * Caricamento classi Shortcodes anche in contesto `DOING_AJAX` (fix 400/`0` su `admin-ajax.php`)
  * Helper per caricare i file degli shortcode centralizzato (`loadShortcodeFiles()`)

##### 1.8.3 _(2025-01-XX)_
* **Refactoring Bootstrap**: Intera logica di inizializzazione spostata in classe dedicata `PluginBootstrap`
  * File principale ridotto da 566 a 19 righe per migliore manutenibilità
  * Separazione delle responsabilità: gestione errori, Composer, inizializzazione
  * Codice più organizzato e testabile con metodi statici
* **Gestione Errori Migliorata**: Sistema robusto per prevenire errori critici WordPress
  * Try-catch per ogni componente con fallback graceful
  * Error handlers per errori fatali e non fatali
  * Log dettagliati con link ai file di log in admin
  * Plugin si disabilita silenziosamente senza far crashare il sito
* **Installazione Automatica Composer**: Supporto per installazione automatica dipendenze
  * Rilevamento automatico PHP binary e Composer
  * Supporto Windows/Unix con shell escaping corretto
  * Configurazione PHP binary tramite settings page
  * Feedback utente con messaggi di successo/errore dettagliati
* **Fix GitHub Actions Workflows**: Corretti tutti i workflow per evitare errori
  * Aggiornati action a versioni recenti (v4/v5)
  * Corretto cache key da composer.lock a composer.json
  * Convertito static.yml da workflow riusabile a standalone
  * Cambiato da Psalm a PHPStan (già configurato nel progetto)
* **Fix Bug Critici**: Corretti bug nelle callback di AdminHelper e HeaderHelper
  * Fix callback scope in AdminHelper::update_user_activity()
  * Fix callback scope in HeaderHelper::add_LogRocket()
  * Aggiunti controlli is_user_logged_in() per sicurezza
* **Bootstrap Centralizzato**: Inizializzazione condizionale per contesto (AJAX, Admin, Front-End)
  * Helper admin istanziati correttamente solo in contesto admin
  * ColorWidget::Initialize() ora chiamato correttamente
  * Endpoint AJAX ora funzionanti (rimosso early return)

##### 1.8.2 _(2025-01-XX)_
* **Fix critical**: Risolto errore autoloading PSR-4 per classi ServerHelper, MyString, HtmlHelper
  * Rinominati file da `.class.php` a `.php` per compliance PSR-4 autoloading
  * Classe `gik25microdata\Utility\ServerHelper` ora caricata correttamente
* **ColorWidget improvements**: Miglioramenti UX e fix bug widget colori
  * Fix z-index: tile zoomato ora appare sopra gli altri elementi
  * Fix scritta: testo sempre visibile sotto tile, overlay su hover
  * Ripristinato layout a griglia su più righe (era diventato orizzontale scrollabile)
  * Migliorate animazioni e transizioni con cubic-bezier
  * Dimensioni tile ripristinate a 120px come originale
  * Aggiunti border-radius, box-shadow e effetti hover più fluidi
  * Rimossi frecce navigazione (non necessarie con layout a griglia)
  * Ottimizzazioni performance: will-change, backface-visibility per animazioni smoother

##### 1.8.1 _(2025-11-02)_
* **Fix critical**: Rimossi conditional tags deprecati (is_single/is_page) da template_redirect - causavano warning WordPress 3.1+
* **Fix critical**: Aggiunto namespace mancante in `add_action` per `_conditionalLoadJsCss_Colori` in totaldesign_specific.php - causava fatal error
* **UX migliorata**: Gestione errore vendor mancante ora mostra messaggio admin friendly invece di exit fatale
* **Docs**: Istruzioni installazione separate per dev/prod, bug fix verificati
* **Refactoring**: ShortcodeBase ottimizzato per verificare global $post invece di conditional tags

##### 1.8.0 _(2025-11-02)_
* **Aggiunto Kitchen Finder Widget**: Nuovo shortcode `[kitchen_finder]` per aiutare gli utenti a trovare la cucina IKEA perfetta
* Wizard a 4 step (spazio, layout, stile, budget) con validazione client-side e server-side
* Design moderno con gradienti, animazioni fluide e card interattive con effetti hover
* Link interni dinamici che si aggiornano automaticamente basati su post WordPress reali
* AJAX per calcolo risultati e generazione PDF lead senza reload pagina
* Security fixes: sanitizzazione XSS, validazione input, gestione errori migliorata
* Performance: caricamento condizionale CSS/JS solo su pagine con lo shortcode
* Mobile-first responsive design con touch targets ottimizzati
* Accessibilità: ARIA labels, keyboard navigation, focus management

##### 1.7.2 _(2024-01-18)_
* Migrato autoloading da classmap a PSR-4 per migliorare performance e compliance con standard industry
* Corretto phpstan.neon paths per puntare a include/ invece di bootstrap.php/src/tests
* Autoloader ora usa PSR-4: mapping diretto namespace → directory senza scansione completa
* PHPStan configuration ottimizzata per analisi corretta del codebase

##### 1.7.1 _(2024-01-18)_
* Fixed critical bugs in shortcode name mismatches: Quote, Telefono, Progressbar, Slidingbox, Perfectpullquote, and Youtube now use correct shortcode names
* Fixed YouTube shortcode to use add_shortcode() for multiple names instead of array
* Fixed Progressbar.php syntax errors in styles() method
* Fixed variable naming bug in OptimizationHelper.php ($shortcodes → $enabledShortcodes)
* Fixed missing namespace declarations in ReviousMicrodataSettingsPage
* Fixed WordpressBehaviourModifier to use __CLASS__ instead of string references
* Fixed superinformati_specific.php to use __NAMESPACE__ for hook registrations
* Fixed missing return statement in CheckIfShortcodeIsUsedInThisPost()
* Fixed deprecated $_SERVER["HTTPS"] check in ServerHelper
* Added domain mapping for www.totaldesign.it in AutomaticallyDetectTheCurrentWebsite()
* **IMPORTANT:** I link negli shortcode site-specific (es. totaldesign_specific.php) sono hardcoded e andrebbero spostati in configurazione/database in futuro

##### 1.7.0 _(2023-03-19)_
* Fixed shortcode callback issue by properly referencing the namespaced function link_vitamine_handler in add_shortcode: add_shortcode('link_vitamine', __NAMESPACE__ . '\\link_vitamine_handler');
* Fixed composer autoload.php and versions
* Fixed missing css load bug (see OptimizazionHelper)
* Fixing huge bug on lists on nonsolodiete

##### 1.6.1 _(2023-03-15)_
* Automatically load domain-specific PHP files based on the current domain, removing the need for manual updates
* Refactor vitamine list in nonsolodiete to use Collection and LinkBase classes

##### 1.6.0 _(2023-03-14)_
* Resolved the blocking issue where the server's PHP version (8.0) was incompatible with the packages specified in composer.json (8.1 required for the --dev)
* Update Composer Version to 2.4
* Explained in the readme how to install --no-dev

##### 1.5.0 _(2023-01-22)_
* Refactored ShortcodeBase.php to convert everything to classes and added namespaces to the classes (not tested)
* Performed code cleaning: Removed unnecessary files: GenericShortcode.php, OttimizzazioneNewspaper.php, LowLevelShortcode.class.php, and shortcode-wpautop-control.php as they contained unused functionality.

##### 1.4.0 _(2022-10-27)_
* Major changes: using OOP and Composer
* Fixed and tested Lists of Posts in Superinformati
* Implemented PHPStan

##### 1.3.3 _(2022-10-9)_
* Forced caching of 404 pages

##### 1.3.2 _(2021-10-2)_
* Added function_exists('is_plugin_active') check, maybe unnecessary because it is related to another error

##### 1.3.1 _(2021-10-2)_

* TODO: removing all tags should be configurable
* Tags: removed the links to tags from every post
* Tags: put in 410 from htaccess (in sitemaps they seem absent)
* Added file RankMathOptimizer.php to noindex specific pages

##### 1.3.0 _(2021-09-22)_

* Implemented conditional loading in all shortcodes (for BE and FE)
* in OptimizationHelper.php changed the method to accept delegates from other classes too
* disabled a couple of unused shortcodes
* replaced PLUGIN_NAME_PREFIX with md_

##### 1.2.5 _(2021-09-15)_

* Completed the implementation of conditional css loading through OptimizationHelper.php
* Fixed huge bug which prevented the loading of CSS revious-microdata.css
* Avoided direct call to OptimizationHelper::ConditionalLoadCssJsOnPostsWhichContainEnabledShortcodes() from GenericShortcode.php (now done through the class constructor)
* TODO: found a bug in blinkingbutton.php all the conditional methods should call ExecuteAfterTemplateRedirect

##### 1.2.0 _(2021-09-13)_

* Progress bar: Fixed bug, introduced typescript 
* Renamed classes inside shortcodes to match the file name 

##### 1.1.9 _(2021-07-17)_

* Moved ListOfPostsHelper in folder \class
* Added to superinformati_specific.php the handler for  scripts in header and override author to "Redazione"

##### 1.1.8 _(2021-06-05)_

* Fixed breadcrumb on Psicocultura author pages [requires Yoast]
* Added elementor experiment files

##### 1.1.7 _(2021-05-11)_

* Fixed regressione (due to lack of template_redirect) conditional loading for FE Boxinformativo 

##### 1.1.6 _(2021-05-06)_

* Added conditional loading for FE / BE (only for Boxinformativo and blinkingbutton) 
* Fixed bug in OptimizationHelper::IsShortcodeUsedInCurrentPost('md_blinkingbutton');
* Initial improvement to OptimizationHelper

##### 1.1.5 _(2020-08-22)_
* Fixed issue with 5px margin in tag body (Progress bar + Elementor) 

##### 1.1.4 _(2020-08-21)_
* Renamed progress bar assets to a more speaking name
* Separated loading of css and js for FE/BE (should be continued)

#### 1.1.3 ###
* Fixed the path of faq.js

#### 1.1.2 ###
* Refactored TinyMCE js to a subfolder

#### 1.1.1 ###
* Renamed progress bar to a more speaking name
