<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SetupFloatMenu_NativeTaxonomy {

	// NATIVE WP CATEGORY
	public function get_native_taxonomy( $filter ) {

		 // initialize variables
		$output = '';
		$tax_id = array();
		
		if( $filter[ 'taxy' ] == 'both' ) {

			$tax_id[ 'category' ] = $this->sfm_collate_tax_term_id( get_the_terms( $filter[ 'pid' ], 'category' ) );
			$tax_id[ 'tag' ] = $this->sfm_collate_tax_term_id( get_the_terms( $filter[ 'pid' ], 'post_tag' ) );

		} else {

			if( $filter[ 'taxy' ] == 'tag' ) {

				$terms = get_the_terms( $filter[ 'pid' ], 'post_tag' );

			} else {

				$terms = get_the_terms( $filter[ 'pid' ], 'category' );

			}

			$tax_id = $this->sfm_collate_tax_term_id( $terms );

		}
        
        // PROCESS THE ARRAY TO GET THE TAXONOMY (TERM) ID
		if( count( $tax_id ) >= 1 ) {

			// PROCESS OUTPUT QUERY
			$f = new SetupFloatMenuFunctions();
			$output = $f->sfm_wp_query( $filter[ 'pid' ], $filter[ 'taxy' ], $tax_id, $filter[ 'max' ], array( $filter[ 'pid' ] ), $filter[ 'orderby' ], $filter[ 'order' ] );

			// RESET QUERY
			$x = new SetupFloatMenu();
			$x->setup_sfmenu_reset_query();

			// RETURN OUTPUT
			return $output;

		} else {

			return 'Selected taxonomy is empty.';

		}

	}


	/**
	 * Capture the Taxonomy Term ID
	 */
	private function sfm_collate_tax_term_id( $terms ) {

		$tax_id = array();

		if( is_array( $terms ) ) :

			foreach( $terms as $term ) {

				//echo 'Name: '.$term->name.'<br />';
				//echo 'Slug: '.$term->slug.'<br />';
				//echo 'Cat ID: '.$term->term_taxonomy_id.'<hr />';

				// set the term IDs separated by comma
				$tax_id[] = $term->term_taxonomy_id;

			}

		endif;

		return $tax_id;

	}

}