# Guida Deploy e Test MCP Server

## 📦 File da Caricare sul Server

Dopo aver committato le modifiche, carica questi file sul server WordPress:

### File Nuovi da Creare:

1. **`include/class/REST/MCPApi.php`** - Classe REST API
2. **`mcp-server/package.json`** - Configurazione Node.js
3. **`mcp-server/server.js`** - Server MCP
4. **`mcp-server/test-api.js`** - Script di test
5. **`mcp-server/README.md`** - Documentazione server

### File Modificati:

1. **`include/site_specific/totaldesign_specific.php`** - Aggiunto init MCPApi

## 🚀 Procedura di Deploy

### Step 1: Carica i File

Carica tutti i file nuovi e modificati sul server Cloudways tramite:
- FTP/SFTP
- Git (se hai un repository)
- File Manager di Cloudways

**Percorsi:**
- Plugin root: `wp-content/plugins/gik25-microdata/`
- REST API: `wp-content/plugins/gik25-microdata/include/class/REST/MCPApi.php`
- MCP Server: `wp-content/plugins/gik25-microdata/mcp-server/`

### Step 2: Verifica Autoloader Composer

L'autoloader Composer dovrebbe trovare automaticamente la classe `gik25microdata\REST\MCPApi` perché:
- Il namespace `gik25microdata\` è mappato a `include/class/` in `composer.json`
- La classe è in `include/class/REST/MCPApi.php`
- Il namespace della classe è `gik25microdata\REST`

**Se la classe non viene trovata:**
```bash
# Sul server, nella directory del plugin
composer dump-autoload
```

### Step 3: Test REST API (Dopo il Deploy)

#### Opzione A: Browser

Apri nel browser:
```
https://www.totaldesign.it/wp-json/td-mcp/v1/categories
```

**Risultato atteso:** JSON con array di categorie.

#### Opzione B: Script di Test

Sul server o localmente:
```bash
cd mcp-server
node test-api.js
```

#### Opzione C: cURL

```bash
curl "https://www.totaldesign.it/wp-json/td-mcp/v1/categories"
```

### Step 4: Verifica Permalink WordPress

La REST API WordPress richiede che i permalink siano configurati come "Post name":

1. Vai in **WordPress Admin → Impostazioni → Permalink**
2. Seleziona **"Nome articolo"** (Post name)
3. Clicca **Salva modifiche** (anche se già configurato, questo rigenera le rewrite rules)

### Step 5: Test MCP Server (Dopo REST API Funzionante)

#### ⚠️ IMPORTANTE: Installazione Dipendenze Node.js

**Il `package.json` è nella directory `mcp-server`, NON nella root del plugin!**

Sul server Cloudways:
```bash
cd wp-content/plugins/gik25-microdata/mcp-server
npm install
```

**NOTA:** Se Node.js non è installato su Cloudways, hai due opzioni:

**Opzione 1: Installa Node.js su Cloudways**
- Cloudways supporta Node.js, ma potrebbe richiedere configurazione
- Contatta supporto Cloudways se necessario

**Opzione 2: Esegui MCP Server Localmente**
- Installa dipendenze sul tuo computer locale
- Configura Cursor per puntare al server locale
- Il server locale chiamerà la REST API sul sito live

**Per installazione locale:**
```bash
# Sul tuo computer
cd "C:\Users\g.salvi\Dropbox\Siti internet\Altri siti\Principali\TotalDesign.it\wp-content\plugins\gik25-microdata\mcp-server"
npm install
```

#### Configurazione Cursor

Aggiungi nel file di configurazione MCP di Cursor:

**Windows:**
```
%APPDATA%\Cursor\User\globalStorage\saoudrizwan.claude-dev\settings\cline_mcp_settings.json
```

**Contenuto (se esegui sul server):**
```json
{
  "mcpServers": {
    "totaldesign": {
      "command": "node",
      "args": ["/home/1340912.cloudwaysapps.com/gwvyrysadj/public_html/wp-content/plugins/gik25-microdata/mcp-server/server.js"],
      "env": {
        "WP_BASE_URL": "https://www.totaldesign.it"
      }
    }
  }
}
```

**Contenuto (se esegui localmente - CONSIGLIATO):**
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

**⚠️ IMPORTANTE:** 
- Sostituisci il percorso con quello corretto
- **Raccomandato:** Esegui il server MCP localmente (più semplice)
- Il server locale può comunque chiamare la REST API sul sito live

**Per trovare il percorso assoluto:**
- **Windows (PowerShell):** `Get-Location` nella directory `mcp-server`
- **Linux/Mac:** `pwd` nella directory `mcp-server`

#### Test in Cursor

Dopo aver riavviato Cursor, prova:
```
"Usa get_categories per ottenere le categorie WordPress"
```

## 🔍 Troubleshooting

### REST API restituisce 404

**Possibili cause:**
1. Plugin non attivo → Attiva il plugin
2. Permalink non configurati → Vai in Impostazioni → Permalink e salva
3. Classe non caricata → Verifica che `MCPApi::init()` sia chiamato in `totaldesign_specific.php`

**Debug:**
Aggiungi temporaneamente in `totaldesign_specific.php`:
```php
error_log('MCPApi class exists: ' . (class_exists('\\gik25microdata\\REST\\MCPApi') ? 'YES' : 'NO'));
```

### Autoloader non trova la classe

**Soluzione:**
```bash
cd wp-content/plugins/gik25-microdata
composer dump-autoload
```

### npm install fallisce con "package.json not found"

**Causa:** Stai eseguendo `npm install` nella directory sbagliata.

**Soluzione:**
```bash
# Assicurati di essere nella directory mcp-server
cd wp-content/plugins/gik25-microdata/mcp-server
npm install
```

### Node.js non installato su Cloudways

**Opzioni:**
1. **Esegui localmente (CONSIGLIATO):** Installa Node.js sul tuo computer e configura Cursor per usare il server locale
2. **Installa su Cloudways:** Contatta supporto Cloudways per abilitare Node.js

### MCP Server non si connette

**Verifica:**
1. Node.js installato: `node --version` (deve essere >= 18)
2. Dipendenze installate: `cd mcp-server && npm install`
3. Percorso corretto in `cline_mcp_settings.json`
4. Riavvia Cursor completamente

## ✅ Checklist Post-Deploy

- [ ] File caricati sul server
- [ ] Plugin attivo in WordPress
- [ ] REST API risponde (test browser/curl)
- [ ] Permalink configurati come "Post name"
- [ ] MCP Server dipendenze installate (`npm install` in `mcp-server/`)
- [ ] Cursor configurato con percorso corretto
- [ ] Cursor riavviato
- [ ] Test query in Cursor funzionante

## 🎯 Test Finale

Dopo tutto configurato, prova queste query in Cursor:

1. "Mostrami tutte le categorie WordPress"
2. "Cerca post su IKEA METOD"
3. "Ottieni 10 post sul colore bianco"
4. "Quali sono i post più popolari?"

Se Cursor riesce a rispondere usando i dati del sito, tutto funziona! 🎉

## 💡 Raccomandazione

**Per semplicità, esegui il server MCP localmente:**
- Installa Node.js sul tuo computer (se non ce l'hai)
- Esegui `npm install` nella directory `mcp-server` locale
- Configura Cursor per usare il server locale
- Il server locale chiamerà comunque la REST API sul sito live

Questo evita problemi di configurazione Node.js su Cloudways.
