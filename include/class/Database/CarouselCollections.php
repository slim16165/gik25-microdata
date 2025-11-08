<?php
namespace gik25microdata\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gestione tabelle database per collezioni caroselli/liste generiche
 * 
 * Tabelle:
 * - wp_carousel_collections: collezioni configurabili (es: "colori", "architetti", "programmi-3d")
 * - wp_carousel_items: items di una collezione (link, titolo, immagine, URL, ordine)
 */
class CarouselCollections
{
    private const TABLE_COLLECTIONS = 'carousel_collections';
    private const TABLE_ITEMS = 'carousel_items';
    private const DB_VERSION = '1.1.0'; // Incrementato per aggiungere template_id e template_config
    private const DB_VERSION_OPTION = 'carousel_collections_db_version';

    /**
     * Inizializza le tabelle database
     * @param string $plugin_file Path del file principale del plugin
     */
    public static function init(string $plugin_file): void
    {
        add_action('plugins_loaded', [self::class, 'maybe_create_tables']);
        register_activation_hook($plugin_file, [self::class, 'create_tables']);
    }

    /**
     * Crea le tabelle se necessario
     */
    public static function maybe_create_tables(): void
    {
        $installed_version = get_option(self::DB_VERSION_OPTION);
        
        if ($installed_version !== self::DB_VERSION) {
            self::create_tables();
            update_option(self::DB_VERSION_OPTION, self::DB_VERSION);
        }
    }

    /**
     * Crea le tabelle database
     */
    public static function create_tables(): void
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Tabella: Collezioni caroselli
        $table_collections = $wpdb->prefix . self::TABLE_COLLECTIONS;
        $sql_collections = "CREATE TABLE IF NOT EXISTS {$table_collections} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            collection_key varchar(100) NOT NULL COMMENT 'chiave univoca (es: colori, architetti, programmi-3d)',
            collection_name varchar(255) NOT NULL COMMENT 'nome visualizzato (es: Colori, Architetti)',
            collection_description text COMMENT 'descrizione della collezione',
            display_type varchar(20) DEFAULT 'carousel' COMMENT 'carousel, list, grid',
            template_id bigint(20) UNSIGNED COMMENT 'FK a wp_carousel_templates.id',
            template_config text COMMENT 'JSON con configurazione template (override variabili CSS, opzioni)',
            shortcode_tag varchar(50) COMMENT 'tag shortcode personalizzato (opzionale)',
            css_class varchar(255) COMMENT 'classi CSS personalizzate',
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY collection_key (collection_key),
            KEY is_active (is_active),
            KEY shortcode_tag (shortcode_tag),
            KEY template_id (template_id)
        ) {$charset_collate};";

        // Tabella: Items collezioni
        $table_items = $wpdb->prefix . self::TABLE_ITEMS;
        $sql_items = "CREATE TABLE IF NOT EXISTS {$table_items} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            collection_id bigint(20) UNSIGNED NOT NULL,
            item_title varchar(255) NOT NULL COMMENT 'titolo/etichetta item',
            item_url varchar(500) NOT NULL COMMENT 'URL link item',
            item_image_url varchar(500) COMMENT 'URL immagine (opzionale)',
            item_description text COMMENT 'descrizione item (opzionale)',
            category varchar(100) COMMENT 'categoria/gruppo item (per raggruppare)',
            display_order int(11) DEFAULT 0 COMMENT 'ordine visualizzazione',
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY collection_id (collection_id),
            KEY is_active (is_active),
            KEY category (category),
            KEY display_order (display_order),
            INDEX collection_active_order (collection_id, is_active, display_order)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_collections);
        dbDelta($sql_items);
    }

    /**
     * Ottieni collezione per chiave
     */
    public static function get_collection_by_key(string $key): ?array
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_COLLECTIONS;
        
        $collection = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE collection_key = %s AND is_active = 1",
            $key
        ), ARRAY_A);

        return $collection ?: null;
    }

    /**
     * Ottieni tutte le collezioni attive
     */
    public static function get_active_collections(): array
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_COLLECTIONS;
        
        return $wpdb->get_results(
            "SELECT * FROM {$table} WHERE is_active = 1 ORDER BY collection_name",
            ARRAY_A
        );
    }

    /**
     * Ottieni items di una collezione
     */
    public static function get_collection_items(int $collection_id, ?string $category = null): array
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_ITEMS;
        
        if ($category) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table} 
                WHERE collection_id = %d 
                AND is_active = 1 
                AND (category = %s OR category IS NULL)
                ORDER BY category, display_order, item_title",
                $collection_id,
                $category
            ), ARRAY_A);
        }
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} 
            WHERE collection_id = %d 
            AND is_active = 1 
            ORDER BY category, display_order, item_title",
            $collection_id
        ), ARRAY_A);
    }

    /**
     * Ottieni categorie di una collezione
     */
    public static function get_collection_categories(int $collection_id): array
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_ITEMS;
        
        $categories = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT category 
            FROM {$table} 
            WHERE collection_id = %d 
            AND is_active = 1 
            AND category IS NOT NULL 
            AND category != ''
            ORDER BY category",
            $collection_id
        ));
        
        return $categories ?: [];
    }

    /**
     * Aggiungi/aggiorna collezione
     */
    public static function upsert_collection(array $data): int
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_COLLECTIONS;
        
        $defaults = [
            'collection_key' => '',
            'collection_name' => '',
            'collection_description' => '',
            'display_type' => 'carousel',
            'shortcode_tag' => null,
            'css_class' => null,
            'is_active' => 1,
        ];
        
        $data = array_merge($defaults, $data);
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE collection_key = %s",
            $data['collection_key']
        ));
        
        if ($existing) {
            $wpdb->update($table, $data, ['id' => $existing]);
            return (int) $existing;
        } else {
            $wpdb->insert($table, $data);
            return (int) $wpdb->insert_id;
        }
    }

    /**
     * Aggiungi/aggiorna item
     */
    public static function upsert_item(array $data): int
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_ITEMS;
        
        $defaults = [
            'collection_id' => 0,
            'item_title' => '',
            'item_url' => '',
            'item_image_url' => null,
            'item_description' => null,
            'category' => null,
            'display_order' => 0,
            'is_active' => 1,
        ];
        
        $data = array_merge($defaults, $data);
        
        if (empty($data['collection_id'])) {
            return 0;
        }
        
        // Se esiste già un item con stesso URL e collezione, aggiorna
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE collection_id = %d AND item_url = %s",
            $data['collection_id'],
            $data['item_url']
        ));
        
        if ($existing) {
            $wpdb->update($table, $data, ['id' => $existing]);
            return (int) $existing;
        } else {
            $wpdb->insert($table, $data);
            return (int) $wpdb->insert_id;
        }
    }

    /**
     * Migrazione dati da codice hardcoded a database
     * Utile per migrare collezioni esistenti
     * 
     * @param string $collection_key Chiave collezione
     * @param array $items Array di items (ogni item può avere 'title', 'url', 'category', 'image_url', 'description')
     * @param string|null $default_category Categoria di default (usata solo se l'item non ha categoria)
     * @return int ID collezione
     */
    public static function migrate_from_hardcoded(string $collection_key, array $items, ?string $default_category = null): int
    {
        // Crea collezione se non esiste
        $collection = self::get_collection_by_key($collection_key);
        if (!$collection) {
            $collection_id = self::upsert_collection([
                'collection_key' => $collection_key,
                'collection_name' => ucfirst(str_replace('-', ' ', $collection_key)),
                'display_type' => 'carousel',
            ]);
        } else {
            $collection_id = (int) $collection['id'];
        }
        
        // Aggiungi items
        $order = 0;
        foreach ($items as $item) {
            // Determina categoria: priorità: item['category'] > default_category > null
            $item_category = $item['category'] ?? $default_category ?? null;
            
            $item_data = [
                'collection_id' => $collection_id,
                'item_title' => $item['title'] ?? $item['label'] ?? '',
                'item_url' => $item['url'] ?? '',
                'item_image_url' => $item['image'] ?? $item['image_url'] ?? null,
                'item_description' => $item['description'] ?? null,
                'category' => $item_category,
                'display_order' => $order++,
            ];
            
            self::upsert_item($item_data);
        }
        
        return $collection_id;
    }
}

