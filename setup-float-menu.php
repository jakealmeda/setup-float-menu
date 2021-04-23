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
include_once( 'lib/sfm-genesis-hooks-list.php' );
include_once( 'lib/sfm-acf-autofill-select-fields.php' );


$sf_menu = new SetupFloatMenu();
class SetupFloatMenu {

    /**
	 * Get all post/page entries link to current entry based on Taxonomy/identifier
	 */
    public function setup_sfmenu_get_entries() {

    	// exit if not post or page entry
    	if( ! is_single() )
    		return TRUE;

    	global $post;
    	$output = '';

    	// set variable array
    	$filter = array(
    		'pid'				=> $post->ID,
    		'max'				=> get_post_meta( $post->ID, 'use_max_entries', TRUE ),
    		'orderby'			=> get_post_meta( $post->ID, 'fm_use_order_field', TRUE ),
    		'order'				=> get_post_meta( $post->ID, 'fm_use_order_by', TRUE ),
    	);


    	$a = new SetupFloatMenu_CustomCategory();


		// CUSTOM FIELD | CATEGORY
		$fm_use_key_cat = get_post_meta( $post->ID, 'fm_use_category', TRUE );
		// CUSTOM FIELD | TAG
		$fm_use_key_tag = get_post_meta( $post->ID, 'fm_use_tag', TRUE );
		if( !empty( $fm_use_key_cat ) && !empty( $fm_use_key_tag )  ) {

			// CUSTOM FIELD - BOTH CATEGORY & TAG
			/*$both_tax = array(
				'category'	=> $fm_use_key_cat,
				'tag'		=> $fm_use_key_tag,
			);*/
			$filter[ 'taxy' ] = 'both';
			$filter[ 'tax_id' ] = array(
				'category'	=> $fm_use_key_cat,
				'tag'		=> $fm_use_key_tag,
			);

			$output .= $a->get_custom_category( $filter );

		} else {

			// CUSTOM FIELD - CATEGORY
			if( !empty( $fm_use_key_cat ) && count( $fm_use_key_cat ) >= 1 ) {

				$filter[ 'taxy' ] = 'category';
				$filter[ 'tax_id' ] = $fm_use_key_cat;

				$output .= $a->get_custom_category( $filter );

			}

			// CUSTOM FIELD - TAG
			if( !empty( $fm_use_key_tag ) && count( $fm_use_key_tag ) >= 1 ) {

				$filter[ 'taxy' ] = 'tag';
				$filter[ 'tax_id' ] = $fm_use_key_tag;

				$output .= $a->get_custom_category( $filter );

			}

		}

		// DEFAULT
		$fm_default = get_post_meta( $post->ID, 'fm_use_default', TRUE );
		if( $fm_default == 'both' ) {

			$filter[ 'taxy' ] = 'both';

			$a = new SetupFloatMenu_NativeTaxonomy();
			$output = $a->get_native_taxonomy( $filter );

		} else {

			// NATIVE WP CATEGORY | DEFAULT
			if( empty( $output ) && $fm_default == 'category' ) {

				$filter[ 'taxy' ] = 'category';

				$a = new SetupFloatMenu_NativeTaxonomy();
				$output = $a->get_native_taxonomy( $filter );

			}

			// NATIVE WP TAG | DEFAULT
			if( empty( $output ) && $fm_default == 'tag' ) {

				$filter[ 'taxy' ] = 'tag';
				
				$a = new SetupFloatMenu_NativeTaxonomy();
				$output = $a->get_native_taxonomy( $filter );

			}

		}

		// MENU NAME
		$menu_name = get_post_meta( $post->ID, 'fm_use_menu_name', TRUE );
		if( empty( $menu_name ) ) {
			$menu_name = '';
		}  else {
			$menu_name = '<div class="item label">'.$menu_name.'</div>';
		}

		// HANDLE OUPTUT
    	if( !empty( $output ) ) {

    		// get selector wrap
    		$wrapper = get_post_meta( $post->ID, 'fm_use_selector', TRUE );
			if( empty( $wrapper ) ) {
				echo '<div class="module floatmenu"><header></header>'.$menu_name.$output.'<footer></footer></div>';
			} else {
				echo '<div class="module floatmenu '.$wrapper.'"><header></header>'.$menu_name.$output.'<footer></footer></div>';
			}

    	}

    	// RESET QUERY
    	$this->setup_sfmenu_reset_query();

    }


	/**
	 * Loop through the hooks
	 */
	public function setup_sfmenu() {

		$filter_args = array();

		// PULL POST ID FROM THE URL
		$post_id = url_to_postid( $_SERVER['REQUEST_URI'] , '_wpg_def_keyword', true ); 

		// VALIDATE AND DISPLAY FIRST MENU
		$init_menu = get_post_meta( $post_id, 'fm_use_hook', TRUE );
		if( !empty( $init_menu ) ) {

			add_action( $init_menu, array( $this, 'setup_sfmenu_get_entries' ) );

		}

		// loop through each repeater's row
		if( have_rows( 'fm_use_more_menus', $post_id ) ):

		    while( have_rows( 'fm_use_more_menus', $post_id ) ): the_row();

				$filter_args = array (
						'pid'					=> $post_id,
						'menu_name'				=> get_sub_field( 'fm_reuse_menu_name' ),
						'category' 				=> get_sub_field( 'fm_reuse_category' ),
						'tag'					=> get_sub_field( 'fm_reuse_tag' ),
						'fm_reuse_default'		=> get_sub_field( 'fm_reuse_default' ),
						'fm_reuse_max_entries'	=> get_sub_field( 'fm_reuse_max_entries' ),
						'fm_reuse_order_field'	=> get_sub_field( 'fm_reuse_order_field' ),
						'fm_reuse_order_by'		=> get_sub_field( 'fm_reuse_order_by' ),
						'fm_reuse_selector'		=> get_sub_field( 'fm_reuse_selector' ),
					);
				
				// display fields based on the hook	| pass the variable to the function
		        add_action( get_sub_field( 'fm_reuse_hook' ), function() use ( $filter_args ) {

		        	$this->setup_sfmenu_process_repeater( $filter_args );

		        });

		    endwhile;
		 
		endif;

	}


    /**
	 * Process repeater field contents
	 * This functions SHOULD ALWAYS BE BELOW the function, setup_sfmenu
	 */
	public function setup_sfmenu_process_repeater( $args ) {
		
		$output = '';

		$a = new SetupFloatMenu_CustomCategory();

    	// set variable array
    	$filter = array(
    		'pid'				=> $args[ 'pid' ],
    		'max'				=> $args[ 'fm_reuse_max_entries' ],
    		'orderby'			=> $args[ 'fm_reuse_order_field' ],
    		'order'				=> $args[ 'fm_reuse_order_by' ],
    	);

    	// Custom Field Taxonomy
    	if( !empty( $args[ 'category' ] ) && !empty( $args[ 'tag' ] )  ) {

			$filter[ 'taxy' ] = 'both';
			$filter[ 'tax_id' ] = array(
				'category'	=> $args[ 'category' ],
				'tag'		=> $args[ 'tag' ],
			);

			$output .= $a->get_custom_category( $filter );

    	} else {

			// CUSTOM FIELD - CATEGORY
			if( !empty( $args[ 'category' ] ) && count( $args[ 'category' ] ) >= 1 ) {

				$filter[ 'taxy' ] = 'category';
				$filter[ 'tax_id' ] = $args[ 'category' ];

				$output .= $a->get_custom_category( $filter );
				//var_dump($args);
			}

			// CUSTOM FIELD - TAG
			if( !empty( $args[ 'tag' ] ) && count( $args[ 'tag' ] ) >= 1 ) {

				$filter[ 'taxy' ] = 'tag';
				$filter[ 'tax_id' ] = $args[ 'tag' ];

				$output .= $a->get_custom_category( $filter );
				//var_dump($args);
			}

		}

		// DEFAULT
		$fm_default = $args[ 'fm_reuse_default' ];
		if( $fm_default == 'both' ) {

			$filter[ 'taxy' ] = 'both';

			$a = new SetupFloatMenu_NativeTaxonomy();
			$output = $a->get_native_taxonomy( $filter );

		} else {

			// NATIVE WP CATEGORY | DEFAULT
			if( empty( $output ) && $fm_default == 'category' ) {

				$filter[ 'taxy' ] = 'category';

				$a = new SetupFloatMenu_NativeTaxonomy();
				$output = $a->get_native_taxonomy( $filter );

			}

			// NATIVE WP TAG | DEFAULT
			if( empty( $output ) && $fm_default == 'tag' ) {

				$filter[ 'taxy' ] = 'tag';
				
				$a = new SetupFloatMenu_NativeTaxonomy();
				$output = $a->get_native_taxonomy( $filter );

			}

		}

		$menu_name = $args[ 'menu_name' ];
		if( empty( $menu_name ) ) {
			$menu_name = '';
		} else {
			$menu_name = '<div class="item label">'.$menu_name.'</di>';
		}

		// HANDLE OUPTUT
    	if( !empty( $output ) ) {

    		// get selector wrap
    		$wrapper = $args[ 'fm_reuse_selector' ];
    		if( empty( $wrapper ) ) {
    			echo '<div class="module floatmenu"><header></header>'.$menu_name.$output.'<footer></footer></div>';
    		} else {
    			echo '<div class="module floatmenu '.$wrapper.'"><header></header>'.$menu_name.$output.'<footer></footer></div>';
    		}
    		
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

		    add_action( 'init', array( $this, 'setup_sfmenu' ) );

		}

		//add_action( 'genesis_before_content_sidebar_wrap', array( $this, 'setup_sfmenu_get_entries' ) );

	}


}