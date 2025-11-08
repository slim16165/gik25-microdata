<?php
namespace gik25microdata\Admin;

use gik25microdata\Database\CarouselCollections;
use gik25microdata\Database\CarouselTemplates;
use gik25microdata\Carousel\CarouselTemplateEngine;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pagina unificata per gestione caroselli con tab
 * 
 * Tab:
 * - Gestione Collezioni
 * - Anteprima Migrazione
 * - Test Caroselli
 */
class CarouselsPage
{
    private const PAGE_SLUG = 'revious-microdata-carousels';
    private const CAPABILITY = 'manage_options';

    /**
     * Inizializza la pagina
     */
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'add_admin_page'], 20);
        
        // Hook per azioni
        add_action('admin_post_carousel_tester_create_test_collection', [self::class, 'handle_create_test_collection']);
        add_action('admin_post_carousel_tester_add_test_items', [self::class, 'handle_add_test_items']);
        add_action('admin_post_carousel_tester_delete_test_collection', [self::class, 'handle_delete_test_collection']);
    }

    /**
     * Aggiunge la pagina admin
     */
    public static function add_admin_page(): void
    {
        add_submenu_page(
            AdminMenu::MENU_SLUG,
            'Caroselli',
            'Caroselli',
            self::CAPABILITY,
            self::PAGE_SLUG,
            [self::class, 'render_page']
        );
    }

    /**
     * Renderizza la pagina con tab
     */
    public static function render_page(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Non hai i permessi per accedere a questa pagina.'));
        }

        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'collections';
        
        ?>
        <div class="wrap">
            <h1>Caroselli - Revious Microdata</h1>
            
            <nav class="nav-tab-wrapper" style="margin: 20px 0;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '&tab=collections')); ?>" 
                   class="nav-tab <?php echo $active_tab === 'collections' ? 'nav-tab-active' : ''; ?>">
                    📦 Gestione Collezioni
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '&tab=migration')); ?>" 
                   class="nav-tab <?php echo $active_tab === 'migration' ? 'nav-tab-active' : ''; ?>">
                    🔄 Anteprima Migrazione
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '&tab=test')); ?>" 
                   class="nav-tab <?php echo $active_tab === 'test' ? 'nav-tab-active' : ''; ?>">
                    🧪 Test Caroselli
                </a>
            </nav>

            <div class="tab-content" style="margin-top: 20px;">
                <?php
                switch ($active_tab) {
                    case 'migration':
                        self::render_migration_tab();
                        break;
                    case 'test':
                        self::render_test_tab();
                        break;
                    case 'collections':
                    default:
                        self::render_collections_tab();
                        break;
                }
                ?>
            </div>
        </div>

        <?php
        self::render_styles();
    }

    /**
     * Tab: Gestione Collezioni
     */
    private static function render_collections_tab(): void
    {
        // Gestisci azioni
        self::handle_collections_actions();
        
        $collections = CarouselCollections::get_active_collections();
        ?>
        <div class="carousels-tab-content">
            <p>Gestisci le collezioni di caroselli, liste e griglie configurabili via database.</p>
            
            <div style="margin: 20px 0;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '&tab=collections&action=new')); ?>" class="button button-primary">
                    ➕ Nuova Collezione
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '&tab=migration')); ?>" class="button">
                    🔄 Migra da Codice Hardcoded
                </a>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Chiave</th>
                        <th>Nome</th>
                        <th>Tipo Display</th>
                        <th>Items</th>
                        <th>Shortcode</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($collections)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">
                                <p>Nessuna collezione presente. <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '&tab=collections&action=new')); ?>">Crea la prima collezione</a> o <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '&tab=migration')); ?>">migra da codice hardcoded</a>.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($collections as $collection): ?>
                            <?php
                            $items = CarouselCollections::get_collection_items((int) $collection['id']);
                            $items_count = count($items);
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($collection['collection_key']); ?></strong></td>
                                <td><?php echo esc_html($collection['collection_name']); ?></td>
                                <td><?php echo esc_html($collection['display_type']); ?></td>
                                <td><?php echo $items_count; ?> items</td>
                                <td><code>[carousel collection="<?php echo esc_attr($collection['collection_key']); ?>"]</code></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '&tab=collections&action=edit&id=' . $collection['id'])); ?>" class="button button-small">Modifica</a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '&tab=collections&action=items&id=' . $collection['id'])); ?>" class="button button-small">Items</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Tab: Anteprima Migrazione
     */
    private static function render_migration_tab(): void
    {
        $migrable_data = self::get_migrable_data();
        ?>
        <div class="carousels-tab-content">
            <p>Questo è un'anteprima di tutti i dati che possono essere migrati dal codice hardcoded alle tabelle database.</p>
            
            <div style="margin: 20px 0; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
                <strong>⚠️ Nota:</strong> Questa è solo un'anteprima. Per eseguire la migrazione effettiva, vai su <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '&tab=collections&action=migrate')); ?>">Gestione Collezioni → Migra da Codice Hardcoded</a>.
            </div>

            <?php foreach ($migrable_data as $collection_key => $collection_data): ?>
                <div class="migration-preview-collection" style="margin: 30px 0; padding: 20px; background: #fff; border: 1px solid #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                    <h2 style="margin-top: 0; color: #1d2327;">
                        📦 <?php echo esc_html($collection_data['name']); ?>
                    </h2>
                    
                    <table class="wp-list-table widefat fixed striped" style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th>Titolo</th>
                                <th>URL</th>
                                <th>Categoria</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($collection_data['items'] as $index => $item): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><strong><?php echo esc_html($item['title']); ?></strong></td>
                                    <td>
                                        <a href="<?php echo esc_url($item['url']); ?>" target="_blank" rel="noopener">
                                            <?php echo esc_html(self::shorten_url($item['url'])); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if (!empty($item['category'])): ?>
                                            <span class="category-badge"><?php echo esc_html($item['category']); ?></span>
                                        <?php else: ?>
                                            <span style="color: #999;">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 15px; padding: 15px; background: #f6f7f7; border-radius: 4px;">
                        <strong>Shortcode risultante:</strong>
                        <code style="display: block; margin-top: 5px; padding: 10px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px;">
                            [carousel collection="<?php echo esc_attr($collection_key); ?>"]
                        </code>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Tab: Test Caroselli
     */
    private static function render_test_tab(): void
    {
        // Verifica se esiste una collezione di test
        $test_collection = CarouselCollections::get_collection_by_key('test-collection');
        $test_items = [];
        
        if ($test_collection) {
            $test_items = CarouselCollections::get_collection_items((int) $test_collection['id']);
        }

        // Ottieni template disponibili
        $templates = CarouselTemplates::get_active_templates();

        // Gestisci messaggi di notifica
        $message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
        $error = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';
        $count = isset($_GET['count']) ? (int) $_GET['count'] : 0;
        
        ?>
        <div class="carousels-tab-content">
            <?php if ($message === 'collection_created'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>Successo!</strong> Collezione di test creata con successo.</p>
                </div>
            <?php elseif ($message === 'items_added'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>Successo!</strong> <?php echo esc_html($count); ?> item(s) aggiunto/i alla collezione di test.</p>
                </div>
            <?php elseif ($message === 'collection_deleted'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>Successo!</strong> Collezione di test eliminata.</p>
                </div>
            <?php elseif ($error === 'template_required'): ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong>Errore!</strong> Devi selezionare un template per creare la collezione.</p>
                </div>
            <?php elseif ($error === 'creation_failed'): ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong>Errore!</strong> Impossibile creare la collezione di test. Verifica i log del server per dettagli.</p>
                </div>
            <?php elseif ($error === 'invalid_data'): ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong>Errore!</strong> Dati non validi. Verifica di aver inserito almeno un URL valido.</p>
                </div>
            <?php elseif ($error === 'no_items_added'): ?>
                <div class="notice notice-warning is-dismissible">
                    <p><strong>Attenzione!</strong> Nessun item valido è stato aggiunto. Verifica che gli URL siano corretti.</p>
                </div>
            <?php elseif ($error === 'collection_not_found'): ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong>Errore!</strong> Collezione di test non trovata.</p>
                </div>
            <?php endif; ?>
            
            <!-- Sezione: Crea/Gestisci Collezione di Test -->
            <div class="card" style="margin: 20px 0;">
                <h2>Crea Collezione di Test</h2>
                
                <?php if (!$test_collection): ?>
                    <p>Crea una collezione di test per provare i template e gli shortcode.</p>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('carousel_tester_create_test_collection', 'carousel_tester_nonce'); ?>
                        <input type="hidden" name="action" value="carousel_tester_create_test_collection">
                        
                        <table class="form-table">
                            <tr>
                                <th><label for="template_id">Template</label></th>
                                <td>
                                    <select name="template_id" id="template_id" required>
                                        <option value="">— Seleziona Template —</option>
                                        <?php foreach ($templates as $template): ?>
                                            <option value="<?php echo esc_attr($template['id']); ?>">
                                                <?php echo esc_html($template['template_name']); ?> (<?php echo esc_html($template['template_key']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="display_type">Tipo Display</label></th>
                                <td>
                                    <select name="display_type" id="display_type">
                                        <option value="list">Lista</option>
                                        <option value="grid">Griglia</option>
                                        <option value="carousel">Carosello</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" class="button button-primary" value="Crea Collezione di Test">
                        </p>
                    </form>
                <?php else: ?>
                    <p><strong>Collezione di test attiva:</strong> <?php echo esc_html($test_collection['collection_name']); ?></p>
                    <p>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline;">
                            <?php wp_nonce_field('carousel_tester_delete_test_collection', '_wpnonce'); ?>
                            <input type="hidden" name="action" value="carousel_tester_delete_test_collection">
                            <input type="submit" class="button button-secondary" value="Elimina Collezione di Test" onclick="return confirm('Sei sicuro di voler eliminare la collezione di test?');">
                        </form>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Sezione: Aggiungi Items di Test -->
            <?php if ($test_collection): ?>
                <div class="card" style="margin: 20px 0;">
                    <h2>Aggiungi Items di Test</h2>
                    <p>Aggiungi items alla collezione di test inserendo URL di post WordPress. Il sistema recupererà automaticamente titolo e immagine.</p>
                    
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('carousel_tester_add_test_items', 'carousel_tester_nonce'); ?>
                        <input type="hidden" name="action" value="carousel_tester_add_test_items">
                        <input type="hidden" name="collection_id" value="<?php echo esc_attr($test_collection['id']); ?>">
                        
                        <table class="form-table">
                            <tr>
                                <th><label for="urls">URL Items (uno per riga)</label></th>
                                <td>
                                    <textarea name="urls" id="urls" rows="10" cols="50" placeholder="https://www.totaldesign.it/articolo-1/
https://www.totaldesign.it/articolo-2/" required></textarea>
                                    <p class="description">Inserisci un URL per riga. Il sistema recupererà automaticamente titolo e immagine dal post.</p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" class="button button-primary" value="Aggiungi Items">
                        </p>
                    </form>
                </div>

                <!-- Sezione: Anteprima Shortcode -->
                <?php if (!empty($test_items)): ?>
                    <div class="card" style="margin: 20px 0;">
                        <h2>Anteprima Shortcode</h2>
                        <p>Usa questi shortcode per visualizzare la collezione di test:</p>
                        
                        <div style="margin: 15px 0;">
                            <strong>Shortcode base:</strong>
                            <code style="display: block; margin-top: 5px; padding: 10px; background: #f6f7f7; border: 1px solid #c3c4c7; border-radius: 4px;">
                                [carousel collection="test-collection"]
                            </code>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <strong>Con limit:</strong>
                            <code style="display: block; margin-top: 5px; padding: 10px; background: #f6f7f7; border: 1px solid #c3c4c7; border-radius: 4px;">
                                [carousel collection="test-collection" limit="5"]
                            </code>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <strong>Con title:</strong>
                            <code style="display: block; margin-top: 5px; padding: 10px; background: #f6f7f7; border: 1px solid #c3c4c7; border-radius: 4px;">
                                [carousel collection="test-collection" title="Titolo Collezione"]
                            </code>
                        </div>
                        
                        <div style="margin: 15px 0;">
                            <strong>Items nella collezione:</strong> <?php echo count($test_items); ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Gestisce azioni collezioni
     */
    private static function handle_collections_actions(): void
    {
        // Implementa logica esistente di CarouselManager
        // Per ora lasciamo vuoto, da implementare se necessario
    }

    /**
     * Ottiene dati migrabili
     */
    private static function get_migrable_data(): array
    {
        // Usa metodi esistenti da MigrationPreview
        if (method_exists('\gik25microdata\Database\CarouselCollections', 'extract_colori_items')) {
            $colori_items = CarouselCollections::extract_colori_items();
            $programmi3d_items = CarouselCollections::extract_programmi3d_items();
            $architetti_items = CarouselCollections::extract_architetti_items();
            
            return [
                'colori' => [
                    'name' => 'Colori',
                    'items' => $colori_items,
                ],
                'programmi-3d' => [
                    'name' => 'Programmi 3D',
                    'items' => $programmi3d_items,
                ],
                'architetti' => [
                    'name' => 'Architetti',
                    'items' => $architetti_items,
                ],
            ];
        }
        
        return [];
    }

    /**
     * Accorcia URL per visualizzazione
     */
    private static function shorten_url(string $url): string
    {
        $parsed = parse_url($url);
        $path = isset($parsed['path']) ? $parsed['path'] : '';
        if (strlen($path) > 50) {
            return substr($path, 0, 47) . '...';
        }
        return $path;
    }

    /**
     * Gestisce creazione collezione di test
     */
    public static function handle_create_test_collection(): void
    {
        check_admin_referer('carousel_tester_create_test_collection', 'carousel_tester_nonce');

        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Non hai i permessi per eseguire questa azione.'));
        }

        $template_id = isset($_POST['template_id']) ? (int) $_POST['template_id'] : 0;
        $display_type = isset($_POST['display_type']) ? sanitize_text_field($_POST['display_type']) : 'list';

        if ($template_id <= 0) {
            wp_redirect(add_query_arg(['page' => self::PAGE_SLUG, 'tab' => 'test', 'error' => 'template_required'], admin_url('admin.php')));
            exit;
        }

        // Crea collezione di test
        $collection_id = CarouselCollections::upsert_collection([
            'collection_key' => 'test-collection',
            'collection_name' => 'Collezione di Test',
            'collection_description' => 'Collezione creata automaticamente per testare template e shortcode',
            'display_type' => $display_type,
            'template_id' => $template_id,
            'is_active' => 1,
        ]);

        if ($collection_id) {
            wp_redirect(add_query_arg(['page' => self::PAGE_SLUG, 'tab' => 'test', 'message' => 'collection_created'], admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg(['page' => self::PAGE_SLUG, 'tab' => 'test', 'error' => 'creation_failed'], admin_url('admin.php')));
        }
        exit;
    }

    /**
     * Gestisce aggiunta items di test
     */
    public static function handle_add_test_items(): void
    {
        check_admin_referer('carousel_tester_add_test_items', 'carousel_tester_nonce');

        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Non hai i permessi per eseguire questa azione.'));
        }

        $collection_id = isset($_POST['collection_id']) ? (int) $_POST['collection_id'] : 0;
        $urls_text = isset($_POST['urls']) ? sanitize_textarea_field($_POST['urls']) : '';

        if ($collection_id <= 0 || empty($urls_text)) {
            wp_redirect(add_query_arg(['page' => self::PAGE_SLUG, 'tab' => 'test', 'error' => 'invalid_data'], admin_url('admin.php')));
            exit;
        }

        $urls = array_filter(array_map('trim', explode("\n", $urls_text)));
        $added_count = 0;

        foreach ($urls as $url) {
            if (empty($url)) {
                continue;
            }

            // Recupera post da URL
            $post_id = url_to_postid($url);
            if (!$post_id) {
                continue;
            }

            $post = get_post($post_id);
            if (!$post) {
                continue;
            }

            // Recupera immagine
            $image_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
            if (!$image_url) {
                $image_url = null;
            }

            // Aggiungi item
            $item_id = CarouselCollections::upsert_item([
                'collection_id' => $collection_id,
                'item_title' => $post->post_title,
                'item_url' => $url,
                'item_image_url' => $image_url,
                'item_description' => wp_trim_words($post->post_excerpt, 20),
                'is_active' => 1,
            ]);

            if ($item_id) {
                $added_count++;
            }
        }

        if ($added_count > 0) {
            wp_redirect(add_query_arg(['page' => self::PAGE_SLUG, 'tab' => 'test', 'message' => 'items_added', 'count' => $added_count], admin_url('admin.php')));
        } else {
            wp_redirect(add_query_arg(['page' => self::PAGE_SLUG, 'tab' => 'test', 'error' => 'no_items_added'], admin_url('admin.php')));
        }
        exit;
    }

    /**
     * Gestisce eliminazione collezione di test
     */
    public static function handle_delete_test_collection(): void
    {
        check_admin_referer('carousel_tester_delete_test_collection', '_wpnonce');

        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Non hai i permessi per eseguire questa azione.'));
        }

        $test_collection = CarouselCollections::get_collection_by_key('test-collection');
        if ($test_collection) {
            // Elimina items
            global $wpdb;
            $items_table = $wpdb->prefix . 'carousel_items';
            $wpdb->delete($items_table, ['collection_id' => $test_collection['id']], ['%d']);

            // Elimina collezione
            $collections_table = $wpdb->prefix . 'carousel_collections';
            $result = $wpdb->delete($collections_table, ['id' => $test_collection['id']], ['%d']);
            
            if ($result === false) {
                wp_redirect(add_query_arg(['page' => self::PAGE_SLUG, 'tab' => 'test', 'error' => 'deletion_failed'], admin_url('admin.php')));
                exit;
            }
        }

        wp_redirect(add_query_arg(['page' => self::PAGE_SLUG, 'tab' => 'test', 'message' => 'collection_deleted'], admin_url('admin.php')));
        exit;
    }

    /**
     * Renderizza CSS
     */
    private static function render_styles(): void
    {
        ?>
        <style>
            .carousels-tab-content {
                background: #fff;
                padding: 20px;
                border: 1px solid #c3c4c7;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .category-badge {
                display: inline-block;
                padding: 2px 8px;
                background: #f0f0f1;
                border-radius: 3px;
                font-size: 12px;
                color: #646970;
            }
        </style>
        <?php
    }
}

