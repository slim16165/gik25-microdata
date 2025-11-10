<?php
namespace gik25microdata\site_specific;

use gik25microdata\ListOfPosts\ListOfPostsHelper;
use gik25microdata\LinkGenerator\LinkGenerator;
use gik25microdata\LinkGenerator\LinkCollectionBuilder;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


add_shortcode('lista_single_temptation_island', __NAMESPACE__ . '\\temptation_island_single_handler');
add_shortcode('temptation_island_vip_2019', __NAMESPACE__ . '\\temptation_island_vip_2019_handler');
add_shortcode('amici_celebrities', __NAMESPACE__ . '\\amici_celebrities_handler');
add_shortcode('tale_e_quale_show_2019', __NAMESPACE__ . '\\tale_e_quale_show_2019_handler');

function temptation_island_single_handler($atts, $content = null)
{
    $links = [
        ['target_url' => 'https://www.chiecosa.it/alessandro-cannataro/', 'nome' => 'Alessandro Cannataro'],
        ['target_url' => 'https://www.chiecosa.it/alessandro-zarino/', 'nome' => 'Alessandro Zarino'],
        ['target_url' => 'https://www.chiecosa.it/alessia-calierno/', 'nome' => 'Alessia Calierno'],
        ['target_url' => 'https://www.chiecosa.it/federica-lepanto/', 'nome' => 'Federica Lepanto'],
        ['target_url' => 'https://www.chiecosa.it/giovanni-arrigoni/', 'nome' => 'Giovanni Arrigoni'],
        ['target_url' => 'https://www.chiecosa.it/giulio-raselli/', 'nome' => 'Giulio Raselli'],
        ['target_url' => 'https://www.chiecosa.it/cristina-rescigni/', 'nome' => 'Cristina Rescigni'],
        ['target_url' => 'https://www.chiecosa.it/elena-cianni/', 'nome' => 'Elena Cianni'],
        ['target_url' => 'https://www.chiecosa.it/javi-martinez/', 'nome' => 'Javier Martinez'],
        ['target_url' => 'https://www.chiecosa.it/maddalena-vasselli/', 'nome' => 'Maddalena Vasselli'],
        ['target_url' => 'https://www.chiecosa.it/mattia-birro/', 'nome' => 'Mattia Birro'],
        ['target_url' => 'https://www.chiecosa.it/moreno-merlo/', 'nome' => 'Moreno Merlo'],
        ['target_url' => 'https://www.chiecosa.it/nicolas-bovi/', 'nome' => 'Nicolas Bovi'],
        ['target_url' => 'https://www.chiecosa.it/noemi-malizia/', 'nome' => 'Noemi Malizia'],
        ['target_url' => 'https://www.chiecosa.it/rodolfo-salemi/', 'nome' => 'Rodolfo Salemi'],
        ['target_url' => 'https://www.chiecosa.it/sabina-bakanaci/', 'nome' => 'Sabina Bakanaci'],
        ['target_url' => 'https://www.chiecosa.it/sammy-hassan/', 'nome' => 'Sammy Hassan'],
        ['target_url' => 'https://www.chiecosa.it/sonia-onelli/', 'nome' => 'Sonia Onelli'],
        ['target_url' => 'https://www.chiecosa.it/vanessa-cinelli/', 'nome' => 'Vanessa Cinelli'],
    ];
    
    return LinkCollectionBuilder::create()
        ->addLinks($links)
        ->withImage(true)
        ->removeIfSelf(true)
        ->ulClass('my_shortcode_list')
        ->build();
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
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/filippo-bisciglia/", "Filippo Bisciglia", "", false);
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/paola-camassa/", "Pamela Camassa", "", false);
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/martin-castrogiovanni/", "Martin Castrogiovanni", "", false);
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/cristina-donadio/", "Cristina Donadio", "", false);
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/ciro-ferrara/", "Ciro Ferrara", "", false);
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/massimiliano-varrese/", "Massimiliano Varrese", "", false);
    $result .= "<h3>Squadra Blu</h3>";
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/joe-bastianich/", "Joe Bastianich", "", false);
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/emanuele-filiberto/", "Emanuele Filiberto Di Savoia", "", false);
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/raniero-monaco-di-lapio/", "Raniero Monaco di Lapio", "", false);
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/francesca-manzini/", "Francesca Manzini", "", false);
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/laura-torrisi/", "Laura Torrisi", "", false);
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/chiara-giordano/", "Chiara Giordano", "", false);
    $result .= "</ul>";

    return $result;
}

function temptation_island_vip_2019_handler($atts, $content = null)
{
    $result = "<h3>Tutte le coppie concorrenti di Temptation Island Vip</h3>";
    $result .= "<p>Di seguito tutte le coppie concorrenti di Temptation Island Vip</p>";
    $result .= "<ul>";
    
    // Coppie
    $coppie = [
        ['url1' => 'https://www.chiecosa.it/nathalie-caldonazzo/', 'nome1' => 'Nathalie Caldonazzo',
         'url2' => 'https://www.chiecosa.it/andrea-ippoliti/', 'nome2' => 'Andrea Ippoliti'],
        ['url1' => 'https://www.chiecosa.it/ciro-petrone/', 'nome1' => 'Ciro Petrone',
         'url2' => 'https://www.chiecosa.it/federica-caputo/', 'nome2' => 'Federica Caputo'],
        ['url1' => 'https://www.chiecosa.it/simone-bonaccorsi/', 'nome1' => 'Simone Bonaccorsi',
         'url2' => 'https://www.chiecosa.it/chiara-esposto/', 'nome2' => 'Chiara Esposito'],
        ['url1' => 'https://www.chiecosa.it/serena-enardu/', 'nome1' => 'Serena Enardu',
         'url2' => 'https://www.chiecosa.it/pago/', 'nome2' => 'Pago'],
        ['url1' => 'https://www.chiecosa.it/anna-pettinelli/', 'nome1' => 'Anna Pettinelli',
         'url2' => 'https://www.chiecosa.it/stefano-andrea-macchi/', 'nome2' => 'Stefano Macchi'],
        ['url1' => 'https://www.chiecosa.it/damiano-er-faina/', 'nome1' => 'Damiano "Er Faina"',
         'url2' => 'https://www.chiecosa.it/sharon-macri/', 'nome2' => 'Sharon Macri'],
        ['url1' => 'https://www.chiecosa.it/gabriele-pippo/', 'nome1' => 'Gabriele Pippo',
         'url2' => 'https://www.chiecosa.it/silvia-tirado/', 'nome2' => 'Silvia Tirado'],
        ['url1' => 'https://www.chiecosa.it/alex-belli/', 'nome1' => 'Alex Belli',
         'url2' => 'https://www.chiecosa.it/delia-duran/', 'nome2' => 'Delia Duran'],
    ];
    
    foreach ($coppie as $coppia) {
        $result .= "<li>";
        $result .= LinkGenerator::generateLinkIfNotSelf($coppia['url1'], $coppia['nome1']);
        $result .= " e ";
        $result .= LinkGenerator::generateLinkIfNotSelf($coppia['url2'], $coppia['nome2']);
        $result .= "</li>";
    }
    
    $result .= "</ul>";
    
    // Single
    $result .= "<h3>Tutti i single di Temptation Island Vip</h3>";
    $result .= "<p>Di seguito tutti i single concorrenti di Temptation Island Vip</p>";
    
    $result .= "<h4>Ragazzi single</h4>";
    $ragazzi = [
        ['target_url' => 'https://www.chiecosa.it/antonio-moriconi/', 'nome' => 'Antonio Moriconi'],
        ['target_url' => 'https://www.chiecosa.it/fabrizio-baldassarre/', 'nome' => 'Fabrizio Baldassarre'],
        ['target_url' => 'https://www.chiecosa.it/mattia-bertucco/', 'nome' => 'Mattia Bertucco'],
        ['target_url' => 'https://www.chiecosa.it/devid-nenci/', 'nome' => 'David Nenci'],
        ['target_url' => 'https://www.chiecosa.it/alessandro-catania/', 'nome' => 'Alessandro Catania'],
        ['target_url' => 'https://www.chiecosa.it/valerio-maggiolini/', 'nome' => 'Valerio Maggiolini'],
        ['target_url' => 'https://www.chiecosa.it/gianmaria-gerolin/', 'nome' => 'Gianmaria Gerolin'],
        ['target_url' => 'https://www.chiecosa.it/michele-loprieno/', 'nome' => 'Michele Loprieno'],
        ['target_url' => 'https://www.chiecosa.it/alessandro-graziani/', 'nome' => 'Alessandro Graziani'],
        ['target_url' => 'https://www.chiecosa.it/jack-queralt/', 'nome' => 'Jack Querlat'],
        ['target_url' => 'https://www.chiecosa.it/riccardo-costantino/', 'nome' => 'Riccardo Costantino'],
    ];
    
    $result .= LinkCollectionBuilder::create()
        ->addLinks($ragazzi)
        ->withImage(true)
        ->removeIfSelf(true)
        ->ulClass('my_shortcode_list')
        ->build();
    
    $result .= "<h4>Ragazze single</h4>";
    $ragazze = [
        ['target_url' => 'https://www.chiecosa.it/federica-francia/', 'nome' => 'Federica Francia'],
        ['target_url' => 'https://www.chiecosa.it/federica-spano/', 'nome' => 'Federica Spano'],
        ['target_url' => 'https://www.chiecosa.it/cecilia-zagarrigo/', 'nome' => 'Cecilia Zagarrigo'],
        ['target_url' => 'https://www.chiecosa.it/marina-vetrova/', 'nome' => 'Marina Vetrova'],
        ['target_url' => 'https://www.chiecosa.it/gaia-mastrototaro/', 'nome' => 'Gaia Mastrotaro'],
        ['target_url' => 'https://www.chiecosa.it/zoe-malucci/', 'nome' => 'Zoe Mallucci'],
        ['target_url' => 'https://www.chiecosa.it/darya-lapushka/', 'nome' => 'Dasha Lapushka'],
        ['target_url' => 'https://www.chiecosa.it/valentina-anna-galli/', 'nome' => 'Valentina Galli'],
        ['target_url' => 'https://www.chiecosa.it/antonietta-fragasso/', 'nome' => 'Antonietta Fragrasso'],
        ['target_url' => 'https://www.chiecosa.it/alice-bertelli/', 'nome' => 'Alice Bertelli'],
    ];
    
    $result .= LinkCollectionBuilder::create()
        ->addLinks($ragazze)
        ->withImage(true)
        ->removeIfSelf(true)
        ->ulClass('my_shortcode_list')
        ->build();
    
    return $result;
}

function tale_e_quale_show_2019_handler($atts, $content = null)
{
    $result = "<h2>I concorrenti di Tale e Quale Show</h2>";
    $result .= "<div class='my_shortcode_list'>
		<ul class='my_shortcode_list'>";
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/francesco-pannofino/", "Francesco Pannofino");
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/davide-de-marinis/", "Davide De Marinis");
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/tiziana-rivale/", "Tiziana Rivale");
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/jessica-morlacchi/", "Jessica Morlacchi");
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/lidia-schillaci/", "Lidia Schillaci");
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/sara-facciolini/", "Sara Facciolini");
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/eva-grimaldi/", "Eva Grimaldi");
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/francesco-monte/", "Francesco Monte");
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/flora-canto/", "Flora Canto");
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/agostino-penna/", "Agostino Penna");
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/david-pratelli/", "Davide Pratelli");
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/luigi-esposito/", "Luigi Esposito (Gigi e Ross)");
    $result .= ListOfPostsHelper::GetLinkWithImage("https://www.chiecosa.it/rosario-morra/", "Rosario Morra (Gigi e Ross)");
    $result .= "</ul></div>";


    return $result;
}

// Funzioni deprecate: usare LinkGenerator invece
// linkIfNotSelf2() -> LinkGenerator::generateLinkIfNotSelf()
// linkIfNotSelf() -> LinkGenerator::generateLinkWithThumbnail() o LinkCollectionBuilder


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

