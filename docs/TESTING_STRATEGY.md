# Strategia Testing

## Stato Attuale

**Implementato:**
- Validazione Sintassi PHP (script bash/PowerShell)
- PHPStan (livello 9), Psalm, PHP CS Fixer
- GitHub Actions (PHP syntax, PHPStan, Psalm, CS Fixer, Security Audit)
- Health Check programmatico
- REST API Testing (script Node.js)
- Carousel Tester (pagina admin)

**Mancante:**
- PHPUnit test unitari
- WordPress Test Suite
- Test Coverage
- Test E2E
- Test Performance

## Piano Implementazione

### Fase 1: Setup PHPUnit (Alta Priorità)

```bash
composer require --dev wp-phpunit/wp-phpunit:^6.5
composer require --dev yoast/phpunit-polyfills:^2.0
```

**Struttura:**
```
tests/
├── Unit/
│   ├── Utility/ (TagHelper, SafeExecution, ServerHelper)
│   ├── Shortcodes/ (ShortcodeBase, GenericCarousel)
│   └── HealthCheck/ (CloudwaysLogParser)
├── Integration/
│   ├── PluginBootstrapTest.php
│   ├── AdminMenuTest.php
│   └── REST/MCPApiTest.php
├── Bootstrap.php
└── phpunit.xml
```

### Fase 2: Test Unitari Core (Alta Priorità)

- Utility classes: TagHelper, SafeExecution, ServerHelper, HtmlHelper
- Shortcode classes: ShortcodeBase, GenericCarousel, ShortcodeRegistry
- HealthCheck: CloudwaysLogParser, HealthChecker

### Fase 3: Test Integrazione (Media Priorità)

- PluginBootstrap, AdminMenu, REST API
- Test con WordPress reale

### Fase 4: Coverage e CI (Media Priorità)

- Code coverage setup
- GitHub Actions coverage reporting
- Target: 70% overall, 80% unit, 60% integration

### Fase 5: Test E2E e Performance (Bassa Priorità)

- Test end-to-end shortcode/widget
- Performance testing
- Codeception, Blackfire

## Comandi

```bash
composer test                    # Esegui tutti i test
vendor/bin/phpunit tests/Unit   # Solo test unitari
vendor/bin/phpunit --coverage-html coverage/  # Con coverage
```

## Priorità

1. **Alta**: Setup PHPUnit, Test Utility classes, Test Shortcode classes base
2. **Media**: Test integrazione, Code coverage setup
3. **Bassa**: Test E2E, Test performance

## Risorse

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [WordPress PHPUnit Tests](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/)
- [WP_Mock](https://github.com/10up/wp_mock)
