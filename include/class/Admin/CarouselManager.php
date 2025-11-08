<?php
namespace gik25microdata\Admin;

use gik25microdata\Database\CarouselCollections;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gestione collezioni caroselli via interfaccia admin
 * 
 * Permette di:
 * - Visualizzare collezioni esistenti
 * - Creare nuove collezioni
 * - Gestire items delle collezioni
 * - Migrare dati da codice hardcoded
 */
class CarouselManager
{
    /**
     * Inizializza il manager
     */
    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'add_admin_page'], 20);
    }

    /**
     * Aggiungi pagina admin
     */
    public static function add_admin_page(): void
    {
        add_submenu_page(
            'revious-microdata',
            'Gestione Caroselli',
            'Caroselli',
            'manage_options',
            'revious-microdata-carousels',
            [self::class, 'render_admin_page']
        );
    }

    /**
     * Renderizza pagina admin
     */
    public static function render_admin_page(): void
    {
        // Gestisci azioni
        self::handle_actions();
        
        $collections = CarouselCollections::get_active_collections();
        ?>
        <div class="wrap">
            <h1>Gestione Caroselli - Revious Microdata</h1>
            <p>Gestisci le collezioni di caroselli, liste e griglie configurabili via database.</p>
            
            <div style="margin: 20px 0;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=revious-microdata-carousels&action=new')); ?>" class="button button-primary">
                    ➕ Nuova Collezione
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=revious-microdata-carousels&action=migrate')); ?>" class="button">
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
                                <p>Nessuna collezione presente. <a href="<?php echo esc_url(admin_url('admin.php?page=revious-microdata-carousels&action=new')); ?>">Crea la prima collezione</a> o <a href="<?php echo esc_url(admin_url('admin.php?page=revious-microdata-carousels&action=migrate')); ?>">migra da codice hardcoded</a>.</p>
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
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=revious-microdata-carousels&action=edit&id=' . $collection['id'])); ?>" class="button button-small">Modifica</a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=revious-microdata-carousels&action=items&id=' . $collection['id'])); ?>" class="button button-small">Items</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd;">
                <h2>📚 Documentazione</h2>
                <p>Consulta la <a href="<?php echo esc_url(plugin_dir_url(__DIR__) . '../../docs/GENERIC_CAROUSEL.md'); ?>" target="_blank">documentazione completa</a> per ulteriori informazioni.</p>
            </div>
        </div>
        <?php
    }

    /**
     * Gestisci azioni (new, edit, migrate, etc.)
     */
    private static function handle_actions(): void
    {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'migrate':
                self::handle_migrate();
                break;
            case 'migrate_execute':
                self::handle_migrate_execute();
                break;
        }
    }

    /**
     * Gestisci migrazione da codice hardcoded
     */
    private static function handle_migrate(): void
    {
        ?>
        <div class="wrap">
            <h1>Migrazione da Codice Hardcoded</h1>
            <p>Questo strumento migra i link hardcoded da <code>totaldesign_specific.php</code> alle tabelle database.</p>
            
            <div style="margin: 20px 0; padding: 20px; background: #fff3cd; border: 1px solid #ffc107;">
                <h2>⚠️ Attenzione</h2>
                <p>Questa operazione creerà le seguenti collezioni:</p>
                <ul>
                    <li><strong>colori</strong> - Articoli sui colori (da <code>link_colori</code>)</li>
                    <li><strong>programmi-3d</strong> - Programmi di grafica 3D (da <code>grafica3d</code>)</li>
                    <li><strong>architetti</strong> - Architetti (da <code>archistar</code>)</li>
                </ul>
                <p>Dopo la migrazione, potrai sostituire gli shortcode hardcoded con:</p>
                <ul>
                    <li><code>[carousel collection="colori"]</code> invece di <code>[link_colori]</code></li>
                    <li><code>[carousel collection="programmi-3d"]</code> invece di <code>[grafica3d]</code></li>
                    <li><code>[carousel collection="architetti"]</code> invece di <code>[archistar]</code></li>
                </ul>
            </div>
            
            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=revious-microdata-carousels&action=migrate_execute')); ?>">
                <?php wp_nonce_field('migrate_carousels', 'migrate_nonce'); ?>
                <p>
                    <input type="submit" class="button button-primary" value="Esegui Migrazione">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=revious-microdata-carousels')); ?>" class="button">Annulla</a>
                </p>
            </form>
        </div>
        <?php
        exit;
    }

    /**
     * Esegui migrazione
     */
    private static function handle_migrate_execute(): void
    {
        if (!isset($_POST['migrate_nonce']) || !wp_verify_nonce($_POST['migrate_nonce'], 'migrate_carousels')) {
            wp_die('Sicurezza: nonce non valido');
        }

        if (!current_user_can('manage_options')) {
            wp_die('Permessi insufficienti');
        }

        // Migra collezione Colori
        $colori_items = self::extract_colori_items();
        $colori_id = CarouselCollections::migrate_from_hardcoded('colori', $colori_items, 'colori-specifici');
        
        // Migra collezione Programmi 3D
        $programmi3d_items = self::extract_programmi3d_items();
        $programmi3d_id = CarouselCollections::migrate_from_hardcoded('programmi-3d', $programmi3d_items);
        
        // Migra collezione Architetti
        $architetti_items = self::extract_architetti_items();
        $architetti_id = CarouselCollections::migrate_from_hardcoded('architetti', $architetti_items);
        
        // Aggiungi categorie per Colori
        self::add_colori_categories($colori_id);
        
        wp_redirect(admin_url('admin.php?page=revious-microdata-carousels&migrated=1'));
        exit;
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
            ['title' => 'Fucksas', 'url' => 'https://www.totaldesign.it/fucksas/'],
            ['title' => 'Franck O. Gehry', 'url' => 'https://www.totaldesign.it/franck-o-gehry/'],
            ['title' => 'Norman Foster', 'url' => 'https://www.totaldesign.it/norman-foster/'],
            ['title' => 'OMA Rem Koolhaas', 'url' => 'https://www.totaldesign.it/oma-rem-koolhaas/'],
            ['title' => 'Mario Botta', 'url' => 'https://www.totaldesign.it/mario-botta/'],
            ['title' => 'Jean Nouvel', 'url' => 'https://www.totaldesign.it/jean-nouvel/'],
            ['title' => 'Santiago Calatrava', 'url' => 'https://www.totaldesign.it/santiago-calatrava/'],
            ['title' => 'Mario Cucinella', 'url' => 'https://www.totaldesign.it/mario-cucinella/'],
            ['title' => 'MVRDV', 'url' => 'https://www.totaldesign.it/mvrdv/'],
            ['title' => 'Herzog de Meuron', 'url' => 'https://www.totaldesign.it/herzog-de-meuron/'],
            ['title' => 'David Chipperfield', 'url' => 'https://www.totaldesign.it/david-chipperfield/'],
            ['title' => 'Kengo Kuma', 'url' => 'https://www.totaldesign.it/kengo-kuma/'],
            ['title' => 'Matteo Thun', 'url' => 'https://www.totaldesign.it/matteo-thun/'],
            ['title' => 'SANAA', 'url' => 'https://www.totaldesign.it/sanaa/'],
            ['title' => 'Daniel Libeskind', 'url' => 'https://www.totaldesign.it/daniel-libeskind/'],
            ['title' => 'Steven Holl', 'url' => 'https://www.totaldesign.it/steven-holl/'],
            ['title' => 'Richard Meier', 'url' => 'https://www.totaldesign.it/richard-meier/'],
            ['title' => 'SOM', 'url' => 'https://www.totaldesign.it/som/'],
            ['title' => 'Snøhetta', 'url' => 'https://www.totaldesign.it/snohetta/'],
            ['title' => 'Toyo Ito', 'url' => 'https://www.totaldesign.it/toyo-ito/'],
            ['title' => 'Archea Associati', 'url' => 'https://www.totaldesign.it/archea/'],
            ['title' => 'Diller Scofidio + Renfro', 'url' => 'https://www.totaldesign.it/diller-scofidio-renfro/'],
            ['title' => 'Gensler', 'url' => 'https://www.totaldesign.it/gensler/'],
            ['title' => 'Peter Zumthor', 'url' => 'https://www.totaldesign.it/peter-zumthor/'],
            ['title' => 'UNStudio', 'url' => 'https://www.totaldesign.it/unstudio/'],
            ['title' => 'Coop-Himmelblau', 'url' => 'https://www.totaldesign.it/coop-himmelblau/'],
            ['title' => 'Grafton Architects', 'url' => 'https://www.totaldesign.it/grafton-architects/'],
            ['title' => 'Bjarke Ingels Group', 'url' => 'https://www.totaldesign.it/bjarke-ingels-group/'],
            ['title' => 'Heatherwick Studio', 'url' => 'https://www.totaldesign.it/heatherwick-studio/'],
            ['title' => 'Nemesi & Partners', 'url' => 'https://www.totaldesign.it/nemesi-partners/'],
            ['title' => 'Asymptote Architecture', 'url' => 'https://www.totaldesign.it/asymptote-architecture/'],
        ];
    }

    /**
     * Aggiunge categorie per collezione Colori
     */
    private static function add_colori_categories(int $collection_id): void
    {
        // Le categorie sono già incluse negli items estratti
        // Questo metodo può essere esteso per gestire categorie aggiuntive
    }
}

