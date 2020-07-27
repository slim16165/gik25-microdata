<?php
if(!defined('ABSPATH')) {
    exit;
}
class MicrodataProgressbar {

    public function __construct() {
        add_shortcode(PLUGIN_NAME_PREFIX . 'progressbar', array($this, 'shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'mdpb_scripts_styles'));
    }

    public function shortcode($atts, $content = null) {

        $mdpb = shortcode_atts(array(
                'progressbar_speed' => '0.6'
            ), $atts);

        if(isset($mdpb['progressbar_speed']) && !empty($mdpb['progressbar_speed'])) {
            $progressbar_speed = $mdpb['progressbar_speed'];
        }
        else {
            $progressbar_speed =  '0.6';
        }

        $progress_bar_html = <<<ABC
        <div class="md-progress-bar-container">
            <div class="md-progress-bar" id="md-progress-bar" style="transition: width {$progressbar_speed}s ease-out 0s;"></div>
        </div>
ABC;

//         $progress_bar_html = <<<ABC
//             <div class="md-progress-bar-container" id="md-progress-bar-container">
//                 <div class="md-progress-bar" id="md-progress-bar" style="transition: width {$progressbar_speed}s ease-out 0s;"></div>
//             </div>
// ABC;
    
        return $progress_bar_html;

    }

    function mdpb_scripts_styles() {
        wp_register_style('mdpb-styles', plugins_url('/gik25-microdata/assets/css/mdpb.css'), array(), '', 'all');
        wp_enqueue_style('mdpb-styles');
        // wp_register_style('revious-microdata', plugins_url('/gik25-microdata/assets/css/revious-micrrodata.css'), array(), '', 'all');
        // wp_enqueue_style('revious-microdata');
        wp_register_script('mdpb-script', plugins_url('/gik25-microdata/assets/js/progressbar.js'), array('jquery'));
        wp_enqueue_script('mdpb-script');
    }

}

//$microdata_progressbar = new MicrodataProgressbar();