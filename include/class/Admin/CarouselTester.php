<?php
namespace gik25microdata\Admin;

use gik25microdata\Database\CarouselCollections;
use gik25microdata\Database\CarouselTemplates;
use gik25microdata\Carousel\CarouselTemplateEngine;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Pagina admin per testare collezioni e shortcode caroselli
 */
class CarouselTester
{
    private const PAGE_SLUG = 'revious-microdata-carousel-tester';
    private const MENU_TITLE = 'Test Caroselli';
    private const PAGE_TITLE = 'Test Collezioni e Shortcode';
    private const CAPABILITY = 'manage_options';

    /**
     * Inizializza la pagina admin
     */
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'add_admin_page'], 20);
        add_action('admin_post_carousel_tester_create_test_collection', [self::class, 'handle_create_test_collection']);
        add_action('admin_post_carousel_tester_add_test_items', [self::class, 'handle_add_test_items']);
        add_action('admin_post_carousel_tester_delete_test_collection', [self::class, 'handle_delete_test_collection']);
    }

    /**
     * Aggiunge la pagina admin
     */
    public static function add_admin_page(): void
    {
        // Aggiungi come submenu del menu principale
        // MENU_SLUG è ora pubblico in AdminMenu, quindi possiamo accedervi direttamente
        add_submenu_page(
            AdminMenu::MENU_SLUG,
            self::PAGE_TITLE,
            self::MENU_TITLE,
            self::CAPABILITY,
            self::PAGE_SLUG,
            [self::class, 'render_page']
        );
    }

    /**
     * Renderizza la pagina admin
     */
    public static function render_page(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Non hai i permessi per accedere a questa pagina.'));
        }

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
        <div class="wrap">
            <h1><?php echo esc_html(self::PAGE_TITLE); ?></h1>
            
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
                    <p><strong>Errore!</strong> Impossibile creare la collezione di test.</p>
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
            
            <div class="carousel-tester-container" style="max-width: 1200px;">
                
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
                                    <th scope="row">
                                        <label for="template_id">Template</label>
                                    </th>
                                    <td>
                                        <select name="template_id" id="template_id" required>
                                            <option value="">-- Seleziona Template --</option>
                                            <?php foreach ($templates as $template): ?>
                                                <option value="<?php echo esc_attr($template['id']); ?>">
                                                    <?php echo esc_html($template['template_name']); ?> 
                                                    (<?php echo esc_html($template['template_key']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="description">Scegli il template da usare per questa collezione.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="display_type">Tipo Display</label>
                                    </th>
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
                                <input type="submit" name="submit" class="button button-primary" value="Crea Collezione di Test">
                            </p>
                        </form>
                    <?php else: ?>
                        <div class="notice notice-success inline">
                            <p><strong>Collezione di test esistente:</strong> 
                                <code><?php echo esc_html($test_collection['collection_key']); ?></code>
                                (<?php echo esc_html($test_collection['collection_name']); ?>)
                            </p>
                        </div>
                        
                        <p>
                            <strong>Template:</strong> 
                            <?php 
                            if (!empty($test_collection['template_id'])) {
                                $template = CarouselTemplates::get_template_by_id((int) $test_collection['template_id']);
                                echo $template ? esc_html($template['template_name']) : 'N/A';
                            } else {
                                echo 'Default';
                            }
                            ?>
                        </p>
                        
                        <p>
                            <strong>Items:</strong> <?php echo count($test_items); ?>
                        </p>
                        
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 20px;">
                            <?php wp_nonce_field('carousel_tester_delete_test_collection', 'carousel_tester_nonce'); ?>
                            <input type="hidden" name="action" value="carousel_tester_delete_test_collection">
                            <input type="submit" class="button button-secondary" value="Elimina Collezione di Test" 
                                   onclick="return confirm('Sei sicuro di voler eliminare la collezione di test?');">
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Sezione: Aggiungi Items di Test -->
                <?php if ($test_collection): ?>
                <div class="card" style="margin: 20px 0;">
                    <h2>Aggiungi Items di Test</h2>
                    
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('carousel_tester_add_test_items', 'carousel_tester_nonce'); ?>
                        <input type="hidden" name="action" value="carousel_tester_add_test_items">
                        <input type="hidden" name="collection_id" value="<?php echo esc_attr($test_collection['id']); ?>">
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="test_urls">URLs di Test (uno per riga)</label>
                                </th>
                                <td>
                                    <textarea name="test_urls" id="test_urls" rows="10" cols="80" 
                                              placeholder="https://www.totaldesign.it/colore-rosso/&#10;https://www.totaldesign.it/colore-verde/&#10;https://www.totaldesign.it/colore-blu/"></textarea>
                                    <p class="description">
                                        Inserisci gli URL dei post WordPress da usare come test. 
                                        Il sistema recupererà automaticamente titolo e immagine dal post.
                                    </p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" name="submit" class="button button-primary" value="Aggiungi Items">
                        </p>
                    </form>
                    
                    <?php if (!empty($test_items)): ?>
                        <h3>Items Attuali (<?php echo count($test_items); ?>)</h3>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>Ordine</th>
                                    <th>Titolo</th>
                                    <th>URL</th>
                                    <th>Categoria</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($test_items as $item): ?>
                                    <tr>
                                        <td><?php echo esc_html($item['display_order']); ?></td>
                                        <td><strong><?php echo esc_html($item['item_title']); ?></strong></td>
                                        <td><a href="<?php echo esc_url($item['item_url']); ?>" target="_blank"><?php echo esc_html($item['item_url']); ?></a></td>
                                        <td><?php echo esc_html($item['category'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Sezione: Test Shortcode -->
                <?php if ($test_collection && !empty($test_items)): ?>
                <div class="card" style="margin: 20px 0;">
                    <h2>Test Shortcode</h2>
                    
                    <h3>Shortcode da Usare</h3>
                    <div style="background: #f0f0f1; padding: 15px; border-radius: 4px; margin: 15px 0;">
                        <code style="font-size: 14px;">
                            [carousel collection="test-collection"]
                        </code>
                        <button type="button" class="button button-small" onclick="copyToClipboard('[carousel collection=\"test-collection\"]')" style="margin-left: 10px;">
                            Copia
                        </button>
                    </div>
                    
                    <h3>Varianti Shortcode</h3>
                    <ul>
                        <li>
                            <code>[carousel collection="test-collection" display="list"]</code> - Forza tipo lista
                        </li>
                        <li>
                            <code>[carousel collection="test-collection" display="grid"]</code> - Forza tipo griglia
                        </li>
                        <li>
                            <code>[carousel collection="test-collection" limit="5"]</code> - Limita a 5 items
                        </li>
                        <li>
                            <code>[carousel collection="test-collection" title="Titolo Personalizzato"]</code> - Titolo personalizzato
                        </li>
                    </ul>
                    
                    <h3>Anteprima Rendering</h3>
                    <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin: 15px 0; border-radius: 4px;">
                        <?php
                        // Renderizza la collezione usando il template engine
                        $rendered = CarouselTemplateEngine::render_collection(
                            $test_collection,
                            $test_items,
                            [
                                'title' => 'Anteprima Collezione di Test',
                                'css_class' => '',
                            ]
                        );
                        echo $rendered;
                        ?>
                    </div>
                    
                    <p class="description">
                        <strong>Nota:</strong> L'anteprima mostra come verrà renderizzata la collezione usando il template selezionato.
                        Le immagini vengono recuperate automaticamente dai post WordPress se non specificate.
                    </p>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Shortcode copiato negli appunti!');
            }, function(err) {
                console.error('Errore copia:', err);
            });
        }
        </script>

        <style>
        .carousel-tester-container .card {
            background: #fff;
            border: 1px solid #c3c4c7;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            padding: 20px;
        }
        .carousel-tester-container h2 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #c3c4c7;
        }
        .carousel-tester-container h3 {
            margin-top: 20px;
        }
        </style>
        <?php
    }

    /**
     * Gestisce la creazione della collezione di test
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
            wp_redirect(add_query_arg(['error' => 'template_required'], wp_get_referer()));
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
            wp_redirect(add_query_arg(['message' => 'collection_created'], wp_get_referer()));
        } else {
            wp_redirect(add_query_arg(['error' => 'creation_failed'], wp_get_referer()));
        }
        exit;
    }

    /**
     * Gestisce l'aggiunta di items di test
     */
    public static function handle_add_test_items(): void
    {
        check_admin_referer('carousel_tester_add_test_items', 'carousel_tester_nonce');

        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Non hai i permessi per eseguire questa azione.'));
        }

        $collection_id = isset($_POST['collection_id']) ? (int) $_POST['collection_id'] : 0;
        $test_urls = isset($_POST['test_urls']) ? sanitize_textarea_field($_POST['test_urls']) : '';

        if ($collection_id <= 0 || empty($test_urls)) {
            wp_redirect(add_query_arg(['error' => 'invalid_data'], wp_get_referer()));
            exit;
        }

        // Parse URLs (uno per riga)
        $urls = array_filter(array_map('trim', explode("\n", $test_urls)));
        $added = 0;

        foreach ($urls as $url) {
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            // Recupera dati dal post WordPress
            $post_id = url_to_postid($url);
            $title = '';
            $image_url = null;

            if ($post_id > 0) {
                $post = get_post($post_id);
                if ($post) {
                    $title = $post->post_title;
                    $image_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
                } else {
                    // Se non trova il post, usa l'URL come titolo
                    $title = basename(parse_url($url, PHP_URL_PATH));
                }
            } else {
                // Se non trova il post, usa l'URL come titolo
                $title = basename(parse_url($url, PHP_URL_PATH));
            }

            // Aggiungi item
            $item_id = CarouselCollections::upsert_item([
                'collection_id' => $collection_id,
                'item_title' => $title ?: 'Item senza titolo',
                'item_url' => $url,
                'item_image_url' => $image_url,
                'display_order' => $added,
                'is_active' => 1,
            ]);

            if ($item_id) {
                $added++;
            }
        }

        if ($added > 0) {
            wp_redirect(add_query_arg(['message' => 'items_added', 'count' => $added], wp_get_referer()));
        } else {
            wp_redirect(add_query_arg(['error' => 'no_items_added'], wp_get_referer()));
        }
        exit;
    }

    /**
     * Gestisce l'eliminazione della collezione di test
     */
    public static function handle_delete_test_collection(): void
    {
        check_admin_referer('carousel_tester_delete_test_collection', 'carousel_tester_nonce');

        if (!current_user_can(self::CAPABILITY)) {
            wp_die(__('Non hai i permessi per eseguire questa azione.'));
        }

        $collection = CarouselCollections::get_collection_by_key('test-collection');
        
        if ($collection) {
            global $wpdb;
            $table_items = $wpdb->prefix . 'carousel_items';
            $table_collections = $wpdb->prefix . 'carousel_collections';
            
            // Elimina items
            $wpdb->delete($table_items, ['collection_id' => $collection['id']], ['%d']);
            
            // Elimina collezione
            $wpdb->delete($table_collections, ['id' => $collection['id']], ['%d']);
            
            wp_redirect(add_query_arg(['message' => 'collection_deleted'], wp_get_referer()));
        } else {
            wp_redirect(add_query_arg(['error' => 'collection_not_found'], wp_get_referer()));
        }
        exit;
    }
}

