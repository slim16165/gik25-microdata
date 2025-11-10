# Piano di Consolidamento e Miglioramento del Plugin

## Obiettivi

1. **Astrarre la logica di creazione link** - Eliminare duplicazione di codice
2. **Centralizzare configurazioni site-specific** - Sistema più gestibile
3. **Migliorare manutenibilità** - Codice più pulito e riutilizzabile
4. **Riorganizzare struttura** - Organizzazione più logica

## Modifiche Implementate

### 1. Sistema Unificato di Generazione Link

#### LinkGenerator (`include/class/LinkGenerator/LinkGenerator.php`)
Classe unificata per generare link in diversi formati:
- `generateStandardLink()` - Link standard senza immagine
- `generateLinkWithThumbnail()` - Link con thumbnail
- `generateCarouselLink()` - Link per carousel (stile TotalDesign)
- `generateSimpleLink()` - Link semplice senza controlli
- `generateLinkIfNotSelf()` - Link con controllo post corrente

**Vantaggi:**
- Elimina duplicazione di `linkIfNotSelf()` e `linkIfNotSelf2()`
- API unificata e consistente
- Facile da testare e mantenere

#### LinkCollectionBuilder (`include/class/LinkGenerator/LinkCollectionBuilder.php`)
Builder pattern per semplificare la creazione di liste di link:
```php
LinkCollectionBuilder::create()
    ->addLinks($links)
    ->withImage(true)
    ->removeIfSelf(true)
    ->columns(2)
    ->ulClass('thumbnail-list')
    ->buildWithTitle('Titolo', 'container-class');
```

**Vantaggi:**
- API fluente e leggibile
- Configurazione centralizzata
- Supporto per colonne multiple

### 2. Sistema di Registry Site-Specific

#### SiteSpecificRegistry (`include/class/SiteSpecific/SiteSpecificRegistry.php`)
Registry centralizzato per gestire configurazioni site-specific:
- Registrazione configurazioni per dominio
- Rilevamento automatico dominio corrente
- Caricamento file specifici

#### SiteConfig (`include/class/SiteSpecific/SiteConfig.php`)
Classe di configurazione per ogni sito:
- Informazioni dominio
- Shortcode registrati
- Filtri e azioni WordPress

**Vantaggi:**
- Configurazione centralizzata
- Facile aggiungere nuovi siti
- Separazione logica di business

### 3. Rifattorizzazione File Site-Specific

#### chiecosa_specific.php
- ✅ Sostituito `linkIfNotSelf()` con `LinkGenerator::generateLinkIfNotSelf()`
- ✅ Sostituito `linkIfNotSelf2()` con `LinkGenerator::generateLinkIfNotSelf()`
- ✅ Usato `LinkCollectionBuilder` per liste di link
- ✅ Codice più pulito e manutenibile

#### nonsolodiete_specific.php
- ✅ Usato `LinkCollectionBuilder` per `link_vitamine_handler()`
- ✅ Usato `LinkCollectionBuilder` per `link_diete_handler()` con colonne multiple

#### superinformati_specific.php
- ✅ Aggiornato `printList()` per usare `LinkCollectionBuilder` internamente
- ✅ Mantenuta compatibilità con codice esistente

### 4. Aggiornamento PluginBootstrap

- ✅ Aggiunto supporto per `SiteSpecificRegistry`
- ✅ Fallback al sistema legacy per retrocompatibilità
- ✅ Aggiunto supporto per più domini (chiecosa.it, prestitiinforma.it)

## Struttura Directory Migliorata

```
include/class/
├── LinkGenerator/          # Nuovo: Sistema unificato link
│   ├── LinkGenerator.php
│   └── LinkCollectionBuilder.php
├── SiteSpecific/           # Nuovo: Registry site-specific
│   ├── SiteSpecificRegistry.php
│   └── SiteConfig.php
├── ListOfPosts/            # Esistente: Mantenuto per compatibilità
├── Shortcodes/            # Esistente
└── ...
```

## Migrazione Codice Esistente

### Prima (Duplicato)
```php
function linkIfNotSelf($target_url, $nome, $removeIfSelf = true) {
    global $current_post;
    $current_permalink = get_permalink($current_post->ID);
    // ... 30+ righe di codice duplicato
}
```

### Dopo (Unificato)
```php
use gik25microdata\LinkGenerator\LinkGenerator;

$link = LinkGenerator::generateLinkIfNotSelf($url, $nome);
```

### Prima (Lista Manuale)
```php
$l = new ListOfPostsHelper(false, true, false);
$collection = new Collection();
foreach ($links as $link) {
    $collection->add(new LinkBase($link['target_url'], $link['nome'], ""));
}
$result = Html::ul()->class("thumbnail-list")->open();
$result .= $l->getLinksWithImagesCurrentColumn($collection);
// ...
```

### Dopo (Builder Pattern)
```php
use gik25microdata\LinkGenerator\LinkCollectionBuilder;

return LinkCollectionBuilder::create()
    ->addLinks($links)
    ->withImage(true)
    ->ulClass('thumbnail-list')
    ->build();
```

## Compatibilità

- ✅ **Retrocompatibilità**: Le classi esistenti (`ListOfPostsHelper`, `ColorWidget`) continuano a funzionare
- ✅ **Gradual Migration**: I file site-specific possono essere migrati gradualmente
- ✅ **Fallback**: `PluginBootstrap` supporta sia il nuovo sistema che quello legacy

## Prossimi Passi (Opzionali)

1. **Riorganizzazione Directory**: Spostare classi correlate in sottodirectory
2. **Documentazione API**: Aggiungere PHPDoc completo
3. **Test Unitari**: Creare test per `LinkGenerator` e `LinkCollectionBuilder`
4. **Migrazione Completa**: Migrare tutti i file site-specific al nuovo sistema
5. **Configurazione Centralizzata**: Usare `SiteSpecificRegistry` per tutti i siti

## Note

- Le funzioni deprecate (`linkIfNotSelf`, `linkIfNotSelf2`) sono state commentate ma non rimosse per compatibilità
- Il sistema è progettato per essere estensibile e facilmente manutenibile
- Tutte le modifiche sono backward-compatible
