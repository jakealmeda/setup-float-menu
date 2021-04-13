<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SetupFloatMenu_CustomCategory {

	public function get_custom_category( $filter ) {

		// initialize variables
		$output = '';

		$f = new SetupFloatMenuFunctions();
		$output = $f->sfm_wp_query( $filter[ 'pid' ], $filter[ 'taxy' ], $filter[ 'tax_id' ], $filter[ 'max' ], array( $filter[ 'pid' ] ), $filter[ 'orderby' ], $filter[ 'order' ] );

		// RESET QUERY
		$x = new SetupFloatMenu();
		$x->setup_sfmenu_reset_query();

		// RETURN OUTPUT
		return $output;

	}

}