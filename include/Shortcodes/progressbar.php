<?php
if(!defined('ABSPATH')) {
    exit;
}
class MicrodataProgressbar {

    public function __construct() {
        add_shortcode(PLUGIN_NAME_PREFIX . 'progressbar', array($this, 'shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'md_progressbar_scripts_styles'));
    }

    public function shortcode($atts, $content = null) {

        $md_progressbar = shortcode_atts(array(
                'progressbar_speed' => '0.6'
            ), $atts);

        if(isset($md_progressbar['progressbar_speed']) && !empty($md_progressbar['progressbar_speed'])) {
            $progressbar_speed = $md_progressbar['progressbar_speed'];
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

    function md_progressbar_scripts_styles() {
        wp_register_style('md_progressbar-styles', plugins_url('/gik25-microdata/assets/css/md_progressbar.css'), array(), '', 'all');
        wp_enqueue_style('md_progressbar-styles');
        // wp_register_style('revious-microdata', plugins_url('/gik25-microdata/assets/css/revious-micrrodata.css'), array(), '', 'all');
        // wp_enqueue_style('revious-microdata');
        wp_register_script('md_progressbar-script', plugins_url('/gik25-microdata/assets/js/progressbar.js'), array('jquery'));
        wp_enqueue_script('md_progressbar-script');
    }

}

$microdata_progressbar = new MicrodataProgressbar();