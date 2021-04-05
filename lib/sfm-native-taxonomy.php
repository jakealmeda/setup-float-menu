<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SetupFloatMenu_NativeTaxonomy {

	// NATIVE WP CATEGORY
	public function get_native_taxonomy( $post_id, $taxy ) {

		 // initialize variables
		$output = '';
		$tax_id = array();

        if( $taxy == 'tag' ) {      	

            $terms = get_the_terms( $post_id, 'post_tag' );

        } else {

            $terms = get_the_terms( $post_id, 'category' );

        }
        
		if( is_array( $terms ) && count( $terms ) >= 1 ) {

			foreach( $terms as $term ) {

				//echo 'Name: '.$term->name.'<br />';
				//echo 'Slug: '.$term->slug.'<br />';
				//echo 'Cat ID: '.$term->term_taxonomy_id.'<hr />';

				// set the term IDs separated by comma
				$tax_id[] = $term->term_taxonomy_id;

			}

			$f = new SetupFloatMenuFunctions();
			$output = $f->sfm_wp_query( $post_id, $taxy, $tax_id, array( $post_id ) );

			// RESET QUERY
			$x = new SetupFloatMenu();
			$x->setup_sfmenu_reset_query();

			// RETURN OUTPUT
			return $output;

		} else {

			return 'Selected taxonomy is empty.';

		}

	}

}