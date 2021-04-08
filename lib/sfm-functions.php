<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SetupFloatMenuFunctions {

	public function sfm_wp_query( $post_id, $taxy, $tax_id, $fm_max_entries, $not_in ) {

		$output = ''; // initialize variable

		if( empty( $fm_max_entries ) || $fm_max_entries == 0 )
			$fm_max_entries = 8;

		// set the arguments
		$args = array(
			'post_type' 		=> get_post_type( $post_id ),
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> $fm_max_entries,
			'post__not_in' 		=> $not_in,
			'orderby' 			=> 'date',
    		'order'   			=> 'DESC',
		);

		// set the taxonomy | add additional filters
		if( $taxy == 'both' )  {

			$args[ 'tax_query' ] = array(
				'relation'	=>	'OR',
				array(
					'taxonomy' => 'category',
					'field'    => 'term_id',
					'terms'    => $tax_id[ 'category' ],
				),
				array(
					'taxonomy' => 'post_tag',
					'field'    => 'term_id',
					'terms'    => $tax_id[ 'tag' ],
				),
			);

		} else {

			if( $taxy == 'tag' ) {
				$args[ 'tag__in' ] = $tax_id; // array
			} else {
				$args[ 'category__in' ] = $tax_id; // array
			}

		}

		// query
		$loop = new WP_Query( $args );
		
		// loop
	    if( $loop->have_posts() ):
	    	
	        // get all post IDs
	        while( $loop->have_posts() ): $loop->the_post();
				
				$pid = get_the_ID();
				
				$output .= '<div><a href="'.get_the_permalink( $pid ).'">'.get_the_title( $pid ).'</a></div>';

			endwhile;

		endif;

		return $output;

	}

}