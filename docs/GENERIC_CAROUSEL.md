# Sistema Generico Caroselli/Liste - Documentazione

## 🎯 Obiettivo

Sistema generico e configurabile via database WordPress per creare caroselli, liste e griglie di link senza hardcodare nel codice PHP.

## 📊 Architettura Database

### Tabelle

#### `wp_carousel_collections`
Tabella per le collezioni configurabili.

| Campo | Tipo | Descrizione |
|-------|------|-------------|
| `id` | bigint | ID univoco |
| `collection_key` | varchar(100) | Chiave univoca (es: "colori", "architetti") |
| `collection_name` | varchar(255) | Nome visualizzato (es: "Colori", "Architetti") |
| `collection_description` | text | Descrizione collezione |
| `display_type` | varchar(20) | Tipo display: `carousel`, `list`, `grid` |
| `shortcode_tag` | varchar(50) | Tag shortcode personalizzato (opzionale) |
| `css_class` | varchar(255) | Classi CSS personalizzate |
| `is_active` | tinyint(1) | Attivo (1) o disattivo (0) |

#### `wp_carousel_items`
Tabella per gli items di una collezione.

| Campo | Tipo | Descrizione |
|-------|------|-------------|
| `id` | bigint | ID univoco |
| `collection_id` | bigint | ID collezione (FK) |
| `item_title` | varchar(255) | Titolo/etichetta item |
| `item_url` | varchar(500) | URL link item |
| `item_image_url` | varchar(500) | URL immagine (opzionale) |
| `item_description` | text | Descrizione item (opzionale) |
| `category` | varchar(100) | Categoria/gruppo (per raggruppare) |
| `display_order` | int(11) | Ordine visualizzazione |
| `is_active` | tinyint(1) | Attivo (1) o disattivo (0) |

## 🚀 Utilizzo

### Shortcode Base

```php
[carousel collection="colori"]
```

### Shortcode con Parametri

```php
// Mostra solo una categoria
[carousel collection="colori" category="pantone"]

// Limita numero items
[carousel collection="architetti" limit="10"]

// Tipo display personalizzato
[carousel collection="programmi-3d" display="grid"]

// Titolo personalizzato
[carousel collection="colori" title="I Nostri Colori Preferiti"]

// Classi CSS personalizzate
[carousel collection="architetti" css_class="custom-architects"]
```

### Alias Shortcode

```php
// Lista (stile lista HTML)
[list collection="colori"]

// Griglia (stile griglia)
[grid collection="architetti"]
```

## 📝 Esempi

### Esempio 1: Carosello Colori

```php
[carousel collection="colori"]
```

Output: Carosello scrollabile con tutti i colori.

### Esempio 2: Lista Architetti

```php
[list collection="architetti"]
```

Output: Lista HTML con link agli architetti.

### Esempio 3: Griglia Programmi 3D

```php
[grid collection="programmi-3d"]
```

Output: Griglia con immagini e titoli.

### Esempio 4: Categoria Specifica

```php
[carousel collection="colori" category="pantone"]
```

Output: Solo colori Pantone.

## 🔧 Gestione Database

### Creare Collezione

```php
use gik25microdata\Database\CarouselCollections;

$collection_id = CarouselCollections::upsert_collection([
    'collection_key' => 'colori',
    'collection_name' => 'Colori',
    'collection_description' => 'Articoli sui colori',
    'display_type' => 'carousel',
]);
```

### Aggiungere Items

```php
CarouselCollections::upsert_item([
    'collection_id' => $collection_id,
    'item_title' => 'Colore Bianco',
    'item_url' => 'https://www.totaldesign.it/colore-bianco/',
    'item_image_url' => 'https://www.totaldesign.it/wp-content/uploads/bianco.jpg',
    'category' => 'colori-specifici',
    'display_order' => 0,
]);
```

### Migrazione da Hardcoded

```php
// Migra dati da codice hardcoded a database
$items = [
    ['title' => 'Colore Bianco', 'url' => 'https://...', 'image' => '...'],
    ['title' => 'Colore Verde', 'url' => 'https://...', 'image' => '...'],
];

CarouselCollections::migrate_from_hardcoded('colori', $items, 'colori-specifici');
```

## 🔄 Migrazione da Codice Hardcoded

### Passo 1: Estrai Dati

Dai file PHP hardcoded (es. `totaldesign_specific.php`), estrai gli array di items.

### Passo 2: Crea Collezione

```php
$collection_id = CarouselCollections::upsert_collection([
    'collection_key' => 'colori',
    'collection_name' => 'Colori',
    'display_type' => 'carousel',
]);
```

### Passo 3: Migra Items

```php
// Estrai items da codice hardcoded
$items = [
    ['title' => 'Color Tortora', 'url' => 'https://...'],
    // ...
];

CarouselCollections::migrate_from_hardcoded('colori', $items);
```

### Passo 4: Sostituisci Shortcode

Sostituisci:
```php
[link_colori]
```

Con:
```php
[carousel collection="colori"]
```

### Passo 5: Rimuovi Codice Hardcoded

Rimuovi le funzioni handler hardcoded da `totaldesign_specific.php`.

## 🎨 Personalizzazione CSS

Il sistema utilizza CSS esistente da `ColorWidget::get_carousel_css()` per i caroselli.

Per personalizzare:

1. Aggiungi classi CSS personalizzate:
```php
[carousel collection="colori" css_class="custom-colors"]
```

2. Definisci CSS personalizzato nel tema:
```css
.custom-colors .row__inner {
    /* Stili personalizzati */
}
```

## 📋 Checklist Migrazione

- [ ] Creare collezioni nel database
- [ ] Migrare items da codice hardcoded
- [ ] Testare shortcode `[carousel collection="..."]`
- [ ] Sostituire shortcode vecchi nei post
- [ ] Rimuovere funzioni handler hardcoded
- [ ] Verificare che tutto funzioni
- [ ] Testare su staging prima di produzione

## 🔍 Query Utili

### Lista Collezioni

```sql
SELECT * FROM wp_carousel_collections WHERE is_active = 1;
```

### Items di una Collezione

```sql
SELECT * FROM wp_carousel_items 
WHERE collection_id = 1 AND is_active = 1 
ORDER BY display_order;
```

### Items per Categoria

```sql
SELECT * FROM wp_carousel_items 
WHERE collection_id = 1 AND category = 'pantone' AND is_active = 1 
ORDER BY display_order;
```

## 🚨 Troubleshooting

### Shortcode non funziona

1. Verifica che la collezione esista: `SELECT * FROM wp_carousel_collections WHERE collection_key = 'colori'`
2. Verifica che ci siano items: `SELECT * FROM wp_carousel_items WHERE collection_id = ?`
3. Verifica che gli items siano attivi: `is_active = 1`

### Items non visualizzati

1. Verifica `display_order`: items con ordine negativo non vengono visualizzati
2. Verifica `category`: se usi `category="..."`, verifica che la categoria esista
3. Verifica `limit`: se usi `limit="10"`, vengono mostrati solo i primi 10

### CSS non applicato

1. Verifica che `ColorWidget::get_carousel_css()` sia caricato
2. Verifica che il tema non sovrascriva gli stili
3. Aggiungi classi CSS personalizzate se necessario

## 🎯 Best Practices

1. **Usa chiavi descrittive**: `collection_key` deve essere chiaro (es: "colori", non "c1")
2. **Raggruppa per categoria**: Usa `category` per raggruppare items correlati
3. **Ordina con display_order**: Usa `display_order` per controllare l'ordine visualizzazione
4. **Mantieni URL assoluti**: Usa URL completi (https://...) per evitare problemi
5. **Testa prima di deploy**: Migra sempre su staging prima di produzione

## 🔮 Futuro

- [ ] Interfaccia admin per gestire collezioni
- [ ] Import/Export collezioni (JSON/CSV)
- [ ] Preview collezioni nell'editor WordPress
- [ ] Supporto immagini da media library WordPress
- [ ] Cache per performance

