# MCP Server - WordPress Model Context Protocol

Server Node.js per integrazione Model Context Protocol con WordPress.

## Documentazione

Documentazione completa: **[../docs/MCP.md](../docs/MCP.md)**

## Quick Start

```bash
# Installa dipendenze
npm install

# Avvia server (comunicazione via stdio)
node server.js
```

## Configurazione

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

## Note

- Il server comunica via stdio (standard input/output)
- Non serve server HTTP separato
- Requisiti: Node.js 18+

Vedi [docs/MCP.md](../docs/MCP.md) per setup completo, endpoints e troubleshooting.
