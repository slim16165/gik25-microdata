# Piano di Consolidamento e Miglioramento Plugin WordPress

## Obiettivo
Consolidare e migliorare il plugin WordPress rifattorizzando le classi, riorganizzando la struttura ad albero e astraendo la logica di creazione dei link specifica dei singoli siti, rendendola più generale e riutilizzabile.

## Architettura Nuova

### 1. Sistema Unificato LinkBuilder
**File**: `include/class/ListOfPosts/LinkBuilder.php`

Classe unificata che astrae tutta la logica di creazione link da:
- `linkIfNotSelf()` / `linkIfNotSelf2()` in `chiecosa_specific.php`
- `ListOfPostsHelper::GetLinkWithImage()`
- `ColorWidget::GetLinkWithImageCarousel()`

**Tipi di rendering supportati**:
- `RENDER_STANDARD`: Link semplice con testo
- `RENDER_THUMBNAIL`: Link con thumbnail (ListOfPostsHelper)
- `RENDER_CAROUSEL`: Link per carousel (ColorWidget)

**Metodi principali**:
- `createLink()`: Crea un singolo link con opzioni
- `createLinkList()`: Crea una lista di link
- `createCarouselLink()`: Metodo di convenienza per carousel
- `createThumbnailLink()`: Metodo di convenienza per thumbnail

### 2. Classe Base SiteSpecificHandler
**File**: `include/class/SiteSpecific/SiteSpecificHandler.php`

Classe base astratta per gestire logica comune site-specific.

**Metodi helper**:
- `createThumbnailList()`: Crea lista con thumbnail
- `createCarouselList()`: Crea lista per carousel
- `createSimpleList()`: Crea lista semplice
- `createSection()`: Crea sezione con titolo e lista
- `registerShortcode()`: Helper per registrare shortcode

### 3. Sistema Configurazione Dichiarativa
**File**: `include/class/SiteSpecific/LinkListConfig.php`

Sistema per definire liste di link in modo dichiarativo e riutilizzabile.

**Caratteristiche**:
- Caricamento da array strutturato
- Caricamento da file JSON
- Validazione configurazione
- Separazione dati da logica rendering

### 4. Struttura Site-Specific Riorganizzata

#### Prima (struttura piatta):
```
include/site_specific/
  - chiecosa_specific.php
  - nonsolodiete_specific.php
  - prestitinforma_specific.php
  - superinformati_specific.php
  - totaldesign_specific.php
```

#### Dopo (struttura modulare):
```
include/site_specific/
  - chiecosa_specific.php (legacy, retrocompatibilità)
  - nonsolodiete_specific.php (legacy, retrocompatibilità)
  - ...
  - ChieCosa/
    - ChieCosaHandler.php
  - NonsoloDieti/
    - NonsoloDietiHandler.php
  - SuperInformati/
    - SuperInformatiHandler.php
  - TotalDesign/
    - TotalDesignHandler.php
    - ProgrammaticHub.php (esistente)
```

## Miglioramenti Implementati

### 1. Eliminazione Duplicazione Codice
- ✅ Funzioni `linkIfNotSelf()` e `linkIfNotSelf2()` consolidate in `LinkBuilder`
- ✅ Logica creazione link unificata
- ✅ Gestione staging centralizzata

### 2. Astrazione Logica Link
- ✅ `LinkBuilder` fornisce API unificata
- ✅ Supporto multipli tipi di rendering
- ✅ Configurazione flessibile

### 3. Struttura Modulare
- ✅ Handler dedicati per ogni sito
- ✅ Eredità da classe base comune
- ✅ Separazione responsabilità

### 4. Retrocompatibilità
- ✅ File originali `*_specific.php` mantenuti
- ✅ Shortcode legacy funzionanti
- ✅ Migrazione graduale possibile

## Esempio Utilizzo

### Prima (codice duplicato):
```php
function link_vitamine_handler($atts, $content = null)
{
    $l = new ListOfPostsHelper(false, true, false);
    $links = [
        ['target_url' => "...", 'nome' => "..."]
    ];
    $collection = new Collection();
    foreach ($links as $link) {
        $collection->add(new LinkBase($link['target_url'], $link['nome'], ""));
    }
    $result = "<h3>...</h3><div class='thumbnail-list'>";
    $result .= Html::ul()->class("thumbnail-list")->open();
    $result .= $l->getLinksWithImagesCurrentColumn($collection);
    $result .= Ul::tag()->close();
    $result .= "</div>";
    return $result;
}
```

### Dopo (codice pulito):
```php
public static function link_vitamine_handler($atts, $content = null): string
{
    $links = self::getVitamineLinks();
    
    return self::createThumbnailList($links, [
        'title' => 'Lista delle principali vitamine',
        'list_class' => 'thumbnail-list',
    ]);
}
```

## Prossimi Passi

1. ✅ Creare `LinkBuilder` - **COMPLETATO**
2. ✅ Creare `SiteSpecificHandler` - **COMPLETATO**
3. ✅ Creare `LinkListConfig` - **COMPLETATO**
4. ✅ Rifattorizzare `NonsoloDietiHandler` - **COMPLETATO**
5. ⏳ Rifattorizzare altri handler (ChieCosa, SuperInformati, TotalDesign)
6. ⏳ Migrare completamente da file legacy
7. ⏳ Aggiungere test unitari
8. ⏳ Documentazione API completa

## Benefici

1. **Manutenibilità**: Codice più pulito e organizzato
2. **Riutilizzabilità**: Logica comune estratta e riutilizzabile
3. **Testabilità**: Struttura modulare più facile da testare
4. **Scalabilità**: Facile aggiungere nuovi siti
5. **Consistenza**: API unificata per tutti i tipi di link

## Note Tecniche

- Mantenuta retrocompatibilità completa
- Nessuna breaking change
- Migrazione graduale possibile
- Supporto per configurazioni JSON future
