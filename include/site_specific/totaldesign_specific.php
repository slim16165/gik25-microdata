<?php ////////////////////

use gik25microdata\ColorWidget;
use gik25microdata\TagHelper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

//Optimizations

ConditionalLoadJsCss_Colori();

function ConditionalLoadJsCss_Colori(): void
{
    add_action('wp_head', '_conditionalLoadJsCss_Colori');
}

function _conditionalLoadJsCss_Colori(): void
{
    global $post;
    $postConTagColori = TagHelper::find_post_id_from_taxonomy("colori", 'post_tag');//args: term_name "colori", taxonomy_type 'post_tag'
    if (in_array($post->ID, $postConTagColori))
        ColorWidget::carousel_js();
}

add_shortcode('link_colori', 'link_colori_handler');
add_shortcode('grafica3d', 'grafica3d_handler');
add_shortcode('archistar', 'archistars_handler');


function link_colori_handler($atts, $content = null): string
{
    $css = ColorWidget::get_carousel_css();

    //TODO: l'output deve essere ColorWidget::GetLinkWithImageCarousel(

    $result = "<style>$css</style>
	<div class='contain'>
        <h3>Articoli sui colori</h3>
		    <p>	Colori Specifici </p>
		        <div class='row'>
					<div class='row__inner'>";
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.totaldesign.it/color-tortora-colore-neutro-tendenza/", "Color Tortora", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-rosso/", "Colore Rosso", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-bordeaux/", "Colore Rosso Bordeaux", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/rosso-tiziano/", "Colore Rosso Bordeaux", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-verde/", "Colore Verde", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-verde-acqua/", "Colore Verde Acqua", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-verde-salvia/", "Colore Verde Salvia", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/color-petrolio-verde/", "Color Petrolio", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/verde-tiffany/", "Colore Verde Tiffany", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/verde-smeraldo/", "Colore Verde Smeraldo", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-turchese/", "Color Turchese", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/grigio-chiaro/", "Colore Grigio Chiaro", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-bianco/", "Colore Bianco", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/arredare-in-bianco-e-nero/", "Colore Tortora", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-rosa-cipria/", "Colore Rosa Cipria", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-rosa-antico/", "Colore Rosa Antico", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-giallo/", "Colore Giallo", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/giallo-ocra/", "Colore Giallo Ocra", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-oro/", "Colore Oro", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-ciano/", "Colore Ciano", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-azzurro/", "Colore Azzurro", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-corallo/", "Colore Corallo", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/color-tortora-e-larredamento-per-interni/", "Color Tortora (arredamento)", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-carta-da-zucchero/", "Colore Carta da Zucchero", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-beige/", "Colore Beige", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-lilla/", "Colore Lilla", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-indaco/", "Colore Indaco", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-ecru-cose-pareti-divano-mobili-e-abbinamenti/", "Colore Ecrù", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-avorio/", "Colore Avorio", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-antracite/", "Colore Antracite", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-arancione/", "Colore Arancione", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/pareti-grigie-perlato/", "Pareti grigio perlate", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-grigio-perla/", "Colore grigio perla", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-nero/", "Colore Nero", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-porpora/", "Color Porpora", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/color-pesca/", "Color Pesca", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-ambra/", "Colore Ambra", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-avio/", "Colore Avio", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-glicine/", "Colore Glicine", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-malva/", "Colore Malva", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-porpora/", "Colore Porpora", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-celeste/", "Colore Celeste", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-sabbia/", "Colore Sabbia", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-bronzo/", "Colore Bronzo", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-zaffiro/", "Colore Zaffiro", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-viola/", "Colore Viola", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/color-lavanda/", "Colore Lavanda", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-blu/", "Colore Blu", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-blu-navy/", "Colore Blu Navy", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-fucsia/", "Colore Fucsia", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-ecru/", "Colore Ecru", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-magenta/", "Colore Magenta", ""));
    $result .= " </div></div>
        <p>
            Colori Pantone
        </p>
        <div class='row'>
            <div class='row__inner'>";

    $collection->add(new LinkBase("https://www.totaldesign.it/il-very-peri-e-il-colore-dellanno-2022-secondo-pantone/", "Classic Very Peri 2022", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colore-pantone-2021/", "Classic Giallo Pantone 2021", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/classic-blue-pantone/", "Classic Blue Pantone 2020", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colori-pantone/", "Colori Pantone", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colori-pantone-2016/", "Colori Pantone 2016", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/ultra-violet-inspiration-scopri-come-arredare-la-casa-con-il-colore-pantone-2018/", "Colore Ultra Violet", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/total-white-arredare-in-bianco/", "Total White", ""));
//	https://www.totaldesign.it/arredare-in-bianco-e-nero/

    $result .= " </div></div><br /><br />
        <p>
            Articoli Vari
        </p>
        <div class='row'>
            <div class='row__inner'>";
    $collection->add(new LinkBase("https://www.totaldesign.it/colori-complementari/", "Colori Complementari", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colori-caldi-freddi-e-neutri/", "Colori Neutri e Freddi", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colori-freddi/", "Colori freddi", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colori-neutri/", "Colori Neutri", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/abbinamento-colori/", "Abbinamento colori", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/catalogo-colori-pareti/", "Colori per arredare", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colori-pareti-soggiorno/", "Colori pareti soggiorno", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colori-pastello-per-arredare-la-casa/", "Colori Pastello", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/pareti-colorate/", "Pareti colorate", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/colori-arcobaleno/", "Colori arcobaleno", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/tonalita-di-giallo/", "Tonalità di Giallo", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/tonalita-di-verde/", "Tonalità di Verde", ""));


    $result .= "</div></div></div>";
    return $result;
}

function grafica3d_handler($atts, $content = null)
{
    $css = ColorWidget::get_carousel_css();

    $result = "<style>$css</style>
	<div class='contain'>
        <h3>Programmi di Grafica 3D</h3>		   
		        <div class='row'>
					<div class='row__inner'>";
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.totaldesign.it/freecad/", "Freecad 3D", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/homestyler-2/", "Homestyler", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/autodesk-revit/", "Autodesk Revit", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/archicad/", "Archicad", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/maya-3d/", "Maya 3D", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/blender-3d/", "Maya 3D", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/librecad/", "Librecad", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/draftsight/", "Draftsight", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/lumion/", "Lumion Grafica 3D", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/rhinoceros-mac/", "Rhinoceros", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/sketchup-2/", "Schetchup", ""));

    $collection->add(new LinkBase("https://www.totaldesign.it/migliori-programmi-gratuiti-per-la-progettazione-3d/", "Migliori Programmi Gratuiti per la progettazione 3D", ""));

    $result .= "</div></div></div>";
    return $result;
}

function archistars_handler($atts, $content = null)
{

    $css = ColorWidget::get_carousel_css();

    $result = "<style>$css</style>
	<div class='contain'>
        <h3>Architetti</h3>		   
		        <div class='row'>
					<div class='row__inner'>";
    $collection = new Collection();
    $collection->add(new LinkBase("https://www.totaldesign.it/renzo-piano/", "Renzo Piano", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/zaha-hadid/", "Zaha Hadid", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/stefano-boeri/", "Stefano Boeri", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/fucksas/", "Fucksas", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/franck-o-gehry/", "Franck O. Gehry", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/norman-foster/", "Norman Foster", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/oma-rem-koolhaas/", "OMA Rem Koolhaas", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/mario-botta/", "Mario Botta", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/jean-nouvel/", "Jean Nouvel", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/santiago-calatrava/", "Santiago Calatrava", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/mario-cucinella/", "Mario Cucinella", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/mvrdv/", "MVRDV", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/herzog-de-meuron/", "Herzog de Meuron", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/david-chipperfield/", "David Chipperfield", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/kengo-kuma/", "Kengo Kuma", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/matteo-thun/", "Matteo Thun", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/sanaa/", "SANAA", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/daniel-libeskind/", "Daniel Libeskind", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/steven-holl/", "Steven Holl", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/richard-meier/", "Richard Meier", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/som/", "SOM", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/snohetta/", "Snøhetta", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/toyo-ito/", "Toyo Ito", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/archea/", "Archea Associati", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/diller-scofidio-renfro/", "Diller Scofidio + Renfro", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/gensler/", "Gensler", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/peter-zumthor/", "Peter Zumthor", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/unstudio/", "UNStudio", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/coop-himmelblau/", "Coop-Himmelblau", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/grafton-architects/", "Grafton Architects", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/bjarke-ingels-group/", "Bjarke Ingels Group", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/heatherwick-studio/", "Heatherwick Studio", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/nemesi-partners/", "Nemesi & Partners", ""));
    $collection->add(new LinkBase("https://www.totaldesign.it/asymptote-architecture/", "Asymptote Architecture", ""));

    $result .= "</div></div></div>";
    return $result;
}