# MCP Server - WordPress Model Context Protocol

Server Node.js per integrazione Model Context Protocol con WordPress.

## 📚 Documentazione

- **Setup Locale**: [docs/MCP.md](../docs/MCP.md)
- **Deploy Cloudways**: [DEPLOY_CLOUDWAYS.md](./DEPLOY_CLOUDWAYS.md)
- **Quick Start Cloudways**: [README_CLOUDWAYS.md](./README_CLOUDWAYS.md)

## 🚀 Quick Start

### Modalità Locale (stdio)

```bash
# Installa dipendenze
npm install

# Avvia server (comunicazione via stdio)
npm start
# oppure
node server.js
```

### Modalità Remota (HTTP su Cloudways)

```bash
# Installa dipendenze
npm install

# Configura variabili d'ambiente
export MCP_API_KEY=$(openssl rand -hex 32)
export WP_BASE_URL=https://www.totaldesign.it

# Avvia server HTTP
npm run start:http
# oppure
node server-http.js
```

## ⚙️ Configurazione

### Locale (Cursor)

Configura Cursor per usare questo server MCP modificando `cline_mcp_settings.json`:

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

### Remoto (Cloudways)

Vedi [DEPLOY_CLOUDWAYS.md](./DEPLOY_CLOUDWAYS.md) per configurazione completa.

## 📋 Modalità Disponibili

| Modalità | File | Uso | Trasporto |
|----------|------|-----|-----------|
| **Locale** | `server.js` | Sviluppo locale con Cursor | stdio |
| **Remoto** | `server-http.js` | Esecuzione su Cloudways | HTTP |

## 🔐 Sicurezza

- **Locale**: Nessuna autenticazione (uso locale)
- **Remoto**: Autenticazione via API Key obbligatoria

## 📝 Note

- **Locale**: Comunica via stdio (standard input/output)
- **Remoto**: Server HTTP con autenticazione API key
- **Requisiti**: Node.js 18+

Vedi [docs/MCP.md](../docs/MCP.md) per setup completo, endpoints e troubleshooting.
