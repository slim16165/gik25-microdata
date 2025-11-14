# Piano d'Azione Integrato - Code Review, Merge e Integrazioni Strategiche

**Data creazione**: 2025-01-30  
**Stato**: 🟢 In Esecuzione  
**Priorità**: Alta

---

## 📋 Obiettivo Generale

1. **Code Review e Merge**: Completare analisi e merge delle branch remote per ottimizzazione e stabilità
2. **Sviluppo Integrazioni Strategiche**: Sviluppare nuove tipologie di integrazioni basate su link hardcoded esistenti per massimizzare valorizzazione siti

---

## 🎯 FASE 1: Code Review e Merge (PRIORITÀ ALTA)

### 1.1 Stato Attuale

✅ **COMPLETATO**: PR #15 mergeata (commit d14849e)
- LinkBuilder e SiteSpecificRegistry integrati
- Refactoring chiecosa_specific.php e totaldesign_specific.php completato

### 1.2 Branch Remote da Analizzare

#### PR Refactoring (da valutare)
- [ ] **PR #12** - 20 nuove feature (+4129/-86) - ⚠️ Molto grande, valutare feature selettive
- [ ] **PR #13** - LinkGenerator pattern (+1079/-320) - ⚠️ Pattern diverso da LinkBuilder
- [ ] **PR #14** - 13 nuove feature (+4104/-14) - ⚠️ Valutare feature selettive

#### PR Dependabot (merge rapido)
- [ ] **PR #11** - codecov-action 3→5
- [ ] **PR #10** - paths-filter 2→3
- [ ] **PR #9** - checkout 4→5

#### PR Altri
- [ ] **PR #5** - Renovate configure (vecchia, 2022) - ⚠️ Valutare se ancora necessaria

### 1.3 Piano Esecuzione Code Review

#### Step 1: Merge PR Dependabot (IMMEDIATO)
**Tempo stimato**: 15 minuti  
**Rischio**: ⭐ (Molto Basso)

```bash
# Merge automatico delle PR Dependabot
gh pr merge 11 --merge
gh pr merge 10 --merge
gh pr merge 9 --merge
```

**Benefici**:
- Aggiornamenti sicurezza
- Compatibilità con workflow GitHub Actions
- Nessun breaking change

#### Step 2: Analisi Feature Selettive PR #12/#14 (BREVE TERMINE)
**Tempo stimato**: 2-3 ore  
**Rischio**: ⭐⭐ (Basso-Medio)

**Feature da valutare** (in ordine di priorità):

**Alta Priorità:**
1. `LinkCache` (PR #12) - Cache per performance link
2. `UrlValidator` (PR #12) - Validazione URL
3. `BrokenLinkChecker` (PR #12) - Controllo link rotti
4. `PerformanceMonitor` (PR #14) - Monitoraggio performance

**Media Priorità:**
5. `ImageEnhancer` (PR #12) - Lazy loading immagini
6. `LinkLogger` (PR #12) - Logging avanzato
7. `SEOEnhancer` (PR #14) - Miglioramenti SEO

**Bassa Priorità:**
8. `ABTester` / `ABTestManager` - A/B testing (valutare se necessario)
9. `ContentRecommender` - Raccomandazioni contenuto
10. `SocialSharing` - Social sharing

**Da evitare** (over-engineering):
- ❌ `ConfigExporter` - Export/import configurazione
- ❌ `ShortcodeBuilder` - Builder visuale (UI complessa)
- ❌ `LinkApiController` - API REST (se non serve)
- ❌ `CustomPostTypeSupport` - Supporto CPT (se non serve)

#### Step 3: Valutazione PR #13 (OPZIONALE)
**Tempo stimato**: 1 ora  
**Rischio**: ⭐⭐ (Basso)

**Decisione necessaria**:
- Preferire `LinkGenerator` (PR #13) o mantenere `LinkBuilder` (PR #15)?
- Se `LinkBuilder` è sufficiente, chiudere PR #13
- Se `LinkGenerator` offre vantaggi, valutare migrazione

#### Step 4: Chiusura PR Vecchie (OPZIONALE)
**Tempo stimato**: 15 minuti

- Valutare PR #5 (Renovate, 2022) - Probabilmente obsoleta

---

## 🚀 FASE 2: Sviluppo Integrazioni Strategiche (PRIORITÀ ALTA)

### 2.1 Analisi Pattern Esistenti

**Pattern identificati nei file site-specific:**

#### TotalDesign.it
- ✅ **Colori**: 50+ link hardcoded (bianco, rosso, verde, ecc.)
- ✅ **IKEA Linee**: BILLY, KALLAX, BESTA, PAX, METOD, ENHET
- ✅ **IKEA Stanze**: Cucina, Soggiorno, Camera, Bagno, Studio, Ingresso
- ✅ **Architetti**: 30+ link (Renzo Piano, Zaha Hadid, ecc.)
- ✅ **Programmi 3D**: 12 link (FreeCAD, Blender, SketchUp, ecc.)
- ✅ **Pantone**: 7 link (colori dell'anno)

#### ChieCosa.it
- ✅ **Personaggi TV**: Temptation Island, Amici Celebrities, Tale e Quale Show
- ✅ **Show**: Lista concorrenti per programma

#### NonSoloDieti.it
- ✅ **Vitamine**: Gruppo B, D, Acido Folico, ecc.
- ✅ **Diete**: Differenti tipologie di diete
- ✅ **Analisi Sangue**: Parametri ematologici

#### SuperInformati.com
- ✅ **Esami Medici**: Emocromo, analisi sangue, parametri
- ✅ **Dimagrimento**: Metodi, integratori, esercizi
- ✅ **Fitness**: Allenamenti, attrezzi, programmi

### 2.2 Statistiche Pattern (da analisi MCP)

**Categoria Arredamento (50 post analizzati):**
- 🎨 **Colori**: 76% dei post (38/50)
- 🏠 **Stanze**: 78% dei post (39/50)
- 🏪 **IKEA**: 36% dei post (18/50)
- 🍳 **Cucine**: 28% dei post (14/50)

---

## 💡 PROPOSTE INTEGRAZIONI STRATEGICHE (10+)

### 1. 🎨 Hub Colori Dinamico
**Tipo**: Shortcode dinamico  
**Pattern base**: `link_colori_handler` (50+ link hardcoded)

**Descrizione**:
- Shortcode `[hub_colori]` che genera hub completo colori
- Query dinamica basata su categorie/tag WordPress
- Sezioni: Colori Specifici, Pantone, Abbinamenti, Palette
- Cross-linking con stanze e IKEA

**Implementazione**:
```php
function hub_colori_dinamico_handler($atts) {
    // Query post categoria "colori" o tag specifici
    // Genera caroselli dinamici
    // Include sezioni: Pantone, Abbinamenti, Stanze correlate
}
```

**Valore**:
- ✅ Riduce manutenzione (no link hardcoded)
- ✅ Aggiornamento automatico con nuovi post
- ✅ SEO migliorato (contenuto dinamico)

---

### 2. 🏪 Hub IKEA Completo
**Tipo**: Shortcode dinamico + Widget  
**Pattern base**: `ProgrammaticHub::IKEA_LINES` + link hardcoded

**Descrizione**:
- Shortcode `[hub_ikea]` con sezioni:
  - Hack per linea (BILLY, KALLAX, BESTA, PAX, METOD, ENHET)
  - Hack per stanza (Cucina, Soggiorno, Camera, Bagno)
  - Compatibilità accessori
  - Guide complete

**Implementazione**:
```php
function hub_ikea_completo_handler($atts) {
    // Query post con tag IKEA + linea/stanza
    // Genera blocchi dinamici per ogni linea
    // Include cross-linking con colori
}
```

**Valore**:
- ✅ Hub centralizzato IKEA
- ✅ Aggiornamento automatico
- ✅ Cross-linking intelligente

---

### 3. 🏛️ Hub Architetti Dinamico
**Tipo**: Shortcode dinamico  
**Pattern base**: `archistars_handler` (30+ link hardcoded)

**Descrizione**:
- Shortcode `[hub_architetti]` che genera hub architetti
- Query dinamica categoria "ArchiStar"
- Sezioni: Architetti famosi, Opere, Stili, Città

**Implementazione**:
```php
function hub_architetti_dinamico_handler($atts) {
    // Query categoria "archistar"
    // Raggruppa per architetto
    // Include opere principali, stili, città
}
```

**Valore**:
- ✅ Aggiornamento automatico con nuovi architetti
- ✅ Organizzazione migliore
- ✅ Cross-linking con città/opere

---

### 4. 🎬 Hub Personaggi TV Dinamico (ChieCosa)
**Tipo**: Shortcode dinamico  
**Pattern base**: `temptation_island_single_handler`, `amici_celebrities_handler`

**Descrizione**:
- Shortcode `[hub_personaggi show="temptation-island"]`
- Query dinamica basata su tag/categorie
- Raggruppamento per show/programma
- Link correlati automatici

**Implementazione**:
```php
function hub_personaggi_tv_handler($atts) {
    $show = $atts['show'] ?? 'all';
    // Query post tag "personaggio-tv" + show specifico
    // Genera lista dinamica
    // Include informazioni show
}
```

**Valore**:
- ✅ Aggiornamento automatico con nuovi personaggi
- ✅ Organizzazione per show
- ✅ Cross-linking tra personaggi stesso show

---

### 5. 💊 Hub Vitamine Dinamico (NonSoloDieti)
**Tipo**: Shortcode dinamico  
**Pattern base**: `link_vitamine_handler` (8 link hardcoded)

**Descrizione**:
- Shortcode `[hub_vitamine]` con query dinamica
- Raggruppamento per gruppo (B, D, ecc.)
- Informazioni dosaggi, carenze, fonti
- Cross-linking con diete

**Implementazione**:
```php
function hub_vitamine_dinamico_handler($atts) {
    // Query categoria "vitamine" o tag specifici
    // Raggruppa per gruppo
    // Include informazioni strutturate
}
```

**Valore**:
- ✅ Aggiornamento automatico
- ✅ Organizzazione per gruppo
- ✅ Cross-linking con diete/carenze

---

### 6. 🏥 Hub Esami Medici Dinamico (SuperInformati)
**Tipo**: Shortcode dinamico  
**Pattern base**: `link_analisi_sangue_handler` (50+ link hardcoded)

**Descrizione**:
- Shortcode `[hub_esami]` con query dinamica
- Raggruppamento per tipo (Ematologici, Biochimici, Ormonali)
- Valori normali, interpretazione
- Cross-linking con sintomi/patologie

**Implementazione**:
```php
function hub_esami_medici_handler($atts) {
    // Query categoria "esami-medici"
    // Raggruppa per tipo esame
    // Include valori riferimento
}
```

**Valore**:
- ✅ Hub completo esami medici
- ✅ Organizzazione per tipo
- ✅ Cross-linking intelligente

---

### 7. 🎨 Cross-Linking Colore + Stanza + IKEA
**Tipo**: Widget contestuale avanzato  
**Pattern base**: `ProgrammaticHub::build_cross_link_block()`

**Descrizione**:
- Widget automatico che genera link incrociati
- Esempio: "Colore Verde Salvia in Cucina con IKEA METOD"
- Basato su keywords articolo
- Query dinamica combinata

**Implementazione**:
```php
class AdvancedCrossLinker {
    public function generate_cross_links($post) {
        // Estrai keywords: colore, stanza, ikea
        // Query combinata
        // Genera link incrociati
    }
}
```

**Valore**:
- ✅ Cross-linking intelligente
- ✅ Aumenta page views
- ✅ Migliora UX

---

### 8. 📐 Hub Programmi 3D Dinamico
**Tipo**: Shortcode dinamico  
**Pattern base**: `grafica3d_handler` (12 link hardcoded)

**Descrizione**:
- Shortcode `[hub_grafica3d]` con query dinamica
- Raggruppamento per tipo (CAD, Rendering, Modellazione)
- Confronti, tutorial, download
- Cross-linking con architettura

**Implementazione**:
```php
function hub_grafica3d_dinamico_handler($atts) {
    // Query categoria "grafica" + tag "3d"
    // Raggruppa per tipo software
    // Include confronti e tutorial
}
```

**Valore**:
- ✅ Aggiornamento automatico
- ✅ Organizzazione per tipo
- ✅ Cross-linking con architettura

---

### 9. 🏠 Hub Stanze Dinamico
**Tipo**: Shortcode dinamico  
**Pattern base**: `ProgrammaticHub::IKEA_ROOMS` + pattern categoria

**Descrizione**:
- Shortcode `[hub_stanze stanza="cucina"]`
- Query dinamica per stanza
- Sezioni: Colori, IKEA, Arredamento, Consigli
- Cross-linking con altre stanze

**Implementazione**:
```php
function hub_stanze_dinamico_handler($atts) {
    $stanza = $atts['stanza'] ?? 'all';
    // Query categoria stanza specifica
    // Include: colori, ikea, arredamento
    // Cross-linking con altre stanze
}
```

**Valore**:
- ✅ Hub completo per stanza
- ✅ Aggiornamento automatico
- ✅ Cross-linking intelligente

---

### 10. 🎯 Widget Correlati Intelligenti
**Tipo**: Widget automatico  
**Pattern base**: `ContextualWidgets` esistente

**Descrizione**:
- Widget che genera link correlati basati su:
  - Keywords articolo
  - Categoria
  - Tag
  - Contenuto (NLP)
- Priorità: Colori > IKEA > Stanze > Architetti

**Implementazione**:
```php
class IntelligentRelatedWidget {
    public function get_related_links($post) {
        // Analisi keywords
        // Query multi-criterio
        // Ranking per rilevanza
        // Genera widget
    }
}
```

**Valore**:
- ✅ Link correlati più rilevanti
- ✅ Aumenta engagement
- ✅ Migliora SEO

---

### 11. 📊 Hub Pantone Dinamico
**Tipo**: Shortcode dinamico  
**Pattern base**: `link_colori_handler` sezione Pantone (7 link)

**Descrizione**:
- Shortcode `[hub_pantone]` con query dinamica
- Raggruppamento per anno
- Colori dell'anno, palette, trend
- Cross-linking con arredamento

**Implementazione**:
```php
function hub_pantone_dinamico_handler($atts) {
    // Query tag "pantone" + anno
    // Raggruppa per anno colore
    // Include palette e trend
}
```

**Valore**:
- ✅ Hub Pantone completo
- ✅ Aggiornamento automatico
- ✅ Cross-linking con trend

---

### 12. 🏃 Hub Fitness Dinamico (SuperInformati)
**Tipo**: Shortcode dinamico  
**Pattern base**: Pattern dimagrimento/fitness (50+ link)

**Descrizione**:
- Shortcode `[hub_fitness]` con query dinamica
- Raggruppamento per tipo (Cardio, Forza, Flessibilità)
- Programmi, attrezzi, integratori
- Cross-linking con dieta

**Implementazione**:
```php
function hub_fitness_dinamico_handler($atts) {
    // Query categoria "fitness" o "dimagrimento"
    // Raggruppa per tipo allenamento
    // Include programmi e attrezzi
}
```

**Valore**:
- ✅ Hub fitness completo
- ✅ Organizzazione per tipo
- ✅ Cross-linking con dieta

---

### 13. 🍽️ Hub Diete Dinamico (NonSoloDieti)
**Tipo**: Shortcode dinamico  
**Pattern base**: `link_diete_handler` (30+ link)

**Descrizione**:
- Shortcode `[hub_diete]` con query dinamica
- Raggruppamento per tipo (Cheto, Mediterranea, Vegana)
- Confronti, pro/contro, ricette
- Cross-linking con vitamine

**Implementazione**:
```php
function hub_diete_dinamico_handler($atts) {
    // Query categoria "diete"
    // Raggruppa per tipo dieta
    // Include confronti e ricette
}
```

**Valore**:
- ✅ Hub diete completo
- ✅ Organizzazione per tipo
- ✅ Cross-linking con vitamine

---

### 14. 🎨 Sistema Raccomandazioni Colore-Stanza
**Tipo**: Widget automatico  
**Pattern base**: `ProgrammaticHub::COLOR_LIBRARY` + `IKEA_ROOMS`

**Descrizione**:
- Widget che raccomanda combinazioni colore-stanza
- Basato su pattern esistenti
- Query dinamica per trovare esempi reali
- Cross-linking con IKEA

**Implementazione**:
```php
class ColorRoomRecommender {
    public function recommend($color, $room) {
        // Query combinata colore + stanza
        // Include esempi reali
        // Suggerisce IKEA compatibili
    }
}
```

**Valore**:
- ✅ Raccomandazioni personalizzate
- ✅ Aumenta engagement
- ✅ Cross-linking intelligente

---

### 15. 🏗️ Hub Città Architettura Dinamico
**Tipo**: Shortcode dinamico  
**Pattern base**: Categoria "Città del mondo" (28 post)

**Descrizione**:
- Shortcode `[hub_citta]` con query dinamica
- Raggruppamento per città
- Architetti, opere, stili
- Cross-linking con architettura

**Implementazione**:
```php
function hub_citta_dinamico_handler($atts) {
    // Query categoria "citta-del-mondo"
    // Raggruppa per città
    // Include architetti e opere
}
```

**Valore**:
- ✅ Hub città completo
- ✅ Organizzazione geografica
- ✅ Cross-linking con architetti

---

## 📅 Piano d'Azione Dettagliato

### SETTIMANA 1: Code Review e Merge

#### Giorno 1-2: Merge PR Dependabot
- [ ] Merge PR #11 (codecov-action)
- [ ] Merge PR #10 (paths-filter)
- [ ] Merge PR #9 (checkout)
- [ ] Test workflow GitHub Actions
- **Output**: PR Dependabot mergeate, workflow aggiornati

#### Giorno 3-4: Analisi Feature Selettive
- [ ] Analizzare `LinkCache` (PR #12)
- [ ] Analizzare `UrlValidator` (PR #12)
- [ ] Analizzare `BrokenLinkChecker` (PR #12)
- [ ] Analizzare `PerformanceMonitor` (PR #14)
- [ ] Decidere quali feature mergeare
- **Output**: Lista feature da mergeare

#### Giorno 5: Merge Feature Selettive
- [ ] Estrarre feature selezionate da PR #12/#14
- [ ] Creare branch `feature/selective-enhancements`
- [ ] Merge feature una alla volta
- [ ] Test ogni feature
- **Output**: Feature selezionate mergeate

---

### SETTIMANA 2-3: Integrazioni Strategiche - Priorità Alta

#### Hub Colori Dinamico (Priorità 1)
- [ ] Creare `HubColoriDinamico` class
- [ ] Implementare query dinamica categorie/tag
- [ ] Sostituire `link_colori_handler` con versione dinamica
- [ ] Test su staging
- **Tempo**: 1-2 giorni
- **Output**: Hub colori completamente dinamico

#### Hub IKEA Completo (Priorità 2)
- [ ] Estendere `ProgrammaticHub::render_ikea_hub()`
- [ ] Implementare query dinamica per linee
- [ ] Implementare query dinamica per stanze
- [ ] Aggiungere cross-linking colore-stanza-IKEA
- **Tempo**: 2-3 giorni
- **Output**: Hub IKEA completo e dinamico

#### Hub Architetti Dinamico (Priorità 3)
- [ ] Creare `HubArchitettiDinamico` class
- [ ] Implementare query categoria "archistar"
- [ ] Sostituire `archistars_handler` con versione dinamica
- [ ] Aggiungere raggruppamento per architetto
- **Tempo**: 1-2 giorni
- **Output**: Hub architetti dinamico

---

### SETTIMANA 4-5: Integrazioni Strategiche - Priorità Media

#### Cross-Linking Intelligente (Priorità 4)
- [ ] Creare `AdvancedCrossLinker` class
- [ ] Implementare estrazione keywords
- [ ] Implementare query combinata
- [ ] Integrare in `ContextualWidgets`
- **Tempo**: 2-3 giorni
- **Output**: Cross-linking automatico colore-stanza-IKEA

#### Hub Personaggi TV (ChieCosa) (Priorità 5)
- [ ] Creare `HubPersonaggiTV` class
- [ ] Implementare query dinamica per show
- [ ] Sostituire handler esistenti
- [ ] Aggiungere raggruppamento per programma
- **Tempo**: 1-2 giorni
- **Output**: Hub personaggi TV dinamico

#### Hub Vitamine (NonSoloDieti) (Priorità 6)
- [ ] Creare `HubVitamineDinamico` class
- [ ] Implementare query categoria "vitamine"
- [ ] Sostituire `link_vitamine_handler`
- [ ] Aggiungere raggruppamento per gruppo
- **Tempo**: 1-2 giorni
- **Output**: Hub vitamine dinamico

---

### SETTIMANA 6-7: Integrazioni Strategiche - Priorità Bassa

#### Hub Esami Medici (SuperInformati) (Priorità 7)
- [ ] Creare `HubEsamiMedici` class
- [ ] Implementare query categoria "esami-medici"
- [ ] Sostituire `link_analisi_sangue_handler`
- [ ] Aggiungere raggruppamento per tipo
- **Tempo**: 2-3 giorni
- **Output**: Hub esami medici dinamico

#### Hub Programmi 3D (Priorità 8)
- [ ] Creare `HubGrafica3D` class
- [ ] Implementare query categoria "grafica" + tag "3d"
- [ ] Sostituire `grafica3d_handler`
- [ ] Aggiungere raggruppamento per tipo
- **Tempo**: 1-2 giorni
- **Output**: Hub programmi 3D dinamico

#### Hub Stanze Dinamico (Priorità 9)
- [ ] Creare `HubStanzeDinamico` class
- [ ] Implementare query per stanza
- [ ] Aggiungere sezioni: colori, IKEA, arredamento
- [ ] Cross-linking con altre stanze
- **Tempo**: 2-3 giorni
- **Output**: Hub stanze dinamico

---

### SETTIMANA 8: Ottimizzazioni e Testing

#### Widget Correlati Intelligenti (Priorità 10)
- [ ] Estendere `IntelligentRelatedWidget`
- [ ] Implementare ranking rilevanza
- [ ] Integrare in `ContextualWidgets`
- [ ] Test A/B
- **Tempo**: 2-3 giorni
- **Output**: Widget correlati migliorati

#### Testing Completo
- [ ] Test tutti gli hub dinamici
- [ ] Verificare performance
- [ ] Verificare SEO
- [ ] Test cross-linking
- **Tempo**: 2-3 giorni
- **Output**: Sistema testato e ottimizzato

---

## 📊 Metriche di Successo

### Code Review e Merge
- ✅ PR Dependabot mergeate: 3/3
- ✅ Feature selettive mergeate: 4-7 (da decidere)
- ✅ Zero breaking changes
- ✅ Test coverage mantenuto/aumentato

### Integrazioni Strategiche
- ✅ Hub dinamici implementati: 10+
- ✅ Link hardcoded ridotti: 80%+
- ✅ Aggiornamento automatico: 100% hub dinamici
- ✅ Cross-linking implementato: 5+ tipologie
- ✅ Page views aumentate: +20% (target)
- ✅ Time on site aumentato: +15% (target)

---

## 🔧 Strumenti e Tecnologie

### Per Code Review
- GitHub CLI (`gh`)
- Git diff/merge
- PHPUnit (se disponibile)
- PHPStan/Psalm (analisi statica)

### Per Integrazioni
- WordPress Query API
- LinkBuilder (già implementato)
- SiteSpecificRegistry (già implementato)
- MCP API (per query dinamiche)
- ContextualWidgets (estendere)

---

## ⚠️ Rischi e Mitigazioni

### Rischi Code Review
- **Rischio**: Feature selettive potrebbero avere dipendenze
- **Mitigazione**: Analisi approfondita prima del merge, test isolati

- **Rischio**: Conflitti tra PR
- **Mitigazione**: Merge incrementale, test dopo ogni merge

### Rischi Integrazioni
- **Rischio**: Query dinamiche potrebbero essere lente
- **Mitigazione**: Cache implementata, query ottimizzate

- **Rischio**: Link dinamici potrebbero non trovare contenuti
- **Mitigazione**: Fallback a link hardcoded, validazione query

- **Rischio**: Breaking changes per shortcode esistenti
- **Mitigazione**: Backward compatibility, versioning shortcode

---

## 📝 Note Implementative

### Pattern Comune per Hub Dinamici

```php
class HubDinamicoBase {
    protected function query_posts($args) {
        // Query WordPress standard
        // Filtri per categoria/tag
        // Ordinamento per rilevanza
    }
    
    protected function build_carousel($posts) {
        $builder = LinkBuilder::create('carousel');
        // Genera carosello da post
    }
    
    protected function build_sections($posts) {
        // Raggruppa post per sezione
        // Genera HTML sezioni
    }
}
```

### Cross-Linking Pattern

```php
class CrossLinker {
    public function generate_links($post) {
        $keywords = $this->extract_keywords($post);
        $links = [];
        
        // Colore + Stanza
        if ($keywords['color'] && $keywords['room']) {
            $links[] = $this->query_color_room($keywords);
        }
        
        // IKEA + Stanza
        if ($keywords['ikea'] && $keywords['room']) {
            $links[] = $this->query_ikea_room($keywords);
        }
        
        return $links;
    }
}
```

---

## 🎯 Priorità Finale

### IMMEDIATO (Settimana 1)
1. Merge PR Dependabot
2. Analisi feature selettive
3. Merge feature selezionate

### BREVE TERMINE (Settimana 2-3)
1. Hub Colori Dinamico
2. Hub IKEA Completo
3. Hub Architetti Dinamico

### MEDIO TERMINE (Settimana 4-5)
1. Cross-Linking Intelligente
2. Hub Personaggi TV
3. Hub Vitamine

### LUNGO TERMINE (Settimana 6-8)
1. Hub Esami Medici
2. Hub Programmi 3D
3. Hub Stanze
4. Widget Correlati Intelligenti

---

**Data aggiornamento**: 2025-01-30  
**Prossima revisione**: Dopo completamento Settimana 1

