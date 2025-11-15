# Internal Links System - Quick Start Guide

**Versione**: 1.0.0  
**Data**: Gennaio 2025

---

## Installazione

Il sistema è già integrato nel plugin `gik25-microdata`. Non è necessaria installazione separata.

### Attivazione Database

Le tabelle vengono create automaticamente all'attivazione del plugin. Se necessario, puoi forzare la creazione:

```php
\gik25microdata\InternalLinks\Core\DatabaseSchema::createTables();
```

---

## Configurazione Base

### 1. Abilitare il Sistema

Vai in **Impostazioni → Internal Links** e abilita il sistema.

### 2. Creare Autolinks

Vai in **Internal Links → Autolinks** e crea le tue prime regole:

- **Keyword**: La parola/frase da cercare
- **URL**: Il link di destinazione
- **Priority**: Priorità (più alto = applicato prima)
- **Max Links per Post**: Numero massimo di link per post
- **Post Types**: Su quali post types applicare

### 3. Configurare Impostazioni

In **Internal Links → Settings**:

- **Default SEO Power**: Potenza SEO di default (default: 100)
- **Penalty per Position**: Penalità per posizione link (default: 10%)
- **Same URL Limit**: Limite stesso URL per post (default: 1)
- **Click Tracking**: Abilita/disabilita tracking click

---

## Utilizzo Base

### Autolinks Automatici

Gli autolinks vengono applicati automaticamente al contenuto dei post quando:

1. Il post è pubblicato
2. Gli autolinks sono abilitati per quel post (o default abilitato)
3. La regola autolink è compatibile con il post (post type, categories, etc.)

### Suggerimenti Link

Per ottenere suggerimenti link per un post:

```php
$manager = \gik25microdata\InternalLinks\Core\InternalLinksManager::getInstance();
$suggestions = $manager->getSuggestions($post_id, 10);
```

### Calcolo Juice

Per calcolare il juice di un link:

```php
$juice = $manager->calculateJuice($post_id, $link_position);
// Restituisce: ['absolute' => 85.5, 'relative' => 75.2]
```

### Tracking Click

Il tracking click è automatico via JavaScript frontend. Per tracking manuale:

```php
$manager->trackClick($link_id, [
    'post_id' => $post_id,
    'ip_address' => $_SERVER['REMOTE_ADDR'],
]);
```

---

## Migrazione Dati

### Da Interlinks Manager (DAIM)

1. Vai in **Internal Links → Migration**
2. Seleziona "Migrate from Interlinks Manager"
3. Clicca "Run Migration"
4. Verifica i risultati

### Da Link Whisper Premium (WPIL)

1. Vai in **Internal Links → Migration**
2. Seleziona "Migrate from Link Whisper Premium"
3. Clicca "Run Migration"
4. Verifica i risultati

**Nota**: Fai sempre un backup del database prima della migrazione!

---

## REST API

### Endpoints Disponibili

**Base URL**: `/wp-json/gik25-il/v1/`

**Autolinks**:
- `GET /autolinks` - Lista autolinks
- `POST /autolinks` - Crea autolink
- `GET /autolinks/{id}` - Get autolink
- `PUT /autolinks/{id}` - Update autolink
- `DELETE /autolinks/{id}` - Delete autolink

**Suggestions**:
- `GET /suggestions/{post_id}` - Get suggestions
- `POST /suggestions/generate` - Genera suggestions

**Reports**:
- `GET /reports/links` - Link report
- `GET /reports/juice` - Juice report
- `GET /reports/clicks` - Click report

**Monitoring**:
- `GET /monitoring/health` - Health check
- `POST /monitoring/check-status` - Check HTTP status

---

## Hooks e Filters

### Actions

```php
// Dopo autolink applicato
do_action('gik25_il_autolink_applied', $post_id, $link_count);

// Dopo suggerimento generato
do_action('gik25_il_suggestion_generated', $post_id, $suggestions);

// Dopo juice calcolato
do_action('gik25_il_juice_calculated', $post_id, $juice_score);
```

### Filters

```php
// Modifica regole autolink prima applicazione
add_filter('gik25_il_autolink_rules', function($rules, $post_id) {
    // Modifica $rules
    return $rules;
}, 10, 2);

// Modifica suggerimenti
add_filter('gik25_il_suggestions', function($suggestions, $post_id) {
    // Modifica $suggestions
    return $suggestions;
}, 10, 2);

// Modifica juice score
add_filter('gik25_il_juice_score', function($juice, $post_id, $link_position) {
    // Modifica $juice
    return $juice;
}, 10, 3);
```

---

## Troubleshooting

### Autolinks non vengono applicati

1. Verifica che il sistema sia abilitato in Settings
2. Verifica che il post abbia autolinks abilitati (meta `_gik25_il_enable_ail`)
3. Verifica che la regola autolink sia compatibile (post type, categories)
4. Controlla i log per errori

### Performance lenta

1. Riduci il numero di autolinks attivi
2. Aumenta il limite "Max Links per Post"
3. Disabilita suggerimenti automatici se non necessari
4. Ottimizza database (indici)

### Migrazione non funziona

1. Verifica che le tabelle sorgente esistano
2. Controlla i permessi database
3. Verifica i log per errori specifici
4. Esegui migrazione in batch più piccoli

---

## Prossimi Step

1. **Completare Admin UI**: Templates dettagliati per tutte le pagine
2. **Implementare Stemming**: Sistema completo multi-lingua
3. **Editor Integration**: Integrazione completa Gutenberg
4. **Search Console**: Connessione e import dati GSC
5. **Testing**: Unit e integration tests completi

---

**Per maggiori dettagli**: Vedi `INTERNAL_LINKS_IMPLEMENTATION_STATUS.md`

