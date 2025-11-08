#!/usr/bin/env node

/**
 * Script di test per la REST API TotalDesign
 * Esegui: node test-api.js
 */

const WP_BASE_URL = process.env.WP_BASE_URL || 'https://www.totaldesign.it';
const API_NAMESPACE = 'td-mcp/v1';

async function testEndpoint(name, url) {
    try {
        console.log(`\n🧪 Test: ${name}`);
        console.log(`   URL: ${url}`);
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (response.ok) {
            console.log(`   ✅ OK (${response.status})`);
            if (Array.isArray(data)) {
                console.log(`   📊 Risultati: ${data.length}`);
                if (data.length > 0) {
                    console.log(`   📝 Primo risultato:`, JSON.stringify(data[0], null, 2).substring(0, 200) + '...');
                }
            } else {
                console.log(`   📝 Risposta:`, JSON.stringify(data, null, 2).substring(0, 300));
            }
            return true;
        } else {
            console.log(`   ❌ ERRORE (${response.status})`);
            console.log(`   📝 Risposta:`, JSON.stringify(data, null, 2));
            return false;
        }
    } catch (error) {
        console.log(`   ❌ ERRORE: ${error.message}`);
        return false;
    }
}

async function runTests() {
    console.log('🚀 Test REST API TotalDesign MCP\n');
    console.log(`📍 Base URL: ${WP_BASE_URL}\n`);
    
    const tests = [
        ['Lista Categorie', `${WP_BASE_URL}/wp-json/${API_NAMESPACE}/categories`],
        ['Post Recenti', `${WP_BASE_URL}/wp-json/${API_NAMESPACE}/posts/recent?limit=5`],
        ['Post Popolari', `${WP_BASE_URL}/wp-json/${API_NAMESPACE}/posts/popular?limit=5`],
        ['Ricerca "ikea"', `${WP_BASE_URL}/wp-json/${API_NAMESPACE}/posts/search?q=ikea&limit=5`],
        ['Post Pantone', `${WP_BASE_URL}/wp-json/${API_NAMESPACE}/posts/pantone?limit=5`],
        ['Post per Colore "bianco"', `${WP_BASE_URL}/wp-json/${API_NAMESPACE}/posts/color/bianco?limit=5`],
        ['Post IKEA "metod"', `${WP_BASE_URL}/wp-json/${API_NAMESPACE}/posts/ikea/metod?limit=5`],
        ['Post per Stanza "cucina"', `${WP_BASE_URL}/wp-json/${API_NAMESPACE}/posts/room/cucina?limit=5`],
    ];
    
    let passed = 0;
    let failed = 0;
    
    for (const [name, url] of tests) {
        const result = await testEndpoint(name, url);
        if (result) {
            passed++;
        } else {
            failed++;
        }
        // Piccola pausa tra le richieste
        await new Promise(resolve => setTimeout(resolve, 500));
    }
    
    console.log(`\n\n📊 Riepilogo:`);
    console.log(`   ✅ Passati: ${passed}`);
    console.log(`   ❌ Falliti: ${failed}`);
    console.log(`   📈 Totale: ${passed + failed}\n`);
    
    if (failed === 0) {
        console.log('🎉 Tutti i test sono passati! La REST API funziona correttamente.\n');
    } else {
        console.log('⚠️  Alcuni test sono falliti. Controlla gli errori sopra.\n');
    }
}

runTests().catch(console.error);

