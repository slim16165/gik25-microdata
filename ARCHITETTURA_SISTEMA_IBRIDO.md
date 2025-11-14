# Architettura Sistema Ibrido C#/WordPress
## Automatizzazione Miglioramento Siti con MCP, Database Vettoriali, Embedding e Wikidata

**Data**: Gennaio 2025  
**Versione**: 1.0

---

## 1. Visione Generale

### 1.1 Obiettivo
Costruire un sistema ibrido **online (WordPress) + offline (C#)** che utilizzi:
- **Database vettoriali** per ricerca semantica
- **MCP (Model Context Protocol)** per comunicazione agentica
- **Progetti semantici** (Wikidata, RDF, SPARQL)
- **Embedding** per comprensione semantica contenuti
- **NLP avanzato** per analisi e miglioramento testi
- **Tool agentici** per automazione intelligente

### 1.2 Architettura High-Level

```
┌─────────────────────────────────────────────────────────────────┐
│                    SISTEMA IBRIDO                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────────┐         ┌──────────────────┐            │
│  │   C# Desktop     │ ◄─────► │  WordPress Plugin │            │
│  │   (Offline)      │   MCP   │     (Online)      │            │
│  └──────────────────┘         └──────────────────┘            │
│         │                              │                      │
│         │                              │                      │
│         ▼                              ▼                      │
│  ┌──────────────────┐         ┌──────────────────┐            │
│  │ Database         │         │ Database         │            │
│  │ Vettoriali       │         │ WordPress        │            │
│  │ (Embedding)      │         │ (MySQL)          │            │
│  └──────────────────┘         └──────────────────┘            │
│         │                              │                      │
│         │                              │                      │
│         └──────────────┬───────────────┘                      │
│                        │                                      │
│                        ▼                                      │
│              ┌──────────────────┐                            │
│              │   Wikidata API   │                            │
│              │   SPARQL/RDF     │                            │
│              └──────────────────┘                            │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. Componenti del Sistema

### 2.1 C# Desktop (Offline)

#### 2.1.1 SeozoomContainer (Consolidato)
**Ruolo**: Hub centrale per analisi SEO, ranking SERP, e gestione WordPress

**Funzionalità**:
- ✅ Analisi SEO avanzata
- ✅ Ranking SERP tracking
- ✅ Analisi revisioni WordPress (integrazione WordpressPostRevisionsAnalyzer)
- ✅ Generazione embedding per contenuti
- ✅ Interfaccia MCP client per comunicazione con WordPress
- ✅ Storage database vettoriali locale (SQLite + embedding)

**Tecnologie**:
- .NET Framework 4.8 → **Migrare a .NET 8.0**
- WPF per UI
- SQLite per storage locale
- **Nuovo**: Integrazione database vettoriali (Qdrant/Chroma locale)

#### 2.1.2 TextProcessingSuite (Integrato)
**Ruolo**: Elaborazione NLP avanzata, embedding, confronto semantico

**Funzionalità**:
- ✅ Allineamento semantico con BERT
- ✅ Generazione embedding
- ✅ Confronto similarità testi
- ✅ Template mining
- ✅ Analisi pattern testuali

**Integrazione**:
- Libreria condivisa utilizzata da SeozoomContainer
- Generazione embedding per contenuti WordPress
- Analisi qualità testi

#### 2.1.3 WikifySmart (Completato)
**Ruolo**: Integrazione Wikidata, enrichment contenuti con dati strutturati

**Funzionalità**:
- ✅ Query SPARQL per Wikidata
- ✅ Recupero entità Wikidata
- ✅ Enrichment contenuti con dati strutturati
- ✅ Generazione link semantici
- ✅ Validazione dati con Wikidata

**Integrazione**:
- Modulo in SeozoomContainer o libreria condivisa
- Chiamate API Wikidata per arricchimento contenuti
- Validazione microdata con Wikidata

#### 2.1.4 WikiHelper (Integrato in WikifySmart)
**Ruolo**: Scraping categorie MediaWiki per input analisi

**Funzionalità**:
- Scraping categorie wiki
- Estrazione link e metadata
- Input per analisi Wikidata

---

### 2.2 WordPress Plugin (Online)

#### 2.2.1 Plugin gik25-microdata (Esteso)
**Ruolo**: Sistema online per gestione contenuti, widget, SEO, e interfaccia MCP

**Funzionalità Esistenti**:
- ✅ Shortcode e widget interattivi
- ✅ Sistema MCP server (REST API)
- ✅ Health check e monitoring
- ✅ SEO e microdata
- ✅ Widget contestuali

**Nuove Funzionalità da Implementare**:

##### A. Sistema Embedding
```php
// Nuova classe: include/class/Embedding/EmbeddingManager.php
class EmbeddingManager {
    // Genera embedding per contenuti WordPress
    public function generateEmbedding($post_id);
    
    // Cerca contenuti simili usando embedding
    public function findSimilarContent($post_id, $limit = 10);
    
    // Aggiorna embedding quando contenuto cambia
    public function updateEmbedding($post_id);
    
    // Batch generation per tutti i post
    public function batchGenerateEmbeddings($post_ids);
}
```

**Storage**:
- Opzione 1: Meta WordPress (`wp_postmeta`) con serializzazione JSON
- Opzione 2: Tabella custom `wp_embeddings` (post_id, embedding_vector, model_version)
- Opzione 3: Database vettoriale esterno (Qdrant/Chroma) con sync

##### B. Database Vettoriale Integration
```php
// Nuova classe: include/class/VectorDB/VectorDBClient.php
class VectorDBClient {
    // Connessione a database vettoriale (Qdrant/Chroma)
    private $vector_db;
    
    // Inserisce embedding nel database vettoriale
    public function insertEmbedding($post_id, $embedding, $metadata);
    
    // Ricerca semantica
    public function semanticSearch($query_embedding, $limit = 10);
    
    // Aggiorna embedding esistente
    public function updateEmbedding($post_id, $embedding);
    
    // Rimuove embedding
    public function deleteEmbedding($post_id);
}
```

**Configurazione**:
- Endpoint database vettoriale (locale o cloud)
- API key per autenticazione
- Collection/namespace per sito WordPress

##### C. Wikidata Integration
```php
// Nuova classe: include/class/Wikidata/WikidataEnricher.php
class WikidataEnricher {
    // Arricchisce contenuto con dati Wikidata
    public function enrichContent($post_id, $entity_id);
    
    // Cerca entità Wikidata per keyword
    public function searchEntity($query, $lang = 'it');
    
    // Recupera proprietà entità
    public function getEntityProperties($entity_id);
    
    // Genera microdata da Wikidata
    public function generateMicrodata($entity_id);
    
    // Validazione dati con Wikidata
    public function validateWithWikidata($data);
}
```

##### D. MCP Server Esteso
```php
// Estendere: include/class/REST/MCPApi.php

// Nuovi endpoint MCP:
// POST /wp-json/wp-mcp/v1/embedding/generate
// POST /wp-json/wp-mcp/v1/embedding/search
// POST /wp-json/wp-mcp/v1/wikidata/enrich
// POST /wp-json/wp-mcp/v1/wikidata/search
// GET  /wp-json/wp-mcp/v1/semantic/similar
```

##### E. Widget Intelligenti
```php
// Nuova classe: include/class/Widgets/SemanticWidget.php
class SemanticWidget {
    // Widget che mostra contenuti simili usando embedding
    public function renderSimilarContent($post_id);
    
    // Widget che mostra dati Wikidata
    public function renderWikidataInfo($entity_id);
    
    // Widget che suggerisce miglioramenti basati su NLP
    public function renderImprovementSuggestions($post_id);
}
```

---

### 2.3 MCP Bridge (Comunicazione)

#### 2.3.1 MCP Server WordPress (Esteso)
**File**: `mcp-server/server.js` e `mcp-server/server-http.js`

**Nuovi Tool MCP**:
```javascript
{
    name: 'generate_embedding',
    description: 'Genera embedding per un post WordPress',
    inputSchema: {
        type: 'object',
        properties: {
            post_id: { type: 'number' },
            force_regenerate: { type: 'boolean', default: false }
        }
    }
},
{
    name: 'semantic_search',
    description: 'Cerca contenuti simili usando ricerca semantica',
    inputSchema: {
        type: 'object',
        properties: {
            query: { type: 'string' },
            limit: { type: 'number', default: 10 }
        }
    }
},
{
    name: 'wikidata_enrich',
    description: 'Arricchisce contenuto con dati Wikidata',
    inputSchema: {
        type: 'object',
        properties: {
            post_id: { type: 'number' },
            entity_id: { type: 'string' }
        }
    }
},
{
    name: 'analyze_content_quality',
    description: 'Analizza qualità contenuto usando NLP',
    inputSchema: {
        type: 'object',
        properties: {
            post_id: { type: 'number' }
        }
    }
}
```

#### 2.3.2 MCP Client C#
**Nuovo progetto**: `SeozoomContainer.MCPClient`

**Funzionalità**:
- Client MCP per comunicazione con WordPress
- Chiamate asincrone ai tool MCP
- Gestione autenticazione e errori
- Cache locale per risultati

---

## 3. Database Vettoriali

### 3.1 Scelta Tecnologia

**Opzioni**:
1. **Qdrant** (Raccomandato)
   - Open source, performante
   - Supporto cloud e self-hosted
   - API REST semplice
   - Buona documentazione

2. **Chroma**
   - Semplice da usare
   - Python-first ma ha API REST
   - Buono per prototipi

3. **Pinecone**
   - Managed service
   - Costoso per produzione
   - Buono per MVP

**Raccomandazione**: **Qdrant self-hosted** per controllo completo e costi contenuti.

### 3.2 Schema Database Vettoriale

**Collection**: `wordpress_content`

**Vettori**:
- **ID**: `post_id` (es: `post_123`)
- **Vector**: Array embedding (768 dimensioni per BERT base, 1536 per OpenAI)
- **Metadata**:
  ```json
  {
    "post_id": 123,
    "post_title": "Titolo post",
    "post_type": "post",
    "post_status": "publish",
    "post_date": "2025-01-15",
    "categories": ["cucina", "design"],
    "tags": ["ikea", "metod"],
    "author_id": 1,
    "site": "totaldesign.it",
    "embedding_model": "bert-base-italian",
    "embedding_version": "1.0"
  }
  ```

### 3.3 Workflow Embedding

```
1. Nuovo/Modificato Post WordPress
   ↓
2. Hook WordPress: save_post
   ↓
3. Plugin genera embedding (o chiama C# via MCP)
   ↓
4. Salva embedding in:
   - wp_postmeta (backup)
   - Database vettoriale (ricerca)
   ↓
5. Aggiorna indici ricerca semantica
```

---

## 4. Flussi di Lavoro

### 4.1 Miglioramento Contenuti Automatico

```
┌─────────────────────────────────────────────────────────────┐
│ 1. C# Analizza Contenuto WordPress via MCP                    │
│    - Recupera post                                            │
│    - Genera embedding                                         │
│    - Analizza qualità con NLP                                │
└─────────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. C# Cerca Miglioramenti                                    │
│    - Ricerca semantica contenuti simili                      │
│    - Confronta con contenuti top-performing                  │
│    - Identifica gap e suggerimenti                          │
└─────────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. C# Arricchisce con Wikidata                               │
│    - Cerca entità Wikidata correlate                         │
│    - Recupera proprietà e dati strutturati                   │
│    - Genera microdata Schema.org                             │
└─────────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. C# Propone Modifiche via MCP                              │
│    - Suggerimenti testo                                      │
│    - Link semantici                                          │
│    - Microdata da aggiungere                                 │
│    - Tag/categorie suggerite                                │
└─────────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. WordPress Applica Modifiche                              │
│    - Preview modifiche                                       │
│    - Approvazione utente (opzionale)                          │
│    - Applicazione automatica o manuale                       │
└─────────────────────────────────────────────────────────────┘
```

### 4.2 Ricerca Semantica Contenuti

```
Utente cerca: "cucina moderna bianca"
   ↓
WordPress Plugin:
  1. Genera embedding query
  2. Cerca nel database vettoriale
  3. Restituisce risultati ordinati per similarità
   ↓
Risultati:
  - Post con contenuti semanticamente simili
  - Non solo keyword matching
  - Ranking basato su similarità semantica
```

### 4.3 Widget Contestuali Intelligenti

```
Post su "Cucina IKEA Metod"
   ↓
Plugin analizza embedding post
   ↓
Cerca nel database vettoriale:
  - Post simili
  - Prodotti correlati
  - Guide correlate
   ↓
Widget mostra automaticamente:
  - "Contenuti simili" (semantic search)
  - "Prodotti correlati" (Wikidata)
  - "Guide utili" (categoria + embedding)
```

---

## 5. Implementazione: Cosa va in C# vs WordPress

### 5.1 C# Desktop (Offline) - COMPETENZE

#### ✅ Analisi Pesante e NLP
- **Generazione embedding**: Usa TextProcessingSuite con BERT
- **Analisi qualità testo**: NLP avanzato, confronto semantico
- **Template mining**: Pattern recognition testi
- **Analisi SEO avanzata**: Ranking SERP, competitor analysis
- **Batch processing**: Elaborazione massiva contenuti

#### ✅ Database Vettoriali Locale
- **Storage embedding**: Database vettoriale locale (Qdrant self-hosted)
- **Ricerca semantica locale**: Per analisi offline
- **Training modelli**: Fine-tuning embedding models
- **Analisi pattern**: Cluster analysis, topic modeling

#### ✅ Integrazione Wikidata
- **Query SPARQL complesse**: WikifySmart
- **Enrichment batch**: Elaborazione massiva con Wikidata
- **Validazione dati**: Confronto dati WordPress vs Wikidata
- **Generazione link semantici**: Collegamenti intelligenti

#### ✅ MCP Client
- **Comunicazione con WordPress**: Chiamate MCP tool
- **Orchestrazione workflow**: Coordinamento operazioni complesse
- **Cache locale**: Storage risultati per analisi offline

---

### 5.2 WordPress Plugin (Online) - COMPETENZE

#### ✅ Gestione Contenuti
- **CRUD post/pages**: Creazione, modifica, eliminazione
- **Gestione media**: Upload, organizzazione immagini
- **Taxonomies**: Categorie, tag, custom taxonomies
- **REST API**: Esposizione dati per MCP

#### ✅ Sistema Embedding (Lightweight)
- **Hook WordPress**: Intercetta salvataggio post
- **Chiamata MCP**: Richiede embedding a C# (o genera localmente se possibile)
- **Storage embedding**: Salva in wp_postmeta o tabella custom
- **Sync database vettoriale**: Inserisce/aggiorna embedding nel DB vettoriale

#### ✅ Ricerca Semantica (Online)
- **Endpoint ricerca**: REST API per ricerca semantica
- **Query database vettoriale**: Chiamate a Qdrant/Chroma
- **Ranking risultati**: Combinazione similarità + SEO score
- **Cache risultati**: Cache query frequenti

#### ✅ Widget e UI
- **Widget contestuali**: Mostra contenuti simili
- **Admin UI**: Interfaccia gestione embedding, Wikidata
- **Preview modifiche**: Anteprima suggerimenti C#
- **Dashboard analytics**: Statistiche miglioramenti

#### ✅ MCP Server
- **Esposizione tool**: Endpoint MCP per C# client
- **Autenticazione**: API key, OAuth
- **Rate limiting**: Protezione abusi
- **Logging**: Tracciamento chiamate MCP

#### ✅ Wikidata Integration (Lightweight)
- **Cache entità**: Cache locale entità Wikidata frequenti
- **Microdata generation**: Schema.org markup da Wikidata
- **Link generation**: Collegamenti intelligenti a Wikidata
- **Validazione**: Controllo coerenza dati

---

## 6. Architettura Dettagliata

### 6.1 Stack Tecnologico

#### C# Desktop
```
SeozoomContainer (Consolidato)
├── .NET 8.0 (migrazione da 4.8)
├── WPF per UI
├── TextProcessingSuite (libreria)
│   ├── BERT embedding generation
│   ├── Semantic comparison
│   └── NLP analysis
├── WikifySmart (modulo)
│   ├── SPARQL queries
│   ├── Wikidata API client
│   └── RDF processing
├── MCP Client
│   ├── Communication with WordPress
│   └── Tool orchestration
└── Vector DB Client
    ├── Qdrant client
    └── Local embedding storage
```

#### WordPress Plugin
```
gik25-microdata (Esteso)
├── PHP 8.1+
├── WordPress REST API
├── MCP Server (REST endpoints)
├── Embedding Manager
│   ├── Generation hooks
│   ├── Storage (wp_postmeta/custom table)
│   └── Vector DB sync
├── Vector DB Client
│   └── Qdrant/Chroma integration
├── Wikidata Enricher
│   ├── Entity search
│   ├── Microdata generation
│   └── Cache management
└── Semantic Widgets
    ├── Similar content
    ├── Wikidata info
    └── Improvement suggestions
```

### 6.2 Database Schema

#### WordPress (MySQL)
```sql
-- Tabella custom per embedding (opzionale)
CREATE TABLE wp_embeddings (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT NOT NULL,
    embedding_model VARCHAR(100),
    embedding_version VARCHAR(20),
    embedding_vector LONGTEXT, -- JSON array
    created_at DATETIME,
    updated_at DATETIME,
    INDEX idx_post_id (post_id),
    INDEX idx_model (embedding_model)
);

-- Tabella cache Wikidata
CREATE TABLE wp_wikidata_cache (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    entity_id VARCHAR(50),
    entity_data LONGTEXT, -- JSON
    cached_at DATETIME,
    expires_at DATETIME,
    INDEX idx_entity_id (entity_id),
    INDEX idx_expires (expires_at)
);
```

#### Database Vettoriale (Qdrant)
```
Collection: wordpress_content
- Vectors: 768-dim (BERT) o 1536-dim (OpenAI)
- Metadata: post_id, title, type, categories, tags, etc.
- Distance: Cosine similarity
```

---

## 7. Piano di Implementazione

### Fase 1: Fondamenta (Settimane 1-4)
1. **Consolidamento C#**
   - Deduplicazione SeozoomContainer
   - Migrazione .NET Framework 4.8 → .NET 8.0
   - Integrazione WordpressPostRevisionsAnalyzer
   - Setup MCP Client

2. **WordPress Plugin Base**
   - Estensione MCP server con nuovi tool
   - Setup struttura EmbeddingManager
   - Setup VectorDBClient base

### Fase 2: Embedding System (Settimane 5-8)
1. **C# Embedding Generation**
   - Integrazione TextProcessingSuite
   - Generazione embedding con BERT
   - Storage locale

2. **WordPress Integration**
   - Hook save_post per generazione embedding
   - Chiamata MCP per embedding generation
   - Storage embedding in WordPress
   - Sync database vettoriale

### Fase 3: Ricerca Semantica (Settimane 9-12)
1. **Database Vettoriale**
   - Setup Qdrant (self-hosted o cloud)
   - Popolazione iniziale embedding
   - Endpoint ricerca semantica

2. **Widget Intelligenti**
   - Widget contenuti simili
   - Integrazione ricerca semantica
   - Ranking risultati

### Fase 4: Wikidata Integration (Settimane 13-16)
1. **C# Wikidata**
   - Completamento WikifySmart
   - Query SPARQL avanzate
   - Enrichment batch

2. **WordPress Wikidata**
   - WikidataEnricher class
   - Cache entità
   - Microdata generation
   - Widget Wikidata info

### Fase 5: Automazione Intelligente (Settimane 17-20)
1. **Analisi Automatica**
   - C# analizza contenuti via MCP
   - Generazione suggerimenti
   - Ranking miglioramenti

2. **Applicazione Modifiche**
   - Preview modifiche WordPress
   - Approvazione utente
   - Applicazione automatica/manuale

---

## 8. Considerazioni Tecniche

### 8.1 Performance
- **Embedding generation**: Costoso computazionalmente → fare offline in C#
- **Database vettoriale**: Query veloci ma necessita indicizzazione
- **Cache**: Aggressiva per entità Wikidata e embedding
- **Batch processing**: Elaborazione notturna per contenuti esistenti

### 8.2 Scalabilità
- **Database vettoriale**: Qdrant scala bene, considerare sharding per siti multipli
- **WordPress**: Hook asincroni per operazioni pesanti
- **MCP**: Rate limiting per protezione server

### 8.3 Sicurezza
- **MCP Authentication**: API key forte, OAuth opzionale
- **Embedding storage**: Non contiene dati sensibili ma validare input
- **Wikidata cache**: Validazione dati esterni

### 8.4 Costi
- **Database vettoriale**: Qdrant self-hosted = costo server
- **Embedding generation**: CPU-intensive, considerare GPU per batch
- **Wikidata API**: Gratuita ma rate-limited
- **Storage**: Embedding occupano spazio (768 float = ~3KB per post)

---

## 9. Metriche di Successo

### 9.1 Qualità Contenuti
- Miglioramento score SEO (RankMath/Yoast)
- Aumento engagement (tempo lettura, bounce rate)
- Miglioramento ranking SERP

### 9.2 Automazione
- % contenuti migliorati automaticamente
- Tempo risparmiato vs editing manuale
- Accuratezza suggerimenti (approvazione utente)

### 9.3 Performance Sistema
- Tempo generazione embedding
- Latenza ricerca semantica
- Throughput MCP calls

---

## 10. Prossimi Step

1. ✅ **Approvazione architettura**: Review e feedback
2. ⏳ **Setup ambiente sviluppo**: Qdrant, .NET 8.0, PHP 8.1+
3. ⏳ **Fase 1 implementazione**: Consolidamento C# e WordPress base
4. ⏳ **Testing**: Unit test, integration test, performance test
5. ⏳ **Documentazione**: API docs, user guide, developer guide

---

**Documento creato**: Gennaio 2025  
**Versione**: 1.0  
**Autore**: Sistema di analisi progetti

