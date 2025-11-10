<?php
namespace gik25microdata\site_specific;

use gik25microdata\ListOfPosts\ListOfPostsHelper;
use gik25microdata\ListOfPosts\Types\LinkBase;
use gik25microdata\LinkGenerator\LinkCollectionBuilder;
use Illuminate\Support\Collection;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Ul;

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

//add_action('wp_head', 'add_HeaderScript');

function add_HeaderScript()
{
    if (defined('DOING_AJAX'))
    {
        return;
    }

    echo <<<TAG
<!-- Popup Ads-->
<script src="https://popups.landingi.com/api/v2/website/install-code?apikey=cd4bfeb5-bcd0-4919-8f8e-c04d78bd7cb6"></script>
TAG;

}

add_shortcode('link_analisi_sangue', __NAMESPACE__ . '\\link_analisi_sangue_handler');
add_shortcode('link_vitamine', __NAMESPACE__ . '\\link_vitamine_handler');
add_shortcode('link_diete', __NAMESPACE__ . '\\link_diete_handler');
add_shortcode('link_diete2', __NAMESPACE__ . '\\link_diete_handler2');

function link_vitamine_handler($atts, $content = null)
{
    $links = [
        ['target_url' => "https://www.nonsolodiete.it/vitamine-del-gruppo-b/", 'nome' => "Vitamine del gruppo B"],
        ['target_url' => "https://www.nonsolodiete.it/vitamina-b1/", 'nome' => "Vitamina B1"],
        ['target_url' => "https://www.nonsolodiete.it/vitamina-b5/", 'nome' => "Vitamina B5"],
        ['target_url' => "https://www.nonsolodiete.it/piridossina-vitamina-b6/", 'nome' => "Vitamina B6"],
        ['target_url' => "https://www.nonsolodiete.it/vitamina-b8/", 'nome' => "Vitamina B8"],
        ['target_url' => "https://www.nonsolodiete.it/vitamina-b12/", 'nome' => "Vitamina B12"],
        ['target_url' => "https://www.nonsolodiete.it/acido-folico-tutto-quello-che-dovete-sapere/", 'nome' => "Acido Folico"],
        ['target_url' => "https://www.nonsolodiete.it/vitamina-d/", 'nome' => "Vitamina D"],
    ];

    return LinkCollectionBuilder::create()
        ->addLinks($links)
        ->withImage(true)
        ->removeIfSelf(false)
        ->ulClass('thumbnail-list')
        ->buildWithTitle('Lista delle principali vitamine', 'thumbnail-list');
}

function link_diete_handler($atts, $content = null)
{
    $links = [
        ['target_url' => "https://www.nonsolodiete.it/le-differenti-diete/", 'nome' => "Diete differenti"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-10-kg/", 'nome' => "Dieta per perdere 10kg"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-chetogenica/", 'nome' => "Dieta chetogenica"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-del-supermetabolismo/", 'nome' => "Dieta supermetabolismo"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-plank/", 'nome' => "Dieta Plank"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-senza-carboidrati/", 'nome' => "Dieta senza carboidrati"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-mima-digiuno/", 'nome' => "Dieta mima digiuno"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-del-riso-scotti-dietidea/", 'nome' => "Dieta del riso scotti"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-lemme/", 'nome' => "Dieta Lemme"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-vegana/", 'nome' => "Dieta Vegana"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-mediterranea/", 'nome' => "Dieta Mediterranea"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-sirt/", 'nome' => "Dieta Sirt"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-delle-uova/", 'nome' => "Dieta delle uova"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-panzironi/", 'nome' => "Dieta Panzironi"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-scarsdale/", 'nome' => "Dieta Scarsdale"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-prima-e-dopo-le-feste/", 'nome' => "Dieta Lampo di Natale"],
        ['target_url' => "https://www.nonsolodiete.it/dieta-tina-cipollari/", 'nome' => "Dieta di Tina Cipollari"],
    ];

    return LinkCollectionBuilder::create()
        ->addLinks($links)
        ->withImage(true)
        ->removeIfSelf(false)
        ->columns(2)
        ->ulClass('thumbnail-list')
        ->buildWithTitle('Lista delle principali Diete', 'thumbnail-list');
}


function link_analisi_sangue_handler($atts, $content = null)
{
    $l = new ListOfPostsHelper(false, true, false);

    $result = "<h3>Analisi del Sangue: gli altri valori da tenere sotto controllo</h3>";
//		$result="RBC, RDW, Ht,  HB, Ematocrito, MCV, MCH, MCHC si riferiscono ai globuli rossi,
//		WBC solo ai globuli bianchi,
//		poi ci sono reticolociti e piastrine.
//		In pratica il 90% dell'emocromo riguarda i globuli rossi.";

    // $links_data = array(
    // 	array(
    // 		'target_url' => "https://www.nonsolodiete.it/vitamine-del-gruppo-b/",
    // 		'nome' => "Vitamine del gruppo B",
    // 		'commento' => 'commento'
    // 	),
    // );

    $links_data_1 = array(
        array(
            'target_url' => "https://www.nonsolodiete.it/esame-emocromocitometrico/",
            'nome' => "Emocromo",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/costo-analisi-del-sangue/",
            'nome' => "Lista esami del sangue",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/mcv-alto-o-basso/",
            'nome' => "MCV",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/autoanalisi-sangue/",
            'nome' => "Autoanalisi sangue",
        )
    );
    $links_data_2 = array(
        array(
            'target_url' => "https://www.nonsolodiete.it/monociti-macrofagi/",
            'nome' => "Monociti",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/leucociti-alti-wbc/",
            'nome' => "Leucociti Alti (Leucocitosi)",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/globuli-bianchi/",
            'nome' => "Globuli bianchi (WBC)",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/leucopenia/",
            'nome' => "Leucociti Bassi(Leucopenia)",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/granulociti-neutrofili/",
            'nome' => "Granulociti neutrofili",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/linfociti/",
            'nome' => "Linfociti (alti, bassi)",
        )
    );
    $links_data_3 = array(
        array(
            'target_url' => "https://www.nonsolodiete.it/anemia-aplastica/",
            'nome' => "Anemia Aplastica",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/globuli-rossi/",
            'nome' => "Globuli Rossi",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/reticolociti/",
            'nome' => "Globuli Rossi",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/ematocrito/",
            'nome' => "Ematocrito",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/rbc/",
            'nome' => "RBC",
        ),
        array(//6
            'target_url' => "https://www.nonsolodiete.it/hb/",
            'nome' => "Emoglobina (HGB o Hb)",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/mch/",
            'nome' => "MCH",
            'commento' => '(contenuto corpuscolare medio di emoglobina)'
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/mchc/",
            'nome' => "MCHC",
            'commento' => '(concentrazione corpuscolare media di emoglobina)'
        ),
        array(//9
            'target_url' => "https://www.nonsolodiete.it/rdw/",
            'nome' => "RDW-CV e RDW-SD",
            'commento' => '(variabilità della dimensione o del volume delle cellule dei globuli rossi; SD = deviazione standard; CV = coefficiente di variazione)'
        )
    );
    $links_data_4 = array(
        array(
            'target_url' => "https://www.nonsolodiete.it/piastrine/",
            'nome' => "Piastrine"
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/mpv-alto-basso/",
            'nome' => "MPV",
            'commento' => '(Volume piastrinico medio)'
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/pdw-analisi-del-sangue/",
            'nome' => "PDW",
            'commento' => '(ampiezza di distribuzione piastrinica)'
        )
    );
    $links_data_5 = array(
        array(
            'target_url' => "https://www.nonsolodiete.it/creatinina-alta-e-bassa/",
            'nome' => "Creatinina",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/albumina-alta-o-bassa/",
            'nome' => "Albumina",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/enzimi-epatici/",
            'nome' => "Enzimi epatici",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/colesterolo-sintomi-cause/",
            'nome' => "Colesterolo",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/esami-del-sangue-in-gravidanza/",
            'nome' => "Analisi del sangue in gravidanza",
        ),
        array(//6
            'target_url' => "https://www.nonsolodiete.it/thc/",
            'nome' => "THC",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/ferritinemia/",
            'nome' => "Ferritina",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/carenza-di-ferro/",
            'nome' => "Carenza di ferro",
        ),
        array(
            'target_url' => "https://www.nonsolodiete.it/transaminasi/",
            'nome' => "Transaminasi",
        )
    );

    $result .= "<ul class=\"thumbnail-list\">";
    $result .= $l->GetLinksWithImages($links_data_1);

    $result .= "<h4>Globuli bianchi</h4>";
    $result .= $l->GetLinksWithImages($links_data_2);

    $result .= "<h4>Globuli Rossi</h4>";
    $result .= $l->GetLinksWithImages($links_data_3);

    $result .= "<h4>Piastrine</h4>";
    $result .= $l->GetLinksWithImages($links_data_4);

    $result .= "<h4>Altro</h4>";
    $result .= $l->GetLinksWithImages($links_data_5);

    $result .= "</ul>";
    return $result;
}

function link_diete_handler2($atts, $content = null)
{
    //$list_layout = 1;// one column
    $list_layout = 2;// two columns

    if (isset($atts['list_layout'])) $list_layout = (int)$atts['list_layout'];

    $l = new ListOfPostsHelper(false, true, false, $list_layout);

    $tag = 'analisi del sangue';// $tag = 'Horror';

    if (isset($atts['tag'])) $tag = $atts['tag'];

    $result = '<h3>Posts by tag: <i style="color: maroon;">' . $tag . '</i></h3>';

    $result .= $l->GetLinksWithImagesByTag($tag);

    return $result;
}
