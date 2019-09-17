<?php ////////////////////
/*
Plugin Name: Revious Microdata
Plugin URI:
Description: Add beautifully styled quotes to your Wordpress posts
Version:     0.1
Author:      Gianluigi Salvi
 */

//add_action('wp_enqueue_scripts', 'revious_microdata_styles');

function revious_microdata_styles()
{
    // Register the style like this for a plugin:
    //wp_register_style('revious-quotes-styles', plugins_url('/revious_microdata.css', __FILE__), array(), '1.7.5', 'all');
    // For either a plugin or a theme, you can then enqueue the style:
    //wp_enqueue_style('revious-quotes-styles');
}


add_shortcode('microdata_telefono', 'microdata_telefono');
add_shortcode('microdata_prezzo', 'microdata_prezzo');
add_shortcode('youtube', 'youtube_handler');
add_shortcode('quote', 'quote_handler');
add_shortcode('flexlist', 'flexlist_handler');
add_shortcode('lista_single_temptation_island', 'temptation_island_single_handler');
add_shortcode('temptation_island_vip_2019', 'temptation_island_vip_2019_handler');
add_shortcode('amici_celebrities', 'amici_celebrities_handler');
add_shortcode('tale_e_quale_show_2019', 'tale_e_quale_show_2019_handler');




function microdata_telefono($atts, $content = null)
{
    $attrValue = shortcode_atts(array(
        'organizationname' => null // (Optional)
    ), $atts);

    $organizationName = $atts['organizationname'];

    $telefonoPuro = wp_strip_all_tags( $content, true);

    if(substr( $telefonoPuro, 0, 1 ) === "+")
        $telefonoSchema = $telefonoPuro;
    else
        $telefonoSchema = "+39-$telefonoPuro";


    $result = "<a href=\"tel:$telefonoPuro\" style=\"color:green;\">$content</a>";

    if(!is_null($organizationName) && !empty($organizationName))
    {
    $result = <<<EOF
<span>
  <span>$organizationName</span>
  $result  
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "$organizationName",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "$telefonoSchema",
        "contactType": "customer support"
      }
    }
    </script>
  
</span>
EOF;
    }

    return $result;
}

function microdata_prezzo($atts, $content = null)
{
    $result = <<<EOF
<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
    <span itemprop="priceCurrency" content="EUR">€</span>
    <span itemprop="price">
EOF
        .do_shortcode($content)
    ."</span>" //Fine price span
."</span>"; //Fine offer span


    return $result;
}

function youtube_handler($atts, $content = null)
{
    $result = wp_oembed_get($atts["url"]);
    return $result;
}

function quote_handler($atts, $content = null)
{
    $result = "<blockquote>$content</blockquote>";
    return $result;
}

function flexlist_handler($atts, $content = null)
{
    $html = $content;
    $dom = new DOMDocument;
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//ul");
    foreach($nodes as $node) {
        $node->setAttribute('style', 'display: flex; flex-wrap: wrap;');
    }

    $nodes = $xpath->query("//li");
    foreach($nodes as $node) {
        $node->setAttribute('style', 'margin-right: 5px;');
    }

    return $dom->saveHTML();
}

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

function amici_celebrities_handler($atts, $content = null)
{
	$result="<h2> I concorrenti di Amici Celebrities</h2>";
	$result.="<table class='my_shortcode_list'>
	<tbody>
	<tr>
	<td><ul class='my_shortcode_list'>
	<h3 style='border: black solid 1px;      padding: 14px;font-family: Arial, Helvetica, sans-serif; '>Squadra Bianca</h3>";
	$result.= linkIfNotSelf("https://www.chiecosa.it/filippo-bisciglia/", 		"Filippo Bisciglia", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/paola-camassa/", 		"Pamela Camassa", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/martin-castrogiovanni/", 		"Martin Castrogiovanni", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/cristina-donadio/", 		"Cristina Donadio", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/ciro-ferrara/", 		"Ciro Ferrara", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/massimiliano-varrese/", 		"Massimiliano Varrese", false);
	$result.= "</ul></td>
<td><ul class='my_shortcode_list'>";
	$result.= "<h3 style='background-color: #0d87ff;     padding: 14px; border: black solid 1px; color: white; font-family: Arial, Helvetica, sans-serif; '>Squadra Blu</h3>";
	$result.= linkIfNotSelf("https://www.chiecosa.it/joe-bastianich/", 	"Joe Bastianich", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/emanuele-filiberto/", 		"Emanuele Filiberto Di Savoia", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/raniero-monaco-di-lapio/", 		"Raniero Monaco di Lapio", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/francesca-manzini/", 		"Francesca Mancini", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/laura-torrisi/", 	    "Laura Torrisi", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/chiara-giordano/", 	    "Chiara Giordano", false);
	$result.= "</ul></td></tr></tbody></table>";

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

	$result.= "</ul>";


	//Tentatiori
	$result.= "<h3>Tutti i single di Temptation Island Vip </h3>
<p> Di seguito tutti i single concorrenti di Temptation Island Vip</p>";
	$result.= "<h4>Ragazzi single</h4>
	<div class='my_shortcode_list'>
	<ul class='my_shortcode_list'>";
	//$result.= linkIfNotSelf("https://www.chiecosa.it/nicolo-brigante/", "Nicolò Brigante");
	$result.= linkIfNotSelf("https://www.chiecosa.it/antonio-moriconi/", "Antonio Moriconi");
	$result.= linkIfNotSelf("https://www.chiecosa.it/fabrizio-baldassarre/", "Fabrizio Baldassarre");
	$result.= linkIfNotSelf("https://www.chiecosa.it/mattia-bertucco/", "Mattia Bertucco");
	$result.= linkIfNotSelf("https://www.chiecosa.it/devid-nenci/", "David Nenci");
	$result.= linkIfNotSelf("https://www.chiecosa.it/alessandro-catania/", "Alessandro Catania");
	$result.= linkIfNotSelf("https://www.chiecosa.it/valerio-maggiolini/", "Valerio Maggiolini");
	$result.= linkIfNotSelf("https://www.chiecosa.it/gianmaria-gerolin/", "Gianmaria Gerolin");
	$result.= linkIfNotSelf("https://www.chiecosa.it/michele-loprieno/", "Michele Loprieno");
	$result.= linkIfNotSelf("https://www.chiecosa.it/alessandro-graziani/", "Alessandro Graziani");
	$result.= linkIfNotSelf("https://www.chiecosa.it/jack-queralt/", "Jack Querlat");
	$result.= linkIfNotSelf("https://www.chiecosa.it/riccardo-costantino/", "Riccardo Costantino");
	//$result.= linkIfNotSelf("https://www.chiecosa.it/michele-mencherini/", "Michele Mencherini");
	$result.= "</div></ul>";


	$result.= "<h4>Ragazze single</h4>
	<div class='my_shortcode_list'><ul class='my_shortcode_list'>";
	$result.= linkIfNotSelf("https://www.chiecosa.it/federica-francia/", "Federica Francia");
	$result.= linkIfNotSelf("https://www.chiecosa.it/federica-spano/", "Federica Spano");
	$result.= linkIfNotSelf("https://www.chiecosa.it/cecilia-zagarrigo/", "Cecilia Zagarrigo");
	//$result.= linkIfNotSelf("https://www.chiecosa.it/giorgia-caldarulo/", "Giorgia Caldarulo");
	$result.= linkIfNotSelf("https://www.chiecosa.it/marina-vetrova/", "Marina Vetrova");
	$result.= linkIfNotSelf("https://www.chiecosa.it/gaia-mastrototaro/", "Gaia Mastrotaro");
	$result.= linkIfNotSelf("https://www.chiecosa.it/zoe-malucci/", "Zoe Mallucci");
	$result.= linkIfNotSelf("https://www.chiecosa.it/darya-lapushka/", "Dasha Lapushka");
	$result.= linkIfNotSelf("https://www.chiecosa.it/valentina-anna-galli/", "Valentina Galli");
	$result.= linkIfNotSelf("https://www.chiecosa.it/antonietta-fragasso/", "Antonietta Fragrasso");
	$result.= linkIfNotSelf("https://www.chiecosa.it/alice-bertelli/", "Alice Bertelli");


	$result.= "</div></ul>";
	return $result;
}

function tale_e_quale_show_2019_handler($atts, $content = null)
{
	$result="<h2>I concorrenti di Tale e Quale Show</h2>";
	$result.="<div class='my_shortcode_list'><ul class='my_shortcode_list'>";
	$result.= linkIfNotSelf("https://www.chiecosa.it/francesco-pannofino/", 		"Francesco Pannofino", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/davide-de-marinis/", 		"Davide De Marinis", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/tiziana-rivale/", 		"Tiziana Rivale", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/jessica-morlacchi/", 		"Jessica Morlacchi", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/lidia-schillaci/", 		"Lidia Schillaci", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/sara-facciolini/", 		"Sara Facciolini", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/eva-grimaldi/", 		"Eva Grimaldi", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/francesco-monte/", 		"Francesco Monte", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/flora-canto/", 		"Flora Canto", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/agostino-penna/", 		"Agostino Penna", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/david-pratelli/", 		"Davide Pratelli", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/luigi-esposito/", 		"Luigi Esposito (Gigi e Ross)", false);
	$result.= linkIfNotSelf("https://www.chiecosa.it/rosario-morra/", 		"Rosario Morra (Gigi e Ross)", false);
	$result.= "</ul></div>";


	return $result;
}


function linkIfNotSelf($target_url, $nome, $removeIfSelf = true)
{
	global $current_post; //il post corrente
	$current_permalink = get_permalink( $current_post->ID );
	if($current_permalink != $target_url)
	{
		//$target_url = str_replace("www.chiecosa.it", "wordpress-217146-896149.cloudwaysapps.com",$target_url);
		$target_postid = url_to_postid( $target_url );

		if($target_postid == 0)
			return "";

		$target_post = get_post($target_postid);
		if($target_post->post_status === "publish")
		{
			$featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');
			return "<li>
<a href=\"$target_url\">			
<div class=\"li-img\">
	<img src=\"$featured_img_url\" alt=\"$nome\" />		
</div>
<div class=\"li-text\">$nome</div>
</a></li>\n";
		}
	}
	else if(!$removeIfSelf)
	{
		//$target_url = str_replace("www.chiecosa.it", "wordpress-217146-896149.cloudwaysapps.com",$target_url);
		$target_postid = url_to_postid( $target_url );

		if($target_postid == 0)
			return "";

		$target_post = get_post($target_postid);
		if($target_post->post_status === "publish")
		{
			$featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');
			return "<li>
<div class=\"li-img\">
	<img src=\"$featured_img_url\" alt=\"$nome\" />		
</div>
<div class=\"li-text\">$nome</div>
</li>\n";
		}
	}
}


function linkIfNotSelf2($url, $nome)
{
		global $current_post;
		$permalink = get_permalink( $current_post->ID );

		if($permalink != $url)
		{
			return "<a href=\"$url\">$nome</a>";
		}
		else
		{
			return "$nome";
		}
}



add_action( 'init', 'revious_microdata_buttons' );

function revious_microdata_buttons() {
    add_filter("mce_external_plugins", "revious_microdata_add_buttons");
    add_filter('mce_buttons', 'revious_microdata_register_buttons');
}

function revious_microdata_add_buttons($plugin_array) {
    $plugin_array['revious_microdata'] = plugins_url( '/revious-microdata.js', __FILE__ );
    return $plugin_array;
}

function revious_microdata_register_buttons($buttons) {
    array_push( $buttons, 'md_telefono_btn', 'boxinfo-menu' );
    array_push( $buttons, 'md_prezzo_btn', 'boxinfo-menu' );
    return $buttons;
}

//

function load_css_single_pages() {
    if(is_single())
    {
        $plugin_url = plugin_dir_url( __FILE__ );
        wp_enqueue_style( 'css_single_pages', trailingslashit( $plugin_url ) . 'revious-microdata.css', array(  ) );
    }
    //else if(is_category() || is_tag())
}

add_action( 'wp_enqueue_scripts', 'load_css_single_pages', 1001 );


//Customizzazione delle liste per chiecosa

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

//    if ( is_single() ) {
//        global $post;
//        error_reporting(E_ALL);
//
//        $content = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
//
//        $html = $content;
//        $dom = new DOMDocument;
//        $dom->loadHTML($html);
//        $xpath = new DOMXPath($dom);
//        $nodes = $xpath->query("//ul");
//
//        $firstUl = $nodes[0];
//
//        //$dom2 = new DOMDocument;
//        //$dom2->loadHTML("<li><strong>Seguici sul nostro profilo Instagram Ufficiale: </strong><a href=\"https://www.instagram.com/chiecosa.it/\">@chiecosa.it</a></li>");
//        //$dom2->importNode($dom2->documentElement,true);
//        //$bar = $dom->createElement("bar");
//        //$firstUl = $firstUl->appendChild($bar);
//
//        return $dom->saveHTML();
//    }
//    return $content;
}


add_filter('the_content', 'modify_content_chiecosa', 1, 1 );



//add_filter( 'xmlrpc_enabled', '__return_false' );

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