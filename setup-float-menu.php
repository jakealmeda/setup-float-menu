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

$sf_menu = new SetupFloatMenuX();

// include file
include_once( 'lib/sfm-functions.php' );
include_once( 'lib/sfm-native-taxonomy.php' );
include_once( 'lib/sfm-custom-taxonomy.php' );
include_once( 'lib/sfm-genesis-hooks-list.php' );
include_once( 'lib/sfm-acf-autofill-select-fields.php' );


class SetupFloatMenuX {
	/*

	TO DO: use template for specific entries

	*/

	/**
	 * Loop through the hooks
	 */
	public function setup_sfmenux() {

		// exit if not post or page entry
//		if( ! is_single() )
//			return TRUE;

		$args = array();
		
		// PULL POST ID FROM THE URL
		$post_id = url_to_postid( $_SERVER['REQUEST_URI'] , '_wpg_def_keyword', true ); 

		// loop through each repeater's row
		if( have_rows( 'hook_menu', $post_id ) ):
			
			while( have_rows( 'hook_menu', $post_id ) ): the_row();

				$args = array (
						'pid'						=> $post_id,
						'content_wrap'				=> get_sub_field( 'content_wrap' ),
						'content_header_wrap'		=> get_sub_field( 'content_header_wrap' ),
						'content_selector'			=> get_sub_field( 'content_selector' ),
						'content_footer_wrap'		=> get_sub_field( 'content_footer_wrap' ),
						'menu_entries'				=> get_sub_field( 'menu_entries' ),
						//'view_template'				=> get_sub_field( 'view_template' ),
					);
				
				// display fields based on the hook	| pass the variable to the function
				add_action( get_sub_field( 'menu_hook' ), function() use ( $args ) {

					$this->setup_sfmenux_get_menus( $args );

				});

			endwhile;
		
		endif;

	}


	/**
	 * Loop through the entry repeater
	 */
	public function setup_sfmenux_get_menus( $args ) {

		// LOOP THROUGH THE ENTRIES
		if( is_array( $args[ 'menu_entries' ] ) ) :


			// SET VARIABLE ARRAY
			$filter = array(
				'pid'				=> $args[ 'pid' ],
				'content_selector'	=> $args[ 'content_selector' ],
			);


			// INITIALIZE
			$custom_tax = new SetupFloatMenu_CustomCategory();
			$native_tax = new SetupFloatMenu_NativeTaxonomy();
			$tag_body = '';
			//$menu_main = '';
			//$menu_specs = '';

			// LOOP THROUGH CHILD REPEATER
			foreach( $args[ 'menu_entries' ] as $v ) {

				$output = ''; // Set an empty variable to avoid undefined variable error

				// Set variables
				$filter[ 'max' ] = $v[ 'max_entries' ];
				$filter[ 'orderby' ] = $v[ 'order_by' ];
				$filter[ 'order' ] = $v[ 'order' ];
				if( empty( $v[ 'display_taxonomy_name' ] ) ) :
					$filter[ 'display_taxonomy_name' ] = 'show'; 				// SET DEFAULT VALUE
				else:
					$filter[ 'display_taxonomy_name' ] = $v[ 'display_taxonomy_name' ];
				endif;

				// TAB | TAXONOMY
				// --------------------------------
				
				// Display | Show entries or taxonomies
				if( $v[ 'display' ] == 'taxy' ) {

					/* **************
					 * TAXONOMY
					 * *********** */

					if( !empty( $v[ 'entry-category' ] ) || !empty( $v[ 'entry-tag' ] ) ) :

						// CATEGORY (ACF CUSTOM FIELD)
						if( !empty( $v[ 'entry-category' ] ) ) :

							$output .= $this->setup_sfmenux_handle_output( $v[ 'entry-category' ], $filter[ 'display_taxonomy_name' ], 'category', $args[ 'content_selector' ] );
						
						endif;

						// POST TAG (ACF CUSTOM FIELD)
						if( !empty( $v[ 'entry-tag' ] ) ) :
							
							$output .= $this->setup_sfmenux_handle_output( $v[ 'entry-tag' ], $filter[ 'display_taxonomy_name' ], 'tag', $args[ 'content_selector' ] );
						
						endif;

		    		else :

						$terms_ptag = get_the_terms( $args[ 'pid' ], 'post_tag' );
						$terms_categ = get_the_terms( $args[ 'pid' ], 'category' );
		    			
						if( $v[ 'default' ] == 'both' ) {

							foreach( array( 'category' => $terms_categ, 'tag' => $terms_ptag ) as $ke => $va ) {

								$output .= $this->setup_sfmenux_handle_output( $va, $filter[ 'display_taxonomy_name' ], $ke, $args[ 'content_selector' ] );

							}

						}

						if( $v[ 'default' ] == 'category' ) {

							// CATEGORY
							$output .= $this->setup_sfmenux_handle_output( $terms_categ, $filter[ 'display_taxonomy_name' ], $v[ 'default' ], $args[ 'content_selector' ] );

						}

						if( $v[ 'default' ] == 'tag' ) {

							// POST TAG
							$output .= $this->setup_sfmenux_handle_output( $terms_ptag, $filter[ 'display_taxonomy_name' ], $v[ 'default' ], $args[ 'content_selector' ] );

						}
						
					endif;

				} else {

					/* **************
					 * ENTRIES
					 * *********** */

					if( is_array( $v[ 'entries' ] ) ) {
						$filter[ 'not_in' ] = $v[ 'entries' ];
					} else {
						$filter[ 'not_in' ] = array( $v[ 'entries' ] );
					}
					

					// ACF CUSTOM FIELDS
					if( !empty( $v[ 'entry-category' ] ) && !empty( $v[ 'entry-tag' ] ) ) {

						// CUSTOM FIELD - BOTH CATEGORY & TAG
						$filter[ 'taxy' ] = 'both';
						$filter[ 'tax_id' ] = array(
							'category'	=> $v[ 'entry-category' ],
							'tag'		=> $v[ 'entry-tag' ],
						);

						$output .= $custom_tax->get_custom_category( $filter );

					} else { // Individual

						// Category
						if( !empty( $v[ 'entry-category' ] ) ) {

							$filter[ 'taxy' ] = 'category';
							$filter[ 'tax_id' ] = $v[ 'entry-category' ];
							
							$output .= $custom_tax->get_custom_category( $filter );
							
						}

						// Post Tag
						if( !empty( $v[ 'entry-tag' ] ) ) {

							$filter[ 'taxy' ] = 'tag';
							$filter[ 'tax_id' ] = $v[ 'entry-tag' ];
							
							$output .= $custom_tax->get_custom_category( $filter );
							
						}

					}


					// DEFAULT TAXONOMIES
					if( empty( $output ) && $v[ 'default' ] == 'both' ) {

						$filter[ 'taxy' ] = 'both';

						$output = $native_tax->get_native_taxonomy( $filter );

					} else {

						// NATIVE WP CATEGORY | DEFAULT
						if( empty( $output ) && $v[ 'default' ] == 'category' ) {

							$filter[ 'taxy' ] = 'category';

							$output = $native_tax->get_native_taxonomy( $filter );

						}

						// NATIVE WP TAG | DEFAULT
						if( empty( $output ) && $v[ 'default' ] == 'tag' ) {

							$filter[ 'taxy' ] = 'tag';

							$output = $native_tax->get_native_taxonomy( $filter );

						}

					}

				} // if( $v[ 'display' ] == 'taxy' ) {
				

				// HEADER WRAP CSS SELECTOR
				if( empty( $args[ 'content_header_wrap' ] ) ) {
					$head_wrap = '';
				} else {
					$head_wrap = ' '.$args[ 'content_header_wrap' ].'>';
				}


				// HANDLE OUPTUT
				if( !empty( $output ) ) {

					// MENU LABEL (NAME)
					if( empty( $v[ 'label_menu' ] ) ) {
						$out_label_menu = ''; // Set an empty variable to avoid undefined variable error					
					} else {
						$out_label_menu = '<header><div class="item label'.$head_wrap.'">'.$v[ 'label_menu' ].'</div></header>';
					}

					$menu_main = $out_label_menu.$output;

				} else {

					$menu_main = '';

				}


				// TAB | SPECIFIC ENTRIES
				// --------------------------------
				if( !empty( $v[ 'entries' ] ) ) {
					// GET ENTRIES
					$menu_specs = $this->setup_sfmenux_related( $v[ 'entries' ], $v[ 'display_post_type' ], $head_wrap, $v[ 'label_entry' ], $args[ 'content_selector' ] );
				} else {
					$menu_specs = '';
				}


				// WHERE TO SHOW
				if( $v[ 'hierarchy' ] == 'before' ) {
					$tag_body .= $menu_specs.$menu_main;
				} else {
					// AFTER
					$tag_body .= $menu_main.$menu_specs;
				}


				// RESET QUERY
				$this->setup_sfmenux_reset_query();

			} // foreach( $args[ 'menu_entries' ] as $v ) {

		endif; // if( is_array( $args[ 'menu_entries' ] ) ) :


		// OUTPUT TAG | OPENING
		if( empty( $args[ 'content_wrap' ] ) ) {
			$tag_open = '<div class="module">'; // removed the class, floatmenu
		} else {
			$tag_open = '<div class="module '.$args[ 'content_wrap' ].'">';
		}


		// OUTPUT TAG | CLOSING
		$tag_close = '<footer></footer></div>';

		// SHOW IT
		echo $tag_open.$tag_body.$tag_close;

		// ############################
/*		if( !empty( $menu_main ) || !empty( $menu_specs ) ) :
			
			$replace_array = array(
				'{@content_wrap_open}'				=> '<div class="module'.$args[ 'content_wrap' ].'">',
				'{@content_wrap_close}'				=> '</div>',
				'{@menu_tax}' 						=> $menu_main,
				'{@menu_specs}'						=> $menu_specs,
			);
		else:
			$replace_array = array(
				'{@content_wrap_open}'				=> NULL,
				'{@content_wrap_close}'				=> NULL,
				'{@menu_tax}' 						=> NULL,
				'{@menu_specs}'						=> NULL,
			);
		endif;

		$q = new SetupFloatMenuFunctions();
		echo strtr( $q->sfm_view_templates_contents( $args[ 'view_template' ] ), $replace_array );*/
		// ############################

	}


	/**
	 * Reset Query
	 */
	public function setup_sfmenux_reset_query() {
		wp_reset_postdata();
		wp_reset_query();
	}


	/**
	 * RELATED ENTRIES
	 */
	public function setup_sfmenux_related( $entries, $post_typed, $head_wrap, $entries_label, $css_selector ) {

		$outs = '';

		foreach( $entries as $e ) {

			$p_entry = get_post( $e );

			// label
			if( $post_typed == 'hide' ) {
				$pt = '';
			} else {
				$pt = ' <span class="item label-entry fontsize-tiny">('.ucfirst( $p_entry->post_type ).')</span>';
			}

			// selector
			if( empty( $css_selector ) ) {
				$selectors = '';
			} else {
				$selectors = ' class="'.$css_selector.'"';
			}
			
			$outs .= '<div'.$selectors.'><a href="'.get_the_permalink( $e ).'">'.$p_entry->post_title.'</a>'.$pt.'</div>';
		}


		if( !empty( $outs ) ):

			if( empty( $entries_label ) ) {
				return $outs;
			} else {
				return '<header><div class="item label'.$head_wrap.'">'.$entries_label.'</div></header>'.$outs;
			}

		endif;

	}


	/**
	 * Handle Output
	 */
	public function setup_sfmenux_handle_output( $taxonomies, $trigger, $black_label, $content_selector ) {

		$out = '';
		
		foreach( $taxonomies as $key => $value ) {

			// validate label
			if( $trigger != 'show' ) {
				$ks = '';
			} else {
				$ks = ' <span class="item label-entry fontsize-tiny">('.ucfirst( $black_label ).')</span>';
			}

			// validate css selector
			if( empty( $content_selector ) ) {
				$selector = '';
			} else {
				$selector = ' class="'.$content_selector.'"';
			}

			if( is_object( $value ) ) {
				$out .= '<div '.$selector.'><a href="'.get_term_link( $value->term_taxonomy_id ).'">'.$value->name.'</a>'.$ks.'</div>';
			} else {
				$out .= '<div '.$selector.'><a href="'.get_term_link( get_term( $value )->term_taxonomy_id ).'">'.get_term( $value )->name.'</a>'.$ks.'</div>';
			}

		}

		return $out;

	}


	/**
	 * This plug-in's directory
	 */
	public function setup_sfm_dir_path() {

		return plugin_dir_path( __FILE__ );

	}


	/**
	 * Enqueue Style
	 */
	public function setup_sfmenux_enqueue_scripts() {

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
	 * Handle the display
	 */
	public function __construct() {

		// Enqueue scripts
		if ( !is_admin() ) {

			add_action( 'wp_enqueue_scripts', array( $this, 'setup_sfmenux_enqueue_scripts' ), 20 );

			add_action( 'init', array( $this, 'setup_sfmenux' ) );

		}

	}


}