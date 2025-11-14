#!/bin/bash

# Script per avviare MCP Server su Cloudways
# Questo script può essere eseguito come servizio systemd o via PM2

# Carica variabili d'ambiente da file .env se esiste
if [ -f .env ]; then
    export $(cat .env | grep -v '^#' | xargs)
fi

# Configurazione default
export WP_BASE_URL=${WP_BASE_URL:-"https://www.totaldesign.it"}
export MCP_HTTP_PORT=${MCP_HTTP_PORT:-3000}
export MCP_HTTP_HOST=${MCP_HTTP_HOST:-"0.0.0.0"}
export MCP_API_KEY=${MCP_API_KEY:-""}

# Genera API key se non esiste
if [ -z "$MCP_API_KEY" ]; then
    echo "ATTENZIONE: MCP_API_KEY non configurata. Genera una chiave sicura."
    echo "Puoi generarla con: openssl rand -hex 32"
    exit 1
fi

# Avvia server HTTP
echo "Avvio WordPress MCP HTTP Server..."
node server-http.js

