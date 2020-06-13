<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


add_shortcode('microdata_prezzo', 'microdata_prezzo');

function microdata_prezzo($atts, $content = null)
{
    //Fine offer span

    return <<<EOF
<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
    <span itemprop="priceCurrency" content="EUR">€</span>
    <span itemprop="price">
EOF
        .do_shortcode($content)
    ."</span>" //Fine price span
    ."</span>";
}
