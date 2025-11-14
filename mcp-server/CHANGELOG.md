# Changelog MCP Server

## [1.1.0] - 2025-01-30

### Aggiunto
- **Server HTTP** (`server-http.js`): Versione HTTP del server MCP per esecuzione remota su Cloudways
- **Supporto remoto**: Il server può ora essere eseguito come servizio HTTP su Cloudways
- **Autenticazione API Key**: Supporto per autenticazione via API key per sicurezza
- **Script di deployment**: Script per PM2 e systemd per gestione servizio
- **Documentazione Cloudways**: Guida completa per deploy su Cloudways

### Modificato
- `package.json`: Aggiunti script `start:http` e `start:cloudways`
- Documentazione aggiornata con istruzioni per esecuzione remota

### Note
- Il server stdio originale (`server.js`) rimane invariato per uso locale
- Il server HTTP (`server-http.js`) è una nuova versione per uso remoto
- Entrambe le versioni possono coesistere

