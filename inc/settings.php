<?php
/**
 * Created by PhpStorm.
 * User: shramee
 * Date: 24/6/15
 * Time: 9:35 PM
 */

/**
 * Add the options page
 */
function siteorigin_panels_options_admin_menu() {
	add_menu_page( 'Home', 'Page Builder', 'manage_options', 'page_builder', 'pootle_page_menu_page', 'dashicons-grid-view', 26 );
	add_submenu_page( 'page_builder', 'Add New', 'Add New', 'manage_options', 'page_builder_add', 'pootle_page_submenu_page' );
	add_submenu_page( 'page_builder', 'Settings', 'Settings', 'manage_options', 'page_builder_settings', 'pootle_page_submenu_page' );
	add_submenu_page( 'page_builder', 'Add-ons', 'Add-ons', 'manage_options', 'page_builder_addons', 'pootle_page_submenu_page' );
}
add_action( 'admin_menu', 'siteorigin_panels_options_admin_menu' );

/**
 * Register all the settings fields.
 */
function siteorigin_panels_options_init() {
	register_setting( 'pootlepage-add-ons', 'pootlepage_add_ons', '__return_false' );
	register_setting( 'pootlepage-display', 'siteorigin_panels_display', 'siteorigin_panels_options_sanitize_display' );

	add_settings_section( 'display', __( 'Display', 'ppb-panels' ), '__return_false', 'pootlepage-display' );

	// The display fields
	add_settings_field( 'responsive', __( 'Responsive', 'ppb-panels' ), 'siteorigin_panels_options_field_display', 'pootlepage-display', 'display', array( 'type' => 'responsive' ) );
	add_settings_field( 'mobile-width', __( 'Mobile Width', 'ppb-panels' ), 'siteorigin_panels_options_field_display', 'pootlepage-display', 'display', array( 'type' => 'mobile-width' ) );
}

add_action( 'admin_init', 'siteorigin_panels_options_init' );

/**
 * Display the admin page.
 */
function pootle_page_menu_page() {
	include plugin_dir_path( POOTLEPAGE_BASE_FILE ) . '/tpl/welcome.php';
}

/**
 * Display the admin page.
 */
function pootle_page_submenu_page() {
	if ( 'page_builder_settings' == filter_input( INPUT_GET, 'page' ) ) {

		include plugin_dir_path( POOTLEPAGE_BASE_FILE ) . '/tpl/options.php';

	} elseif ( 'page_builder_addons' == filter_input( INPUT_GET, 'page' ) ) {

		include plugin_dir_path( POOTLEPAGE_BASE_FILE ) . '/tpl/add-ons.php';

	} elseif ( 'page_builder_add' == filter_input( INPUT_GET, 'page' ) ) {

	?>
		<div class="wrap">
			<h2 class="page_builder_add">If you are not automatically redirected. <a href="<?php echo admin_url( '/post-new.php?post_type=page&page_builder=pootle' ); ?>"> Click Here to Create New page with Pootle Page Builder.</a><h2>
		</div>
	<?php

	}
}

/**
 * Redirecting for Page Builder > Add New option
 */

if ( 'admin.php' == $pagenow && 'page_builder_add' == filter_input( INPUT_GET, 'page' ) ) {
	header( 'Location: ' . admin_url( '/post-new.php?post_type=page&page_builder=pootle' ) );
	die();
}