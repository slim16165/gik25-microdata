<?php
declare(strict_types=1);
namespace gik25microdata\site_specific;

use gik25microdata\ListOfPosts\ListOfPostsHelper;
use gik25microdata\ListOfPosts\Types\LinkBase;
use Illuminate\Support\Collection;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Ul;

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

add_shortcode('link_analisi_sangue', __NAMESPACE__ . '\\link_analisi_sangue_handler_2');
add_shortcode('sedi_inps', __NAMESPACE__ . '\\sedi_inps_handler');
add_shortcode('link_vitamine', __NAMESPACE__ . '\\link_vitamine_handler');
add_shortcode('link_diete', __NAMESPACE__ . '\\link_diete_handler');
add_shortcode('link_dimagrimento', __NAMESPACE__ . '\\link_dimagrimento_handler');
add_shortcode('link_tatuaggi', __NAMESPACE__ . '\\link_tatuaggi_handler');

add_action('wp_head', __NAMESPACE__ . '\\add_HeaderScript');
add_action('wp_footer', __NAMESPACE__ . '\\add_FooterScript');

add_filter('the_author', __NAMESPACE__ . '\\its_my_company');

add_filter('elementor/frontend/print_google_fonts', '__return_false');

/**
 * @return void
 */
function add_HeaderScript():void
{
    if (defined('DOING_AJAX')) {
        return;
    }

    //Disabilito adsense su una pagina
    if (!defined('ADVADS_ADS_DISABLED'))
    {
        //Adsense();

        global $post;
        if ($post->ID == 7557)
            define('ADVADS_ADS_DISABLED', true);
    }
}


function Adsense(): void
{
//Google Adsense
    echo <<<TAG
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4057480177165624"
     crossorigin="anonymous"></script>
TAG;
}

function add_FooterScript():void
{
    if (defined('DOING_AJAX')) {
        return;
    }
}


function its_my_company($prova): string
{
    return $prova;
}


/**
 * @param $atts
 * @param null $content
 * @return string
 */
function link_analisi_sangue_handler_2($atts, $content = null): string
{
    $l = new ListOfPostsHelper(false, true, false);

    $result = Html::ul()->class("nicelist")->open();
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.superinformati.com/indici-corpuscolari-quali-emocromo/11328/", "Indici Corpuscolari", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/mcv-volume-corpuscolare-medio/4733/", "MCV", "(volume corpuscolare medio)"));
    $result .= $l->getLinksWithImagesCurrentColumn($collection);
    $result .= Ul::tag()->close();

    $result .= "<h4>Globuli bianchi</h4>";
    $result .= Html::ul()->class("nicelist")->open();
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.superinformati.com/leucociti/11969/", "Leucociti", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/neutrofili/13060/", "Neutrofili", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/globuli-bianchi-bassi/12798/", "Leucopenia", "(Globuli bianchi bassi)"));
    $collection->add(new LinkBase("https://www.superinformati.com/monociti-macrofagi/13038/", "Monocidi Macrofagi", ""));
    $result .= $l->getLinksWithImagesCurrentColumn($collection);
    $result .= Ul::tag()->close();

    #region Globuli Rossi

    $result .= "<h4>Globuli Rossi</h4>";
    $result .= Html::ul()->class("nicelist")->open();
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.superinformati.com/globuli-rossi/12049/", "Globuli rossi", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/reticolociti/20155/", "Reticolociti", "(Globuli rossi non del tutto formati)"));
    $collection->add(new LinkBase("https://www.superinformati.com/mch/11247/", "MCH", "(contenuto corpuscolare medio di emoglobina)"));
    $collection->add(new LinkBase("https://www.superinformati.com/mchc/11315/", "MCHC", "(concentrazione corpuscolare media di emoglobina)"));
    $collection->add(new LinkBase("https://www.superinformati.com/rdw-cv/4709/", "RDW-CV e RDW-SD", "(variabilità della dimensione o del volume delle cellule dei globuli rossi; SD = deviazione standard; CV = coefficiente di variazione)"));
    $collection->add(new LinkBase("https://www.superinformati.com/ves-esami-valori/24528/", "VES", "(velocità di elettrosedimentazione)"));
    $result .= $l->getLinksWithImagesCurrentColumn($collection);
    $result .= Ul::tag()->close();
    #endregion

    #region Piastrine

    $result .= "<h4>Piastrine</h4>";
    $result .= Html::ul()->class("nicelist")->open();
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.superinformati.com/piastrine/12139/", "Piastrine", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/mpv-volume-piastrinico-medio/11119/", "MPV", "(Volume piastrinico medio)"));
    $collection->add(new LinkBase("https://www.superinformati.com/pdw/11324/", "PDW", "(ampiezza di distribuzione piastrinica)"));
    $result .= $l->getLinksWithImagesCurrentColumn($collection);
    $result .= Ul::tag()->close();
    #endregion

    $result .= "<h4>Altro</h4>";
    $result .= Html::ul()->class("nicelist")->open();
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.superinformati.com/emazie-nelle-urine/8115/", "Emazie nelle urine", "(emazie è un sinonimo di globuli rossi)"));
    $collection->add(new LinkBase("https://www.superinformati.com/thc/12796/", "THC nelle urine", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/nuovi-parametri-per-il-livello-del-colesterolo/6110/", "Colesterolo", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/trigliceridi/12422/", "Trigliceridi", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/creatinina/12088/", "Creatinina", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/ferritina/12231/", "Ferritina", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/sideremia/12233/", "Sideremia", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/omocisteina/12249/", "Omocisteina", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/transferrina/12235/", "Transferrina", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/analisi-del-sangue-in-gravidanza/12503/", "Analisi del sangue in gravidanza", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/transaminasi/12525/", "Transaminasi", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/carenza-di-ferro/12541/", "Carenza di ferro", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/gamma-gt-alte-e-basse/25530/", "Gamma GT alte e basse", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/iperglicemia/24650/", "Iperglicemia", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/glicemia/27786/", "Glicemia", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/anemia-falciforme/27231/", "Anemia Falciforme", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/iperomocisteinemia/4969/", "Iperomocisteinemia", ""));
    $result .= $l->getLinksWithImagesCurrentColumn($collection);
    $result .= Ul::tag()->close();

    return $result;
}

function link_dimagrimento_handler($atts, $content = null): string
{
    $l = new ListOfPostsHelper(false, true, false);
    $links_data = [
        [
            'target_url' => "https://www.superinformati.com/dimagrire-pancia/13568/",
            'nome' => "Dimagrire pancia"
        ],
        [
            'target_url' => "https://www.superinformati.com/come-dimagrire-metodi/7066/",
            'nome' => "Come dimagrire"
        ],
        [
            'target_url' => "https://www.superinformati.com/dimagrire-in-menopausa/9867/",
            'nome' => "Dimagrire in Menopausa"
        ],
        [
            'target_url' => "https://www.superinformati.com/i-migliori-integratori-e-farmaci-per-perdere-peso/2607/",
            'nome' => "Integratori per dimagrire"
        ],
        [
            'target_url' => "https://www.superinformati.com/dimagrire-in-fretta-senza-diete-criolipolisi-o-aqualyx/163/",
            'nome' => "Dimagrire in fretta"
        ],
        [
            'target_url' => "https://www.superinformati.com/dimagrire-corsa-programma/6939/",
            'nome' => "Dimagrire correndo"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-in-gravidanza/26184/",
            'nome' => "Dimagrire in gravidanza"
        ],
        [
            'target_url' => "https://www.superinformati.com/non-riesco-a-dimagrire/7968/",
            'nome' => "Non riesco a dimagrire: cause"
        ],
        [
            'target_url' => "https://www.superinformati.com/dimagrire-con-la-cyclette-trucchi-e-programma-per-perdere-peso-pedalando/3812/",
            'nome' => "Dimagrire con la cyclette"
        ],
        [
            'target_url' => "https://www.superinformati.com/come-dimagrire-i-fianchi/13620/",
            'nome' => "Dimagrire i fianchi"
        ],
        [
            'target_url' => "https://www.superinformati.com/dimagrire-in-fretta/19628/",
            'nome' => "Dimagrire 5 o 10 kg"
        ],
        [
            'target_url' => "https://www.superinformati.com/tutti-i-farmaci-e-gli-integratori-efficaci-per-dimagrire-velocemente/3241/",
            'nome' => "Farmaci per dimagrire"
        ],
        [
            'target_url' => "https://www.superinformati.com/cibi-che-fanno-dimagrire-calorie-negative/9015/",
            'nome' => "Cibi che fanno dimagrire"
        ],
        [
            'target_url' => "https://www.superinformati.com/come-dimagrire-le-cosce/13595/",
            'nome' => "Dimagrire cosce"
        ],
        [
            'target_url' => "https://www.superinformati.com/dimagrire-camminando/9090/",
            'nome' => "Dimagrire camminando"
        ],
        [
            'target_url' => "https://www.superinformati.com/dimagrire-velocemente/13625/",
            'nome' => "Dimagrire in una settimana"
        ],
        [
            'target_url' => "https://www.superinformati.com/total-crunch/7287/",
            'nome' => "Dimagrire con Total Crunch"
        ],
        [
            'target_url' => "https://www.superinformati.com/come-allenarsi-a-casa/17153/",
            'nome' => "Allenarsi a casa"
        ],
        [
            'target_url' => "https://www.superinformati.com/come-dimagrisco/20082/",
            'nome' => "Come dimagrisco in fretta?"
        ],
        [
            'target_url' => "https://www.superinformati.com/calcolare-peso-forma/8226/",
            'nome' => "Calcolo peso forma e peso ideale"
        ],
        [
            'target_url' => "https://www.superinformati.com/stretching/26911/",
            'nome' => "Stretching"
        ],
        [
            'target_url' => "https://www.superinformati.com/allenamento-pha/6472/",
            'nome' => "Allenamento PHA"
        ],
        [
            'target_url' => "https://www.superinformati.com/kayla-itsines-bgg/7846/",
            'nome' => "Allenamento BGG"
        ],
        [
            'target_url' => "https://www.superinformati.com/esercizi-anticellulite/8906/",
            'nome' => "Esercizi anticellulite"
        ],
        [
            'target_url' => "https://www.superinformati.com/spinning/25552/",
            'nome' => "Spinning"
        ],
        [
            'target_url' => "https://www.superinformati.com/pillole-dimagranti-davvero-efficaci-esistono/2238/",
            'nome' => "Pillole dimagranti efficaci"
        ],
        [
            'target_url' => "https://www.superinformati.com/home-fitness/15765/",
            'nome' => "Home fitness"
        ],
        [
            'target_url' => "https://www.superinformati.com/perdere-3-kg-in-una-settimana/3398/",
            'nome' => "Perdere 3 kg in una settimana"
        ],
        [
            'target_url' => "https://www.superinformati.com/5-migliori-app-per-allenarsi/17795/",
            'nome' => "App per allenarsi a casa"
        ],
        [
            'target_url' => "https://www.superinformati.com/cellulite-i-10-trattamenti-di-medicina-estetica-piu-efficaci-nel-2015/3027/",
            'nome' => "Come eliminare la cellulite"
        ],
        [
            'target_url' => "https://www.superinformati.com/allenamento-funzionale/25028/",
            'nome' => "Allenamento funzionale"
        ],
        [
            'target_url' => "https://www.superinformati.com/cerotti-per-dimagrire/12073/",
            'nome' => "Cerotti dimagranti"
        ],
        [
            'target_url' => "https://www.superinformati.com/dimagrire-le-braccia/18845/",
            'nome' => "Esercizi braccia"
        ],
        [
            'target_url' => "https://www.superinformati.com/esercizi-addominali/18137/",
            'nome' => "Esercizi addominali"
        ],
        [
            'target_url' => "https://www.superinformati.com/aerobica-e-anaerobica/3231/",
            'nome' => "Attività aerobica e anaerobica"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-perdere-5-kg-in-1-mese/7817/",
            'nome' => "Perdere 5 kg in un mese"
        ],
        [
            'target_url' => "https://www.superinformati.com/esercizi-trx/18987/",
            'nome' => "Esercizi TRX"
        ],
        [
            'target_url' => "https://www.superinformati.com/lo-yoga-modifica-il-genoma/7092/",
            'nome' => "Yoga"
        ],
        [
            'target_url' => "https://www.superinformati.com/massa-magra/18306/",
            'nome' => "Massa magra"
        ],
        [
            'target_url' => "https://www.superinformati.com/5-consigli-per-allenarsi-come-un-professionista/2883/",
            'nome' => "Allenarsi in maniera efficace"
        ],
        [
            'target_url' => "https://www.superinformati.com/meditazione/23143/",
            'nome' => "Meditazione"
        ],
        [
            'target_url' => "https://www.superinformati.com/massa-corporea-indice/22125/",
            'nome' => "Indice di Massa corporea"
        ],
        [
            'target_url' => "https://www.superinformati.com/misurare-il-grasso-corporeo-per-dimagrire-migliorando-le-diete/127/",
            'nome' => "Massa grassa"
        ],
        [
            'target_url' => "https://www.superinformati.com/perdere-peso-e-pancia-piatta-velocemente/3306/",
            'nome' => "Sgonfiare la pancia"
        ],
        [
            'target_url' => "https://www.superinformati.com/come-rassodare-il-seno/14988/",
            'nome' => "Rassodare il seno"
        ],
        [
            'target_url' => "https://www.superinformati.com/tabata-training/3184/",
            'nome' => "Allenamento Tabata"
        ],
        [
            'target_url' => "https://www.superinformati.com/hiit/20565/",
            'nome' => "Allenamento HIIT"
        ],
        [
            'target_url' => "https://www.superinformati.com/integratori-termogenici/3721/",
            'nome' => "Integratori termogenici"
        ],
        [
            'target_url' => "https://www.superinformati.com/integratori-palestra/19189/",
            'nome' => "Integratori palestra"
        ],
        [
            'target_url' => "https://www.superinformati.com/bcaa-gli-aminoacidi-ramificati-leucina-isoleucina-e-valina/6345/",
            'nome' => "BCAA"
        ],
        [
            'target_url' => "https://www.superinformati.com/come-allenarsi-con-manubri/19104/",
            'nome' => "Allenarsi con i manubri"
        ],
        [
            'target_url' => "https://www.superinformati.com/esercizi-corpo-libero-casa/18797/",
            'nome' => "Esercizi a corpo libero"
        ],
        [
            'target_url' => "https://www.superinformati.com/allenarsi-con-elastici/18325/",
            'nome' => "Allenarsi con gli elastici"
        ],
        [
            'target_url' => "https://www.superinformati.com/allenare-glutei-a-casa/18139/",
            'nome' => "Allenare i glutei"
        ],
        [
            'target_url' => "https://www.superinformati.com/camminata-veloce/16678/",
            'nome' => "Camminata Veloce"
        ],
        [
            'target_url' => "https://www.superinformati.com/bruciare-i-grassi/16664/",
            'nome' => "Bruciare i grassi velocemente"
        ],
        [
            'target_url' => "https://www.superinformati.com/perdere-peso-in-una-settimana/16540/",
            'nome' => "Come perdere peso in una settimana"
        ],
        [
            'target_url' => "https://www.superinformati.com/panca-palestra/15800/",
            'nome' => "Panca da palestra"
        ],
        [
            'target_url' => "https://www.superinformati.com/panca-addominale/15794/",
            'nome' => "Panca addominali"
        ],
        [
            'target_url' => "https://www.superinformati.com/cellulite-rimedi-naturali/15847/",
            'nome' => "Rimedi Naturali cellulite"
        ],
        [
            'target_url' => "https://www.superinformati.com/smagliature-cause/2840/",
            'nome' => "Smagliature"
        ],
        [
            'target_url' => "https://www.superinformati.com/come-eliminare-le-smagliature/4234/",
            'nome' => "Come eliminare le smagliature"
        ],
        [
            'target_url' => "https://www.superinformati.com/cellulite-i-10-trattamenti-di-medicina-estetica-piu-efficaci-nel-2015/3027/",
            'nome' => "Trattamenti contro la cellulite"
        ],
        [
            'target_url' => "https://www.superinformati.com/cellulite-le-cause-i-rimedi-le-novita-per-la-cura-nel-2018/2997/",
            'nome' => "Cellulite"
        ],
        [
            'target_url' => "https://www.superinformati.com/allenamento-bici/6017/",
            'nome' => "Allenamento Bici per dimagrire"
        ],
        [
            'target_url' => "https://www.superinformati.com/ginnastica-per-dimagrire-10-kg/28895/",
            'nome' => "Ginnastica per dimagrire 10 kg"
        ],
        [
            'target_url' => "https://www.superinformati.com/esercizi-schiena/29764/",
            'nome' => "Esercizi per la schiena"
        ]
    ];

    $result = printList($l, $links_data, "Lista dei principali metodi per dimagrire e tonificare", "thumbnail-list");

    return $result;
}

function link_vitamine_handler($atts, $content = null): string
{
    $l = new ListOfPostsHelper(false, true, false);
    $links_data = [
        [
            'target_url' => "https://www.superinformati.com/vitamina-a/10741/",
            'nome' => "Vitamina A"
        ],
        [
            'target_url' => "https://www.superinformati.com/vitamine-gruppo-b/4459/",
            'nome' => "Vitamine del gruppo B"
        ],
        [
            'target_url' => "https://www.superinformati.com/vitamina-b1/10623/",
            'nome' => "Vitamina B1"
        ],
        [
            'target_url' => "https://www.superinformati.com/vitamina-b2/10641/",
            'nome' => "Vitamina B2"
        ],
        [
            'target_url' => "https://www.superinformati.com/vitamina-b3/25258/",
            'nome' => "Vitamina B3"
        ],
        [
            'target_url' => "https://www.superinformati.com/vitamina-b5/25396/",
            'nome' => "Vitamina B5"
        ],
        [
            'target_url' => "https://www.superinformati.com/vitamina-b6/8536/",
            'nome' => "Vitamina B6"
        ],
        [
            'target_url' => "https://www.superinformati.com/vitamina-b8/25440/",
            'nome' => "Vitamina B8"
        ],
        [
            'target_url' => "https://www.superinformati.com/vitamina-b9/8569/",
            'nome' => "Vitamina B9"
        ],
        [
            'target_url' => "https://www.superinformati.com/vitamina-b12/6188/",
            'nome' => "Vitamina B12"
        ],
        [
            'target_url' => "https://www.superinformati.com/vitamina-c/10866/",
            'nome' => "Vitamina C"
        ],
        [
            'target_url' => "https://www.superinformati.com/vitamina-d-caratteristiche/8822/",
            'nome' => "Vitamina"
        ],
        [
            'target_url' => "https://www.superinformati.com/carenza-vitamina-d/8810/",
            'nome' => "Vitamina D"
        ],
        [
            'target_url' => "https://www.superinformati.com/vitamina-e/8920/",
            'nome' => "Vitamina E"
        ],
        [
            'target_url' => "https://www.superinformati.com/vitamina-k/8950/",
            'nome' => "Vitamina K"
        ],
        [
            'target_url' => "https://www.superinformati.com/vitamina-k2/8964/",
            'nome' => "Vitamina K2"
        ]
    ];

    $result = printList($l, $links_data, "Lista delle principali vitamine", "thumbnail-list");
    return $result;
}

/**
 * @param ListOfPostsHelper $l
 * @param array $links_data
 * @param $title
 * @param $ulClass
 * @return string
 */
function printList(ListOfPostsHelper $l, array $links_data, $title, $ulClass): string
{
    $result = Html::h3($title);
    $result .= Html::div()->class($ulClass)->open();
    $result .= Html::ul()->class($ulClass)->open();
    $result .= $l->GetLinksWithImages($links_data);
    $result .= Html::ul()->close();
    $result .= Html::div()->close();


    //$result = H3::tag()->content($title)->render();
//    $result .= Html::div("", ["class" => $ulClass])->open();
//    $result .= Div::tag()->Class('thumbnail-list')->open();
//    $result .= Ul::tag()->close();
//    $result .= Div::tag()->close();
//    $result .= Html::closeTag("ul");

    return $result;
}

function link_tatuaggi_handler($atts, $content = null): string
{
    $l = new ListOfPostsHelper(false, true, false);
    $links_data = [
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-femminili/7575/",
            'nome' => "Tatuaggi Femminili"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-belli/29257/",
            'nome' => "Tatuaggi belli 2021"
        ],
        [
            'target_url' => "https://www.superinformati.com/mandala-tattoo/29885/",
            'nome' => "Mandala Tattoo"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-new-school/18504/",
            'nome' => "Tatuaggi New School Tattoo"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-scritte/7513/",
            'nome' => "Tatuaggi Scritte"
        ],
        [
            'target_url' => "https://www.superinformati.com/catalogo-tatuaggi-tanti-vari-e-da-poter-sfogliare/5973/",
            'nome' => "Catalogo Tatuaggi 2021"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-old-school/7482/",
            'nome' => "Tatuaggi Old School Tattoo"
        ],
        [
            'target_url' => "https://www.superinformati.com/larte-del-tatuaggio-soggetti-legati-alla-natura/4875/",
            'nome' => "L'arte del Tatuaggio "
        ],
        [
            'target_url' => "https://www.superinformati.com/pixel-tattoos-tatuaggio-2-0/5794/",
            'nome' => "Pixel Tattoos"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggio-uomo/17448/",
            'nome' => "Tatuaggio Uomo"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggio-joker/5479/",
            'nome' => "Tatuaggio Joker"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggio-ancora/29249/",
            'nome' => "Tatuaggio ancora"
        ],
        [
            'target_url' => "https://www.superinformati.com/veliero-old-school/7777/",
            'nome' => "Tatuaggio veliero"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggio-chicano/29645/",
            'nome' => "Tatuaggio Chicano"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-donna/15387/",
            'nome' => "Tatuaggi Donna"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-lettere/23857/",
            'nome' => "Tatuaggi Lettere"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-tribali/7646/",
            'nome' => "Tatuaggi Tribali"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-giapponesi/28136/",
            'nome' => "Tatuaggi Giapponesi"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-colorati/29328/",
            'nome' => "Tatuaggi colorati"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-maori/7502/",
            'nome' => "Tatuaggi Maori"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-piccoli/7557/",
            'nome' => "Tatuaggi Piccoli"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-polinesiani/23788/",
            'nome' => "Tatuaggi Polinesiani"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-spalla/13351/",
            'nome' => "Tatuaggi Spalla"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggio-yin-e-yang/5441/",
            'nome' => "Yin e Yang"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-animali/29318/",
            'nome' => "Tatuaggi animali"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-nomi/23849/",
            'nome' => "Tatuaggi Nomi"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-fiori-di-ciliegio/4455/",
            'nome' => "Tatuaggi Fiori Tattoo"
        ],
        [
            'target_url' => "https://www.superinformati.com/microdermal/26371/",
            'nome' => "Microdermal, il piercing sotto pelle"
        ],
        [
            'target_url' => "https://www.superinformati.com/rimuovere-un-tatuaggio/5316/",
            'nome' => "Rimozione tatuaggi"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-numeri-romani/5125/",
            'nome' => "Tatuaggi e numeri romani"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-di-coppia/29911/",
            'nome' => "Tatuaggi di coppia"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-farfalle/29643/",
            'nome' => "Tatuaggi farfalle"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggio-piede/30026/",
            'nome' => "Tatuaggio piede"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-stilizzati/30164/",
            'nome' => "Tatuaggi stilizzati"
        ],
        [
            'target_url' => "https://www.superinformati.com/tartaruga-maori/30304/",
            'nome' => "Tatuaggio tartaruga maori"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggi-amicizia-idee-uomo-donna/29824/",
            'nome' => "Tatuaggi amicizia"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggio-carpa-koi/30505/",
            'nome' => "Tatuaggio Carpa Koi"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggio-infinito/30507/",
            'nome' => "Tatuaggio Infinito"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggio-braccio/30683/",
            'nome' => "Tatuaggio braccio"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggio-croce/30674/",
            'nome' => "Tatuaggio croce"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggio-gufo/30837/",
            'nome' => "Tatuaggio gufo"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggio-fenice/30881/",
            'nome' => "Tatuaggio Fenice"
        ],
        [
            'target_url' => "https://www.superinformati.com/tatuaggio-diamante/30887/",
            'nome' => "Tatuaggio diamante"
        ]
    ];

    $result = printList($l, $links_data, "Articoli sui tatuaggi", "thumbnail-list");
    return $result;
}

function link_diete_handler($atts, $content = null): string
{
    $l = new ListOfPostsHelper(false, true, false);
    $links_data = [

        [
            'target_url' => "https://www.superinformati.com/dieta-alcalina/27138/",
            'nome' => "Dieta Alcalina"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-dottor-nowzaradan/12891/",
            'nome' => "Dieta Dott. Nowzaradan"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-detox-menu-settimanale/5518/",
            'nome' => "Dieta Detox"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-scarsdale/3802/",
            'nome' => "Dieta Scarsdale"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-atkins/3960/",
            'nome' => "Dieta Atkins"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-digiuno-intermittente-16-8/14580/",
            'nome' => "Dieta Digiuno intermittente"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-ipocalorica/22933/",
            'nome' => "Dieta Ipocalorica"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-louise-parker-menu-schema/10093/",
            'nome' => "Dieta Louise Parker"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-carbs-lover/3882/",
            'nome' => "Dieta Carb's Lover"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-disintossicante-fegato/9290/",
            'nome' => "Dieta disintossicante fegato"
        ],
        [
            'target_url' => "https://www.superinformati.com/la-dieta-vegana/4057/",
            'nome' => "Dieta Vegana"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-iposodica-menu/26515/",
            'nome' => "Dieta Iposodica"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-lemme-4-kg-2-giorni/3874/",
            'nome' => "Dieta Lemme"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-lampo/26326/",
            'nome' => "Dieta Lampo"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-anticellulite/26563/",
            'nome' => "Dieta anticellulite"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-del-riso/16199/",
            'nome' => "Dieta del riso"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-a-punti/26206/",
            'nome' => "Dieta a punti"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-veloce/19847/",
            'nome' => "Dieta veloce"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-mediterranea-e-cibi-sani/3578/",
            'nome' => "Dieta Mediterranea"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-in-gravidanza/26184/",
            'nome' => "Dieta in gravidanza"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-fruttariana/4828/",
            'nome' => "Dieta fruttariana"
        ],
        [
            'target_url' => "https://www.superinformati.com/cibi-dietetici/26063/",
            'nome' => "Cibi dietetici"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-brasiliana/10279/",
            'nome' => "Dieta brasiliana"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-della-longevita/6154/",
            'nome' => "Dieta della longevità"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-dimagrante/19187/",
            'nome' => "Dieta dimagrante"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-dellacqua/25698/",
            'nome' => "Dieta dell'acqua"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-sirt/19749/",
            'nome' => "Dieta SIRT"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-panzironi-life-120/19049/",
            'nome' => "Dieta Panzironi"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-antiossidante/6203/",
            'nome' => "Dieta depurativa antiossidante"
        ],
        [
            'target_url' => "https://www.superinformati.com/liposuzione-alimentare-dieta-blackburn/4598/",
            'nome' => "Dieta Blackburn"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-rina/19269/",
            'nome' => "Dieta Rina"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-fast/3886/",
            'nome' => "Dieta Fast 5.2"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-paleolitica/3746/",
            'nome' => "Dieta Paleolitica"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-senza-glutine/13109/",
            'nome' => "Dieta senza glutine"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-liquida/24473/",
            'nome' => "Dieta liquida"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-a-zona/4005/",
            'nome' => "Dieta a zona"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-plank-scopri-come-perdere-10-kg-in-un-mese/3752/",
            'nome' => "Perdere 10 kg con la Dieta Plank"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-senza-carboidrati-addio-pane-e-pasta/6386/",
            'nome' => "Dieta senza carboidrati"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-ferrea/15578/",
            'nome' => "Dieta Ferrea"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-plank/17083/",
            'nome' => "Dieta Plank"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-equilibrata/22866/",
            'nome' => "Dieta equilibrata"
        ],
        [
            'target_url' => "https://www.superinformati.com/cristiano-ronaldo-dieta/11019/",
            'nome' => "Dieta Cristiano Ronaldo"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-lipofidica/10140/",
            'nome' => "Dieta Lipofidica"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-macrobiotica/21654/",
            'nome' => "Dieta Macrobiotica"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-gruppo-sanguigno/3799/",
            'nome' => "Dieta del gruppo sanguigno"
        ],
        [
            'target_url' => "https://www.superinformati.com/diete-low-carb-iperproteiche/3686/",
            'nome' => "Dieta Low Carb"
        ],
        [
            'target_url' => "https://www.superinformati.com/la-dieta-tisanoreica/3903/",
            'nome' => "Dieta Tisanoreica"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-estiva/20352/",
            'nome' => "Dieta estiva"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-dukan-la-dieta-delle-polemiche/2551/",
            'nome' => "Dieta Dukan"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-drenante/20143/",
            'nome' => "Dieta drenante e sgonfiante"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-per-colesterolo-alto/19825/",
            'nome' => "Dieta per il colesterolo"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-chetogenica-funziona/3526/",
            'nome' => "Dieta chetogenica"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-proteica/15938/",
            'nome' => "Dieta proteica"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-iperproteica/5987/",
            'nome' => "Dieta iperproteica"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-mima-digiuno/12862/",
            'nome' => "Dieta Mima digiuno"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-tlc/12431/",
            'nome' => "Dieta TLC"
        ],
        [
            'target_url' => "https://www.superinformati.com/perdere-peso-dieta-personalizzata/5672/",
            'nome' => "Dieta personalizzata"
        ],
        [
            'target_url' => "https://www.superinformati.com/ricette-dietetiche/3297/",
            'nome' => "Ricette dietetiche"
        ],
        [
            'target_url' => "https://www.superinformati.com/alimentazione-corretta/22783/",
            'nome' => "Alimentazione corretta"
        ],
        [
            'target_url' => "https://www.superinformati.com/le-15-diete-piu-famose-e-le-piu-efficaci/4030/",
            'nome' => "Le 33 diete più efficaci - articolone"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-vegetariana/3300/",
            'nome' => "Dieta vegetariana"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-del-minestrone/28366/",
            'nome' => "Dieta del minestrone"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-dash/28377/",
            'nome' => "Dieta Dash"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-del-limone/28884/",
            'nome' => "Dieta del limone"
        ],
        [
            'target_url' => "https://www.superinformati.com/dieta-herbalife/29867/",
            'nome' => "Dieta Herbalife"
        ]
    ];

    $result = printList($l, $links_data, "Lista delle principali diete", "thumbnail-list");
    return $result;
}

function sedi_inps_handler($atts, $content = null): string
{
    $l = new ListOfPostsHelper(false, true, false);

    $result = "<h3>Sedi INPS in tutta italia</h3>";

    $result .= Html::ul()->class("nicelist")->open();
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.superinformati.com/inps-bologna/13095/", "Sedi Inps Bologna", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-firenze/13130/", "Sedi Inps Firenze", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-brescia/13157/", "Sedi Inps Brescia", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-piacenza/13178/", "Sedi Inps Piacenza", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-napoli/13191/", "Sedi Inps Napoli", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-lecco/13174/", "Sedi Inps Lecco", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-potenza/13176/", "Sedi Inps Potenza", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-catania/13323/", "Sedi Inps Catania", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-padova/13332/", "Sedi Inps Padova", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-palermo/13091/", "Sedi Inps Palermo", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-taranto/14045/", "Sedi Inps Taranto", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-chieti/14043/", "Sedi Inps Chieti", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-trieste/14047/", "Sedi Inps Trieste", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-udine/14049/", "Sedi Inps Udine", ""));

    $collection->add(new LinkBase("https://www.superinformati.com/inps-teramo/14613/", "Sedi Inps Teramo", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-siena/14611/", "Sedi Inps Siena", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-prato/14609/", "Sedi Inps Prato", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-pistoia/14607/", "Sedi Inps Pistoia", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-brindisi/14602/", "Sedi Inps Brindisi", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-agrigento/14599/", "Sedi Inps Agrigento", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-caserta/14051/", "Sedi Inps Caserta", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-viterbo/14917/", "Inps Viterbo", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-vercelli/14916/", "Inps Vercelli", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-oristano/14913/", "Inps Oristano", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-terni/14915/", "Inps Terni", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-ragusa/14914/", "Inps Ragusa", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-biella/14909/", "Inps Biella", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-catanzaro/14910/", "Inps Catanzaro", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-enna/14911/", "Inps Enna", ""));
    $collection->add(new LinkBase("https://www.superinformati.com/inps-lodi/13419/", "Inps Lodi", ""));
    $result .= $l->getLinksWithImagesCurrentColumn($collection);
    $result .= Ul::tag()->close();

    $result .= "</ul>";
    return $result;
}
