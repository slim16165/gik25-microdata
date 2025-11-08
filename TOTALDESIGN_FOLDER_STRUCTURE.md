# Proposta Struttura Folder TotalDesign

## Struttura Attuale

```
include/site_specific/
├── totaldesign_specific.php (235 righe - contiene tutto)
└── Totaldesign/
    └── ProgrammaticHub.php
```

## Problema

Il file `totaldesign_specific.php` è diventato troppo grande e contiene:
- Inizializzazioni (MCPApi, ContextualWidgets, ProgrammaticHub)
- Ottimizzazioni (ConditionalLoadJsCss_Colori)
- 3 handler functions molto lunghe (link_colori, grafica3d, archistar)

## Proposta Struttura

```
include/site_specific/
├── totaldesign_specific.php (file bootstrap semplice - 20 righe)
└── Totaldesign/
    ├── Init.php (inizializzazioni)
    ├── Optimizations.php (conditional loading)
    ├── ProgrammaticHub.php (già presente)
    └── Shortcodes/
        ├── LinkColori.php
        ├── Grafica3D.php
        └── Archistar.php
```

## Dettaglio File

### `totaldesign_specific.php` (bootstrap)
```php
<?php
namespace gik25microdata\site_specific;

if (!defined('ABSPATH')) {
    exit;
}

// Carica modulo TotalDesign
require_once __DIR__ . '/Totaldesign/Init.php';
\gik25microdata\site_specific\Totaldesign\Init::bootstrap();
```

### `Totaldesign/Init.php`
```php
<?php
namespace gik25microdata\site_specific\Totaldesign;

use gik25microdata\site_specific\Totaldesign\Optimizations;
use gik25microdata\site_specific\Totaldesign\Shortcodes\LinkColori;
use gik25microdata\site_specific\Totaldesign\Shortcodes\Grafica3D;
use gik25microdata\site_specific\Totaldesign\Shortcodes\Archistar;
use gik25microdata\Widgets\ContextualWidgets;
use gik25microdata\REST\MCPApi;

class Init {
    public static function bootstrap(): void {
        // Ottimizzazioni
        Optimizations::init();
        
        // Programmatic Hub
        ProgrammaticHub::init();
        
        // REST API MCP
        if (class_exists('\\gik25microdata\\REST\\MCPApi')) {
            MCPApi::init();
            add_filter('wp_mcp_enable_extended_routes', '__return_true');
        }
        
        // Widget contestuali
        if (class_exists('\\gik25microdata\\Widgets\\ContextualWidgets')) {
            ContextualWidgets::init();
        }
        
        // Shortcode
        LinkColori::register();
        Grafica3D::register();
        Archistar::register();
    }
}
```

### `Totaldesign/Optimizations.php`
```php
<?php
namespace gik25microdata\site_specific\Totaldesign;

use gik25microdata\ColorWidget;
use gik25microdata\TagHelper;

class Optimizations {
    public static function init(): void {
        add_action('wp_head', [self::class, 'conditionalLoadJsCss_Colori']);
    }
    
    public static function conditionalLoadJsCss_Colori(): void {
        global $post;
        $postConTagColori = TagHelper::find_post_id_from_taxonomy("colori", 'post_tag');
        if (in_array($post->ID, $postConTagColori)) {
            ColorWidget::carousel_js();
        }
    }
}
```

### `Totaldesign/Shortcodes/LinkColori.php`
```php
<?php
namespace gik25microdata\site_specific\Totaldesign\Shortcodes;

use gik25microdata\ColorWidget;

class LinkColori {
    public static function register(): void {
        add_shortcode('link_colori', [self::class, 'handle']);
    }
    
    public static function handle($atts, $content = null): string {
        // Sposta qui la funzione link_colori_handler()
    }
}
```

### `Totaldesign/Shortcodes/Grafica3D.php`
```php
<?php
namespace gik25microdata\site_specific\Totaldesign\Shortcodes;

use gik25microdata\ColorWidget;

class Grafica3D {
    public static function register(): void {
        add_shortcode('grafica3d', [self::class, 'handle']);
    }
    
    public static function handle($atts, $content = null): string {
        // Sposta qui la funzione grafica3d_handler()
    }
}
```

### `Totaldesign/Shortcodes/Archistar.php`
```php
<?php
namespace gik25microdata\site_specific\Totaldesign\Shortcodes;

use gik25microdata\ColorWidget;

class Archistar {
    public static function register(): void {
        add_shortcode('archistar', [self::class, 'handle']);
    }
    
    public static function handle($atts, $content = null): string {
        // Sposta qui la funzione archistars_handler()
    }
}
```

## Vantaggi

✅ **Separazione responsabilità**: ogni file ha una responsabilità chiara  
✅ **Manutenibilità**: più facile trovare e modificare codice  
✅ **Scalabilità**: facile aggiungere nuovi shortcode/widget  
✅ **Organizzazione**: struttura chiara e logica  
✅ **Namespace**: namespace puliti e organizzati  

## Migrazione

1. Creare folder `Totaldesign/Shortcodes/`
2. Spostare handler functions in classi separate
3. Creare `Init.php` e `Optimizations.php`
4. Semplificare `totaldesign_specific.php` a bootstrap
5. Testare che tutto funzioni

## Note

- I widget Kitchen Finder e App Navigator rimangono in `include/class/Shortcodes/` perché sono generici
- ProgrammaticHub rimane in `Totaldesign/` perché è specifico TotalDesign
- La struttura è compatibile con autoloader Composer se necessario

