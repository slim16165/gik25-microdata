<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


add_shortcode('microdata_prezzo', 'microdata_prezzo');
add_shortcode('microdata_telefono', 'microdata_telefono');
add_action('init', 'revious_microdata_prezzo_buttons');

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

function microdata_telefono($atts, $content = null)
{
    $attrValue = shortcode_atts(array(
        'organizationname' => null // (Optional)
    ), $atts);

    if(isset($atts['organizationname'])) 
        $organizationName = $atts['organizationname'];
    else
        $organizationName = '';

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


function revious_microdata_prezzo_buttons()
{
    add_filter("mce_external_plugins", "revious_microdata_add_tinymce_plugins");
    add_filter('mce_buttons', 'revious_microdata_register_prezzo_buttons');
}

function revious_microdata_add_tinymce_plugins($plugin_array)
{
    // $plugins_url = plugins_url('gik25-microdata/assets/js/revious-microdata.js', 'revious-microdata');
    $plugins_url = plugins_url('gik25-microdata/assets/js/revious-microdata.js');
    $plugin_array['revious_microdata'] = $plugins_url;
    return $plugin_array;
}

function revious_microdata_register_prezzo_buttons($buttons)
{
    array_push($buttons, 'md_telefono_btn');
    array_push($buttons, 'md_prezzo_btn');
    //array_push($buttons, 'DomandeERisposte_btn');
    return $buttons;
}