# Interfacce MCP - Specifica Tecnica Completa

**Data**: Gennaio 2025  
**Versione**: 1.0  
**Scopo**: Specifica tecnica completa delle interfacce MCP per comunicazione WordPress ↔ C#

---

## 1. Panoramica

Questo documento definisce le **interfacce MCP (Model Context Protocol)** che devono essere implementate per la comunicazione tra:
- **WordPress Plugin** (PHP) → Espone MCP Server
- **C# Desktop Application** → Implementa MCP Client e Server

### 1.1 Protocollo Base

**Trasporto**: HTTP REST API  
**Formato**: JSON  
**Autenticazione**: Bearer Token (API Key)  
**Versioning**: `/v1` nel namespace

### 1.2 Endpoint Base

**WordPress MCP Server**:
- Base URL: `https://www.totaldesign.it/wp-json/wp-mcp/v1/`
- Health Check: `GET /health`
- Tools: `POST /mcp/tools/call`
- List Tools: `GET /mcp/tools`

**C# MCP Server** (se implementato):
- Base URL: `http://localhost:5000/api/mcp/v1/`
- Health Check: `GET /health`
- Tools: `POST /tools/call`

---

## 2. Tool: Embedding

### 2.1 generate_embedding

**Descrizione**: Genera embedding per un post WordPress usando BERT o altro modello.

**Request**:
```json
POST /mcp/tools/call
Content-Type: application/json
Authorization: Bearer {api-key}

{
  "name": "generate_embedding",
  "arguments": {
    "post_id": 123,
    "content": "Testo completo del post...",
    "force_regenerate": false,
    "model": "bert-base-italian"
  }
}
```

**Response Success** (200 OK):
```json
{
  "content": {
    "embedding": [0.123, 0.456, 0.789, ...],
    "model": "bert-base-italian",
    "version": "1.0",
    "dimensions": 768,
    "generated_at": "2025-01-15T10:30:00Z",
    "post_id": 123
  },
  "isError": false,
  "responseTime": 1.234
}
```

**Response Error** (200 OK con isError: true):
```json
{
  "content": null,
  "isError": true,
  "error": "Failed to generate embedding: Model not found",
  "errorCode": "EMBEDDING_GENERATION_FAILED",
  "responseTime": 0.5
}
```

**Codici Errore**:
- `EMBEDDING_GENERATION_FAILED`: Errore generico generazione
- `MODEL_NOT_FOUND`: Modello embedding non disponibile
- `INVALID_CONTENT`: Contenuto non valido o vuoto
- `TIMEOUT`: Timeout durante generazione

**Note**:
- `embedding` è array di float con dimensioni dipendenti dal modello (768 per BERT base, 1536 per OpenAI)
- `force_regenerate`: se `true`, rigenera anche se embedding esiste già
- `model`: opzionale, default `bert-base-italian`

---

### 2.2 batch_generate_embeddings

**Descrizione**: Genera embedding per più post in batch.

**Request**:
```json
{
  "name": "batch_generate_embeddings",
  "arguments": {
    "posts": [
      {
        "post_id": 123,
        "content": "Contenuto post 1..."
      },
      {
        "post_id": 456,
        "content": "Contenuto post 2..."
      }
    ],
    "model": "bert-base-italian"
  }
}
```

**Response Success**:
```json
{
  "content": {
    "results": [
      {
        "post_id": 123,
        "embedding": [0.123, 0.456, ...],
        "success": true
      },
      {
        "post_id": 456,
        "embedding": [0.789, 0.012, ...],
        "success": true
      }
    ],
    "total": 2,
    "successful": 2,
    "failed": 0
  },
  "isError": false,
  "responseTime": 5.678
}
```

**Response Error**:
```json
{
  "content": {
    "results": [
      {
        "post_id": 123,
        "success": true,
        "embedding": [...]
      },
      {
        "post_id": 456,
        "success": false,
        "error": "Invalid content"
      }
    ],
    "total": 2,
    "successful": 1,
    "failed": 1
  },
  "isError": false,
  "responseTime": 3.456
}
```

---

## 3. Tool: Ricerca Semantica

### 3.1 semantic_search

**Descrizione**: Cerca contenuti simili usando ricerca semantica nel database vettoriale.

**Request**:
```json
{
  "name": "semantic_search",
  "arguments": {
    "query": "cucina moderna bianca",
    "limit": 10,
    "threshold": 0.7,
    "filters": {
      "post_type": "post",
      "post_status": "publish",
      "categories": ["cucina"]
    }
  }
}
```

**Response Success**:
```json
{
  "content": {
    "results": [
      {
        "post_id": 789,
        "title": "Cucina Moderna in Bianco",
        "url": "https://www.totaldesign.it/cucina-moderna-bianco/",
        "similarity": 0.89,
        "excerpt": "..."
      },
      {
        "post_id": 790,
        "title": "Arredare Cucina Bianca",
        "url": "https://www.totaldesign.it/arredare-cucina-bianca/",
        "similarity": 0.85,
        "excerpt": "..."
      }
    ],
    "total": 2,
    "query_embedding_generated": true
  },
  "isError": false,
  "responseTime": 0.234
}
```

**Codici Errore**:
- `SEARCH_FAILED`: Errore generico ricerca
- `VECTOR_DB_UNAVAILABLE`: Database vettoriale non disponibile
- `INVALID_QUERY`: Query non valida o vuota
- `EMBEDDING_GENERATION_FAILED`: Impossibile generare embedding per query

---

### 3.2 find_similar_content

**Descrizione**: Trova contenuti simili a un post specifico.

**Request**:
```json
{
  "name": "find_similar_content",
  "arguments": {
    "post_id": 123,
    "limit": 10,
    "threshold": 0.7,
    "exclude_post_id": true
  }
}
```

**Response Success**:
```json
{
  "content": {
    "post_id": 123,
    "similar_posts": [
      {
        "post_id": 456,
        "title": "...",
        "url": "...",
        "similarity": 0.88
      }
    ],
    "total": 1
  },
  "isError": false,
  "responseTime": 0.123
}
```

---

## 4. Tool: Analisi Contenuti

### 4.1 analyze_content_quality

**Descrizione**: Analizza qualità contenuto usando NLP avanzato.

**Request**:
```json
{
  "name": "analyze_content_quality",
  "arguments": {
    "post_id": 123,
    "content": "Testo completo del post...",
    "language": "it"
  }
}
```

**Response Success**:
```json
{
  "content": {
    "post_id": 123,
    "score": 0.85,
    "suggestions": [
      "Aggiungi più immagini per migliorare engagement",
      "Considera di aggiungere una call-to-action",
      "Il titolo potrebbe essere più specifico"
    ],
    "metrics": {
      "readability": 0.78,
      "seo_score": 0.82,
      "keyword_density": 0.025,
      "sentence_length": 18.5,
      "paragraph_count": 12,
      "word_count": 850
    },
    "analysis_date": "2025-01-15T10:30:00Z"
  },
  "isError": false,
  "responseTime": 2.345
}
```

**Codici Errore**:
- `ANALYSIS_FAILED`: Errore generico analisi
- `INVALID_CONTENT`: Contenuto non valido
- `LANGUAGE_NOT_SUPPORTED`: Lingua non supportata

---

### 4.2 analyze_seo

**Descrizione**: Analizza SEO di un post.

**Request**:
```json
{
  "name": "analyze_seo",
  "arguments": {
    "post_id": 123,
    "title": "Titolo post",
    "content": "Contenuto...",
    "meta_description": "Meta description...",
    "url": "https://www.totaldesign.it/post-url/"
  }
}
```

**Response Success**:
```json
{
  "content": {
    "post_id": 123,
    "seo_score": 0.82,
    "issues": [
      {
        "type": "missing_meta_description",
        "severity": "medium",
        "message": "Meta description mancante"
      }
    ],
    "suggestions": [
      "Aggiungi meta description ottimizzata",
      "Considera di aggiungere più link interni"
    ],
    "keywords": {
      "primary": "cucina moderna",
      "secondary": ["arredamento", "design"]
    }
  },
  "isError": false,
  "responseTime": 1.567
}
```

---

## 5. Tool: Wikidata

### 5.1 wikidata_enrich

**Descrizione**: Arricchisce contenuto con dati Wikidata.

**Request**:
```json
{
  "name": "wikidata_enrich",
  "arguments": {
    "post_id": 123,
    "entity_id": "Q12345",
    "language": "it"
  }
}
```

**Response Success**:
```json
{
  "content": {
    "post_id": 123,
    "entity_id": "Q12345",
    "entity_data": {
      "label": "Cucina",
      "description": "Stanza per preparazione cibo",
      "aliases": ["cucina moderna", "cucina design"],
      "properties": {
        "P31": ["Q123456"], // instance of
        "P361": ["Q789012"] // part of
      }
    },
    "microdata": {
      "schema_type": "Thing",
      "properties": {
        "name": "Cucina",
        "description": "..."
      }
    },
    "semantic_links": [
      {
        "text": "Arredamento",
        "url": "https://www.wikidata.org/wiki/Q789",
        "type": "related"
      }
    ]
  },
  "isError": false,
  "responseTime": 0.789
}
```

**Codici Errore**:
- `WIKIDATA_ENRICH_FAILED`: Errore generico enrichment
- `ENTITY_NOT_FOUND`: Entità Wikidata non trovata
- `SPARQL_QUERY_FAILED`: Errore query SPARQL

---

### 5.2 wikidata_search

**Descrizione**: Cerca entità Wikidata per keyword.

**Request**:
```json
{
  "name": "wikidata_search",
  "arguments": {
    "query": "cucina",
    "language": "it",
    "limit": 10
  }
}
```

**Response Success**:
```json
{
  "content": {
    "results": [
      {
        "entity_id": "Q12345",
        "label": "Cucina",
        "description": "Stanza per preparazione cibo",
        "score": 0.95
      },
      {
        "entity_id": "Q67890",
        "label": "Cucina (arredamento)",
        "description": "Mobili per cucina",
        "score": 0.82
      }
    ],
    "total": 2
  },
  "isError": false,
  "responseTime": 0.456
}
```

---

### 5.3 wikidata_query

**Descrizione**: Esegue query SPARQL personalizzata su Wikidata.

**Request**:
```json
{
  "name": "wikidata_query",
  "arguments": {
    "sparql": "SELECT ?item ?itemLabel WHERE { ?item wdt:P31 wd:Q12345 . SERVICE wikibase:label { bd:serviceParam wikibase:language \"it\" } }",
    "format": "json"
  }
}
```

**Response Success**:
```json
{
  "content": {
    "results": {
      "bindings": [
        {
          "item": { "value": "http://www.wikidata.org/entity/Q123" },
          "itemLabel": { "value": "Cucina" }
        }
      ]
    },
    "query_time": 0.123
  },
  "isError": false,
  "responseTime": 0.234
}
```

---

## 6. Tool: Health Check

### 6.1 health_check

**Descrizione**: Verifica disponibilità e stato del server MCP.

**Request**:
```json
{
  "name": "health_check",
  "arguments": {}
}
```

**Response Success**:
```json
{
  "content": {
    "available": true,
    "version": "1.0.0",
    "services": {
      "embedding_generator": {
        "available": true,
        "models": ["bert-base-italian", "bert-base-multilingual"]
      },
      "vector_db": {
        "available": true,
        "collections": ["wordpress_content"]
      },
      "wikidata": {
        "available": true,
        "endpoint": "https://query.wikidata.org/sparql"
      }
    },
    "uptime": 3600,
    "timestamp": "2025-01-15T10:30:00Z"
  },
  "isError": false,
  "responseTime": 0.012
}
```

---

## 7. Autenticazione

### 7.1 Header Authorization

**Formato**:
```
Authorization: Bearer {api-key}
```

**Esempio**:
```
Authorization: Bearer abc123def456ghi789
```

### 7.2 Errori Autenticazione

**Response 401 Unauthorized**:
```json
{
  "error": "Unauthorized",
  "message": "Invalid or missing API key",
  "errorCode": "AUTH_FAILED"
}
```

**Response 403 Forbidden**:
```json
{
  "error": "Forbidden",
  "message": "API key does not have permission for this tool",
  "errorCode": "AUTH_PERMISSION_DENIED"
}
```

---

## 8. Gestione Errori

### 8.1 Codici Errore Standard

**Categorie**:
- `AUTH_*`: Errori autenticazione
- `EMBEDDING_*`: Errori generazione embedding
- `SEARCH_*`: Errori ricerca semantica
- `ANALYSIS_*`: Errori analisi contenuti
- `WIKIDATA_*`: Errori Wikidata
- `SYSTEM_*`: Errori sistema generici

### 8.2 Formato Errore

**Response con Errore** (200 OK):
```json
{
  "content": null,
  "isError": true,
  "error": "Human-readable error message",
  "errorCode": "ERROR_CODE",
  "errorDetails": {
    "field": "additional error context"
  },
  "responseTime": 0.123
}
```

**Response HTTP Error** (4xx/5xx):
```json
{
  "error": "Bad Request",
  "message": "Invalid request parameters",
  "errorCode": "INVALID_REQUEST",
  "details": {
    "field": "post_id",
    "issue": "must be a positive integer"
  }
}
```

---

## 9. Versioning

### 9.1 Versioning API

**URL Pattern**:
```
/wp-json/wp-mcp/v1/{tool}
```

**Versioning**:
- Versione corrente: `v1`
- Breaking changes: incrementare a `v2`
- Backward compatibility: mantenere `v1` per almeno 6 mesi

### 9.2 Versioning Tool

Ogni tool può avere una versione interna:

```json
{
  "name": "generate_embedding",
  "version": "1.0",
  "arguments": {...}
}
```

---

## 10. Rate Limiting

### 10.1 Limiti

**Default**:
- 100 richieste/minuto per IP
- 10 richieste/secondo per tool pesante (embedding, analisi)

**Response 429 Too Many Requests**:
```json
{
  "error": "Rate limit exceeded",
  "message": "Too many requests. Please try again later.",
  "errorCode": "RATE_LIMIT_EXCEEDED",
  "retryAfter": 60
}
```

**Header Response**:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1642234560
Retry-After: 60
```

---

## 11. Timeout

### 11.1 Timeout Tool

**Default**: 30 secondi

**Tool Specifici**:
- `generate_embedding`: 60 secondi
- `batch_generate_embeddings`: 300 secondi
- `wikidata_query`: 30 secondi
- Altri: 30 secondi

**Response Timeout**:
```json
{
  "content": null,
  "isError": true,
  "error": "Request timeout",
  "errorCode": "TIMEOUT",
  "responseTime": 30.0
}
```

---

## 12. Esempi Completi

### 12.1 Workflow Completo: Genera Embedding e Cerca Simili

**Step 1: Genera Embedding**
```bash
curl -X POST https://www.totaldesign.it/wp-json/wp-mcp/v1/embedding/generate \
  -H "Authorization: Bearer {api-key}" \
  -H "Content-Type: application/json" \
  -d '{
    "post_id": 123,
    "content": "Testo post...",
    "model": "bert-base-italian"
  }'
```

**Step 2: Cerca Simili**
```bash
curl -X POST https://www.totaldesign.it/wp-json/wp-mcp/v1/semantic/search \
  -H "Authorization: Bearer {api-key}" \
  -H "Content-Type: application/json" \
  -d '{
    "query": "cucina moderna",
    "limit": 10
  }'
```

### 12.2 Workflow: Arricchisci con Wikidata

**Step 1: Cerca Entità**
```bash
curl -X POST https://www.totaldesign.it/wp-json/wp-mcp/v1/wikidata/search \
  -H "Authorization: Bearer {api-key}" \
  -H "Content-Type: application/json" \
  -d '{
    "query": "cucina",
    "language": "it"
  }'
```

**Step 2: Arricchisci Post**
```bash
curl -X POST https://www.totaldesign.it/wp-json/wp-mcp/v1/wikidata/enrich \
  -H "Authorization: Bearer {api-key}" \
  -H "Content-Type: application/json" \
  -d '{
    "post_id": 123,
    "entity_id": "Q12345"
  }'
```

---

## 13. Testing

### 13.1 Test Tool

**Health Check**:
```bash
curl https://www.totaldesign.it/wp-json/wp-mcp/v1/health
```

**List Tools**:
```bash
curl https://www.totaldesign.it/wp-json/wp-mcp/v1/mcp/tools
```

### 13.2 Test con Postman

**Collection JSON** disponibile in `docs/postman/mcp-collection.json`

---

## 14. Riferimenti

- **Guida Sviluppatore C#**: `docs/GUIDA_SVILUPPATORE_CSHARP.md`
- **Architettura Sistema**: `ARCHITETTURA_SISTEMA_IBRIDO.md`
- **MCP Documentation**: `docs/MCP.md`
- **Model Context Protocol**: https://modelcontextprotocol.io

---

**Documento creato**: Gennaio 2025  
**Versione**: 1.0  
**Autore**: Sistema di analisi progetti

