<?php
namespace gik25microdata;

use DOMDocument;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

	class AdminHelper
	{
        public function __construct()
        {
            add_action('init', [$this, 'update_user_activity']);
//            add_action( 'init', 'remove_extra_field_profile' );
        }

        public function remove_extra_field_profile()
		{
			$current_file_url =  preg_replace( "#\?.*#" , "" , basename( $_SERVER['REQUEST_URI'] ) );

			if( $current_file_url == "profile.php" )
			{
				add_action( 'wp_loaded', function(){ ob_start("profile_callback"); } );
				add_action( 'shutdown', function(){ ob_end_flush(); } );
			}
		}

		public function profile_callback( $html )
		{
			$profile_dom = new DOMDocument;
			$profile_dom->loadHTML( $html );

			$all_lines = $profile_dom->getElementsByTagname( 'tr' );

			$excludes = array(
				'user-rich-editing-wrap',
				'user-admin-color-wrap',
				'user-comment-shortcuts-wrap',
				'show-admin-bar user-admin-bar-front-wrap',
				'user-url-wrap',
				'user-description-wrap'
			);

			$deletes = array();

			foreach ( $all_lines as $line )
			{
				$tr_calss = $line->getAttribute("class");

				if( in_array( $tr_calss, $excludes ) )
				{
					$deletes[] = $line;
				}
			}

			$deletes[] = $profile_dom->getElementsByTagname( 'h2' )->item(0);

			foreach ($deletes as $delete)
			{
				$delete->parentNode->removeChild( $delete );
			}

			return $profile_dom->saveHTML();
		}

    function update_user_activity(): void
    {
        // Aggiorna user meta solo se l'utente è loggato
        if (is_user_logged_in()) {
            update_user_meta(get_current_user_id(), '<last_activity>', time());
        }
    }

	}



