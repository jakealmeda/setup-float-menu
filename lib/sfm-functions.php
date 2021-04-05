<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SetupFloatMenuFunctions {

	public function sfm_wp_query( $post_id, $taxy, $tax_id, $not_in ) {

		$output = ''; // initialize variable

		// set the arguments
		$args = array(
			'post_type' 		=> get_post_type( $post_id ),
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> -1,
			'post__not_in' 		=> $not_in,
		);

		// set the taxonomy
		if( $taxy == 'tag' ) {
			$args[ 'tag__in' ] = $tax_id; // array
		} else {
			$args[ 'category__in' ] = $tax_id; // array
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