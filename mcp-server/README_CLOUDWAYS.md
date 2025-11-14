# 🌐 MCP Server Remoto su Cloudways

Guida rapida per configurare il server MCP per esecuzione remota.

## 🚀 Quick Start

### 1. Upload su Cloudways

```bash
# Carica directory mcp-server sul server
scp -r mcp-server/ user@cloudways-server:/path/to/mcp-server/
```

### 2. Installa Dipendenze

```bash
cd /path/to/mcp-server
npm install
```

### 3. Configura Variabili

Crea file `.env`:

```bash
WP_BASE_URL=https://www.totaldesign.it
MCP_HTTP_PORT=3000
MCP_HTTP_HOST=0.0.0.0
MCP_API_KEY=$(openssl rand -hex 32)
```

### 4. Avvia con PM2

```bash
npm install -g pm2
export MCP_API_KEY=your-generated-key
pm2 start ecosystem.config.js
pm2 save
```

### 5. Verifica

```bash
curl http://localhost:3000/health
```

## 📚 Documentazione Completa

Vedi [DEPLOY_CLOUDWAYS.md](./DEPLOY_CLOUDWAYS.md) per guida dettagliata.

