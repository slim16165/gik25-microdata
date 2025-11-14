<?php
namespace gik25microdata\site_specific;

use gik25microdata\Widgets\ColorWidget;
use gik25microdata\Utility\TagHelper;
use gik25microdata\site_specific\Totaldesign\ProgrammaticHub;
use gik25microdata\Widgets\ContextualWidgets;
use gik25microdata\REST\MCPApi;
use gik25microdata\ListOfPosts\LinkBuilder;
use gik25microdata\Hubs\DynamicColorHub;
use gik25microdata\Hubs\DynamicArchitectsHub;
use gik25microdata\Hubs\Dynamic3DGraphicsHub;
use gik25microdata\Hubs\AdvancedCrossLinker;
use gik25microdata\Widgets\ColorHarmonyVisualizer;
use gik25microdata\Widgets\PaletteGeneratorParticles;
use gik25microdata\Widgets\ProductComparisonCinematic;
use gik25microdata\Widgets\RoomSimulatorIsometric;
use gik25microdata\Widgets\IKEAHackExplorer3D;
use gik25microdata\Widgets\LightingSimulator;
use gik25microdata\Widgets\ColorPicker3D;
use gik25microdata\Widgets\ArchitecturalVisualization3D;
use gik25microdata\Widgets\FluidColorMixer;
use gik25microdata\Widgets\InteractiveDesignGame;
use gik25microdata\Widgets\ColorRoomRecommender;
use gik25microdata\Widgets\PantoneHubDynamic;
use gik25microdata\Widgets\IsometricIKEAConfigurator;
use gik25microdata\Widgets\ColorExplosionEffect;
use gik25microdata\Widgets\AdvancedColorPicker;
use gik25microdata\Widgets\WidgetRegistry;
use gik25microdata\Widgets\WidgetPerformanceMonitor;
use gik25microdata\Widgets\MobileAppShell;
use gik25microdata\Widgets\Psycho\MoodColorTracker;
use gik25microdata\Widgets\Psycho\ColorTherapyVisualizer;
use gik25microdata\Widgets\Psycho\PersonalityColorTest;
use gik25microdata\Widgets\Psycho\StressReliefColors;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

//Optimizations

function ConditionalLoadJsCss_Colori()
{
    add_action('wp_head', __NAMESPACE__ . '\\_conditionalLoadJsCss_Colori');
}

function _conditionalLoadJsCss_Colori()
{
    global $post;
    
    // Verifica che $post sia valido (può essere null in archive pages, search, ecc.)
    if (!is_a($post, 'WP_Post') || !isset($post->ID)) {
        return;
    }
    
    $postConTagColori = TagHelper::find_post_id_from_taxonomy("colori", 'post_tag');//args: term_name "colori", taxonomy_type 'post_tag'
    if (in_array($post->ID, $postConTagColori))
        ColorWidget::carousel_js();
}

ConditionalLoadJsCss_Colori();
ProgrammaticHub::init();

// Inizializza Hub Dinamici (nuove integrazioni strategiche)
if (class_exists('\\gik25microdata\\Hubs\\DynamicColorHub')) {
    DynamicColorHub::init();
}
if (class_exists('\\gik25microdata\\Hubs\\DynamicArchitectsHub')) {
    DynamicArchitectsHub::init();
}
if (class_exists('\\gik25microdata\\Hubs\\Dynamic3DGraphicsHub')) {
    Dynamic3DGraphicsHub::init();
}
if (class_exists('\\gik25microdata\\Hubs\\AdvancedCrossLinker')) {
    AdvancedCrossLinker::init();
}

// Inizializza Widget Avanzatissimi Livello Videogame
if (class_exists('\\gik25microdata\\Widgets\\ColorHarmonyVisualizer')) {
    ColorHarmonyVisualizer::init();
}
if (class_exists('\\gik25microdata\\Widgets\\PaletteGeneratorParticles')) {
    PaletteGeneratorParticles::init();
}
if (class_exists('\\gik25microdata\\Widgets\\ProductComparisonCinematic')) {
    ProductComparisonCinematic::init();
}
if (class_exists('\\gik25microdata\\Widgets\\RoomSimulatorIsometric')) {
    RoomSimulatorIsometric::init();
}
if (class_exists('\\gik25microdata\\Widgets\\IKEAHackExplorer3D')) {
    IKEAHackExplorer3D::init();
}
if (class_exists('\\gik25microdata\\Widgets\\LightingSimulator')) {
    LightingSimulator::init();
}
if (class_exists('\\gik25microdata\\Widgets\\ColorPicker3D')) {
    ColorPicker3D::init();
}
if (class_exists('\\gik25microdata\\Widgets\\ArchitecturalVisualization3D')) {
    ArchitecturalVisualization3D::init();
}
if (class_exists('\\gik25microdata\\Widgets\\FluidColorMixer')) {
    FluidColorMixer::init();
}
if (class_exists('\\gik25microdata\\Widgets\\InteractiveDesignGame')) {
    InteractiveDesignGame::init();
}
if (class_exists('\\gik25microdata\\Widgets\\ColorRoomRecommender')) {
    ColorRoomRecommender::init();
}
if (class_exists('\\gik25microdata\\Widgets\\PantoneHubDynamic')) {
    PantoneHubDynamic::init();
}
if (class_exists('\\gik25microdata\\Widgets\\IsometricIKEAConfigurator')) {
    IsometricIKEAConfigurator::init();
}
if (class_exists('\\gik25microdata\\Widgets\\ColorExplosionEffect')) {
    ColorExplosionEffect::init();
}
if (class_exists('\\gik25microdata\\Widgets\\AdvancedColorPicker')) {
    AdvancedColorPicker::init();
}
if (class_exists('\\gik25microdata\\Widgets\\WidgetPerformanceMonitor')) {
    WidgetPerformanceMonitor::init();
}
if (class_exists('\\gik25microdata\\Widgets\\MobileAppShell')) {
    MobileAppShell::init();
}
if (class_exists('\\gik25microdata\\Widgets\\Psycho\\MoodColorTracker')) {
    MoodColorTracker::init();
}
if (class_exists('\\gik25microdata\\Widgets\\Psycho\\ColorTherapyVisualizer')) {
    ColorTherapyVisualizer::init();
}
if (class_exists('\\gik25microdata\\Widgets\\Psycho\\PersonalityColorTest')) {
    PersonalityColorTest::init();
}
if (class_exists('\\gik25microdata\\Widgets\\Psycho\\StressReliefColors')) {
    StressReliefColors::init();
}

// REST API per MCP Server
// L'autoloader Composer dovrebbe caricare la classe automaticamente
// Se non funziona, verifica che composer.json includa il namespace gik25microdata\REST
if (class_exists('\\gik25microdata\\REST\\MCPApi')) {
    MCPApi::init();
    
    // Abilita route estese per TotalDesign (color, ikea, room, pantone)
    add_filter('wp_mcp_enable_extended_routes', '__return_true');
}

// Widget contestuali nelle pagine/articoli
if (class_exists('\\gik25microdata\\Widgets\\ContextualWidgets')) {
    ContextualWidgets::init();
}

// Attiva Kitchen Finder shortcode - il file KitchenFinder.php si auto-istanzia alla fine
// La classe viene caricata dall'autoloader quando necessario, e l'istanziazione alla fine del file
// ($kitchen_finder = new KitchenFinder()) registra automaticamente lo shortcode

add_shortcode('link_colori', __NAMESPACE__ . '\\link_colori_handler');
add_shortcode('grafica3d', __NAMESPACE__ . '\\grafica3d_handler');
add_shortcode('archistar', __NAMESPACE__ . '\\archistars_handler');


function link_colori_handler($atts, $content = null)
{
    $css = ColorWidget::get_carousel_css();
    $builder = LinkBuilder::create('carousel');

    $result = "<style>$css</style>
	<div class='contain'>
        <h3>Articoli sui colori</h3>
		    <p>Colori Specifici</p>
		        <div class='row'>
					<div class='row__inner'>";
    $result .= $builder->buildCarouselLink("https://www.totaldesign.it/color-tortora-colore-neutro-tendenza/", "Color Tortora");
    $colori = [
        "https://www.totaldesign.it/colore-rosso/" => "Colore Rosso",
        "https://www.totaldesign.it/colore-bordeaux/" => "Colore Rosso Bordeaux",
        "https://www.totaldesign.it/rosso-tiziano/" => "Colore Rosso Tiziano",
        "https://www.totaldesign.it/colore-verde/" => "Colore Verde",
        "https://www.totaldesign.it/colore-verde-acqua/" => "Colore Verde Acqua",
        "https://www.totaldesign.it/colore-verde-salvia/" => "Colore Verde Salvia",
        "https://www.totaldesign.it/color-petrolio-verde/" => "Color Petrolio",
        "https://www.totaldesign.it/verde-tiffany/" => "Colore Verde Tiffany",
        "https://www.totaldesign.it/verde-smeraldo/" => "Colore Verde Smeraldo",
        "https://www.totaldesign.it/colore-turchese/" => "Color Turchese",
        "https://www.totaldesign.it/grigio-chiaro/" => "Colore Grigio Chiaro",
        "https://www.totaldesign.it/colore-bianco/" => "Colore Bianco",
        "https://www.totaldesign.it/arredare-in-bianco-e-nero/" => "Colore Bianco e Nero",
        "https://www.totaldesign.it/colore-rosa-cipria/" => "Colore Rosa Cipria",
        "https://www.totaldesign.it/colore-rosa-antico/" => "Colore Rosa Antico",
        "https://www.totaldesign.it/colore-giallo/" => "Colore Giallo",
        "https://www.totaldesign.it/giallo-ocra/" => "Colore Giallo Ocra",
        "https://www.totaldesign.it/colore-oro/" => "Colore Oro",
        "https://www.totaldesign.it/colore-ciano/" => "Colore Ciano",
        "https://www.totaldesign.it/colore-azzurro/" => "Colore Azzurro",
        "https://www.totaldesign.it/colore-corallo/" => "Colore Corallo",
        "https://www.totaldesign.it/color-tortora-e-larredamento-per-interni/" => "Color Tortora (arredamento)",
        "https://www.totaldesign.it/colore-carta-da-zucchero/" => "Colore Carta da Zucchero",
        "https://www.totaldesign.it/colore-beige/" => "Colore Beige",
        "https://www.totaldesign.it/colore-lilla/" => "Colore Lilla",
        "https://www.totaldesign.it/colore-indaco/" => "Colore Indaco",
        "https://www.totaldesign.it/colore-ecru-cose-pareti-divano-mobili-e-abbinamenti/" => "Colore Ecrù",
        "https://www.totaldesign.it/colore-avorio/" => "Colore Avorio",
        "https://www.totaldesign.it/colore-antracite/" => "Colore Antracite",
        "https://www.totaldesign.it/colore-arancione/" => "Colore Arancione",
        "https://www.totaldesign.it/pareti-grigie-perlato/" => "Pareti grigio perlate",
        "https://www.totaldesign.it/colore-grigio-perla/" => "Colore grigio perla",
        "https://www.totaldesign.it/colore-nero/" => "Colore Nero",
        "https://www.totaldesign.it/colore-porpora/" => "Color Porpora",
        "https://www.totaldesign.it/color-pesca/" => "Color Pesca",
        "https://www.totaldesign.it/colore-ambra/" => "Colore Ambra",
        "https://www.totaldesign.it/colore-avio/" => "Colore Avio",
        "https://www.totaldesign.it/colore-glicine/" => "Colore Glicine",
        "https://www.totaldesign.it/colore-malva/" => "Colore Malva",
        "https://www.totaldesign.it/colore-celeste/" => "Colore Celeste",
        "https://www.totaldesign.it/colore-sabbia/" => "Colore Sabbia",
        "https://www.totaldesign.it/colore-bronzo/" => "Colore Bronzo",
        "https://www.totaldesign.it/colore-zaffiro/" => "Colore Zaffiro",
        "https://www.totaldesign.it/colore-viola/" => "Colore Viola",
        "https://www.totaldesign.it/color-lavanda/" => "Colore Lavanda",
        "https://www.totaldesign.it/colore-blu/" => "Colore Blu",
        "https://www.totaldesign.it/colore-blu-navy/" => "Colore Blu Navy",
        "https://www.totaldesign.it/blu-cobalto/" => "Colore Blu Cobalto",
        "https://www.totaldesign.it/colore-fucsia/" => "Colore Fucsia",
        "https://www.totaldesign.it/colore-ecru/" => "Colore Ecru",
        "https://www.totaldesign.it/colore-magenta/" => "Colore Magenta",
    ];
    
    foreach ($colori as $url => $nome) {
        $result .= $builder->buildCarouselLink($url, $nome);
    }
    $result .= " </div></div>
        <p>
            Colori Pantone
        </p>
        <div class='row'>
            <div class='row__inner'>";

    $pantone = [
        "https://www.totaldesign.it/il-very-peri-e-il-colore-dellanno-2022-secondo-pantone/" => "Classic Very Peri 2022",
        "https://www.totaldesign.it/colore-pantone-2021/" => "Classic Giallo Pantone 2021",
        "https://www.totaldesign.it/classic-blue-pantone/" => "Classic Blue Pantone 2020",
        "https://www.totaldesign.it/colori-pantone/" => "Colori Pantone",
        "https://www.totaldesign.it/colori-pantone-2016/" => "Colori Pantone 2016",
        "https://www.totaldesign.it/ultra-violet-inspiration-scopri-come-arredare-la-casa-con-il-colore-pantone-2018/" => "Colore Ultra Violet",
        "https://www.totaldesign.it/total-white-arredare-in-bianco/" => "Total White",
    ];
    
    foreach ($pantone as $url => $nome) {
        $result .= $builder->buildCarouselLink($url, $nome);
    }
//	https://www.totaldesign.it/arredare-in-bianco-e-nero/

    $result .= " </div></div><br /><br />
        <p>
            Articoli Vari
        </p>
        <div class='row'>
            <div class='row__inner'>";
    $articoli = [
        "https://www.totaldesign.it/colori-complementari/" => "Colori Complementari",
        "https://www.totaldesign.it/colori-caldi-freddi-e-neutri/" => "Colori Neutri e Freddi",
        "https://www.totaldesign.it/colori-freddi/" => "Colori freddi",
        "https://www.totaldesign.it/colori-neutri/" => "Colori Neutri",
        "https://www.totaldesign.it/abbinamento-colori/" => "Abbinamento colori",
        "https://www.totaldesign.it/catalogo-colori-pareti/" => "Colori per arredare",
        "https://www.totaldesign.it/colori-pareti-soggiorno/" => "Colori pareti soggiorno",
        "https://www.totaldesign.it/colori-pastello-per-arredare-la-casa/" => "Colori Pastello",
        "https://www.totaldesign.it/pareti-colorate/" => "Pareti colorate",
        "https://www.totaldesign.it/colori-arcobaleno/" => "Colori arcobaleno",
        "https://www.totaldesign.it/tonalita-di-giallo/" => "Tonalità di Giallo",
        "https://www.totaldesign.it/tonalita-di-verde/" => "Tonalità di Verde",
    ];
    
    foreach ($articoli as $url => $nome) {
        $result .= $builder->buildCarouselLink($url, $nome);
    }


    $result .= "</div></div></div>";
    return $result;
}

function grafica3d_handler($atts, $content = null)
{
    $css = ColorWidget::get_carousel_css();
    $builder = LinkBuilder::create('carousel');

    $programmi = [
        "https://www.totaldesign.it/freecad/" => "Freecad 3D",
        "https://www.totaldesign.it/homestyler-2/" => "Homestyler",
        "https://www.totaldesign.it/autodesk-revit/" => "Autodesk Revit",
        "https://www.totaldesign.it/archicad/" => "Archicad",
        "https://www.totaldesign.it/maya-3d/" => "Maya 3D",
        "https://www.totaldesign.it/blender-3d/" => "Blender 3D",
        "https://www.totaldesign.it/librecad/" => "Librecad",
        "https://www.totaldesign.it/draftsight/" => "Draftsight",
        "https://www.totaldesign.it/lumion/" => "Lumion Grafica 3D",
        "https://www.totaldesign.it/rhinoceros-mac/" => "Rhinoceros",
        "https://www.totaldesign.it/sketchup-2/" => "Schetchup",
        "https://www.totaldesign.it/migliori-programmi-gratuiti-per-la-progettazione-3d/" => "Migliori Programmi Gratuiti per la progettazione 3D",
    ];

    $result = "<style>$css</style>
	<div class='contain'>
        <h3>Programmi di Grafica 3D</h3>		   
		        <div class='row'>
					<div class='row__inner'>";

    foreach ($programmi as $url => $nome) {
        $result .= $builder->buildCarouselLink($url, $nome);
    }

    $result .= "</div></div></div>";
    return $result;
}

function archistars_handler($atts, $content = null)
{
    $css = ColorWidget::get_carousel_css();
    $builder = LinkBuilder::create('carousel');

    $architetti = [
        "https://www.totaldesign.it/renzo-piano/" => "Renzo Piano",
        "https://www.totaldesign.it/zaha-hadid/" => "Zaha Hadid",
        "https://www.totaldesign.it/stefano-boeri/" => "Stefano Boeri",
        "https://www.totaldesign.it/fucksas/" => "Fucksas",
        "https://www.totaldesign.it/franck-o-gehry/" => "Franck O. Gehry",
        "https://www.totaldesign.it/norman-foster/" => "Norman Foster",
        "https://www.totaldesign.it/oma-rem-koolhaas/" => "OMA Rem Koolhaas",
        "https://www.totaldesign.it/mario-botta/" => "Mario Botta",
        "https://www.totaldesign.it/jean-nouvel/" => "Jean Nouvel",
        "https://www.totaldesign.it/santiago-calatrava/" => "Santiago Calatrava",
        "https://www.totaldesign.it/mario-cucinella/" => "Mario Cucinella",
        "https://www.totaldesign.it/mvrdv/" => "MVRDV",
        "https://www.totaldesign.it/herzog-de-meuron/" => "Herzog de Meuron",
        "https://www.totaldesign.it/david-chipperfield/" => "David Chipperfield",
        "https://www.totaldesign.it/kengo-kuma/" => "Kengo Kuma",
        "https://www.totaldesign.it/matteo-thun/" => "Matteo Thun",
        "https://www.totaldesign.it/sanaa/" => "SANAA",
        "https://www.totaldesign.it/daniel-libeskind/" => "Daniel Libeskind",
        "https://www.totaldesign.it/steven-holl/" => "Steven Holl",
        "https://www.totaldesign.it/richard-meier/" => "Richard Meier",
        "https://www.totaldesign.it/som/" => "SOM",
        "https://www.totaldesign.it/snohetta/" => "Snøhetta",
        "https://www.totaldesign.it/toyo-ito/" => "Toyo Ito",
        "https://www.totaldesign.it/archea/" => "Archea Associati",
        "https://www.totaldesign.it/diller-scofidio-renfro/" => "Diller Scofidio + Renfro",
        "https://www.totaldesign.it/gensler/" => "Gensler",
        "https://www.totaldesign.it/peter-zumthor/" => "Peter Zumthor",
        "https://www.totaldesign.it/unstudio/" => "UNStudio",
        "https://www.totaldesign.it/coop-himmelblau/" => "Coop-Himmelblau",
        "https://www.totaldesign.it/grafton-architects/" => "Grafton Architects",
        "https://www.totaldesign.it/bjarke-ingels-group/" => "Bjarke Ingels Group",
        "https://www.totaldesign.it/heatherwick-studio/" => "Heatherwick Studio",
        "https://www.totaldesign.it/nemesi-partners/" => "Nemesi & Partners",
        "https://www.totaldesign.it/asymptote-architecture/" => "Asymptote Architecture",
    ];

    $result = "<style>$css</style>
	<div class='contain'>
        <h3>Architetti</h3>		   
		        <div class='row'>
					<div class='row__inner'>";

    foreach ($architetti as $url => $nome) {
        $result .= $builder->buildCarouselLink($url, $nome);
    }

    $result .= "</div></div></div>";
    return $result;
}
