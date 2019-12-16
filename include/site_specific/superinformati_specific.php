<?php

add_shortcode('link_analisi_sangue', 'link_analisi_sangue_handler');
add_shortcode('sedi_inps', 'sedi_inps_handler');

/**
 * @param $atts
 * @param null $content
 * @return string
 */
function link_analisi_sangue_handler($atts, $content = null)
{
	$result = "<h3>Analisi del Sangue: gli altri valori da tenere sotto controllo</h3>";
//		$result="RBC, RDW, Ht,  HB, Ematocrito, MCV, MCH, MCHC si riferiscono ai globuli rossi,
//		WBC solo ai globuli bianchi,
//		poi ci sono reticolociti e piastrine.
//		In pratica il 90% dell'emocromo riguarda i globuli rossi.";

	$result .= "<ul class=\"nicelist\">";
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/indici-corpuscolari-quali-emocromo.htm", "Indici Corpuscolari", "", false, false);

	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/mcv-volume-corpuscolare-medio.htm", "MCV",
		"(volume corpuscolare medio)", false, false);

	$result .= "<h4>Globuli bianchi</h4>";
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/leucociti.htm", "Leucociti", "", false, false);
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/neutrofili.htm", "Neutrofili", "", false, false);
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/globuli-bianchi-bassi.htm", "Leucopenia", "(Globuli bianchi bassi)", false, false);
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/monociti-macrofagi.htm", "Monocidi Macrofagi", "", false, false);

	#region Globuli Rossi

	$result .= "<h4>Globuli Rossi</h4>";

	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/globuli-rossi.htm", "Globuli rossi", "", false, false);

	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/mch.htm", "MCH",
		"(contenuto corpuscolare medio di emoglobina)", false, false);

	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/mchc.htm", "MCHC",
		"(concentrazione corpuscolare media di emoglobina)", false, false);

	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/rdw-cv.htm", "RDW-CV e RDW-SD",
		"(variabilità della dimensione o del volume delle cellule dei globuli rossi; SD = deviazione standard; CV = coefficiente di variazione)", false, false);

	#endregion

    #region Piastrine

	$result .= "<h4>Piastrine</h4>";
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/piastrine.htm", "Piastrine", "", false, false);

	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/mpv-volume-piastrinico-medio.htm", "MPV",
		"(Volume piastrinico medio)", false, false);

	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/pdw.htm", "PDW",
		"(ampiezza di distribuzione piastrinica)", false, false);

	#endregion

	$result .= "<h4>Altro</h4>";
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/emazie-nelle-urine.htm", "Emazie nelle urine",
		"(emazie è un sinonimo di globuli rossi)", false, false);

	$result .= GetLinkWithImage(" https://www.superinformati.com/medicina-e-salute/thc.htm", "THC nelle urine",
		"", false, false);

	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/nuovi-parametri-per-il-livello-del-colesterolo.htm", "Colesterolo", "", false, false);
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/trigliceridi.htm", "Trigliceridi", "", false, false);

	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/creatinina.htm", "Creatinina", "", false, false);
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/ferritina.htm", "Ferritina", "", false, false);
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/sideremia.htm", "Sideremia", "", false, false);
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/omocisteina.htm", "Omocisteina", "", false, false);
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/transferrina.htm", "Transferrina", "", false, false);
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/analisi-del-sangue-in-gravidanza.htm", "Analisi del sangue in gravidanza", "", false, false);
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/transaminasi.htm", "Transaminasi", "", false, false);
	$result .= GetLinkWithImage("https://www.superinformati.com/medicina-e-salute/carenza-di-ferro.htm", "Carenza di ferro", "", false, false);

	$result .= "</ul>";
	return $result;
}


function sedi_inps_handler($atts, $content = null)
{
    $result = "<h3>Sedi INPS in tutta italia</h3>";

    $result .= "<ul class=\"nicelist\">";

    //$myrows = $wpdb->get_results( "SELECT id, name FROM mytable" );
//    $sql = <<<TAG
//SELECT p.ID
//FROM `wp_postS` p
//LEFT JOIN wp_term_relationships ON (p.ID = wp_term_relationships.object_id)
//LEFT JOIN wp_term_taxonomy ON (wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id)
//LEFT JOIN wp_terms ON (wp_terms.term_id = wp_term_taxonomy.term_id )
//WHERE (wp_terms.name = "inps")
//AND p.post_type = 'post'
//AND p.post_status = 'publish'
//AND p.post_parent = 0
//AND  wp_term_taxonomy.taxonomy = 'post_tag'
//TAG;
//
//    while ($myrows->have_posts()): $myrows->the_post();
//
//        $id = get_the_ID();
//        $permalink = get_the_permalink();
//        $wpdb->show_errors();
//
//    endwhile;


    $result .= GetLinkWithImage("https://www.superinformati.com/consumatori/inps-bologna.htm", "Sedi Inps Bologna", "", false, false);
    $result .= GetLinkWithImage("https://www.superinformati.com/consumatori/inps-firenze.htm", "Sedi Inps Firenze", "", false, false);
    $result .= GetLinkWithImage("https://www.superinformati.com/consumatori/inps-brescia.htm", "Sedi Inps Brescia", "", false, false);
    $result .= GetLinkWithImage("https://www.superinformati.com/consumatori/inps-piacenza.htm", "Sedi Inps Piacenza", "", false, false);
    $result .= GetLinkWithImage("https://www.superinformati.com/consumatori/inps-napoli.htm", "Sedi Inps Napoli", "", false, false);
    $result .= GetLinkWithImage("https://www.superinformati.com/consumatori/inps-lecco.htm", "Sedi Inps Lecco", "", false, false);
    $result .= GetLinkWithImage("https://www.superinformati.com/consumatori/inps-potenza.htm", "Sedi Inps Potenza", "", false, false);
    $result .= GetLinkWithImage("https://www.superinformati.com/consumatori/inps-catania.htm", "Sedi Inps Catania", "", false, false);
    $result .= GetLinkWithImage("https://www.superinformati.com/consumatori/inps-padova.htm", "Sedi Inps Padova", "", false, false);
    $result .= GetLinkWithImage("https://www.superinformati.com/consumatori/inps-palermo.htm", "Sedi Inps Palermo", "", false, false);
    $result .= GetLinkWithImage("https://www.superinformati.com/consumatori/inps-taranto.htm", "Sedi Inps Taranto", "", false, false);
    $result .= GetLinkWithImage("https://www.superinformati.com/consumatori/inps-chieti.htm", "Sedi Inps Chieti", "", false, false);
    $result .= GetLinkWithImage("https://www.superinformati.com/consumatori/inps-trieste.htm", "Sedi Inps Trieste", "", false, false);
    $result .= GetLinkWithImage("https://www.superinformati.com/consumatori/inps-udine.htm", "Sedi Inps Udine", "", false, false);
    $result .= "</ul>";
    return $result;
}





?>