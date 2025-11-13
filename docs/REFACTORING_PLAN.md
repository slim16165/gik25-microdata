# Piano di Consolidamento e Miglioramento del Plugin WordPress

## Obiettivi

1. **Unificare la logica di creazione link**: Astrazione della logica specifica dei singoli siti in un sistema riutilizzabile
2. **Riorganizzare la struttura**: Migliorare l'organizzazione delle classi e ridurre la duplicazione
3. **Centralizzare la configurazione**: Sistema unificato per la gestione dei siti specifici

## Modifiche Implementate

### 1. Sistema Unificato per la Gestione dei Link

#### Classe `LinkBuilder` (`include/class/ListOfPosts/LinkBuilder.php`)

Nuova classe che astrae tutta la logica di creazione link, sostituendo le funzioni duplicate nei file `*_specific.php`.

**Caratteristiche:**
- Supporto per link semplici, con immagine, e per caroselli
- Gestione automatica dello staging environment
- Configurazione flessibile tramite `LinkConfig`
- Factory methods per preset comuni

**Metodi principali:**
- `buildLink()`: Crea un link con/senza immagine
- `buildSimpleLink()`: Crea un link semplice che non punta a se stesso
- `buildCarouselLink()`: Crea un link per caroselli (compatibile con ColorWidget)
- `buildLinksList()`: Crea una lista di link da array
- `create()`: Factory method per preset

**Esempio d'uso:**
```php
$builder = LinkBuilder::create('carousel');
$html = $builder->buildCarouselLink($url, $nome);
```

### 2. Registry Centralizzato per Siti Specifici

#### Classe `SiteSpecificRegistry` (`include/class/SiteSpecific/SiteSpecificRegistry.php`)

Sistema centralizzato per la gestione della configurazione dei siti specifici.

**Caratteristiche:**
- Mapping dominio -> file configurabile
- Cache per evitare rilevamenti multipli
- API per registrare nuovi domini
- Metodi di utilità per verificare domini registrati

**Metodi principali:**
- `detectCurrentSite()`: Rileva il sito corrente
- `loadSiteSpecificFile()`: Carica il file specifico
- `registerDomain()`: Registra un nuovo dominio
- `getRegisteredDomains()`: Ottiene tutti i domini registrati

### 3. Refactoring dei File Site-Specific

#### `chiecosa_specific.php`
- ✅ Sostituito `linkIfNotSelf()` e `linkIfNotSelf2()` con `LinkBuilder`
- ✅ Refactoring degli handler shortcode per usare `LinkBuilder`
- ✅ Funzioni deprecate mantenute per backward compatibility

#### `totaldesign_specific.php`
- ✅ Sostituito `ColorWidget::GetLinkWithImageCarousel()` con `LinkBuilder::buildCarouselLink()`
- ✅ Refactoring degli handler per usare array e loop invece di chiamate ripetute
- ✅ Codice più pulito e manutenibile

### 4. Aggiornamento PluginBootstrap

- ✅ Integrato `SiteSpecificRegistry` nel metodo `detectCurrentWebsite()`
- ✅ Fallback al metodo precedente per compatibilità
- ✅ Gestione errori migliorata

## Struttura delle Classi

```
include/class/
├── ListOfPosts/
│   ├── LinkBuilder.php          [NUOVO] Builder unificato per link
│   ├── LinkConfig.php           [ESISTENTE]
│   ├── ListOfPostsHelper.php    [ESISTENTE]
│   └── ...
├── SiteSpecific/
│   └── SiteSpecificRegistry.php [NUOVO] Registry per siti specifici
└── ...
```

## Benefici

1. **Riduzione duplicazione**: Logica di creazione link centralizzata
2. **Manutenibilità**: Modifiche ai link in un solo punto
3. **Estensibilità**: Facile aggiungere nuovi tipi di link
4. **Testabilità**: Classi isolate più facili da testare
5. **Consistenza**: Comportamento uniforme tra tutti i siti

## Compatibilità

- ✅ Backward compatibility mantenuta: funzioni deprecate ancora funzionanti
- ✅ Nessuna breaking change per gli shortcode esistenti
- ✅ Template carosello compatibile con ColorWidget esistente

## Prossimi Passi (Opzionali)

1. **Riorganizzare Utility**: Consolidare helper duplicati
2. **Rimuovere funzioni deprecate**: Dopo periodo di transizione
3. **Documentazione**: Aggiungere PHPDoc completo
4. **Test**: Aggiungere unit test per le nuove classi
5. **Refactoring altri file**: Applicare lo stesso pattern a `nonsolodiete_specific.php` e `superinformati_specific.php`

## Note

- Le funzioni `linkIfNotSelf()` e `linkIfNotSelf2()` sono marcate come `@deprecated` ma ancora funzionanti
- Il template carosello è compatibile con `ColorWidget::GetLinkTemplateCarousel()`
- `SiteSpecificRegistry` supporta cache per performance
