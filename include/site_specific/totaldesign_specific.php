<?php ////////////////////

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

OptimizationHelper::ConditionalLoadJsCss_Colori();

add_shortcode('link_colori', 'link_colori_handler');
add_shortcode('grafica3d', 'grafica3d_handler');
add_shortcode('archistar', 'archistars_handler');


function link_colori_handler($atts, $content = null)
{
    $css = ColorWidget::get_carousel_css();

    $result = "<style>$css</style>
	<div class='contain'>
        <h3>Articoli sui colori</h3>
		    <p>	Colori Specifici </p>
		        <div class='row'>
					<div class='row__inner'>";
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/color-tortora-colore-neutro-tendenza/", "Color Tortora");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-rosso/", "Colore Rosso");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-verde-acqua/", "Colore Verde Acqua");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-verde-salvia/", "Colore Verde Salvia");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-turchese//", "Color Turchese");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/grigio-chiaro/", "Colore Grigio Chiaro");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-bianco/", "Colore Bianco");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/arredare-in-bianco-e-nero/", "Colore Tortora");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-rosa-antico/", "Colore Rosa Antico");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-verde/", "Colore Verde");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-giallo/", "Colore Giallo");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-oro/", "Colore Oro");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-ciano/", "Colore Ciano");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-corallo/", "Colore Corallo");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/color-tortora-e-larredamento-per-interni/", "Color Tortora (arredamento)");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-carta-da-zucchero/", "Colore Carta da Zucchero");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-beige/", "Colore Beige");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-lilla/", "Colore Lilla");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-indaco/", "Colore Indaco");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-ecru-cose-pareti-divano-mobili-e-abbinamenti/", "Colore Ecrù");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-avorio/", "Colore Avorio");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-antracite/", "Colore Antracite");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-arancione/", "Colore Arancione");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/pareti-grigie-perlato/", "Pareti grigio perlate");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colori-pareti-soggiorno/", "Colori pareti soggiorno");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-porpora/", "Color Porpora");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-ambra/", "Colore Ambra");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-avio/", "Colore Avio");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-glicine/", "Colore Glicine");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-malva/", "Colore Malva");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-porpora/", "Colore Porpora");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-celeste/", "Colore Celeste");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-sabbia/", "Colore Sabbia");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-bronzo/", "Colore Bronzo");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-zaffiro/", "Colore Zaffiro");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-viola/", "Colore Viola");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-blu/", "Colore Blu");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-fucsia/", "Colore Fucsia");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-ecru/", "Colore Ecru");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/verde-tiffany/", "Colore Verde Tiffany");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colore-magenta/", "Colore Magenta");
    $result .= " </div></div>
        <p>
            Colori Pantone
        </p>
        <div class='row'>
            <div class='row__inner'>";

    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/classic-blue-pantone/", "Classic Blue Pantone 2020");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colori-pantone/", "Colori Pantone");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colori-pantone-2016/", "Colori Pantone 2016");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/ultra-violet-inspiration-scopri-come-arredare-la-casa-con-il-colore-pantone-2018/", "Colore Ultra Violet");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/total-white-arredare-in-bianco/", "Total White");
//	https://www.totaldesign.it/arredare-in-bianco-e-nero/

    $result .= " </div></div><br /><br />
        <p>
            Articoli Vari
        </p>
        <div class='row'>
            <div class='row__inner'>";
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/catalogo-colori-pareti/", "Colori per arredare");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colori-pastello-per-arredare-la-casa/", "Colori Pastello");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colori-neutri/", "Colori Neutri");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/colori-caldi-freddi-e-neutri/", "Colori Neutri e Freddi");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/abbinamento-colori/", "Abbinamento colori");


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

    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/freecad/", "Freecad 3D");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/homestyler-2/", "Homestyler");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/autodesk-revit/", "Autodesk Revit");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/archicad/", "Archicad");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/maya-3d/", "Maya 3D");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/blender-3d/", "Maya 3D");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/librecad/", "Librecad");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/draftsight/", "Draftsight");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/lumion/", "Lumion Grafica 3D");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/rhinoceros-mac/", "Rhinoceros");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/sketchup-2/", "Schetchup");

    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/migliori-programmi-gratuiti-per-la-progettazione-3d/", "Migliori Programmi Gratuiti per la progettazione 3D");

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

    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/renzo-piano/", "Renzo Piano");       
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/zaha-hadid/", "Zaha Hadid");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/stefano-boeri/", "Stefano Boeri");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/fucksas/", "Fucksas");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/franck-o-gehry/", "Franck O. Gehry");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/norman-foster/", "Norman Foster");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/oma-rem-koolhaas/", "OMA Rem Koolhaas");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/mario-botta/", "Mario Botta");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/jean-nouvel/", "Jean Nouvel");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/santiago-calatrava/", "Santiago Calatrava");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/mario-cucinella/", "Mario Cucinella");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/mvrdv/", "MVRDV");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/herzog-de-meuron/", "Herzog de Meuron");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/david-chipperfield/", "David Chipperfield");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/kengo-kuma/", "Kengo Kuma");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/matteo-thun/", "Matteo Thun");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/sanaa/", "SANAA");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/daniel-libeskind/", "Daniel Libeskind");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/steven-holl/", "Steven Holl");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/richard-meier/", "Richard Meier");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/som/", "SOM");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/snohetta/", "Snøhetta");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/toyo-ito/", "Toyo Ito");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/archea/", "Archea Associati");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/diller-scofidio-renfro/", "Diller Scofidio + Renfro");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/gensler/", "Gensler");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/unstudio/", "UNStudio");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/coop-himmelblau/", "Coop-Himmelblau");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/grafton-architects/", "Grafton Architects");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/bjarke-ingels-group/", "Bjarke Ingels Group");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/heatherwick-studio/", "Heatherwick Studio");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/nemesi-partners/", "Nemesi & Partners");
    $result .= ColorWidget::GetLinkWithImageCarousel("https://www.totaldesign.it/asymptote-architecture/", "Asymptote Architecture");

    $result .= "</div></div></div>";
    return $result;
}