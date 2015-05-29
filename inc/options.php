<?php

/**
 * Get the settings
 *
 * @param string $key Only get a specific key.
 * @return mixed
 */
function siteorigin_panels_setting( $key = '' ) {

	if ( has_action( 'after_setup_theme' ) ) {
		// Only use static settings if we've initialized the theme
		static $settings;
	}
	else {
		$settings = false;
	}

	if ( empty( $settings ) ) {
		$display_settings = get_option( 'siteorigin_panels_settings', array() ); //This option does not exist
		$display_settings = get_option( 'siteorigin_panels_display', array() );
		
		$generalSettings = get_option( 'siteorigin_panels_general', array() );

		$settings = get_theme_support( 'siteorigin-panels' );
		if ( ! empty( $settings ) ) $settings = $settings[0];
		else $settings = array();


		$settings = wp_parse_args( $settings, array(
			'home-page' => false,																					    // Is the home page supported
			'home-page-default' => false,																			    // What's the default layout for the home page?
			'home-template' => 'home-panels.php',																	    // The file used to render a home page.
			'post-types' => get_option( 'pootle_page_builder_post_types', array( 'page' ) ),						    // Post types that can be edited using panels.

			'responsive' => ! isset( $display_settings['responsive'] ) ? true : $display_settings['responsive'] == '1', // Should we use a responsive layout
			'mobile-width' => ! isset( $display_settings['mobile-width'] ) ? 780 : $display_settings['mobile-width'],   // What is considered a mobile width?

			'margin-bottom' => ! isset( $display_settings['margin-bottom'] ) ? 30 : $display_settings['margin-bottom'], // Bottom margin of a cell
			'margin-sides' => ! isset( $display_settings['margin-sides'] ) ? 30 : $display_settings['margin-sides'],    // Spacing between 2 cells
			'affiliate-id' => false,																				    // Set your affiliate ID
			'copy-content' => '',                                                                                       // Should we copy across content
			'animations' => true,                                                                                       // We want animations always enabled
			'inline-css' => true,                                                                                       // How to display CSS
			'remove-list-padding' => ! isset( $display_settings['remove-list-padding'] ) ? true : $display_settings['remove-list-padding'] == '1',	// Remove left padding on list
		 ) );

		// Filter these settings
		$settings = apply_filters( 'siteorigin_panels_settings', $settings );
		if ( empty( $settings['post-types'] ) ) $settings['post-types'] = array();
	}

	if ( ! empty( $key ) ) return isset( $settings[$key] ) ? $settings[$key] : null;
	return $settings;
}

/**
 * Add the options page
 */
function siteorigin_panels_options_admin_menu() {
	add_options_page( 'Page Builder', 'Page Builder', 'manage_options', 'page_builder', 'pootle_page_options_page' );
}
add_action( 'admin_menu', 'siteorigin_panels_options_admin_menu', 100 );

/**
 * Display the admin page.
 */
function pootle_page_options_page() {
	include plugin_dir_path( POOTLEPAGE_BASE_FILE ) . '/tpl/options.php';
}

/**
 * Register all the settings fields.
 */
function siteorigin_panels_options_init() {
	register_setting( 'pootlepage-general', 'siteorigin_panels_general', 'siteorigin_panels_options_sanitize_general' );
	register_setting( 'pootlepage-display', 'siteorigin_panels_display', 'siteorigin_panels_options_sanitize_display' );
	register_setting( 'pootlepage-widgets', 'pootlepage-widgets' );

	add_settings_section( 'styling', __( 'Widget Styling', 'siteorigin-panels' ), 'pp_pb_options_page_styling', 'pootlepage-styling' );
	add_settings_section( 'display', __( 'Display', 'siteorigin-panels' ), '__return_false', 'pootlepage-display' );

	// The display fields
	add_settings_field( 'responsive', __( 'Responsive', 'siteorigin-panels' ), 'siteorigin_panels_options_field_display', 'pootlepage-display', 'display', array( 'type' => 'responsive' ) );
	add_settings_field( 'mobile-width', __( 'Mobile Width', 'siteorigin-panels' ), 'siteorigin_panels_options_field_display', 'pootlepage-display', 'display', array( 'type' => 'mobile-width' ) );
	add_settings_field( 'margin-sides', __( 'Margin Sides', 'siteorigin-panels' ), 'siteorigin_panels_options_field_display', 'pootlepage-display', 'display', array( 'type' => 'margin-sides' ) );
	add_settings_field( 'margin-bottom', __( 'Margin Bottom', 'siteorigin-panels' ), 'siteorigin_panels_options_field_display', 'pootlepage-display', 'display', array( 'type' => 'margin-bottom' ) );
	add_settings_field( 'remove-list-padding', __( 'Remove list padding', 'siteorigin-panels' ), 'siteorigin_panels_options_field_display', 'pootlepage-display', 'display', array(
		'type' => 'remove-list-padding',
		'description' => __( 'Remove left padding for list widgets used in page content container.', 'siteorigin-panels' ),
	) );
}
add_action( 'admin_init', 'siteorigin_panels_options_init' );

add_action( 'admin_notices', 'pp_pb_admin_notices' );

function pp_pb_admin_notices() {

	$notices = get_option( 'pootle_page_admin_notices', array() );

	delete_option( 'pootle_page_admin_notices' );

	if ( 0 < count( $notices ) ) {
		$html = '';
		foreach ( $notices as $k => $v ) {
			$html .= '<div id="' . esc_attr( $k ) . '" class="fade ' . esc_attr( $v['type'] ) . '">' . wpautop( '<strong>' . esc_html( $v['message'] ) . '</strong>' ) . '</div>' . "\n";
		}
		echo $html;
	}

}


function pp_pb_options_page_styling() {
	global $PP_PB_WF_Settings;
	$PP_PB_WF_Settings->settings_screen();
}

function pootlepage_options_page_styling() {
	$customizeUrl = admin_url( 'customize.php' );
	echo "<p>Folio uses the WordPress customizer to allow you to style your widgets and preview them easily. Click <a href='" . esc_attr( $customizeUrl ) . "'>here</a> to go to these settings.</p>";
}

function siteorigin_panels_options_field_generic( $args, $groupName ) {
	$settings = siteorigin_panels_setting();
	switch( $args['type'] ) {
		case 'responsive' :
		case 'bundled-widgets' :
		case 'remove-list-padding' :
			?><label><input type="checkbox" name="<?php echo $groupName ?>[<?php echo esc_attr( $args['type'] ) ?>]" <?php checked( $settings[$args['type']] ) ?> value="1" /> <?php _e( 'Enabled', 'siteorigin-panels' ) ?></label><?php
			break;
		case 'margin-bottom' :
		case 'margin-sides' :
		case 'mobile-width' :
			?><input type="text" name="<?php echo $groupName ?>[<?php echo esc_attr( $args['type'] ) ?>]" value="<?php echo esc_attr( $settings[$args['type']] ) ?>" class="small-text" /> <?php _e( 'px', 'siteorigin-panels' ) ?><?php
			break;
	}

	if ( ! empty( $args['description'] ) ) {
		?><p class="description"><?php echo esc_html( $args['description'] ) ?></p><?php
	}
}

/**
 * Display the fields for the other settings.
 *
 * @param $args
 */
function siteorigin_panels_options_field_display( $args ) {
	siteorigin_panels_options_field_generic( $args, 'siteorigin_panels_display' );
}

/**
 * Check that we have valid post types
 *
 * @param $types
 * @return array
 */
function siteorigin_panels_options_sanitize_post_types( $types ) {
	if ( empty( $types ) ) return array();
	$all_post_types = get_post_types( array( '_builtin' => false ) );
	$all_post_types = array_merge( array( 'post' => 'post', 'page' => 'page' ), $all_post_types );
	foreach( $types as $type => $val ) {
		if ( ! in_array( $type, $all_post_types ) ) unset( $types[$type] );
		else $types[$type] = ! empty( $types[$type] );
	}
	
	// Only non empty items
	return array_keys( array_filter( $types ) );
}

/**
 * Sanitize the other options fields
 *
 * @param $vals
 * @return mixed
 */

function siteorigin_panels_options_sanitize_general( $vals ) {
	foreach( $vals as $f => $v ) {
		switch( $f ) {
			case 'copy-content' :
			case 'animations' :
				$vals[$f] = ! empty( $vals[$f] );
				break;
		}
	}

	$vals['copy-content'] = ! empty( $vals['copy-content'] );
	$vals['animations'] = ! empty( $vals['animations'] );

	return $vals;
}

function siteorigin_panels_options_sanitize_display( $vals ) {
	foreach( $vals as $f => $v ) {
		switch( $f ) {
			case 'remove-list-padding' :
			case 'responsive' :
			case 'bundled-widgets' :
				$vals[$f] = ! empty( $vals[$f] );
				break;
			case 'margin-bottom' :
			case 'margin-sides' :
			case 'mobile-width' :
				$vals[$f] = intval( $vals[$f] );
				break;
		}
	}
	$vals['copy-content'] = false;
	$vals['animations'] = true;
	$vals['inline-css'] = true;
	$vals['responsive'] = ! empty( $vals['responsive'] );
	$vals['remove-list-padding'] = ! empty( $vals['remove-list-padding'] );
	$vals['bundled-widgets'] = ! empty( $vals['bundled-widgets'] );
	return $vals;
}