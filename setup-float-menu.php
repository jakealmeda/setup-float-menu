<?php
/**
 * Plugin Name: Setup Float Menu
 * Description: Displays a menu of a group of page/post entries based on Taxonomy
 * Version: 1.0
 * Author: Jake Almeda
 * Author URI: http://smarterwebpackages.com/
 * Network: true
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$sf_menu = new SetupFloatMenu();
class SetupFloatMenu {


    /**
	 * Get all post/page entries link to current entry based on Taxonomy/identifier
	 */
    public function setup_sfmenu_get_entries() {

    	global $post;
    	$category_id = '';
    	$output = '';

    	$terms = get_the_terms( $post->ID, 'category' );
    	if( is_array( $terms ) ) {

    		foreach( $terms as $term ) {

    			//echo 'Name: '.$term->name.'<br />';
    			//echo 'Slug: '.$term->slug.'<br />';
    			//echo 'Cat ID: '.$term->term_taxonomy_id.'<hr />';
    			$category_id .= $term->term_taxonomy_id.', ';

    		}

			$args = array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'cat' => $category_id, //you can pass comma-separated ids here
				'posts_per_page' => -1,
				'post__not_in' => array( $post->ID ),
			);
			$loop = new WP_Query( $args );

		    if( $loop->have_posts() ):
		        
		        // get all post IDs
		        while( $loop->have_posts() ): $loop->the_post();
					
					$pid = get_the_ID();

					$output .= '<div><a href="'.get_the_permalink( $pid ).'">'.get_the_title( $pid ).'</a></div>';

				endwhile;

			endif;
    		
    	}

    	if( !empty( $output ) ) {
    		echo '<div class="mini-menu">'.$output.'</div>';
    	}

    	$this->setup_sfmenu_reset_query();

    }


	/**
	 * Enqueue Style
	 */
	public function setup_sfmenu_enqueue_scripts() {

		// 'jquery-effects-core', 'jquery-effects-fade', 'jquery-ui-accordion'
		/*$scripts = array( 'jquery-ui-core', 'jquery-effects-slide' );
		foreach ( $scripts as $value ) {
			if( !wp_script_is( $value, 'enqueued' ) ) {
				wp_enqueue_script( $value );
			}
		}

		// last arg is true - will be placed before </body>
		wp_enqueue_script( 'setup-pull-script', plugins_url( 'js/asset.js', __FILE__ ), NULL, NULL, TRUE );
		*/
		// enqueue styles
		wp_enqueue_style( 'setup-float-menu-style', plugins_url( 'css/style.css', __FILE__ ) );

	}


	/**
	 * Reset Query
	 */
	public function setup_sfmenu_reset_query() {
		wp_reset_postdata();
		wp_reset_query();
	}

    /**
     * Handle the display
     */
	public function __construct() {

		// Enqueue scripts
		if ( !is_admin() ) {

		    add_action( 'wp_enqueue_scripts', array( $this, 'setup_sfmenu_enqueue_scripts' ), 20 );

		}

		add_action( 'genesis_before_content_sidebar_wrap', array( $this, 'setup_sfmenu_get_entries' ) );

	}


}