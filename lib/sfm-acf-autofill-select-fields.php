<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//include_once( 'sfm-genesis-hooks-list.php' );

$sf_menu_acf = new SetupFloatMenu_FillACFSelectFields();
class SetupFloatMenu_FillACFSelectFields {

	/**
	 * Auto fill Select options for Genesis Hooks
	 *
	 */
	public function sfm_autofill_select_hooks( $field ) {

		$hookers = new SetupFloatMenu_GenesisHooksList();

		$field['choices'] = array();

		//Loop through whatever data you are using, and assign a key/value
		if( is_array( $hookers->genesis_hooks ) ) {

		    foreach( $hookers->genesis_hooks as $value ) {
		        
		        $field['choices'][$value] = $value;
		    }

		    return $field;

		}

	}


	// CONSTRUCT
	public function __construct() {

		// AUTO FILL SELECT FOR HOOKS (ACF)
		add_filter( 'acf/load_field/name=fm_use_hook', array( $this, 'sfm_autofill_select_hooks' ) );
		add_filter( 'acf/load_field/name=fm_reuse_hook', array( $this, 'sfm_autofill_select_hooks' ) );

	}
	
}