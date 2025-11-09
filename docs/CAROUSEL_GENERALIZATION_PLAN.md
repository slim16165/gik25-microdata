# Piano Generalizzazione Sistema Caroselli

## Obiettivo

Eliminare hardcoding di caroselli/liste, rendendo tutto configurabile via database WordPress. Sistema generico, configurabile, estendibile e retrocompatibile.

## Architettura Proposta

### Livello 1: Database - Template CSS/JS/DOM

**Tabella: `wp_carousel_templates`**
- Template riutilizzabili per CSS, DOM structure e JavaScript
- Campi: `template_key`, `template_name`, `template_type`, `css_content`, `dom_structure`, `js_content`, `css_variables`, `is_system`, `is_active`

**Tabella: `wp_carousel_collections` (ESTESA)**
- Aggiungere `template_id` (FK a templates)
- Aggiungere `template_config` (JSON con configurazione template)

### Livello 2: Template Engine

**Classe: `CarouselTemplateEngine`**
- `render_collection($collection_id, $options)`: Renderizza collezione con template
- `get_css($template_id, $config)`: Genera CSS da template + variabili
- `get_dom($template_id, $items, $config)`: Genera DOM da template + items
- `get_js($template_id, $config)`: Genera JS da template + configurazione

**Template Variables:**
- `{{item.title}}`, `{{item.url}}`, `{{item.image}}`
- `{{css.tile-size}}`, `{{css.gap}}`, `{{css.hover-scale}}`

### Livello 3: Unificazione Sistemi

**Classe: `UnifiedCarouselRenderer`**
- Unifica `ColorWidget` e `ListOfPostsHelper` in sistema unico
- Wrapper per retrocompatibilità durante migrazione

### Livello 4: GenericCarousel Enhancement

- Supporto template configurabili
- Multi-Template per categoria
- CSS/JS Lazy Loading
- Template Inheritance

## Piano Implementazione

### Fase 1: Database e Template System (Priorità Alta)

- [ ] Creare tabella `wp_carousel_templates`
- [ ] Creare template di sistema predefiniti (tile-carousel, thumbnail-list, grid-modern, simple-list)
- [ ] Estendere tabella `wp_carousel_collections` (aggiungere `template_id`, `template_config`)
- [ ] Creare classe `CarouselTemplateEngine`

### Fase 2: Unificazione Sistemi (Priorità Alta)

- [ ] Creare classe `UnifiedCarouselRenderer`
- [ ] Creare wrapper per `ColorWidget::GetLinkWithImageCarousel()`
- [ ] Creare wrapper per `ListOfPostsHelper::getLinksWithImagesCurrentColumn()`
- [ ] Test retrocompatibilità

### Fase 3: Enhancement GenericCarousel (Priorità Media)

- [ ] Integrare template system in `GenericCarousel`
- [ ] Supporto multi-template
- [ ] CSS/JS lazy loading
- [ ] Template inheritance

### Fase 4: Migrazione Dati Hardcoded (Priorità Alta)

- [ ] Migrare handler TotalDesign (link_colori, grafica3d, archistars)
- [ ] Migrare handler SuperInformati (analisi-sangue, vitamine, diete, ecc.)
- [ ] Migrare handler NonSoloDieti
- [ ] Migrare handler ChieCosa

### Fase 5: Admin Interface Enhancement (Priorità Media)

- [ ] Template Manager (CRUD template)
- [ ] Collection Manager Enhancement (selezione template, configurazione variabili)
- [ ] Preview template in tempo reale

### Fase 6: Deprecazione e Cleanup (Priorità Bassa)

- [ ] Deprecare `ColorWidget::GetLinkWithImageCarousel()`
- [ ] Deprecare `ListOfPostsHelper::getLinksWithImagesCurrentColumn()`
- [ ] Rimuovere handler hardcoded da file site_specific
- [ ] Cleanup codice (rimuovere CSS/JS hardcoded)

## Criteri di Successo

1. Zero Hardcoding: Nessun URL, titolo, CSS, DOM, JS hardcoded
2. Retrocompatibilità: Tutti i caroselli esistenti continuano a funzionare
3. Configurabilità: Ogni aspetto configurabile via database
4. Multi-Sito: Sistema funziona per tutti i siti senza modifiche codice
5. Performance: Nessun degrado performance (lazy loading CSS/JS)
6. Manutenibilità: Facile aggiungere nuovi template e stili

## Priorità

1. **Fase 1** (Critica): Database e Template System
2. **Fase 2** (Critica): Unificazione Sistemi
3. **Fase 4** (Alta): Migrazione Dati Hardcoded
4. **Fase 3** (Media): Enhancement GenericCarousel
5. **Fase 5** (Media): Admin Interface Enhancement
6. **Fase 6** (Bassa): Deprecazione e Cleanup

## Riferimenti

- `include/class/Carousel/CarouselTemplateEngine.php`: Engine template
- `include/class/Database/CarouselCollections.php`: Gestione collezioni
- `include/class/Database/CarouselTemplates.php`: Gestione template
- `include/class/Shortcodes/GenericCarousel.php`: Shortcode generico
- `include/class/Widgets/ColorWidget.php`: Widget colori (da deprecare)
- `include/class/ListOfPosts/ListOfPostsHelper.php`: Helper liste (da deprecare)
