# Piano di Generalizzazione Sistema Caroselli/Liste

## 🎯 Obiettivo

Eliminare completamente la creazione hardcoded di caroselli/liste, rendendo tutto configurabile via database WordPress. Il sistema deve essere:
- **Generico**: Funziona per tutti i siti (TotalDesign, SuperInformati, NonSoloDieti, ChieCosa, ecc.)
- **Configurabile**: CSS, DOM, template, JavaScript configurabili via database
- **Estendibile**: Facile aggiungere nuovi template e stili
- **Retrocompatibile**: I caroselli esistenti continuano a funzionare durante la migrazione

---

## 📊 Analisi Situazione Attuale

### Problemi Identificati

#### 1. **Doppio Sistema Incompatibile**
- **`ColorWidget`** (TotalDesign): CSS hardcoded, DOM hardcoded (`.tile`, `.row`, `.row__inner`), JS hardcoded
- **`ListOfPostsHelper`** (SuperInformati, NonSoloDieti, ChieCosa): Classi diverse (`nicelist`, `thumbnail-list`, `my_shortcode_list`)

#### 2. **CSS Hardcoded**
- `ColorWidget::get_carousel_css()`: ~250 righe CSS inline hardcoded
- Stili specifici per `.tile`, `.row`, `.row__inner`, hover states, responsive
- Non configurabile, non personalizzabile per sito

#### 3. **DOM Structure Hardcoded**
- `ColorWidget::GetLinkWithImageCarousel()`: Genera HTML hardcoded con struttura fissa
- `ListOfPostsHelper::getLinksWithImagesCurrentColumn()`: Genera HTML diverso
- Template non configurabili

#### 4. **JavaScript Hardcoded**
- `ColorWidget::carousel_js()`: ~200 righe JS inline hardcoded
- Gestione touch, hover, scroll - tutto hardcoded
- Non configurabile

#### 5. **Handler Functions Hardcoded**
- `link_colori_handler()`, `grafica3d_handler()`, `archistars_handler()` (TotalDesign)
- `link_analisi_sangue_handler_2()`, `link_vitamine_handler()`, `link_diete_handler()` (SuperInformati)
- `link_vitamine_handler()`, `link_diete_handler()` (NonSoloDieti)
- `temptation_island_single_handler()`, `amici_celebrities_handler()` (ChieCosa)
- Tutti hardcoded con URL e titoli nel codice

#### 6. **Template Non Configurabili**
- Struttura HTML fissa: `<div class="contain">`, `<h3>`, `<p>`, `<div class="row">`, ecc.
- Categorie hardcoded nel codice (es: "Colori Specifici", "Colori Pantone", "Articoli Vari")
- Layout non personalizzabile

---

## 🏗️ Architettura Proposta

### Livello 1: Database - Template CSS/JS/DOM

#### Tabella: `wp_carousel_templates`
Template riutilizzabili per CSS, DOM structure e JavaScript.

| Campo | Tipo | Descrizione |
|-------|------|-------------|
| `id` | bigint | ID univoco |
| `template_key` | varchar(100) | Chiave univoca (es: "tile-carousel", "thumbnail-list", "grid-modern") |
| `template_name` | varchar(255) | Nome visualizzato |
| `template_type` | varchar(20) | Tipo: `css`, `dom`, `js`, `full` |
| `css_content` | longtext | CSS del template (può essere NULL se tipo != css/full) |
| `dom_structure` | longtext | Template HTML/DOM (può essere NULL se tipo != dom/full) |
| `js_content` | longtext | JavaScript del template (può essere NULL se tipo != js/full) |
| `css_variables` | text | JSON con variabili CSS configurabili (es: `{"tile-size": "120px", "gap": "2px"}`) |
| `is_system` | tinyint(1) | Template di sistema (non eliminabile) |
| `is_active` | tinyint(1) | Attivo |
| `created_at` | datetime | Data creazione |
| `updated_at` | datetime | Data aggiornamento |

**Template di Sistema Predefiniti:**
1. `tile-carousel` (attuale ColorWidget) - per TotalDesign
2. `thumbnail-list` (attuale ListOfPostsHelper) - per SuperInformati/NonSoloDieti
3. `grid-modern` - nuovo template griglia moderna
4. `simple-list` - lista semplice senza immagini

#### Tabella: `wp_carousel_collections` (ESTESA)
Aggiungere campo `template_id` per associare template.

| Campo Aggiunto | Tipo | Descrizione |
|----------------|------|-------------|
| `template_id` | bigint | FK a `wp_carousel_templates.id` |
| `template_config` | text | JSON con configurazione template (override variabili CSS, opzioni) |

#### Tabella: `wp_carousel_items` (GIÀ ESISTENTE)
Nessuna modifica necessaria.

---

### Livello 2: Template Engine

#### Classe: `CarouselTemplateEngine`
Gestisce rendering con template configurabili.

**Metodi:**
- `render_collection($collection_id, $options)`: Renderizza collezione con template associato
- `get_css($template_id, $config)`: Genera CSS da template + variabili
- `get_dom($template_id, $items, $config)`: Genera DOM da template + items
- `get_js($template_id, $config)`: Genera JS da template + configurazione
- `parse_template_variables($template_content, $variables)`: Sostituisce variabili nel template

**Template Variables:**
- `{{item.title}}`, `{{item.url}}`, `{{item.image}}`, `{{item.description}}`
- `{{category.name}}`, `{{collection.name}}`
- `{{css.tile-size}}`, `{{css.gap}}`, `{{css.hover-scale}}`

---

### Livello 3: Unificazione Sistemi

#### Classe: `UnifiedCarouselRenderer`
Unifica `ColorWidget` e `ListOfPostsHelper` in un sistema unico.

**Strategia:**
1. **Fase 1 - Wrapper**: Creare wrapper che mantiene compatibilità
   - `ColorWidget::GetLinkWithImageCarousel()` → chiama `UnifiedCarouselRenderer::render_item()`
   - `ListOfPostsHelper::getLinksWithImagesCurrentColumn()` → chiama `UnifiedCarouselRenderer::render_items()`

2. **Fase 2 - Migrazione**: Migrare gradualmente i siti al nuovo sistema
   - TotalDesign: da `ColorWidget` a `GenericCarousel` con template `tile-carousel`
   - SuperInformati: da `ListOfPostsHelper` a `GenericCarousel` con template `thumbnail-list`
   - Altri siti: stesso processo

3. **Fase 3 - Deprecazione**: Deprecare `ColorWidget` e `ListOfPostsHelper` (mantenere per retrocompatibilità)

---

### Livello 4: GenericCarousel Enhancement

#### Estendere `GenericCarousel` per:
1. **Template System**: Supporto template configurabili
2. **Multi-Template**: Supporto per template diversi per categoria
3. **CSS/JS Lazy Loading**: Carica CSS/JS solo se necessario
4. **Template Inheritance**: Template possono estendere altri template
5. **Custom Variables**: Variabili personalizzate per sito/collezione

---

## 📋 Piano di Implementazione

### Fase 1: Database e Template System (Priorità Alta)

#### 1.1 Creare Tabella `wp_carousel_templates`
- [ ] Creare migration per tabella
- [ ] Popolare template di sistema predefiniti
- [ ] Migrare CSS attuale `ColorWidget::get_carousel_css()` → template `tile-carousel`
- [ ] Migrare DOM attuale `ColorWidget::GetLinkWithImageCarousel()` → template `tile-carousel`
- [ ] Migrare JS attuale `ColorWidget::carousel_js()` → template `tile-carousel`
- [ ] Creare template `thumbnail-list` da `ListOfPostsHelper`

#### 1.2 Estendere Tabella `wp_carousel_collections`
- [ ] Aggiungere campo `template_id`
- [ ] Aggiungere campo `template_config` (JSON)
- [ ] Migration per dati esistenti (assegnare template di default)

#### 1.3 Creare Classe `CarouselTemplateEngine`
- [ ] Metodo `render_collection()`
- [ ] Metodo `get_css()` con parsing variabili
- [ ] Metodo `get_dom()` con parsing variabili
- [ ] Metodo `get_js()` con parsing variabili
- [ ] Metodo `parse_template_variables()`

---

### Fase 2: Unificazione Sistemi (Priorità Alta)

#### 2.1 Creare Classe `UnifiedCarouselRenderer`
- [ ] Metodo `render_item($item, $template_id, $config)`
- [ ] Metodo `render_items($items, $template_id, $config)`
- [ ] Metodo `render_collection($collection_id, $options)`

#### 2.2 Wrapper per Retrocompatibilità
- [ ] `ColorWidget::GetLinkWithImageCarousel()` → wrapper a `UnifiedCarouselRenderer`
- [ ] `ListOfPostsHelper::getLinksWithImagesCurrentColumn()` → wrapper a `UnifiedCarouselRenderer`
- [ ] Mantenere funzionalità esistente durante migrazione

---

### Fase 3: Enhancement GenericCarousel (Priorità Media)

#### 3.1 Integrare Template System in `GenericCarousel`
- [ ] Modificare `ShortcodeHandler()` per usare template da database
- [ ] Supporto `template_id` e `template_config` in attributi shortcode
- [ ] Lazy loading CSS/JS basato su template

#### 3.2 Supporto Multi-Template
- [ ] Template diversi per categoria (es: "Colori Specifici" usa template A, "Pantone" usa template B)
- [ ] Configurazione per categoria in `wp_carousel_items.category`

---

### Fase 4: Migrazione Dati Hardcoded (Priorità Alta)

#### 4.1 TotalDesign
- [ ] Migrare `link_colori_handler()` → collezione database `colori` con template `tile-carousel`
- [ ] Migrare `grafica3d_handler()` → collezione database `programmi3d` con template `tile-carousel`
- [ ] Migrare `archistars_handler()` → collezione database `architetti` con template `tile-carousel`
- [ ] Aggiornare shortcode esistenti: `[link_colori]` → `[carousel collection="colori"]`
- [ ] Test retrocompatibilità

#### 4.2 SuperInformati
- [ ] Migrare `link_analisi_sangue_handler_2()` → collezione database `analisi-sangue` con template `thumbnail-list`
- [ ] Migrare `link_vitamine_handler()` → collezione database `vitamine` con template `thumbnail-list`
- [ ] Migrare `link_diete_handler()` → collezione database `diete` con template `thumbnail-list`
- [ ] Migrare `link_dimagrimento_handler()` → collezione database `dimagrimento` con template `thumbnail-list`
- [ ] Migrare `link_tatuaggi_handler()` → collezione database `tatuaggi` con template `thumbnail-list`
- [ ] Migrare `sedi_inps_handler()` → collezione database `sedi-inps` con template `thumbnail-list`

#### 4.3 NonSoloDieti
- [ ] Migrare `link_vitamine_handler()` → collezione database `vitamine-nsd` con template `thumbnail-list`
- [ ] Migrare `link_diete_handler()` → collezione database `diete-nsd` con template `thumbnail-list`

#### 4.4 ChieCosa
- [ ] Migrare `temptation_island_single_handler()` → collezione database `temptation-island` con template `simple-list`
- [ ] Migrare `amici_celebrities_handler()` → collezione database `amici-celebrities` con template `simple-list`
- [ ] Migrare altri handler simili

---

### Fase 5: Admin Interface Enhancement (Priorità Media)

#### 5.1 Template Manager
- [ ] Pagina admin "Template Caroselli"
- [ ] CRUD template (Create, Read, Update, Delete)
- [ ] Editor CSS/DOM/JS con syntax highlighting
- [ ] Preview template in tempo reale
- [ ] Import/Export template

#### 5.2 Collection Manager Enhancement
- [ ] Selezione template nella creazione collezione
- [ ] Configurazione variabili template (UI per JSON `template_config`)
- [ ] Preview collezione con template selezionato
- [ ] Test template su collezione esistente

---

### Fase 6: Deprecazione e Cleanup (Priorità Bassa)

#### 6.1 Deprecare Classi Vecchie
- [ ] Aggiungere `@deprecated` a `ColorWidget::GetLinkWithImageCarousel()`
- [ ] Aggiungere `@deprecated` a `ListOfPostsHelper::getLinksWithImagesCurrentColumn()`
- [ ] Log warning quando vengono usate (opzionale)

#### 6.2 Rimuovere Handler Hardcoded
- [ ] Rimuovere `link_colori_handler()`, `grafica3d_handler()`, `archistars_handler()` da `totaldesign_specific.php`
- [ ] Rimuovere handler da `superinformati_specific.php`
- [ ] Rimuovere handler da altri file site_specific
- [ ] Rimuovere shortcode registrati hardcoded

#### 6.3 Cleanup Codice
- [ ] Rimuovere `ColorWidget::get_carousel_css()` (sostituito da template)
- [ ] Rimuovere `ColorWidget::carousel_js()` (sostituito da template)
- [ ] Rimuovere `ColorWidget` completamente (se non più usato)
- [ ] Rimuovere `ListOfPostsHelper` (se non più usato)

---

## 🔧 Dettagli Tecnici

### Template CSS con Variabili

```css
/* Template: tile-carousel */
.tile {
    width: {{css.tile-size}};
    height: {{css.tile-size}};
    gap: {{css.gap}};
}

.tile:hover {
    transform: scale({{css.hover-scale}});
}
```

**Configurazione:**
```json
{
    "css.tile-size": "120px",
    "css.gap": "2px",
    "css.hover-scale": "1.2"
}
```

### Template DOM con Variabili

```html
<!-- Template: tile-carousel -->
<div class="tile">
    <a href="{{item.url}}" class="tile__link">
        <img src="{{item.image}}" alt="{{item.title}}" class="tile__img">
        <div class="tile__details">
            <span class="tile__title">{{item.title}}</span>
        </div>
    </a>
</div>
```

### Template JavaScript con Configurazione

```javascript
// Template: tile-carousel
(function() {
    var config = {
        hoverScale: {{js.hover-scale}},
        touchEnabled: {{js.touch-enabled}}
    };
    // ... codice JS ...
})();
```

---

## 📊 Stima Impatto

### File da Modificare
- `include/class/Database/CarouselCollections.php` - Estendere tabelle
- `include/class/Shortcodes/GenericCarousel.php` - Integrare template system
- `include/class/ColorWidget.php` - Wrapper per retrocompatibilità
- `include/class/ListOfPosts/ListOfPostsHelper.php` - Wrapper per retrocompatibilità
- `include/site_specific/totaldesign_specific.php` - Rimuovere handler hardcoded
- `include/site_specific/superinformati_specific.php` - Rimuovere handler hardcoded
- `include/site_specific/nonsolodiete_specific.php` - Rimuovere handler hardcoded
- `include/site_specific/chiecosa_specific.php` - Rimuovere handler hardcoded

### File da Creare
- `include/class/Carousel/CarouselTemplateEngine.php` - Engine template
- `include/class/Carousel/UnifiedCarouselRenderer.php` - Renderer unificato
- `include/class/Admin/TemplateManager.php` - Admin interface template
- `include/class/Database/CarouselTemplates.php` - Gestione tabelle template

### Database
- Nuova tabella: `wp_carousel_templates`
- Estensione tabella: `wp_carousel_collections` (+2 campi)

---

## ✅ Criteri di Successo

1. **Zero Hardcoding**: Nessun URL, titolo, CSS, DOM, JS hardcoded nei file site_specific
2. **Retrocompatibilità**: Tutti i caroselli esistenti continuano a funzionare
3. **Configurabilità**: Ogni aspetto (CSS, DOM, JS) configurabile via database
4. **Multi-Sito**: Sistema funziona per tutti i siti senza modifiche codice
5. **Performance**: Nessun degrado performance (lazy loading CSS/JS)
6. **Manutenibilità**: Facile aggiungere nuovi template e stili

---

## 🚀 Priorità Implementazione

1. **Fase 1** (Critica): Database e Template System
2. **Fase 2** (Critica): Unificazione Sistemi
3. **Fase 4** (Alta): Migrazione Dati Hardcoded
4. **Fase 3** (Media): Enhancement GenericCarousel
5. **Fase 5** (Media): Admin Interface Enhancement
6. **Fase 6** (Bassa): Deprecazione e Cleanup

---

## 📝 Note

- Mantenere retrocompatibilità durante tutta la migrazione
- Testare ogni fase prima di procedere alla successiva
- Documentare template system per facilità uso futuro
- Creare template di esempio per ogni tipo di visualizzazione

