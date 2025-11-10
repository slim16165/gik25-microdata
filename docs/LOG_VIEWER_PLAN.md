# Piano Rifattorizzazione Log Viewer Avanzato

## Obiettivi

1. Portare la gestione log a livello superiore, indipendente da HealthChecker
2. Creare UI avanzata per errori PHP con Grid.js (filtri, export, dettagli)
3. Mostrare anche warning PHP in summary (non solo critici)
4. Riutilizzare codice esistente di qualità
5. Creare link simbolico a Telerik per uso futuro

## Struttura Proposta

### Nuove Classi

1. **`include/class/LogViewer/LogFormatter.php`**
   - Estrarre `format_log_line()` e `format_log_line_preview()` da HealthChecker
   - Utility condivisa per formattazione log

2. **`include/class/LogViewer/LogViewer.php`**
   - Classe principale per rendering UI Log Viewer
   - Tab in Health Check page
   - Integrazione Grid.js per tabella avanzata

3. **`include/class/LogViewer/LogViewerAPI.php`**
   - REST API endpoints per log data
   - Filtri: severity, file, data, contesto
   - Export: CSV/JSON
   - Endpoint: `/wp-json/gik25/v1/logs/errors`

### Modifiche Esistenti

1. **`include/class/HealthCheck/HealthChecker.php`**
   - Aggiungere tab "Log Viewer" dopo "Dettagli"
   - Modificare summary per mostrare anche warning PHP
   - Rimuovere logica rendering log (spostare in LogViewer)
   - Mantenere solo chiamata a CloudwaysLogParser

2. **`include/class/HealthCheck/CloudwaysLogParser.php`**
   - Aggiungere metodo pubblico `get_php_errors_structured()` per estrarre errori PHP in formato strutturato
   - Metodo già esistente `analyze_php_errors()` può essere riutilizzato

3. **`externals/telerik/`**
   - Creare link simbolico (Windows: `mklink /D`)

## Funzionalità Log Viewer

### Filtri
- Severity: Fatal, Error, Warning, Info
- File: dropdown con lista file coinvolti
- Data: range date picker
- Contesto: WP-CLI, AJAX, WP-CRON, Frontend, Backend, REST API

### Export
- CSV: esporta log filtrati
- JSON: esporta log filtrati con metadati completi

### Dettagli Espandibili
- Stack trace completo
- Contesto esecuzione
- File e linea esatta
- Timestamp formattato
- Messaggio errore completo

## UI Grid.js

### Colonne
- Timestamp (sortable)
- Severity (badge colorato, filterable)
- File (sortable, filterable)
- Linea (sortable)
- Messaggio (truncato, expandibile)
- Contesto (badge, filterable)
- Azioni (espandi dettagli)

### Features
- Ricerca full-text
- Ordinamento multi-colonna
- Paginazione (50 righe per pagina)
- Filtri avanzati (sidebar)
- Export button (CSV/JSON)

## REST API Endpoints

### GET `/wp-json/gik25/v1/logs/errors`
Query params:
- `severity`: fatal,error,warning,info (comma-separated)
- `file`: nome file (pattern matching)
- `since`: timestamp Unix
- `until`: timestamp Unix
- `context`: wp_cli,ajax,wp_cron,frontend,backend,rest_api (comma-separated)
- `hours`: limita l'analisi alle ultime N ore (0 = tutto il log)
- `limit`: numero massimo risultati (default: 1000)
- `offset`: offset paginazione
- `format`: json,csv (default: json)

Response JSON:
```json
{
  "total": 150,
  "limit": 1000,
  "offset": 0,
  "errors": [
    {
      "id": "err_123",
      "timestamp": 1733846400,
      "severity": "fatal",
      "file": "/path/to/file.php",
      "line": 42,
      "message": "Fatal error message",
      "stack_trace": ["...", "..."],
      "context": ["ajax", "backend"],
      "count": 5
    }
  ]
}
```

## Codice Riutilizzabile

### Da HealthChecker
- `format_log_line()` → LogFormatter
- `format_log_line_preview()` → LogFormatter
- Logica rendering errori PHP (estraere in LogViewer)

### Da CloudwaysLogParser
- `analyze_php_errors()` - già raggruppa errori
- `recent_errors_tail()` - già restituisce errori strutturati
- `parse_php_error_timestamp()` - parsing timestamp
- `get_server_timezone()` - timezone detection
- `check_log_timestamp_warning()` - warning timestamp

## Implementazione

### Fase 1: Preparazione
1. Creare link simbolico Telerik
2. Estrarre LogFormatter da HealthChecker
3. Creare struttura directory LogViewer

### Fase 2: API
1. Creare LogViewerAPI con REST endpoints
2. Implementare filtri backend
3. Implementare export CSV/JSON

### Fase 3: UI
1. Aggiungere tab "Log Viewer" in HealthChecker
2. Integrare Grid.js (CDN)
3. Implementare tabella con colonne
4. Implementare filtri UI
5. Implementare dettagli espandibili

### Fase 4: Refactoring
1. Spostare logica rendering log da HealthChecker a LogViewer
2. Modificare summary per mostrare anche warning
3. Pulizia codice inutilizzato

## File da Creare/Modificare

### Nuovi File
- `include/class/LogViewer/LogFormatter.php`
- `include/class/LogViewer/LogViewer.php`
- `include/class/LogViewer/LogViewerAPI.php`
- `assets/js/log-viewer.js` (opzionale, può essere inline)

### File Modificati
- `include/class/HealthCheck/HealthChecker.php`
- `include/class/HealthCheck/CloudwaysLogParser.php` (aggiungere metodi pubblici)
- `include/class/PluginBootstrap.php` (registrare LogViewerAPI)

### Link Simbolico
- `externals/telerik` → `C:\Users\g.salvi\Dropbox\Siti internet\Altri siti\Principali\TotalDesign.it\my-tools\telerik`

## Note

- Grid.js viene caricato via CDN (più leggero)
- Telerik linkato ma non usato inizialmente (per uso futuro)
- LogViewer è standalone ma integrato come tab in Health Check
- Mantenere backward compatibility con HealthChecker esistente

