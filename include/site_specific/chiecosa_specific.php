<?php
	/**
	 * Created by PhpStorm.
	 * User: g.salvi
	 * Date: 17/09/2019
	 * Time: 14:44
	 */


	add_shortcode('lista_single_temptation_island', 'temptation_island_single_handler');
	add_shortcode('temptation_island_vip_2019', 'temptation_island_vip_2019_handler');
	add_shortcode('amici_celebrities', 'amici_celebrities_handler');
	add_shortcode('tale_e_quale_show_2019', 'tale_e_quale_show_2019_handler');

	function temptation_island_single_handler($atts, $content = null)
	{
		$result="<ul>";
		$result.= linkIfNotSelf("https://www.chiecosa.it/alessandro-cannataro/", 	"Alessandro Cannataro");
		$result.= linkIfNotSelf("https://www.chiecosa.it/alessandro-zarino/", 	    "Alessandro Zarino");
		$result.= linkIfNotSelf("https://www.chiecosa.it/alessia-calierno/", 		"Alessia Calierno");
		$result.= linkIfNotSelf("https://www.chiecosa.it/federica-lepanto/", 		"Federica Lepanto");
		$result.= linkIfNotSelf("https://www.chiecosa.it/giovanni-arrigoni/", 	    "Giovanni Arrigoni");
		$result.= linkIfNotSelf("https://www.chiecosa.it/giulio-raselli/", 		    "Giulio Raselli");
		$result.= linkIfNotSelf("https://www.chiecosa.it/cristina-rescigni/", 		"Cristina Rescigni");
		$result.= linkIfNotSelf("https://www.chiecosa.it/elena-cianni/", 			"Elena Cianni");
		$result.= linkIfNotSelf("https://www.chiecosa.it/javi-martinez/", 		    "Javier Martinez");
		$result.= linkIfNotSelf("https://www.chiecosa.it/maddalena-vasselli/", 		"Maddalena Vasselli");
		$result.= linkIfNotSelf("https://www.chiecosa.it/mattia-birro/", 			"Mattia Birro");
		$result.= linkIfNotSelf("https://www.chiecosa.it/moreno-merlo/", 			"Moreno Merlo");
		$result.= linkIfNotSelf("https://www.chiecosa.it/nicolas-bovi/", 			"Nicolas Bovi");
		$result.= linkIfNotSelf("https://www.chiecosa.it/noemi-malizia/", 		    "Noemi Malizia");
		$result.= linkIfNotSelf("https://www.chiecosa.it/rodolfo-salemi/", 		    "Rodolfo Salemi");
		$result.= linkIfNotSelf("https://www.chiecosa.it/sabina-bakanaci/", 		"Sabina Bakanaci");
		$result.= linkIfNotSelf("https://www.chiecosa.it/sammy-hassan/", 			"Sammy Hassan");
		$result.= linkIfNotSelf("https://www.chiecosa.it/sonia-onelli/", 			"Sonia Onelli");
		$result.= linkIfNotSelf("https://www.chiecosa.it/vanessa-cinelli/", 		"Vanessa Cinelli");

		$result.= "</ul>";
		return $result;
	}

//	function amici_celebrities_handler($atts, $content = null)
//	{
//		$result="<h2> I concorrenti di Amici Celebrities</h2>";
//		$result.="<table class='my_shortcode_list'>
//	<tbody>
//	<tr>
//	<td><ul class='my_shortcode_list'>
//	<h3 style='border: black solid 1px;      padding: 14px;font-family: Arial, Helvetica, sans-serif; '>Squadra Bianca</h3>";
//		$result.= linkIfNotSelf("https://www.chiecosa.it/filippo-bisciglia/", 		"Filippo Bisciglia", false);
//		$result.= linkIfNotSelf("https://www.chiecosa.it/paola-camassa/", 		"Pamela Camassa", false);
//		$result.= linkIfNotSelf("https://www.chiecosa.it/martin-castrogiovanni/", 		"Martin Castrogiovanni", false);
//		$result.= linkIfNotSelf("https://www.chiecosa.it/cristina-donadio/", 		"Cristina Donadio", false);
//		$result.= linkIfNotSelf("https://www.chiecosa.it/ciro-ferrara/", 		"Ciro Ferrara", false);
//		$result.= linkIfNotSelf("https://www.chiecosa.it/massimiliano-varrese/", 		"Massimiliano Varrese", false);
//		$result.= "</ul></td>
//<td><ul class='my_shortcode_list'>";
//		$result.= "<h3 style='background-color: #0d87ff;     padding: 14px; border: black solid 1px; color: white; font-family: Arial, Helvetica, sans-serif; '>Squadra Blu</h3>";
//		$result.= linkIfNotSelf("https://www.chiecosa.it/joe-bastianich/", 	"Joe Bastianich", false);
//		$result.= linkIfNotSelf("https://www.chiecosa.it/emanuele-filiberto/", 		"Emanuele Filiberto Di Savoia", false);
//		$result.= linkIfNotSelf("https://www.chiecosa.it/raniero-monaco-di-lapio/", 		"Raniero Monaco di Lapio", false);
//		$result.= linkIfNotSelf("https://www.chiecosa.it/francesca-manzini/", 		"Francesca Manzini", false);
//		$result.= linkIfNotSelf("https://www.chiecosa.it/laura-torrisi/", 	    "Laura Torrisi", false);
//		$result.= linkIfNotSelf("https://www.chiecosa.it/chiara-giordano/", 	    "Chiara Giordano", false);
//		$result.= "</ul></td></tr></tbody></table>";
//
//		return $result;
//	}

	function amici_celebrities_handler($atts, $content = null)
	{
		$result = "<h2> I concorrenti di Amici Celebrities</h2>";
		$result .= "<ul class='my_shortcode_list'>";
		$result .= "<h3>Squadra Bianca</h3>";
		$result .= GetLinkWithImage("https://www.chiecosa.it/filippo-bisciglia/", "Filippo Bisciglia","", false);
		$result .= GetLinkWithImage("https://www.chiecosa.it/paola-camassa/", "Pamela Camassa", "",false);
		$result .= GetLinkWithImage("https://www.chiecosa.it/martin-castrogiovanni/", "Martin Castrogiovanni", "",false);
		$result .= GetLinkWithImage("https://www.chiecosa.it/cristina-donadio/", "Cristina Donadio", "",false);
		$result .= GetLinkWithImage("https://www.chiecosa.it/ciro-ferrara/", "Ciro Ferrara", "",false);
		$result .= GetLinkWithImage("https://www.chiecosa.it/massimiliano-varrese/", "Massimiliano Varrese", "",false);
		$result .= "<h3>Squadra Blu</h3>";
		$result .= GetLinkWithImage("https://www.chiecosa.it/joe-bastianich/", "Joe Bastianich", "",false);
		$result .= GetLinkWithImage("https://www.chiecosa.it/emanuele-filiberto/", "Emanuele Filiberto Di Savoia", "",false);
		$result .= GetLinkWithImage("https://www.chiecosa.it/raniero-monaco-di-lapio/", "Raniero Monaco di Lapio", "",false);
		$result .= GetLinkWithImage("https://www.chiecosa.it/francesca-manzini/", "Francesca Manzini", "",false);
		$result .= GetLinkWithImage("https://www.chiecosa.it/laura-torrisi/", "Laura Torrisi", "",false);
		$result .= GetLinkWithImage("https://www.chiecosa.it/chiara-giordano/", "Chiara Giordano", "",false);
		$result .= "</ul>";

		return $result;
}

	function temptation_island_vip_2019_handler($atts, $content = null)
	{
		$result="<h3>Tutte le coppie concorrenti di Temptation Island Vip </h3>
	<p> Di seguito tutte le coppie concorrenti di Temptation Island Vip</p>
	<ul>";
		$result.= "<li>";
		$result.= linkIfNotSelf2("https://www.chiecosa.it/nathalie-caldonazzo/", "Nathalie Caldonazzo");
		$result.= " e ".linkIfNotSelf2("https://www.chiecosa.it/andrea-ippoliti/", "Andrea Ippoliti");
		$result.= "</li>";

		$result.= "<li>";
		$result.= linkIfNotSelf2("https://www.chiecosa.it/ciro-petrone/", "Ciro Petrone");
		$result.= " e ".linkIfNotSelf2("https://www.chiecosa.it/federica-caputo/", "Federica Caputo");
		$result.= "</li>";

		$result.= "<li>";
		$result.= linkIfNotSelf2("https://www.chiecosa.it/simone-bonaccorsi/", "Simone Bonaccorsi");
		$result.= " e ".linkIfNotSelf2("https://www.chiecosa.it/chiara-esposto/", "Chiara Esposito");
		$result.= "</li>";

		$result.= "<li>";
		$result.= linkIfNotSelf2("https://www.chiecosa.it/serena-enardu/", "Serena Enardu");
		$result.= " e ".linkIfNotSelf2("https://www.chiecosa.it/pago/", 			"Pago");
		$result.= "</li>";

		$result.= "<li>";
		$result.= linkIfNotSelf2("https://www.chiecosa.it/anna-pettinelli/", "Anna Pettinelli");
		$result.= " e ".linkIfNotSelf2("https://www.chiecosa.it/stefano-andrea-macchi/", "Stefano Macchi");
		$result.= "</li>";

		$result.= "<li>";
		$result.= linkIfNotSelf2("https://www.chiecosa.it/damiano-er-faina/", 		"Damiano \"Er Faina\"");
		$result.= " e ".linkIfNotSelf2("https://www.chiecosa.it/sharon-macri/", "Sharon Macri");
		$result.= "</li>";

		$result.= "<li>";
		$result.= linkIfNotSelf2("https://www.chiecosa.it/gabriele-pippo/", 		"Gabriele Pippo");
		$result.= " e ".linkIfNotSelf2("https://www.chiecosa.it/silvia-tirado/", "Silvia Tirado");
		$result.= "</li>";

		$result.= "<li>";
		$result.= linkIfNotSelf2("https://www.chiecosa.it/alex-belli/", 		"Alex Belli");
		$result.= " e ".linkIfNotSelf2("https://www.chiecosa.it/delia-duran/", "Delia Duran");
		$result.= "</li>";

		$result.= "</ul>";


		//Tentatiori
		$result.= "<h3>Tutti i single di Temptation Island Vip </h3>
<p> Di seguito tutti i single concorrenti di Temptation Island Vip</p>";
		$result.= "<h4>Ragazzi single</h4>
	<div class='my_shortcode_list'>
	<ul class='my_shortcode_list'>";
		$result.= GetLinkWithImage("https://www.chiecosa.it/antonio-moriconi/", "Antonio Moriconi");
		$result.= GetLinkWithImage("https://www.chiecosa.it/fabrizio-baldassarre/", "Fabrizio Baldassarre");
		$result.= GetLinkWithImage("https://www.chiecosa.it/mattia-bertucco/", "Mattia Bertucco");
		$result.= GetLinkWithImage("https://www.chiecosa.it/devid-nenci/", "David Nenci");
		$result.= GetLinkWithImage("https://www.chiecosa.it/alessandro-catania/", "Alessandro Catania");
		$result.= GetLinkWithImage("https://www.chiecosa.it/valerio-maggiolini/", "Valerio Maggiolini");
		$result.= GetLinkWithImage("https://www.chiecosa.it/gianmaria-gerolin/", "Gianmaria Gerolin");
		$result.= GetLinkWithImage("https://www.chiecosa.it/michele-loprieno/", "Michele Loprieno");
		$result.= GetLinkWithImage("https://www.chiecosa.it/alessandro-graziani/", "Alessandro Graziani");
		$result.= GetLinkWithImage("https://www.chiecosa.it/jack-queralt/", "Jack Querlat");
		$result.= GetLinkWithImage("https://www.chiecosa.it/riccardo-costantino/", "Riccardo Costantino");
		$result.= "</div></ul>";


		$result.= "<h4>Ragazze single</h4>
	<div class='my_shortcode_list'><ul class='my_shortcode_list'>";
		$result.= GetLinkWithImage("https://www.chiecosa.it/federica-francia/", "Federica Francia");
		$result.= GetLinkWithImage("https://www.chiecosa.it/federica-spano/", "Federica Spano");
		$result.= GetLinkWithImage("https://www.chiecosa.it/cecilia-zagarrigo/", "Cecilia Zagarrigo");
		$result.= GetLinkWithImage("https://www.chiecosa.it/marina-vetrova/", "Marina Vetrova");
		$result.= GetLinkWithImage("https://www.chiecosa.it/gaia-mastrototaro/", "Gaia Mastrotaro");
		$result.= GetLinkWithImage("https://www.chiecosa.it/zoe-malucci/", "Zoe Mallucci");
		$result.= GetLinkWithImage("https://www.chiecosa.it/darya-lapushka/", "Dasha Lapushka");
		$result.= GetLinkWithImage("https://www.chiecosa.it/valentina-anna-galli/", "Valentina Galli");
		$result.= GetLinkWithImage("https://www.chiecosa.it/antonietta-fragasso/", "Antonietta Fragrasso");
		$result.= GetLinkWithImage("https://www.chiecosa.it/alice-bertelli/", "Alice Bertelli");
		$result.= "</div></ul>";
		return $result;
	}

	function tale_e_quale_show_2019_handler($atts, $content = null)
	{
		$result="<h2>I concorrenti di Tale e Quale Show</h2>";
		$result.="<div class='my_shortcode_list'>
		<ul class='my_shortcode_list'>";
		$result.= GetLinkWithImage("https://www.chiecosa.it/francesco-pannofino/", 		"Francesco Pannofino");
		$result.= GetLinkWithImage("https://www.chiecosa.it/davide-de-marinis/", 		"Davide De Marinis");
		$result.= GetLinkWithImage("https://www.chiecosa.it/tiziana-rivale/", 		"Tiziana Rivale");
		$result.= GetLinkWithImage("https://www.chiecosa.it/jessica-morlacchi/", 		"Jessica Morlacchi");
		$result.= GetLinkWithImage("https://www.chiecosa.it/lidia-schillaci/", 		"Lidia Schillaci");
		$result.= GetLinkWithImage("https://www.chiecosa.it/sara-facciolini/", 		"Sara Facciolini");
		$result.= GetLinkWithImage("https://www.chiecosa.it/eva-grimaldi/", 		"Eva Grimaldi");
		$result.= GetLinkWithImage("https://www.chiecosa.it/francesco-monte/", 		"Francesco Monte");
		$result.= GetLinkWithImage("https://www.chiecosa.it/flora-canto/", 		"Flora Canto");
		$result.= GetLinkWithImage("https://www.chiecosa.it/agostino-penna/", 		"Agostino Penna");
		$result.= GetLinkWithImage("https://www.chiecosa.it/david-pratelli/", 		"Davide Pratelli");
		$result.= GetLinkWithImage("https://www.chiecosa.it/luigi-esposito/", 		"Luigi Esposito (Gigi e Ross)");
		$result.= GetLinkWithImage("https://www.chiecosa.it/rosario-morra/", 		"Rosario Morra (Gigi e Ross)");
		$result.= "</ul></div>";


		return $result;
	}


	function modify_content_chiecosa($content)
	{

		$instagram = "<a class=\"instagram\" href=\"https://www.instagram.com/chiecosa.it/\">
          <i class=\"icon icon--instagram-64\"></i>
          Seguici sul nuovo profilo instagram!
        </a>";

		if ( is_singular('post')  && in_the_loop() && is_main_query() && (stripos($content, "Seguici sul nostro profilo") === false)) {

			$primaOccorrenza = strpos($content, "</ul>", 0);

			//$secondaOccorrenza = strrpos($content,"</ul>", $primaOccorrenza + 1);
			//$primaOccorrenza-=4;

			$replacement = "<li><strong>Seguici sul nostro profilo Instagram Ufficiale: <a href=\"https://www.instagram.com/chiecosa.it/\">@chiecosa.it</a></strong></li>";
			$content = substr_replace($content, $replacement, $primaOccorrenza, 0);



			return $content.$instagram;

		}
		return $content.$instagram;
	}


	add_filter('the_content', 'modify_content_chiecosa', 1, 1 );





	add_filter( 'get_the_archive_title', function ($title) {
		if ( is_category() ) {
			$title = single_cat_title( '', false );
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
		} elseif ( is_author() ) {
			$title = '<span class="vcard">' . get_the_author() . '</span>' ;
		}

		return $title;
	});


	add_filter( 'embed_handler_html', 'wpse_202291_embed_handler_html', 1, 3 );
	function wpse_202291_embed_handler_html( $return, $url, $attr ) {
		// process $return
		return $return;
	}


function rss_post_thumbnail($content) {
    global $post;
    if(has_post_thumbnail($post->ID)) {
        $content = '<media:content>' . get_the_post_thumbnail($post->ID) .
            '</media:content>';
    }
    return $content;
}

add_filter('the_excerpt_rss', 'rss_post_thumbnail');
add_filter('the_content_feed', 'rss_post_thumbnail');