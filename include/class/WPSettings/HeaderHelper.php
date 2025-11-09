<?php

namespace gik25microdata\WPSettings;

use gik25microdata\Utility\ServerHelper;

class HeaderHelper
{
    public function __construct()
    {
        add_action('admin_head', [$this, 'add_LogRocket']);
    }

    function add_LogRocket(): void
    {
        if ( defined( 'DOING_AJAX' ))
        {
            return;
        }

        // Verifica che l'utente sia loggato prima di caricare LogRocket
        if (!is_user_logged_in()) {
            return;
        }
        
        // Verifica che LogRocket sia abilitato nelle impostazioni (di default disabilitato)
        $options = get_option('revious_microdata_option_name', []);
        $logrocket_enabled = isset($options['logrocket_enabled']) && $options['logrocket_enabled'] == '1';
        
        if (!$logrocket_enabled) {
            return; // LogRocket disabilitato di default
        }

        $domain = ServerHelper::getSecondLevelDomainOnly();
        //$domain = getSecondLevelDomain();

        $user = wp_get_current_user();
        echo /** @lang javascript */
        <<<TAG
<script src="https://cdn.lr-ingest.io/LogRocket.min.js" crossorigin="anonymous"></script>
<script>window.LogRocket && window.LogRocket.init('hdyhlv/si', {mergeIframes: true});
LogRocket.identify('{$user->user_login}-$domain', {
  name: '{$user->user_nicename}-$domain',
  email: '{$user->user_email}',
  website: '$domain'
});
</script>
TAG;

    }


//	add_action('wp_head', 'add_Teads');

//	add_filter('the_posts', 'conditionally_add_scripts_and_styles'); // the_posts gets triggered before wp_head
//	function conditionally_add_scripts_and_styles($posts){
//		if (empty($posts)) return $posts;style-post-UnusedCSS+UnCSS.css
//
//	$shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued
//	foreach ($posts as $post) {
//		if (stripos($post-&gt;post_content, '[code]') !== false) {
//			$shortcode_found = true; // bingo!
//			break;
//		}
//	}
//
//	if ($shortcode_found) {
//		// enqueue here
//		wp_enqueue_style('my-style', '/style.css');
//		wp_enqueue_script('my-script', '/script.js');
//	}
//
//	return $posts;
//}

#endregion
}