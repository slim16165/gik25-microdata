# Guida all'Uso del Sistema LinkGenerator

## LinkGenerator - Generazione Link Singoli

### Link Standard (senza immagine)
```php
use gik25microdata\LinkGenerator\LinkGenerator;

$link = LinkGenerator::generateStandardLink(
    'https://example.com/post/',
    'Titolo Post',
    'Commento opzionale',
    true // removeIfSelf
);
// Output: <li><a href="...">Titolo Post</a> (Commento opzionale)</li>
```

### Link con Thumbnail
```php
$link = LinkGenerator::generateLinkWithThumbnail(
    'https://example.com/post/',
    'Titolo Post',
    'Commento',
    true // removeIfSelf
);
// Output: <li><a href="..."><div class="li-img">...</div><div class="li-text">...</div></a></li>
```

### Link Carousel (stile TotalDesign)
```php
$link = LinkGenerator::generateCarouselLink(
    'https://example.com/post/',
    'Titolo Post'
);
// Output: <div class="tile">...</div>
```

### Link Semplice (solo testo)
```php
$link = LinkGenerator::generateSimpleLink(
    'https://example.com/post/',
    'Titolo Post'
);
// Output: <a href="...">Titolo Post</a>
```

### Link con Controllo Post Corrente
```php
$link = LinkGenerator::generateLinkIfNotSelf(
    'https://example.com/post/',
    'Titolo Post'
);
// Se punta al post corrente: "Titolo Post" (solo testo)
// Altrimenti: <a href="...">Titolo Post</a>
```

## LinkCollectionBuilder - Liste di Link

### Lista Base
```php
use gik25microdata\LinkGenerator\LinkCollectionBuilder;

$links = [
    ['target_url' => 'https://example.com/post1/', 'nome' => 'Post 1'],
    ['target_url' => 'https://example.com/post2/', 'nome' => 'Post 2'],
    ['target_url' => 'https://example.com/post3/', 'nome' => 'Post 3'],
];

$html = LinkCollectionBuilder::create()
    ->addLinks($links)
    ->withImage(true)
    ->removeIfSelf(true)
    ->build();
```

### Lista con Titolo
```php
$html = LinkCollectionBuilder::create()
    ->addLinks($links)
    ->withImage(true)
    ->ulClass('thumbnail-list')
    ->buildWithTitle('Lista dei Post', 'container-class');
```

### Lista Multi-Colonna
```php
$html = LinkCollectionBuilder::create()
    ->addLinks($links)
    ->withImage(true)
    ->columns(2) // 2 colonne
    ->ulClass('thumbnail-list')
    ->build();
```

### Aggiunta Link Singoli
```php
$builder = LinkCollectionBuilder::create()
    ->addLink('https://example.com/post1/', 'Post 1', 'Commento')
    ->addLink('https://example.com/post2/', 'Post 2')
    ->withImage(true)
    ->build();
```

### Configurazioni Avanzate
```php
$html = LinkCollectionBuilder::create()
    ->addLinks($links)
    ->withImage(true)           // Include immagini
    ->removeIfSelf(true)        // Rimuove link al post corrente
    ->columns(3)                // 3 colonne
    ->ulClass('custom-class')   // Classe CSS personalizzata
    ->build();
```

## Esempi Pratici

### Shortcode Handler Semplice
```php
function my_shortcode_handler($atts, $content = null) {
    $links = [
        ['target_url' => 'https://example.com/post1/', 'nome' => 'Post 1'],
        ['target_url' => 'https://example.com/post2/', 'nome' => 'Post 2'],
    ];
    
    return LinkCollectionBuilder::create()
        ->addLinks($links)
        ->withImage(true)
        ->buildWithTitle('I Miei Post', 'thumbnail-list');
}
```

### Shortcode con Coppie di Link
```php
function coppie_handler($atts, $content = null) {
    $result = "<ul>";
    
    $coppie = [
        ['url1' => 'https://example.com/persona1/', 'nome1' => 'Persona 1',
         'url2' => 'https://example.com/persona2/', 'nome2' => 'Persona 2'],
    ];
    
    foreach ($coppie as $coppia) {
        $result .= "<li>";
        $result .= LinkGenerator::generateLinkIfNotSelf($coppia['url1'], $coppia['nome1']);
        $result .= " e ";
        $result .= LinkGenerator::generateLinkIfNotSelf($coppia['url2'], $coppia['nome2']);
        $result .= "</li>";
    }
    
    $result .= "</ul>";
    return $result;
}
```

### Migrazione da Codice Legacy

#### Prima
```php
function old_handler($atts, $content = null) {
    $l = new ListOfPostsHelper(false, true, false);
    $collection = new Collection();
    
    foreach ($links as $link) {
        $collection->add(new LinkBase($link['target_url'], $link['nome'], ""));
    }
    
    $result = Html::ul()->class("thumbnail-list")->open();
    $result .= $l->getLinksWithImagesCurrentColumn($collection);
    $result .= Ul::tag()->close();
    return $result;
}
```

#### Dopo
```php
function new_handler($atts, $content = null) {
    return LinkCollectionBuilder::create()
        ->addLinks($links)
        ->withImage(true)
        ->ulClass('thumbnail-list')
        ->build();
}
```

## Best Practices

1. **Usa LinkCollectionBuilder per liste**: Più pulito e manutenibile
2. **Usa LinkGenerator per link singoli**: API semplice e diretta
3. **Configura sempre removeIfSelf**: Evita link circolari
4. **Usa buildWithTitle()**: Include automaticamente titolo e contenitore
5. **Mantieni consistenza**: Usa le stesse classi CSS per liste simili

## Compatibilità

- ✅ Funziona con codice esistente
- ✅ Supporta tutti i formati esistenti
- ✅ Retrocompatibile con `ListOfPostsHelper`
- ✅ Supporta `ColorWidget` per carousel
