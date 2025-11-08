# Guida Test MCP Server

## Test 1: REST API WordPress (Diretto)

### Opzione A: Browser

Apri nel browser questi URL per verificare che la REST API risponda:

1. **Lista Categorie:**
   ```
   https://www.totaldesign.it/wp-json/td-mcp/v1/categories
   ```

2. **Post Recenti:**
   ```
   https://www.totaldesign.it/wp-json/td-mcp/v1/posts/recent?limit=5
   ```

3. **Ricerca:**
   ```
   https://www.totaldesign.it/wp-json/td-mcp/v1/posts/search?q=ikea&limit=5
   ```

4. **Post per Colore:**
   ```
   https://www.totaldesign.it/wp-json/td-mcp/v1/posts/color/bianco?limit=5
   ```

**Risultato atteso:** JSON con array di oggetti.

### Opzione B: Script Node.js (Consigliato)

```bash
cd mcp-server
node test-api.js
```

Lo script testerà automaticamente tutti gli endpoint e mostrerà i risultati.

### Opzione C: cURL (Terminale)

```bash
# Lista categorie
curl "https://www.totaldesign.it/wp-json/td-mcp/v1/categories"

# Post recenti
curl "https://www.totaldesign.it/wp-json/td-mcp/v1/posts/recent?limit=5"

# Ricerca
curl "https://www.totaldesign.it/wp-json/td-mcp/v1/posts/search?q=ikea&limit=5"
```

### Opzione D: PowerShell (Windows)

```powershell
# Lista categorie
Invoke-RestMethod -Uri "https://www.totaldesign.it/wp-json/td-mcp/v1/categories" | ConvertTo-Json

# Post recenti
Invoke-RestMethod -Uri "https://www.totaldesign.it/wp-json/td-mcp/v1/posts/recent?limit=5" | ConvertTo-Json
```

## Test 2: MCP Server Node.js

### Step 1: Installa Dipendenze

```bash
cd mcp-server
npm install
```

### Step 2: Verifica che il Server si Avvii

```bash
node server.js
```

**Nota:** Il server comunica via stdio, quindi non vedrai output normale. Se vedi errori, li vedrai qui.

### Step 3: Test Manuale (Opzionale)

Puoi testare manualmente il server usando `echo` (ma è complesso). Meglio testare direttamente in Cursor.

## Test 3: Integrazione Cursor

### Step 1: Configura Cursor

Aggiungi nel file di configurazione MCP:

**Windows:**
```
%APPDATA%\Cursor\User\globalStorage\saoudrizwan.claude-dev\settings\cline_mcp_settings.json
```

**macOS/Linux:**
```
~/.config/Cursor/User/globalStorage/saoudrizwan.claude-dev/settings/cline_mcp_settings.json
```

**Contenuto:**
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

**⚠️ IMPORTANTE:** Sostituisci il percorso con il percorso assoluto corretto del file `server.js` sul tuo sistema.

### Step 2: Riavvia Cursor

Chiudi e riapri Cursor completamente.

### Step 3: Verifica Connessione

In Cursor, chiedi:
```
"Puoi vedere le risorse disponibili dal server MCP totaldesign?"
```

Oppure:
```
"Usa il tool get_categories per ottenere le categorie WordPress"
```

### Step 4: Test Query

Prova queste query in Cursor:

1. **Lista Categorie:**
   ```
   "Mostrami tutte le categorie WordPress usando il tool get_categories"
   ```

2. **Ricerca Post:**
   ```
   "Cerca post su IKEA METOD usando search_posts"
   ```

3. **Post per Colore:**
   ```
   "Ottieni post sul colore bianco usando get_posts_by_color"
   ```

4. **Post per Categoria:**
   ```
   "Mostrami 10 post della categoria 'colori' usando get_posts_by_category"
   ```

## Troubleshooting

### REST API non risponde (404)

**Possibili cause:**
1. Plugin non attivo
2. `MCPApi::init()` non chiamato
3. Permalink WordPress non configurati come "Post name"

**Soluzione:**
- Verifica che il plugin sia attivo
- Controlla `include/site_specific/totaldesign_specific.php` che contenga `MCPApi::init()`
- Vai in WordPress Admin → Impostazioni → Permalink e salva (anche se già configurato)

### REST API risponde ma dati vuoti

**Possibili cause:**
1. Nessun post pubblicato
2. Cache WordPress

**Soluzione:**
- Verifica che ci siano post pubblicati
- Svuota cache WordPress (se usi plugin cache)

### MCP Server non si connette in Cursor

**Possibili cause:**
1. Percorso errato in `cline_mcp_settings.json`
2. Node.js non installato
3. Dipendenze non installate

**Soluzione:**
- Verifica il percorso assoluto (usa `pwd` o `Get-Location` per trovarlo)
- Verifica Node.js: `node --version` (deve essere >= 18)
- Installa dipendenze: `cd mcp-server && npm install`

### Errori nel Server MCP

**Controlla i log:**
- In Cursor, apri la console sviluppatore (F12 o Cmd+Option+I)
- Cerca errori relativi a "totaldesign" o "mcp"

## Verifica Rapida

Esegui questo comando per un test completo:

```bash
# Test REST API
cd mcp-server
node test-api.js

# Se tutti i test passano, la REST API funziona!
```

Poi configura Cursor e testa le query.

## Domande di Test

Dopo aver configurato tutto, prova a chiedere in Cursor:

1. "Quali sono le categorie più popolari del sito?"
2. "Cerca tutti i post su IKEA METOD"
3. "Mostrami i post più recenti sul colore bianco"
4. "Quanti post ci sono sulla categoria 'colori'?"

Se Cursor riesce a rispondere usando i dati del sito, tutto funziona! 🎉

