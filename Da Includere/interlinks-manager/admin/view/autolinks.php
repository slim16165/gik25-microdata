<?php
/**
 * The file used to display the "Autolinks" menu in the admin area.
 *
 * @package interlinks-manager
 */

$this->menu_elements->capability = get_option( $this->shared->get( 'slug' ) . '_categories_menu_required_capability' );
$this->menu_elements->context = 'crud';
$this->menu_elements->display_menu_content();