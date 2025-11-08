<?php
namespace gik25microdata\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Anteprima dati migrabili da codice hardcoded
 * 
 * Mostra un'anteprima di tutti i dati che possono essere migrati
 * dal codice hardcoded (totaldesign_specific.php) alle tabelle database
 */
class MigrationPreview
{
    /**
     * Inizializza la preview
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
            'Anteprima Migrazione',
            'Anteprima Migrazione',
            'manage_options',
            'revious-microdata-migration-preview',
            [self::class, 'render_admin_page']
        );
    }

    /**
     * Renderizza pagina admin
     */
    public static function render_admin_page(): void
    {
        $migrable_data = self::get_migrable_data();
        ?>
        <div class="wrap">
            <h1>Anteprima Migrazione Dati - Revious Microdata</h1>
            <p>Questo è un'anteprima di tutti i dati che possono essere migrati dal codice hardcoded alle tabelle database.</p>
            
            <div style="margin: 20px 0; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
                <strong>⚠️ Nota:</strong> Questa è solo un'anteprima. Per eseguire la migrazione effettiva, vai su <a href="<?php echo esc_url(admin_url('admin.php?page=revious-microdata-carousels&action=migrate')); ?>">Gestione Caroselli → Migra da Codice Hardcoded</a>.
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
                                            <span style="padding: 3px 8px; background: #f0f0f1; border-radius: 3px; font-size: 11px;">
                                                <?php echo esc_html($item['category']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #646970;">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 15px; padding: 10px; background: #f6f7f7; border-radius: 4px;">
                        <strong>Riepilogo:</strong>
                        <ul style="margin: 5px 0 0 20px;">
                            <li><strong>Totale items:</strong> <?php echo count($collection_data['items']); ?></li>
                            <li><strong>Tipo display:</strong> <?php echo esc_html($collection_data['display_type']); ?></li>
                            <li><strong>Shortcode originale:</strong> <code><?php echo esc_html($collection_data['original_shortcode']); ?></code></li>
                            <li><strong>Shortcode dopo migrazione:</strong> <code>[carousel collection="<?php echo esc_attr($collection_key); ?>"]</code></li>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>

            <div style="margin: 30px 0; padding: 20px; background: #fff; border: 1px solid #c3c4c7;">
                <h2>📊 Riepilogo Generale</h2>
                <ul style="margin-left: 20px;">
                    <li><strong>Collezioni migrabili:</strong> <?php echo count($migrable_data); ?></li>
                    <li><strong>Totale items:</strong> <?php echo array_sum(array_map(function($c) { return count($c['items']); }, $migrable_data)); ?></li>
                    <li><strong>File sorgente:</strong> <code>include/site_specific/totaldesign_specific.php</code></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Ottieni tutti i dati migrabili
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
     * Accorcia URL per display
     */
    private static function shorten_url(string $url, int $max_length = 60): string
    {
        if (strlen($url) <= $max_length) {
            return $url;
        }
        return substr($url, 0, $max_length - 3) . '...';
    }
}

