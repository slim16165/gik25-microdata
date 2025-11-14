#!/usr/bin/env node

/**
 * MCP Server HTTP per esecuzione remota su Cloudways
 * Versione HTTP del server MCP che può essere eseguita come servizio web
 * Supporta autenticazione via API key e comunicazione HTTP/WebSocket
 */

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import http from 'http';
import url from 'url';

import {
    CallToolRequestSchema,
    ListResourcesRequestSchema,
    ListToolsRequestSchema,
    ReadResourceRequestSchema,
} from '@modelcontextprotocol/sdk/types.js';

// Configurazione (può essere passata via env)
const WP_BASE_URL = process.env.WP_BASE_URL || 'https://www.totaldesign.it';
const API_NAMESPACE = 'wp-mcp/v1';
const WP_API_NAMESPACE = 'wp/v2';
const WP_AUTH = process.env.WP_AUTH || null;
const VAULT_PATH = process.env.VAULT_PATH || null;

// Configurazione server HTTP
const HTTP_PORT = process.env.MCP_HTTP_PORT || 3000;
const HTTP_HOST = process.env.MCP_HTTP_HOST || '0.0.0.0';
const API_KEY = process.env.MCP_API_KEY || null; // API key per autenticazione

// Estensioni specifiche per siti
const SITE_EXTENSIONS = process.env.SITE_EXTENSIONS
    ? JSON.parse(process.env.SITE_EXTENSIONS)
    : {
        'totaldesign.it': {
            name: 'TotalDesign',
            features: ['colors', 'ikea', 'rooms', 'pantone'],
        },
    };

/**
 * Verifica autenticazione API key
 */
function verifyAuth(req) {
    if (!API_KEY) {
        return true; // Nessuna autenticazione richiesta se API_KEY non è configurata
    }
    
    const authHeader = req.headers['authorization'];
    if (!authHeader) {
        return false;
    }
    
    // Supporta sia "Bearer TOKEN" che "API-Key TOKEN"
    const token = authHeader.replace(/^(Bearer|API-Key)\s+/i, '');
    return token === API_KEY;
}

/**
 * Crea risposta JSON
 */
function jsonResponse(res, statusCode, data) {
    res.writeHead(statusCode, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify(data));
}

/**
 * Crea risposta di errore
 */
function errorResponse(res, statusCode, message) {
    jsonResponse(res, statusCode, { error: message });
}

class WordPressMCPHTTPServer {
    constructor() {
        this.server = new Server(
            {
                name: 'wordpress-mcp-http',
                version: '1.0.0',
            },
            {
                capabilities: {
                    tools: {},
                    resources: {},
                },
            }
        );
        
        this.setupHandlers();
    }
    
    setupHandlers() {
        // Lista tools
        this.server.setRequestHandler(ListToolsRequestSchema, async () => {
            return {
                tools: [
                    {
                        name: 'search_posts',
                        description: 'Cerca post nel sito WordPress',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                query: { type: 'string', description: 'Query di ricerca' },
                                limit: { type: 'number', description: 'Numero massimo di risultati', default: 20 },
                            },
                            required: ['query'],
                        },
                    },
                    {
                        name: 'get_posts_by_category',
                        description: 'Ottieni post per categoria',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                category_slug: { type: 'string', description: 'Slug della categoria' },
                                limit: { type: 'number', default: 10 },
                            },
                            required: ['category_slug'],
                        },
                    },
                    {
                        name: 'get_categories',
                        description: 'Ottieni lista di tutte le categorie WordPress',
                        inputSchema: {
                            type: 'object',
                            properties: {},
                        },
                    },
                    // Aggiungi altri tools come nel server stdio originale
                ],
            };
        });
        
        // Lista resources
        this.server.setRequestHandler(ListResourcesRequestSchema, async () => {
            return {
                resources: [
                    {
                        uri: 'wordpress://posts',
                        name: 'WordPress Posts',
                        description: 'Accesso ai post WordPress',
                        mimeType: 'application/json',
                    },
                    {
                        uri: 'wordpress://categories',
                        name: 'WordPress Categories',
                        description: 'Accesso alle categorie WordPress',
                        mimeType: 'application/json',
                    },
                ],
            };
        });
        
        // Call tool
        this.server.setRequestHandler(CallToolRequestSchema, async (request) => {
            const { name, arguments: args } = request.params;
            
            try {
                switch (name) {
                    case 'search_posts':
                        return await this.searchPosts(args.query, args.limit || 20);
                    
                    case 'get_posts_by_category':
                        return await this.getPostsByCategory(args.category_slug, args.limit || 10);
                    
                    case 'get_categories':
                        return await this.getCategories();
                    
                    default:
                        throw new Error(`Tool sconosciuto: ${name}`);
                }
            } catch (error) {
                return {
                    content: [
                        {
                            type: 'text',
                            text: `Errore: ${error.message}`,
                        },
                    ],
                    isError: true,
                };
            }
        });
        
        // Read resource
        this.server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
            const { uri } = request.params;
            
            try {
                if (uri.startsWith('wordpress://posts')) {
                    return await this.getPostsResource();
                } else if (uri.startsWith('wordpress://categories')) {
                    return await this.getCategoriesResource();
                } else {
                    throw new Error(`Resource sconosciuta: ${uri}`);
                }
            } catch (error) {
                return {
                    contents: [
                        {
                            uri,
                            mimeType: 'text/plain',
                            text: `Errore: ${error.message}`,
                        },
                    ],
                };
            }
        });
    }
    
    /**
     * Chiama WordPress REST API
     */
    async callWordPressAPI(endpoint, method = 'GET', body = null) {
        const url = `${WP_BASE_URL}/wp-json/${API_NAMESPACE}${endpoint}`;
        const headers = {
            'Content-Type': 'application/json',
        };
        
        if (WP_AUTH) {
            headers['Authorization'] = `Basic ${WP_AUTH}`;
        }
        
        const options = {
            method,
            headers,
        };
        
        if (body) {
            options.body = JSON.stringify(body);
        }
        
        const response = await fetch(url, options);
        
        if (!response.ok) {
            throw new Error(`WordPress API error: ${response.status} ${response.statusText}`);
        }
        
        return await response.json();
    }
    
    async searchPosts(query, limit) {
        const data = await this.callWordPressAPI(`/posts/search?q=${encodeURIComponent(query)}&limit=${limit}`);
        return {
            content: [
                {
                    type: 'text',
                    text: JSON.stringify(data, null, 2),
                },
            ],
        };
    }
    
    async getPostsByCategory(categorySlug, limit) {
        const data = await this.callWordPressAPI(`/posts/category/${categorySlug}?limit=${limit}`);
        return {
            content: [
                {
                    type: 'text',
                    text: JSON.stringify(data, null, 2),
                },
            ],
        };
    }
    
    async getCategories() {
        const data = await this.callWordPressAPI('/categories');
        return {
            content: [
                {
                    type: 'text',
                    text: JSON.stringify(data, null, 2),
                },
            ],
        };
    }
    
    async getPostsResource() {
        const data = await this.callWordPressAPI('/posts/recent?limit=50');
        return {
            contents: [
                {
                    uri: 'wordpress://posts',
                    mimeType: 'application/json',
                    text: JSON.stringify(data, null, 2),
                },
            ],
        };
    }
    
    async getCategoriesResource() {
        const data = await this.callWordPressAPI('/categories');
        return {
            contents: [
                {
                    uri: 'wordpress://categories',
                    mimeType: 'application/json',
                    text: JSON.stringify(data, null, 2),
                },
            ],
        };
    }
    
    /**
     * Helper per chiamare tool
     */
    async callTool(name, args) {
        const handler = this.server._requestHandlers?.get(CallToolRequestSchema);
        if (handler) {
            return await handler({ params: { name, arguments: args } });
        }
        // Fallback: chiama direttamente i metodi helper
        switch (name) {
            case 'search_posts':
                return await this.searchPosts(args.query, args.limit || 20);
            case 'get_posts_by_category':
                return await this.getPostsByCategory(args.category_slug, args.limit || 10);
            case 'get_categories':
                return await this.getCategories();
            default:
                throw new Error(`Tool sconosciuto: ${name}`);
        }
    }
    
    /**
     * Helper per lista tools
     */
    async listTools() {
        const handler = this.server._requestHandlers?.get(ListToolsRequestSchema);
        if (handler) {
            return await handler({ params: {} });
        }
        // Fallback: ritorna lista base
        return {
            tools: [
                {
                    name: 'search_posts',
                    description: 'Cerca post nel sito WordPress',
                },
                {
                    name: 'get_posts_by_category',
                    description: 'Ottieni post per categoria',
                },
                {
                    name: 'get_categories',
                    description: 'Ottieni lista di tutte le categorie WordPress',
                },
            ],
        };
    }
    
    /**
     * Helper per lista resources
     */
    async listResources() {
        const handler = this.server._requestHandlers?.get(ListResourcesRequestSchema);
        if (handler) {
            return await handler({ params: {} });
        }
        // Fallback: ritorna lista base
        return {
            resources: [
                {
                    uri: 'wordpress://posts',
                    name: 'WordPress Posts',
                    description: 'Accesso ai post WordPress',
                    mimeType: 'application/json',
                },
                {
                    uri: 'wordpress://categories',
                    name: 'WordPress Categories',
                    description: 'Accesso alle categorie WordPress',
                    mimeType: 'application/json',
                },
            ],
        };
    }
    
    /**
     * Helper per leggere resource
     */
    async readResource(uri) {
        const handler = this.server._requestHandlers?.get(ReadResourceRequestSchema);
        if (handler) {
            return await handler({ params: { uri } });
        }
        // Fallback: chiama direttamente i metodi helper
        if (uri.startsWith('wordpress://posts')) {
            return await this.getPostsResource();
        } else if (uri.startsWith('wordpress://categories')) {
            return await this.getCategoriesResource();
        } else {
            throw new Error(`Resource sconosciuta: ${uri}`);
        }
    }
    
    /**
     * Avvia server HTTP
     */
    async start() {
        const httpServer = http.createServer(async (req, res) => {
            // CORS headers
            res.setHeader('Access-Control-Allow-Origin', '*');
            res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            
            if (req.method === 'OPTIONS') {
                res.writeHead(200);
                res.end();
                return;
            }
            
            // Verifica autenticazione
            if (!verifyAuth(req)) {
                errorResponse(res, 401, 'Unauthorized: API key richiesta');
                return;
            }
            
            const parsedUrl = url.parse(req.url, true);
            const path = parsedUrl.pathname;
            
            // Health check
            if (path === '/health' || path === '/') {
                jsonResponse(res, 200, {
                    status: 'ok',
                    service: 'wordpress-mcp-http',
                    version: '1.0.0',
                    wordpress: WP_BASE_URL,
                });
                return;
            }
            
            // Endpoint per chiamate MCP tools
            if (path === '/mcp/tools/call' && req.method === 'POST') {
                let body = '';
                req.on('data', chunk => {
                    body += chunk.toString();
                });
                
                req.on('end', async () => {
                    try {
                        const request = JSON.parse(body);
                        const { name, arguments: args } = request;
                        
                        // Chiama direttamente il metodo helper
                        const result = await this.callTool(name, args || {});
                        jsonResponse(res, 200, result);
                    } catch (error) {
                        errorResponse(res, 500, `Tool error: ${error.message}`);
                    }
                });
                return;
            }
            
            // Endpoint per lista tools
            if (path === '/mcp/tools' && req.method === 'GET') {
                try {
                    const result = await this.listTools();
                    jsonResponse(res, 200, result);
                } catch (error) {
                    errorResponse(res, 500, `Error: ${error.message}`);
                }
                return;
            }
            
            // Endpoint per lista resources
            if (path === '/mcp/resources' && req.method === 'GET') {
                try {
                    const result = await this.listResources();
                    jsonResponse(res, 200, result);
                } catch (error) {
                    errorResponse(res, 500, `Error: ${error.message}`);
                }
                return;
            }
            
            // Endpoint per leggere resource
            if (path.startsWith('/mcp/resources/') && req.method === 'GET') {
                try {
                    const uri = decodeURIComponent(path.replace('/mcp/resources/', ''));
                    const result = await this.readResource(uri);
                    jsonResponse(res, 200, result);
                } catch (error) {
                    errorResponse(res, 500, `Error: ${error.message}`);
                }
                return;
            }
            
            // Proxy diretto a WordPress REST API (per semplicità)
            if (path.startsWith('/wp/')) {
                try {
                    const wpEndpoint = path.replace('/wp/', '');
                    const data = await this.callWordPressAPI(`/${wpEndpoint}${parsedUrl.search || ''}`);
                    jsonResponse(res, 200, data);
                } catch (error) {
                    errorResponse(res, 500, error.message);
                }
                return;
            }
            
            errorResponse(res, 404, 'Not Found');
        });
        
        httpServer.listen(HTTP_PORT, HTTP_HOST, () => {
            console.log(`WordPress MCP HTTP Server avviato`);
            console.log(`- Host: ${HTTP_HOST}:${HTTP_PORT}`);
            console.log(`- WordPress: ${WP_BASE_URL}`);
            console.log(`- API Namespace: ${API_NAMESPACE}`);
            if (API_KEY) {
                console.log(`- Autenticazione: Abilitata (API Key)`);
            } else {
                console.log(`- Autenticazione: Disabilitata`);
            }
            console.log(`- Health Check: http://${HTTP_HOST}:${HTTP_PORT}/health`);
            console.log(`- Tools Endpoint: http://${HTTP_HOST}:${HTTP_PORT}/mcp/tools`);
            console.log(`- Resources Endpoint: http://${HTTP_HOST}:${HTTP_PORT}/mcp/resources`);
        });
    }
}

// Avvia il server
const server = new WordPressMCPHTTPServer();
server.start().catch(console.error);

