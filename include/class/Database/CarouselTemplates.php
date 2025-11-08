<?php
namespace gik25microdata\Database;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gestione tabelle database per template caroselli/liste configurabili
 * 
 * Template riutilizzabili per CSS, DOM structure e JavaScript.
 * Permette di configurare completamente l'aspetto e comportamento dei caroselli
 * senza modificare codice PHP.
 */
class CarouselTemplates
{
    private const TABLE_TEMPLATES = 'carousel_templates';
    private const DB_VERSION = '1.0.0';
    private const DB_VERSION_OPTION = 'carousel_templates_db_version';

    /**
     * Inizializza le tabelle database
     * @param string $plugin_file Path del file principale del plugin
     */
    public static function init(string $plugin_file): void
    {
        add_action('plugins_loaded', [self::class, 'maybe_create_tables']);
        register_activation_hook($plugin_file, [self::class, 'create_tables']);
        
        // Popola template di sistema al primo avvio
        add_action('plugins_loaded', [self::class, 'maybe_populate_system_templates'], 20);
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

        // Tabella: Template caroselli
        $table_templates = $wpdb->prefix . self::TABLE_TEMPLATES;
        $sql_templates = "CREATE TABLE IF NOT EXISTS {$table_templates} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            template_key varchar(100) NOT NULL COMMENT 'chiave univoca (es: thumbnail-list, simple-list, grid-modern)',
            template_name varchar(255) NOT NULL COMMENT 'nome visualizzato',
            template_type varchar(20) DEFAULT 'full' COMMENT 'css, dom, js, full',
            css_content longtext COMMENT 'CSS del template',
            dom_structure longtext COMMENT 'Template HTML/DOM',
            js_content longtext COMMENT 'JavaScript del template',
            css_variables text COMMENT 'JSON con variabili CSS configurabili',
            is_system tinyint(1) DEFAULT 0 COMMENT 'Template di sistema (non eliminabile)',
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY template_key (template_key),
            KEY is_active (is_active),
            KEY is_system (is_system)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_templates);
    }

    /**
     * Popola template di sistema se necessario
     */
    public static function maybe_populate_system_templates(): void
    {
        // Verifica se già popolati
        $populated = get_option('carousel_templates_populated', false);
        if ($populated) {
            return;
        }

        self::populate_system_templates();
        update_option('carousel_templates_populated', true);
    }

    /**
     * Popola template di sistema predefiniti
     */
    private static function populate_system_templates(): void
    {
        // Template: thumbnail-list (per SuperInformati, NonSoloDieti)
        self::create_template([
            'template_key' => 'thumbnail-list',
            'template_name' => 'Thumbnail List',
            'template_type' => 'full',
            'css_content' => self::get_thumbnail_list_css(),
            'dom_structure' => self::get_thumbnail_list_dom(),
            'js_content' => '',
            'css_variables' => json_encode([
                'item-padding' => '10px',
                'image-size' => '80px',
                'gap' => '15px'
            ]),
            'is_system' => 1,
        ]);

        // Template: simple-list (per ChieCosa, liste semplici)
        self::create_template([
            'template_key' => 'simple-list',
            'template_name' => 'Simple List',
            'template_type' => 'full',
            'css_content' => self::get_simple_list_css(),
            'dom_structure' => self::get_simple_list_dom(),
            'js_content' => '',
            'css_variables' => json_encode([
                'item-padding' => '8px',
                'gap' => '5px'
            ]),
            'is_system' => 1,
        ]);

        // Template: grid-modern (nuovo template griglia moderna)
        self::create_template([
            'template_key' => 'grid-modern',
            'template_name' => 'Grid Modern',
            'template_type' => 'full',
            'css_content' => self::get_grid_modern_css(),
            'dom_structure' => self::get_grid_modern_dom(),
            'js_content' => '',
            'css_variables' => json_encode([
                'grid-columns' => '3',
                'gap' => '20px',
                'item-min-height' => '200px'
            ]),
            'is_system' => 1,
        ]);
    }

    /**
     * Crea un template
     */
    public static function create_template(array $data): ?int
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_TEMPLATES;

        $result = $wpdb->insert(
            $table,
            [
                'template_key' => $data['template_key'],
                'template_name' => $data['template_name'],
                'template_type' => $data['template_type'] ?? 'full',
                'css_content' => $data['css_content'] ?? null,
                'dom_structure' => $data['dom_structure'] ?? null,
                'js_content' => $data['js_content'] ?? null,
                'css_variables' => $data['css_variables'] ?? null,
                'is_system' => $data['is_system'] ?? 0,
                'is_active' => $data['is_active'] ?? 1,
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d']
        );

        return $result ? $wpdb->insert_id : null;
    }

    /**
     * Ottieni template per chiave
     */
    public static function get_template_by_key(string $key): ?array
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_TEMPLATES;

        $template = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE template_key = %s AND is_active = 1",
                $key
            ),
            ARRAY_A
        );

        return $template ?: null;
    }

    /**
     * Ottieni template per ID
     */
    public static function get_template_by_id(int $id): ?array
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_TEMPLATES;

        $template = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE id = %d AND is_active = 1",
                $id
            ),
            ARRAY_A
        );

        return $template ?: null;
    }

    /**
     * Ottieni tutti i template attivi
     */
    public static function get_active_templates(): array
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_TEMPLATES;

        return $wpdb->get_results(
            "SELECT * FROM {$table} WHERE is_active = 1 ORDER BY template_name ASC",
            ARRAY_A
        ) ?: [];
    }

    /**
     * CSS per template thumbnail-list
     * Compatibile con CSS esistente in revious-microdata.css
     */
    private static function get_thumbnail_list_css(): string
    {
        return <<<'CSS'
/* Template: thumbnail-list - Compatibile con CSS esistente */
.generic-carousel-container.thumbnail-list {
    margin: 20px 0;
}

.generic-carousel-container.thumbnail-list ul.thumbnail-list,
.generic-carousel-container.thumbnail-list ol.thumbnail-list {
    list-style: none;
    padding: 0;
    margin: 0;
    column-count: 2;
}

.generic-carousel-container.thumbnail-list li {
    background: #efefef;
    border-radius: 0.3em;
    margin-bottom: 1px;
    border-collapse: collapse;
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
    break-inside: avoid;
}

.generic-carousel-container.thumbnail-list li a {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
    text-decoration: none;
    color: inherit;
    width: 100%;
}

.generic-carousel-container.thumbnail-list li img.li-img {
    display: inline-block;
    height: 50px;
    width: 50px;
    margin-bottom: 0 !important;
    box-sizing: content-box;
    flex-shrink: 0;
}

.generic-carousel-container.thumbnail-list li .item-title {
    display: inline-block;
    vertical-align: middle;
    line-height: normal;
    padding-left: 8px;
    text-decoration: none;
    font-size: 0.94em;
    box-sizing: content-box;
}

@media screen and (max-width: 600px) {
    .generic-carousel-container.thumbnail-list li .item-title {
        line-height: normal;
        font-size: 0.54em;
    }
    
    .generic-carousel-container.thumbnail-list li img.li-img {
        max-width: 50px;
        max-height: 50px;
        height: 50px;
        width: 50px;
    }
}
CSS;
    }

    /**
     * DOM structure per template thumbnail-list
     * Usa placeholder PHP-style: {ITEM_URL}, {ITEM_TITLE}, {ITEM_IMAGE}
     */
    private static function get_thumbnail_list_dom(): string
    {
        return <<<'HTML'
<ul class="thumbnail-list">
{ITEMS_LOOP_START}
<li>
    <a href="{ITEM_URL}" title="{ITEM_TITLE}">
        {ITEM_IMAGE_TAG}
        <span class="item-title">{ITEM_TITLE}</span>
    </a>
</li>
{ITEMS_LOOP_END}
</ul>
HTML;
    }

    /**
     * CSS per template simple-list
     */
    private static function get_simple_list_css(): string
    {
        return <<<'CSS'
/* Template: simple-list */
.generic-carousel-container.simple-list {
    margin: 20px 0;
}

.generic-carousel-container.simple-list ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.generic-carousel-container.simple-list li {
    padding: 8px 0;
    border-bottom: 1px solid #e5e5e5;
}

.generic-carousel-container.simple-list li:last-child {
    border-bottom: none;
}

.generic-carousel-container.simple-list li a {
    color: #2271b1;
    text-decoration: none;
    font-size: 15px;
    line-height: 1.6;
}

.generic-carousel-container.simple-list li a:hover {
    text-decoration: underline;
    color: #135e96;
}
CSS;
    }

    /**
     * DOM structure per template simple-list
     */
    private static function get_simple_list_dom(): string
    {
        return <<<'HTML'
<ul class="simple-list">
{ITEMS_LOOP_START}
<li><a href="{ITEM_URL}">{ITEM_TITLE}</a></li>
{ITEMS_LOOP_END}
</ul>
HTML;
    }

    /**
     * CSS per template grid-modern
     */
    private static function get_grid_modern_css(): string
    {
        return <<<'CSS'
/* Template: grid-modern */
.generic-carousel-container.grid-modern {
    margin: 20px 0;
}

.generic-carousel-container.grid-modern .grid-items {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.generic-carousel-container.grid-modern .grid-item {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
    min-height: 200px;
}

.generic-carousel-container.grid-modern .grid-item:hover {
    border-color: #2271b1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.generic-carousel-container.grid-modern .grid-item a {
    display: block;
    text-decoration: none;
    color: inherit;
    height: 100%;
}

.generic-carousel-container.grid-modern .grid-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    display: block;
}

.generic-carousel-container.grid-modern .grid-item .item-title {
    padding: 15px;
    font-weight: 600;
    color: #1d2327;
    line-height: 1.4;
}

@media (max-width: 782px) {
    .generic-carousel-container.grid-modern .grid-items {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
}
CSS;
    }

    /**
     * DOM structure per template grid-modern
     */
    private static function get_grid_modern_dom(): string
    {
        return <<<'HTML'
<div class="grid-items">
{ITEMS_LOOP_START}
<div class="grid-item">
    <a href="{ITEM_URL}" title="{ITEM_TITLE}">
        {ITEM_IMAGE_TAG}
        <span class="item-title">{ITEM_TITLE}</span>
    </a>
</div>
{ITEMS_LOOP_END}
</div>
HTML;
    }
}

