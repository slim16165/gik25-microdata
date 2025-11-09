# Admin UI Enhancement Plan

## Task Rimanenti

### Tools Tab Enhancement (Media Priorità)

- [ ] **Rebuild CSS/JS Caches**: Pulsante "Rebuild Assets" in Tools tab
  - Rigenera cache CSS/JS per shortcode abilitati
  - Pulisce cache browser/CDN (se configurato)
  - WP-CLI command: `wp gik25 assets rebuild`

### Help Tab (Bassa Priorità)

- [ ] **Help Tab Implementation**: Integrare in `AdminMenu.php`
  - Overview plugin e funzionalità
  - Link a documentazione `docs/`
  - FAQ comuni, changelog, link supporto

### Miglioramenti Futuri (Bassa Priorità)

- [ ] **Advanced Usage Scanner**: Filtri avanzati (author, category, tag), export usage report (CSV/JSON)
- [ ] **Advanced Health Check**: Check plugin conflitti, cron events inattivi, missing assets, alerting
- [ ] **UI/UX Improvements**: Design tab migliorato, search/filter, preview shortcode, drag & drop

## Riferimenti

- `include/class/Shortcodes/ShortcodeRegistry.php`: Registry shortcode
- `include/class/Admin/AdminMenu.php`: Menu admin principale
- `include/class/Admin/ShortcodesUnifiedPage.php`: Pagina shortcode unificata
- `include/class/Admin/ShortcodesUsagePage.php`: Usage scanner
- `include/class/Admin/ToolsPage.php`: Tools page
- `include/class/HealthCheck/HealthChecker.php`: Health check system

## Note

- Phase 1-2 completate (ShortcodeRegistry, Admin Menu con Tab, Usage Scanner)
- Phase 3 parzialmente completata (Health Check esteso, Tools page base)
- Task rimanenti sono nice-to-have, non critici
