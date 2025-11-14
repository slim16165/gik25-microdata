/**
 * Configurazione PM2 per MCP Server su Cloudways
 * 
 * Installazione PM2:
 * npm install -g pm2
 * 
 * Avvio:
 * pm2 start ecosystem.config.js
 * pm2 save
 * pm2 startup
 */

module.exports = {
    apps: [{
        name: 'wordpress-mcp-server',
        script: 'server-http.js',
        instances: 1,
        exec_mode: 'fork',
        env: {
            NODE_ENV: 'production',
            WP_BASE_URL: 'https://www.totaldesign.it',
            MCP_HTTP_PORT: 3000,
            MCP_HTTP_HOST: '0.0.0.0',
            MCP_API_KEY: process.env.MCP_API_KEY || '', // Imposta via: export MCP_API_KEY=...
        },
        error_file: './logs/pm2-error.log',
        out_file: './logs/pm2-out.log',
        log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
        merge_logs: true,
        autorestart: true,
        watch: false,
        max_memory_restart: '500M',
    }]
};

