<?php
	/**
	 * Created by PhpStorm.
	 * User: g.salvi
	 * Date: 17/09/2019
	 * Time: 14:44
	 */


	add_shortcode('link_analisi_sangue', 'link_analisi_sangue_handler');
	add_shortcode('link_vitamine', 'link_vitamine_handler');
	add_shortcode('link_diete', 'link_diete_handler');

	function link_vitamine_handler($atts, $content = null)
	{
		$result = "<h3>Lista delle principali vitamine</h3>
		<div class='thumbnail-list'>";

		$result .= "<ul class='thumbnail-list'>";
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/vitamine-del-gruppo-b/", "Vitamine del gruppo B");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/vitamina-b1/", "Vitamina B1");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/vitamina-b5/", "Vitamina B5");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/piridossina-vitamina-b6/", "Vitamina B6");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/vitamina-b8/", "Vitamina B8");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/vitamina-b12/", "Vitamina B12");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/acido-folico-tutto-quello-che-dovete-sapere/", "Acido Folico");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/vitamina-d/", "Vitamina D");

		$result .= "</ul></div>";
		return $result;
	}

	function link_diete_handler($atts, $content = null)
	{
		$result = "<h3>Lista principali Diete</h3>
		<div class='thumbnail-list'>";

//		find_post_id_from_taxonomy("dieta");

		$result .= "<ul class='thumbnail-list'>";
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/le-differenti-diete/", "Diete differenti");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-10-kg/", "Dieta per perdere 10kg");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-chetogenica/", "Dieta chetogenica");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-del-supermetabolismo/", "Dieta supermetabolismo");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-plank/", "Dieta Plank");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-senza-carboidrati/", "Dieta senza carboidrati");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-mima-digiuno/", "Dieta mima digiuno");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-del-riso-scotti-dietidea/", "Dieta del riso scotti");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-lemme/", "Dieta Lemme");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-vegana/", "Dieta Vegana");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-mediterranea/", "Dieta Mediterranea");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-sirt/", "Dieta Sirt");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-delle-uova/", "Dieta delle uova");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-panzironi/", "Dieta Panzironi");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-scarsdale/", "Dieta Scarsdale");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-prima-e-dopo-le-feste/", "Dieta Lampo di Natale");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/dieta-tina-cipollari/", "Dieta di Tina Cipollari");

		$result .= "</ul></div>";
		return $result;
	}



	function link_analisi_sangue_handler($atts, $content = null)
	{
		$result = "<h3>Analisi del Sangue: gli altri valori da tenere sotto controllo</h3>";
//		$result="RBC, RDW, Ht,  HB, Ematocrito, MCV, MCH, MCHC si riferiscono ai globuli rossi,
//		WBC solo ai globuli bianchi,
//		poi ci sono reticolociti e piastrine.
//		In pratica il 90% dell'emocromo riguarda i globuli rossi.";

		$result .= "<ul class=\"thumbnail-list\">";
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/esame-emocromocitometrico/", "Emocromo");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/costo-analisi-del-sangue/", "Lista esami del sangue");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/mcv-alto-o-basso/", "MCV");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/autoanalisi-sangue/", "Autoanalisi sangue");

		$result .= "<h4>Globuli bianchi</h4>";
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/monociti-macrofagi/", "Monociti");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/leucociti-alti-wbc/", "Leucociti Alti (Leucocitosi)");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/globuli-bianchi/", "Globuli bianchi (WBC)");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/leucopenia/", "Leucociti Bassi(Leucopenia)");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/granulociti-neutrofili/", "Granulociti neutrofili");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/linfociti/", "Linfociti (alti, bassi)");

		$result .= "<h4>Globuli Rossi</h4>";
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/anemia-aplastica/", "Anemia Aplastica");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/globuli-rossi/", "Globuli Rossi");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/reticolociti/", "Globuli Rossi");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/ematocrito/", "Ematocrito");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/rbc/", "RBC");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/hb/", "Emoglobina (HGB o Hb)");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/mch/", "MCH","(contenuto corpuscolare medio di emoglobina)");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/mchc/", "MCHC", "(concentrazione corpuscolare media di emoglobina)");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/rdw/", "RDW-CV e RDW-SD","(variabilità della dimensione o del volume delle cellule dei globuli rossi; SD = deviazione standard; CV = coefficiente di variazione)");

		$result .= "<h4>Piastrine</h4>";
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/piastrine/", "Piastrine");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/mpv-alto-basso/", "MPV","(Volume piastrinico medio)");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/pdw-analisi-del-sangue/", "PDW", "(ampiezza di distribuzione piastrinica)");


		$result .= "<h4>Altro</h4>";
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/creatinina-alta-e-bassa/", "Creatinina");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/albumina-alta-o-bassa/", "Albumina");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/enzimi-epatici/", "Enzimi epatici");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/colesterolo-sintomi-cause/", "Colesterolo");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/esami-del-sangue-in-gravidanza/", "Analisi del sangue in gravidanza");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/thc/", "THC");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/ferritinemia/", "Ferritina");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/carenza-di-ferro/", "Carenza di ferro");
		$result .= GetLinkWithImage("https://www.nonsolodiete.it/transaminasi/", "Transaminasi");


		$result .= "</ul>";
		return $result;
	}
