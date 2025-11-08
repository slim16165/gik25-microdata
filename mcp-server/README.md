# MCP Server - WordPress Model Context Protocol

Server Node.js per integrazione Model Context Protocol con WordPress.

## 📚 Documentazione

Tutta la documentazione completa si trova nella cartella `docs/` alla root del progetto:

- **[MCP_ARCHITECTURE.md](../docs/MCP_ARCHITECTURE.md)** - Architettura e funzionamento
- **[MCP_SETUP.md](../docs/MCP_SETUP.md)** - Guida setup e configurazione
- **[DEPLOY_MCP.md](../docs/DEPLOY_MCP.md)** - Guida deploy su produzione
- **[TEST_MCP.md](../docs/TEST_MCP.md)** - Guida test e troubleshooting
- **[MCP_SERVER_README.md](../docs/MCP_SERVER_README.md)** - Documentazione dettagliata server

## 🚀 Quick Start

```bash
# Installa dipendenze
npm install

# Avvia server (comunicazione via stdio)
node server.js
```

## ⚙️ Configurazione

Configura Cursor per usare questo server MCP modificando `~/.cursor/mcp.json` o `.cursor/mcp.json`:

```json
{
  "mcpServers": {
    "wordpress-mcp": {
      "command": "node",
      "args": ["path/to/mcp-server/server.js"],
      "env": {
        "WP_BASE_URL": "https://tuo-sito.it",
        "WP_USERNAME": "username",
        "WP_PASSWORD": "application_password"
      }
    }
  }
}
```

## 📝 Note

- Il server comunica via stdio (standard input/output)
- Non serve server HTTP separato
- Requisiti: Node.js 18+

Vedi la documentazione completa in `docs/` per maggiori dettagli.

