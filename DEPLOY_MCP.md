# Guida Setup MCP Server

## 📋 Panoramica

L'MCP Server è un componente **locale** che si installa sul tuo computer per permettere a Cursor di interrogare siti WordPress tramite REST API.

**Architettura:**
- **REST API WordPress**: Deve essere disponibile pubblicamente (su qualsiasi hosting WordPress)
- **MCP Server Node.js**: Installato localmente sul tuo computer
- **Cursor**: Si connette al MCP Server locale via stdio

## 🚀 Setup Locale

### Prerequisiti

- Node.js 18+ installato sul tuo computer
- Plugin WordPress con REST API attiva
- Cursor installato

### Step 1: Installa Dipendenze Node.js

```bash
cd mcp-server
npm install
```

**Risultato:** Viene creata la cartella `node_modules` con le dipendenze.

### Step 2: Verifica REST API WordPress

La REST API WordPress deve essere accessibile pubblicamente. Testa in browser:

```
https://TUO-SITO.it/wp-json/wp-mcp/v1/categories
```

**Se il sito è in locale o non pubblicamente accessibile:**
- Usa un tunnel (ngrok, localtunnel) per esporre il sito
- Oppure usa un sito di staging/produzione

**Se la REST API non risponde:**
1. Verifica che il plugin sia attivo
2. Vai in WordPress Admin → Impostazioni → Permalink
3. Seleziona "Nome articolo" (Post name) e salva

### Step 3: Configura MCP Server in Cursor

Crea/modifica il file di configurazione Cursor nel progetto:

**`.cursor/mcp.json`** (nel progetto) o nella configurazione globale di Cursor.

**Configurazione base:**
```json
{
  "mcpServers": {
    "wordpress": {
      "env": {
        "WP_BASE_URL": "https://TUO-SITO.it"
      },
      "command": "node",
      "args": [
        "PERCORSO_ASSOLUTO/mcp-server/server.js"
      ]
    }
  }
}
```

**Per trovare il percorso assoluto:**
- **Windows (PowerShell):** `Get-Location` nella directory `mcp-server`
- **Linux/Mac:** `pwd` nella directory `mcp-server`

### Step 4: Configurazione Opzionale

#### Autenticazione per Modifica Articoli

Se vuoi modificare articoli, configura un'Application Password WordPress:

1. WordPress Admin → Utenti → Profilo
2. Crea "Application Password"
3. Codifica in base64: `username:application_password`
4. Aggiungi a `.cursor/mcp.json`:
```json
{
  "mcpServers": {
    "wordpress": {
      "env": {
        "WP_BASE_URL": "https://TUO-SITO.it",
        "WP_AUTH": "base64_encoded_username:password"
      }
    }
  }
}
```

#### Estensioni Sito Specifiche

Per configurare estensioni specifiche (colori, IKEA, stanze):

```json
{
  "env": {
    "SITE_EXTENSIONS": "{\"totaldesign.it\":{\"name\":\"TotalDesign\",\"features\":[\"colors\",\"ikea\",\"rooms\"]}}"
  }
}
```

#### Vault Obsidian (Opzionale)

Per abilitare ricerca nel vault:

```json
{
  "env": {
    "VAULT_PATH": "C:\\Users\\...\\Obsidian Vault"
  }
}
```

## 🧪 Test

### Test 1: REST API WordPress

Verifica che la REST API sia accessibile:

```bash
curl "https://TUO-SITO.it/wp-json/wp-mcp/v1/categories"
```

**Risultato atteso:** JSON con array di categorie.

### Test 2: MCP Server in Cursor

1. Riavvia Cursor completamente
2. Prova a chiedere: "Usa get_categories per ottenere le categorie WordPress"
3. Se Cursor riesce a rispondere usando i dati del sito, tutto funziona! 🎉

## 🔍 Troubleshooting

### REST API restituisce 404

**Possibili cause:**
1. Plugin non attivo → Attiva il plugin
2. Permalink non configurati → Vai in Impostazioni → Permalink e salva
3. Classe non caricata → Verifica che `MCPApi::init()` sia chiamato nel file site-specific

### npm install fallisce

**Causa:** Stai eseguendo `npm install` nella directory sbagliata.

**Soluzione:**
```bash
# Assicurati di essere nella directory mcp-server
cd mcp-server
npm install
```

### MCP Server non si connette

**Verifica:**
1. Node.js installato: `node --version` (deve essere >= 18)
2. Dipendenze installate: `cd mcp-server && npm install`
3. Percorso corretto in `.cursor/mcp.json`
4. Riavvia Cursor completamente

## ✅ Checklist

- [ ] Node.js 18+ installato
- [ ] Dipendenze installate (`npm install` in `mcp-server/`)
- [ ] REST API WordPress accessibile pubblicamente
- [ ] Plugin WordPress attivo
- [ ] Cursor configurato con percorso corretto
- [ ] Cursor riavviato
- [ ] Test query in Cursor funzionante

## 🎯 Esempi di Query

Dopo tutto configurato, prova queste query in Cursor:

1. "Mostrami tutte le categorie WordPress"
2. "Cerca post su IKEA METOD"
3. "Ottieni 10 post sul colore bianco"
4. "Quali sono i post più popolari?"

## 📝 Note

- Il server MCP gira **localmente** sul tuo computer
- Non serve Node.js sul server WordPress
- Il server locale chiama la REST API pubblica del sito WordPress
- Puoi usare lo stesso server MCP per più siti WordPress cambiando `WP_BASE_URL`
