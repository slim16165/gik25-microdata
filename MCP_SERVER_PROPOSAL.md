# Proposta: MCP Server per TotalDesign.it

## Cos'è un MCP Server?

Un **Model Context Protocol (MCP) Server** è un servizio che permette a Cursor (o altri client AI) di interrogare direttamente il tuo sito WordPress per ottenere informazioni in tempo reale.

## Vantaggi

✅ **Interrogazione diretta**: Posso chiedere "dimmi tutti i post su IKEA METOD" e ricevere dati reali  
✅ **Aggiornamento automatico**: I dati sono sempre aggiornati, senza dover esportare/importare  
✅ **Query complesse**: Posso fare ricerche avanzate (per categoria, tag, data, popolarità)  
✅ **Integrazione nativa**: Funziona direttamente in Cursor, senza setup complesso  

## Come Funzionerebbe

### 1. Endpoint REST API WordPress

Creeremmo un endpoint personalizzato:
```
https://www.totaldesign.it/wp-json/wp-mcp/v1/posts?category=colori&limit=10
https://www.totaldesign.it/wp-json/wp-mcp/v1/search?q=ikea+metod
https://www.totaldesign.it/wp-json/wp-mcp/v1/categories
```

### 2. MCP Server (Node.js o PHP)

Un server che:
- Si connette al database WordPress
- Espone funzioni MCP standard (`list_resources`, `fetch_resource`, `query`)
- Restituisce dati in formato JSON strutturato

### 3. Configurazione in Cursor

Aggiungi nel file di configurazione MCP di Cursor:
```json
{
  "mcpServers": {
    "totaldesign": {
      "command": "node",
      "args": ["path/to/mcp-server.js"],
      "env": {
        "WP_DB_HOST": "...",
        "WP_DB_NAME": "...",
        "WP_DB_USER": "...",
        "WP_DB_PASS": "..."
      }
    }
  }
}
```

## Funzionalità Proposte

### Resource Types

1. **`td://posts/category/{slug}`** - Post per categoria
2. **`td://posts/search/{query}`** - Ricerca post
3. **`td://posts/color/{color}`** - Post per colore
4. **`td://posts/ikea/{line}`** - Post per linea IKEA
5. **`td://categories`** - Lista categorie
6. **`td://popular`** - Post più popolari

### Esempi di Query

```javascript
// In Cursor, potrei fare:
"Mostrami i 10 post più recenti sulla categoria 'colori'"
"Quali post parlano di IKEA METOD?"
"Dimmi tutti i post sul colore bianco pubblicati nel 2024"
```

## Implementazione

### Opzione 1: MCP Server PHP (Consigliato)
- ✅ Già integrato con WordPress
- ✅ Accesso diretto a `WP_Query`
- ✅ Usa le funzioni native WordPress
- ⚠️ Richiede PHP-CLI configurato

### Opzione 2: MCP Server Node.js
- ✅ Più flessibile per MCP
- ✅ Facile da distribuire
- ⚠️ Richiede connessione DB separata

### Opzione 3: REST API WordPress + MCP Bridge
- ✅ Usa REST API nativa WordPress
- ✅ Più sicuro (autenticazione WordPress)
- ⚠️ Richiede due componenti

## Sicurezza

- Autenticazione via API key o OAuth
- Rate limiting
- Sanitizzazione input
- Logging accessi

## Prossimi Passi

1. **Decidi l'approccio** (PHP, Node.js, o REST API)
2. **Definisci le risorse** necessarie (quali query servono?)
3. **Setup iniziale** (creo il server base)
4. **Test** (verifichiamo che funzioni)
5. **Deploy** (configurazione in Cursor)

## Domande per Te

1. Preferisci PHP (più integrato) o Node.js (più standard MCP)?
2. Quali query sono prioritarie? (categorie, colori, IKEA, ricerca generica?)
3. Vuoi autenticazione o accesso pubblico?
4. Preferisci dati in tempo reale o cache?

---

**Risposta breve**: Sì, possiamo creare un MCP server! È fattibile e molto utile per questo progetto. 🚀

