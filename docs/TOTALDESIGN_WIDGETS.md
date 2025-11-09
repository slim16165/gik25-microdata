# Widget TotalDesign - Elenco Completo

## 🎨 Widget e Shortcode TotalDesign

### 1. **Kitchen Finder** 🏠
**Shortcode**: `[kitchen_finder]`  
**Classe**: `gik25microdata\Shortcodes\KitchenFinder`  
**File**: `include/class/Shortcodes/KitchenFinder.php`  
**Descrizione**: Wizard 4-step per trovare la cucina perfetta (layout, misure, stile, budget)  
**Features**:
- Wizard interattivo con 4 step
- Calcolo automatico configurazione cucina
- Generazione lead con PDF
- AJAX per calcoli in tempo reale
- Responsive mobile/desktop

### 2. **App Navigator** 🧭
**Shortcode**: `[app_nav]`  
**Classe**: `gik25microdata\Shortcodes\AppNav`  
**File**: `include/class/Shortcodes/AppNav.php`  
**Descrizione**: Navigazione app-like multi-livello con varianti mobile/desktop  
**Features**:
- Navigazione multi-livello (sezioni → sottosezioni)
- Variante mobile (touch-friendly con back button)
- Variante desktop (tutti i livelli visibili)
- Animazioni slide per mobile
- Tabs: Scopri, Colori, IKEA, Stanze, Trend

### 3. **Contextual Widgets** 🤖
**Classe**: `gik25microdata\Widgets\ContextualWidgets`  
**File**: `include/class/Widgets/ContextualWidgets.php`  
**Descrizione**: Widget contestuali automatici sugli articoli  
**Features**:
- Inserimento automatico basato su keywords
- Kitchen Finder per articoli cucina
- Palette correlate per articoli colori
- Pattern detection: cucine, colori, IKEA, stanze

### 4. **Color Widget - Carosello Colori** 🎨
**Shortcode**: `[link_colori]`  
**Handler**: `link_colori_handler()`  
**File**: `include/site_specific/totaldesign_specific.php`  
**Descrizione**: Carosello con link a tutti gli articoli sui colori  
**Features**:
- 50+ colori (Bianco, Verde, Rosso, Blu, ecc.)
- Colori Pantone (Very Peri 2022, Classic Blue 2020, ecc.)
- Articoli vari (Colori complementari, Abbinamenti, ecc.)
- Carosello scrollabile orizzontale
- CSS condizionale (caricato solo se necessario)

### 5. **Grafica 3D Widget** 🖥️
**Shortcode**: `[grafica3d]`  
**Handler**: `grafica3d_handler()`  
**File**: `include/site_specific/totaldesign_specific.php`  
**Descrizione**: Carosello programmi di grafica 3D  
**Features**:
- Freecad, Homestyler, Autodesk Revit, Archicad
- Maya 3D, Blender 3D, Librecad, Draftsight
- Lumion, Rhinoceros, Sketchup
- Link a guide e tutorial

### 6. **Archistar Widget** 🏛️
**Shortcode**: `[archistar]`  
**Handler**: `archistars_handler()`  
**File**: `include/site_specific/totaldesign_specific.php`  
**Descrizione**: Carosello architetti famosi  
**Features**:
- 30+ architetti (Renzo Piano, Zaha Hadid, Stefano Boeri, ecc.)
- Fucksas, Frank Gehry, Norman Foster, OMA Rem Koolhaas
- Mario Botta, Jean Nouvel, Santiago Calatrava, ecc.
- Link a biografie e progetti

### 7. **Programmatic Hub** 🎯
**Classe**: `gik25microdata\site_specific\Totaldesign\ProgrammaticHub`  
**File**: `include/site_specific/Totaldesign/ProgrammaticHub.php`  
**Descrizione**: Suite di widget programmatici per TotalDesign  

#### 7.1. **Color Hub**
**Shortcode**: `[td_colori_hub]`  
**Descrizione**: Hub centrale colori con filtri e palette

#### 7.2. **IKEA Hub**
**Shortcode**: `[td_ikea_hub]`  
**Descrizione**: Hub linee IKEA con prodotti e guide

#### 7.3. **Programmatic Home**
**Shortcode**: `[td_programmatic_home]`  
**Descrizione**: Homepage programmatica con hero, intent, trending

#### 7.4. **Abbinamenti Colore**
**Shortcode**: `[td_abbinamenti_colore color="bianco"]`  
**Descrizione**: Suggerimenti abbinamenti colore

#### 7.5. **Palette Correlate**
**Shortcode**: `[td_palette_correlate color="verde" limit="3"]`  
**Descrizione**: Palette di colori correlate

#### 7.6. **Colore Stanza**
**Shortcode**: `[td_colore_stanza room="cucina"]`  
**Descrizione**: Palette per stanza specifica

#### 7.7. **Prodotti Colore**
**Shortcode**: `[td_prodotti_colore color="bianco"]`  
**Descrizione**: Prodotti IKEA per colore

#### 7.8. **Lead Box**
**Shortcode**: `[td_lead_box type="color|ikea"]`  
**Descrizione**: Box CTA per lead generation

#### 7.9. **Hack Correlati**
**Shortcode**: `[td_hack_correlati limit="4"]`  
**Descrizione**: Hack IKEA correlati per linea/stanza

#### 7.10. **Completa Set**
**Shortcode**: `[td_completa_set]`  
**Descrizione**: Prodotti complementari per completare set IKEA

#### 7.11. **Color Match IKEA**
**Shortcode**: `[td_color_match_ikea color="bianco" line="metod"]`  
**Descrizione**: Match colore con linea IKEA

## 🔧 Configurazione e Attivazione

Tutti i widget sono attivati in `include/site_specific/totaldesign_specific.php`:

```php
// Kitchen Finder (auto-istanziato)
// App Navigator (auto-istanziato)
// Contextual Widgets
ContextualWidgets::init();

// Shortcode custom
add_shortcode('link_colori', 'link_colori_handler');
add_shortcode('grafica3d', 'grafica3d_handler');
add_shortcode('archistar', 'archistars_handler');

// Programmatic Hub
ProgrammaticHub::init();
```

## 📊 Statistiche

- **Totale Widget**: 18
- **Shortcode Base**: 3 (link_colori, grafica3d, archistar)
- **Programmatic Hub Shortcode**: 11
- **Widget Automatici**: 1 (Contextual Widgets)
- **Widget Interattivi**: 2 (Kitchen Finder, App Navigator)

## 🎯 Utilizzo

### Widget Manuali
Inserisci shortcode direttamente nell'editor:
```
[kitchen_finder title="Trova la cucina perfetta"]
[app_nav]
[link_colori]
[td_palette_correlate color="bianco"]
```

### Widget Automatici
I Contextual Widgets si inseriscono automaticamente negli articoli basandosi su keywords del contenuto.

### Widget Programmatici
I widget Programmatic Hub sono progettati per pagine hub/landing page programmatiche.

