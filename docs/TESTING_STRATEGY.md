# Strategia Testing - Revious Microdata Plugin

## Stato Attuale

### ✅ Già Implementato
- **Validazione Sintassi PHP**: Script bash/PowerShell per validare sintassi (`scripts/validate-php-syntax.*`)
- **PHPStan**: Analisi statica livello 9 configurata (`phpstan.neon`)
- **Psalm**: Configurato in `composer.json` (da implementare config file)
- **PHP CS Fixer**: Configurato per code style
- **GitHub Actions**: Workflow per PHP syntax, PHPStan, Psalm, CS Fixer, Security Audit
- **Health Check**: Sistema programmatico per verificare funzionalità plugin
- **REST API Testing**: Script Node.js per testare endpoint MCP (`mcp-server/test-api.js`)
- **Carousel Tester**: Pagina admin per testare caroselli (`CarouselTester.php`)

### ❌ Mancante
- **PHPUnit**: Test unitari non implementati
- **WordPress Test Suite**: Test di integrazione con WordPress
- **Test Coverage**: Nessuna misurazione coverage
- **Test E2E**: Test end-to-end per shortcode/widget
- **Test Performance**: Benchmark e test performance

## Piano di Miglioramento

### Fase 1: Setup PHPUnit e Test Base (Alta Priorità)

#### 1.1 Configurazione PHPUnit
```bash
# Installa WordPress test suite
composer require --dev wp-phpunit/wp-phpunit:^6.5
composer require --dev yoast/phpunit-polyfills:^2.0
```

#### 1.2 Struttura Directory Test
```
tests/
├── Unit/
│   ├── Utility/
│   │   ├── TagHelperTest.php
│   │   ├── SafeExecutionTest.php
│   │   └── ServerHelperTest.php
│   ├── Shortcodes/
│   │   ├── ShortcodeBaseTest.php
│   │   └── GenericCarouselTest.php
│   └── HealthCheck/
│       └── CloudwaysLogParserTest.php
├── Integration/
│   ├── PluginBootstrapTest.php
│   ├── AdminMenuTest.php
│   └── REST/
│       └── MCPApiTest.php
├── Bootstrap.php
└── phpunit.xml
```

#### 1.3 Configurazione phpunit.xml
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    bootstrap="tests/Bootstrap.php"
    backupGlobals="false"
    colors="true"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    verbose="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory>./tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>./tests/Integration</directory>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist>
            <directory>./include</directory>
            <exclude>
                <directory>./include/vendor</directory>
            </exclude>
        </whitelist>
    </filter>
    <php>
        <const name="WP_TESTS_PHPUNIT_POLYFILLS_PATH" value="vendor/yoast/phpunit-polyfills"/>
    </php>
</phpunit>
```

#### 1.4 Test Bootstrap
```php
<?php
// tests/Bootstrap.php
require_once __DIR__ . '/../vendor/autoload.php';

// Carica WordPress test environment
if (!defined('ABSPATH')) {
    define('ABSPATH', '/path/to/wordpress/');
}

// Mock WordPress functions necessarie per test unitari
if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}
// ... altri mock necessari
```

### Fase 2: Test Unitari Core (Alta Priorità)

#### 2.1 Utility Classes
- **TagHelperTest**: Test `find_post_id_from_taxonomy()`, edge cases
- **SafeExecutionTest**: Test error handling, exception catching
- **ServerHelperTest**: Test `getSecondLevelDomainOnly()`, vari domini
- **HtmlHelperTest**: Test sanitizzazione, output HTML

#### 2.2 Shortcode Classes
- **ShortcodeBaseTest**: Test base class, registration, rendering
- **GenericCarouselTest**: Test rendering caroselli, parametri, fallback
- **ShortcodeRegistryTest**: Test registry, registration, lookup

#### 2.3 Health Check
- **CloudwaysLogParserTest**: Test parsing log, estrazione errori, filtri
- **HealthCheckerTest**: Test esecuzione check, report generation

### Fase 3: Test di Integrazione (Media Priorità)

#### 3.1 Plugin Bootstrap
- **PluginBootstrapTest**: Test inizializzazione, hook registration, context detection
- **SafeExecutionIntegrationTest**: Test protezione errori in ambiente WordPress reale

#### 3.2 Admin
- **AdminMenuTest**: Test menu registration, tab navigation, page rendering
- **SettingsPageTest**: Test salvataggio settings, validazione input
- **ShortcodesManagerPageTest**: Test shortcode management, toggle, persistence

#### 3.3 REST API
- **MCPApiTest**: Test endpoint REST, autenticazione, response format
- **HealthCheckApiTest**: Test endpoint health check, error reporting

### Fase 4: Test Coverage e CI (Media Priorità)

#### 4.1 Code Coverage
```bash
# Aggiungi coverage a PHPUnit
composer require --dev phpunit/phpunit-coverage
```

#### 4.2 GitHub Actions Coverage
```yaml
- name: Generate Coverage
  run: vendor/bin/phpunit --coverage-clover=coverage.xml

- name: Upload Coverage
  uses: codecov/codecov-action@v3
  with:
    file: ./coverage.xml
```

#### 4.3 Target Coverage
- **Unit Tests**: 80% coverage classi core (Utility, Shortcodes, HealthCheck)
- **Integration Tests**: 60% coverage classi admin e REST API
- **Overall**: 70% coverage totale

### Fase 5: Test E2E e Performance (Bassa Priorità)

#### 5.1 Test E2E Shortcode
- **Shortcode Rendering Test**: Test rendering shortcode in pagina WordPress reale
- **Widget Test**: Test widget cucine, app-nav, color widget in frontend
- **Carousel Test**: Test caroselli con dati reali, navigazione, responsive

#### 5.2 Test Performance
- **Query Performance**: Test query database, N+1 problems
- **Asset Loading**: Test caricamento condizionale CSS/JS
- **Cache Test**: Test cache, invalidazione, performance

#### 5.3 Strumenti
- **Codeception**: Per test E2E WordPress
- **Blackfire/New Relic**: Per profiling performance
- **WP-CLI**: Per test da command line

## Best Practices

### 1. Test Naming
```php
// ✅ Buono
public function test_find_post_id_from_taxonomy_returns_array()
public function test_safe_execution_catches_exceptions()
public function test_shortcode_renders_with_valid_attributes()

// ❌ Cattivo
public function test1()
public function test_tag_helper()
```

### 2. Test Structure (AAA Pattern)
```php
public function test_example()
{
    // Arrange
    $helper = new TagHelper();
    $taxonomy = 'post_tag';
    $term = 'test-tag';
    
    // Act
    $result = $helper->find_post_id_from_taxonomy($term, $taxonomy);
    
    // Assert
    $this->assertIsArray($result);
    $this->assertNotEmpty($result);
}
```

### 3. Mocking WordPress
```php
// Usa WP_Mock o crea mock manuali
use Mockery;
use WP_Mock;

public function setUp(): void
{
    WP_Mock::setUp();
}

public function tearDown(): void
{
    WP_Mock::tearDown();
    Mockery::close();
}
```

### 4. Test Data
```php
// Usa factories per test data
class PostFactory
{
    public static function create(array $args = []): WP_Post
    {
        // Crea post di test
    }
}
```

### 5. Isolation
- Ogni test deve essere indipendente
- Usa `setUp()` e `tearDown()` per cleanup
- Non dipendere da ordine esecuzione test

## Comandi Utili

```bash
# Esegui tutti i test
composer test

# Esegui solo test unitari
vendor/bin/phpunit tests/Unit

# Esegui con coverage
vendor/bin/phpunit --coverage-html coverage/

# Esegui test specifico
vendor/bin/phpunit tests/Unit/Utility/TagHelperTest.php

# Esegui con verbose
vendor/bin/phpunit --verbose

# Esegui test in parallelo (se supportato)
vendor/bin/phpunit --process-isolation
```

## Priorità Implementazione

1. **Alta Priorità** (Fase 1-2):
   - Setup PHPUnit
   - Test Utility classes (TagHelper, SafeExecution)
   - Test Shortcode classes base
   - Test HealthCheck core

2. **Media Priorità** (Fase 3-4):
   - Test integrazione PluginBootstrap
   - Test Admin pages
   - Test REST API
   - Code coverage setup

3. **Bassa Priorità** (Fase 5):
   - Test E2E
   - Test performance
   - Advanced coverage

## Risorse

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [WordPress PHPUnit Tests](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/)
- [WP_Mock](https://github.com/10up/wp_mock)
- [Codeception for WordPress](https://codeception.com/for/wordpress)

## Note

- I test devono essere veloci (< 1 minuto per suite completa)
- Test devono essere deterministi (stesso risultato ogni volta)
- Test devono testare comportamento, non implementazione
- Mantieni test semplici e leggibili
- Aggiorna test quando cambia codice

