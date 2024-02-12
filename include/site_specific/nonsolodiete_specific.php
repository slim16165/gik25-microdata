<?php

use gik25microdata\ListOfPosts\ListOfPostsMain;
use gik25microdata\ListOfPosts\Types\LinkBase;
use Illuminate\Support\Collection;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Ul;

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

//add_action('wp_head', 'add_HeaderScript');

function add_HeaderScript(): void
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

add_shortcode('link_analisi_sangue', 'link_analisi_sangue_handler');
add_shortcode('link_vitamine', 'link_vitamine_handler');
add_shortcode('link_diete', 'link_diete_handler');
add_shortcode('link_diete2', 'link_diete_handler2');

function link_vitamine_handler($atts, $content = null)
{
    $l = new ListOfPostsMain(false, true, false);
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.nonsolodiete.it/vitamine-del-gruppo-b/", "Vitamine del gruppo B", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/vitamina-b1/", "Vitamina B1", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/vitamina-b5/", "Vitamina B5", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/piridossina-vitamina-b6/", "Vitamina B6", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/vitamina-b8/", "Vitamina B8", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/vitamina-b12/", "Vitamina B12", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/acido-folico-tutto-quello-che-dovete-sapere/", "Acido Folico", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/vitamina-d/", "Vitamina D", ""));


    $result = "<h3>Lista delle principali vitamine</h3>
		<div class='thumbnail-list'>";

    $result .= Html::ul()->addClass("thumbnail-list")->open();
    $result .= $l->getLinksWithImagesCurrentColumn($collection);
    $result .= Ul::tag()->close();

    $result .= "</div>";
    return $result;
}

function link_diete_handler($atts, $content = null): string
{
    $l = new ListOfPostsMain(false, true, false, 2 /* two columns */);

//		find_post_id_from_taxonomy("dieta");
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.nonsolodiete.it/le-differenti-diete/", "Diete differenti", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-10-kg/", "Dieta per perdere 10kg", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-chetogenica/", "Dieta chetogenica", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-del-supermetabolismo/", "Dieta supermetabolismo", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-plank/", "Dieta Plank", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-senza-carboidrati/", "Dieta senza carboidrati", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-mima-digiuno/", "Dieta mima digiuno", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-del-riso-scotti-dietidea/", "Dieta del riso scotti", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-lemme/", "Dieta Lemme", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-vegana/", "Dieta Vegana", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-mediterranea/", "Dieta Mediterranea", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-sirt/", "Dieta Sirt", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-delle-uova/", "Dieta delle uova", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-panzironi/", "Dieta Panzironi", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-scarsdale/", "Dieta Scarsdale", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-prima-e-dopo-le-feste/", "Dieta Lampo di Natale", ""));
    $collection->add(new LinkBase("https://www.nonsolodiete.it/dieta-tina-cipollari/", "Dieta di Tina Cipollari", ""));

    $result = "<h3>Lista delle principali Diete</h3>
		<div class='thumbnail-list'>";

    $result .= Html::ul()->addClass("thumbnail-list")->open();
    $result .= $l->getLinksWithImagesCurrentColumn($collection);
    $result .= Ul::tag()->close();

    $result .= "</div>";
    return $result;
}


function link_analisi_sangue_handler($atts, $content = null): string
{
    $l = new ListOfPostsMain(false, true, false);

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
    $collection1 = new Collection();
    $collection1->add(new LinkBase("https://www.nonsolodiete.it/esame-emocromocitometrico/", "Emocromo", ""));
    $collection1->add(new LinkBase("https://www.nonsolodiete.it/costo-analisi-del-sangue/", "Lista esami del sangue", ""));
    $collection1->add(new LinkBase("https://www.nonsolodiete.it/mcv-alto-o-basso/", "MCV", ""));
    $collection1->add(new LinkBase("https://www.nonsolodiete.it/autoanalisi-sangue/", "Autoanalisi sangue", ""));

    $collection2 = new Collection();
    $collection2->add(new LinkBase("https://www.nonsolodiete.it/monociti-macrofagi/", "Monociti", ""));
    $collection2->add(new LinkBase("https://www.nonsolodiete.it/leucociti-alti-wbc/", "Leucociti Alti (Leucocitosi)", ""));
    $collection2->add(new LinkBase("https://www.nonsolodiete.it/globuli-bianchi/", "Globuli bianchi (WBC)", ""));
    $collection2->add(new LinkBase("https://www.nonsolodiete.it/leucopenia/", "Leucociti Bassi(Leucopenia)", ""));
    $collection2->add(new LinkBase("https://www.nonsolodiete.it/granulociti-neutrofili/", "Granulociti neutrofili", ""));
    $collection2->add(new LinkBase("https://www.nonsolodiete.it/linfociti/", "Linfociti (alti, bassi)", ""));

    $collection3 = new Collection();
    $collection3->add(new LinkBase("https://www.nonsolodiete.it/anemia-aplastica/", "Anemia Aplastica", ""));
    $collection3->add(new LinkBase("https://www.nonsolodiete.it/globuli-rossi/", "Globuli Rossi", ""));
    $collection3->add(new LinkBase("https://www.nonsolodiete.it/reticolociti/", "Globuli Rossi", ""));
    $collection3->add(new LinkBase("https://www.nonsolodiete.it/ematocrito/", "Ematocrito", ""));
    $collection3->add(new LinkBase("https://www.nonsolodiete.it/rbc/", "RBC", ""));
    $collection3->add(new LinkBase("https://www.nonsolodiete.it/hb/", "Emoglobina (HGB o Hb)"));
    $collection3->add(new LinkBase("https://www.nonsolodiete.it/mch/", "MCH", "(contenuto corpuscolare medio di emoglobina)"));
    $collection3->add(new LinkBase("https://www.nonsolodiete.it/mchc/", "MCHC", "(concentrazione corpuscolare media di emoglobina)"));
    $collection3->add(new LinkBase("https://www.nonsolodiete.it/rdw/", "RDW-CV e RDW-SD", '(variabilità della dimensione o del volume delle cellule dei globuli rossi; SD = deviazione standard; CV = coefficiente di variazione)'));

    $collection4 = new Collection();
    $collection4->add(new LinkBase("https://www.nonsolodiete.it/piastrine/", "Piastrine", ""));
    $collection4->add(new LinkBase("https://www.nonsolodiete.it/mpv-alto-basso/", "MPV", "(Volume piastrinico medio)"));
    $collection4->add(new LinkBase("https://www.nonsolodiete.it/pdw-analisi-del-sangue/", "PDW", "(ampiezza di distribuzione piastrinica)"));

    $collection5 = new Collection();
    $collection5->add(new LinkBase("https://www.nonsolodiete.it/creatinina-alta-e-bassa/", "Creatinina", ""));
    $collection5->add(new LinkBase("https://www.nonsolodiete.it/albumina-alta-o-bassa/", "Albumina", ""));
    $collection5->add(new LinkBase("https://www.nonsolodiete.it/enzimi-epatici/", "Enzimi epatici", ""));
    $collection5->add(new LinkBase("https://www.nonsolodiete.it/colesterolo-sintomi-cause/", "Colesterolo", ""));
    $collection5->add(new LinkBase("https://www.nonsolodiete.it/esami-del-sangue-in-gravidanza/", "Analisi del sangue in gravidanza", ""));
    $collection5->add(new LinkBase("https://www.nonsolodiete.it/thc/", "THC"));
    $collection5->add(new LinkBase("https://www.nonsolodiete.it/ferritinemia/", "Ferritina", ""));
    $collection5->add(new LinkBase("https://www.nonsolodiete.it/carenza-di-ferro/", "Carenza di ferro", ""));
    $collection5->add(new LinkBase("https://www.nonsolodiete.it/transaminasi/", "Transaminasi", ""));

    $result .= "<ul class=\"thumbnail-list\">";
    $result .= $l->GetLinksWithImages($collection1);

    $result .= "<h4>Globuli bianchi</h4>";
    $result .= $l->GetLinksWithImages($collection2);

    $result .= "<h4>Globuli Rossi</h4>";
    $result .= $l->GetLinksWithImages($collection3);

    $result .= "<h4>Piastrine</h4>";
    $result .= $l->GetLinksWithImages($collection4);

    $result .= "<h4>Altro</h4>";
    $result .= $l->GetLinksWithImages($collection5);

    $result .= "</ul>";
    return $result;
}

function link_diete_handler2($atts, $content = null): string
{
    //$list_layout = 1;// one column
    $list_layout = 2;// two columns

    if (isset($atts['list_layout'])) $list_layout = (int)$atts['list_layout'];

    $l = new ListOfPostsMain(false, true, false, $list_layout);

    $tag = 'analisi del sangue';// $tag = 'Horror';

    if (isset($atts['tag'])) $tag = $atts['tag'];

    $result = '<h3>Posts by tag: <i style="color: maroon;">' . $tag . '</i></h3>';

    $result .= $l->GetLinksWithImagesByTag($tag);

    return $result;
}
