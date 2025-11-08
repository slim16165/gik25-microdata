# TotalDesign MCP Server

Server MCP (Model Context Protocol) per interrogare TotalDesign.it WordPress.

## Installazione

### 1. Installa dipendenze

```bash
cd mcp-server
npm install
```

### 2. Configurazione

Il server usa queste variabili d'ambiente (opzionali):

- `WP_BASE_URL`: URL base del sito WordPress (default: `https://www.totaldesign.it`)

Esempio:
```bash
export WP_BASE_URL=https://www.totaldesign.it
```

## Uso con Cursor

Aggiungi questa configurazione nel file di configurazione MCP di Cursor:

**Windows**: `%APPDATA%\Cursor\User\globalStorage\saoudrizwan.claude-dev\settings\cline_mcp_settings.json`

**macOS/Linux**: `~/.config/Cursor/User/globalStorage/saoudrizwan.claude-dev/settings/cline_mcp_settings.json`

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

**Nota**: Sostituisci `/percorso/completo/a/mcp-server/server.js` con il percorso assoluto del file `server.js` nella directory `mcp-server` del plugin.

## Tools Disponibili

### `search_posts`
Cerca post nel sito WordPress.

**Parametri:**
- `query` (string, richiesto): Query di ricerca
- `limit` (number, opzionale): Numero massimo di risultati (default: 20)

**Esempio:**
```json
{
  "query": "ikea metod",
  "limit": 10
}
```

### `get_posts_by_category`
Ottieni post per categoria.

**Parametri:**
- `category_slug` (string, richiesto): Slug della categoria
- `limit` (number, opzionale): Numero massimo di risultati (default: 10)

**Esempio:**
```json
{
  "category_slug": "colori",
  "limit": 15
}
```

### `get_posts_by_color`
Ottieni post relativi a un colore specifico.

**Parametri:**
- `color` (string, richiesto): Nome del colore (es: "bianco", "verde-salvia")
- `limit` (number, opzionale): Numero massimo di risultati (default: 15)

**Esempio:**
```json
{
  "color": "bianco",
  "limit": 10
}
```

### `get_posts_by_ikea_line`
Ottieni post relativi a una linea IKEA.

**Parametri:**
- `line` (string, richiesto): Nome della linea (es: "metod", "enhet", "billy")
- `limit` (number, opzionale): Numero massimo di risultati (default: 15)

**Esempio:**
```json
{
  "line": "metod",
  "limit": 10
}
```

### `get_posts_by_room`
Ottieni post relativi a una stanza.

**Parametri:**
- `room` (string, richiesto): Nome della stanza (es: "cucina", "soggiorno", "camera")
- `limit` (number, opzionale): Numero massimo di risultati (default: 15)

**Esempio:**
```json
{
  "room": "cucina",
  "limit": 10
}
```

### `get_pantone_posts`
Ottieni post relativi a Pantone.

**Parametri:**
- `limit` (number, opzionale): Numero massimo di risultati (default: 20)

**Esempio:**
```json
{
  "limit": 15
}
```

### `get_categories`
Ottieni lista di tutte le categorie WordPress.

**Parametri:** Nessuno

## Risorse Disponibili

- `td://categories` - Lista categorie
- `td://posts/popular` - Post popolari
- `td://posts/recent` - Post recenti
- `td://posts/category/{slug}` - Post per categoria
- `td://posts/color/{color}` - Post per colore
- `td://posts/ikea/{line}` - Post per linea IKEA

## Test Locale

Puoi testare il server direttamente:

```bash
node server.js
```

Il server comunica via stdio, quindi funziona meglio quando chiamato da Cursor o altri client MCP.

## Troubleshooting

### Errore: "Cannot find module '@modelcontextprotocol/sdk'"
Esegui `npm install` nella directory `mcp-server`.

### Errore: "HTTP 404"
Verifica che:
1. La REST API WordPress sia attiva
2. L'URL base sia corretto
3. Il plugin WordPress sia attivo e la classe `MCPApi` sia caricata

### Errore: "Connection refused"
Verifica che:
1. Il sito WordPress sia raggiungibile
2. Non ci siano firewall che bloccano le richieste
3. La REST API non richieda autenticazione (attualmente è pubblica)

## Note

- Il server usa cache WordPress (1 ora) per ottimizzare le performance
- Tutte le richieste sono pubbliche (nessuna autenticazione richiesta)
- I dati sono in formato JSON standardizzato

