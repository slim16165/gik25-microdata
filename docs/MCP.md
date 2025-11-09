# MCP Server - Documentazione Completa

## 📋 Panoramica

Sistema MCP (Model Context Protocol) per interrogare TotalDesign.it WordPress tramite REST API. Il sistema è composto da:

1. **REST API WordPress (PHP)** - Espone dati via endpoint REST
2. **MCP Server Node.js** - Client MCP che chiama la REST API
3. **Cursor Integration** - Integrazione con Cursor per sviluppo assistito

## 🏗️ Architettura

### Componenti

- **Backend WordPress / REST API (PHP)**: Sul server Cloudways, espone endpoint REST API
- **MCP Server Node.js**: Locale sul tuo computer, comunica con Cursor via stdio
- **Cursor**: Locale, interfaccia per sviluppare

### Flusso

```
Cursor (locale) 
    ↓ stdio
MCP Server Node.js (locale)
    ↓ HTTP request
REST API WordPress (Cloudways)
    ↓ query
Database WordPress (Cloudways)
```

**⚠️ IMPORTANTE:**
- Il server MCP Node.js **NON** gira su Cloudways
- Il server MCP Node.js gira **sul tuo computer locale**
- Il server MCP Node.js fa HTTP request al backend WordPress su Cloudways

## 🚀 Installazione

### Prerequisiti

- Node.js 18+ installato
- Plugin WordPress con REST API attiva
- Cursor installato

### Step 1: Installa Dipendenze Node.js

```bash
cd mcp-server
npm install
```

### Step 2: Verifica REST API WordPress

Testa in browser:
```
https://www.totaldesign.it/wp-json/wp-mcp/v1/categories
```

### Step 3: Configura Cursor

Aggiungi nel file di configurazione MCP di Cursor:

**Windows:**
```
%APPDATA%\Cursor\User\globalStorage\saoudrizwan.claude-dev\settings\cline_mcp_settings.json
```

**macOS/Linux:**
```
~/.config/Cursor/User/globalStorage/saoudrizwan.claude-dev/settings/cline_mcp_settings.json
```

**Configurazione:**
```json
{
  "mcpServers": {
    "totaldesign": {
      "command": "node",
      "args": ["/percorso/completo/a/mcp-server/server.js"],
      "env": {
        "WP_BASE_URL": "https://www.totaldesign.it"
      }
    }
  }
}
```

**Nota:** Sostituisci `/percorso/completo/a/mcp-server/server.js` con il percorso assoluto corretto.

### Step 4: Riavvia Cursor

Chiudi e riapri Cursor completamente.

## 📡 REST API Endpoints

### Lettura Dati

- `GET /wp-json/wp-mcp/v1/categories` - Lista categorie
- `GET /wp-json/wp-mcp/v1/posts/search?q={query}` - Ricerca post
- `GET /wp-json/wp-mcp/v1/posts/category/{slug}` - Post per categoria
- `GET /wp-json/wp-mcp/v1/posts/color/{color}` - Post per colore
- `GET /wp-json/wp-mcp/v1/posts/ikea/{line}` - Post per linea IKEA
- `GET /wp-json/wp-mcp/v1/posts/room/{room}` - Post per stanza
- `GET /wp-json/wp-mcp/v1/posts/pantone` - Post Pantone
- `GET /wp-json/wp-mcp/v1/posts/popular` - Post popolari
- `GET /wp-json/wp-mcp/v1/posts/recent` - Post recenti

### Modifica (richiede autenticazione)

- `POST /wp-json/wp/v2/posts/{id}` - Modifica articolo
- `POST /wp-json/wp/v2/tags` - Crea tag
- `POST /wp-json/wp/v2/posts/{id}` - Aggiorna tag di un post

## 🛠️ Tools MCP Disponibili

### Lettura Dati

- `get_categories` - Lista categorie
- `search_posts` - Ricerca post
- `get_posts_by_category` - Post per categoria
- `get_posts_by_color` - Post per colore
- `get_posts_by_ikea_line` - Post per linea IKEA
- `get_posts_by_room` - Post per stanza
- `get_pantone_posts` - Post Pantone
- `get_post_full` - Post completo con contenuto

### Analisi

- `analyze_widget_suggestions` - Suggerisci widget basati su contenuti
- `analyze_patterns` - Analizza pattern comuni

### Modifica (richiede autenticazione)

- `update_post` - Modifica articolo
- `create_tag` - Crea tag
- `add_tags_to_post` - Aggiungi tag a post

### Tag

- `get_tags` - Lista tag (con ricerca)
- `get_post_tags` - Tag di un post
- `add_tags_to_post` - Aggiungi tag (crea se non esistono)

## 🔐 Autenticazione

Per modificare articoli, configura un'Application Password WordPress:

1. WordPress Admin → Utenti → Profilo
2. Crea "Application Password"
3. Codifica in base64: `username:application_password`
4. Aggiungi a configurazione MCP come variabile d'ambiente `WP_AUTH`

## 🧪 Test

### Test REST API

```bash
# Lista categorie
curl "https://www.totaldesign.it/wp-json/wp-mcp/v1/categories"

# Post recenti
curl "https://www.totaldesign.it/wp-json/wp-mcp/v1/posts/recent?limit=5"

# Ricerca
curl "https://www.totaldesign.it/wp-json/wp-mcp/v1/posts/search?q=ikea&limit=5"
```

### Test MCP Server

In Cursor, prova:
- "Mostrami tutte le categorie WordPress"
- "Cerca post su IKEA METOD"
- "Ottieni 10 post sul colore bianco"

## 🔍 Troubleshooting

### REST API non risponde (404)

1. Verifica che il plugin sia attivo
2. Controlla che `MCPApi::init()` sia chiamato in `totaldesign_specific.php`
3. Vai in WordPress Admin → Impostazioni → Permalink e salva

### MCP Server non si connette

1. Verifica il percorso assoluto in `cline_mcp_settings.json`
2. Assicurati che Node.js sia installato (`node --version`)
3. Controlla che le dipendenze siano installate (`npm install` in `mcp-server/`)
4. Riavvia Cursor completamente

### Dati non aggiornati

- La cache è di 1 ora
- Svuota cache WordPress se necessario

## 📚 Documentazione Aggiuntiva

Per dettagli specifici, consulta:
- `MCP_ARCHITECTURE.md` - Architettura dettagliata
- `MCP_SETUP.md` - Setup passo-passo
- `TEST_MCP.md` - Guida test
- `DEPLOY_MCP.md` - Deploy e configurazione produzione

## ✅ Funzionalità Implementate

### REST API WordPress
- ✅ Lettura dati (categorie, post, ricerca)
- ✅ Analisi contenuti per suggerire widget
- ✅ Analisi pattern (cucine, colori, IKEA, stanze)
- ✅ Gestione tag (lista, ricerca, tag di un post)
- ✅ Post completo con contenuto, categorie e tag

### MCP Server Node.js
- ✅ Lettura dati (tutti gli endpoint REST API)
- ✅ Analisi contenuti e suggerimenti widget
- ✅ Modifica articoli (titolo, contenuto, excerpt, categorie, tag)
- ✅ Gestione tag completa (crea, cerca, aggiungi a post)
- ✅ Sistema estensioni per siti specifici (TotalDesign)
- ✅ Query vault opzionale (file markdown locali)

## 🎯 Obiettivi

### 1. Esplorazione Contenuti per Widget
- Analizzare articoli esistenti
- Identificare pattern (cucine, colori, IKEA, stanze)
- Suggerire widget contestuali da creare

### 2. Modifica Articoli
- Leggere contenuto articoli completi
- Modificare titolo, contenuto, categorie, tag
- Aggiornare meta dati

### 3. Multi-Sito con Estensioni
- Server base funziona su qualsiasi WordPress
- Estensioni specifiche per TotalDesign (colori, IKEA, stanze)
- Configurazione via variabili d'ambiente

