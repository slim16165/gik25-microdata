<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_shortcode('microdata_telefono', 'microdata_telefono');

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