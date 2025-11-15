# Progettazione Architettura: Sistema Link Interni Unificato

**Data**: Gennaio 2025  
**Versione**: 1.0  
**Scopo**: Progettazione architettura unificata per sistema link interni

---

## 1. Design API Unificata

### 1.1 Namespace e Struttura

**Namespace PHP**: `gik25microdata\InternalLinks`

**Struttura Classi**:
```
gik25microdata\InternalLinks\
├── Core\
│   ├── InternalLinksManager.php      # Singleton principale
│   ├── LinkProcessor.php              # Processamento link
│   └── LinkAnalyzer.php               # Analisi link
├── Autolinks\
│   ├── AutolinkEngine.php             # Engine autolinks unificato
│   ├── KeywordMatcher.php             # Matching keyword (DAIM + WPIL)
│   ├── AutolinkRule.php                # Modello regola autolink
│   └── ContextMatcher.php              # Context matching (DAIM)
├── Suggestions\
│   ├── SuggestionEngine.php           # Engine suggerimenti (WPIL)
│   ├── SemanticAnalyzer.php           # Analisi semantica
│   ├── SuggestionRanker.php           # Ranking con juice
│   └── PhraseExtractor.php             # Estrazione frasi
├── Reports\
│   ├── ReportGenerator.php            # Generazione report
│   ├── JuiceCalculator.php            # Calcolo juice (DAIM)
│   ├── LinkStats.php                  # Statistiche link
│   └── ClickTracker.php               # Click tracking unificato
├── Monitoring\
│   ├── HttpStatusChecker.php          # HTTP status (DAIM)
│   ├── ErrorDetector.php              # Error detection (WPIL)
│   └── HealthMonitor.php              # Health check
├── Integration\
│   ├── EditorIntegration.php          # Editor integration (WPIL)
│   ├── SearchConsole.php              # GSC (WPIL)
│   └── ExportImport.php              # Export/Import (WPIL)
└── Utils\
    ├── Stemmer.php                     # Stemming multi-lingua (WPIL)
    ├── LanguageSupport.php            # Supporto lingue
    └── LinkValidator.php              # Validazione link
```

### 1.2 Interfacce Principali

#### InternalLinksManager Interface

```php
namespace gik25microdata\InternalLinks\Core;

class InternalLinksManager {
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Processa contenuto e applica autolinks
     */
    public function processContent($content, $post_id, $options = []);
    
    /**
     * Genera suggerimenti per un post
     */
    public function getSuggestions($post_id, $limit = 10);
    
    /**
     * Calcola juice per un link
     */
    public function calculateJuice($post_id, $link_position);
    
    /**
     * Traccia click su link
     */
    public function trackClick($link_id, $data = []);
    
    /**
     * Verifica HTTP status link
     */
    public function checkHttpStatus($url);
    
    /**
     * Genera report
     */
    public function generateReport($type, $filters = []);
}
```

#### AutolinkEngine Interface

```php
namespace gik25microdata\InternalLinks\Autolinks;

interface AutolinkEngineInterface {
    /**
     * Applica autolinks a contenuto
     */
    public function applyAutolinks($content, $post_id, $rules);
    
    /**
     * Match keyword in contenuto
     */
    public function matchKeyword($keyword, $content, $context = []);
    
    /**
     * Valida regola autolink
     */
    public function validateRule($rule);
}
```

#### SuggestionEngine Interface

```php
namespace gik25microdata\InternalLinks\Suggestions;

interface SuggestionEngineInterface {
    /**
     * Genera suggerimenti per post
     */
    public function generateSuggestions($post_id, $options = []);
    
    /**
     * Rank suggerimenti
     */
    public function rankSuggestions($suggestions, $post_id);
    
    /**
     * Estrae frasi da contenuto
     */
    public function extractPhrases($content);
}
```

### 1.3 WordPress Hooks e Filters

**Actions**:
```php
// Processamento contenuto
add_action('the_content', [$manager, 'processContent'], 10, 1);

// Salvataggio post
add_action('save_post', [$manager, 'onPostSave'], 10, 2);

// Click tracking
add_action('wp_ajax_gik25_track_link_click', [$tracker, 'handleClick']);
add_action('wp_ajax_nopriv_gik25_track_link_click', [$tracker, 'handleClick']);

// HTTP status check
add_action('gik25_il_check_http_status', [$checker, 'checkStatus']);
```

**Filters**:
```php
// Modifica autolinks prima applicazione
add_filter('gik25_il_autolink_rules', [$manager, 'filterRules'], 10, 2);

// Modifica suggerimenti
add_filter('gik25_il_suggestions', [$manager, 'filterSuggestions'], 10, 2);

// Modifica juice calculation
add_filter('gik25_il_juice_score', [$calculator, 'modifyJuice'], 10, 3);

// Modifica link processing
add_filter('gik25_il_process_link', [$processor, 'processLink'], 10, 2);
```

**Custom Hooks**:
```php
// Dopo autolink applicato
do_action('gik25_il_autolink_applied', $post_id, $link_count);

// Dopo suggerimento generato
do_action('gik25_il_suggestion_generated', $post_id, $suggestions);

// Dopo juice calcolato
do_action('gik25_il_juice_calculated', $post_id, $juice_score);
```

### 1.4 REST API Endpoints

**Namespace**: `/wp-json/gik25-il/v1/`

**Endpoints**:
```php
// Autolinks
GET    /autolinks                    # Lista autolinks
POST   /autolinks                    # Crea autolink
GET    /autolinks/{id}               # Get autolink
PUT    /autolinks/{id}               # Update autolink
DELETE /autolinks/{id}               # Delete autolink

// Suggestions
GET    /suggestions/{post_id}         # Get suggestions per post
POST   /suggestions/generate         # Genera suggestions

// Reports
GET    /reports/links                # Link report
GET    /reports/juice                # Juice report
GET    /reports/clicks               # Click report
GET    /reports/status               # HTTP status report

// Monitoring
GET    /monitoring/health            # Health check
POST   /monitoring/check-status     # Check HTTP status

// Export/Import
GET    /export/links                 # Export links CSV
GET    /export/links/excel          # Export links Excel
POST   /import/keywords              # Import keywords CSV
```

---

## 2. Design Database

### 2.1 Schema Unificato Completo

```sql
-- ============================================
-- AUTOLINKS
-- ============================================
CREATE TABLE wp_gik25_il_autolinks (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    keyword VARCHAR(255) NOT NULL,
    url VARCHAR(2083) NOT NULL,
    anchor_text VARCHAR(255),
    title VARCHAR(1024),
    
    -- Matching options (da DAIM)
    string_before INT DEFAULT 1,
    string_after INT DEFAULT 1,
    keyword_before VARCHAR(255),
    keyword_after VARCHAR(255),
    case_insensitive TINYINT(1) DEFAULT 0,
    
    -- Stemming (da WPIL)
    use_stemming TINYINT(1) DEFAULT 0,
    language VARCHAR(10) DEFAULT 'it',
    
    -- Limits
    max_links_per_post INT DEFAULT 1,
    same_url_limit INT DEFAULT 1,
    priority INT DEFAULT 0,
    
    -- Filtering
    post_types TEXT, -- JSON array
    categories TEXT, -- JSON array
    tags TEXT, -- JSON array
    term_group_id BIGINT DEFAULT 0,
    category_id BIGINT DEFAULT 0,
    
    -- Link options
    open_new_tab TINYINT(1) DEFAULT 0,
    use_nofollow TINYINT(1) DEFAULT 0,
    
    -- Status
    enabled TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_keyword (keyword),
    INDEX idx_url (url(255)),
    INDEX idx_priority (priority),
    INDEX idx_enabled (enabled),
    INDEX idx_category (category_id),
    INDEX idx_term_group (term_group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INTERNAL LINKS REGISTRY
-- ============================================
CREATE TABLE wp_gik25_il_links (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    source_post_id BIGINT NOT NULL,
    target_post_id BIGINT NOT NULL,
    target_url VARCHAR(2083) NOT NULL,
    anchor_text VARCHAR(255),
    
    -- Link metadata
    link_type ENUM('manual', 'autolink', 'suggestion') DEFAULT 'manual',
    autolink_id BIGINT NULL,
    suggestion_id BIGINT NULL,
    
    -- Position in content
    position INT,
    sentence TEXT,
    context_before TEXT,
    context_after TEXT,
    
    -- Juice data
    juice_score DECIMAL(10,4) DEFAULT 0,
    juice_calculated_at DATETIME,
    
    -- Statistics
    click_count INT DEFAULT 0,
    last_click_at DATETIME,
    
    -- Status
    http_status INT,
    http_status_checked_at DATETIME,
    is_broken TINYINT(1) DEFAULT 0,
    is_ignored TINYINT(1) DEFAULT 0,
    
    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_source (source_post_id),
    INDEX idx_target (target_post_id),
    INDEX idx_url (target_url(255)),
    INDEX idx_type (link_type),
    INDEX idx_autolink (autolink_id),
    INDEX idx_broken (is_broken),
    INDEX idx_http_status (http_status),
    UNIQUE KEY unique_link (source_post_id, target_url(255), anchor_text(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SUGGESTIONS CACHE
-- ============================================
CREATE TABLE wp_gik25_il_suggestions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT NOT NULL,
    suggested_post_id BIGINT NOT NULL,
    
    -- Suggestion data
    phrase TEXT NOT NULL,
    anchor_text VARCHAR(255),
    sentence TEXT,
    context_before TEXT,
    context_after TEXT,
    
    -- Scoring
    similarity_score DECIMAL(5,4) DEFAULT 0,
    juice_score DECIMAL(10,4) DEFAULT 0,
    combined_score DECIMAL(5,4) DEFAULT 0,
    
    -- Status
    is_applied TINYINT(1) DEFAULT 0,
    applied_at DATETIME,
    is_ignored TINYINT(1) DEFAULT 0,
    
    -- Cache
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    
    INDEX idx_post (post_id),
    INDEX idx_suggested (suggested_post_id),
    INDEX idx_score (combined_score),
    INDEX idx_applied (is_applied),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CLICK TRACKING
-- ============================================
CREATE TABLE wp_gik25_il_clicks (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    link_id BIGINT NOT NULL,
    post_id BIGINT NOT NULL,
    
    -- Click data
    ip_address VARCHAR(45),
    user_agent TEXT,
    referrer VARCHAR(2083),
    device_type VARCHAR(50),
    browser VARCHAR(100),
    
    -- Timestamp
    clicked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_link (link_id),
    INDEX idx_post (post_id),
    INDEX idx_date (clicked_at),
    INDEX idx_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- HTTP STATUS CACHE
-- ============================================
CREATE TABLE wp_gik25_il_http_status (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(2083) NOT NULL,
    post_id BIGINT,
    
    -- Status data
    http_status INT,
    status_description TEXT,
    redirect_url VARCHAR(2083),
    
    -- Check metadata
    checked_at DATETIME,
    check_duration INT, -- milliseconds
    error_message TEXT,
    
    -- Cache
    expires_at DATETIME,
    
    INDEX idx_url (url(255)),
    INDEX idx_status (http_status),
    INDEX idx_post (post_id),
    INDEX idx_expires (expires_at),
    UNIQUE KEY unique_url (url(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- JUICE DATA
-- ============================================
CREATE TABLE wp_gik25_il_juice (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT NOT NULL,
    url VARCHAR(2083) NOT NULL,
    
    -- Juice metrics
    seo_power INT DEFAULT 100,
    juice_absolute DECIMAL(10,4) DEFAULT 0,
    juice_relative DECIMAL(10,4) DEFAULT 0,
    
    -- Link counts
    inbound_links INT DEFAULT 0,
    outbound_links INT DEFAULT 0,
    total_links INT DEFAULT 0,
    
    -- Calculation metadata
    calculated_at DATETIME,
    calculation_version VARCHAR(20),
    
    INDEX idx_post (post_id),
    INDEX idx_url (url(255)),
    INDEX idx_juice (juice_absolute)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CATEGORIES (da DAIM)
-- ============================================
CREATE TABLE wp_gik25_il_categories (
    category_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TERM GROUPS (da DAIM)
-- ============================================
CREATE TABLE wp_gik25_il_term_groups (
    term_group_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    
    -- Filtri (JSON array di oggetti)
    -- [{"post_type": "post", "taxonomy": "category", "term_id": 123}, ...]
    filters TEXT,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ARCHIVE/STATISTICS (da DAIM)
-- ============================================
CREATE TABLE wp_gik25_il_archive (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT NOT NULL,
    
    -- Post data
    post_title TEXT,
    post_permalink TEXT,
    post_type VARCHAR(20),
    post_date DATETIME,
    
    -- Link counts
    manual_links INT DEFAULT 0,
    autolinks INT DEFAULT 0,
    inbound_links INT DEFAULT 0,
    outbound_links INT DEFAULT 0,
    
    -- Content metrics
    content_length INT DEFAULT 0,
    recommended_links INT DEFAULT 0,
    
    -- Engagement
    click_count INT DEFAULT 0,
    optimization_score DECIMAL(5,2) DEFAULT 0,
    
    -- Timestamps
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_post (post_id),
    INDEX idx_type (post_type),
    INDEX idx_optimization (optimization_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2.2 Migration Strategy

#### Migration da DAIM

```php
class DaimMigration {
    public function migrateAutolinks() {
        // Migra wp_daim_autolinks -> wp_gik25_il_autolinks
        // Mappa campi:
        // - keyword, url, title -> stesso
        // - categories, tags -> JSON array
        // - term_group_id -> stesso
        // - Aggiungi: use_stemming, language
    }
    
    public function migrateJuice() {
        // Migra wp_daim_juice -> wp_gik25_il_juice
        // Mappa: url, iil, juice -> juice_absolute
    }
    
    public function migrateHits() {
        // Migra wp_daim_hits -> wp_gik25_il_clicks
        // Mappa: source_post_id, target_url -> link_id (se esiste)
    }
    
    public function migrateHttpStatus() {
        // Migra wp_daim_http_status -> wp_gik25_il_http_status
        // Mappa: url, code -> http_status
    }
    
    public function migrateArchive() {
        // Migra wp_daim_archive -> wp_gik25_il_archive
        // Mappa diretta
    }
}
```

#### Migration da WPIL

```php
class WpilMigration {
    public function migrateKeywords() {
        // Migra wp_wpil_keywords -> wp_gik25_il_autolinks
        // Mappa:
        // - keyword_text -> keyword
        // - link -> url
        // - Aggiungi: use_stemming = true (WPIL usa sempre)
    }
    
    public function migrateLinks() {
        // Migra wp_wpil_links -> wp_gik25_il_links
        // Mappa:
        // - post_id -> source_post_id
        // - url -> target_url
        // - anchor -> anchor_text
        // - internal -> link_type
    }
    
    public function migrateClicks() {
        // Migra wp_wpil_clicks -> wp_gik25_il_clicks
        // Mappa diretta con più dettagli
    }
    
    public function migrateErrors() {
        // Migra wp_wpil_errors -> wp_gik25_il_http_status
        // Mappa errori come http_status != 200
    }
}
```

### 2.3 Indexing Strategy

**Performance Indexes**:
- Link lookups: `(source_post_id, target_post_id)`
- Autolink matching: `(keyword, enabled, priority)`
- Suggestions: `(post_id, combined_score)`
- Click tracking: `(link_id, clicked_at)`
- HTTP status: `(url, expires_at)`

**Full-Text Indexes** (se supportato):
- `wp_gik25_il_autolinks.keyword` - Per ricerca keyword
- `wp_gik25_il_links.anchor_text` - Per ricerca anchor

---

## 3. Design UI/UX

### 3.1 Admin Menu Structure

```
Interlinks (icon)
├── Dashboard          # Overview unificato
├── Links              # Report link (inbound/outbound)
├── Autolinks          # Gestione autolinks
├── Suggestions        # Gestione suggerimenti
├── Juice              # Juice analysis
├── Clicks             # Click tracking
├── Status             # HTTP status
├── Categories         # Categories (da DAIM)
├── Term Groups        # Term groups (da DAIM)
├── Target Keywords    # Target keywords (da WPIL)
├── Settings           # Impostazioni
└── Tools              # Export/Import, Migration, etc.
```

### 3.2 Dashboard Unificato

**Layout**:
```
┌─────────────────────────────────────────────────┐
│ Overview Statistics                             │
├─────────────────────────────────────────────────┤
│ [Total Links] [Autolinks] [Manual] [Juice Avg]  │
│ [Clicks Today] [Broken Links] [Suggestions]    │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│ Recent Activity                                  │
├─────────────────────────────────────────────────┤
│ - Link aggiunto a "Post X"                      │
│ - Autolink applicato a "Post Y"                 │
│ - Suggestion generata per "Post Z"              │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│ Top Posts by Juice                              │
├─────────────────────────────────────────────────┤
│ [Table: Post | Juice | Links | Clicks]          │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│ Quick Actions                                    │
├─────────────────────────────────────────────────┤
│ [Generate Suggestions] [Check Status] [Export]  │
└─────────────────────────────────────────────────┘
```

### 3.3 Editor Integration

**Gutenberg**:
- Meta box "Internal Links" con tabs:
  - Suggestions (da WPIL)
  - Autolinks (preview)
  - Target Keywords (da WPIL)
  - Statistics (juice, links count)

**Classic Editor**:
- Meta box simile a Gutenberg
- Inline editing support

**Page Builders**:
- Supporto per: Elementor, Beaver Builder, etc. (da WPIL)
- Integration via hooks WordPress

### 3.4 Reports UI

**Link Report** (da WPIL migliorato):
- Tabs: All Links, Inbound, Outbound, Broken
- Filters: Post type, Date range, Status
- Columns: Post, Links, Juice, Clicks, Status
- Actions: Edit, Delete, Ignore

**Juice Report** (da DAIM):
- Table: URL, Juice Absolute, Juice Relative, Inbound Links
- Charts: Juice distribution
- Filters: Date range, Post type

**Click Report** (da WPIL):
- Table: Link, Post, Clicks, Date range
- Charts: Clicks over time
- Export: CSV, Excel

**Status Report** (da DAIM):
- Table: URL, Status, Last Check, Error
- Filters: Status code, Post type
- Actions: Re-check, Ignore

### 3.5 Autolinks Management UI

**List View**:
- Table: Keyword, URL, Priority, Enabled, Links Count
- Bulk actions: Enable/Disable, Delete
- Filters: Category, Term Group, Enabled

**Edit View**:
- Form completo con tutte le opzioni
- Preview: Test su contenuto di esempio
- Validation: Check keyword conflicts

**Wizard** (da DAIM):
- Step 1: Upload CSV
- Step 2: Map columns
- Step 3: Preview
- Step 4: Import

### 3.6 Suggestions UI

**Suggestions List**:
- Table: Phrase, Suggested Post, Score, Actions
- Filters: Score threshold, Post type
- Actions: Apply, Ignore, Edit

**In-Editor**:
- Sidebar con suggerimenti
- One-click apply
- Preview before apply

---

## 4. Algoritmi Unificati

### 4.1 Autolinks Algorithm Unificato

**Pseudocode**:
```
function applyAutolinks(content, post_id):
    // 1. Load autolinks rules (ordered by priority)
    rules = loadAutolinksRules(post_id)
    
    // 2. Apply protected blocks
    protected_content = applyProtectedBlocks(content)
    
    // 3. For each rule:
    for rule in rules:
        // 3.1 Check compliance
        if not checkCompliance(rule, post_id):
            continue
        
        // 3.2 Check limits
        if sameUrlLimitReached(rule.url, content):
            continue
        if maxLinksReached(rule, content):
            continue
        
        // 3.3 Match keyword
        if rule.use_stemming:
            matches = stemMatch(rule.keyword, protected_content, rule.language)
        else:
            matches = exactMatch(rule.keyword, protected_content, rule.case_insensitive)
        
        // 3.4 Apply context filtering (DAIM)
        if rule.keyword_before or rule.keyword_after:
            matches = filterByContext(matches, rule.keyword_before, rule.keyword_after)
        
        // 3.5 Apply string before/after (DAIM)
        if rule.string_before > 1 or rule.string_after > 1:
            matches = filterByStringContext(matches, rule.string_before, rule.string_after)
        
        // 3.6 Create links
        for match in matches:
            link = createLink(match, rule)
            protected_content = insertLink(protected_content, link, match.position)
    
    // 4. Remove protected blocks
    final_content = removeProtectedBlocks(protected_content)
    
    return final_content
```

### 4.2 Suggestions Algorithm Unificato

**Pseudocode**:
```
function generateSuggestions(post_id, options):
    // 1. Get post content
    content = getPostContent(post_id)
    
    // 2. Extract phrases
    phrases = extractPhrases(content, options)
    
    // 3. For each phrase:
    suggestions = []
    for phrase in phrases:
        // 3.1 Stem phrase (WPIL)
        stemmed_phrase = stem(phrase, language)
        
        // 3.2 Search in other posts
        candidate_posts = searchPosts(stemmed_phrase, options)
        
        // 3.3 Calculate similarity (WPIL)
        for candidate in candidate_posts:
            similarity = calculateSimilarity(phrase, candidate.content)
            
            // 3.4 Get juice score (DAIM)
            juice = getJuiceScore(candidate.post_id)
            
            // 3.5 Combined score
            combined_score = (similarity * 0.7) + (juice * 0.3)
            
            suggestions.append({
                'post_id': candidate.post_id,
                'phrase': phrase,
                'similarity': similarity,
                'juice': juice,
                'score': combined_score
            })
    
    // 4. Rank and filter
    suggestions = sortByScore(suggestions)
    suggestions = filterByThreshold(suggestions, options.threshold)
    
    // 5. Cache results
    cacheSuggestions(post_id, suggestions)
    
    return suggestions
```

### 4.3 Juice Calculation Algorithm

**Formula Unificata** (da DAIM):
```
function calculateJuice(post_id, link_position):
    // 1. Get SEO power
    seo_power = getPostMeta(post_id, '_gik25_il_seo_power') 
                || getOption('gik25_il_default_seo_power')
    
    // 2. Get total links in post
    total_links = countLinksInPost(post_id)
    
    // 3. Calculate juice per link
    juice_per_link = seo_power / total_links
    
    // 4. Calculate position index
    links_before = countLinksBeforePosition(post_id, link_position)
    
    // 5. Apply position penalty
    penalty_percentage = getOption('gik25_il_penalty_per_position')
    penalty = (juice_per_link / 100 * penalty_percentage) * links_before
    
    // 6. Final juice
    final_juice = max(0, juice_per_link - penalty)
    
    // 7. Calculate relative juice
    max_juice = getMaxJuiceInSite()
    relative_juice = (final_juice / max_juice) * 100
    
    return {
        'absolute': final_juice,
        'relative': relative_juice
    }
```

---

## 5. Performance Optimization

### 5.1 Caching Strategy

**Cache Layers**:
1. **Object Cache** (Redis/Memcached):
   - Autolinks rules
   - Suggestions per post
   - Juice scores
   - HTTP status

2. **Transients**:
   - Suggestions (15 minuti)
   - HTTP status (24 ore)
   - Report data (1 ora)

3. **Database Indexes**:
   - Tutti i lookup frequenti indicizzati
   - Composite indexes per query complesse

### 5.2 Batch Processing

**Background Jobs**:
- Suggestions generation: Batch di 50 post
- HTTP status check: Batch di 100 URL
- Juice calculation: Batch di 200 post
- Link processing: Batch di 100 post

**Queue System**:
- WordPress Cron per jobs schedulati
- Action Scheduler (se disponibile) per queue avanzata

### 5.3 Memory Management

**Memory Limits**:
- Batch size configurabile
- Memory break points
- Chunk processing per grandi dataset

**Optimizations**:
- Lazy loading per dati pesanti
- Compression per suggestions
- Pagination per tutte le liste

---

## 6. Security Considerations

### 6.1 Input Validation

- Sanitize tutte le keyword
- Validate URL format
- Escape output HTML
- Nonce verification per AJAX

### 6.2 Permission Checks

- Capability: `manage_options` per admin
- Capability: `edit_posts` per editor
- Filter: `gik25_il_user_can_manage` per custom

### 6.3 SQL Injection Prevention

- Prepared statements sempre
- `$wpdb->prepare()` per tutte le query
- Input sanitization

---

## 7. Testing Strategy

### 7.1 Unit Tests

- Autolinks matching
- Juice calculation
- Suggestions ranking
- Stemming accuracy

### 7.2 Integration Tests

- End-to-end autolinks application
- Suggestions generation
- Click tracking
- HTTP status checking

### 7.3 Performance Tests

- Load test con 1000+ autolinks
- Suggestions per 10000+ post
- Juice calculation batch
- HTTP status check batch

---

## 8. Migration Plan

### 8.1 Pre-Migration

1. Backup database completo
2. Export settings da DAIM
3. Export settings da WPIL
4. Documentazione stato attuale

### 8.2 Migration Steps

1. **Install nuovo plugin** (modalità compatibilità)
2. **Run migration scripts**:
   - Migra autolinks DAIM
   - Migra keywords WPIL
   - Migra links
   - Migra juice data
   - Migra click data
   - Migra HTTP status
3. **Validate dati migrati**
4. **Test funzionalità**
5. **Disattiva plugin vecchi** (opzionale, mantenere per rollback)

### 8.3 Post-Migration

1. Verifica funzionalità
2. Confronto dati (old vs new)
3. Performance check
4. User acceptance testing
5. Cleanup (opzionale)

---

## 9. Roadmap Implementazione

### Fase 1: Core Infrastructure (Settimana 1-2)
- InternalLinksManager
- LinkProcessor base
- Database schema
- Migration scripts base

### Fase 2: Autolinks Engine (Settimana 3-4)
- Portare algoritmo DAIM
- Aggiungere stemming WPIL
- Integrare context matching
- Testing

### Fase 3: Suggestions Engine (Settimana 5-6)
- Portare algoritmo WPIL
- Aggiungere juice scoring
- Ottimizzazioni performance
- Testing

### Fase 4: Reports & Monitoring (Settimana 7-8)
- Reports unificati
- Juice calculator
- HTTP status checker
- Click tracker
- Testing

### Fase 5: UI & Integration (Settimana 9-10)
- Admin interface
- Editor integration
- Export/Import
- Search Console
- Testing

### Fase 6: Migration & Polish (Settimana 11-12)
- Migration tools completi
- Testing estensivo
- Documentation
- Release

---

**Prossimo Step**: Implementazione Core (Fase 3)

