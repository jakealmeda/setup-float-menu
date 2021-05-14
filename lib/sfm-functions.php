<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SetupFloatMenuFunctions {

	public function sfm_wp_query( $post_id, $taxy, $tax_id, $fm_max_entries, $not_in, $orderby, $order, $contentcss, $display_taxonomy_name ) {

		$output = ''; // initialize variable

		if( empty( $fm_max_entries ) || $fm_max_entries == 0 )
			$fm_max_entries = 8;

		// set default order by field
		if( empty( $orderby ) ) {
			$orderby = 'date';
		} else {
			$orderby = $orderby;
		}

		// set default order
		if( empty( $order ) ) {
			$order = 'DESC';
		} else {
			$order = $order;
		}

		// set selector
		if( empty( $contentcss ) ) {
			$selector = '';
		} else {
			$selector = ' class="'.$contentcss.'"';
		}

		// set the arguments
		$args = array(
			'post_type' 		=> get_post_type( $post_id ),
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> $fm_max_entries,
			'post__not_in' 		=> $not_in,
			'orderby' 			=> $orderby,
    		'order'   			=> $order,
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

		// show post type
		if( $display_taxonomy_name == 'show' ) {
			$dtn = ' <span class="item label-entry fontsize-tiny">('.ucfirst( get_post_type( $post_id ) ).')</span>';
		} else {
			$dtn = '';
		}

		// query
		$loop = new WP_Query( $args );
		
		// loop
		if( $loop->have_posts() ):

			// get all post IDs
			while( $loop->have_posts() ): $loop->the_post();
				
				$pid = get_the_ID();
				
				$output .= '<div'.$selector.'><a href="'.get_the_permalink( $pid ).'">'.get_the_title( $pid ).'</a>'.$dtn.'</div>';
				
			endwhile;

		endif;

		return $output;

	}

	/**
	* Get VIEW template (INCLUDE)
	*
	*/
	function sfm_view_templates_contents( $layout ) {

		$z = new SetupFloatMenuX();

		$layout_file = $z->setup_sfm_dir_path().'views/'.$layout;

		return file_get_contents( $layout_file );

	}

}