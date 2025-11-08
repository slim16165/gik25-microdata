#!/usr/bin/env node

/**
 * MCP Server generico per WordPress
 * Interroga qualsiasi sito WordPress via REST API
 * Supporta estensioni configurabili per siti specifici
 */

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
    CallToolRequestSchema,
    ListResourcesRequestSchema,
    ListToolsRequestSchema,
    ReadResourceRequestSchema,
} from '@modelcontextprotocol/sdk/types.js';

// Configurazione (può essere passata via env)
const WP_BASE_URL = process.env.WP_BASE_URL || 'https://www.totaldesign.it';
const API_NAMESPACE = 'wp-mcp/v1'; // Namespace generico WordPress MCP
const WP_API_NAMESPACE = 'wp/v2'; // REST API WordPress nativa

// Autenticazione per modifiche (Application Password)
// Formato: username:application_password (base64 encoded)
const WP_AUTH = process.env.WP_AUTH || null; // Base64 encoded username:password
const VAULT_PATH = process.env.VAULT_PATH || null; // Percorso vault (opzionale)

// Estensioni specifiche per siti (configurabile via database o env)
// Formato: { 'domain.com': { name: 'SiteName', features: ['feature1', 'feature2'] } }
const SITE_EXTENSIONS = process.env.SITE_EXTENSIONS 
    ? JSON.parse(process.env.SITE_EXTENSIONS)
    : {
        'totaldesign.it': {
            name: 'TotalDesign',
            features: ['colors', 'ikea', 'rooms', 'pantone'],
        },
    };

class WordPressMCPServer {
    constructor() {
        this.server = new Server(
            {
                name: 'wordpress-mcp-server',
                version: '1.0.0',
            },
            {
                capabilities: {
                    resources: {},
                    tools: {},
                },
            }
        );

        this.setupHandlers();
    }

    setupHandlers() {
        // Lista risorse disponibili
        this.server.setRequestHandler(ListResourcesRequestSchema, async () => {
            return {
                resources: [
                    {
                        uri: 'wp://categories',
                        name: 'Categorie WordPress',
                        description: 'Lista tutte le categorie del sito',
                        mimeType: 'application/json',
                    },
                    {
                        uri: 'wp://posts/popular',
                        name: 'Post Popolari',
                        description: 'Post più popolari del sito',
                        mimeType: 'application/json',
                    },
                    {
                        uri: 'wp://posts/recent',
                        name: 'Post Recenti',
                        description: 'Post più recenti del sito',
                        mimeType: 'application/json',
                    },
                ],
            };
        });

        // Leggi risorsa
        this.server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
            const { uri } = request.params;

            try {
                let data;
                if (uri === 'wp://categories') {
                    data = await this.fetchCategories();
                } else if (uri === 'wp://posts/popular') {
                    data = await this.fetchPopularPosts();
                } else if (uri === 'wp://posts/recent') {
                    data = await this.fetchRecentPosts();
                } else if (uri.startsWith('wp://posts/category/')) {
                    const slug = uri.replace('wp://posts/category/', '');
                    data = await this.fetchPostsByCategory(slug);
                } else if (uri.startsWith('wp://posts/color/')) {
                    // Retrocompatibilità
                    const color = uri.replace('wp://posts/color/', '');
                    data = await this.fetchPostsByColor(color);
                } else if (uri.startsWith('wp://posts/ikea/')) {
                    // Retrocompatibilità
                    const line = uri.replace('wp://posts/ikea/', '');
                    data = await this.fetchPostsByIkeaLine(line);
                } else {
                    throw new Error(`Risorsa non trovata: ${uri}`);
                }

                return {
                    contents: [
                        {
                            uri,
                            mimeType: 'application/json',
                            text: JSON.stringify(data, null, 2),
                        },
                    ],
                };
            } catch (error) {
                throw new Error(`Errore nel recupero della risorsa: ${error.message}`);
            }
        });

        // Lista tools disponibili
        this.server.setRequestHandler(ListToolsRequestSchema, async () => {
            return {
                tools: [
                    {
                        name: 'search_posts',
                        description: 'Cerca post nel sito WordPress',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                query: {
                                    type: 'string',
                                    description: 'Query di ricerca',
                                },
                                limit: {
                                    type: 'number',
                                    description: 'Numero massimo di risultati (default: 20)',
                                    default: 20,
                                },
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
                                category_slug: {
                                    type: 'string',
                                    description: 'Slug della categoria',
                                },
                                limit: {
                                    type: 'number',
                                    description: 'Numero massimo di risultati (default: 10)',
                                    default: 10,
                                },
                            },
                            required: ['category_slug'],
                        },
                    },
                    {
                        name: 'get_posts_by_color',
                        description: 'Ottieni post relativi a un colore specifico',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                color: {
                                    type: 'string',
                                    description: 'Nome del colore (es: bianco, verde-salvia)',
                                },
                                limit: {
                                    type: 'number',
                                    description: 'Numero massimo di risultati (default: 15)',
                                    default: 15,
                                },
                            },
                            required: ['color'],
                        },
                    },
                    {
                        name: 'get_posts_by_ikea_line',
                        description: 'Ottieni post relativi a una linea IKEA',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                line: {
                                    type: 'string',
                                    description: 'Nome della linea IKEA (es: metod, enhet, billy)',
                                },
                                limit: {
                                    type: 'number',
                                    description: 'Numero massimo di risultati (default: 15)',
                                    default: 15,
                                },
                            },
                            required: ['line'],
                        },
                    },
                    {
                        name: 'get_posts_by_room',
                        description: 'Ottieni post relativi a una stanza',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                room: {
                                    type: 'string',
                                    description: 'Nome della stanza (es: cucina, soggiorno, camera)',
                                },
                                limit: {
                                    type: 'number',
                                    description: 'Numero massimo di risultati (default: 15)',
                                    default: 15,
                                },
                            },
                            required: ['room'],
                        },
                    },
                    {
                        name: 'get_pantone_posts',
                        description: 'Ottieni post relativi a Pantone',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                limit: {
                                    type: 'number',
                                    description: 'Numero massimo di risultati (default: 20)',
                                    default: 20,
                                },
                            },
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
                    {
                        name: 'get_post_full',
                        description: 'Ottieni post completo con contenuto, categorie e tag',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                post_id: {
                                    type: 'number',
                                    description: 'ID del post',
                                },
                            },
                            required: ['post_id'],
                        },
                    },
                    {
                        name: 'analyze_widget_suggestions',
                        description: 'Analizza contenuti e suggerisci widget da creare. Utile per capire quali widget sono necessari per una categoria o insieme di articoli.',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                category_slug: {
                                    type: 'string',
                                    description: 'Slug della categoria da analizzare',
                                },
                                post_ids: {
                                    type: 'array',
                                    items: { type: 'number' },
                                    description: 'Array di ID post da analizzare',
                                },
                                limit: {
                                    type: 'number',
                                    description: 'Numero massimo di post da analizzare (default: 50)',
                                    default: 50,
                                },
                            },
                        },
                    },
                    {
                        name: 'analyze_patterns',
                        description: 'Analizza pattern comuni in contenuti (cucine, colori, IKEA, stanze)',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                category_slug: {
                                    type: 'string',
                                    description: 'Slug della categoria da analizzare',
                                },
                                limit: {
                                    type: 'number',
                                    description: 'Numero massimo di post da analizzare (default: 100)',
                                    default: 100,
                                },
                            },
                        },
                    },
                    {
                        name: 'update_post',
                        description: 'Modifica un articolo WordPress. Richiede autenticazione configurata.',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                post_id: {
                                    type: 'number',
                                    description: 'ID del post da modificare',
                                },
                                title: {
                                    type: 'string',
                                    description: 'Nuovo titolo (opzionale)',
                                },
                                content: {
                                    type: 'string',
                                    description: 'Nuovo contenuto (opzionale)',
                                },
                                excerpt: {
                                    type: 'string',
                                    description: 'Nuovo excerpt (opzionale)',
                                },
                                categories: {
                                    type: 'array',
                                    items: { type: 'number' },
                                    description: 'Array di ID categorie (opzionale)',
                                },
                                tags: {
                                    type: 'array',
                                    items: { 
                                        oneOf: [
                                            { type: 'number' },
                                            { type: 'string' }
                                        ]
                                    },
                                    description: 'Array di ID tag (numeri) o nomi tag (stringhe). I tag vengono creati se non esistono.',
                                },
                            },
                            required: ['post_id'],
                        },
                    },
                    {
                        name: 'get_tags',
                        description: 'Ottieni lista di tag WordPress. Può cercare tag per nome.',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                search: {
                                    type: 'string',
                                    description: 'Cerca tag per nome (opzionale)',
                                },
                                limit: {
                                    type: 'number',
                                    description: 'Numero massimo di risultati (default: 100)',
                                    default: 100,
                                },
                            },
                        },
                    },
                    {
                        name: 'get_post_tags',
                        description: 'Ottieni tag di un post specifico',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                post_id: {
                                    type: 'number',
                                    description: 'ID del post',
                                },
                            },
                            required: ['post_id'],
                        },
                    },
                    {
                        name: 'add_tags_to_post',
                        description: 'Aggiungi tag a un post. I tag vengono creati se non esistono. Richiede autenticazione.',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                post_id: {
                                    type: 'number',
                                    description: 'ID del post',
                                },
                                tags: {
                                    type: 'array',
                                    items: { type: 'string' },
                                    description: 'Array di nomi tag da aggiungere',
                                },
                            },
                            required: ['post_id', 'tags'],
                        },
                    },
                    {
                        name: 'create_tag',
                        description: 'Crea un nuovo tag. Richiede autenticazione.',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                name: {
                                    type: 'string',
                                    description: 'Nome del tag',
                                },
                                slug: {
                                    type: 'string',
                                    description: 'Slug del tag (opzionale, generato automaticamente se non fornito)',
                                },
                                description: {
                                    type: 'string',
                                    description: 'Descrizione del tag (opzionale)',
                                },
                            },
                            required: ['name'],
                        },
                    },
                    ...(VAULT_PATH ? [{
                        name: 'search_vault',
                        description: 'Cerca nel vault (file markdown locali)',
                        inputSchema: {
                            type: 'object',
                            properties: {
                                query: {
                                    type: 'string',
                                    description: 'Query di ricerca',
                                },
                                limit: {
                                    type: 'number',
                                    description: 'Numero massimo di risultati (default: 10)',
                                    default: 10,
                                },
                            },
                            required: ['query'],
                        },
                    }] : []),
                ],
            };
        });

        // Esegui tool
        this.server.setRequestHandler(CallToolRequestSchema, async (request) => {
            const { name, arguments: args } = request.params;

            try {
                let result;
                switch (name) {
                    case 'search_posts':
                        result = await this.searchPosts(args.query, args.limit || 20);
                        break;
                    case 'get_posts_by_category':
                        result = await this.fetchPostsByCategory(args.category_slug, args.limit || 10);
                        break;
                    case 'get_posts_by_color':
                        result = await this.fetchPostsByColor(args.color, args.limit || 15);
                        break;
                    case 'get_posts_by_ikea_line':
                        result = await this.fetchPostsByIkeaLine(args.line, args.limit || 15);
                        break;
                    case 'get_posts_by_room':
                        result = await this.fetchPostsByRoom(args.room, args.limit || 15);
                        break;
                    case 'get_pantone_posts':
                        result = await this.fetchPantonePosts(args.limit || 20);
                        break;
                    case 'get_categories':
                        result = await this.fetchCategories();
                        break;
                    case 'get_post_full':
                        result = await this.getPostFull(args.post_id);
                        break;
                    case 'analyze_widget_suggestions':
                        result = await this.analyzeWidgetSuggestions(args);
                        break;
                    case 'analyze_patterns':
                        result = await this.analyzePatterns(args);
                        break;
                    case 'update_post':
                        result = await this.updatePost(args);
                        break;
                    case 'get_tags':
                        result = await this.getTags(args.search, args.limit || 100);
                        break;
                    case 'get_post_tags':
                        result = await this.getPostTags(args.post_id);
                        break;
                    case 'add_tags_to_post':
                        result = await this.addTagsToPost(args.post_id, args.tags);
                        break;
                    case 'create_tag':
                        result = await this.createTag(args);
                        break;
                    case 'search_vault':
                        if (!VAULT_PATH) {
                            throw new Error('Vault non configurato. Imposta VAULT_PATH in ambiente.');
                        }
                        result = await this.searchVault(args.query, args.limit || 10);
                        break;
                    default:
                        throw new Error(`Tool sconosciuto: ${name}`);
                }

                return {
                    content: [
                        {
                            type: 'text',
                            text: JSON.stringify(result, null, 2),
                        },
                    ],
                };
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
    }

    // Metodi helper per chiamate API
    async fetchAPI(endpoint, params = {}, method = 'GET', body = null) {
        const url = new URL(`${WP_BASE_URL}/wp-json/${API_NAMESPACE}${endpoint}`);
        
        if (method === 'GET') {
            Object.entries(params).forEach(([key, value]) => {
                url.searchParams.append(key, value);
            });
        }

        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
            },
        };

        // Aggiungi autenticazione se disponibile
        if (WP_AUTH && method !== 'GET') {
            options.headers['Authorization'] = `Basic ${WP_AUTH}`;
        }

        if (body) {
            options.body = JSON.stringify(body);
        }

        const response = await fetch(url.toString(), options);
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP ${response.status}: ${response.statusText} - ${errorText}`);
        }
        
        return await response.json();
    }

    // Chiama REST API WordPress nativa (per modifiche)
    async fetchWPNativeAPI(endpoint, method = 'GET', body = null) {
        const url = new URL(`${WP_BASE_URL}/wp-json/${WP_API_NAMESPACE}${endpoint}`);

        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
            },
        };

        // Richiede autenticazione per modifiche
        if (WP_AUTH) {
            options.headers['Authorization'] = `Basic ${WP_AUTH}`;
        } else {
            throw new Error('Autenticazione WordPress non configurata. Imposta WP_AUTH in ambiente.');
        }

        if (body) {
            options.body = JSON.stringify(body);
        }

        const response = await fetch(url.toString(), options);
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP ${response.status}: ${response.statusText} - ${errorText}`);
        }
        
        return await response.json();
    }

    async fetchCategories() {
        return await this.fetchAPI('/categories');
    }

    async fetchPostsByCategory(slug, limit = 10) {
        return await this.fetchAPI(`/posts/category/${slug}`, { limit });
    }

    async searchPosts(query, limit = 20) {
        return await this.fetchAPI('/posts/search', { q: query, limit });
    }

    async fetchPostsByColor(color, limit = 15) {
        return await this.fetchAPI(`/posts/color/${color}`, { limit });
    }

    async fetchPostsByIkeaLine(line, limit = 15) {
        return await this.fetchAPI(`/posts/ikea/${line}`, { limit });
    }

    async fetchPostsByRoom(room, limit = 15) {
        return await this.fetchAPI(`/posts/room/${room}`, { limit });
    }

    async fetchPantonePosts(limit = 20) {
        return await this.fetchAPI('/posts/pantone', { limit });
    }

    async fetchPopularPosts(limit = 20) {
        return await this.fetchAPI('/posts/popular', { limit });
    }

    async fetchRecentPosts(limit = 20) {
        return await this.fetchAPI('/posts/recent', { limit });
    }

    async getPostFull(postId) {
        return await this.fetchAPI(`/posts/${postId}`);
    }

    async analyzeWidgetSuggestions(args) {
        const body = {};
        if (args.category_slug) body.category_slug = args.category_slug;
        if (args.post_ids) body.post_ids = args.post_ids;
        if (args.limit) body.limit = args.limit;

        return await this.fetchAPI('/analyze/widget-suggestions', {}, 'POST', body);
    }

    async analyzePatterns(args) {
        const body = {};
        if (args.category_slug) body.category_slug = args.category_slug;
        if (args.limit) body.limit = args.limit;

        return await this.fetchAPI('/analyze/patterns', {}, 'POST', body);
    }

    async updatePost(args) {
        const body = {};
        if (args.title) body.title = args.title;
        if (args.content) body.content = args.content;
        if (args.excerpt) body.excerpt = args.excerpt;
        if (args.categories) body.categories = args.categories;
        
        // Gestisci tag: possono essere ID (numeri) o nomi (stringhe)
        if (args.tags) {
            const tagIds = [];
            const tagNames = [];
            
            for (const tag of args.tags) {
                if (typeof tag === 'number') {
                    tagIds.push(tag);
                } else if (typeof tag === 'string') {
                    tagNames.push(tag);
                }
            }
            
            // Se ci sono nomi tag, convertili in ID (creando i tag se necessario)
            if (tagNames.length > 0) {
                const resolvedIds = await this.resolveTagNamesToIds(tagNames);
                tagIds.push(...resolvedIds);
            }
            
            body.tags = tagIds;
        }

        return await this.fetchWPNativeAPI(`/posts/${args.post_id}`, 'POST', body);
    }

    async getTags(search = null, limit = 100) {
        const params = { limit };
        if (search) params.search = search;
        return await this.fetchAPI('/tags', params);
    }

    async getPostTags(postId) {
        return await this.fetchAPI(`/posts/${postId}/tags`);
    }

    async addTagsToPost(postId, tagNames) {
        // Prima risolvi i nomi tag in ID (creando i tag se necessario)
        const tagIds = await this.resolveTagNamesToIds(tagNames);
        
        // Ottieni i tag esistenti del post
        const existingTags = await this.getPostTags(postId);
        const existingTagIds = existingTags.map(tag => tag.id);
        
        // Unisci i tag esistenti con i nuovi
        const allTagIds = [...new Set([...existingTagIds, ...tagIds])];
        
        // Aggiorna il post con tutti i tag
        return await this.fetchWPNativeAPI(`/posts/${postId}`, 'POST', { tags: allTagIds });
    }

    async createTag(args) {
        const body = {
            name: args.name,
        };
        
        if (args.slug) body.slug = args.slug;
        if (args.description) body.description = args.description;

        return await this.fetchWPNativeAPI('/tags', 'POST', body);
    }

    /**
     * Risolve nomi tag in ID, creando i tag se non esistono
     */
    async resolveTagNamesToIds(tagNames) {
        const tagIds = [];
        
        for (const tagName of tagNames) {
            // Cerca il tag esistente
            const tags = await this.getTags(tagName, 10);
            let tag = tags.find(t => t.name.toLowerCase() === tagName.toLowerCase());
            
            if (tag) {
                // Tag esiste, usa il suo ID
                tagIds.push(tag.id);
            } else {
                // Tag non esiste, crealo
                try {
                    const newTag = await this.createTag({ name: tagName });
                    tagIds.push(newTag.id);
                } catch (error) {
                    // Se il tag esiste già (race condition), cerca di nuovo
                    const tagsRetry = await this.getTags(tagName, 10);
                    const existingTag = tagsRetry.find(t => t.name.toLowerCase() === tagName.toLowerCase());
                    if (existingTag) {
                        tagIds.push(existingTag.id);
                    } else {
                        throw new Error(`Impossibile creare il tag "${tagName}": ${error.message}`);
                    }
                }
            }
        }
        
        return tagIds;
    }

    async searchVault(query, limit = 10) {
        // Implementazione base per ricerca nel vault
        // Può essere estesa per supportare Obsidian, file markdown, ecc.
        const fs = await import('fs/promises');
        const path = await import('path');

        try {
            const files = await fs.readdir(VAULT_PATH, { recursive: true, withFileTypes: true });
            const markdownFiles = files
                .filter(file => file.isFile() && file.name.endsWith('.md'))
                .slice(0, 100); // Limita a 100 file per performance

            const results = [];

            for (const file of markdownFiles) {
                try {
                    const filePath = path.join(file.path || VAULT_PATH, file.name);
                    const content = await fs.readFile(filePath, 'utf-8');
                    const lowerContent = content.toLowerCase();
                    const lowerQuery = query.toLowerCase();

                    if (lowerContent.includes(lowerQuery)) {
                        // Trova snippet con la query
                        const lines = content.split('\n');
                        const matchingLines = lines
                            .map((line, index) => ({ line, index }))
                            .filter(({ line }) => line.toLowerCase().includes(lowerQuery))
                            .slice(0, 3); // Prime 3 righe che matchano

                        results.push({
                            file: file.name,
                            path: filePath,
                            matches: matchingLines.length,
                            snippets: matchingLines.map(({ line, index }) => ({
                                line: index + 1,
                                content: line.trim().substring(0, 200),
                            })),
                        });

                        if (results.length >= limit) break;
                    }
                } catch (err) {
                    // Ignora errori di lettura file
                    continue;
                }
            }

            return {
                query,
                total_results: results.length,
                results,
            };
        } catch (error) {
            throw new Error(`Errore nella ricerca vault: ${error.message}`);
        }
    }

    async run() {
        const transport = new StdioServerTransport();
        await this.server.connect(transport);
        
        const siteUrl = new URL(WP_BASE_URL).hostname;
        const extension = SITE_EXTENSIONS[siteUrl] || null;
        
        console.error(`WordPress MCP Server avviato`);
        console.error(`- WordPress: ${WP_BASE_URL}`);
        console.error(`- API Namespace: ${API_NAMESPACE}`);
        if (extension) {
            console.error(`- Estensioni: ${extension.name} (${extension.features.join(', ')})`);
        } else {
            console.error(`- Estensioni: Nessuna (modo generico)`);
        }
        if (WP_AUTH) {
            console.error(`- Autenticazione: Configurata`);
        }
        if (VAULT_PATH) {
            console.error(`- Vault: ${VAULT_PATH}`);
        }
    }
}

// Avvia il server
const server = new WordPressMCPServer();
server.run().catch(console.error);

