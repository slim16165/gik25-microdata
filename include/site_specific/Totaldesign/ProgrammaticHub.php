<?php
namespace gik25microdata\site_specific\Totaldesign;

use gik25microdata\Utility\TagHelper;
use WP_Post;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ProgrammaticHub
{
    private const COLOR_LIBRARY = [
        'bianco' => [
            'label' => 'Bianco',
            'keywords' => ['bianco', 'colore bianco'],
            'tone' => ['bianco'],
            'environments' => ['soggiorno', 'camera', 'bagno', 'cucina'],
            'styles' => ['minimal', 'scandinavo'],
            'palettes' => ['monocromatica', 'neutra-accento'],
            'swatches' => ['#f8f8f6', '#eaeaea', '#d6d5d1', '#bfbdb9'],
            'pairings' => ['Bianco + nero opaco', 'Bianco + legno chiaro', 'Bianco + ottone'],
            'materials' => [
                ['label' => 'Legno chiaro', 'search' => 'legno chiaro'],
                ['label' => 'Metallo nero', 'search' => 'metallo nero'],
                ['label' => 'Lino naturale', 'search' => 'lino naturale']
            ],
            'products' => [
                ['label' => 'Vernice lavabile bianco seta', 'search' => 'vernice bianco seta'],
                ['label' => 'Rulli anti-goccia', 'search' => 'rulli pittura professionali'],
                ['label' => 'Tende in lino bianco', 'search' => 'tende lino bianco']
            ],
        ],
        'nero' => [
            'label' => 'Nero',
            'keywords' => ['nero', 'colore nero'],
            'tone' => ['nero'],
            'environments' => ['soggiorno', 'studio', 'camera'],
            'styles' => ['industriale', 'minimal'],
            'palettes' => ['monocromatica', 'neutra-accento'],
            'swatches' => ['#1c1c1c', '#2d2d2d', '#3d3d3d', '#555555'],
            'pairings' => ['Nero + ottone', 'Nero + legno caldo', 'Nero + grigio fumo'],
            'materials' => [
                ['label' => 'Ottone satinato', 'search' => 'ottone arredamento'],
                ['label' => 'Legno noce', 'search' => 'legno noce'],
                ['label' => 'Vetro fumé', 'search' => 'vetro fumé']
            ],
            'products' => [
                ['label' => 'Vernice nero carbone', 'search' => 'vernice nera pareti'],
                ['label' => 'Maniglie in ottone', 'search' => 'maniglie ottone design'],
                ['label' => 'Lampade nere opache', 'search' => 'lampada nera opaca']
            ],
        ],
        'grigio' => [
            'label' => 'Grigio',
            'keywords' => ['grigio', 'grigio perla', 'grigio chiaro'],
            'tone' => ['grigio'],
            'environments' => ['soggiorno', 'cucina', 'studio'],
            'styles' => ['moderno', 'minimal'],
            'palettes' => ['monocromatica', 'neutra-accento'],
            'swatches' => ['#e3e3e3', '#c9c9c9', '#a8a8a8', '#7a7a7a'],
            'pairings' => ['Grigio + rovere', 'Grigio + nero', 'Grigio + verde salvia'],
            'materials' => [
                ['label' => 'Cemento', 'search' => 'cemento resina'],
                ['label' => 'Rovere naturale', 'search' => 'rovere naturale'],
                ['label' => 'Pietra chiara', 'search' => 'pietra chiara interni']
            ],
            'products' => [
                ['label' => 'Vernice effetto cemento', 'search' => 'vernice effetto cemento'],
                ['label' => 'Pannelli acustici grigi', 'search' => 'pannelli fonoassorbenti grigi'],
                ['label' => 'Divano grigio chiaro', 'search' => 'divano grigio design']
            ],
        ],
        'beige' => [
            'label' => 'Beige',
            'keywords' => ['beige', 'colore beige'],
            'tone' => ['beige'],
            'environments' => ['soggiorno', 'camera'],
            'styles' => ['boho', 'classico', 'scandinavo'],
            'palettes' => ['neutra-accento', 'analoghi'],
            'swatches' => ['#f4eadc', '#e1d2b8', '#c9b99b', '#a49272'],
            'pairings' => ['Beige caldo + nero', 'Beige + azzurro polvere', 'Beige + legno chiaro'],
            'materials' => [
                ['label' => 'Rattan', 'search' => 'arredi rattan'],
                ['label' => 'Canapa', 'search' => 'tessuti canapa'],
                ['label' => 'Legno chiaro', 'search' => 'legno chiaro arredo']
            ],
            'products' => [
                ['label' => 'Vernice beige sabbia', 'search' => 'vernice beige'],
                ['label' => 'Tappeto intrecciato', 'search' => 'tappeto juta'],
                ['label' => 'Cuscini color mattone', 'search' => 'cuscini color mattone']
            ],
        ],
        'tortora' => [
            'label' => 'Tortora',
            'keywords' => ['tortora', 'color tortora'],
            'tone' => ['tortora'],
            'environments' => ['soggiorno', 'camera', 'bagno'],
            'styles' => ['classico', 'moderno'],
            'palettes' => ['analoghi', 'neutra-accento'],
            'swatches' => ['#d8d1c6', '#b8aea3', '#9c9186', '#7a6c62'],
            'pairings' => ['Tortora + legno chiaro', 'Tortora + nero opaco', 'Tortora + salvia'],
            'materials' => [
                ['label' => 'Legno chiaro', 'search' => 'legno chiaro'],
                ['label' => 'Ceramica satinata', 'search' => 'ceramica satinata'],
                ['label' => 'Tessili bouclé', 'search' => 'tessuto bouclé']
            ],
            'products' => [
                ['label' => 'Vernice tortora neutro', 'search' => 'vernice tortora'],
                ['label' => 'Plaid bouclé tortora', 'search' => 'plaid bouclé'],
                ['label' => 'Lampade ottone satinato', 'search' => 'lampada ottone satinato']
            ],
        ],
        'verde-salvia' => [
            'label' => 'Verde salvia',
            'keywords' => ['verde salvia', 'colore verde salvia', 'salvia'],
            'tone' => ['verde'],
            'environments' => ['cucina', 'soggiorno', 'camera'],
            'styles' => ['scandinavo', 'boho'],
            'palettes' => ['analoghi', 'neutra-accento'],
            'swatches' => ['#dfe5dd', '#b9caba', '#8fa48f', '#6a816c'],
            'pairings' => ['Verde salvia + ottone', 'Verde salvia + legno miele', 'Salvia + grigio caldo'],
            'materials' => [
                ['label' => 'Legno miele', 'search' => 'legno miele'],
                ['label' => 'Ottone', 'search' => 'ottone lucido'],
                ['label' => 'Ceramica bianca', 'search' => 'ceramica bianca cucina']
            ],
            'products' => [
                ['label' => 'Vernice verde salvia', 'search' => 'vernice verde salvia'],
                ['label' => 'Maniglie ottone spazzolato', 'search' => 'maniglie ottone'],
                ['label' => 'Tessili color crema', 'search' => 'cuscini crema']
            ],
        ],
        'ottanio' => [
            'label' => 'Ottanio',
            'keywords' => ['ottanio', 'color ottanio'],
            'tone' => ['blu', 'verde'],
            'environments' => ['soggiorno', 'studio'],
            'styles' => ['boho', 'moderno'],
            'palettes' => ['complementare', 'analoghi'],
            'swatches' => ['#0f4c5c', '#145d6d', '#1b7081', '#2d889d'],
            'pairings' => ['Ottanio + oro', 'Ottanio + legno scuro', 'Ottanio + rosa cipria'],
            'materials' => [
                ['label' => 'Oro spazzolato', 'search' => 'oro spazzolato'],
                ['label' => 'Velluto', 'search' => 'velluto ottanio'],
                ['label' => 'Legno scuro', 'search' => 'legno scuro arredo']
            ],
            'products' => [
                ['label' => 'Vernice ottanio profondo', 'search' => 'vernice ottanio'],
                ['label' => 'Lampada oro', 'search' => 'lampada ottone'],
                ['label' => 'Cuscini rosa cipria', 'search' => 'cuscini rosa cipria']
            ],
        ],
        'petrolio' => [
            'label' => 'Petrolio',
            'keywords' => ['petrolio', 'verde petrolio'],
            'tone' => ['blu', 'verde'],
            'environments' => ['soggiorno', 'camera'],
            'styles' => ['moderno', 'industriale'],
            'palettes' => ['complementare', 'analoghi'],
            'swatches' => ['#043f47', '#0b5960', '#11747a', '#1c8d95'],
            'pairings' => ['Petrolio + rame', 'Petrolio + tortora', 'Petrolio + legno scuro'],
            'materials' => [
                ['label' => 'Rame spazzolato', 'search' => 'rame spazzolato'],
                ['label' => 'Pelle cognac', 'search' => 'pelle cognac'],
                ['label' => 'Legno scuro', 'search' => 'legno scuro design']
            ],
            'products' => [
                ['label' => 'Vernice verde petrolio', 'search' => 'vernice verde petrolio'],
                ['label' => 'Lampade rame', 'search' => 'lampada rame'],
                ['label' => 'Coperte color tortora', 'search' => 'coperta tortora']
            ],
        ],
        'terracotta' => [
            'label' => 'Terracotta',
            'keywords' => ['terracotta', 'color terracotta'],
            'tone' => ['terracotta'],
            'environments' => ['soggiorno', 'ingresso'],
            'styles' => ['boho', 'mediterraneo'],
            'palettes' => ['analoghi', 'complementare'],
            'swatches' => ['#e6b89c', '#d98f6b', '#c56a3f', '#a44a23'],
            'pairings' => ['Terracotta + blu petrolio', 'Terracotta + beige caldo', 'Terracotta + ottone'],
            'materials' => [
                ['label' => 'Ceramica artigianale', 'search' => 'ceramica terracotta'],
                ['label' => 'Legno naturale', 'search' => 'legno naturale'],
                ['label' => 'Cuoio', 'search' => 'arredi cuoio']
            ],
            'products' => [
                ['label' => 'Vernice terracotta', 'search' => 'vernice terracotta'],
                ['label' => 'Tappeto berbero', 'search' => 'tappeto berbero'],
                ['label' => 'Lampada ottone vintage', 'search' => 'lampada ottone vintage']
            ],
        ],
        'senape' => [
            'label' => 'Senape',
            'keywords' => ['senape', 'giallo senape'],
            'tone' => ['giallo'],
            'environments' => ['soggiorno', 'studio'],
            'styles' => ['retrò', 'boho'],
            'palettes' => ['complementare', 'analoghi'],
            'swatches' => ['#f2c94c', '#e0a800', '#c68800', '#9c6b00'],
            'pairings' => ['Senape + blu notte', 'Senape + grigio caldo', 'Senape + verde oliva'],
            'materials' => [
                ['label' => 'Velluto', 'search' => 'velluto senape'],
                ['label' => 'Legno scuro', 'search' => 'legno scuro'],
                ['label' => 'Metallo nero', 'search' => 'metallo nero arredo']
            ],
            'products' => [
                ['label' => 'Poltrona senape', 'search' => 'poltrona senape'],
                ['label' => 'Plaid grigio caldo', 'search' => 'plaid grigio caldo'],
                ['label' => 'Quadri botanici', 'search' => 'quadri botanici']
            ],
        ],
        'azzurro' => [
            'label' => 'Azzurro',
            'keywords' => ['azzurro', 'colore azzurro', 'celeste'],
            'tone' => ['azzurro'],
            'environments' => ['camera', 'bagno'],
            'styles' => ['scandinavo', 'classico'],
            'palettes' => ['analoghi', 'complementare'],
            'swatches' => ['#d7ecff', '#a3d2ff', '#70b8ff', '#3e8ee0'],
            'pairings' => ['Azzurro + sabbia', 'Azzurro + bianco', 'Azzurro + ottone'],
            'materials' => [
                ['label' => 'Lino azzurro polvere', 'search' => 'lino azzurro'],
                ['label' => 'Canne naturali', 'search' => 'canne bambù'],
                ['label' => 'Ceramica lucida', 'search' => 'piastrelle azzurre']
            ],
            'products' => [
                ['label' => 'Vernice azzurro polvere', 'search' => 'vernice azzurro polvere'],
                ['label' => 'Set asciugamani sabbia', 'search' => 'asciugamani sabbia'],
                ['label' => 'Lampada ottone', 'search' => 'lampada ottone azzurro']
            ],
        ],
        'mattone' => [
            'label' => 'Mattone',
            'keywords' => ['mattone', 'rosso mattone'],
            'tone' => ['rosso'],
            'environments' => ['ingresso', 'soggiorno'],
            'styles' => ['industriale', 'mediterraneo'],
            'palettes' => ['analoghi', 'complementare'],
            'swatches' => ['#d87a5d', '#c65c3a', '#a84324', '#7d2e18'],
            'pairings' => ['Mattone + blu profondo', 'Mattone + grigio caldo', 'Mattone + ottone'],
            'materials' => [
                ['label' => 'Mattoni a vista', 'search' => 'mattoni a vista'],
                ['label' => 'Ferro naturale', 'search' => 'arredo ferro'],
                ['label' => 'Legno recuperato', 'search' => 'legno recupero']
            ],
            'products' => [
                ['label' => 'Vernice rosso mattone', 'search' => 'vernice rosso mattone'],
                ['label' => 'Mensole ferro', 'search' => 'mensole ferro'],
                ['label' => 'Specchio rotondo nero', 'search' => 'specchio nero']
            ],
        ],
    ];
    private const ROOM_GUIDE = [
        'soggiorno' => [
            'label' => 'Soggiorno',
            'keywords' => ['soggiorno', 'living'],
            'ctas' => [
                ['label' => 'Vedi esempi reali', 'slug' => 'colori-pareti-soggiorno'],
                ['label' => 'Scarica palette', 'search' => 'palette soggiorno pdf'],
                ['label' => 'Prodotti consigliati', 'search' => 'vernice soggiorno']
            ],
        ],
        'cucina' => [
            'label' => 'Cucina',
            'keywords' => ['cucina'],
            'ctas' => [
                ['label' => 'Vedi esempi reali', 'slug' => 'colori-pareti-cucina'],
                ['label' => 'Scarica palette', 'search' => 'palette cucina scarica'],
                ['label' => 'Prodotti consigliati', 'search' => 'vernice cucina lavabile']
            ],
        ],
        'camera' => [
            'label' => 'Camera da letto',
            'keywords' => ['camera da letto', 'camera'],
            'ctas' => [
                ['label' => 'Vedi esempi reali', 'slug' => 'colori-pareti-camera-da-letto'],
                ['label' => 'Scarica palette', 'search' => 'palette camera da letto'],
                ['label' => 'Prodotti consigliati', 'search' => 'tessili camera da letto']
            ],
        ],
        'bagno' => [
            'label' => 'Bagno',
            'keywords' => ['bagno'],
            'ctas' => [
                ['label' => 'Vedi esempi reali', 'slug' => 'colori-pareti-bagno'],
                ['label' => 'Scarica palette', 'search' => 'palette bagno'],
                ['label' => 'Prodotti consigliati', 'search' => 'vernice bagno antimuffa']
            ],
        ],
        'ingresso' => [
            'label' => 'Ingresso',
            'keywords' => ['ingresso', 'entrata'],
            'ctas' => [
                ['label' => 'Vedi esempi reali', 'search' => 'colori ingresso'],
                ['label' => 'Scarica palette', 'search' => 'palette ingresso'],
                ['label' => 'Prodotti consigliati', 'search' => 'lampade ingresso design']
            ],
        ],
    ];

    private const IKEA_LINES = [
        'billy' => [
            'label' => 'BILLY',
            'keywords' => ['billy'],
            'categories' => ['librerie', 'vetrine'],
            'accessories' => ['Illuminazione integrata', 'Ante in vetro', 'Piedini design'],
        ],
        'kallax' => [
            'label' => 'KALLAX',
            'keywords' => ['kallax'],
            'categories' => ['moduli cubi', 'postazione studio'],
            'accessories' => ['Cassetti inserti', 'Ruote industriali', 'Piani effetto legno'],
        ],
        'besta' => [
            'label' => 'BESTA',
            'keywords' => ['besta'],
            'categories' => ['parete attrezzata', 'madia'],
            'accessories' => ['Piedini design', 'Maniglie premium', 'Illuminazione LED'],
        ],
        'pax' => [
            'label' => 'PAX / PLATSA',
            'keywords' => ['pax', 'platsa'],
            'categories' => ['guardaroba modulare', 'cabina armadio'],
            'accessories' => ['Organizer cassetti', 'Illuminazione interna', 'Cassetti estraibili'],
        ],
    ];

    private const IKEA_ROOMS = [
        'cucina' => [
            'label' => 'Cucina',
            'needs' => ['salvaspazio', 'organizzazione'],
            'budget' => ['€', '€€'],
            'keywords' => ['cucina ikea', 'metod'],
        ],
        'soggiorno' => [
            'label' => 'Soggiorno',
            'needs' => ['angoli difficili', 'hack'],
            'budget' => ['€', '€€€'],
            'keywords' => ['soggiorno ikea', 'besta', 'kallax'],
        ],
        'camera' => [
            'label' => 'Camera',
            'needs' => ['guardaroba', 'organizzazione'],
            'budget' => ['€€', '€€€'],
            'keywords' => ['camera ikea', 'pax'],
        ],
        'bagno' => [
            'label' => 'Bagno',
            'needs' => ['salvaspazio'],
            'budget' => ['€', '€€'],
            'keywords' => ['bagno ikea', 'godmorgon'],
        ],
        'studio' => [
            'label' => 'Studio',
            'needs' => ['cavi in ordine', 'smart working'],
            'budget' => ['€', '€€'],
            'keywords' => ['studio ikea', 'scrivania bekant'],
        ],
        'ingresso' => [
            'label' => 'Ingresso',
            'needs' => ['angoli difficili', 'organizzazione'],
            'budget' => ['€'],
            'keywords' => ['ingresso ikea', 'hemmes'],
        ],
    ];

    private const INTENT_PATTERNS = [
        'Guide' => '/\bcome\b|guida|idee/i',
        'Migliori/Confronti' => '/miglior[ei]|confronto|vs\b/i',
        'Prezzi/Costi' => '/prezzo|costo|quanto/i',
        'Errori comuni' => '/errore|sbagli|evitare/i',
        'Hack' => '/hack|trasform|DIY|personalizz/i',
    ];

    private static bool $assetsPrinted = false;
    public static function init(): void
    {
        add_action('init', [self::class, 'register_shortcodes']);
    }

    public static function register_shortcodes(): void
    {
        add_shortcode('td_colori_hub', [self::class, 'render_color_hub']);
        add_shortcode('td_ikea_hub', [self::class, 'render_ikea_hub']);
        add_shortcode('td_programmatic_home', [self::class, 'render_programmatic_home']);
        add_shortcode('td_abbinamenti_colore', [self::class, 'render_color_pairings']);
        add_shortcode('td_palette_correlate', [self::class, 'render_related_palettes']);
        add_shortcode('td_colore_stanza', [self::class, 'render_room_related_palettes']);
        add_shortcode('td_prodotti_colore', [self::class, 'render_color_products']);
        add_shortcode('td_lead_box', [self::class, 'render_lead_box']);
        add_shortcode('td_hack_correlati', [self::class, 'render_related_hacks']);
        add_shortcode('td_completa_set', [self::class, 'render_complete_set']);
        add_shortcode('td_color_match_ikea', [self::class, 'render_ikea_color_match']);
    }
    public static function render_color_hub($atts): string
    {
        $output = self::print_assets();

        $atts = shortcode_atts([
            'limit' => 4,
        ], $atts);

        $limit = (int) $atts['limit'];
        if ($limit < 1) {
            $limit = 4;
        }

        $output .= '<section class="td-hub td-color-hub">';
        $output .= '<header class="td-hub__header"><h1>Colori &amp; Palette</h1>';
        $output .= '<p>Esplora le palette più richieste, abbina colori e materiali e scopri idee reali pubblicate su Total Design.</p></header>';

        $output .= self::build_color_filters();

        $output .= '<div class="td-block"><h2>Palette pronte</h2><div class="td-card-grid" data-grid="color-library">';
        foreach (self::COLOR_LIBRARY as $key => $data) {
            $posts = self::get_posts_for_keywords($data['keywords'], $limit);
            $output .= self::build_color_card($key, $data, $posts);
        }
        $output .= '</div></div>';

        $output .= self::build_color_pairing_chips();
        $output .= self::build_materials_block();
        $output .= self::build_room_guides();
        $output .= self::build_trend_block();

        $output .= '</section>';

        return $output;
    }

    public static function render_ikea_hub($atts): string
    {
        $output = self::print_assets();

        $atts = shortcode_atts([
            'limit' => 6,
        ], $atts);

        $limit = (int) $atts['limit'];
        if ($limit < 1) {
            $limit = 6;
        }

        $output .= '<section class="td-hub td-ikea-hub">';
        $output .= '<header class="td-hub__header"><h1>IKEA Guide &amp; Hack</h1>';
        $output .= '<p>Hack, compatibilità e idee salvaspazio basate sugli articoli pubblicati nella sezione IKEA.</p></header>';

        $output .= self::build_ikea_room_filters($limit);
        $output .= self::build_ikea_line_blocks($limit);
        $output .= self::build_ikea_compatibility_block();
        $output .= self::build_ikea_lead_boxes();

        $output .= '</section>';

        return $output;
    }

    public static function render_programmatic_home($atts): string
    {
        $output = self::print_assets();

        $output .= '<section class="td-home">';
        $output .= '<div class="td-home__hero">' . self::build_home_hero() . '</div>';
        $output .= '<div class="td-home__intent"><h2>Esplora per intento</h2>' . self::build_intent_block() . '</div>';
        $output .= '<div class="td-home__trending"><h2>Trending &amp; Seasonal</h2>' . self::build_trending_posts() . '</div>';
        $output .= '<div class="td-home__cross"><h2>Colori ↔ IKEA</h2>' . self::build_cross_link_block() . '</div>';
        $output .= '</section>';

        return $output;
    }
    public static function render_color_pairings($atts): string
    {
        $output = self::print_assets();

        $atts = shortcode_atts([
            'color' => '',
        ], $atts);

        $colorKey = self::normalize_color_key($atts['color']);
        if (!$colorKey) {
            $colorKey = self::detect_color_from_context();
        }

        if (!$colorKey || !isset(self::COLOR_LIBRARY[$colorKey])) {
            return '';
        }

        $output .= '<div class="td-widget td-widget--chips">';
        $output .= '<h3>Abbina questo colore con…</h3><div class="td-chip-row">';
        foreach (self::COLOR_LIBRARY[$colorKey]['pairings'] as $pairing) {
            $url = esc_url(home_url('/?s=' . rawurlencode($pairing)));
            $output .= '<a class="td-chip" href="' . $url . '">' . esc_html($pairing) . '</a>';
        }
        $output .= '</div></div>';

        return $output;
    }

    public static function render_related_palettes($atts): string
    {
        $output = self::print_assets();

        $atts = shortcode_atts([
            'color' => '',
            'limit' => 3,
        ], $atts);

        $colorKey = self::normalize_color_key($atts['color']);
        if (!$colorKey) {
            $colorKey = self::detect_color_from_context();
        }

        if (!$colorKey || !isset(self::COLOR_LIBRARY[$colorKey])) {
            return '';
        }

        $limit = max(1, (int) $atts['limit']);
        $posts = self::get_posts_for_keywords(self::COLOR_LIBRARY[$colorKey]['keywords'], $limit, ['exclude_current' => true]);

        if (empty($posts)) {
            return '';
        }

        $output .= '<div class="td-widget td-widget--palettes"><h3>Palette correlate</h3><div class="td-card-grid">';
        foreach ($posts as $post) {
            $output .= self::build_post_card($post);
        }
        $output .= '</div></div>';

        return $output;
    }

    public static function render_room_related_palettes($atts): string
    {
        $output = self::print_assets();

        $atts = shortcode_atts([
            'room' => '',
            'limit' => 3,
        ], $atts);

        $roomKey = self::normalize_room_key($atts['room']);
        if (!$roomKey) {
            $roomKey = self::detect_room_from_context();
        }

        if (!$roomKey || !isset(self::ROOM_GUIDE[$roomKey])) {
            return '';
        }

        $keywords = array_merge(self::ROOM_GUIDE[$roomKey]['keywords'], ['palette', 'colori']);
        $posts = self::get_posts_for_keywords($keywords, max(1, (int) $atts['limit']), ['exclude_current' => true]);
        if (empty($posts)) {
            return '';
        }

        $output .= '<div class="td-widget td-widget--room"><h3>Nella stanza ' . esc_html(self::ROOM_GUIDE[$roomKey]['label']) . '</h3>';
        $output .= '<div class="td-card-grid">';
        foreach ($posts as $post) {
            $output .= self::build_post_card($post);
        }
        $output .= '</div></div>';

        return $output;
    }
    public static function render_color_products($atts): string
    {
        $output = self::print_assets();

        $atts = shortcode_atts([
            'color' => '',
        ], $atts);

        $colorKey = self::normalize_color_key($atts['color']);
        if (!$colorKey) {
            $colorKey = self::detect_color_from_context();
        }

        if (!$colorKey || !isset(self::COLOR_LIBRARY[$colorKey])) {
            return '';
        }

        $products = self::COLOR_LIBRARY[$colorKey]['products'];
        if (empty($products)) {
            return '';
        }

        $output .= '<div class="td-widget td-widget--products"><h3>Prodotti consigliati</h3><div class="td-card-grid td-card-grid--mini">';
        foreach ($products as $product) {
            $url = esc_url(home_url('/?s=' . rawurlencode($product['search'])));
            $output .= '<a class="td-product-card" href="' . $url . '"><span class="td-product-card__label">' . esc_html($product['label']) . '</span>';
            $output .= '<span class="td-product-card__meta">Ricerca: ' . esc_html($product['search']) . '</span></a>';
        }
        $output .= '</div></div>';

        return $output;
    }

    public static function render_lead_box($atts): string
    {
        $output = self::print_assets();

        $atts = shortcode_atts([
            'type' => 'color',
            'title' => '',
            'cta_label' => '',
            'cta_url' => '',
        ], $atts);

        $defaults = [
            'color' => [
                'title' => 'Consulenza colore 30′',
                'copy' => 'Prenota una sessione rapida con i nostri interior designer per scegliere palette e materiali perfetti.',
                'cta' => 'Richiedi la consulenza',
                'url' => home_url('/contatti/?ref=consulenza-colore'),
            ],
            'ikea' => [
                'title' => 'Progetto stanza IKEA in 48h',
                'copy' => 'Ricevi un progetto completo con lista prodotti, accessori compatibili e moodboard personalizzata.',
                'cta' => 'Richiedi il progetto',
                'url' => home_url('/contatti/?ref=progetto-ikea'),
            ],
        ];

        $type = $atts['type'];
        $config = $defaults[$type] ?? $defaults['color'];

        if (!empty($atts['title'])) {
            $config['title'] = $atts['title'];
        }
        if (!empty($atts['cta_label'])) {
            $config['cta'] = $atts['cta_label'];
        }
        if (!empty($atts['cta_url'])) {
            $config['url'] = $atts['cta_url'];
        }

        $output .= '<div class="td-widget td-widget--lead">';
        $output .= '<h3>' . esc_html($config['title']) . '</h3>';
        $output .= '<p>' . esc_html($config['copy']) . '</p>';
        $output .= '<a class="td-btn" href="' . esc_url($config['url']) . '">' . esc_html($config['cta']) . '</a>';
        $output .= '</div>';

        return $output;
    }

    public static function render_related_hacks($atts): string
    {
        $output = self::print_assets();

        $limit = max(1, (int) ($atts['limit'] ?? 4));

        $lineKey = self::detect_ikea_line_from_context();
        $roomKey = self::detect_room_from_context();

        if (!$lineKey && !$roomKey) {
            return '';
        }

        $keywords = ['ikea'];
        if ($lineKey && isset(self::IKEA_LINES[$lineKey])) {
            $keywords = array_merge($keywords, self::IKEA_LINES[$lineKey]['keywords']);
        }
        if ($roomKey && isset(self::IKEA_ROOMS[$roomKey])) {
            $keywords = array_merge($keywords, self::IKEA_ROOMS[$roomKey]['keywords']);
        }

        $posts = self::get_posts_for_keywords($keywords, $limit, ['exclude_current' => true]);
        if (empty($posts)) {
            return '';
        }

        $output .= '<div class="td-widget td-widget--hacks"><h3>Hack correlati</h3><div class="td-card-grid">';
        foreach ($posts as $post) {
            $output .= self::build_post_card($post);
        }
        $output .= '</div></div>';

        return $output;
    }

    public static function render_complete_set($atts): string
    {
        $output = self::print_assets();

        $lineKey = self::detect_ikea_line_from_context();
        if (!$lineKey || !isset(self::IKEA_LINES[$lineKey])) {
            return '';
        }

        $accessories = self::IKEA_LINES[$lineKey]['accessories'];
        if (empty($accessories)) {
            return '';
        }

        $output .= '<div class="td-widget td-widget--products"><h3>Completa il set</h3><div class="td-card-grid td-card-grid--mini">';
        foreach ($accessories as $accessory) {
            $url = esc_url(home_url('/?s=' . rawurlencode($accessory . ' IKEA')));
            $output .= '<a class="td-product-card" href="' . $url . '"><span class="td-product-card__label">' . esc_html($accessory) . '</span>';
            $output .= '<span class="td-product-card__meta">Suggerimento compatibile</span></a>';
        }
        $output .= '</div></div>';

        return $output;
    }

    public static function render_ikea_color_match($atts): string
    {
        $output = self::print_assets();

        $lineKey = self::detect_ikea_line_from_context();
        if (!$lineKey) {
            return '';
        }

        $colorKeys = self::suggest_colors_for_line($lineKey);
        if (empty($colorKeys)) {
            return '';
        }

        $output .= '<div class="td-widget td-widget--chips"><h3>Color match</h3><div class="td-chip-row">';
        foreach ($colorKeys as $colorKey) {
            if (!isset(self::COLOR_LIBRARY[$colorKey])) {
                continue;
            }
            $label = self::COLOR_LIBRARY[$colorKey]['label'];
            $url = esc_url(home_url('/?s=' . rawurlencode($label . ' IKEA')));
            $output .= '<a class="td-chip" href="' . $url . '">' . esc_html($label) . '</a>';
        }
        $output .= '</div></div>';

        return $output;
    }
    private static function build_color_filters(): string
    {
        $tones = [];
        $environments = [];
        $styles = [];
        $palettes = [];

        foreach (self::COLOR_LIBRARY as $color) {
            $tones = array_merge($tones, $color['tone']);
            $environments = array_merge($environments, $color['environments']);
            $styles = array_merge($styles, $color['styles']);
            $palettes = array_merge($palettes, $color['palettes']);
        }

        $output = '<div class="td-filters">';
        $output .= self::build_filter_group('tone', 'Tono', array_unique($tones));
        $output .= self::build_filter_group('environment', 'Uso / ambiente', array_unique($environments));
        $output .= self::build_filter_group('style', 'Stile', array_unique($styles));
        $output .= self::build_filter_group('palette', 'Palette', array_unique($palettes));
        $output .= '</div>';

        return $output;
    }

    private static function build_filter_group(string $group, string $label, array $values): string
    {
        sort($values);
        $output = '<div class="td-filter-group" data-filter-group="' . esc_attr($group) . '">';
        $output .= '<span class="td-filter-group__label">' . esc_html($label) . '</span>';
        $output .= '<button class="td-filter is-active" data-filter="*">Tutti</button>';
        foreach ($values as $value) {
            if ($value === '') {
                continue;
            }
            $output .= '<button class="td-filter" data-filter="' . esc_attr($value) . '">' . esc_html(ucfirst($value)) . '</button>';
        }
        $output .= '</div>';
        return $output;
    }

    private static function build_color_card(string $key, array $data, array $posts): string
    {
        $classes = self::prepare_data_classes([
            'tone' => $data['tone'],
            'environment' => $data['environments'],
            'style' => $data['styles'],
            'palette' => $data['palettes'],
        ]);

        $output = '<article class="td-card" data-key="' . esc_attr($key) . '" ' . $classes . '>';
        $output .= '<header class="td-card__header"><h3>' . esc_html($data['label']) . '</h3><div class="td-swatch-row">';
        foreach ($data['swatches'] as $swatch) {
            $output .= '<span class="td-swatch" style="--swatch:' . esc_attr($swatch) . '" aria-hidden="true"></span>';
        }
        $output .= '</div></header>';

        if (!empty($posts)) {
            $output .= '<ul class="td-card__list">';
            foreach ($posts as $post) {
                $output .= '<li><a href="' . esc_url(get_permalink($post)) . '">' . esc_html(get_the_title($post)) . '</a></li>';
            }
            $output .= '</ul>';
        } else {
            $output .= '<p class="td-card__empty">Nessun articolo disponibile per il momento.</p>';
        }

        $output .= '</article>';
        return $output;
    }

    private static function build_color_pairing_chips(): string
    {
        $pairs = [];
        foreach (self::COLOR_LIBRARY as $color) {
            foreach ($color['pairings'] as $pair) {
                $pairs[$pair] = $pair;
            }
        }

        if (empty($pairs)) {
            return '';
        }

        $output = '<div class="td-block td-block--chips"><h2>Abbinamenti rapidi</h2><div class="td-chip-row">';
        foreach ($pairs as $pair) {
            $url = esc_url(home_url('/?s=' . rawurlencode($pair)));
            $output .= '<a class="td-chip" href="' . $url . '">' . esc_html($pair) . '</a>';
        }
        $output .= '</div></div>';

        return $output;
    }

    private static function build_materials_block(): string
    {
        $output = '<div class="td-block td-block--materials"><h2>Materiali &amp; texture</h2>';
        foreach (self::COLOR_LIBRARY as $color) {
            if (empty($color['materials'])) {
                continue;
            }
            $output .= '<div class="td-material-row"><h3>' . esc_html($color['label']) . '</h3><div class="td-chip-row">';
            foreach ($color['materials'] as $material) {
                $url = esc_url(home_url('/?s=' . rawurlencode($material['search'])));
                $output .= '<a class="td-chip" href="' . $url . '">' . esc_html($material['label']) . '</a>';
            }
            $output .= '</div></div>';
        }
        $output .= '</div>';
        return $output;
    }

    private static function build_room_guides(): string
    {
        $output = '<div class="td-block td-block--rooms"><h2>Guida alle pareti per stanza</h2><div class="td-card-grid">';
        foreach (self::ROOM_GUIDE as $key => $room) {
            $posts = self::get_posts_for_keywords($room['keywords'], 4);
            $output .= '<article class="td-card td-card--room" data-room="' . esc_attr($key) . '"><h3>' . esc_html($room['label']) . '</h3>';
            if (!empty($posts)) {
                $output .= '<ul class="td-card__list">';
                foreach ($posts as $post) {
                    $output .= '<li><a href="' . esc_url(get_permalink($post)) . '">' . esc_html(get_the_title($post)) . '</a></li>';
                }
                $output .= '</ul>';
            }
            if (!empty($room['ctas'])) {
                $output .= '<div class="td-cta-row">';
                foreach ($room['ctas'] as $cta) {
                    $url = !empty($cta['slug'])
                        ? esc_url(home_url('/' . trim($cta['slug'], '/') . '/'))
                        : esc_url(home_url('/?s=' . rawurlencode($cta['search'] ?? $room['label'])));
                    $output .= '<a class="td-btn td-btn--ghost" href="' . $url . '">' . esc_html($cta['label']) . '</a>';
                }
                $output .= '</div>';
            }
            $output .= '</article>';
        }
        $output .= '</div></div>';
        return $output;
    }

    private static function build_trend_block(): string
    {
        $posts = self::get_trend_posts(6);
        if (empty($posts)) {
            return '';
        }

        $output = '<div class="td-block td-block--trends"><h2>Tavolozza stagionale &amp; trend</h2><div class="td-card-grid">';
        foreach ($posts as $post) {
            $output .= self::build_post_card($post);
        }
        $output .= '</div><div class="td-info"><strong>Alternative economiche:</strong> scopri vernici compatibili e tessili budget-friendly nella sezione prodotti consigliati.</div></div>';

        return $output;
    }
    private static function build_post_card(WP_Post $post): string
    {
        $thumbnail = get_the_post_thumbnail_url($post, 'medium');
        $output = '<article class="td-card td-card--post">';
        if ($thumbnail) {
            $output .= '<a class="td-card__thumb" href="' . esc_url(get_permalink($post)) . '"><img src="' . esc_url($thumbnail) . '" alt="' . esc_attr(get_the_title($post)) . '"></a>';
        }
        $output .= '<div class="td-card__body"><h3><a href="' . esc_url(get_permalink($post)) . '">' . esc_html(get_the_title($post)) . '</a></h3></div>';
        $output .= '</article>';
        return $output;
    }

    private static function build_ikea_room_filters(int $limit): string
    {
        $output = '<div class="td-block td-block--ikea-rooms"><h2>IKEA per stanza</h2>';
        $output .= '<div class="td-filters">';
        $output .= self::build_filter_group('room', 'Stanza', array_map(static fn($room) => $room['label'], self::IKEA_ROOMS));
        $needs = [];
        $budgets = [];
        foreach (self::IKEA_ROOMS as $room) {
            $needs = array_merge($needs, $room['needs']);
            $budgets = array_merge($budgets, $room['budget']);
        }
        $output .= self::build_filter_group('need', 'Esigenza', array_unique($needs));
        $output .= self::build_filter_group('budget', 'Budget', array_unique($budgets));
        $output .= '</div>';

        $output .= '<div class="td-card-grid" data-grid="ikea-rooms">';
        foreach (self::IKEA_ROOMS as $key => $room) {
            $posts = self::get_posts_for_keywords(array_merge(['ikea'], $room['keywords']), $limit);
            $classes = self::prepare_data_classes([
                'room' => [$room['label']],
                'need' => $room['needs'],
                'budget' => $room['budget'],
            ]);
            $output .= '<article class="td-card td-card--room" data-key="' . esc_attr($key) . '" ' . $classes . '><h3>' . esc_html($room['label']) . '</h3>';
            if (!empty($posts)) {
                $output .= '<ul class="td-card__list">';
                foreach ($posts as $post) {
                    $output .= '<li><a href="' . esc_url(get_permalink($post)) . '">' . esc_html(get_the_title($post)) . '</a></li>';
                }
                $output .= '</ul>';
            }
            $output .= '<div class="td-card__tags">';
            foreach ($room['needs'] as $need) {
                $output .= '<span class="td-tag">' . esc_html($need) . '</span>';
            }
            foreach ($room['budget'] as $budget) {
                $output .= '<span class="td-tag">' . esc_html($budget) . '</span>';
            }
            $output .= '</div>';
            $output .= '<a class="td-btn td-btn--ghost" href="' . esc_url(home_url('/?s=' . rawurlencode($room['label'] . ' IKEA hack'))) . '">Hack e guide</a>';
            $output .= '</article>';
        }
        $output .= '</div></div>';

        return $output;
    }

    private static function build_ikea_line_blocks(int $limit): string
    {
        $output = '<div class="td-block td-block--ikea-lines"><h2>Hack per linea</h2><div class="td-card-grid">';
        foreach (self::IKEA_LINES as $key => $line) {
            $posts = self::get_posts_for_keywords(array_merge(['ikea'], $line['keywords']), $limit);
            $output .= '<article class="td-card td-card--line" data-line="' . esc_attr($key) . '"><h3>' . esc_html($line['label']) . '</h3>';
            if (!empty($posts)) {
                $output .= '<ul class="td-card__list">';
                foreach ($posts as $post) {
                    $output .= '<li><a href="' . esc_url(get_permalink($post)) . '">' . esc_html(get_the_title($post)) . '</a></li>';
                }
                $output .= '</ul>';
            }
            if (!empty($line['categories'])) {
                $output .= '<div class="td-card__tags">';
                foreach ($line['categories'] as $category) {
                    $output .= '<span class="td-tag">' . esc_html($category) . '</span>';
                }
                $output .= '</div>';
            }
            $output .= '<a class="td-btn td-btn--ghost" href="' . esc_url(home_url('/?s=' . rawurlencode($line['label'] . ' IKEA hack'))) . '">Vedi tutti</a>';
            $output .= '</article>';
        }
        $output .= '</div></div>';

        return $output;
    }

    private static function build_ikea_compatibility_block(): string
    {
        $items = [
            ['label' => 'Maniglie premium per KALLAX', 'search' => 'maniglie kallax'],
            ['label' => 'Top effetto marmo per BESTA', 'search' => 'top besta marmo'],
            ['label' => 'Illuminazione LED per BILLY', 'search' => 'illuminazione billy'],
            ['label' => 'Accessori guardaroba PAX', 'search' => 'accessori pax'],
        ];

        $output = '<div class="td-block td-block--compatibility"><h2>Compatibilità &amp; alternative</h2><div class="td-card-grid td-card-grid--mini">';
        foreach ($items as $item) {
            $output .= '<a class="td-product-card" href="' . esc_url(home_url('/?s=' . rawurlencode($item['search']))) . '"><span class="td-product-card__label">' . esc_html($item['label']) . '</span><span class="td-product-card__meta">Ricerca guidata</span></a>';
        }
        $output .= '</div><div class="td-info">Suggerimento: incrocia i colori di tendenza con i tuoi moduli IKEA usando il widget "Color match".</div></div>';

        return $output;
    }

    private static function build_ikea_lead_boxes(): string
    {
        return '<div class="td-block td-block--leads">' . self::render_lead_box(['type' => 'ikea']) . self::render_lead_box(['type' => 'color']) . '</div>';
    }

    private static function build_home_hero(): string
    {
        $output = '<div class="td-hero-grid">';
        $output .= '<a class="td-hero" href="' . esc_url(home_url('/colori')) . '"><h2>Colori &amp; Palette</h2><p>Biblioteca aggiornata di palette, materiali e moodboard.</p></a>';
        $output .= '<a class="td-hero" href="' . esc_url(home_url('/ikea')) . '"><h2>IKEA Guide &amp; Hack</h2><p>Hack verificati, compatibilità e idee di personalizzazione.</p></a>';
        $output .= '</div>';
        return $output;
    }

    private static function build_intent_block(): string
    {
        $posts = get_posts([
            'post_type' => 'post',
            'posts_per_page' => 80,
            'orderby' => 'date',
            'order' => 'DESC',
            'suppress_filters' => false,
        ]);

        if (empty($posts)) {
            return '';
        }

        $grouped = [];
        foreach (self::INTENT_PATTERNS as $label => $pattern) {
            $grouped[$label] = [];
        }

        foreach ($posts as $post) {
            $title = $post->post_title;
            foreach (self::INTENT_PATTERNS as $label => $pattern) {
                if (preg_match($pattern, $title)) {
                    $grouped[$label][] = $post;
                    break;
                }
            }
        }

        $output = '<div class="td-intent-grid">';
        foreach ($grouped as $label => $items) {
            if (empty($items)) {
                continue;
            }
            $output .= '<section class="td-intent"><h3>' . esc_html($label) . '</h3><ul>';
            $count = 0;
            foreach ($items as $post) {
                $output .= '<li><a href="' . esc_url(get_permalink($post)) . '">' . esc_html(get_the_title($post)) . '</a></li>';
                $count++;
                if ($count >= 5) {
                    break;
                }
            }
            $output .= '</ul></section>';
        }
        $output .= '</div>';

        return $output;
    }

    private static function build_trending_posts(): string
    {
        $query = new \WP_Query([
            'post_type' => 'post',
            'posts_per_page' => 6,
            'orderby' => 'comment_count',
            'order' => 'DESC',
            'date_query' => [
                [
                    'after' => '60 days ago',
                ],
            ],
        ]);

        if (!$query->have_posts()) {
            return '';
        }

        $output = '<div class="td-card-grid">';
        while ($query->have_posts()) {
            $query->the_post();
            $output .= self::build_post_card(get_post());
        }
        wp_reset_postdata();
        $output .= '</div>';

        return $output;
    }

    private static function build_cross_link_block(): string
    {
        $pairs = [
            ['color' => 'verde-salvia', 'ikea' => 'cucina'],
            ['color' => 'tortora', 'ikea' => 'soggiorno'],
            ['color' => 'ottanio', 'ikea' => 'studio'],
            ['color' => 'beige', 'ikea' => 'camera'],
        ];

        $output = '<div class="td-card-grid td-card-grid--mini">';
        foreach ($pairs as $pair) {
            if (!isset(self::COLOR_LIBRARY[$pair['color']], self::IKEA_ROOMS[$pair['ikea']])) {
                continue;
            }
            $colorLabel = self::COLOR_LIBRARY[$pair['color']]['label'];
            $roomLabel = self::IKEA_ROOMS[$pair['ikea']]['label'];
            $search = $colorLabel . ' ' . $roomLabel . ' IKEA';
            $output .= '<a class="td-product-card" href="' . esc_url(home_url('/?s=' . rawurlencode($search))) . '"><span class="td-product-card__label">' . esc_html($colorLabel . ' in ' . $roomLabel) . '</span><span class="td-product-card__meta">Scopri idee dagli articoli</span></a>';
        }
        $output .= '</div>';

        return $output;
    }
    private static function print_assets(): string
    {
        if (self::$assetsPrinted) {
            return '';
        }
        self::$assetsPrinted = true;

        ob_start();
        ?>
        <style>
            .td-hub, .td-home {font-family:'Inter','Open Sans',sans-serif;display:flex;flex-direction:column;gap:32px;margin:0 auto;max-width:1180px;padding:8px 12px;}
            .td-hub__header h1 {font-size:2.4rem;margin-bottom:.35em;color:#0f172a;}
            .td-hub__header p {color:#475569;max-width:720px;}
            .td-home h2 {font-size:1.8rem;color:#0f172a;}
            .td-block {background:#fff;border-radius:16px;padding:24px;box-shadow:0 12px 30px rgba(15,23,42,.08);}
            .td-block + .td-block {margin-top:16px;}
            .td-card-grid {display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));}
            .td-card-grid--mini {grid-template-columns:repeat(auto-fit,minmax(200px,1fr));}
            .td-card {background:#f8fafc;border-radius:14px;padding:18px;border:1px solid #e2e8f0;display:flex;flex-direction:column;gap:12px;transition:transform .2s ease,box-shadow .2s ease;}
            .td-card:hover {transform:translateY(-4px);box-shadow:0 16px 32px rgba(15,23,42,.12);}
            .td-card__header {display:flex;flex-direction:column;gap:12px;}
            .td-card__header h3 {margin:0;font-size:1.2rem;color:#0f172a;}
            .td-swatch-row {display:flex;gap:6px;}
            .td-swatch {width:32px;height:32px;border-radius:8px;background:var(--swatch);box-shadow:inset 0 0 0 1px rgba(15,23,42,.08);}
            .td-card__list {list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px;}
            .td-card__list a {color:#0f172a;text-decoration:none;font-weight:600;}
            .td-card__list a:hover {text-decoration:underline;}
            .td-card__empty {margin:0;color:#475569;}
            .td-tag {display:inline-block;background:#e2e8f0;color:#0f172a;font-size:.75rem;padding:4px 10px;border-radius:999px;margin:2px 4px 0 0;font-weight:600;}
            .td-info {margin-top:16px;color:#334155;font-size:.95rem;}
            .td-chip-row {display:flex;flex-wrap:wrap;gap:10px;}
            .td-chip {display:inline-flex;align-items:center;justify-content:center;padding:8px 16px;border-radius:999px;background:#e0f2fe;color:#0f172a;font-weight:600;text-decoration:none;transition:.2s;}
            .td-chip:hover {background:#0ea5e9;color:#fff;}
            .td-filters {display:flex;flex-wrap:wrap;gap:16px;margin-bottom:16px;}
            .td-filter-group {display:flex;align-items:center;flex-wrap:wrap;gap:8px;background:#e2e8f0;padding:10px 12px;border-radius:40px;}
            .td-filter-group__label {font-weight:700;text-transform:uppercase;letter-spacing:.08em;font-size:.7rem;color:#1e293b;}
            .td-filter {border:0;background:transparent;padding:6px 14px;border-radius:30px;font-size:.85rem;cursor:pointer;transition:.2s;font-weight:600;color:#1e293b;}
            .td-filter:hover {background:#cbd5f5;}
            .td-filter.is-active {background:#1d4ed8;color:#fff;}
            .td-hero-grid {display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;}
            .td-hero {position:relative;display:block;padding:32px;border-radius:18px;background:linear-gradient(135deg,#1d4ed8,#6366f1);color:#fff;text-decoration:none;box-shadow:0 14px 40px rgba(79,70,229,.2);transition:.3s;}
            .td-hero:nth-child(2) {background:linear-gradient(135deg,#047857,#22d3ee);}
            .td-hero:hover {transform:translateY(-6px);}
            .td-hero h2 {margin:0 0 12px;font-size:1.8rem;color:#fff;}
            .td-hero p {margin:0;font-size:1rem;}
            .td-intent-grid {display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));}
            .td-intent ul {margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:8px;}
            .td-intent a {color:#0f172a;text-decoration:none;font-weight:600;}
            .td-intent a:hover {text-decoration:underline;}
            .td-widget {background:#fff;border-radius:16px;padding:20px;box-shadow:0 10px 28px rgba(15,23,42,.1);margin-bottom:16px;}
            .td-widget h3 {margin-top:0;color:#0f172a;}
            .td-widget--chips .td-chip-row {gap:8px;}
            .td-widget--products .td-card-grid {margin-top:12px;}
            .td-product-card {display:flex;flex-direction:column;gap:4px;padding:16px;border-radius:14px;border:1px solid #cbd5f5;background:#eff6ff;text-decoration:none;color:#0f172a;font-weight:600;transition:.2s;}
            .td-product-card:hover {background:#1d4ed8;color:#fff;}
            .td-product-card__meta {font-size:.8rem;font-weight:500;opacity:.85;}
            .td-btn {display:inline-flex;align-items:center;justify-content:center;padding:10px 22px;border-radius:999px;font-weight:700;text-decoration:none;background:#1d4ed8;color:#fff;transition:.2s;}
            .td-btn:hover {background:#1e40af;color:#fff;}
            .td-btn--ghost {background:#e0f2fe;color:#0f172a;}
            .td-btn--ghost:hover {background:#0ea5e9;color:#fff;}
            .td-cta-row {display:flex;flex-wrap:wrap;gap:10px;margin-top:12px;}
            .td-card__thumb img {width:100%;height:auto;border-radius:12px;}
            @media (max-width:768px){.td-hub,.td-home{padding:8px;} .td-block{padding:18px;} .td-hero{padding:24px;}}
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const grids = document.querySelectorAll('[data-grid]');
                grids.forEach(function(grid){
                    const filterGroups = grid.parentElement.querySelectorAll('.td-filter-group');
                    if (!filterGroups.length) {return;}
                    const cards = grid.querySelectorAll('.td-card');
                    const activeFilters = {};
                    filterGroups.forEach(function(group){
                        const groupKey = group.getAttribute('data-filter-group');
                        activeFilters[groupKey] = '*';
                        group.addEventListener('click', function(evt){
                            if (!(evt.target instanceof HTMLElement)) {return;}
                            if (!evt.target.matches('.td-filter')) {return;}
                            const value = evt.target.getAttribute('data-filter');
                            group.querySelectorAll('.td-filter').forEach(btn => btn.classList.remove('is-active'));
                            evt.target.classList.add('is-active');
                            activeFilters[groupKey] = value || '*';
                            cards.forEach(function(card){
                                let visible = true;
                                for (const [filterKey, filterValue] of Object.entries(activeFilters)) {
                                    if (filterValue === '*' || !filterValue) {continue;}
                                    const dataset = card.dataset[filterKey];
                                    if (!dataset) {visible = false; break;}
                                    const values = dataset.split(' ');
                                    if (!values.includes(filterValue)) {visible = false; break;}
                                }
                                card.style.display = visible ? '' : 'none';
                            });
                        });
                    });
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }
    private static function prepare_data_classes(array $groups): string
    {
        $attributes = [];
        foreach ($groups as $key => $values) {
            $values = array_map(static fn($value) => sanitize_title($value), $values);
            $attributes[] = 'data-' . esc_attr($key) . '="' . esc_attr(implode(' ', $values)) . '"';
        }
        return implode(' ', $attributes);
    }

    private static function get_posts_for_keywords(array $keywords, int $limit, array $options = []): array
    {
        $limit = max(1, $limit);
        $keywords = array_filter(array_unique($keywords));
        if (empty($keywords)) {
            return [];
        }

        $excludeIds = [];
        if (!empty($options['exclude_current'])) {
            $current = self::get_current_post();
            if ($current) {
                $excludeIds[] = $current->ID;
            }
        }

        $posts = [];
        foreach ($keywords as $keyword) {
            $posts = array_merge($posts, self::get_posts_by_tag($keyword));
        }

        if (empty($posts)) {
            $query = new \WP_Query([
                'post_type' => 'post',
                'posts_per_page' => $limit,
                'orderby' => 'date',
                'order' => 'DESC',
                's' => implode(' ', $keywords),
                'post__not_in' => $excludeIds,
            ]);
            return $query->posts;
        }

        $unique = [];
        foreach ($posts as $post) {
            if (!$post instanceof WP_Post) {
                continue;
            }
            if (in_array($post->ID, $excludeIds, true)) {
                continue;
            }
            $unique[$post->ID] = $post;
            if (count($unique) >= $limit) {
                break;
            }
        }

        return array_values($unique);
    }

    private static function get_posts_by_tag(string $term): array
    {
        $posts = [];
        $ids = TagHelper::find_post_id_from_taxonomy($term, 'post_tag');
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $post = get_post($id);
                if ($post instanceof WP_Post && $post->post_status === 'publish') {
                    $posts[] = $post;
                }
            }
        }
        return $posts;
    }

    private static function get_trend_posts(int $limit): array
    {
        $query = new \WP_Query([
            'post_type' => 'post',
            'posts_per_page' => $limit * 2,
            'orderby' => 'date',
            'order' => 'DESC',
            'date_query' => [
                [
                    'after' => '1 year ago',
                ],
            ],
        ]);

        if (!$query->have_posts()) {
            return [];
        }

        $keywords = '/202[4-5]|trend|novità|stagion|palette/i';
        $results = [];
        while ($query->have_posts()) {
            $query->the_post();
            $post = get_post();
            if (!$post instanceof WP_Post) {
                continue;
            }
            if (preg_match($keywords, $post->post_title)) {
                $results[] = $post;
            }
            if (count($results) >= $limit) {
                break;
            }
        }
        wp_reset_postdata();

        return $results;
    }

    private static function normalize_color_key(string $color): ?string
    {
        if (empty($color)) {
            return null;
        }
        $color = sanitize_title($color);
        if (isset(self::COLOR_LIBRARY[$color])) {
            return $color;
        }
        foreach (self::COLOR_LIBRARY as $key => $data) {
            foreach ($data['keywords'] as $keyword) {
                if (sanitize_title($keyword) === $color) {
                    return $key;
                }
            }
        }
        return null;
    }

    private static function normalize_room_key(string $room): ?string
    {
        if (empty($room)) {
            return null;
        }
        $room = sanitize_title($room);
        if (isset(self::ROOM_GUIDE[$room])) {
            return $room;
        }
        foreach (self::ROOM_GUIDE as $key => $data) {
            if (sanitize_title($data['label']) === $room) {
                return $key;
            }
        }
        return null;
    }

    private static function detect_color_from_context(): ?string
    {
        $post = self::get_current_post();
        if (!$post) {
            return null;
        }
        $haystack = strtolower($post->post_title . ' ' . wp_strip_all_tags($post->post_content));
        foreach (self::COLOR_LIBRARY as $key => $data) {
            foreach ($data['keywords'] as $keyword) {
                if (str_contains($haystack, strtolower($keyword))) {
                    return $key;
                }
            }
        }
        return null;
    }

    private static function detect_room_from_context(): ?string
    {
        $post = self::get_current_post();
        if (!$post) {
            return null;
        }
        $haystack = strtolower($post->post_title . ' ' . wp_strip_all_tags($post->post_content));
        foreach (self::ROOM_GUIDE as $key => $room) {
            foreach ($room['keywords'] as $keyword) {
                if (str_contains($haystack, strtolower($keyword))) {
                    return $key;
                }
            }
        }
        return null;
    }

    private static function detect_ikea_line_from_context(): ?string
    {
        $post = self::get_current_post();
        if (!$post) {
            return null;
        }
        $haystack = strtolower($post->post_title . ' ' . wp_strip_all_tags($post->post_content));
        foreach (self::IKEA_LINES as $key => $line) {
            foreach ($line['keywords'] as $keyword) {
                if (str_contains($haystack, strtolower($keyword))) {
                    return $key;
                }
            }
        }
        return null;
    }

    private static function suggest_colors_for_line(string $lineKey): array
    {
        return match ($lineKey) {
            'billy' => ['bianco', 'grigio', 'verde-salvia'],
            'kallax' => ['tortora', 'beige', 'nero'],
            'besta' => ['tortora', 'ottanio', 'petrolio'],
            'pax' => ['bianco', 'senape', 'azzurro'],
            default => ['bianco', 'grigio'],
        };
    }

    private static function get_current_post(): ?WP_Post
    {
        global $post;
        return $post instanceof WP_Post ? $post : null;
    }
}
