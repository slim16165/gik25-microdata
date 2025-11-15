# Porting PHP a .NET - Studio e Proposta

**Data**: Gennaio 2025  
**Versione**: 1.0.0  
**Scopo**: Analisi e proposta per porting sistema Internal Links da PHP a .NET

---

## 1. Analisi Componenti PHP da Portare

### 1.1 Core Classes

**PHP**:
- `InternalLinksManager.php` - Singleton manager
- `LinkProcessor.php` - Processamento link
- `LinkAnalyzer.php` - Analisi link

**Equivalente .NET**:
- `InternalLinksManager.cs` - Singleton pattern (C#)
- `LinkProcessor.cs` - Classe statica o service
- `LinkAnalyzer.cs` - Service con dependency injection

### 1.2 Autolinks Engine

**PHP**:
- `AutolinkEngine.php` - Engine principale
- `KeywordMatcher.php` - Matching keyword
- `ContextMatcher.php` - Context matching
- `AutolinkRule.php` - Modello regola

**Equivalente .NET**:
- `AutolinkEngine.cs` - Service class
- `KeywordMatcher.cs` - Service con regex/string matching
- `ContextMatcher.cs` - Service
- `AutolinkRule.cs` - POCO class

### 1.3 Suggestions Engine

**PHP**:
- `SuggestionEngine.php` - Engine suggerimenti
- `SemanticAnalyzer.php` - Analisi semantica (Jaccard + Cosine)
- `PhraseExtractor.php` - Estrazione frasi
- `SuggestionRanker.php` - Ranking

**Equivalente .NET**:
- `SuggestionEngine.cs` - Service
- `SemanticAnalyzer.cs` - Service con algoritmi similarity
- `PhraseExtractor.cs` - Service con regex/tokenization
- `SuggestionRanker.cs` - Service

### 1.4 Reports & Monitoring

**PHP**:
- `JuiceCalculator.php` - Calcolo juice
- `LinkStats.php` - Statistiche
- `ReportGenerator.php` - Generazione report
- `ClickTracker.php` - Click tracking
- `HttpStatusChecker.php` - HTTP status check

**Equivalente .NET**:
- `JuiceCalculator.cs` - Service
- `LinkStats.cs` - Service
- `ReportGenerator.cs` - Service
- `ClickTracker.cs` - Service
- `HttpStatusChecker.cs` - Service con `HttpClient`

### 1.5 Utils

**PHP**:
- `Stemmer.php` - Stemming multi-lingua (wamania/php-stemmer)
- `LanguageSupport.php` - Supporto lingue
- `LinkValidator.php` - Validazione link

**Equivalente .NET**:
- `Stemmer.cs` - Portare a libreria .NET (Snowball.NET o simile)
- `LanguageSupport.cs` - Service
- `LinkValidator.cs` - Service con `Uri` class

---

## 2. Strategia di Porting

### 2.1 Architettura .NET

**Framework Consigliato**: .NET 8.0 (LTS)

**Pattern Architetturali**:
- **Dependency Injection**: Usare `Microsoft.Extensions.DependencyInjection`
- **Repository Pattern**: Per accesso database
- **Service Layer**: Per business logic
- **DTO/POCO**: Per modelli dati

**Struttura Progetto**:
```
InternalLinks.NET/
├── InternalLinks.Core/          # Business logic
│   ├── Services/
│   │   ├── AutolinkEngine.cs
│   │   ├── SuggestionEngine.cs
│   │   ├── JuiceCalculator.cs
│   │   └── ...
│   ├── Models/
│   │   ├── AutolinkRule.cs
│   │   ├── Link.cs
│   │   └── ...
│   └── Utils/
│       ├── Stemmer.cs
│       └── ...
├── InternalLinks.Data/          # Data access
│   ├── Repositories/
│   ├── Entities/
│   └── DbContext.cs
├── InternalLinks.API/           # REST API (ASP.NET Core)
│   ├── Controllers/
│   └── Program.cs
└── InternalLinks.Tests/         # Unit tests
```

### 2.2 Database Access

**PHP**: WordPress `$wpdb` global

**Equivalente .NET**:
- **Entity Framework Core**: Per ORM
- **Dapper**: Per query raw (più performante)
- **MySQL Connector**: Per MySQL/MariaDB

**Esempio**:
```csharp
public class InternalLinksDbContext : DbContext
{
    public DbSet<AutolinkRule> Autolinks { get; set; }
    public DbSet<Link> Links { get; set; }
    // ...
}
```

### 2.3 Stemming

**PHP**: `wamania/php-stemmer` (Snowball algorithm)

**Equivalente .NET**:
- **Snowball.NET**: Porting ufficiale Snowball
- **Lucene.NET**: Include stemming
- **Custom Implementation**: Portare algoritmi Snowball

**Esempio**:
```csharp
using Snowball;

var stemmer = new ItalianStemmer();
string stemmed = stemmer.Stem("parole");
```

### 2.4 HTTP Requests

**PHP**: `wp_remote_request()`

**Equivalente .NET**:
- **HttpClient**: Standard .NET
- **IHttpClientFactory**: Per dependency injection

**Esempio**:
```csharp
public class HttpStatusChecker
{
    private readonly HttpClient _httpClient;
    
    public async Task<HttpStatusResult> CheckStatusAsync(string url)
    {
        var response = await _httpClient.HeadAsync(url);
        return new HttpStatusResult
        {
            StatusCode = (int)response.StatusCode,
            CheckedAt = DateTime.UtcNow
        };
    }
}
```

### 2.5 Regex e String Processing

**PHP**: `preg_match`, `preg_replace`

**Equivalente .NET**:
- **System.Text.RegularExpressions**: `Regex` class
- **System.Linq**: Per manipolazione array/liste

**Esempio**:
```csharp
// PHP: preg_match_all('/\b\w+\b/u', $text, $matches)
var matches = Regex.Matches(text, @"\b\w+\b", RegexOptions.IgnoreCase);
var words = matches.Cast<Match>().Select(m => m.Value).ToArray();
```

---

## 3. Mapping PHP -> .NET

### 3.1 Tipi Dati

| PHP | .NET |
|-----|------|
| `array` | `List<T>`, `Dictionary<K,V>` |
| `string` | `string` |
| `int` | `int` |
| `float` | `double` |
| `bool` | `bool` |
| `null` | `null` |
| `object` | `class` |

### 3.2 Funzioni WordPress

| PHP WordPress | .NET Equivalente |
|---------------|------------------|
| `get_post_meta()` | Repository pattern + EF Core |
| `update_post_meta()` | Repository pattern + EF Core |
| `get_option()` | Configuration/Options pattern |
| `update_option()` | Configuration/Options pattern |
| `wp_remote_request()` | `HttpClient` |
| `wp_send_json_success()` | `Ok()` in ASP.NET Core |
| `wp_send_json_error()` | `BadRequest()` in ASP.NET Core |

### 3.3 Database

| PHP | .NET |
|-----|------|
| `$wpdb->get_results()` | `DbContext.Set<T>().ToList()` |
| `$wpdb->get_var()` | `DbContext.Set<T>().FirstOrDefault()` |
| `$wpdb->insert()` | `DbContext.Set<T>().Add()` + `SaveChanges()` |
| `$wpdb->update()` | `DbContext.Set<T>().Update()` + `SaveChanges()` |
| `$wpdb->delete()` | `DbContext.Set<T>().Remove()` + `SaveChanges()` |

---

## 4. Librerie .NET Consigliate

### 4.1 Core

- **Microsoft.Extensions.DependencyInjection**: DI container
- **Microsoft.Extensions.Logging**: Logging
- **Microsoft.Extensions.Configuration**: Configuration
- **Microsoft.Extensions.Caching.Memory**: Caching

### 4.2 Database

- **Microsoft.EntityFrameworkCore**: ORM
- **Pomelo.EntityFrameworkCore.MySql**: MySQL provider
- **Dapper**: Micro-ORM per query raw

### 4.3 Text Processing

- **Snowball.NET**: Stemming (se disponibile)
- **Lucene.NET**: Full-text search e stemming
- **System.Text.RegularExpressions**: Regex

### 4.4 HTTP & API

- **Microsoft.AspNetCore.Mvc**: ASP.NET Core MVC
- **Microsoft.AspNetCore.Http**: HTTP context
- **System.Net.Http**: HttpClient

### 4.5 Utilities

- **Newtonsoft.Json**: JSON serialization
- **AutoMapper**: Object mapping
- **FluentValidation**: Validation

---

## 5. Esempio Porting: AutolinkEngine

### PHP Originale
```php
class AutolinkEngine
{
    public function applyAutolinks($content, $post_id, $rules = null)
    {
        if ($rules === null) {
            $rules = $this->loadRules($post_id);
        }
        // ... processing
        return $content;
    }
    
    public function loadRules($post_id = 0)
    {
        global $wpdb;
        $results = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}gik25_il_autolinks WHERE enabled = 1",
            ARRAY_A
        );
        // ...
    }
}
```

### .NET Equivalente
```csharp
public class AutolinkEngine
{
    private readonly IAutolinkRepository _repository;
    private readonly ILogger<AutolinkEngine> _logger;
    
    public AutolinkEngine(
        IAutolinkRepository repository,
        ILogger<AutolinkEngine> logger)
    {
        _repository = repository;
        _logger = logger;
    }
    
    public async Task<string> ApplyAutolinksAsync(
        string content, 
        int postId, 
        IEnumerable<AutolinkRule> rules = null)
    {
        if (rules == null)
        {
            rules = await LoadRulesAsync(postId);
        }
        // ... processing
        return content;
    }
    
    private async Task<IEnumerable<AutolinkRule>> LoadRulesAsync(int postId)
    {
        return await _repository.GetEnabledRulesAsync();
    }
}
```

---

## 6. Esempio Porting: JuiceCalculator

### PHP Originale
```php
public function calculateJuice($post_id, $link_position)
{
    $seo_power = get_post_meta($post_id, '_gik25_il_seo_power', true);
    $total_links = $this->getNumberOfLinks($post_id);
    $juice_per_link = $total_links > 0 ? $seo_power / $total_links : $seo_power;
    // ...
    return ['absolute' => $final_juice, 'relative' => $relative_juice];
}
```

### .NET Equivalente
```csharp
public class JuiceCalculator
{
    private readonly IPostMetaRepository _metaRepository;
    private readonly ILinkRepository _linkRepository;
    
    public async Task<JuiceResult> CalculateJuiceAsync(
        int postId, 
        int linkPosition)
    {
        var seoPower = await _metaRepository.GetMetaAsync(
            postId, 
            "_gik25_il_seo_power", 
            defaultValue: 100);
        
        var totalLinks = await _linkRepository.GetLinkCountAsync(postId);
        var juicePerLink = totalLinks > 0 
            ? seoPower / totalLinks 
            : seoPower;
        
        // ...
        return new JuiceResult
        {
            Absolute = finalJuice,
            Relative = relativeJuice
        };
    }
}
```

---

## 7. REST API Porting

### PHP WordPress REST API
```php
register_rest_route('gik25-il/v1', '/autolinks', [
    'methods' => 'GET',
    'callback' => [$this, 'getAutolinks'],
]);
```

### .NET ASP.NET Core
```csharp
[ApiController]
[Route("api/v1/[controller]")]
public class AutolinksController : ControllerBase
{
    private readonly IAutolinkService _service;
    
    [HttpGet]
    public async Task<ActionResult<IEnumerable<AutolinkDto>>> GetAutolinks()
    {
        var autolinks = await _service.GetAllAsync();
        return Ok(autolinks);
    }
}
```

---

## 8. Considerazioni Performance

### 8.1 Caching

**PHP**: WordPress transients, object cache

**Equivalente .NET**:
- **IMemoryCache**: In-memory caching
- **IDistributedCache**: Redis/SQL Server cache
- **Response Caching**: ASP.NET Core response cache

### 8.2 Async/Await

**PHP**: Sincrono (con estensioni async opzionali)

**Equivalente .NET**:
- **async/await**: Nativo in .NET
- **Task<T>**: Per operazioni asincrone
- **IAsyncEnumerable<T>**: Per streaming

### 8.3 Database

**PHP**: Query sincrone

**Equivalente .NET**:
- **EF Core async**: `ToListAsync()`, `FirstOrDefaultAsync()`
- **Dapper async**: `QueryAsync<T>()`

---

## 9. Testing

### PHP
- PHPUnit

### .NET
- **xUnit**: Framework testing
- **Moq**: Mocking
- **FluentAssertions**: Assertions fluent
- **Microsoft.AspNetCore.Mvc.Testing**: Integration tests

---

## 10. Deployment

### PHP
- WordPress plugin directory
- Composer per dipendenze

### .NET
- **Self-contained deployment**: Include runtime
- **Framework-dependent**: Richiede .NET runtime
- **Docker**: Containerizzazione
- **NuGet**: Gestione pacchetti

---

## 11. Prossimi Step

1. **Setup Progetto .NET**: Creare solution e progetti
2. **Porting Database Schema**: Entity Framework migrations
3. **Porting Core Classes**: Una classe alla volta
4. **Porting Utils**: Stemmer, validators
5. **Porting Services**: Engines, calculators
6. **Porting API**: REST endpoints
7. **Testing**: Unit e integration tests
8. **Documentation**: API docs con Swagger

---

## 12. Note Importanti

- **WordPress Integration**: Il sistema .NET dovrà comunicare con WordPress via REST API o database diretto
- **Backward Compatibility**: Mantenere compatibilità con dati esistenti
- **Performance**: .NET generalmente più performante di PHP
- **Type Safety**: C# offre type safety migliore di PHP
- **Async**: .NET nativamente asincrono, migliora throughput

---

**Status**: ✅ Studio Completo - Pronto per Implementazione

