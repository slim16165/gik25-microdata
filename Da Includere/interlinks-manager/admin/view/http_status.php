<?php
/**
 * The file used to display the "HTTP Status" menu in the admin area.
 *
 * @package interlinks-manager
 */

$this->menu_elements->capability = get_option( $this->shared->get( 'slug' ) . '_http_status_menu_required_capability' );
$this->menu_elements->context = null;
$this->menu_elements->display_menu_content();