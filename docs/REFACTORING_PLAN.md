# Piano di Consolidamento e Miglioramento Plugin WordPress

## Obiettivo
Consolidare e migliorare il plugin WordPress rifattorizzando le classi, riorganizzando la struttura ad albero e astraendo la logica di creazione dei link specifica dei singoli siti, rendendola più generale e riutilizzabile.

## Problemi Identificati

### 1. Duplicazione della Logica di Creazione Link
- Funzioni `linkIfNotSelf()` e `linkIfNotSelf2()` duplicate in `chiecosa_specific.php`
- Uso diretto di `ListOfPostsHelper::GetLinkWithImage()` in vari file
- Uso di `ColorWidget::GetLinkWithImageCarousel()` in `totaldesign_specific.php`
- Logica di rendering duplicata e non standardizzata

### 2. Struttura Non Organizzata
- File `*_specific.php` contengono logica mista (shortcode, helper, filtri)
- Mancanza di una classe base comune per funzionalità site-specific
- Namespace non completamente organizzati

### 3. Difficoltà di Manutenzione
- Modifiche alla logica di link richiedono aggiornamenti in più file
- Nessun sistema unificato per gestire diversi stili di rendering

## Soluzione Implementata

### 1. Sistema Unificato di Rendering Link

#### Interfaccia `LinkRendererInterface`
Definisce il contratto per tutti i renderer:
- `render(LinkBase $link, array $options)`: Renderizza un singolo link
- `renderCollection(Collection $links, array $options)`: Renderizza una collezione
- `supports(string $option)`: Verifica supporto opzioni

#### Implementazioni
- **StandardLinkRenderer**: Link con thumbnail (default)
- **CarouselLinkRenderer**: Link in formato carousel (per TotalDesign)
- **SimpleLinkRenderer**: Link semplici senza immagini

### 2. LinkBuilder Unificato

Classe `LinkBuilder` che astrae la creazione di link:
- Factory methods: `standard()`, `carousel()`, `simple()`
- Metodi per creare link da array o Collection
- Supporto per multi-colonna
- Opzioni configurabili (removeIfSelf, withImage, nColumns, etc.)

### 3. Classe Base SiteSpecificBase

Classe astratta con metodi helper comuni:
- `linkBuilder()`: Crea builder standard
- `carouselBuilder()`: Crea builder carousel
- `simpleBuilder()`: Crea builder semplice
- `renderList()`: Renderizza lista con titolo
- `renderListWithSections()`: Renderizza lista con sezioni

### 4. Helper Functions per Backward Compatibility

File `helpers.php` con funzioni per compatibilità:
- `linkIfNotSelf()`: Mantiene compatibilità con codice esistente
- `linkIfNotSelf2()`: Versione semplice senza immagini
- `ReplaceTargetUrlIfStaging()`: Helper per URL staging

## Struttura Nuova

```
include/class/
├── ListOfPosts/
│   ├── LinkBuilder.php (NUOVO - Builder unificato)
│   ├── Renderer/ (NUOVO)
│   │   ├── LinkRendererInterface.php
│   │   ├── StandardLinkRenderer.php
│   │   ├── CarouselLinkRenderer.php
│   │   └── SimpleLinkRenderer.php
│   ├── ListOfPostsHelper.php (esistente, può usare LinkBuilder)
│   └── ...
├── SiteSpecific/ (NUOVO)
│   ├── SiteSpecificBase.php (classe base astratta)
│   └── Helper.php (helper functions)
└── ...

include/site_specific/
├── helpers.php (NUOVO - funzioni helper per backward compatibility)
├── chiecosa_specific.php (rifattorizzato)
├── prestitinforma_specific.php (rifattorizzato)
└── ...
```

## Esempi di Utilizzo

### Esempio 1: Link Standard con Immagini
```php
use gik25microdata\ListOfPosts\LinkBuilder;

$builder = LinkBuilder::standard([
    'removeIfSelf' => true,
    'withImage' => true,
    'nColumns' => 2
]);

$links = [
    ['target_url' => 'https://example.com/page1', 'nome' => 'Page 1'],
    ['target_url' => 'https://example.com/page2', 'nome' => 'Page 2'],
];

$html = $builder->createLinksFromArray($links, ['ulClass' => 'thumbnail-list']);
```

### Esempio 2: Link Carousel
```php
use gik25microdata\ListOfPosts\LinkBuilder;

$builder = LinkBuilder::carousel();
$html = $builder->createLink('https://example.com/page', 'Page Title');
```

### Esempio 3: Usando SiteSpecificBase
```php
use gik25microdata\SiteSpecific\SiteSpecificBase;

class MySiteSpecific extends SiteSpecificBase {
    public static function renderMyLinks() {
        $builder = self::linkBuilder(true, true, false, 2);
        $links = [/* ... */];
        return self::renderList($builder, $links, 'My Links');
    }
}
```

### Esempio 4: Backward Compatibility
```php
// Le funzioni esistenti continuano a funzionare
$html = linkIfNotSelf('https://example.com/page', 'Page Title');
$html2 = linkIfNotSelf2('https://example.com/page', 'Page Title');
```

## Migrazione dei File Site_Specific

### File Migrati
- ✅ `prestitinforma_specific.php`: Migrato completamente al nuovo sistema
- ✅ `chiecosa_specific.php`: Aggiornato per usare helpers.php

### File da Migrare
- ⏳ `nonsolodiete_specific.php`: Usa `ListOfPostsHelper` - può essere migrato facilmente
- ⏳ `superinformati_specific.php`: Usa `ListOfPostsHelper` e `printList()` - può essere migrato
- ⏳ `totaldesign_specific.php`: Usa `ColorWidget::GetLinkWithImageCarousel()` - può usare `LinkBuilder::carousel()`

## Vantaggi

1. **Riusabilità**: Logica di creazione link centralizzata e riutilizzabile
2. **Manutenibilità**: Modifiche in un solo punto si propagano a tutti i siti
3. **Flessibilità**: Facile aggiungere nuovi stili di rendering
4. **Testabilità**: Interfaccia chiara facilita i test
5. **Backward Compatibility**: Codice esistente continua a funzionare
6. **Type Safety**: Uso di tipi PHP 7.4+ per maggiore sicurezza

## Prossimi Passi

1. ✅ Creare sistema unificato di rendering
2. ✅ Creare LinkBuilder
3. ✅ Creare SiteSpecificBase
4. ⏳ Migrare tutti i file site_specific
5. ⏳ Aggiornare ListOfPostsHelper per usare LinkBuilder internamente (opzionale)
6. ⏳ Aggiungere test unitari per i nuovi componenti
7. ⏳ Documentare API completa

## Note Tecniche

- **Namespace**: `gik25microdata\ListOfPosts\Renderer\*` per i renderer
- **Namespace**: `gik25microdata\SiteSpecific\*` per le classi site-specific
- **Autoloading**: Aggiornato `composer.json` per includere nuovi namespace
- **Dependencies**: Usa `Illuminate\Support\Collection` e `Yiisoft\Html\*`

## Compatibilità

- ✅ PHP 7.4+
- ✅ WordPress 5.8+
- ✅ Backward compatible con codice esistente
- ✅ Non breaking changes per shortcode esistenti
