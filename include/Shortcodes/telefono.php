<?php
if(!defined('ABSPATH')) {
    exit;
}
class Telefono {

    public function __construct() {
        add_shortcode(PLUGIN_NAME_PREFIX . 'telefono', array($this, 'shortcode'));
        add_shortcode('microdata_telefono', array($this, 'shortcode'));
        add_filter('mce_external_plugins', array($this, 'mdt_register_plugin'));
        add_filter('mce_buttons', array($this, 'mdt_register_button'));
    }

    public function shortcode($atts, $content = null) {
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

    public function mdt_register_plugin($plugin_array) {
        $plugins_url = plugins_url('gik25-microdata/assets/js/TinyMCE/telefono.js');
        $plugin_array['md_telefono'] = $plugins_url;
        return $plugin_array;
    }

    public function mdt_register_button($buttons) {
        array_push($buttons, 'md_telefono-menu');
        return $buttons;
    }

}

$telefono = new Telefono();