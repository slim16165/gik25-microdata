# 🚀 Deploy MCP Server su Cloudways

Guida per configurare il server MCP per esecuzione remota su Cloudways.

---

## 📋 Prerequisiti

- Server Cloudways con Node.js 18+ installato
- Accesso SSH al server
- Porta disponibile per il server MCP (default: 3000)

---

## 🔧 Setup Iniziale

### 1. Upload File sul Server

Carica la directory `mcp-server` sul server Cloudways:

```bash
# Da locale, upload via SCP
scp -r mcp-server/ user@cloudways-server:/path/to/mcp-server/

# Oppure usa SFTP o il file manager di Cloudways
```

### 2. Installa Dipendenze

```bash
cd /path/to/mcp-server
npm install
```

### 3. Configura Variabili d'Ambiente

Crea file `.env`:

```bash
# WordPress URL
WP_BASE_URL=https://www.totaldesign.it

# Server HTTP
MCP_HTTP_PORT=3000
MCP_HTTP_HOST=0.0.0.0

# Autenticazione (GENERA UNA CHIAVE SICURA!)
MCP_API_KEY=your-secure-api-key-here

# Opzionale: Autenticazione WordPress per modifiche
WP_AUTH=base64_encoded_username:password

# Opzionale: Estensioni siti
SITE_EXTENSIONS='{"totaldesign.it":{"name":"TotalDesign","features":["colors","ikea","rooms","pantone"]}}'
```

**Genera API Key sicura:**
```bash
openssl rand -hex 32
```

---

## 🚀 Avvio Server

### Opzione 1: PM2 (Consigliato)

PM2 gestisce automaticamente riavvii e log.

```bash
# Installa PM2 globalmente
npm install -g pm2

# Configura API key
export MCP_API_KEY=your-generated-key-here

# Avvia con PM2
pm2 start ecosystem.config.js

# Salva configurazione
pm2 save

# Abilita avvio automatico
pm2 startup
```

**Comandi utili PM2:**
```bash
pm2 status          # Stato server
pm2 logs           # Visualizza log
pm2 restart all    # Riavvia server
pm2 stop all       # Ferma server
```

### Opzione 2: Systemd Service

Crea file `/etc/systemd/system/mcp-server.service`:

```ini
[Unit]
Description=WordPress MCP HTTP Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/mcp-server
Environment="WP_BASE_URL=https://www.totaldesign.it"
Environment="MCP_HTTP_PORT=3000"
Environment="MCP_HTTP_HOST=0.0.0.0"
Environment="MCP_API_KEY=your-api-key-here"
ExecStart=/usr/bin/node /path/to/mcp-server/server-http.js
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Attiva servizio:
```bash
sudo systemctl daemon-reload
sudo systemctl enable mcp-server
sudo systemctl start mcp-server
sudo systemctl status mcp-server
```

### Opzione 3: Screen/Tmux (Temporaneo)

```bash
screen -S mcp-server
cd /path/to/mcp-server
export MCP_API_KEY=your-key-here
node server-http.js
# Ctrl+A+D per detach
```

---

## 🔒 Configurazione Firewall

Su Cloudways, apri la porta del server MCP:

```bash
# UFW (Ubuntu)
sudo ufw allow 3000/tcp

# Firewalld (CentOS)
sudo firewall-cmd --permanent --add-port=3000/tcp
sudo firewall-cmd --reload
```

**Nota:** Considera di limitare l'accesso solo agli IP che necessitano del server MCP.

---

## 🌐 Configurazione Nginx Reverse Proxy (Opzionale)

Se vuoi esporre il server tramite dominio (es: `mcp.totaldesign.it`):

```nginx
server {
    listen 80;
    server_name mcp.totaldesign.it;
    
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }
}
```

Poi configura SSL con Let's Encrypt:
```bash
sudo certbot --nginx -d mcp.totaldesign.it
```

---

## ✅ Verifica Funzionamento

### Health Check

```bash
curl http://localhost:3000/health
```

Risposta attesa:
```json
{
  "status": "ok",
  "service": "wordpress-mcp-http",
  "version": "1.0.0",
  "wordpress": "https://www.totaldesign.it"
}
```

### Test Endpoint WordPress

```bash
curl -H "Authorization: API-Key your-api-key-here" \
     http://localhost:3000/wp/categories
```

---

## 🔗 Configurazione Cursor per Server Remoto

Aggiorna `cline_mcp_settings.json` per usare il server remoto:

```json
{
  "mcpServers": {
    "totaldesign-remote": {
      "command": "node",
      "args": [
        "/path/to/mcp-client.js"
      ],
      "env": {
        "MCP_SERVER_URL": "https://mcp.totaldesign.it",
        "MCP_API_KEY": "your-api-key-here"
      }
    }
  }
}
```

Oppure crea un client HTTP che si connette al server remoto.

---

## 📊 Monitoraggio

### Log PM2

```bash
pm2 logs wordpress-mcp-server
```

### Log Systemd

```bash
sudo journalctl -u mcp-server -f
```

### Health Check Automatico

Crea cron job per verificare che il server sia attivo:

```bash
# Aggiungi a crontab
*/5 * * * * curl -f http://localhost:3000/health || systemctl restart mcp-server
```

---

## 🔐 Sicurezza

### Best Practices

1. **Usa API Key forte**: Genera con `openssl rand -hex 32`
2. **Limita accesso IP**: Configura firewall per permettere solo IP specifici
3. **Usa HTTPS**: Configura SSL tramite Nginx reverse proxy
4. **Rotazione chiavi**: Cambia API key periodicamente
5. **Monitora log**: Controlla accessi sospetti

### Rate Limiting

Considera di aggiungere rate limiting con Nginx:

```nginx
limit_req_zone $binary_remote_addr zone=mcp_limit:10m rate=10r/s;

server {
    location / {
        limit_req zone=mcp_limit burst=20;
        proxy_pass http://127.0.0.1:3000;
    }
}
```

---

## 🐛 Troubleshooting

### Server non si avvia

```bash
# Verifica Node.js
node --version  # Deve essere >= 18

# Verifica porta disponibile
netstat -tuln | grep 3000

# Verifica permessi
ls -la server-http.js
```

### Errori di connessione WordPress

```bash
# Testa REST API WordPress direttamente
curl https://www.totaldesign.it/wp-json/wp-mcp/v1/categories

# Verifica WP_AUTH se configurata
echo $WP_AUTH
```

### PM2 non mantiene processo

```bash
# Verifica configurazione
pm2 describe wordpress-mcp-server

# Riavvia con log dettagliati
pm2 restart wordpress-mcp-server --update-env
pm2 logs wordpress-mcp-server --lines 100
```

---

## 📝 Note

- Il server HTTP è separato dal server stdio originale
- Mantiene compatibilità con le stesse funzionalità
- Supporta autenticazione via API key
- Può essere esposto pubblicamente o solo internamente

---

## 🔄 Aggiornamenti

Per aggiornare il server:

```bash
cd /path/to/mcp-server
git pull  # Se usi git
npm install
pm2 restart wordpress-mcp-server
```

