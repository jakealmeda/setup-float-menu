<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SetupFloatMenu_CustomCategory {

	public function get_custom_category( $filter ) {
		
		// initialize variables
		$output = '';

		$not_in = array_merge( $filter[ 'not_in' ], array( $filter[ 'pid' ] ) );

		$f = new SetupFloatMenuFunctions();
		$output = $f->sfm_wp_query( $filter[ 'pid' ], $filter[ 'taxy' ], $filter[ 'tax_id' ], $filter[ 'max' ], $not_in, $filter[ 'orderby' ], $filter[ 'order' ], $filter[ 'content_selector' ], $filter[ 'display_taxonomy_name' ] );

		// RESET QUERY
		$x = new SetupFloatMenux();
		$x->setup_sfmenux_reset_query();

		// RETURN OUTPUT
		return $output;

	}

}