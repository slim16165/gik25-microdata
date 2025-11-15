# Guida Sviluppatore C# - Sistema Ibrido WordPress/C#

**Data**: Gennaio 2025  
**Versione**: 1.0  
**Target**: Sviluppatori C# che implementano la parte offline del sistema ibrido

---

## 1. Panoramica

### 1.1 Ruolo C# nel Sistema Ibrido

Il componente C# funziona come **hub offline** che:
- Genera embedding per contenuti WordPress usando TextProcessingSuite (BERT)
- Esegue analisi NLP avanzate (qualità testo, SEO, pattern)
- Interroga Wikidata con query SPARQL complesse (WikifySmart)
- Comunica con WordPress via MCP (Model Context Protocol)
- Gestisce database vettoriali locali (Qdrant) per analisi offline

**Importante**: WordPress funziona **indipendentemente** da C#. C# migliora le funzionalità quando disponibile, ma non è obbligatorio.

### 1.2 Architettura C#

```
SeozoomContainer (Consolidato)
├── .NET 8.0 (migrazione da 4.8)
├── WPF per UI
├── SeozoomContainer.MCPClient (NUOVO)
│   ├── Comunicazione con WordPress MCP Server
│   ├── Chiamate tool MCP
│   └── Gestione autenticazione/errori
├── TextProcessingSuite (libreria)
│   ├── BERT embedding generation
│   ├── Semantic comparison
│   └── NLP analysis
├── WikifySmart (modulo)
│   ├── SPARQL queries
│   ├── Wikidata API client
│   └── RDF processing
└── Vector DB Client
    ├── Qdrant client
    └── Local embedding storage
```

### 1.3 Comunicazione MCP

**Flusso**:
```
C# MCP Client → HTTP Request → WordPress MCP Server (Node.js) → WordPress REST API → Database
```

**Endpoint WordPress MCP Server**:
- Locale: `http://localhost:3000` (per sviluppo)
- Remoto: `https://www.totaldesign.it:3000` (produzione Cloudways)

---

## 2. Setup Progetto

### 2.1 Prerequisiti

- **.NET 8.0 SDK** (migrazione da .NET Framework 4.8)
- **Visual Studio 2022** o **JetBrains Rider**
- **NuGet Packages**:
  - `HttpClient` (già incluso in .NET 8.0)
  - `Newtonsoft.Json` o `System.Text.Json`
  - `Qdrant.Client` (per database vettoriale)
  - Librerie TextProcessingSuite (BERT, NLP)

### 2.2 Struttura Progetti

```
SeozoomContainer/
├── SeoZoomReader/                    # Progetto principale WPF
├── SeozoomContainer.MCPClient/       # NUOVO: Client MCP
│   ├── MCPClient.cs                  # Classe principale
│   ├── Models/                        # Modelli request/response
│   │   ├── MCPRequest.cs
│   │   ├── MCPResponse.cs
│   │   └── ToolResult.cs
│   ├── Tools/                         # Implementazione tool
│   │   ├── EmbeddingTool.cs
│   │   ├── SemanticSearchTool.cs
│   │   ├── WikidataTool.cs
│   │   └── AnalysisTool.cs
│   └── Exceptions/
│       ├── MCPException.cs
│       └── MCPTimeoutException.cs
├── TextProcessingSuite/                # Libreria embedding/NLP
├── WikifySmart/                       # Modulo Wikidata
└── VectorDB.Client/                   # Client Qdrant
```

### 2.3 Creazione Progetto MCP Client

**Nuovo progetto .NET 8.0 Class Library**:

```bash
dotnet new classlib -n SeozoomContainer.MCPClient -f net8.0
cd SeozoomContainer.MCPClient
dotnet add package Newtonsoft.Json
dotnet add package Qdrant.Client
```

**Aggiungi riferimento a TextProcessingSuite**:
```bash
dotnet add reference ../TextProcessingSuite/TextProcessingSuite.csproj
```

---

## 3. Implementazione MCP Client

### 3.1 Classe Base MCPClient

**File**: `SeozoomContainer.MCPClient/MCPClient.cs`

```csharp
using System;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;

namespace SeozoomContainer.MCPClient
{
    public class MCPClient
    {
        private readonly HttpClient _httpClient;
        private readonly string _baseUrl;
        private readonly string _apiKey;
        private readonly int _timeoutSeconds;

        public MCPClient(string baseUrl, string apiKey = null, int timeoutSeconds = 30)
        {
            _baseUrl = baseUrl.TrimEnd('/');
            _apiKey = apiKey;
            _timeoutSeconds = timeoutSeconds;
            
            _httpClient = new HttpClient
            {
                BaseAddress = new Uri(_baseUrl),
                Timeout = TimeSpan.FromSeconds(timeoutSeconds)
            };
            
            if (!string.IsNullOrEmpty(apiKey))
            {
                _httpClient.DefaultRequestHeaders.Add("Authorization", $"Bearer {apiKey}");
            }
        }

        /// <summary>
        /// Chiama un tool MCP
        /// </summary>
        public async Task<ToolResult<T>> CallToolAsync<T>(string toolName, object arguments)
        {
            var request = new MCPRequest
            {
                Name = toolName,
                Arguments = arguments
            };

            var json = JsonConvert.SerializeObject(request);
            var content = new StringContent(json, Encoding.UTF8, "application/json");

            try
            {
                var response = await _httpClient.PostAsync("/mcp/tools/call", content);
                response.EnsureSuccessStatusCode();

                var responseJson = await response.Content.ReadAsStringAsync();
                var result = JsonConvert.DeserializeObject<MCPResponse<T>>(responseJson);

                if (result.IsError)
                {
                    throw new MCPException($"Tool {toolName} failed: {result.Error}");
                }

                return new ToolResult<T>
                {
                    Success = true,
                    Data = result.Content,
                    ResponseTime = result.ResponseTime
                };
            }
            catch (TaskCanceledException)
            {
                throw new MCPTimeoutException($"Tool {toolName} timed out after {_timeoutSeconds}s");
            }
            catch (HttpRequestException ex)
            {
                throw new MCPException($"HTTP error calling tool {toolName}: {ex.Message}", ex);
            }
        }

        /// <summary>
        /// Health check del server MCP
        /// </summary>
        public async Task<HealthCheckResult> HealthCheckAsync()
        {
            try
            {
                var response = await _httpClient.GetAsync("/health");
                response.EnsureSuccessStatusCode();
                
                var json = await response.Content.ReadAsStringAsync();
                return JsonConvert.DeserializeObject<HealthCheckResult>(json);
            }
            catch
            {
                return new HealthCheckResult { Available = false };
            }
        }
    }
}
```

### 3.2 Modelli Request/Response

**File**: `SeozoomContainer.MCPClient/Models/MCPRequest.cs`

```csharp
namespace SeozoomContainer.MCPClient.Models
{
    public class MCPRequest
    {
        [JsonProperty("name")]
        public string Name { get; set; }
        
        [JsonProperty("arguments")]
        public object Arguments { get; set; }
    }
}
```

**File**: `SeozoomContainer.MCPClient/Models/MCPResponse.cs`

```csharp
namespace SeozoomContainer.MCPClient.Models
{
    public class MCPResponse<T>
    {
        [JsonProperty("content")]
        public T Content { get; set; }
        
        [JsonProperty("isError")]
        public bool IsError { get; set; }
        
        [JsonProperty("error")]
        public string Error { get; set; }
        
        [JsonProperty("responseTime")]
        public double? ResponseTime { get; set; }
    }
}
```

**File**: `SeozoomContainer.MCPClient/Models/ToolResult.cs`

```csharp
namespace SeozoomContainer.MCPClient.Models
{
    public class ToolResult<T>
    {
        public bool Success { get; set; }
        public T Data { get; set; }
        public double? ResponseTime { get; set; }
        public string Error { get; set; }
    }
}
```

### 3.3 Eccezioni

**File**: `SeozoomContainer.MCPClient/Exceptions/MCPException.cs`

```csharp
namespace SeozoomContainer.MCPClient.Exceptions
{
    public class MCPException : Exception
    {
        public MCPException(string message) : base(message) { }
        public MCPException(string message, Exception innerException) : base(message, innerException) { }
    }

    public class MCPTimeoutException : MCPException
    {
        public MCPTimeoutException(string message) : base(message) { }
    }
}
```

---

## 4. Implementazione Tool MCP

### 4.1 Tool: generate_embedding

**File**: `SeozoomContainer.MCPClient/Tools/EmbeddingTool.cs`

```csharp
using System;
using System.Threading.Tasks;
using TextProcessingSuite.SemanticProcessing; // Assumendo namespace

namespace SeozoomContainer.MCPClient.Tools
{
    public class EmbeddingTool
    {
        private readonly MCPClient _mcpClient;
        private readonly EmbeddingGenerator _embeddingGenerator; // Da TextProcessingSuite

        public EmbeddingTool(MCPClient mcpClient, EmbeddingGenerator embeddingGenerator)
        {
            _mcpClient = mcpClient;
            _embeddingGenerator = embeddingGenerator;
        }

        /// <summary>
        /// Genera embedding per un post WordPress
        /// Questo metodo viene chiamato da WordPress via MCP Server
        /// </summary>
        public async Task<float[]> GenerateEmbeddingAsync(int postId, string content, string model = "bert-base-italian")
        {
            // Usa TextProcessingSuite per generare embedding
            var embedding = await _embeddingGenerator.GenerateAsync(content, model);
            
            return embedding;
        }

        /// <summary>
        /// Batch generation per più post
        /// </summary>
        public async Task<Dictionary<int, float[]>> BatchGenerateAsync(Dictionary<int, string> posts)
        {
            var results = new Dictionary<int, float[]>();
            
            foreach (var post in posts)
            {
                try
                {
                    var embedding = await GenerateEmbeddingAsync(post.Key, post.Value);
                    results[post.Key] = embedding;
                }
                catch (Exception ex)
                {
                    // Log errore ma continua con altri post
                    Console.WriteLine($"Error generating embedding for post {post.Key}: {ex.Message}");
                }
            }
            
            return results;
        }
    }
}
```

**Nota**: Questo tool viene esposto dal **WordPress MCP Server**, non implementato direttamente qui. Il C# deve implementare un **MCP Server** che espone questi tool, oppure WordPress chiama C# direttamente.

**Correzione**: Il C# deve implementare un **MCP Server** che WordPress può chiamare, oppure WordPress chiama C# via HTTP REST API.

### 4.2 Tool: semantic_search

**File**: `SeozoomContainer.MCPClient/Tools/SemanticSearchTool.cs`

```csharp
using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Qdrant.Client;

namespace SeozoomContainer.MCPClient.Tools
{
    public class SemanticSearchTool
    {
        private readonly QdrantClient _qdrantClient;
        private readonly EmbeddingGenerator _embeddingGenerator;

        public SemanticSearchTool(QdrantClient qdrantClient, EmbeddingGenerator embeddingGenerator)
        {
            _qdrantClient = qdrantClient;
            _embeddingGenerator = embeddingGenerator;
        }

        /// <summary>
        /// Cerca contenuti simili usando ricerca semantica
        /// </summary>
        public async Task<List<SearchResult>> SearchAsync(string query, int limit = 10, float threshold = 0.7f)
        {
            // 1. Genera embedding per la query
            var queryEmbedding = await _embeddingGenerator.GenerateAsync(query);
            
            // 2. Cerca nel database vettoriale
            var searchResults = await _qdrantClient.SearchAsync(
                collectionName: "wordpress_content",
                queryVector: queryEmbedding,
                limit: limit,
                scoreThreshold: threshold
            );
            
            // 3. Mappa risultati
            return searchResults.Select(r => new SearchResult
            {
                PostId = int.Parse(r.Payload["post_id"].ToString()),
                Similarity = r.Score,
                Title = r.Payload["post_title"].ToString(),
                Url = r.Payload["url"].ToString()
            }).ToList();
        }
    }

    public class SearchResult
    {
        public int PostId { get; set; }
        public float Similarity { get; set; }
        public string Title { get; set; }
        public string Url { get; set; }
    }
}
```

### 4.3 Tool: analyze_content_quality

**File**: `SeozoomContainer.MCPClient/Tools/AnalysisTool.cs`

```csharp
using System;
using System.Collections.Generic;
using System.Threading.Tasks;
using TextProcessingSuite.NLP; // Assumendo namespace

namespace SeozoomContainer.MCPClient.Tools
{
    public class AnalysisTool
    {
        private readonly ContentAnalyzer _analyzer; // Da TextProcessingSuite

        public AnalysisTool(ContentAnalyzer analyzer)
        {
            _analyzer = analyzer;
        }

        /// <summary>
        /// Analizza qualità contenuto usando NLP
        /// </summary>
        public async Task<QualityAnalysisResult> AnalyzeQualityAsync(int postId, string content)
        {
            var analysis = await _analyzer.AnalyzeAsync(content);
            
            return new QualityAnalysisResult
            {
                PostId = postId,
                Score = analysis.OverallScore,
                Suggestions = analysis.Suggestions,
                Metrics = new QualityMetrics
                {
                    Readability = analysis.ReadabilityScore,
                    SeoScore = analysis.SeoScore,
                    KeywordDensity = analysis.KeywordDensity,
                    SentenceLength = analysis.AvgSentenceLength,
                    ParagraphCount = analysis.ParagraphCount
                }
            };
        }
    }

    public class QualityAnalysisResult
    {
        public int PostId { get; set; }
        public float Score { get; set; }
        public List<string> Suggestions { get; set; }
        public QualityMetrics Metrics { get; set; }
    }

    public class QualityMetrics
    {
        public float Readability { get; set; }
        public float SeoScore { get; set; }
        public float KeywordDensity { get; set; }
        public float SentenceLength { get; set; }
        public int ParagraphCount { get; set; }
    }
}
```

### 4.4 Tool: wikidata_enrich

**File**: `SeozoomContainer.MCPClient/Tools/WikidataTool.cs`

```csharp
using System;
using System.Threading.Tasks;
using WikifySmart.SPARQL; // Assumendo namespace

namespace SeozoomContainer.MCPClient.Tools
{
    public class WikidataTool
    {
        private readonly WikidataClient _wikidataClient; // Da WikifySmart

        public WikidataTool(WikidataClient wikidataClient)
        {
            _wikidataClient = wikidataClient;
        }

        /// <summary>
        /// Arricchisce contenuto con dati Wikidata
        /// </summary>
        public async Task<WikidataEnrichmentResult> EnrichAsync(int postId, string entityId)
        {
            // 1. Recupera entità Wikidata
            var entity = await _wikidataClient.GetEntityAsync(entityId);
            
            // 2. Genera microdata Schema.org
            var microdata = GenerateMicrodata(entity);
            
            // 3. Genera link semantici
            var links = GenerateSemanticLinks(entity);
            
            return new WikidataEnrichmentResult
            {
                PostId = postId,
                EntityId = entityId,
                EntityData = entity,
                Microdata = microdata,
                SemanticLinks = links
            };
        }

        /// <summary>
        /// Cerca entità Wikidata per keyword
        /// </summary>
        public async Task<List<WikidataEntity>> SearchEntityAsync(string query, string lang = "it")
        {
            return await _wikidataClient.SearchAsync(query, lang);
        }

        private string GenerateMicrodata(WikidataEntity entity)
        {
            // Implementazione generazione microdata Schema.org
            // Vedi documentazione Wikidata per dettagli
            return "";
        }

        private List<SemanticLink> GenerateSemanticLinks(WikidataEntity entity)
        {
            // Implementazione generazione link semantici
            return new List<SemanticLink>();
        }
    }

    public class WikidataEnrichmentResult
    {
        public int PostId { get; set; }
        public string EntityId { get; set; }
        public object EntityData { get; set; }
        public string Microdata { get; set; }
        public List<SemanticLink> SemanticLinks { get; set; }
    }

    public class SemanticLink
    {
        public string Text { get; set; }
        public string Url { get; set; }
        public string Type { get; set; }
    }
}
```

---

## 5. Integrazione con WordPress

### 5.1 Chiamata Tool da WordPress

WordPress chiama C# via MCP Server. Il C# deve esporre un **MCP Server** o un **REST API** che WordPress può chiamare.

**Opzione A: MCP Server C#** (Raccomandato)
- Implementa server MCP che espone tool
- WordPress chiama via HTTP al server MCP

**Opzione B: REST API C#**
- Implementa REST API semplice
- WordPress chiama direttamente

### 5.2 Esempio Integrazione

**Nel progetto principale SeoZoomReader**:

```csharp
using SeozoomContainer.MCPClient;
using SeozoomContainer.MCPClient.Tools;

public class MainViewModel
{
    private readonly MCPClient _mcpClient;
    private readonly EmbeddingTool _embeddingTool;
    
    public MainViewModel()
    {
        // Inizializza MCP Client per comunicare con WordPress
        _mcpClient = new MCPClient(
            baseUrl: "http://localhost:3000", // WordPress MCP Server
            apiKey: "your-api-key"
        );
        
        // Inizializza tool
        var embeddingGenerator = new EmbeddingGenerator();
        _embeddingTool = new EmbeddingTool(_mcpClient, embeddingGenerator);
    }
    
    public async Task ProcessWordPressPostAsync(int postId)
    {
        // 1. Recupera post da WordPress via MCP
        var postResult = await _mcpClient.CallToolAsync<WordPressPost>(
            "get_post_full",
            new { id = postId }
        );
        
        if (!postResult.Success)
        {
            throw new Exception($"Failed to get post: {postResult.Error}");
        }
        
        var post = postResult.Data;
        
        // 2. Genera embedding
        var embedding = await _embeddingTool.GenerateEmbeddingAsync(
            postId,
            post.Content
        );
        
        // 3. Analizza qualità
        var analysis = await _analysisTool.AnalyzeQualityAsync(postId, post.Content);
        
        // 4. Cerca contenuti simili
        var similar = await _semanticSearchTool.SearchAsync(post.Title, limit: 10);
        
        // 5. Invia risultati a WordPress (opzionale)
        // WordPress può recuperare risultati via MCP o salvarli localmente
    }
}
```

---

## 6. Testing

### 6.1 Unit Test

**File**: `SeozoomContainer.MCPClient.Tests/EmbeddingToolTests.cs`

```csharp
using Xunit;
using SeozoomContainer.MCPClient.Tools;

namespace SeozoomContainer.MCPClient.Tests
{
    public class EmbeddingToolTests
    {
        [Fact]
        public async Task GenerateEmbedding_ValidContent_ReturnsEmbedding()
        {
            // Arrange
            var embeddingGenerator = new MockEmbeddingGenerator();
            var mcpClient = new MockMCPClient();
            var tool = new EmbeddingTool(mcpClient, embeddingGenerator);
            
            // Act
            var embedding = await tool.GenerateEmbeddingAsync(123, "Test content");
            
            // Assert
            Assert.NotNull(embedding);
            Assert.Equal(768, embedding.Length); // BERT base dimensioni
        }
    }
}
```

### 6.2 Integration Test

**Test con WordPress MCP Server reale**:

```csharp
[Fact]
public async Task CallWordPressMCP_HealthCheck_Success()
{
    var client = new MCPClient("http://localhost:3000", "test-api-key");
    var health = await client.HealthCheckAsync();
    
    Assert.True(health.Available);
}
```

### 6.3 Mock per Testing

```csharp
public class MockMCPClient : MCPClient
{
    public override async Task<ToolResult<T>> CallToolAsync<T>(string toolName, object arguments)
    {
        // Mock implementation per testing
        return new ToolResult<T> { Success = true, Data = default(T) };
    }
}
```

---

## 7. Logging e Monitoring

### 7.1 Logging

```csharp
using Microsoft.Extensions.Logging;

public class MCPClient
{
    private readonly ILogger<MCPClient> _logger;
    
    public async Task<ToolResult<T>> CallToolAsync<T>(string toolName, object arguments)
    {
        _logger.LogInformation("Calling MCP tool: {ToolName}", toolName);
        
        try
        {
            var result = await InternalCallToolAsync<T>(toolName, arguments);
            _logger.LogInformation("MCP tool {ToolName} succeeded in {ResponseTime}ms", 
                toolName, result.ResponseTime);
            return result;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "MCP tool {ToolName} failed", toolName);
            throw;
        }
    }
}
```

### 7.2 Metrics

```csharp
public class MCPMetrics
{
    private int _totalCalls = 0;
    private int _successfulCalls = 0;
    private int _failedCalls = 0;
    private TimeSpan _totalResponseTime = TimeSpan.Zero;
    
    public void RecordCall(bool success, TimeSpan responseTime)
    {
        _totalCalls++;
        if (success)
            _successfulCalls++;
        else
            _failedCalls++;
        
        _totalResponseTime += responseTime;
    }
    
    public double GetSuccessRate() => (double)_successfulCalls / _totalCalls;
    public TimeSpan GetAverageResponseTime() => _totalResponseTime / _totalCalls;
}
```

---

## 8. Deployment

### 8.1 Build

```bash
dotnet build SeozoomContainer.MCPClient/SeozoomContainer.MCPClient.csproj -c Release
```

### 8.2 Publish

```bash
dotnet publish SeozoomContainer.MCPClient/SeozoomContainer.MCPClient.csproj -c Release -o ./publish
```

### 8.3 Configurazione

**appsettings.json**:
```json
{
  "MCP": {
    "BaseUrl": "http://localhost:3000",
    "ApiKey": "your-api-key",
    "TimeoutSeconds": 30
  },
  "Qdrant": {
    "Host": "localhost",
    "Port": 6333,
    "CollectionName": "wordpress_content"
  }
}
```

---

## 9. Riferimenti

- **Interfacce MCP**: Vedi `docs/INTERFACCE_MCP.md`
- **Architettura Sistema**: Vedi `ARCHITETTURA_SISTEMA_IBRIDO.md`
- **TextProcessingSuite**: Documentazione libreria embedding
- **WikifySmart**: Documentazione modulo Wikidata
- **Qdrant Client**: https://github.com/qdrant/qdrant-dotnet

---

**Documento creato**: Gennaio 2025  
**Versione**: 1.0  
**Autore**: Sistema di analisi progetti

