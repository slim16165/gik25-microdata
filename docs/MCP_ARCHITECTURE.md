# Architettura MCP Server - Spiegazione

## 🏗️ Componenti dell'Architettura

### 1. **Backend WordPress / REST API (PHP) - Su Cloudways** ☁️

**Dove gira:** Sul server Cloudways insieme al sito WordPress

**Cosa fa:**
- Espone endpoint REST API per leggere dati dal database WordPress
- Implementato in `include/class/REST/MCPApi.php`
- Accessibile via: `https://www.totaldesign.it/wp-json/wp-mcp/v1/...`
- **Questo è il "BE" (backend)** - il backend WordPress che gestisce i dati

**Endpoint attuali:**
- `GET /categories` - Lista categorie
- `GET /posts/search?q={query}` - Ricerca post
- `GET /posts/category/{slug}` - Post per categoria
- `GET /posts/color/{color}` - Post per colore
- `GET /posts/ikea/{line}` - Post per linea IKEA
- `GET /posts/room/{room}` - Post per stanza
- `GET /posts/pantone` - Post Pantone
- `GET /posts/popular` - Post popolari
- `GET /posts/recent` - Post recenti

**Caratteristiche:**
- ✅ Cache WordPress (1 ora)
- ✅ Accesso pubblico (lettura)
- ✅ Dati formattati in JSON
- ✅ Route estese opzionali (color, ikea, room, pantone) configurabili via filter

---

### 2. **MCP Server Node.js - Locale** 💻

**Dove gira:** Sul tuo computer locale (Windows) - **NON su Cloudways**

**Cosa fa:**
- Comunica con Cursor via stdio (standard input/output)
- Chiama la REST API WordPress (che è su Cloudways) per ottenere dati
- Espone "tools" e "risorse" che Cursor può usare
- Implementato in `mcp-server/server.js`

**Flusso:**
```
Cursor (locale) 
    ↓ stdio
MCP Server Node.js (locale - sul tuo PC)
    ↓ HTTP request
REST API WordPress (Cloudways - backend)
    ↓ query
Database WordPress (Cloudways)
```

**⚠️ IMPORTANTE:**
- Il server MCP Node.js **NON** gira su Cloudways
- Il server MCP Node.js gira **sul tuo computer locale**
- Il server MCP Node.js fa HTTP request al backend WordPress su Cloudways
- Il backend WordPress (REST API) è quello che gira su Cloudways

**Perché Node.js locale?**
- Cursor comunica via stdio (pipe del terminale) - richiede processo locale
- Il server MCP deve essere sempre disponibile quando Cursor è aperto
- Non richiede server esterno dedicato
- Più semplice da gestire e debuggare
- Non consuma risorse su Cloudways

---

## 🔄 Flusso Completo

1. **Cursor (locale)** → Chiama un tool MCP (es: `get_categories`)
2. **MCP Server Node.js (locale)** → Riceve la richiesta via stdio
3. **MCP Server (locale)** → Fa HTTP request alla REST API WordPress (Cloudways)
4. **REST API WordPress (Cloudways - BE)** → Interroga il database e ritorna JSON
5. **MCP Server (locale)** → Riceve JSON e ritorna i dati a Cursor
6. **Cursor (locale)** → Usa i dati per aiutare a sviluppare

**Riepilogo:**
- ✅ **Backend WordPress (BE)**: Su Cloudways - gestisce dati e database
- ✅ **MCP Server Node.js**: Locale - fa da ponte tra Cursor e backend WordPress
- ✅ **Cursor**: Locale - interfaccia per sviluppare

---

## 🎯 Obiettivi del MCP Server

### 1. **Esplorazione Contenuti per Widget**
- Analizzare articoli esistenti
- Identificare pattern (cucine, colori, IKEA, stanze)
- Suggerire widget contestuali da creare
- Identificare categorie popolari che necessitano widget

### 2. **Modifica Articoli**
- Leggere contenuto articoli completi
- Modificare titolo, contenuto, categorie, tag
- Aggiornare meta dati
- Gestire immagini/thumbnail

### 3. **Multi-Sito con Estensioni**
- Server base funziona su qualsiasi WordPress
- Estensioni specifiche per TotalDesign (colori, IKEA, stanze)
- Configurazione via variabili d'ambiente

### 4. **Query Vault (Opzionale)**
- Se hai un vault Obsidian o file markdown
- Permettere query su note/documenti locali
- Integrare conoscenza del vault con contenuti WordPress

---

## 🔐 Autenticazione per Modifica Articoli

WordPress REST API nativa supporta modifica post:
- **Endpoint:** `POST /wp-json/wp/v2/posts/{id}`
- **Autenticazione:** Application Password (WordPress 5.6+)
- **Sicurezza:** HTTPS + Basic Auth

**Setup:**
1. WordPress Admin → Utenti → Profilo
2. Crea "Application Password"
3. Usa: `username:application_password` per Basic Auth

---

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

### Gestione Tag
- ✅ Lista tag con ricerca
- ✅ Tag di un post specifico
- ✅ Creazione tag
- ✅ Aggiunta tag a post (creazione automatica se non esistono)
- ✅ Aggiornamento post con tag (nomi o ID)

## 📋 Tool MCP Disponibili

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

### Opzionale
- `search_vault` - Ricerca nel vault (se configurato)

