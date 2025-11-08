<?php
namespace gik25microdata\Admin;

use gik25microdata\Shortcodes\ShortcodeRegistry;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pagina unificata per gestione shortcode
 * 
 * Combina funzionalità di ShortcodesPage e ReviousMicrodataSettingsPage
 * con layout migliorato tipo Elementor (1 shortcode per riga)
 */
class ShortcodesManagerPage
{
    private const CAPABILITY = 'manage_options';

    /**
     * Inizializza la pagina
     */
    public static function init(): void
    {
        add_action('admin_post_gik25_toggle_shortcode', [self::class, 'handleToggle']);
    }

    /**
     * Render della pagina
     */
    public static function renderPage(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Non hai i permessi per visualizzare questa pagina.', 'gik25-microdata'));
        }

        $items = ShortcodeRegistry::getItemsForAdmin();
        
        // Aggiungi shortcode registrati direttamente (non nel registry)
        $items = self::merge_direct_shortcodes($items);
        
        $categories = self::get_categories();
        $action_url = admin_url('admin-post.php');
        
        // Organizza shortcode per categoria
        $items_by_category = [];
        foreach ($items as $slug => $item) {
            $category = self::get_item_category($slug, $item);
            if (!isset($items_by_category[$category])) {
                $items_by_category[$category] = [];
            }
            $items_by_category[$category][$slug] = $item;
        }
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Gestione Shortcode', 'gik25-microdata'); ?></h1>

            <?php if (isset($_GET['updated'])) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Impostazioni salvate.', 'gik25-microdata'); ?></p>
                </div>
            <?php endif; ?>

            <p><?php esc_html_e('Gestisci gli shortcode del plugin. Attiva o disattiva il caricamento automatico di CSS e JS per ogni shortcode.', 'gik25-microdata'); ?></p>

            <!-- Filtri e Ricerca -->
            <div class="shortcode-manager-filters">
                <div class="shortcode-search">
                    <input type="text" id="shortcode-search-input" placeholder="Cerca shortcode per nome, descrizione o alias..." class="shortcode-search-field">
                </div>
                <div class="shortcode-category-filters">
                    <button class="category-filter active" data-category="all">Tutti</button>
                    <?php foreach ($categories as $cat_key => $cat_label) : ?>
                        <button class="category-filter" data-category="<?php echo esc_attr($cat_key); ?>">
                            <?php echo esc_html($cat_label); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Lista Shortcode (1 per riga) -->
            <div class="shortcode-list-wrapper">
                <div class="shortcode-list" id="shortcode-list">
                    <?php foreach ($items_by_category as $category => $category_items) : ?>
                        <?php foreach ($category_items as $slug => $item) : ?>
                            <?php self::render_shortcode_row($slug, $item, $category, $action_url); ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Messaggio nessun risultato -->
                <div class="shortcode-no-results" id="shortcode-no-results" style="display: none;">
                    <p>Nessuno shortcode trovato. Prova a modificare i filtri o la ricerca.</p>
                </div>
            </div>
        </div>

        <?php
        self::render_styles();
        self::render_scripts();
    }

    /**
     * Renderizza una riga shortcode
     */
    private static function render_shortcode_row(string $slug, array $item, string $category, string $action_url): void
    {
        $label = $item['label'] ?? $slug;
        $description = $item['description'] ?? '';
        $example = $item['example'] ?? '';
        $aliases = $item['aliases'] ?? [];
        $enabled = !empty($item['enabled']);
        $icon = self::get_shortcode_icon($slug);
        $category_label = self::get_category_label($category);
        ?>
        <div class="shortcode-row" data-category="<?php echo esc_attr($category); ?>" data-shortcode="<?php echo esc_attr($slug); ?>">
            <div class="shortcode-row-content">
                <!-- Icona -->
                <div class="shortcode-row-icon">
                    <?php if (strpos($icon, 'dashicon:') === 0): ?>
                        <?php $dashicon_class = str_replace('dashicon:', '', $icon); ?>
                        <span class="dashicons <?php echo esc_attr($dashicon_class); ?>"></span>
                    <?php elseif ($icon): ?>
                        <img src="<?php echo esc_url($icon); ?>" alt="<?php echo esc_attr($slug); ?>">
                    <?php else: ?>
                        <span class="dashicons dashicons-shortcode"></span>
                    <?php endif; ?>
                </div>

                <!-- Informazioni -->
                <div class="shortcode-row-info">
                    <div class="shortcode-row-header">
                        <h3 class="shortcode-row-title">
                            <code><?php echo esc_html($slug); ?></code>
                            <span class="shortcode-row-label"><?php echo esc_html($label); ?></span>
                        </h3>
                        <span class="shortcode-row-category"><?php echo esc_html($category_label); ?></span>
                    </div>
                    
                    <?php if ($description): ?>
                        <p class="shortcode-row-description"><?php echo esc_html($description); ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($aliases)): ?>
                        <div class="shortcode-row-aliases">
                            <strong>Alias:</strong>
                            <?php foreach ($aliases as $alias): ?>
                                <code class="shortcode-alias-tag"><?php echo esc_html($alias); ?></code>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($example): ?>
                        <div class="shortcode-row-example">
                            <strong>Esempio:</strong>
                            <code><?php echo esc_html($example); ?></code>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Toggle e Azioni -->
                <div class="shortcode-row-actions">
                    <div class="shortcode-row-status">
                        <?php if ($enabled): ?>
                            <span class="status-badge status-enabled">
                                <span class="dashicons dashicons-yes"></span>
                                Abilitato
                            </span>
                        <?php else: ?>
                            <span class="status-badge status-disabled">
                                <span class="dashicons dashicons-no"></span>
                                Disabilitato
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php 
                    // Verifica se lo shortcode è gestito dal registry
                    $is_registry_managed = self::is_registry_managed($slug);
                    ?>
                    
                    <?php if ($is_registry_managed): ?>
                        <form method="post" action="<?php echo esc_url($action_url); ?>" class="shortcode-toggle-form">
                            <?php wp_nonce_field('gik25_toggle_shortcode'); ?>
                            <input type="hidden" name="action" value="gik25_toggle_shortcode">
                            <input type="hidden" name="slug" value="<?php echo esc_attr($slug); ?>">
                            <input type="hidden" name="enable" value="<?php echo $enabled ? '0' : '1'; ?>">
                            <input type="hidden" name="redirect_page" value="<?php echo esc_attr(AdminMenu::MENU_SLUG . '-shortcodes'); ?>">
                            
                            <label class="shortcode-toggle-switch">
                                <input type="checkbox" <?php checked($enabled); ?> onchange="this.form.submit()">
                                <span class="toggle-slider"></span>
                            </label>
                        </form>
                    <?php else: ?>
                        <div class="shortcode-toggle-switch" style="opacity: 0.5; cursor: not-allowed;" title="Questo shortcode è sempre abilitato">
                            <label class="shortcode-toggle-switch">
                                <input type="checkbox" checked disabled>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <small style="display: block; margin-top: 5px; color: #646970; font-size: 11px;">Sempre abilitato</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Gestisce toggle enable/disable
     */
    public static function handleToggle(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Operazione non consentita.', 'gik25-microdata'));
        }

        check_admin_referer('gik25_toggle_shortcode');

        $slug = isset($_POST['slug']) ? sanitize_text_field(wp_unslash($_POST['slug'])) : '';
        $enable = isset($_POST['enable']) ? (bool) intval($_POST['enable']) : false;
        $redirect_page = isset($_POST['redirect_page']) ? sanitize_text_field(wp_unslash($_POST['redirect_page'])) : AdminMenu::MENU_SLUG . '-shortcodes';

        if ($slug) {
            ShortcodeRegistry::setSlugEnabled($slug, $enable);
        }

        wp_safe_redirect(add_query_arg([
            'page' => $redirect_page,
            'updated' => $slug,
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Merge shortcode registrati direttamente (non nel registry)
     */
    private static function merge_direct_shortcodes(array $items): array
    {
        global $shortcode_tags;
        
        // Shortcode specifici da includere
        $direct_shortcodes = [
            'link_colori' => [
                'label' => 'Link Colori',
                'description' => 'Carosello link articoli sui colori.',
                'aliases' => [],
                'example' => '[link_colori]',
                'enabled' => true, // Sempre abilitato, non gestito dal registry
            ],
            'grafica3d' => [
                'label' => 'Grafica 3D',
                'description' => 'Carosello programmi di grafica 3D.',
                'aliases' => [],
                'example' => '[grafica3d]',
                'enabled' => true,
            ],
            'archistar' => [
                'label' => 'Archistar',
                'description' => 'Carosello architetti famosi.',
                'aliases' => [],
                'example' => '[archistar]',
                'enabled' => true,
            ],
        ];
        
        // Aggiungi solo se sono registrati
        foreach ($direct_shortcodes as $slug => $data) {
            if (isset($shortcode_tags[$slug])) {
                // Verifica se già presente nel registry
                if (!isset($items[$slug])) {
                    $items[$slug] = $data;
                }
            }
        }
        
        return $items;
    }

    /**
     * Verifica se uno shortcode è gestito dal registry
     */
    private static function is_registry_managed(string $slug): bool
    {
        $registry = ShortcodeRegistry::getRegistry();
        return isset($registry[$slug]);
    }

    /**
     * Ottiene categoria per uno shortcode
     */
    private static function get_item_category(string $slug, array $item): string
    {
        // Mappa categorie basata su slug
        $category_map = [
            'visual' => ['md_quote', 'md_boxinfo', 'md_flipbox', 'md_slidingbox', 'md_blinkingbutton', 'md_progressbar', 'md_perfectpullquote', 'md_prezzo', 'md_flexlist'],
            'functional' => ['md_telefono', 'md_youtube'],
            'totaldesign' => ['kitchen_finder', 'app_nav', 'carousel', 'list', 'grid', 'link_colori', 'grafica3d', 'archistar', 'td_colori_hub', 'td_ikea_hub', 'td_programmatic_home', 'td_abbinamenti_colore', 'td_palette_correlate', 'td_colore_stanza', 'td_prodotti_colore', 'td_lead_box', 'td_hack_correlati', 'td_completa_set', 'td_color_match_ikea'],
            'other' => [],
        ];

        foreach ($category_map as $category => $shortcodes) {
            if (in_array($slug, $shortcodes)) {
                return $category;
            }
        }

        return 'other';
    }

    /**
     * Ottiene categorie disponibili
     */
    private static function get_categories(): array
    {
        return [
            'visual' => '🎨 Widget Grafici',
            'functional' => '⚙️ Shortcode Funzionali',
            'totaldesign' => '🏠 Widget TotalDesign',
            'other' => '📦 Altri',
        ];
    }

    /**
     * Ottiene etichetta categoria
     */
    private static function get_category_label(string $category): string
    {
        $categories = self::get_categories();
        return $categories[$category] ?? ucfirst($category);
    }

    /**
     * Ottiene icona per shortcode
     */
    private static function get_shortcode_icon(string $slug): string
    {
        $icon_map = [
            'md_quote' => 'dashicon:dashicons-format-quote',
            'md_boxinfo' => 'dashicon:dashicons-info',
            'md_blinkingbutton' => 'dashicon:dashicons-admin-links',
            'md_progressbar' => 'dashicon:dashicons-chart-bar',
            'md_slidingbox' => 'dashicon:dashicons-slides',
            'md_flipbox' => 'dashicon:dashicons-update',
            'md_perfectpullquote' => 'dashicon:dashicons-editor-quote',
            'md_prezzo' => 'dashicon:dashicons-tag',
            'md_flexlist' => 'dashicon:dashicons-list-view',
            'md_telefono' => 'dashicon:dashicons-phone',
            'md_youtube' => 'dashicon:dashicons-video-alt3',
            'kitchen_finder' => 'dashicon:dashicons-admin-home',
            'app_nav' => 'dashicon:dashicons-menu',
            'carousel' => 'dashicon:dashicons-images-alt',
            'list' => 'dashicon:dashicons-list-view',
            'grid' => 'dashicon:dashicons-grid-view',
        ];

        return $icon_map[$slug] ?? 'dashicon:dashicons-shortcode';
    }

    /**
     * Renderizza CSS
     */
    private static function render_styles(): void
    {
        ?>
        <style>
            /* Filtri e Ricerca */
            .shortcode-manager-filters {
                margin: 20px 0;
                padding: 20px;
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .shortcode-search {
                margin-bottom: 15px;
            }
            .shortcode-search-field {
                width: 100%;
                max-width: 500px;
                padding: 10px 15px;
                border: 1px solid #8c8f94;
                border-radius: 4px;
                font-size: 14px;
                transition: border-color 0.2s;
            }
            .shortcode-search-field:focus {
                border-color: #2271b1;
                outline: none;
                box-shadow: 0 0 0 1px #2271b1;
            }
            .shortcode-category-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            .category-filter {
                padding: 8px 16px;
                background: #f6f7f7;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                transition: all 0.2s ease;
                font-weight: 500;
            }
            .category-filter:hover {
                background: #f0f0f1;
                border-color: #8c8f94;
            }
            .category-filter.active {
                background: #2271b1;
                color: #fff;
                border-color: #2271b1;
            }

            /* Lista Shortcode (1 per riga) */
            .shortcode-list-wrapper {
                margin-top: 20px;
            }
            .shortcode-list {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }
            .shortcode-row {
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 8px;
                transition: all 0.3s ease;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .shortcode-row:hover {
                border-color: #2271b1;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            .shortcode-row-content {
                display: flex;
                align-items: flex-start;
                padding: 20px;
                gap: 20px;
            }
            .shortcode-row-icon {
                flex-shrink: 0;
                width: 48px;
                height: 48px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f6f7f7;
                border-radius: 8px;
                border: 1px solid #e5e5e5;
            }
            .shortcode-row-icon .dashicons {
                font-size: 24px;
                width: 24px;
                height: 24px;
                color: #2271b1;
            }
            .shortcode-row-icon img {
                width: 32px;
                height: 32px;
                object-fit: contain;
            }
            .shortcode-row-info {
                flex: 1;
                min-width: 0;
            }
            .shortcode-row-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 10px;
                flex-wrap: wrap;
                gap: 10px;
            }
            .shortcode-row-title {
                margin: 0;
                font-size: 16px;
                font-weight: 600;
                color: #1d2327;
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }
            .shortcode-row-title code {
                background: #f6f7f7;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 13px;
                color: #2271b1;
                font-weight: 600;
            }
            .shortcode-row-label {
                color: #646970;
                font-weight: 500;
            }
            .shortcode-row-category {
                font-size: 11px;
                color: #646970;
                text-transform: uppercase;
                font-weight: 600;
                letter-spacing: 0.5px;
                padding: 4px 8px;
                background: #f6f7f7;
                border-radius: 4px;
            }
            .shortcode-row-description {
                margin: 8px 0;
                font-size: 14px;
                color: #646970;
                line-height: 1.5;
            }
            .shortcode-row-aliases,
            .shortcode-row-example {
                margin: 8px 0;
                font-size: 13px;
                color: #646970;
            }
            .shortcode-row-aliases strong,
            .shortcode-row-example strong {
                color: #1d2327;
                margin-right: 8px;
            }
            .shortcode-alias-tag {
                display: inline-block;
                margin: 2px 4px 2px 0;
                padding: 2px 6px;
                background: #f0f0f1;
                border-radius: 3px;
                font-size: 11px;
                color: #2271b1;
            }
            .shortcode-row-example code {
                background: #f6f7f7;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                color: #1d2327;
                word-break: break-all;
            }
            .shortcode-row-actions {
                flex-shrink: 0;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 10px;
            }
            .shortcode-row-status {
                text-align: right;
            }
            .status-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 4px 10px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 600;
            }
            .status-badge .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
            }
            .status-enabled {
                background: #d1e7dd;
                color: #0f5132;
            }
            .status-enabled .dashicons {
                color: #008a20;
            }
            .status-disabled {
                background: #f8d7da;
                color: #842029;
            }
            .status-disabled .dashicons {
                color: #b32d2e;
            }
            .shortcode-toggle-form {
                margin: 0;
            }
            .shortcode-toggle-switch {
                position: relative;
                display: inline-block;
                width: 44px;
                height: 24px;
            }
            .shortcode-toggle-switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }
            .toggle-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
                border-radius: 24px;
            }
            .toggle-slider:before {
                position: absolute;
                content: "";
                height: 18px;
                width: 18px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }
            .shortcode-toggle-switch input:checked + .toggle-slider {
                background-color: #2271b1;
            }
            .shortcode-toggle-switch input:checked + .toggle-slider:before {
                transform: translateX(20px);
            }
            .shortcode-no-results {
                text-align: center;
                padding: 40px 20px;
                color: #646970;
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 8px;
            }

            /* Responsive */
            @media (max-width: 782px) {
                .shortcode-row-content {
                    flex-direction: column;
                }
                .shortcode-row-actions {
                    align-items: flex-start;
                    width: 100%;
                }
                .shortcode-row-header {
                    flex-direction: column;
                    align-items: flex-start;
                }
            }
        </style>
        <?php
    }

    /**
     * Renderizza JavaScript
     */
    private static function render_scripts(): void
    {
        ?>
        <script>
        (function() {
            const searchInput = document.getElementById('shortcode-search-input');
            const categoryFilters = document.querySelectorAll('.category-filter');
            const shortcodeRows = document.querySelectorAll('.shortcode-row');
            const shortcodeList = document.getElementById('shortcode-list');
            const noResults = document.getElementById('shortcode-no-results');

            // Funzione per filtrare shortcode
            function filterShortcodes() {
                const searchTerm = searchInput.value.toLowerCase();
                const activeCategory = document.querySelector('.category-filter.active')?.dataset.category || 'all';
                let visibleCount = 0;

                shortcodeRows.forEach(row => {
                    const shortcode = row.dataset.shortcode.toLowerCase();
                    const category = row.dataset.category;
                    const description = row.querySelector('.shortcode-row-description')?.textContent.toLowerCase() || '';
                    const label = row.querySelector('.shortcode-row-label')?.textContent.toLowerCase() || '';
                    const aliases = Array.from(row.querySelectorAll('.shortcode-alias-tag')).map(tag => tag.textContent.toLowerCase()).join(' ');
                    const example = row.querySelector('.shortcode-row-example code')?.textContent.toLowerCase() || '';

                    const matchesSearch = !searchTerm || 
                        shortcode.includes(searchTerm) || 
                        description.includes(searchTerm) || 
                        label.includes(searchTerm) ||
                        aliases.includes(searchTerm) ||
                        example.includes(searchTerm);
                    
                    const matchesCategory = activeCategory === 'all' || category === activeCategory;

                    if (matchesSearch && matchesCategory) {
                        row.style.display = 'block';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Mostra/nascondi messaggio nessun risultato
                if (visibleCount === 0) {
                    noResults.style.display = 'block';
                    shortcodeList.style.display = 'none';
                } else {
                    noResults.style.display = 'none';
                    shortcodeList.style.display = 'flex';
                }
            }

            // Event listener per ricerca
            if (searchInput) {
                searchInput.addEventListener('input', filterShortcodes);
            }

            // Event listener per filtri categoria
            categoryFilters.forEach(filter => {
                filter.addEventListener('click', function() {
                    categoryFilters.forEach(f => f.classList.remove('active'));
                    this.classList.add('active');
                    filterShortcodes();
                });
            });
        })();
        </script>
        <?php
    }
}

