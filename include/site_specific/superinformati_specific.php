<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_shortcode('link_analisi_sangue', 'link_analisi_sangue_handler_2');
add_shortcode('sedi_inps', 'sedi_inps_handler');
add_shortcode('link_vitamine', 'link_vitamine_handler');
add_shortcode('link_diete', 'link_diete_handler');

/**
 * @param $atts
 * @param null $content
 * @return string
 */
function link_analisi_sangue_handler_2($atts, $content = null)
{
    $l = new ListOfPostsHelper(false,true,false );
    
	$result = "<h3>Analisi del Sangue: gli altri valori da tenere sotto controllo</h3>";
//		$result="RBC, RDW, Ht,  HB, Ematocrito, MCV, MCH, MCHC si riferiscono ai globuli rossi,
//		WBC solo ai globuli bianchi,
//		poi ci sono reticolociti e piastrine.
//		In pratica il 90% dell'emocromo riguarda i globuli rossi.";

	$result .= "<ul class=\"nicelist\">";
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/indici-corpuscolari-quali-emocromo.htm", "Indici Corpuscolari", "");

	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/mcv-volume-corpuscolare-medio.htm", "MCV",
        "(volume corpuscolare medio)");

	$result .= "<h4>Globuli bianchi</h4>";
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/leucociti.htm", "Leucociti", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/neutrofili.htm", "Neutrofili", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/globuli-bianchi-bassi.htm", "Leucopenia", "(Globuli bianchi bassi)");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/monociti-macrofagi.htm", "Monocidi Macrofagi", "");

	#region Globuli Rossi

	$result .= "<h4>Globuli Rossi</h4>";

	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/globuli-rossi.htm", "Globuli rossi", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/reticolociti.htm", "Reticolociti", "(Globuli rossi non del tutto formati)");

	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/mch.htm", "MCH",
        "(contenuto corpuscolare medio di emoglobina)");

	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/mchc.htm", "MCHC",
        "(concentrazione corpuscolare media di emoglobina)");

	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/rdw-cv.htm", "RDW-CV e RDW-SD",
        "(variabilità della dimensione o del volume delle cellule dei globuli rossi; SD = deviazione standard; CV = coefficiente di variazione)");

	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/ves-esami-valori.htm", "VES", "(velocità di elettrosedimentazione)");

	

	#endregion

    #region Piastrine

	$result .= "<h4>Piastrine</h4>";
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/piastrine.htm", "Piastrine", "");

	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/mpv-volume-piastrinico-medio.htm", "MPV",
        "(Volume piastrinico medio)");

	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/pdw.htm", "PDW",
        "(ampiezza di distribuzione piastrinica)");

	#endregion

	$result .= "<h4>Altro</h4>";
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/emazie-nelle-urine.htm", "Emazie nelle urine",
        "(emazie è un sinonimo di globuli rossi)");

	$result .= $l->GetLinkWithImage(" https://www.superinformati.com/medicina-e-salute/thc.htm", "THC nelle urine",
        "");

	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/nuovi-parametri-per-il-livello-del-colesterolo.htm", "Colesterolo", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/trigliceridi.htm", "Trigliceridi", "");

	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/creatinina.htm", "Creatinina", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/ferritina.htm", "Ferritina", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/sideremia.htm", "Sideremia", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/omocisteina.htm", "Omocisteina", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/transferrina.htm", "Transferrina", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/analisi-del-sangue-in-gravidanza.htm", "Analisi del sangue in gravidanza", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/transaminasi.htm", "Transaminasi", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/carenza-di-ferro.htm", "Carenza di ferro", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/gamma-gt-alte-e-basse.htm", "Gamma GT alte e basse", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/iperglicemia.htm", "Iperglicemia", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/anemia-falciforme.htm", "Anemia Falciforme", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/iperomocisteinemia.htm", "Iperomocisteinemia", "");

	$result .= "</ul>";
	return $result;
}

function link_vitamine_handler($atts, $content = null)
{
    $l = new ListOfPostsHelper(false, true, false);
    $links_data = array(
        array(
            'target_url' => "https://www.superinformati.com/integratori/vitamina-a.htm",
            'nome' => "Vitamina A"
        ),
        array(
            'target_url' => "https://www.superinformati.com/integratori/vitamine-gruppo-b.htm",
            'nome' => "Vitamine del gruppo B"
        ),
        array(
            'target_url' => "https://www.superinformati.com/integratori/vitamina-b1.htm",
            'nome' => "Vitamina B1"
        ),
        array(
            'target_url' => "https://www.superinformati.com/integratori/vitamina-b2.htm",
            'nome' => "Vitamina B2"
        ),
        array(
            'target_url' => "https://www.superinformati.com/medicina-e-salute/vitamina-b3.htm",
            'nome' => "Vitamina B3"
        ),
        array(
            'target_url' => "https://www.superinformati.com/medicina-e-salute/vitamina-b5.htm",
            'nome' => "Vitamina B5"
        ),
        array(
            'target_url' => "https://www.superinformati.com/integratori/vitamina-b6.htm",
            'nome' => "Vitamina B6"
        ),
        array(
            'target_url' => "https://www.superinformati.com/medicina-e-salute/vitamina-b8.htm",
            'nome' => "Vitamina B8"
        ),
        array(
            'target_url' => "https://www.superinformati.com/integratori/vitamina-b9.htm",
            'nome' => "Vitamina B9"
        ),
        array(
            'target_url' => "https://www.superinformati.com/integratori/vitamina-b12.htm",
            'nome' => "Vitamina B12"
        ),
        array(
            'target_url' => "https://www.superinformati.com/integratori/vitamina-c.htm",
            'nome' => "Vitamina C"
        ),
        array(
            'target_url' => "https://www.superinformati.com/integratori/vitamina-d-caratteristiche.htm",
            'nome' => "Vitamina"
        ),
        array(
            'target_url' => "https://www.superinformati.com/integratori/carenza-vitamina-d.htm",
            'nome' => "Vitamina D"
        ),
        array(
            'target_url' => "https://www.superinformati.com/integratori/vitamina-e.htm",
            'nome' => "Vitamina E"
        ),
        array(
            'target_url' => "https://www.superinformati.com/integratori/vitamina-k.htm",
            'nome' => "Vitamina K"
        ),
        array(
            'target_url' => "https://www.superinformati.com/integratori/vitamina-k2.htm",
            'nome' => "Vitamina K2"
        )
    );

    $result = "<h3>Lista delle principali vitamine</h3>
		<div class='thumbnail-list'>";

    $result .= "<ul class='thumbnail-list'>";

    $result .= $l->GetLinksWithImages($links_data);

    $result .= "</ul></div>";
    return $result;
}


function link_diete_handler($atts, $content = null)
{
    $l = new ListOfPostsHelper(false, true, false);
    $links_data = array(

array(
    'target_url' => "https://www.superinformati.com/diete/dieta-alcalina.htm",
    'nome' => "Dieta Alcalina"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-dottor-nowzaradan.htm",
    'nome' => "Dieta Dott. Nowzaradan"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-detox-menu-settimanale.htm",
    'nome' => "Dieta Detox"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-scarsdale.htm",
    'nome' => "Dieta Scarsdale"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-atkins.htm",
    'nome' => "Dieta Atkins"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-digiuno-intermittente-16-8.htm",
    'nome' => "Dieta Digiuno intermittente"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-ipocalorica.htm",
    'nome' => "Dieta Ipocalorica"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-louise-parker-menu-schema.htm",
    'nome' => "Dieta Louise Parker"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-supermetabolismo.htm",
    'nome' => "Dieta del Supermetabolismo"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-carb-lovers.htm",
    'nome' => "Dieta Carb's Lover"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-disintossicante-fegato.htm",
    'nome' => "Dieta disintossicante fegato"
),
array(
    'target_url' => "https://www.superinformati.com/diete/la-dieta-vegana.htm",
    'nome' => "Dieta Vegana"
),
array(
    'target_url' => "https://www.superinformati.com/medicina-e-salute/dieta-iposodica-menu.htm",
    'nome' => "Dieta Iposodica"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-lemme.htm",
    'nome' => "Dieta Lemme"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-lampo.htm",
    'nome' => "Dieta Lampo"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-anticellulite.htm",
    'nome' => "Dieta anticellulite"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-del-riso.htm",
    'nome' => "Dieta del riso"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-a-punti.htm",
    'nome' => "Dieta a punti"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-veloce.htm",
    'nome' => "Dieta veloce"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-mediterranea-e-cibi-sani.htm",
    'nome' => "Dieta Mediterranea"
),
array(
    'target_url' => "https://www.superinformati.com/maternita/dieta-in-gravidanza.htm",
    'nome' => "Dieta in gravidanza"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-fruttariana.htm",
    'nome' => "Dieta fruttariana"
),
array(
    'target_url' => "https://www.superinformati.com/diete/cibi-dietetici.htm",
    'nome' => "Cibi dietetici"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-brasiliana.htm",
    'nome' => "Dieta brasiliana"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-della-longevita.htm",
    'nome' => "Dieta della longevità"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-dimagrante.htm",
    'nome' => "Dieta dimagrante"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-dellacqua.htm",
    'nome' => "Dieta dell'acqua"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-sirt.htm",
    'nome' => "Dieta SIRT"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-panzironi-life-120.htm",
    'nome' => "Dieta Panzironi"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-antiossidante.htm",
    'nome' => "Dieta depurativa antiossidante"
),
array(
    'target_url' => "https://www.superinformati.com/diete/liposuzione-alimentare-dieta-blackburn.htm",
    'nome' => "Dieta Blackburn"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-rina.htm",
    'nome' => "Dieta Rina"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-fast.htm",
    'nome' => "Dieta Fast 5.2"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-paleolitica.htm",
    'nome' => "Dieta Paleolitica"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-senza-glutine.htm",
    'nome' => "Dieta senza glutine"
),
array(
    'target_url' => "https://www.superinformati.com/medicina-e-salute/dieta-liquida.htm",
    'nome' => "Dieta liquida"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-a-zona.htm",
    'nome' => "Dieta a zona"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-plank-scopri-come-perdere-10-kg-in-un-mese.htm",
    'nome' => "Perdere 10 kg con la Dieta Plank"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-senza-carboidrati-addio-pane-e-pasta.htm",
    'nome' => "Dieta senza carboidrati"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-ferrea.htm",
    'nome' => "Dieta Ferrea"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-plank.htm",
    'nome' => "Dieta Plank"
),
array(
    'target_url' => "https://www.superinformati.com/alimentazione/dieta-equilibrata.htm",
    'nome' => "Dieta equilibrata"
),
array(
    'target_url' => "https://www.superinformati.com/diete/cristiano-ronaldo-dieta.htm",
    'nome' => "Dieta Cristiano Ronaldo"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-lipofidica.htm",
    'nome' => "Dieta Lipofidica"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-macrobiotica.htm",
    'nome' => "Dieta Macrobiotica"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-gruppo-sanguigno.htm",
    'nome' => "Dieta del gruppo sanguigno"
),
array(
    'target_url' => "https://www.superinformati.com/diete/diete-low-carb-iperproteiche.htm",
    'nome' => "Dieta Low Carb"
),
array(
    'target_url' => "https://www.superinformati.com/diete/la-dieta-tisanoreica.htm",
    'nome' => "Dieta Tisanoreica"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-estiva.htm",
    'nome' => "Dieta estiva"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-dukan-la-dieta-delle-polemiche.htm",
    'nome' => "Dieta Dukan"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-drenante.htm",
    'nome' => "Dieta drenante e sgonfiante"
),
array(
    'target_url' => "https://www.superinformati.com/alimentazione/dieta-per-colesterolo-alto.htm",
    'nome' => "Dieta per il colesterolo"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-chetogenica-funziona.htm",
    'nome' => "Dieta chetogenica"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-proteica.htm",
    'nome' => "Dieta proteica"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-iperproteica.htm",
    'nome' => "Dieta iperproteica"
),
array(
    'target_url' => "https://www.superinformati.com/dimagrire/dieta-mima-digiuno.htm",
    'nome' => "Dieta Mima digiuno"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-tlc.htm",
    'nome' => "Dieta TLC"
),
array(
    'target_url' => "https://www.superinformati.com/diete/perdere-peso-dieta-personalizzata.htm",
    'nome' => "Dieta personalizzata"
),
array(
    'target_url' => "https://www.superinformati.com/medicina-e-salute/ricette-dietetiche-per-dimagrire.htm",
    'nome' => "Ricette dietetiche"
),
array(
    'target_url' => "https://www.superinformati.com/alimentazione/alimentazione-corretta.htm",
    'nome' => "Alimentazione corretta"
),
array(
    'target_url' => "https://www.superinformati.com/diete/le-15-diete-piu-famose-e-le-piu-efficaci.htm",
    'nome' => "Le 33 diete più efficaci - articolone"
),
array(
    'target_url' => "https://www.superinformati.com/diete/dieta-vegetariana-bilanciata-nellapporto-dei-nutrienti.htm",
    'nome' => "Dieta vegetariana"
)

    );

    $result = "<h3>Lista delle principali diete</h3>
		<div class='thumbnail-list'>";

    $result .= "<ul class='thumbnail-list'>";

    $result .= $l->GetLinksWithImages($links_data);

    $result .= "</ul></div>";
    return $result;
}


function sedi_inps_handler($atts, $content = null)
{
    $l = new ListOfPostsHelper(false,true,false );

    $result = "<h3>Sedi INPS in tutta italia</h3>";

    $result .= "<ul class=\"nicelist\">"; 


    $result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-bologna.htm", "Sedi Inps Bologna", "");
    $result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-firenze.htm", "Sedi Inps Firenze", "");
    $result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-brescia.htm", "Sedi Inps Brescia", "");
    $result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-piacenza.htm", "Sedi Inps Piacenza", "");
    $result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-napoli.htm", "Sedi Inps Napoli", "");
    $result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-lecco.htm", "Sedi Inps Lecco", "");
    $result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-potenza.htm", "Sedi Inps Potenza", "");
    $result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-catania.htm", "Sedi Inps Catania", "");
    $result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-padova.htm", "Sedi Inps Padova", "");
    $result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-palermo.htm", "Sedi Inps Palermo", "");
    $result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-taranto.htm", "Sedi Inps Taranto", "");
    $result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-chieti.htm", "Sedi Inps Chieti", "");
    $result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-trieste.htm", "Sedi Inps Trieste", "");
    $result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-udine.htm", "Sedi Inps Udine", "");

	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-teramo.htm", "Sedi Inps Teramo", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-siena.htm", "Sedi Inps Siena", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-prato.htm", "Sedi Inps Prato", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-pistoia.htm", "Sedi Inps Pistoia", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-brindisi.htm", "Sedi Inps Brindisi", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-agrigento.htm", "Sedi Inps Agrigento", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-caserta.htm", "Sedi Inps Caserta", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-viterbo.htm", "Inps Viterbo", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-vercelli.htm", "Inps Vercelli", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-oristano.htm", "Inps Oristano", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-terni.htm", "Inps Terni", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-ragusa.htm", "Inps Ragusa", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-biella.htm", "Inps Biella", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-catanzaro.htm", "Inps Catanzaro", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-enna.htm", "Inps Enna", "");
	$result .= $l->GetLinkWithImage("https://www.superinformati.com/consumatori/inps-lodi.htm", "Inps Lodi", "");


	$result .= "</ul>";
    return $result;
}