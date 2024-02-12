<?php

use gik25microdata\ListOfPosts\ListOfPostsMain;
use gik25microdata\ListOfPosts\ListOfPostsRenderHelper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


add_shortcode('lista_single_temptation_island', 'temptation_island_single_handler');
add_shortcode('temptation_island_vip_2019', 'temptation_island_vip_2019_handler');
add_shortcode('amici_celebrities', 'amici_celebrities_handler');
add_shortcode('tale_e_quale_show_2019', 'tale_e_quale_show_2019_handler');

function temptation_island_single_handler($atts, $content = null): string
{
//    $target_url = ReplaceTargetUrlIfStagingBulk($target_url);

    $result = "<ul>";
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.chiecosa.it/alessandro-cannataro/", "Alessandro Cannataro", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/alessandro-zarino/", "Alessandro Zarino", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/alessia-calierno/", "Alessia Calierno", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/federica-lepanto/", "Federica Lepanto", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/giovanni-arrigoni/", "Giovanni Arrigoni", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/giulio-raselli/", "Giulio Raselli", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/cristina-rescigni/", "Cristina Rescigni", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/elena-cianni/", "Elena Cianni", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/javi-martinez/", "Javier Martinez", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/maddalena-vasselli/", "Maddalena Vasselli", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/mattia-birro/", "Mattia Birro", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/moreno-merlo/", "Moreno Merlo", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/nicolas-bovi/", "Nicolas Bovi", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/noemi-malizia/", "Noemi Malizia", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/rodolfo-salemi/", "Rodolfo Salemi", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/sabina-bakanaci/", "Sabina Bakanaci", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/sammy-hassan/", "Sammy Hassan", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/sonia-onelli/", "Sonia Onelli", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/vanessa-cinelli/", "Vanessa Cinelli", ""));

    $result .= "</ul>";
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
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.chiecosa.it/filippo-bisciglia/", "Filippo Bisciglia", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/paola-camassa/", "Pamela Camassa", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/martin-castrogiovanni/", "Martin Castrogiovanni", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/cristina-donadio/", "Cristina Donadio", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/ciro-ferrara/", "Ciro Ferrara", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/massimiliano-varrese/", "Massimiliano Varrese", ""));
    $result .= "<h3>Squadra Blu</h3>";
    $collection->add(new LinkBase("https://www.chiecosa.it/joe-bastianich/", "Joe Bastianich", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/emanuele-filiberto/", "Emanuele Filiberto Di Savoia", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/raniero-monaco-di-lapio/", "Raniero Monaco di Lapio", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/francesca-manzini/", "Francesca Manzini", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/laura-torrisi/", "Laura Torrisi", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/chiara-giordano/", "Chiara Giordano", ""));
    $result .= "</ul>";

    return $result;
}

function temptation_island_vip_2019_handler($atts, $content = null)
{
    $result = "<h3>Tutte le coppie concorrenti di Temptation Island Vip </h3>
	<p> Di seguito tutte le coppie concorrenti di Temptation Island Vip</p>
	<ul>";
    $result .= "<li>";
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.chiecosa.it/nathalie-caldonazzo/", "Nathalie Caldonazzo", ""));
    //" e " . $collection->add(new LinkBase("https://www.chiecosa.it/andrea-ippoliti/", "Andrea Ippoliti", ""));
    $result .= "</li>";

    $result .= "<li>";
    $collection->add(new LinkBase("https://www.chiecosa.it/ciro-petrone/", "Ciro Petrone", ""));
    //" e " . $collection->add(new LinkBase("https://www.chiecosa.it/federica-caputo/", "Federica Caputo", ""));
    $result .= "</li>";

    $result .= "<li>";
    $collection->add(new LinkBase("https://www.chiecosa.it/simone-bonaccorsi/", "Simone Bonaccorsi", ""));
    //" e " . $collection->add(new LinkBase("https://www.chiecosa.it/chiara-esposto/", "Chiara Esposito", ""));
    $result .= "</li>";

    $result .= "<li>";
    $collection->add(new LinkBase("https://www.chiecosa.it/serena-enardu/", "Serena Enardu", ""));
    //" e " . $collection->add(new LinkBase("https://www.chiecosa.it/pago/", "Pago", ""));
    $result .= "</li>";

    $result .= "<li>";
    $collection->add(new LinkBase("https://www.chiecosa.it/anna-pettinelli/", "Anna Pettinelli", ""));
    //" e " . $collection->add(new LinkBase("https://www.chiecosa.it/stefano-andrea-macchi/", "Stefano Macchi", ""));
    $result .= "</li>";

    $result .= "<li>";
    $collection->add(new LinkBase("https://www.chiecosa.it/damiano-er-faina/", "Damiano \"Er Faina\"", ""));
    //" e " . $collection->add(new LinkBase("https://www.chiecosa.it/sharon-macri/", "Sharon Macri", ""));
    $result .= "</li>";

    $result .= "<li>";
    $collection->add(new LinkBase("https://www.chiecosa.it/gabriele-pippo/", "Gabriele Pippo", ""));
    //" e " . $collection->add(new LinkBase("https://www.chiecosa.it/silvia-tirado/", "Silvia Tirado", ""));
    $result .= "</li>";

    $result .= "<li>";
    $collection->add(new LinkBase("https://www.chiecosa.it/alex-belli/", "Alex Belli", ""));
    //" e " . $collection->add(new LinkBase("https://www.chiecosa.it/delia-duran/", "Delia Duran", ""));
    $result .= "</li>";

    $result .= "</ul>";


    //Tentatiori
    $result .= "<h3>Tutti i single di Temptation Island Vip </h3>
<p> Di seguito tutti i single concorrenti di Temptation Island Vip</p>";
    $result .= "<h4>Ragazzi single</h4>
	<div class='my_shortcode_list'>
	<ul class='my_shortcode_list'>";
    $collection->add(new LinkBase("https://www.chiecosa.it/antonio-moriconi/", "Antonio Moriconi", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/fabrizio-baldassarre/", "Fabrizio Baldassarre", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/mattia-bertucco/", "Mattia Bertucco", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/devid-nenci/", "David Nenci", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/alessandro-catania/", "Alessandro Catania", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/valerio-maggiolini/", "Valerio Maggiolini", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/gianmaria-gerolin/", "Gianmaria Gerolin", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/michele-loprieno/", "Michele Loprieno", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/alessandro-graziani/", "Alessandro Graziani", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/jack-queralt/", "Jack Querlat", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/riccardo-costantino/", "Riccardo Costantino", ""));
    $result .= "</div></ul>";


    $result .= "<h4>Ragazze single</h4>
	<div class='my_shortcode_list'><ul class='my_shortcode_list'>";
    $collection->add(new LinkBase("https://www.chiecosa.it/federica-francia/", "Federica Francia", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/federica-spano/", "Federica Spano", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/cecilia-zagarrigo/", "Cecilia Zagarrigo", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/marina-vetrova/", "Marina Vetrova", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/gaia-mastrototaro/", "Gaia Mastrotaro", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/zoe-malucci/", "Zoe Mallucci", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/darya-lapushka/", "Dasha Lapushka", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/valentina-anna-galli/", "Valentina Galli", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/antonietta-fragasso/", "Antonietta Fragrasso", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/alice-bertelli/", "Alice Bertelli", ""));
    $result .= "</div></ul>";
    return $result;
}

function tale_e_quale_show_2019_handler($atts, $content = null)
{
    $result = "<h2>I concorrenti di Tale e Quale Show</h2>";
    $result .= "<div class='my_shortcode_list'>
		<ul class='my_shortcode_list'>";
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.chiecosa.it/francesco-pannofino/", "Francesco Pannofino", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/davide-de-marinis/", "Davide De Marinis", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/tiziana-rivale/", "Tiziana Rivale", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/jessica-morlacchi/", "Jessica Morlacchi", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/lidia-schillaci/", "Lidia Schillaci", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/sara-facciolini/", "Sara Facciolini", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/eva-grimaldi/", "Eva Grimaldi", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/francesco-monte/", "Francesco Monte", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/flora-canto/", "Flora Canto", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/agostino-penna/", "Agostino Penna", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/david-pratelli/", "Davide Pratelli", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/luigi-esposito/", "Luigi Esposito (Gigi e Ross)", ""));
    $collection->add(new LinkBase("https://www.chiecosa.it/rosario-morra/", "Rosario Morra (Gigi e Ross)", ""));
    $result .= "</ul></div>";


    return $result;
}

function linkIfNotSelf2($url, $nome)
{
    global $current_post;
    $permalink = get_permalink($current_post->ID);

    if ($permalink != $url) {
        return "<a href=\"$url\">$nome</a>";
    } else {
        return "$nome";
    }
}

function linkIfNotSelf($target_url, $nome, $removeIfSelf = true): string
{
    global $current_post; //il post corrente
    $current_permalink = get_permalink($current_post->ID);

    if ($current_permalink != $target_url) {
        $target_postid = url_to_postid($target_url);

        if ($target_postid == 0)
            return "";

        $target_post = get_post($target_postid);
        if ($target_post->post_status === "publish") {
            $featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');
            return <<<TAG
<li>
<a href="$target_url">			
<div class="li-img">
	<img src="$featured_img_url" alt="$nome" />		
</div>
<div class="li-text">$nome</div>
</a></li>\n
TAG;
        }
    } else if (!$removeIfSelf) {
        $target_postid = url_to_postid($target_url);

        if ($target_postid == 0)
            return "";

        $target_post = get_post($target_postid);
        if ($target_post->post_status === "publish") {
            $featured_img_url = get_the_post_thumbnail_url($target_post->ID, 'thumbnail');
            return <<<TAG
<li>
<div class="li-img">
	<img src="$featured_img_url" alt="$nome" />		
</div>
<div class="li-text">$nome</div>
</li>\n
TAG;
        }
    }

    return "";
}


//TODO: spostare nella classe base
add_filter('get_the_archive_title', function ($title) {
    if (is_category()) {
        $title = single_cat_title('', false);
    } elseif (is_tag()) {
        $title = single_tag_title('', false);
    } elseif (is_author()) {
        $title = '<span class="vcard">' . get_the_author() . '</span>';
    }

    return $title;
});


//TODO: capire se viene utilizzato
add_filter('embed_handler_html', 'wpse_202291_embed_handler_html', 1, 3);
function wpse_202291_embed_handler_html($return, $url, $attr)
{
    // process $return
    return $return;
}


add_filter('the_content', 'modify_content_chiecosa', 1, 1);
function modify_content_chiecosa($content)
{

    $instagram = "<a class=\"instagram\" href=\"https://www.instagram.com/chiecosa.it/\">
          <i class=\"icon icon--instagram-64\"></i>
          Seguici sul nuovo profilo instagram!
        </a>";

    if (is_singular('post') && in_the_loop() && is_main_query() && (stripos($content, "Seguici sul nostro profilo") === false)) {

        $primaOccorrenza = strpos($content, "</ul>", 0);

        //$secondaOccorrenza = strrpos($content,"</ul>", $primaOccorrenza + 1);
        //$primaOccorrenza-=4;

        $replacement = "<li><strong>Seguici sul nostro profilo Instagram Ufficiale: <a href=\"https://www.instagram.com/chiecosa.it/\">@chiecosa.it</a></strong></li>";
        $content = substr_replace($content, $replacement, $primaOccorrenza, 0);


        return $content . $instagram;

    }
    return $content . $instagram;
}



//TODO: spostare nella classe base
add_filter('the_excerpt_rss', 'rss_post_thumbnail');
add_filter('the_content_feed', 'rss_post_thumbnail');
function rss_post_thumbnail($content)
{
    global $post;
    if (has_post_thumbnail($post->ID)) {
        $content = '<media:content>' . get_the_post_thumbnail($post->ID) .
            '</media:content>';
    }
    return $content;
}

