# Sistema Health Check - Verifica Funzionalità Plugin

## 🎯 Obiettivo

Sistema programmatico + checklist admin per verificare che dopo un deploy nessuna funzionalità del plugin si sia rotta.

## 📋 Funzionalità

### 1. **Check Automatici Programmatici**

Il sistema verifica automaticamente:

- ✅ **Shortcode Registrati**: Verifica che tutti gli shortcode siano registrati correttamente
- ✅ **REST API Endpoints**: Testa che gli endpoint REST API rispondano (HTTP 200)
- ✅ **AJAX Endpoints**: Verifica che gli hook AJAX siano registrati
- ✅ **File Critici**: Controlla che i file principali esistano
- ✅ **Tabelle Database**: Verifica presenza tabelle (opzionali)
- ✅ **Assets (CSS/JS)**: Verifica che CSS/JS siano accessibili via URL
- ✅ **Classi PHP**: Verifica che le classi siano caricate correttamente

### 2. **Pagina Admin WordPress**

Accesso: **Strumenti → Health Check**

La pagina mostra:
- Riepilogo: totale check, successi, warning, errori
- Dettagli per ogni check con status (✅/⚠️/❌)
- Pulsante "Esegui Health Check" per eseguire check in tempo reale
- Pulsante "Esporta Risultati" per salvare report HTML

### 3. **REST API Endpoint**

Endpoint pubblico per testing esterno:

```
GET /wp-json/gik25/v1/health-check
```

Risposta JSON:
```json
{
  "total": 7,
  "success": 6,
  "warnings": 1,
  "errors": 0,
  "timestamp": "2025-11-07 12:00:00",
  "checks": [
    {
      "name": "Shortcode Registrati",
      "status": "success",
      "message": "Tutti gli shortcode registrati (18)",
      "details": "..."
    },
    ...
  ]
}
```

### 4. **AJAX Endpoint**

Per eseguire check via AJAX (usato dalla pagina admin):

```
POST /wp-admin/admin-ajax.php
Action: gik25_health_check
```

## 🔧 Utilizzo

### Pagina Admin

1. Vai in **WordPress Admin → Strumenti → Health Check**
2. Clicca "🔄 Esegui Health Check"
3. Visualizza i risultati
4. Clicca "📥 Esporta Risultati" per salvare report

### REST API

```bash
# Test rapido
curl https://tuo-sito.it/wp-json/gik25/v1/health-check

# Con jq per formattazione
curl https://tuo-sito.it/wp-json/gik25/v1/health-check | jq
```

### Dopo Deploy

**Checklist manuale**:
1. ✅ Esegui Health Check dalla pagina admin
2. ✅ Verifica che tutti i check siano "success" o al massimo "warning"
3. ✅ Se ci sono errori, controlla i dettagli
4. ✅ Testa manualmente le funzionalità critiche:
   - Shortcode funzionano?
   - REST API risponde?
   - AJAX funziona?
   - CSS/JS caricati?

## 📊 Check Disponibili

### Shortcode Registrati
Verifica che questi shortcode siano registrati:
- `kitchen_finder`, `app_nav`, `carousel`, `list`, `grid`
- `md_quote`, `boxinfo`, `md_progressbar`, ecc.

### REST API Endpoints
Testa questi endpoint:
- `/wp-json/wp-mcp/v1/categories`
- `/wp-json/wp-mcp/v1/posts/recent`
- `/wp-json/wp-mcp/v1/posts/search`

### AJAX Endpoints
Verifica hook registrati:
- `wp_ajax_kitchen_finder_calculate`
- `wp_ajax_nopriv_kitchen_finder_calculate`
- `wp_ajax_kitchen_finder_pdf`
- `wp_ajax_nopriv_kitchen_finder_pdf`

### File Critici
Controlla esistenza:
- `include/class/PluginBootstrap.php`
- `include/class/Shortcodes/kitchenfinder.php`
- `assets/css/kitchen-finder.css`
- `assets/js/kitchen-finder.js`
- ecc.

### Tabelle Database
Verifica tabelle (opzionali):
- `wp_carousel_collections`
- `wp_carousel_items`

### Assets (CSS/JS)
Verifica accessibilità via URL:
- `.../assets/css/kitchen-finder.css`
- `.../assets/js/kitchen-finder.js`
- ecc.

### Classi PHP
Verifica classi caricate:
- `gik25microdata\PluginBootstrap`
- `gik25microdata\Shortcodes\KitchenFinder`
- `gik25microdata\REST\MCPApi`
- ecc.

## 🚨 Troubleshooting

### Check Fallisce

1. **Shortcode non registrati**: Verifica che le classi siano istanziate
2. **REST API non risponde**: Verifica permalink (Impostazioni → Permalink)
3. **File mancanti**: Verifica deploy completo
4. **Classi non caricate**: Verifica autoloader Composer

### Warning vs Errori

- **Warning**: Funzionalità opzionali mancanti (es. tabelle database)
- **Errori**: Funzionalità critiche mancanti (es. shortcode, REST API)

## 📝 Estendere Health Check

Puoi aggiungere nuovi check modificando `HealthChecker::run_all_checks()`:

```php
private static function run_all_checks(): array
{
    $checks = [];
    
    // Check esistenti...
    $checks[] = self::check_shortcodes();
    
    // Nuovo check personalizzato
    $checks[] = self::check_custom_feature();
    
    return $checks;
}
```

## 🎯 Best Practices

1. **Esegui Health Check dopo ogni deploy**
2. **Salva report**: Esporta risultati per riferimento futuro
3. **Monitora warning**: Anche se non sono errori, indicano configurazioni mancanti
4. **Test manuale**: Health check non sostituisce test manuali delle funzionalità

