# Admin UI Enhancement Plan

## ✅ Completato

### Phase 1 – Overview + Shortcodes ✅
- ✅ **ShortcodeRegistry**: Creato e funzionante (`include/class/Shortcodes/ShortcodeRegistry.php`)
  - Registry con metadata (label, description, example, aliases)
  - Gestione enable/disable via option `gik25_shortcodes_enabled`
  - Filtro `pre_do_shortcode_tag` per disabilitare shortcode
- ✅ **Admin Menu con Tab**: Dashboard unificata con tab (Dashboard/Impostazioni/Strumenti)
  - Tab Dashboard: Overview, statistiche, link utili
  - Tab Impostazioni: Settings page integrata
  - Tab Strumenti: Tools page integrata
- ✅ **Shortcodes Unified Page**: Pagina con tab Gestione/Utilizzo
  - Tab Gestione: Toggle shortcode, documentazione, esempi
  - Tab Utilizzo: Scanner usage, filtri, lista post/pagine con shortcode

### Phase 2 – Usage Scanner ✅
- ✅ **Usage Tab**: Implementato in `ShortcodesUsagePage.php`
  - Query `$wpdb->prepare` per cercare shortcode in `post_content`
  - Filtri per shortcode, post type, date range
  - Lista post con conteggio occorrenze, tipo, data, status
  - Funzione `countOccurrences()` per conteggio accurato

### Phase 3 – Health & Tools (Parzialmente Completato)
- ✅ **Health Check Esteso**: `HealthChecker.php` esteso con:
  - Check required options
  - Parser log Cloudways per errori server
  - Check errori PHP, HTTP 500, slow queries
  - REST API health check endpoints
  - Visualizzazione errori PHP migliorata
- ⚠️ **Tools Tab**: Parzialmente implementato
  - ✅ Tools page esiste (`ToolsPage.php`)
  - ❌ Export/import enabled shortcodes (da implementare)
  - ❌ Clear shortcode index (da implementare)
  - ❌ Rebuild CSS/JS caches (da implementare)
- ❌ **Help Tab**: Non implementato
  - Help tab con spiegazioni
  - Link a documentazione `docs/`

## 📋 Task Rimanenti

### Phase 3.1 – Tools Tab Enhancement (Media Priorità)

#### Export/Import Shortcodes
- [ ] Aggiungere pulsante "Export" in Tools tab
  - Esporta configurazione shortcode abilitati in JSON
  - Include metadata (version, date, site)
- [ ] Aggiungere pulsante "Import" in Tools tab
  - Importa configurazione da JSON
  - Validazione formato, backup prima import
  - Preview cambiamenti prima di applicare
- [ ] Aggiungere WP-CLI command: `wp gik25 shortcodes export/import`

#### Clear Shortcode Index
- [ ] Aggiungere pulsante "Clear Usage Index" in Tools tab
  - Pulisce cache usage (se implementata)
  - Conferma prima di cancellare
- [ ] Aggiungere WP-CLI command: `wp gik25 shortcodes clear-index`

#### Rebuild CSS/JS Caches
- [ ] Aggiungere pulsante "Rebuild Assets" in Tools tab
  - Rigenera cache CSS/JS per shortcode abilitati
  - Pulisce cache browser/CDN (se configurato)
- [ ] Aggiungere WP-CLI command: `wp gik25 assets rebuild`

### Phase 3.2 – Help Tab (Bassa Priorità)

#### Help Tab Implementation
- [ ] Creare classe `HelpTab.php` o integrare in `AdminMenu.php`
- [ ] Aggiungere help tab con:
  - Overview plugin e funzionalità
  - Link a documentazione `docs/`
  - FAQ comuni
  - Changelog versione corrente
  - Link supporto/contatti
- [ ] Integrare help tab nel menu admin principale

### Phase 4 – Miglioramenti Futuri (Bassa Priorità)

#### Advanced Usage Scanner
- [ ] Implementare cache usage index in tabella `wp_gik25_shortcode_index`
- [ ] Aggiungere cron job per aggiornamento automatico index
- [ ] Aggiungere filtri avanzati (author, category, tag)
- [ ] Aggiungere export usage report (CSV/JSON)

#### Advanced Health Check
- [ ] Aggiungere check per plugin conflitti (Yoast/RankMath)
- [ ] Aggiungere check per cron events inattivi
- [ ] Aggiungere check per missing assets (CSS/JS files)
- [ ] Aggiungere alerting (email/webhook) per errori critici

#### UI/UX Improvements
- [ ] Migliorare design tab con icone e colori
- [ ] Aggiungere search/filter in tab Gestione shortcode
- [ ] Aggiungere preview shortcode in admin
- [ ] Aggiungere drag & drop per ordinamento shortcode

## 📝 Note

- Le funzionalità completate sono già in produzione
- Tools tab ha base solida, serve solo aggiungere funzionalità avanzate
- Help tab è opzionale ma migliorerebbe UX
- Miglioramenti futuri sono nice-to-have, non critici

## 🐛 Fix Stabilità (v2.0.1)

### Fix Critici Applicati
- ✅ **ShortcodeBase**: Metodo `scripts()` reso opzionale (verifica `method_exists()`)
  - Risolve fatal error per shortcode senza JavaScript (Perfectpullquote, Prezzo, Youtube)
- ✅ **totaldesign_specific.php**: Check null per `$post` prima di accedere a `$post->ID`
  - Previene errori in archive/search pages dove `$post` può essere null

### Note Tecniche
- I fix di stabilità sono stati applicati e testati in produzione
- Nessun impatto sulle funzionalità admin UI esistenti
- Tutti i shortcode ora funzionano correttamente anche senza metodo `scripts()`

## Riferimenti

- `include/class/Shortcodes/ShortcodeRegistry.php`: Registry shortcode
- `include/class/Admin/AdminMenu.php`: Menu admin principale
- `include/class/Admin/ShortcodesUnifiedPage.php`: Pagina shortcode unificata
- `include/class/Admin/ShortcodesUsagePage.php`: Usage scanner
- `include/class/Admin/ToolsPage.php`: Tools page
- `include/class/HealthCheck/HealthChecker.php`: Health check system
