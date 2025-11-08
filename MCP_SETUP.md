# Setup MCP Server per TotalDesign.it

## Panoramica

Sistema ibrido per interrogare TotalDesign.it WordPress:
1. **REST API WordPress (PHP)** - Espone dati via endpoint REST
2. **MCP Server Node.js** - Client MCP che chiama la REST API

## Componenti

### 1. REST API WordPress (`include/class/REST/MCPApi.php`)

Classe PHP che registra endpoint REST API:
- `/wp-json/td-mcp/v1/categories` - Lista categorie
- `/wp-json/td-mcp/v1/posts/category/{slug}` - Post per categoria
- `/wp-json/td-mcp/v1/posts/search?q={query}` - Ricerca post
- `/wp-json/td-mcp/v1/posts/color/{color}` - Post per colore
- `/wp-json/td-mcp/v1/posts/ikea/{line}` - Post per linea IKEA
- `/wp-json/td-mcp/v1/posts/room/{room}` - Post per stanza
- `/wp-json/td-mcp/v1/posts/pantone` - Post Pantone
- `/wp-json/td-mcp/v1/posts/popular` - Post popolari
- `/wp-json/td-mcp/v1/posts/recent` - Post recenti

**Caratteristiche:**
- ✅ Cache WordPress (1 ora)
- ✅ Accesso pubblico (nessuna autenticazione)
- ✅ Dati formattati in JSON standardizzato
- ✅ Integrato con `WP_Query` nativo

### 2. MCP Server Node.js (`mcp-server/`)

Server MCP standard che:
- Espone tools per interrogare la REST API
- Fornisce risorse URI (`td://...`)
- Comunica via stdio con Cursor

## Installazione

### Step 1: Verifica REST API WordPress

1. Assicurati che il plugin sia attivo
2. Verifica che `MCPApi::init()` sia chiamato in `totaldesign_specific.php`
3. Testa un endpoint:
   ```
   https://www.totaldesign.it/wp-json/td-mcp/v1/categories
   ```

### Step 2: Installa MCP Server Node.js

```bash
cd mcp-server
npm install
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
      "args": ["C:\\Users\\g.salvi\\Dropbox\\Siti internet\\Altri siti\\Principali\\TotalDesign.it\\wp-content\\plugins\\gik25-microdata\\mcp-server\\server.js"],
      "env": {
        "WP_BASE_URL": "https://www.totaldesign.it"
      }
    }
  }
}
```

**Nota:** Sostituisci il percorso con il percorso assoluto corretto del file `server.js`.

### Step 4: Riavvia Cursor

Riavvia Cursor per caricare la nuova configurazione MCP.

## Test

### Test REST API

```bash
# Lista categorie
curl "https://www.totaldesign.it/wp-json/td-mcp/v1/categories"

# Post per categoria
curl "https://www.totaldesign.it/wp-json/td-mcp/v1/posts/category/colori?limit=5"

# Ricerca
curl "https://www.totaldesign.it/wp-json/td-mcp/v1/posts/search?q=ikea+metod&limit=5"
```

### Test MCP Server

In Cursor, puoi ora chiedere:
- "Mostrami i 10 post più recenti sulla categoria 'colori'"
- "Cerca post su IKEA METOD"
- "Dimmi tutti i post sul colore bianco"

## Vantaggi di questo Approccio

✅ **Standard MCP**: Usa protocollo MCP standard, compatibile con Cursor  
✅ **WordPress Native**: REST API usa `WP_Query` e funzioni native WordPress  
✅ **Cache Intelligente**: Cache WordPress per performance  
✅ **Separazione Responsabilità**: PHP gestisce dati, Node.js gestisce protocollo  
✅ **Facile da Mantenere**: Modifiche ai dati solo in PHP, MCP server è generico  
✅ **Cloudways Compatible**: Funziona su Cloudways senza modifiche  

## Prossimi Passi

1. **Testa gli endpoint REST API** per verificare che funzionino
2. **Installa e configura il MCP server** in Cursor
3. **Testa le query** in Cursor per verificare l'integrazione
4. **Ottimizza** aggiungendo più endpoint se necessario

## Troubleshooting

### REST API non risponde
- Verifica che il plugin sia attivo
- Controlla che `MCPApi::init()` sia chiamato
- Verifica permalink WordPress (devono essere "Post name")

### MCP Server non si connette
- Verifica il percorso assoluto in `cline_mcp_settings.json`
- Assicurati che Node.js sia installato (`node --version`)
- Controlla i log di Cursor per errori

### Dati non aggiornati
- La cache è di 1 ora, puoi svuotarla manualmente
- Aggiungi `?nocache=1` agli endpoint per bypassare cache (non implementato, ma puoi aggiungerlo)

## Estensioni Future

- Autenticazione API key (opzionale)
- Rate limiting
- Endpoint per architetti
- Endpoint per programmi 3D
- Webhook per invalidare cache

