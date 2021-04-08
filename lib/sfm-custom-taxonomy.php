<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SetupFloatMenu_CustomCategory {

	public function get_custom_category( $post_id, $taxy, $get_from_these_taxo, $fm_max_entries ) {

		// initialize variables
		$output = '';

		$f = new SetupFloatMenuFunctions();
		$output = $f->sfm_wp_query( $post_id, $taxy, $get_from_these_taxo, $fm_max_entries, array( $post_id ) );

		// RESET QUERY
		$x = new SetupFloatMenu();
		$x->setup_sfmenu_reset_query();

		// RETURN OUTPUT
		return $output;

	}

}