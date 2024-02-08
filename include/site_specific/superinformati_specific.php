<?php
declare(strict_types=1);


use gik25microdata\ListOfPosts\ListOfPostsMain;
use gik25microdata\ListOfPosts\Types\LinkBase;
use gik25microdata\ListOfPosts\WPPostsHelper;
use Illuminate\Support\Collection;

require_once "superinformati_links.php";

if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
}

add_shortcode('link_analisi_sangue', 'link_analisi_sangue_handler_2');
add_shortcode('sedi_inps', 'sedi_inps_handler');
add_shortcode('link_vitamine', 'link_vitamine_handler');
add_shortcode('link_diete', 'link_diete_handler');
add_shortcode('link_dimagrimento', 'link_dimagrimento_handler');
add_shortcode('link_tatuaggi', 'link_tatuaggi_handler');

add_action('wp_head', 'add_HeaderScript');
add_action('wp_footer', 'add_FooterScript');

add_filter('the_author', 'its_my_company');

add_filter('elementor/frontend/print_google_fonts', '__return_false');

/**
 * @return void
 */
function add_HeaderScript(): void
{
    if (defined('DOING_AJAX'))
    {
        return;
    }

    //Disabilito adsense su una pagina
    if (!defined('ADVADS_ADS_DISABLED'))
    {
        //Adsense();

        global $post;
        if ($post->ID == 7557)
            define('ADVADS_ADS_DISABLED', true);
    }
}

function add_FooterScript(): void
{
    if (defined('DOING_AJAX'))
    {
        return;
    }
}

function link_analisi_sangue_handler_2($atts, $content = null): string
{
    $shortcode = "link_analisi_sangue";
    $cat = "analisi_sangue";


    $links[] = GetListOfMed1();
    $links[] = GetListOfMed2();
    $links[] = GetListOfMed3();
    $links[] = GetListOfMed4();
    $links[] = GetListOfMed5();
    $descr[] = "Generale";
    $descr[] = "Globuli bianchi";
    $descr[] = "Globuli Rossi";
    $descr[] = "Piastrine";
    $descr[] = "Altro";

    $result = "";
    for ($i = 0; $i < count($links); $i++)
    {
        /** @var Collection $link */
        $link = $links[$i];
        $listOfPosts = new ListOfPostsMain($link, "{$cat}_{$descr[$i]}");
        $listOfPosts->SaveLinks();

        //    $blocks = $listOfPosts->ConvertToListOfBlocks($cat, $description);
        //    $blocks->SaveListOfBlocks();

        $listOfPosts->InitRenderHelper(false, true, false);
        $result .= $listOfPosts->RenderLinksAsHtml2($descr[$i], "nicelist");
    }

    return $result;
}

function link_dimagrimento_handler($atts, $content = null): string
{
    $links = ConvertArrayToList(GetListOfDimagrimentoLinks());
    $cat = "link_dimagrimento";
    $description = "Lista dei principali metodi per dimagrire e tonificare";

    return standardBehavior($links, $cat, $description, "thumbnail-list");
}

function link_vitamine_handler($atts, $content = null): string
{
    $links = ConvertArrayToList(GetListOfVitamins());
    $cat = 'link_vitamine';
    $description = "Lista delle principali vitamine";

    return standardBehavior($links, $cat, $description, "thumbnail-list");
}

function link_tatuaggi_handler($atts, $content = null): string
{
    $links = ConvertArrayToList(getListOfTatooLinks());
    $cat = 'link_tatuaggi';
    $description = "Articoli sui tatuaggi";

    return standardBehavior($links, $cat, $description, "thumbnail-list");
}


function link_diete_handler($atts, $content = null): string
{
    $links = ConvertArrayToList(getListOfDietLinks());
    $cat = 'link_diete';
    $description = 'Lista delle principali diete';

    return standardBehavior($links, $cat, $description, "thumbnail-list");
}


function sedi_inps_handler($atts, $content = null): string
{
    $links = getSediInpsLinkCollection();
    $cat = 'sedi_inps';
    $description = "Sedi INPS in tutta Italia";

    return standardBehavior($links, $cat, $description, "nicelist");
}

function standardBehavior(Collection $links, string $cat, string $description, $ulClass): string
{
    do_action( 'qm/start', 'link_handler' );
    $result = print_css_in_header();

    $listOfPosts = new ListOfPostsMain($links, $cat);
//    $listOfPosts->SaveLinks();

//    $listOfBlocks = $listOfPosts->ConvertToListOfBlocks($cat, $description);
//    $listOfBlocks->SaveListOfBlocks();

    $listOfPosts->InitRenderHelper(false, true, false);
    $result.= $listOfPosts->RenderLinksAsHtml($description, $ulClass);

    do_action( 'qm/stop', 'link_handler' );

    return $result;
}

function print_css_in_header(): string
{
    $path = __DIR__ . '../../../assets/css/revious-microdata.css';
    $cssFilePath = realpath($path);

    if ($cssFilePath === false)
    {
        do_action('qm/error', "Errore nella lettura del file CSS:". $path ."\n");
        return "";
    } else
    {
        $cssContent = file_get_contents($cssFilePath);
        return "<style>" . PHP_EOL . $cssContent . PHP_EOL . "</style>";
    }
}

function ConvertArrayToList($linksData): Collection
{
    $linksCollection = new Collection();

    foreach ($linksData as $linkData)
    {
        $link = new LinkBase($linkData['target_url'], $linkData['nome']);
        $linksCollection->add($link);
    }

    return $linksCollection;
}


//require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

function custom_plugin_table_install()
{
    global $wpdb;

    // Nome della tabella con prefisso del database
    $table_links = $wpdb->prefix . 'custom_links';
    $table_lists = $wpdb->prefix . 'custom_link_lists';

    // Charset e collate del database
    $charset_collate = $wpdb->get_charset_collate();

    // SQL per creare la tabella wp_custom_links
    $sql_links = "CREATE TABLE $table_links (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        link_category varchar(255) NOT NULL,
        link_url varchar(255) NOT NULL,
        link_name varchar(255) NOT NULL,
        link_description varchar(255) DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // SQL per creare la tabella wp_custom_link_lists
    $sql_lists = "CREATE TABLE $table_lists (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        shortcode_name varchar(255) NOT NULL,
        description varchar(255) DEFAULT NULL,
        links_json longtext NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Esecuzione delle query con dbDelta
//    dbDelta($sql_links);
    dbDelta($sql_lists);
}

//// Chiamata alla funzione per installare le tabelle
//custom_plugin_table_install();
//
//register_activation_hook(__FILE__, 'custom_plugin_table_install');

