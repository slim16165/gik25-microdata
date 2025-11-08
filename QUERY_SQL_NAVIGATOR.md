# Query SQL per Popolare il Navigator Dinamico

## 1. Query Base: Tutte le Categorie WordPress

```sql
-- Elenco tutte le categorie con conteggio post
SELECT 
    t.term_id,
    t.name AS categoria_nome,
    t.slug AS categoria_slug,
    COUNT(p.ID) AS numero_post
FROM wp_terms t
INNER JOIN wp_term_taxonomy tt ON t.term_id = tt.term_id
INNER JOIN wp_term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
INNER JOIN wp_posts p ON tr.object_id = p.ID
WHERE tt.taxonomy = 'category'
    AND p.post_status = 'publish'
    AND p.post_type = 'post'
GROUP BY t.term_id, t.name, t.slug
ORDER BY numero_post DESC;
```

## 2. Post per Categoria (Top N)

```sql
-- Post più recenti per categoria specifica (sostituisci 'CATEGORIA_SLUG')
SELECT 
    p.ID,
    p.post_title,
    p.post_name AS slug,
    p.post_date,
    p.post_excerpt,
    CONCAT('https://www.totaldesign.it/', p.post_name, '/') AS url
FROM wp_posts p
INNER JOIN wp_term_relationships tr ON p.ID = tr.object_id
INNER JOIN wp_term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
INNER JOIN wp_terms t ON tt.term_id = t.term_id
WHERE tt.taxonomy = 'category'
    AND t.slug = 'CATEGORIA_SLUG'  -- Sostituisci con slug categoria
    AND p.post_status = 'publish'
    AND p.post_type = 'post'
ORDER BY p.post_date DESC
LIMIT 10;
```

## 3. Post per Parole Chiave nel Titolo/Contenuto

```sql
-- Post che contengono keyword specifiche (es. "colore bianco", "ikea metod")
SELECT 
    p.ID,
    p.post_title,
    p.post_name AS slug,
    p.post_date,
    LEFT(p.post_excerpt, 150) AS excerpt,
    CONCAT('https://www.totaldesign.it/', p.post_name, '/') AS url
FROM wp_posts p
WHERE p.post_status = 'publish'
    AND p.post_type = 'post'
    AND (
        p.post_title LIKE '%KEYWORD%' 
        OR p.post_content LIKE '%KEYWORD%'
        OR p.post_excerpt LIKE '%KEYWORD%'
    )
ORDER BY 
    CASE 
        WHEN p.post_title LIKE '%KEYWORD%' THEN 1
        ELSE 2
    END,
    p.post_date DESC
LIMIT 20;
```

## 4. Post più Popolari (per Visualizzazioni/Commenti)

```sql
-- Post più popolari (se hai plugin statistiche, altrimenti usa commenti)
SELECT 
    p.ID,
    p.post_title,
    p.post_name AS slug,
    p.post_date,
    LEFT(p.post_excerpt, 150) AS excerpt,
    COUNT(c.comment_ID) AS numero_commenti,
    CONCAT('https://www.totaldesign.it/', p.post_name, '/') AS url
FROM wp_posts p
LEFT JOIN wp_comments c ON p.ID = c.comment_post_ID 
    AND c.comment_approved = '1'
WHERE p.post_status = 'publish'
    AND p.post_type = 'post'
GROUP BY p.ID, p.post_title, p.post_name, p.post_date, p.post_excerpt
ORDER BY numero_commenti DESC, p.post_date DESC
LIMIT 20;
```

## 5. Post per Colore Specifico (basato su slug/titolo)

```sql
-- Post relativi a un colore specifico (es. "bianco", "verde-salvia")
SELECT 
    p.ID,
    p.post_title,
    p.post_name AS slug,
    p.post_date,
    LEFT(p.post_excerpt, 150) AS excerpt,
    CONCAT('https://www.totaldesign.it/', p.post_name, '/') AS url
FROM wp_posts p
WHERE p.post_status = 'publish'
    AND p.post_type = 'post'
    AND (
        p.post_name LIKE '%COLORE%'  -- es. '%bianco%', '%verde-salvia%'
        OR p.post_title LIKE '%COLORE%'
    )
ORDER BY p.post_date DESC
LIMIT 15;
```

## 6. Post IKEA (per linea/sistema)

```sql
-- Post relativi a linee IKEA specifiche
SELECT 
    p.ID,
    p.post_title,
    p.post_name AS slug,
    p.post_date,
    LEFT(p.post_excerpt, 150) AS excerpt,
    CONCAT('https://www.totaldesign.it/', p.post_name, '/') AS url
FROM wp_posts p
WHERE p.post_status = 'publish'
    AND p.post_type = 'post'
    AND (
        p.post_name LIKE '%ikea%'
        OR p.post_title LIKE '%ikea%'
        OR p.post_content LIKE '%ikea%'
    )
    AND (
        p.post_name LIKE '%LINEA%'  -- es. '%metod%', '%enhet%', '%billy%'
        OR p.post_title LIKE '%LINEA%'
        OR p.post_content LIKE '%LINEA%'
    )
ORDER BY p.post_date DESC
LIMIT 15;
```

## 7. Post per Stanza/Ambiente

```sql
-- Post relativi a stanze specifiche (soggiorno, cucina, camera, bagno)
SELECT 
    p.ID,
    p.post_title,
    p.post_name AS slug,
    p.post_date,
    LEFT(p.post_excerpt, 150) AS excerpt,
    CONCAT('https://www.totaldesign.it/', p.post_name, '/') AS url
FROM wp_posts p
WHERE p.post_status = 'publish'
    AND p.post_type = 'post'
    AND (
        p.post_name LIKE '%STANZA%'  -- es. '%soggiorno%', '%cucina%', '%camera%'
        OR p.post_title LIKE '%STANZA%'
        OR p.post_content LIKE '%STANZA%'
    )
ORDER BY p.post_date DESC
LIMIT 15;
```

## 8. Post Pantone

```sql
-- Post relativi a Pantone
SELECT 
    p.ID,
    p.post_title,
    p.post_name AS slug,
    p.post_date,
    LEFT(p.post_excerpt, 150) AS excerpt,
    CONCAT('https://www.totaldesign.it/', p.post_name, '/') AS url
FROM wp_posts p
WHERE p.post_status = 'publish'
    AND p.post_type = 'post'
    AND (
        p.post_name LIKE '%pantone%'
        OR p.post_title LIKE '%pantone%'
        OR p.post_content LIKE '%pantone%'
    )
ORDER BY p.post_date DESC
LIMIT 20;
```

## 9. Query Combinata: Post con Immagine in Evidenza

```sql
-- Post con featured image (thumbnail)
SELECT 
    p.ID,
    p.post_title,
    p.post_name AS slug,
    p.post_date,
    LEFT(p.post_excerpt, 150) AS excerpt,
    CONCAT('https://www.totaldesign.it/', p.post_name, '/') AS url,
    pm.meta_value AS featured_image_id,
    (SELECT guid FROM wp_posts WHERE ID = pm.meta_value) AS featured_image_url
FROM wp_posts p
INNER JOIN wp_postmeta pm ON p.ID = pm.post_id 
    AND pm.meta_key = '_thumbnail_id'
WHERE p.post_status = 'publish'
    AND p.post_type = 'post'
    AND t.slug = 'CATEGORIA_SLUG'  -- Opzionale: filtra per categoria
ORDER BY p.post_date DESC
LIMIT 20;
```

## 10. Query per Architetti

```sql
-- Post relativi a architetti (basato su slug/titolo)
SELECT 
    p.ID,
    p.post_title,
    p.post_name AS slug,
    p.post_date,
    LEFT(p.post_excerpt, 150) AS excerpt,
    CONCAT('https://www.totaldesign.it/', p.post_name, '/') AS url
FROM wp_posts p
WHERE p.post_status = 'publish'
    AND p.post_type = 'post'
    AND (
        p.post_name LIKE '%ARCHITETTO%'  -- es. '%renzo-piano%', '%zaha-hadid%'
        OR p.post_title LIKE '%ARCHITETTO%'
    )
ORDER BY p.post_date DESC
LIMIT 20;
```

## Istruzioni per l'Uso

1. **Sostituisci i placeholder:**
   - `CATEGORIA_SLUG` → slug della categoria WordPress
   - `KEYWORD` → parola chiave da cercare
   - `COLORE` → nome colore (es. "bianco", "verde-salvia")
   - `LINEA` → linea IKEA (es. "metod", "enhet", "billy")
   - `STANZA` → ambiente (es. "soggiorno", "cucina")
   - `ARCHITETTO` → nome architetto (es. "renzo-piano")

2. **Esegui le query:**
   - Via phpMyAdmin
   - Via WP-CLI: `wp db query "QUERY_SQL"`
   - Via script PHP WordPress

3. **Esporta i risultati:**
   - CSV per analisi
   - JSON per integrazione nel codice
   - Array PHP per hardcoding iniziale

## Prossimi Passi

Dopo aver eseguito queste query, potremo:
- Integrare i risultati nel navigator dinamico
- Creare funzioni PHP che usano `WP_Query` invece di SQL diretto
- Implementare cache/transient per performance
- Creare un MCP server per interrogare il sito direttamente

