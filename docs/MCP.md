# MCP Server - Documentazione

Sistema MCP (Model Context Protocol) per interrogare TotalDesign.it WordPress tramite REST API.

## Architettura

```
Cursor (locale) → MCP Server Node.js (locale) → REST API WordPress (Cloudways) → Database WordPress
```

**Componenti:**
- **REST API WordPress (PHP)**: Sul server Cloudways, espone endpoint REST API
- **MCP Server Node.js**: Locale sul tuo computer, comunica con Cursor via stdio
- **Cursor**: Locale, interfaccia per sviluppare

**Importante:** Il server MCP Node.js gira sul tuo computer locale, NON su Cloudways.

## Installazione

### Prerequisiti
- Node.js 18+
- Plugin WordPress con REST API attiva
- Cursor installato

### Setup Rapido

1. **Installa dipendenze:**
```bash
cd mcp-server
npm install
```

2. **Verifica REST API:**
Testa in browser: `https://www.totaldesign.it/wp-json/wp-mcp/v1/categories`

3. **Configura Cursor:**
Aggiungi in `cline_mcp_settings.json`:
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

4. **Riavvia Cursor**

## REST API Endpoints

**Lettura:**
- `GET /wp-json/wp-mcp/v1/categories` - Lista categorie
- `GET /wp-json/wp-mcp/v1/posts/search?q={query}` - Ricerca post
- `GET /wp-json/wp-mcp/v1/posts/category/{slug}` - Post per categoria
- `GET /wp-json/wp-mcp/v1/posts/color/{color}` - Post per colore
- `GET /wp-json/wp-mcp/v1/posts/ikea/{line}` - Post per linea IKEA
- `GET /wp-json/wp-mcp/v1/posts/room/{room}` - Post per stanza
- `GET /wp-json/wp-mcp/v1/posts/pantone` - Post Pantone
- `GET /wp-json/wp-mcp/v1/posts/popular` - Post popolari
- `GET /wp-json/wp-mcp/v1/posts/recent` - Post recenti
- `GET /wp-json/wp-mcp/v1/health/errors` - Errori recenti
- `GET /wp-json/wp-mcp/v1/health/errors/critical` - Errori critici
- `POST /wp-json/wp-mcp/v1/health/debug` - Toggle debug mode

**Modifica (richiede autenticazione):**
- `POST /wp-json/wp/v2/posts/{id}` - Modifica articolo
- `POST /wp-json/wp/v2/tags` - Crea tag
- `POST /wp-json/wp/v2/posts/{id}` - Aggiorna tag di un post

## Tools MCP Disponibili

**Lettura Dati:**
- `get_categories` - Lista categorie
- `search_posts` - Ricerca post
- `get_posts_by_category` - Post per categoria
- `get_posts_by_color` - Post per colore
- `get_posts_by_ikea_line` - Post per linea IKEA
- `get_posts_by_room` - Post per stanza
- `get_pantone_posts` - Post Pantone
- `get_post_full` - Post completo con contenuto

**Analisi:**
- `analyze_widget_suggestions` - Suggerisci widget basati su contenuti
- `analyze_patterns` - Analizza pattern comuni

**Modifica (richiede autenticazione):**
- `update_post` - Modifica articolo
- `create_tag` - Crea tag
- `add_tags_to_post` - Aggiungi tag a post
- `get_tags` - Lista tag (con ricerca)
- `get_post_tags` - Tag di un post

## Autenticazione

Per modificare articoli, configura un'Application Password WordPress:
1. WordPress Admin → Utenti → Profilo
2. Crea "Application Password"
3. Codifica in base64: `username:application_password`
4. Aggiungi a configurazione MCP come variabile d'ambiente `WP_AUTH`

## Test

**REST API:**
```bash
curl "https://www.totaldesign.it/wp-json/wp-mcp/v1/categories"
curl "https://www.totaldesign.it/wp-json/wp-mcp/v1/posts/recent?limit=5"
```

**MCP Server (in Cursor):**
- "Mostrami tutte le categorie WordPress"
- "Cerca post su IKEA METOD"
- "Ottieni 10 post sul colore bianco"

## Troubleshooting

**REST API non risponde (404):**
1. Verifica che il plugin sia attivo
2. Controlla che `MCPApi::init()` sia chiamato in `totaldesign_specific.php`
3. Vai in WordPress Admin → Impostazioni → Permalink e salva

**MCP Server non si connette:**
1. Verifica il percorso assoluto in `cline_mcp_settings.json`
2. Assicurati che Node.js sia installato (`node --version`)
3. Controlla che le dipendenze siano installate (`npm install` in `mcp-server/`)
4. Riavvia Cursor completamente

**Dati non aggiornati:**
- La cache è di 1 ora
- Svuota cache WordPress se necessario

## Riferimenti

- Implementazione REST API: `include/class/REST/MCPApi.php`
- Server Node.js: `mcp-server/server.js`
- Configurazione: `mcp-server/README.md`
