<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_shortcode('link_analisi_sangue', 'link_analisi_sangue_handler');
add_shortcode('sedi_inps', 'sedi_inps_handler');

/**
 * @param $atts
 * @param null $content
 * @return string
 */
function link_analisi_sangue_handler($atts, $content = null)
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

	$result .= "</ul>";
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