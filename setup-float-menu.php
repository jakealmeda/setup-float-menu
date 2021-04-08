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


// include file
include_once( 'lib/sfm-functions.php' );
include_once( 'lib/sfm-native-taxonomy.php' );
include_once( 'lib/sfm-custom-taxonomy.php' );


$sf_menu = new SetupFloatMenu();
class SetupFloatMenu {


    /**
	 * Get all post/page entries link to current entry based on Taxonomy/identifier
	 */
    public function setup_sfmenu_get_entries() {

    	global $post;
    	$output = '';
    	
    	$fm_max_entries = get_post_meta( $post->ID, 'use_max_entries', TRUE );

		// CUSTOM FIELD - CATEGORY
		$fm_use_key_cat = get_post_meta( $post->ID, 'fm_use_category', TRUE );
		// CUSTOM FIELD - TAG
		$fm_use_key_tag = get_post_meta( $post->ID, 'fm_use_tag', TRUE );
		if( !empty( $fm_use_key_cat ) && !empty( $fm_use_key_tag )  ) {

			// CUSTOM FIELD - BOTH CATEGORY & TAG
			$both_tax = array(
				'category'	=> $fm_use_key_cat,
				'tag'		=> $fm_use_key_tag,
			);

			$a = new SetupFloatMenu_CustomCategory();
			$output .= $a->get_custom_category( $post->ID, 'both', $both_tax, $fm_max_entries );

		} else {

			// CUSTOM FIELD - CATEGORY
			if( !empty( $fm_use_key_cat ) && count( $fm_use_key_cat ) >= 1 ) {

				$a = new SetupFloatMenu_CustomCategory();
				$output .= $a->get_custom_category( $post->ID, 'category', $fm_use_key_cat, $fm_max_entries );

			}

			// CUSTOM FIELD - TAG
			if( !empty( $fm_use_key_tag ) && count( $fm_use_key_tag ) >= 1 ) {

				$a = new SetupFloatMenu_CustomCategory();
				$output .= $a->get_custom_category( $post->ID, 'tag', $fm_use_key_tag, $fm_max_entries );

			}

		}

		$fm_default = get_post_meta( $post->ID, 'fm_use_default', TRUE );
		if( $fm_default == 'both' ) {

			$a = new SetupFloatMenu_NativeTaxonomy();
			$output = $a->get_native_taxonomy( $post->ID, 'both', $fm_max_entries );

		} else {

			// NATIVE WP CATEGORY | DEFAULT
			if( empty( $output ) && $fm_default == 'category' ) {

				$a = new SetupFloatMenu_NativeTaxonomy();
				$output = $a->get_native_taxonomy( $post->ID, 'category', $fm_max_entries );

			}

			// NATIVE WP TAG | DEFAULT
			if( empty( $output ) && $fm_default == 'tag' ) {
				
				$a = new SetupFloatMenu_NativeTaxonomy();
				$output = $a->get_native_taxonomy( $post->ID, 'tag', $fm_max_entries );

			}

		}

		// NATIVE WP CATEGORY AND TAG | DEFAULT
/*		if( empty( $output ) && in_array( $fm_default, array( 'category', 'tag', 'both' ) ) ) {
			$output = $output_cat.$output_tag;
		}
*/
		// HANDLE OUPTUT
    	if( !empty( $output ) ) {
    		echo '<div class="mini-menu"><div class="text-base">RELATED STUFF</div>'.$output.'</div>';
    	}

    	// RESET QUERY
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