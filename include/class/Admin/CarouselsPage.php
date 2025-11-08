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
                        <strong>Riepilogo:</strong>
                        <ul style="margin: 5px 0 0 20px;">
                            <li><strong>Totale items:</strong> <?php echo count($collection_data['items']); ?></li>
                            <li><strong>Tipo display:</strong> <?php echo esc_html($collection_data['display_type'] ?? 'carousel'); ?></li>
                            <li><strong>Shortcode originale:</strong> <code><?php echo esc_html($collection_data['original_shortcode'] ?? 'N/A'); ?></code></li>
                            <li><strong>Shortcode dopo migrazione:</strong> <code>[carousel collection="<?php echo esc_attr($collection_key); ?>"]</code></li>
                        </ul>
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
            <?php elseif ($error === 'creation_failed' || $error === 'creation_exception'): ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong>❌ Errore durante la creazione della collezione di test</strong></p>
                    <?php if (isset($_GET['error_detail'])): ?>
                        <p><strong>Dettaglio:</strong> <?php echo esc_html(urldecode($_GET['error_detail'])); ?></p>
                    <?php else: ?>
                        <p>Impossibile creare la collezione di test. Possibili cause:</p>
                        <ul style="margin-left: 20px; list-style: disc;">
                            <li>La tabella database <code>wp_carousel_collections</code> non esiste o non è accessibile</li>
                            <li>Errore di connessione al database</li>
                            <li>Problemi di permessi sul database</li>
                        </ul>
                        <p><strong>Soluzione:</strong> Verifica i log di WordPress (wp-config.php: <code>WP_DEBUG_LOG</code>) o contatta l'amministratore del sistema.</p>
                    <?php endif; ?>
                </div>
            <?php elseif ($error === 'template_not_found'): ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong>❌ Template non trovato</strong></p>
                    <?php if (isset($_GET['error_detail'])): ?>
                        <p><?php echo esc_html(urldecode($_GET['error_detail'])); ?></p>
                    <?php endif; ?>
                    <p>Il template selezionato non esiste più nel database. Seleziona un altro template dalla lista.</p>
                </div>
            <?php elseif ($error === 'invalid_display_type'): ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong>❌ Tipo display non valido</strong></p>
                    <?php if (isset($_GET['error_detail'])): ?>
                        <p><?php echo esc_html(urldecode($_GET['error_detail'])); ?></p>
                    <?php endif; ?>
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
        return [
            'colori' => [
                'name' => 'Colori',
                'display_type' => 'carousel',
                'original_shortcode' => '[link_colori]',
                'items' => self::extract_colori_items(),
            ],
            'programmi3d' => [
                'name' => 'Programmi Grafica 3D',
                'display_type' => 'carousel',
                'original_shortcode' => '[grafica3d]',
                'items' => self::extract_programmi3d_items(),
            ],
            'architetti' => [
                'name' => 'Architetti Famosi',
                'display_type' => 'carousel',
                'original_shortcode' => '[archistar]',
                'items' => self::extract_architetti_items(),
            ],
        ];
    }

    /**
     * Estrae items per collezione Colori
     */
    private static function extract_colori_items(): array
    {
        return [
            // Colori Specifici
            ['title' => 'Color Tortora', 'url' => 'https://www.totaldesign.it/color-tortora-colore-neutro-tendenza/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Rosso', 'url' => 'https://www.totaldesign.it/colore-rosso/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Rosso Bordeaux', 'url' => 'https://www.totaldesign.it/colore-bordeaux/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Rosso Tiziano', 'url' => 'https://www.totaldesign.it/rosso-tiziano/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Verde', 'url' => 'https://www.totaldesign.it/colore-verde/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Verde Acqua', 'url' => 'https://www.totaldesign.it/colore-verde-acqua/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Verde Salvia', 'url' => 'https://www.totaldesign.it/colore-verde-salvia/', 'category' => 'colori-specifici'],
            ['title' => 'Color Petrolio', 'url' => 'https://www.totaldesign.it/color-petrolio-verde/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Verde Tiffany', 'url' => 'https://www.totaldesign.it/verde-tiffany/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Verde Smeraldo', 'url' => 'https://www.totaldesign.it/verde-smeraldo/', 'category' => 'colori-specifici'],
            ['title' => 'Color Turchese', 'url' => 'https://www.totaldesign.it/colore-turchese/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Grigio Chiaro', 'url' => 'https://www.totaldesign.it/grigio-chiaro/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Bianco', 'url' => 'https://www.totaldesign.it/colore-bianco/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Bianco e Nero', 'url' => 'https://www.totaldesign.it/arredare-in-bianco-e-nero/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Rosa Cipria', 'url' => 'https://www.totaldesign.it/colore-rosa-cipria/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Rosa Antico', 'url' => 'https://www.totaldesign.it/colore-rosa-antico/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Giallo', 'url' => 'https://www.totaldesign.it/colore-giallo/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Giallo Ocra', 'url' => 'https://www.totaldesign.it/giallo-ocra/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Oro', 'url' => 'https://www.totaldesign.it/colore-oro/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Ciano', 'url' => 'https://www.totaldesign.it/colore-ciano/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Azzurro', 'url' => 'https://www.totaldesign.it/colore-azzurro/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Corallo', 'url' => 'https://www.totaldesign.it/colore-corallo/', 'category' => 'colori-specifici'],
            ['title' => 'Color Tortora (arredamento)', 'url' => 'https://www.totaldesign.it/color-tortora-e-larredamento-per-interni/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Carta da Zucchero', 'url' => 'https://www.totaldesign.it/colore-carta-da-zucchero/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Beige', 'url' => 'https://www.totaldesign.it/colore-beige/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Lilla', 'url' => 'https://www.totaldesign.it/colore-lilla/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Indaco', 'url' => 'https://www.totaldesign.it/colore-indaco/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Ecrù', 'url' => 'https://www.totaldesign.it/colore-ecru-cose-pareti-divano-mobili-e-abbinamenti/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Avorio', 'url' => 'https://www.totaldesign.it/colore-avorio/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Antracite', 'url' => 'https://www.totaldesign.it/colore-antracite/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Arancione', 'url' => 'https://www.totaldesign.it/colore-arancione/', 'category' => 'colori-specifici'],
            ['title' => 'Pareti grigio perlate', 'url' => 'https://www.totaldesign.it/pareti-grigie-perlato/', 'category' => 'colori-specifici'],
            ['title' => 'Colore grigio perla', 'url' => 'https://www.totaldesign.it/colore-grigio-perla/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Nero', 'url' => 'https://www.totaldesign.it/colore-nero/', 'category' => 'colori-specifici'],
            ['title' => 'Color Porpora', 'url' => 'https://www.totaldesign.it/colore-porpora/', 'category' => 'colori-specifici'],
            ['title' => 'Color Pesca', 'url' => 'https://www.totaldesign.it/color-pesca/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Ambra', 'url' => 'https://www.totaldesign.it/colore-ambra/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Avio', 'url' => 'https://www.totaldesign.it/colore-avio/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Glicine', 'url' => 'https://www.totaldesign.it/colore-glicine/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Malva', 'url' => 'https://www.totaldesign.it/colore-malva/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Celeste', 'url' => 'https://www.totaldesign.it/colore-celeste/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Sabbia', 'url' => 'https://www.totaldesign.it/colore-sabbia/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Bronzo', 'url' => 'https://www.totaldesign.it/colore-bronzo/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Zaffiro', 'url' => 'https://www.totaldesign.it/colore-zaffiro/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Viola', 'url' => 'https://www.totaldesign.it/colore-viola/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Lavanda', 'url' => 'https://www.totaldesign.it/color-lavanda/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Blu', 'url' => 'https://www.totaldesign.it/colore-blu/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Blu Navy', 'url' => 'https://www.totaldesign.it/colore-blu-navy/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Blu Cobalto', 'url' => 'https://www.totaldesign.it/blu-cobalto/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Fucsia', 'url' => 'https://www.totaldesign.it/colore-fucsia/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Ecru', 'url' => 'https://www.totaldesign.it/colore-ecru/', 'category' => 'colori-specifici'],
            ['title' => 'Colore Magenta', 'url' => 'https://www.totaldesign.it/colore-magenta/', 'category' => 'colori-specifici'],
            
            // Colori Pantone
            ['title' => 'Classic Very Peri 2022', 'url' => 'https://www.totaldesign.it/il-very-peri-e-il-colore-dellanno-2022-secondo-pantone/', 'category' => 'pantone'],
            ['title' => 'Classic Giallo Pantone 2021', 'url' => 'https://www.totaldesign.it/colore-pantone-2021/', 'category' => 'pantone'],
            ['title' => 'Classic Blue Pantone 2020', 'url' => 'https://www.totaldesign.it/classic-blue-pantone/', 'category' => 'pantone'],
            ['title' => 'Colori Pantone', 'url' => 'https://www.totaldesign.it/colori-pantone/', 'category' => 'pantone'],
            ['title' => 'Colori Pantone 2016', 'url' => 'https://www.totaldesign.it/colori-pantone-2016/', 'category' => 'pantone'],
            ['title' => 'Colore Ultra Violet', 'url' => 'https://www.totaldesign.it/ultra-violet-inspiration-scopri-come-arredare-la-casa-con-il-colore-pantone-2018/', 'category' => 'pantone'],
            ['title' => 'Total White', 'url' => 'https://www.totaldesign.it/total-white-arredare-in-bianco/', 'category' => 'pantone'],
            
            // Articoli Vari
            ['title' => 'Colori Complementari', 'url' => 'https://www.totaldesign.it/colori-complementari/', 'category' => 'articoli-vari'],
            ['title' => 'Colori Neutri e Freddi', 'url' => 'https://www.totaldesign.it/colori-caldi-freddi-e-neutri/', 'category' => 'articoli-vari'],
            ['title' => 'Colori freddi', 'url' => 'https://www.totaldesign.it/colori-freddi/', 'category' => 'articoli-vari'],
            ['title' => 'Colori Neutri', 'url' => 'https://www.totaldesign.it/colori-neutri/', 'category' => 'articoli-vari'],
            ['title' => 'Abbinamento colori', 'url' => 'https://www.totaldesign.it/abbinamento-colori/', 'category' => 'articoli-vari'],
            ['title' => 'Colori per arredare', 'url' => 'https://www.totaldesign.it/catalogo-colori-pareti/', 'category' => 'articoli-vari'],
            ['title' => 'Colori pareti soggiorno', 'url' => 'https://www.totaldesign.it/colori-pareti-soggiorno/', 'category' => 'articoli-vari'],
            ['title' => 'Colori Pastello', 'url' => 'https://www.totaldesign.it/colori-pastello-per-arredare-la-casa/', 'category' => 'articoli-vari'],
            ['title' => 'Pareti colorate', 'url' => 'https://www.totaldesign.it/pareti-colorate/', 'category' => 'articoli-vari'],
            ['title' => 'Colori arcobaleno', 'url' => 'https://www.totaldesign.it/colori-arcobaleno/', 'category' => 'articoli-vari'],
            ['title' => 'Tonalità di Giallo', 'url' => 'https://www.totaldesign.it/tonalita-di-giallo/', 'category' => 'articoli-vari'],
            ['title' => 'Tonalità di Verde', 'url' => 'https://www.totaldesign.it/tonalita-di-verde/', 'category' => 'articoli-vari'],
        ];
    }

    /**
     * Estrae items per collezione Programmi 3D
     */
    private static function extract_programmi3d_items(): array
    {
        return [
            ['title' => 'Freecad 3D', 'url' => 'https://www.totaldesign.it/freecad/'],
            ['title' => 'Homestyler', 'url' => 'https://www.totaldesign.it/homestyler-2/'],
            ['title' => 'Autodesk Revit', 'url' => 'https://www.totaldesign.it/autodesk-revit/'],
            ['title' => 'Archicad', 'url' => 'https://www.totaldesign.it/archicad/'],
            ['title' => 'Maya 3D', 'url' => 'https://www.totaldesign.it/maya-3d/'],
            ['title' => 'Blender 3D', 'url' => 'https://www.totaldesign.it/blender-3d/'],
            ['title' => 'Librecad', 'url' => 'https://www.totaldesign.it/librecad/'],
            ['title' => 'Draftsight', 'url' => 'https://www.totaldesign.it/draftsight/'],
            ['title' => 'Lumion Grafica 3D', 'url' => 'https://www.totaldesign.it/lumion/'],
            ['title' => 'Rhinoceros', 'url' => 'https://www.totaldesign.it/rhinoceros-mac/'],
            ['title' => 'Schetchup', 'url' => 'https://www.totaldesign.it/sketchup-2/'],
            ['title' => 'Migliori Programmi Gratuiti per la progettazione 3D', 'url' => 'https://www.totaldesign.it/migliori-programmi-gratuiti-per-la-progettazione-3d/'],
        ];
    }

    /**
     * Estrae items per collezione Architetti
     */
    private static function extract_architetti_items(): array
    {
        return [
            ['title' => 'Renzo Piano', 'url' => 'https://www.totaldesign.it/renzo-piano/'],
            ['title' => 'Zaha Hadid', 'url' => 'https://www.totaldesign.it/zaha-hadid/'],
            ['title' => 'Stefano Boeri', 'url' => 'https://www.totaldesign.it/stefano-boeri/'],
            ['title' => 'Massimiliano Fuksas', 'url' => 'https://www.totaldesign.it/massimiliano-fuksas/'],
            ['title' => 'Frank Gehry', 'url' => 'https://www.totaldesign.it/frank-gehry/'],
            ['title' => 'Norman Foster', 'url' => 'https://www.totaldesign.it/norman-foster/'],
            ['title' => 'OMA Rem Koolhaas', 'url' => 'https://www.totaldesign.it/oma-rem-koolhaas/'],
            ['title' => 'Mario Botta', 'url' => 'https://www.totaldesign.it/mario-botta/'],
            ['title' => 'Jean Nouvel', 'url' => 'https://www.totaldesign.it/jean-nouvel/'],
            ['title' => 'Santiago Calatrava', 'url' => 'https://www.totaldesign.it/santiago-calatrava/'],
            ['title' => 'Tadao Ando', 'url' => 'https://www.totaldesign.it/tadao-ando/'],
            ['title' => 'Richard Meier', 'url' => 'https://www.totaldesign.it/richard-meier/'],
            ['title' => 'Daniel Libeskind', 'url' => 'https://www.totaldesign.it/daniel-libeskind/'],
            ['title' => 'Bjarke Ingels', 'url' => 'https://www.totaldesign.it/bjarke-ingels/'],
            ['title' => 'Shigeru Ban', 'url' => 'https://www.totaldesign.it/shigeru-ban/'],
            ['title' => 'Alvaro Siza', 'url' => 'https://www.totaldesign.it/alvaro-siza/'],
            ['title' => 'Oscar Niemeyer', 'url' => 'https://www.totaldesign.it/oscar-niemeyer/'],
            ['title' => 'Le Corbusier', 'url' => 'https://www.totaldesign.it/le-corbusier/'],
            ['title' => 'Frank Lloyd Wright', 'url' => 'https://www.totaldesign.it/frank-lloyd-wright/'],
            ['title' => 'Ludwig Mies van der Rohe', 'url' => 'https://www.totaldesign.it/ludwig-mies-van-der-rohe/'],
            ['title' => 'Antoni Gaudí', 'url' => 'https://www.totaldesign.it/antoni-gaudi/'],
            ['title' => 'Peter Zumthor', 'url' => 'https://www.totaldesign.it/peter-zumthor/'],
            ['title' => 'Herzog & de Meuron', 'url' => 'https://www.totaldesign.it/herzog-de-meuron/'],
            ['title' => 'David Chipperfield', 'url' => 'https://www.totaldesign.it/david-chipperfield/'],
            ['title' => 'Renzo Piano Building Workshop', 'url' => 'https://www.totaldesign.it/renzo-piano-building-workshop/'],
            ['title' => 'Carlo Scarpa', 'url' => 'https://www.totaldesign.it/carlo-scarpa/'],
            ['title' => 'Aldo Rossi', 'url' => 'https://www.totaldesign.it/aldo-rossi/'],
            ['title' => 'Paolo Portoghesi', 'url' => 'https://www.totaldesign.it/paolo-portoghesi/'],
            ['title' => 'Gio Ponti', 'url' => 'https://www.totaldesign.it/gio-ponti/'],
            ['title' => 'Giancarlo De Carlo', 'url' => 'https://www.totaldesign.it/giancarlo-de-carlo/'],
        ];
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

        // Verifica che il template esista
        $template = CarouselTemplates::get_template_by_id($template_id);
        if (!$template) {
            wp_redirect(add_query_arg(['page' => self::PAGE_SLUG, 'tab' => 'test', 'error' => 'template_not_found', 'error_detail' => urlencode('Template ID ' . $template_id . ' non trovato nel database')], admin_url('admin.php')));
            exit;
        }

        // Verifica che display_type sia valido
        $valid_display_types = ['carousel', 'list', 'grid'];
        if (!in_array($display_type, $valid_display_types)) {
            wp_redirect(add_query_arg(['page' => self::PAGE_SLUG, 'tab' => 'test', 'error' => 'invalid_display_type', 'error_detail' => urlencode('Tipo display "' . $display_type . '" non valido. Valori ammessi: ' . implode(', ', $valid_display_types))], admin_url('admin.php')));
            exit;
        }

        // Crea collezione di test
        try {
            $collection_id = CarouselCollections::upsert_collection([
                'collection_key' => 'test-collection',
                'collection_name' => 'Collezione di Test',
                'collection_description' => 'Collezione creata automaticamente per testare template e shortcode',
                'display_type' => $display_type,
                'template_id' => $template_id,
                'is_active' => 1,
            ]);

            if ($collection_id > 0) {
                wp_redirect(add_query_arg(['page' => self::PAGE_SLUG, 'tab' => 'test', 'message' => 'collection_created'], admin_url('admin.php')));
            } else {
                global $wpdb;
                $last_error = $wpdb->last_error;
                $error_message = 'Errore durante l\'inserimento nel database.';
                if (!empty($last_error)) {
                    $error_message .= ' Dettaglio: ' . $last_error;
                } else {
                    $error_message .= ' Verifica che la tabella wp_carousel_collections esista e sia accessibile.';
                }
                wp_redirect(add_query_arg(['page' => self::PAGE_SLUG, 'tab' => 'test', 'error' => 'creation_failed', 'error_detail' => urlencode($error_message)], admin_url('admin.php')));
            }
        } catch (\Exception $e) {
            $error_message = 'Errore: ' . $e->getMessage();
            wp_redirect(add_query_arg(['page' => self::PAGE_SLUG, 'tab' => 'test', 'error' => 'creation_exception', 'error_detail' => urlencode($error_message)], admin_url('admin.php')));
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

