<?php
namespace gik25microdata\site_specific\NonsoloDieti;

use gik25microdata\SiteSpecific\SiteSpecificHandler;
use gik25microdata\ListOfPosts\ListOfPostsHelper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Handler per logica site-specific di NonSoloDieti.it
 * 
 * Utilizza le nuove classi unificate per gestire shortcode e liste di link
 * 
 * @package gik25microdata\site_specific\NonsoloDieti
 */
class NonsoloDietiHandler extends SiteSpecificHandler
{
    protected static function getSiteName(): string
    {
        return 'nonsolodiete';
    }
    
    /**
     * Registra tutti gli shortcode del sito
     */
    public static function registerShortcodes(): void
    {
        self::registerShortcode('link_analisi_sangue', [self::class, 'link_analisi_sangue_handler']);
        self::registerShortcode('link_vitamine', [self::class, 'link_vitamine_handler']);
        self::registerShortcode('link_diete', [self::class, 'link_diete_handler']);
        self::registerShortcode('link_diete2', [self::class, 'link_diete_handler2']);
    }
    
    /**
     * Handler per shortcode link_vitamine
     */
    public static function link_vitamine_handler($atts, $content = null): string
    {
        $links = self::getVitamineLinks();
        
        return self::createThumbnailList($links, [
            'title' => 'Lista delle principali vitamine',
            'list_class' => 'thumbnail-list',
            'remove_if_self' => false,
            'with_image' => true,
        ]);
    }
    
    /**
     * Handler per shortcode link_diete
     */
    public static function link_diete_handler($atts, $content = null): string
    {
        $links = self::getDieteLinks();
        
        return self::createThumbnailList($links, [
            'title' => 'Lista delle principali Diete',
            'list_class' => 'thumbnail-list',
            'columns' => 2,
            'remove_if_self' => false,
            'with_image' => true,
        ]);
    }
    
    /**
     * Handler per shortcode link_analisi_sangue
     */
    public static function link_analisi_sangue_handler($atts, $content = null): string
    {
        $html = self::createThumbnailList(
            self::getAnalisiSangueLinks1(),
            ['title' => 'Analisi del Sangue: gli altri valori da tenere sotto controllo']
        );
        
        $html .= self::createSection('Globuli bianchi', self::getAnalisiSangueLinks2());
        $html .= self::createSection('Globuli Rossi', self::getAnalisiSangueLinks3());
        $html .= self::createSection('Piastrine', self::getAnalisiSangueLinks4());
        $html .= self::createSection('Altro', self::getAnalisiSangueLinks5());
        
        return $html;
    }
    
    /**
     * Handler per shortcode link_diete2
     */
    public static function link_diete_handler2($atts, $content = null): string
    {
        $listLayout = isset($atts['list_layout']) ? (int)$atts['list_layout'] : 2;
        $tag = $atts['tag'] ?? 'analisi del sangue';
        
        $l = new ListOfPostsHelper(false, true, false, $listLayout);
        
        $result = '<h3>Posts by tag: <i style="color: maroon;">' . esc_html($tag) . '</i></h3>';
        $result .= $l->GetLinksWithImagesByTag($tag);
        
        return $result;
    }
    
    /**
     * Restituisce lista link vitamine
     */
    private static function getVitamineLinks(): array
    {
        return [
            ['url' => 'https://www.nonsolodiete.it/vitamine-del-gruppo-b/', 'title' => 'Vitamine del gruppo B'],
            ['url' => 'https://www.nonsolodiete.it/vitamina-b1/', 'title' => 'Vitamina B1'],
            ['url' => 'https://www.nonsolodiete.it/vitamina-b5/', 'title' => 'Vitamina B5'],
            ['url' => 'https://www.nonsolodiete.it/piridossina-vitamina-b6/', 'title' => 'Vitamina B6'],
            ['url' => 'https://www.nonsolodiete.it/vitamina-b8/', 'title' => 'Vitamina B8'],
            ['url' => 'https://www.nonsolodiete.it/vitamina-b12/', 'title' => 'Vitamina B12'],
            ['url' => 'https://www.nonsolodiete.it/acido-folico-tutto-quello-che-dovete-sapere/', 'title' => 'Acido Folico'],
            ['url' => 'https://www.nonsolodiete.it/vitamina-d/', 'title' => 'Vitamina D'],
        ];
    }
    
    /**
     * Restituisce lista link diete
     */
    private static function getDieteLinks(): array
    {
        return [
            ['url' => 'https://www.nonsolodiete.it/le-differenti-diete/', 'title' => 'Diete differenti'],
            ['url' => 'https://www.nonsolodiete.it/dieta-10-kg/', 'title' => 'Dieta per perdere 10kg'],
            ['url' => 'https://www.nonsolodiete.it/dieta-chetogenica/', 'title' => 'Dieta chetogenica'],
            ['url' => 'https://www.nonsolodiete.it/dieta-del-supermetabolismo/', 'title' => 'Dieta supermetabolismo'],
            ['url' => 'https://www.nonsolodiete.it/dieta-plank/', 'title' => 'Dieta Plank'],
            ['url' => 'https://www.nonsolodiete.it/dieta-senza-carboidrati/', 'title' => 'Dieta senza carboidrati'],
            ['url' => 'https://www.nonsolodiete.it/dieta-mima-digiuno/', 'title' => 'Dieta mima digiuno'],
            ['url' => 'https://www.nonsolodiete.it/dieta-del-riso-scotti-dietidea/', 'title' => 'Dieta del riso scotti'],
            ['url' => 'https://www.nonsolodiete.it/dieta-lemme/', 'title' => 'Dieta Lemme'],
            ['url' => 'https://www.nonsolodiete.it/dieta-vegana/', 'title' => 'Dieta Vegana'],
            ['url' => 'https://www.nonsolodiete.it/dieta-mediterranea/', 'title' => 'Dieta Mediterranea'],
            ['url' => 'https://www.nonsolodiete.it/dieta-sirt/', 'title' => 'Dieta Sirt'],
            ['url' => 'https://www.nonsolodiete.it/dieta-delle-uova/', 'title' => 'Dieta delle uova'],
            ['url' => 'https://www.nonsolodiete.it/dieta-panzironi/', 'title' => 'Dieta Panzironi'],
            ['url' => 'https://www.nonsolodiete.it/dieta-scarsdale/', 'title' => 'Dieta Scarsdale'],
            ['url' => 'https://www.nonsolodiete.it/dieta-prima-e-dopo-le-feste/', 'title' => 'Dieta Lampo di Natale'],
            ['url' => 'https://www.nonsolodiete.it/dieta-tina-cipollari/', 'title' => 'Dieta di Tina Cipollari'],
        ];
    }
    
    /**
     * Restituisce lista link analisi sangue - sezione 1
     */
    private static function getAnalisiSangueLinks1(): array
    {
        return [
            ['url' => 'https://www.nonsolodiete.it/esame-emocromocitometrico/', 'title' => 'Emocromo'],
            ['url' => 'https://www.nonsolodiete.it/costo-analisi-del-sangue/', 'title' => 'Lista esami del sangue'],
            ['url' => 'https://www.nonsolodiete.it/mcv-alto-o-basso/', 'title' => 'MCV'],
            ['url' => 'https://www.nonsolodiete.it/autoanalisi-sangue/', 'title' => 'Autoanalisi sangue'],
        ];
    }
    
    /**
     * Restituisce lista link analisi sangue - globuli bianchi
     */
    private static function getAnalisiSangueLinks2(): array
    {
        return [
            ['url' => 'https://www.nonsolodiete.it/monociti-macrofagi/', 'title' => 'Monociti'],
            ['url' => 'https://www.nonsolodiete.it/leucociti-alti-wbc/', 'title' => 'Leucociti Alti (Leucocitosi)'],
            ['url' => 'https://www.nonsolodiete.it/globuli-bianchi/', 'title' => 'Globuli bianchi (WBC)'],
            ['url' => 'https://www.nonsolodiete.it/leucopenia/', 'title' => 'Leucociti Bassi(Leucopenia)'],
            ['url' => 'https://www.nonsolodiete.it/granulociti-neutrofili/', 'title' => 'Granulociti neutrofili'],
            ['url' => 'https://www.nonsolodiete.it/linfociti/', 'title' => 'Linfociti (alti, bassi)'],
        ];
    }
    
    /**
     * Restituisce lista link analisi sangue - globuli rossi
     */
    private static function getAnalisiSangueLinks3(): array
    {
        return [
            ['url' => 'https://www.nonsolodiete.it/anemia-aplastica/', 'title' => 'Anemia Aplastica'],
            ['url' => 'https://www.nonsolodiete.it/globuli-rossi/', 'title' => 'Globuli Rossi'],
            ['url' => 'https://www.nonsolodiete.it/reticolociti/', 'title' => 'Reticolociti'],
            ['url' => 'https://www.nonsolodiete.it/ematocrito/', 'title' => 'Ematocrito'],
            ['url' => 'https://www.nonsolodiete.it/rbc/', 'title' => 'RBC'],
            ['url' => 'https://www.nonsolodiete.it/hb/', 'title' => 'Emoglobina (HGB o Hb)'],
            ['url' => 'https://www.nonsolodiete.it/mch/', 'title' => 'MCH', 'comment' => '(contenuto corpuscolare medio di emoglobina)'],
            ['url' => 'https://www.nonsolodiete.it/mchc/', 'title' => 'MCHC', 'comment' => '(concentrazione corpuscolare media di emoglobina)'],
            ['url' => 'https://www.nonsolodiete.it/rdw/', 'title' => 'RDW-CV e RDW-SD', 'comment' => '(variabilità della dimensione o del volume delle cellule dei globuli rossi; SD = deviazione standard; CV = coefficiente di variazione)'],
        ];
    }
    
    /**
     * Restituisce lista link analisi sangue - piastrine
     */
    private static function getAnalisiSangueLinks4(): array
    {
        return [
            ['url' => 'https://www.nonsolodiete.it/piastrine/', 'title' => 'Piastrine'],
            ['url' => 'https://www.nonsolodiete.it/mpv-alto-basso/', 'title' => 'MPV', 'comment' => '(Volume piastrinico medio)'],
            ['url' => 'https://www.nonsolodiete.it/pdw-analisi-del-sangue/', 'title' => 'PDW', 'comment' => '(ampiezza di distribuzione piastrinica)'],
        ];
    }
    
    /**
     * Restituisce lista link analisi sangue - altro
     */
    private static function getAnalisiSangueLinks5(): array
    {
        return [
            ['url' => 'https://www.nonsolodiete.it/creatinina-alta-e-bassa/', 'title' => 'Creatinina'],
            ['url' => 'https://www.nonsolodiete.it/albumina-alta-o-bassa/', 'title' => 'Albumina'],
            ['url' => 'https://www.nonsolodiete.it/enzimi-epatici/', 'title' => 'Enzimi epatici'],
            ['url' => 'https://www.nonsolodiete.it/colesterolo-sintomi-cause/', 'title' => 'Colesterolo'],
            ['url' => 'https://www.nonsolodiete.it/esami-del-sangue-in-gravidanza/', 'title' => 'Analisi del sangue in gravidanza'],
            ['url' => 'https://www.nonsolodiete.it/thc/', 'title' => 'THC'],
            ['url' => 'https://www.nonsolodiete.it/ferritinemia/', 'title' => 'Ferritina'],
            ['url' => 'https://www.nonsolodiete.it/carenza-di-ferro/', 'title' => 'Carenza di ferro'],
            ['url' => 'https://www.nonsolodiete.it/transaminasi/', 'title' => 'Transaminasi'],
        ];
    }
}
