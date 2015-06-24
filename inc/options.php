<?php

/**
 * Register's the settings
 */
require_once( 'settings.php' );

/**
 * Get the settings
 *
 * @param string $key Only get a specific key.
 *
 * @return mixed
 */
function siteorigin_panels_setting( $key = '' ) {

	if ( has_action( 'after_setup_theme' ) ) {
		// Only use static settings if we've initialized the theme
		static $settings;
	} else {
		$settings = false;
	}

	if ( empty( $settings ) ) {
		$display_settings = get_option( 'siteorigin_panels_display', array() );

		$settings = get_theme_support( 'ppb-panels' );
		if ( ! empty( $settings ) ) {
			$settings = $settings[0];
		} else {
			$settings = array();
		}


		$settings = wp_parse_args( $settings, array(
			'home-page'         => false,
			// Is the home page supported
			'home-page-default' => false,
			// What's the default layout for the home page?
			'home-template'     => 'home-panels.php',
			// The file used to render a home page.
			'post-types'        => get_option( 'pootle_page_builder_post_types', array( 'page' ) ),
			// Post types that can be edited using panels.

			'responsive'        => ! isset( $display_settings['responsive'] ) ? true : $display_settings['responsive'] == '1',
			// Should we use a responsive layout
			'mobile-width'      => ! isset( $display_settings['mobile-width'] ) ? 780 : $display_settings['mobile-width'],
			// What is considered a mobile width?

			'margin-bottom'     => ! isset( $display_settings['margin-bottom'] ) ? 0 : $display_settings['margin-bottom'],
			// Bottom margin of a cell
			'margin-sides'      => ! isset( $display_settings['margin-sides'] ) ? 10 : $display_settings['margin-sides'],
			// Spacing between 2 cells
			'affiliate-id'      => false,
			// Set your affiliate ID
			'copy-content'      => '',
			// Should we copy across content
			'animations'        => true,
			// We want animations always enabled
			'inline-css'        => true,
			// How to display CSS
		) );

		// Filter these settings
		$settings = apply_filters( 'siteorigin_panels_settings', $settings );
		if ( empty( $settings['post-types'] ) ) {
			$settings['post-types'] = array();
		}
	}

	if ( ! empty( $key ) ) {
		return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
	}

	return $settings;
}

function pootlepage_options_page_styling() {
	$customizeUrl = admin_url( 'customize.php' );
	echo "<p>Folio uses the WordPress customizer to allow you to style your widgets and preview them easily. Click <a href='" . esc_attr( $customizeUrl ) . "'>here</a> to go to these settings.</p>";
}

function siteorigin_panels_options_field_generic( $args, $groupName ) {
	$settings = siteorigin_panels_setting();
	switch ( $args['type'] ) {
		case 'responsive' :
		case 'bundled-widgets' :
			?><label><input type="checkbox"
			                name="<?php echo $groupName ?>[<?php echo esc_attr( $args['type'] ) ?>]" <?php checked( $settings[ $args['type'] ] ) ?>
			                value="1"/> <?php _e( 'Enabled', 'ppb-panels' ) ?></label><?php
			break;
		case 'margin-bottom' :
		case 'margin-sides' :
		case 'mobile-width' :
			?><input type="text" name="<?php echo $groupName ?>[<?php echo esc_attr( $args['type'] ) ?>]"
			         value="<?php echo esc_attr( $settings[ $args['type'] ] ) ?>"
			         class="small-text" /> <?php _e( 'px', 'ppb-panels' ) ?><?php
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
 *
 * @return array
 */
function siteorigin_panels_options_sanitize_post_types( $types ) {
	if ( empty( $types ) ) {
		return array();
	}
	$all_post_types = get_post_types( array( '_builtin' => false ) );
	$all_post_types = array_merge( array( 'post' => 'post', 'page' => 'page' ), $all_post_types );
	foreach ( $types as $type => $val ) {
		if ( ! in_array( $type, $all_post_types ) ) {
			unset( $types[ $type ] );
		} else {
			$types[ $type ] = ! empty( $types[ $type ] );
		}
	}

	// Only non empty items
	return array_keys( array_filter( $types ) );
}

/**
 * Sanitize the other options fields
 *
 * @param $vals
 *
 * @return mixed
 */

function siteorigin_panels_options_sanitize_general( $vals ) {
	foreach ( $vals as $f => $v ) {
		switch ( $f ) {
			case 'copy-content' :
			case 'animations' :
				$vals[ $f ] = ! empty( $vals[ $f ] );
				break;
		}
	}

	$vals['copy-content'] = ! empty( $vals['copy-content'] );
	$vals['animations']   = ! empty( $vals['animations'] );

	return $vals;
}

function siteorigin_panels_options_sanitize_display( $vals ) {
	foreach ( $vals as $f => $v ) {
		switch ( $f ) {
			case 'responsive' :
			case 'bundled-widgets' :
				$vals[ $f ] = ! empty( $vals[ $f ] );
				break;
			case 'margin-bottom' :
			case 'margin-sides' :
			case 'mobile-width' :
				$vals[ $f ] = intval( $vals[ $f ] );
				break;
		}
	}
	$vals['copy-content']    = false;
	$vals['animations']      = true;
	$vals['inline-css']      = true;
	$vals['responsive']      = ! empty( $vals['responsive'] );
	$vals['bundled-widgets'] = ! empty( $vals['bundled-widgets'] );

	return $vals;
}