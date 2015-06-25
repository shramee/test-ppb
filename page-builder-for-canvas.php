<?php
/*
Plugin Name: Pootle Page Builder
Plugin URI: http://pootlepress.com/
Description: A page builder for WooThemes Canvas.
Version: 2.9.9
Author: PootlePress
Author URI: http://pootlepress.com/
License: GPL version 3
*/


define( 'POOTLEPAGE_VERSION', '2.9.9' );
define( 'POOTLEPAGE_BASE_FILE', __FILE__ );
define( 'POOTLEPAGE_DIR', __DIR__ );
define( 'POOTLEPAGE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Tracking presence of version older than 3.0.0
 */
if ( - 1 == version_compare( get_option( 'siteorigin_panels_initial_version' ), '2.5' ) ) {
	define( 'POOTLEPAGE_OLD_V', get_option( 'siteorigin_panels_initial_version' ) );
}

//Solving old version post types
add_action( 'admin_init', 'pp_pb_version_check' );
/**
 * Checks if older version of Page Builder was being used on site
 * Then runs compatibility functions accordingly
 * @since 3.0.0
 */
function pp_pb_version_check() {

	//Get initial version
	$initial_version = get_option( 'siteorigin_panels_initial_version', POOTLEPAGE_VERSION );

	if ( POOTLEPAGE_VERSION != get_option( 'pootle_page_builder_version' ) ) {

		//If initial version < Current version
		if ( - 1 == version_compare( $initial_version, POOTLEPAGE_VERSION ) ) {

			//Sort compatibility issues
			require_once 'inc/class-pootle-page-compatibility.php';
			new Pootle_Page_Compatibility();
		}

		//Update current version
		update_option( 'pootle_page_builder_version', POOTLEPAGE_VERSION );

	}
}

function pp_pb_check_for_conflict() {
	if ( is_plugin_active( 'wx-pootle-text-widget/pootlepress-text-widget.php' ) ||
	     is_plugin_active( 'pootle-text-widget-master/pootlepress-text-widget.php' )
	) {

		$pluginFile = __FILE__;
		$plugin     = plugin_basename( $pluginFile );
		if ( is_plugin_active( $plugin ) ) {
			deactivate_plugins( $plugin );
			wp_die( "ERROR: <strong>Page Builder</strong> cannot be activated if Pootle Text Widget is also activated. " .
			        "Page Builder is unable to continue and has been deactivated. " .
			        "<br /><br />Back to the WordPress <a href='" . get_admin_url( null, 'plugins.php' ) . "'>Plugins page</a>." );
		}
	}
}

require_once 'inc/class-render-layout.php';
require_once 'inc/class-content-blocks.php';
require_once 'inc/class-front-css-js.php';
require_once 'inc/cxpb-styles.php';

require_once 'page-builder-for-canvas-functions.php';
require_once 'widgets/basic.php';

require_once 'inc/vars.php';
require_once 'inc/options.php';
require_once 'inc/revisions.php';
require_once 'inc/copy.php';
require_once 'inc/styles.php';
require_once 'inc/legacy.php';
require_once 'inc/notice.php';
require_once 'inc/vantage-extra.php';
require_once 'inc/class-pootlepress-updater.php';
require_once 'inc/class-pootlepage-font-utility.php';
if ( defined( 'SITEORIGIN_PANELS_DEV' ) && SITEORIGIN_PANELS_DEV ) {
	include plugin_dir_path( __FILE__ ) . 'inc/debug.php';
}

/**
 * Hook for activation of Page Builder.
 */
function siteorigin_panels_activate() {
	add_option( 'siteorigin_panels_initial_version', POOTLEPAGE_VERSION, '', 'no' );

	$current_user = wp_get_current_user();

	//Get first name if set
	$username = '';
	if ( ! empty( $current_user->user_firstname ) ) {
		$username = " {$current_user->user_firstname}";
	}

	$welcome_message = "<b>Hey{$username}! Welcome to Page builder.</b> You're all set to start building stunning pages!<br><a class='button pootle' href='" . admin_url( '/admin.php?page=page_builder_home' ) . "'>Get started</a>";

	ppb_add_admin_notice( 'welcome', $welcome_message, 'updated pootle' );
}

register_activation_hook( __FILE__, 'siteorigin_panels_activate' );

/**
 * Initialize the Page Builder.
 */
function siteorigin_panels_init() {
	$display_settings = get_option( 'siteorigin_panels_display', array() );
	if ( isset( $display_settings['bundled-widgets'] ) && ! $display_settings['bundled-widgets'] ) {
		return;
	}
}

add_action( 'plugins_loaded', 'siteorigin_panels_init' );

/**
 * Initialize the language files
 */
function siteorigin_panels_init_lang() {
	load_plugin_textdomain( 'ppb-panels', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}

add_action( 'plugins_loaded', 'siteorigin_panels_init_lang' );

/**
 * Add the admin menu entries
 */
function siteorigin_panels_admin_menu() {
	if ( ! siteorigin_panels_setting( 'home-page' ) ) {
		return;
	}

	add_theme_page(
		__( 'Custom Home Page Builder', 'ppb-panels' ),
		__( 'Home Page', 'ppb-panels' ),
		'edit_theme_options',
		'so_panels_home_page',
		'siteorigin_panels_render_admin_home_page'
	);
}

add_action( 'admin_menu', 'siteorigin_panels_admin_menu' );

/**
 * Render the page used to build the custom home page.
 */
function siteorigin_panels_render_admin_home_page() {
	add_meta_box( 'so-panels-panels', __( 'Page Builder', 'ppb-panels' ), 'siteorigin_panels_metabox_render', 'appearance_page_so_panels_home_page', 'advanced', 'high' );
	include plugin_dir_path( __FILE__ ) . 'tpl/admin-home-page.php';
}

/**
 * Callback to register the Panels Metaboxes
 */
function siteorigin_panels_metaboxes() {
	foreach ( siteorigin_panels_setting( 'post-types' ) as $type ) {
		add_meta_box( 'so-panels-panels', __( 'Page Builder', 'ppb-panels' ), 'siteorigin_panels_metabox_render', $type, 'advanced', 'high' );
	}
}

add_action( 'add_meta_boxes', 'siteorigin_panels_metaboxes' );

/**
 * Save home page
 */
function siteorigin_panels_save_home_page() {
	if ( ! isset( $_POST['_sopanels_home_nonce'] ) || ! wp_verify_nonce( $_POST['_sopanels_home_nonce'], 'save' ) ) {
		return;
	}
	if ( empty( $_POST['panels_js_complete'] ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	update_option( 'siteorigin_panels_home_page', siteorigin_panels_get_panels_data_from_post( $_POST ) );
	update_option( 'siteorigin_panels_home_page_enabled', $_POST['siteorigin_panels_home_enabled'] == 'true' ? true : '' );

	// If we've enabled the panels home page, change show_on_front to posts, this is required for the home page to work properly
	if ( $_POST['siteorigin_panels_home_enabled'] == 'true' ) {
		update_option( 'show_on_front', 'posts' );
	}
}

add_action( 'admin_init', 'siteorigin_panels_save_home_page' );

/**
 * If this is the main query, store that we're accessing the front page
 *
 * @param $wp_query
 */
function siteorigin_panels_render_home_page_prepare( $wp_query ) {
	if ( ! $wp_query->is_main_query() ) {
		return;
	}
	if ( ! get_option( 'siteorigin_panels_home_page_enabled', siteorigin_panels_setting( 'home-page-default' ) ) ) {
		return;
	}

	$GLOBALS['siteorigin_panels_is_home'] = @ $wp_query->is_front_page();
}

add_action( 'pre_get_posts', 'siteorigin_panels_render_home_page_prepare' );

/**
 * This fixes a rare case where pagination for a home page loop extends further than post pagination.
 */
function siteorigin_panels_render_home_page() {
	if (
		empty( $GLOBALS['siteorigin_panels_is_home'] ) ||
		! is_404() ||
		! get_option( 'siteorigin_panels_home_page_enabled', siteorigin_panels_setting( 'home-page-default' ) )
	) {
		return;
	}

	// This query was for the home page, but because of pagination we're getting a 404
	// Create a fake query so the home page keeps working with the post loop widget
	$paged = get_query_var( 'paged' );
	if ( empty( $paged ) ) {
		return;
	}

	query_posts( array() );
	set_query_var( 'paged', $paged );

	// Make this query the main one
	$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
	status_header( 200 ); // Overwrite the 404 header we set earlier.
}

add_action( 'template_redirect', 'siteorigin_panels_render_home_page' );

/**
 * @return mixed|void Are we currently viewing the home page
 */
function siteorigin_panels_is_home() {
	$home = ( is_home() && get_option( 'siteorigin_panels_home_page_enabled', siteorigin_panels_setting( 'home-page-default' ) ) );

	return apply_filters( 'siteorigin_panels_is_home', $home );
}

/**
 * Disable home page panels when we change show_on_front to something other than posts.
 *
 * @param $old
 * @param $new
 *
 * @action update_option_show_on_front
 */
function siteorigin_panels_disable_on_front_page_change( $old, $new ) {
	if ( $new != 'posts' ) {
		// Disable panels home page
		update_option( 'siteorigin_panels_home_page_enabled', '' );
	}
}

add_action( 'update_option_show_on_front', 'siteorigin_panels_disable_on_front_page_change', 10, 2 );


/**
 * Check if we're currently viewing a panel.
 *
 * @param bool $can_edit Also check if the user can edit this page
 *
 * @return bool
 */
function ppb_is_panel( $can_edit = false ) {
	// Check if this is a panel
	$is_panel = ( is_singular() && get_post_meta( get_the_ID(), 'panels_data', false ) != '' );

	return $is_panel && ( ! $can_edit || ( ( is_singular() && current_user_can( 'edit_post', get_the_ID() ) ) || ( siteorigin_panels_is_home() && current_user_can( 'edit_theme_options' ) ) ) );
}

/**
 * Render a panel metabox.
 *
 * @param $post
 */
function siteorigin_panels_metabox_render( $post ) {
	include plugin_dir_path( __FILE__ ) . 'tpl/metabox-panels.php';
}


/**
 * Enqueue the panels admin scripts
 *
 * @action admin_print_scripts-post-new.php
 * @action admin_print_scripts-post.php
 * @action admin_print_scripts-appearance_page_so_panels_home_page
 */
function siteorigin_panels_admin_enqueue_scripts( $prefix ) {
	$screen = get_current_screen();

	if ( ( $screen->base == 'post' && in_array( $screen->id, siteorigin_panels_setting( 'post-types' ) ) ) || $screen->base == 'appearance_page_so_panels_home_page' ) {
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-button' );

		wp_enqueue_script( 'so-undomanager', plugin_dir_url( __FILE__ ) . 'js/undomanager.min.js', array(), 'fb30d7f' );

		// check if "chosen" is already used, e.g. by WooCommerce
		if ( ! wp_script_is( 'chosen' ) ) {
			wp_enqueue_script( 'so-panels-chosen', plugin_dir_url( __FILE__ ) . 'js/chosen/chosen.jquery.min.min.js', array( 'jquery' ), POOTLEPAGE_VERSION );
		}

		wp_enqueue_script( 'so-panels-admin', plugin_dir_url( __FILE__ ) . 'js/panels.admin.js', array( 'jquery' ), POOTLEPAGE_VERSION );
		wp_enqueue_script( 'so-sticky-admin-panels', plugin_dir_url( __FILE__ ) . 'js/sticky.admin.panels.js', array( 'jquery' ), POOTLEPAGE_VERSION );
		wp_enqueue_script( 'so-panels-admin-panels', plugin_dir_url( __FILE__ ) . 'js/panels.admin.panels.js', array(
			'jquery',
			'jquery-ui-tabs'
		), POOTLEPAGE_VERSION );
		wp_enqueue_script( 'so-panels-admin-grid', plugin_dir_url( __FILE__ ) . 'js/panels.admin.grid.js', array( 'jquery' ), POOTLEPAGE_VERSION );
		wp_enqueue_script( 'so-panels-admin-prebuilt', plugin_dir_url( __FILE__ ) . 'js/panels.admin.prebuilt.js', array( 'jquery' ), POOTLEPAGE_VERSION );
		wp_enqueue_script( 'so-panels-admin-tooltip', plugin_dir_url( __FILE__ ) . 'js/panels.admin.tooltip.min.js', array( 'jquery' ), POOTLEPAGE_VERSION );
		wp_enqueue_script( 'so-panels-admin-media', plugin_dir_url( __FILE__ ) . 'js/panels.admin.media.min.js', array( 'jquery' ), POOTLEPAGE_VERSION );
		wp_enqueue_script( 'so-panels-admin-styles', plugin_dir_url( __FILE__ ) . 'js/panels.admin.styles.js', array( 'jquery', 'jquery-ui-slider' ), POOTLEPAGE_VERSION );

		wp_enqueue_script( 'row-options', plugin_dir_url( __FILE__ ) . 'js/row.options.admin.js', array( 'jquery' ) );

		wp_localize_script( 'so-panels-admin', 'panels', array(
			'previewUrl' => wp_nonce_url( add_query_arg( 'siteorigin_panels_preview', 'true', get_home_url() ), 'ppb-panels-preview' ),
			'i10n'       => array(
				'buttons'  => array(
					'insert'    => __( 'Insert', 'ppb-panels' ),
					'cancel'    => __( 'cancel', 'ppb-panels' ),
					'delete'    => __( 'Delete', 'ppb-panels' ),
					'duplicate' => __( 'Duplicate', 'ppb-panels' ),
					'style'     => __( 'Style', 'ppb-panels' ),
					'edit'      => __( 'Edit', 'ppb-panels' ),
					'done'      => __( 'Done', 'ppb-panels' ),
					'undo'      => __( 'Undo', 'ppb-panels' ),
					'add'       => __( 'Add', 'ppb-panels' ),
				),
				'messages' => array(
					'deleteColumns' => __( 'Columns deleted', 'ppb-panels' ),
					'deleteWidget'  => __( 'Widget deleted', 'ppb-panels' ),
					'confirmLayout' => __( 'Are you sure you want to load this layout? It will overwrite your current page.', 'ppb-panels' ),
					'editWidget'    => __( 'Edit %s Widget', 'ppb-panels' ),
					'styleWidget'   => __( 'Style Widget', 'ppb-panels' )
				),
			),
		) );

		// this is the data of the widget and row that have been setup
		$panels_data = siteorigin_panels_get_current_admin_panels_data();

		// Remove any widgets with classes that don't exist
		if ( ! empty( $panels_data['panels'] ) ) {
			foreach ( $panels_data['panels'] as $i => $panel ) {
				if ( ! class_exists( $panel['info']['class'] ) ) {
					unset( $panels_data['panels'][ $i ] );
				}
			}
		}

		// Add in the forms
		if ( count( $panels_data ) > 0 ) {

			foreach ( $panels_data['widgets'] as $i => $widget ) {

				if ( ! empty( $widget['info']['class'] ) && ! class_exists( $widget['info']['class'] ) ) {
					unset( $panels_data['widgets'][ $i ] );
				}
			}

			// load all data even if no widget inside, so row styling will be loaded
			wp_localize_script( 'so-panels-admin', 'panelsData', $panels_data );
		}

		// Set up the row styles
		wp_localize_script( 'so-panels-admin', 'panelsStyleFields', siteorigin_panels_style_get_fields() );

		pootle_page_enqueue_color_picker();

		wp_localize_script( 'pp-pb-color-picker', 'wpColorPickerL10n', array(
			'clear'         => __( 'Clear' ),
			'defaultString' => __( 'Default' ),
			'pick'          => __( 'Select Color' ),
			'current'       => __( 'Current Color' ),
		) );

		wp_enqueue_style( 'wp-color-picker' );

		// Render all the widget forms. A lot of widgets use this as a chance to enqueue their scripts
		$original_post = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null; // Make sure widgets don't change the global post.
		foreach ( $GLOBALS['wp_widget_factory']->widgets as $class => $widget_obj ) {
			ob_start();
			$widget_obj->form( array() );
			ob_clean();
		}
		$GLOBALS['post'] = $original_post;

		// handle a special case for Event Calendar Pro,
		// since it doesn't enqueue script if not in Widgets page
		if ( class_exists( 'TribeEventsMiniCalendarWidget' ) ) {
			Tribe_Template_Factory::asset_package( 'select2' );
			//wp_enqueue_script( 'calendar-widget-admin',  plugin_dir_url( __FILE__ ) . '/js/calendar-widget-admin.js' );
		}

		// This gives panels a chance to enqueue scripts too, without having to check the screen ID.
		do_action( 'siteorigin_panel_enqueue_admin_scripts' );
		do_action( 'sidebar_admin_setup' );
	}
}

add_action( 'admin_print_scripts-post-new.php', 'siteorigin_panels_admin_enqueue_scripts' );
add_action( 'admin_print_scripts-post.php', 'siteorigin_panels_admin_enqueue_scripts' );
add_action( 'admin_print_scripts-appearance_page_so_panels_home_page', 'siteorigin_panels_admin_enqueue_scripts' );

/**
 * Enqueue script for custom customize control.
 */
function pootlepage_customize_enqueue() {
	wp_enqueue_style( 'pootlepage-customize-styles', plugin_dir_url( __FILE__ ) . '/css/customize-controls.css' );
}

add_action( 'customize_controls_enqueue_scripts', 'pootlepage_customize_enqueue' );

function pootle_page_enqueue_color_picker() {

	wp_dequeue_script( "iris" );
	wp_enqueue_script( "pp-pb-iris", plugin_dir_url( __FILE__ ) . '/js/iris.js', array(
		'jquery-ui-draggable',
		'jquery-ui-slider',
		'jquery-touch-punch'
	) );
	wp_enqueue_script( 'pp-pb-color-picker', plugin_dir_url( __FILE__ ) . '/js/color-picker-custom.js', array( 'pp-pb-iris' ) );

}

/**
 * Enqueue the admin panel styles
 *
 * @action admin_print_styles-post-new.php
 * @action admin_print_styles-post.php
 */
function siteorigin_panels_admin_enqueue_styles() {
	$screen = get_current_screen();
	if ( in_array( $screen->id, siteorigin_panels_setting( 'post-types' ) ) || $screen->base == 'appearance_page_so_panels_home_page' ) {
		wp_enqueue_style( 'so-panels-admin', plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), POOTLEPAGE_VERSION );

		global $wp_version;
		if ( version_compare( $wp_version, '3.9.beta.1', '<' ) ) {
			// Versions before 3.9 need some custom jQuery UI styling
			wp_enqueue_style( 'so-panels-admin-jquery-ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css', array(), POOTLEPAGE_VERSION );
		} else {
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
		}

		wp_enqueue_style( 'so-panels-chosen', plugin_dir_url( __FILE__ ) . 'js/chosen/chosen.css', array(), POOTLEPAGE_VERSION );
		do_action( 'siteorigin_panel_enqueue_admin_styles' );
	}
}

add_action( 'admin_print_styles-post-new.php', 'siteorigin_panels_admin_enqueue_styles' );
add_action( 'admin_print_styles-post.php', 'siteorigin_panels_admin_enqueue_styles' );
add_action( 'admin_print_styles-appearance_page_so_panels_home_page', 'siteorigin_panels_admin_enqueue_styles' );

function pootlepage_option_page_enqueue() {

	wp_enqueue_style( 'pootlepage-main-admin', plugin_dir_url( __FILE__ ) . 'css/main-admin.css', array(), POOTLEPAGE_VERSION );
	global $pagenow;
	if ( $pagenow == 'admin.php' && false !== strpos( filter_input( INPUT_GET, 'page' ), 'page_builder' ) ) {

		wp_enqueue_script( 'ppb-settings-script', plugin_dir_url( __FILE__ ) . 'js/settings.js', array() );
		wp_enqueue_style( 'ppb-settings-styles', plugin_dir_url( __FILE__ ) . 'css/settings.css', array() );
		wp_enqueue_style( 'pootlepage-option-admin', plugin_dir_url( __FILE__ ) . 'css/option-admin.css', array(), POOTLEPAGE_VERSION );
		wp_enqueue_script( 'pootlepage-option-admin', plugin_dir_url( __FILE__ ) . 'js/option-admin.js', array( 'jquery' ), POOTLEPAGE_VERSION );
	}
}

add_action( 'admin_enqueue_scripts', 'pootlepage_option_page_enqueue' );

/**
 * Add a help tab to pages with panels.
 */
function ppb_panels_add_help_tab( $prefix ) {
	$screen = get_current_screen();
	if ( $screen->base == 'post' && in_array( $screen->id, siteorigin_panels_setting( 'post-types' ) ) ) {
		$screen->add_help_tab( array(
			'id'       => 'panels-help-tab', //unique id for the tab
			'title'    => __( 'Page Builder', 'ppb-panels' ), //unique visible title for the tab
			'callback' => 'ppb_panels_help_tab'
		) );
	}
}
add_action( 'load-page.php', 'ppb_panels_add_help_tab', 12 );
add_action( 'load-post-new.php', 'ppb_panels_add_help_tab', 12 );

/**
 * Display the content for the help tab.
 */
function ppb_panels_help_tab() {
	include POOTLEPAGE_DIR . 'tpl/help.php';
}

/**
 * Save the panels data
 *
 * @param $post_id
 * @param $post
 *
 * @action save_post
 */
function siteorigin_panels_save_post( $post_id, $post ) {
	if ( empty( $_POST['_sopanels_nonce'] ) || ! wp_verify_nonce( $_POST['_sopanels_nonce'], 'save' ) ) {
		return;
	}
	if ( empty( $_POST['panels_js_complete'] ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	// Don't Save panels if Post Type for $post_id is not same as current post ID type
	// (Prevents population product panels data in saving Tabs via Meta)
	if ( get_post_type( $_POST['post_ID'] ) != 'wc_product_tab' and get_post_type( $post_id ) == 'wc_product_tab' ) {
		return;
	}

	$panels_data = siteorigin_panels_get_panels_data_from_post( $_POST );

	if ( function_exists( 'wp_slash' ) ) {
		$panels_data = wp_slash( $panels_data );
	}
	update_post_meta( $post_id, 'panels_data', $panels_data );
}

add_action( 'save_post', 'siteorigin_panels_save_post', 10, 2 );

/**
 * Get the home page panels layout data.
 *
 * @return mixed|void
 */
function siteorigin_panels_get_home_page_data() {
	$panels_data = get_option( 'siteorigin_panels_home_page', null );
	if ( is_null( $panels_data ) ) {
		// Load the default layout
		$layouts     = apply_filters( 'siteorigin_panels_prebuilt_layouts', array() );
		$panels_data = ! empty( $layouts['default_home'] ) ? $layouts['default_home'] : current( $layouts );
	}

	return $panels_data;
}

/**
 * Get the Page Builder data for the current admin page.
 *
 * @return array
 */
function siteorigin_panels_get_current_admin_panels_data() {
	$screen = get_current_screen();

	global $post;
	$panels_data = get_post_meta( $post->ID, 'panels_data', true );
	$panels_data = apply_filters( 'siteorigin_panels_data', $panels_data, $post->ID );

	if ( empty( $panels_data ) ) {
		$panels_data = array();
	}

	//Set default styles if none
	if ( isset( $panels_data['widgets'] ) ) {
		foreach ( $panels_data['widgets'] as &$widget ) {
			if ( isset( $widget['info'] ) ) {
				if ( ! isset( $widget['info']['style'] ) ) {
					$widget['info']['style'] = ppb_default_content_block_style();
				}
			}
		}
	}


	return $panels_data;
}

function ppb_default_content_block_style() {
	$widgetStyleFields = pp_pb_widget_styling_fields();

	$result = array();
	foreach ( $widgetStyleFields as $key => $field ) {
		if ( $field['type'] == 'border' ) {
			$result[ $key . '-width' ] = 0;
			$result[ $key . '-color' ] = '';
		} elseif ( $field['type'] == 'number' ) {
			$result[ $key ] = 0;
		} elseif ( $field['type'] == 'checkbox' ) {
			$result[ $key ] = '';
		} else {
			$result[ $key ] = '';
		}
	}

	return $result;

}

add_action( 'after_setup_theme', 'pootlepage_after_setup_theme' );

function pootlepage_after_setup_theme() {
	if ( class_exists( 'WF' ) && class_exists( 'WF_Meta' ) ) {
		add_action( 'admin_print_scripts', 'pootlepage_fix_framework_js_error' );
	}
}

function pootlepage_fix_framework_js_error() {
	echo "<script>var wooSelectedShortcodeType = typeof wooSelectedShortcodeType == 'undefined' ? '' : wooSelectedShortcodeType;</script>\n";
}

/**
 * Handles creating the preview.
 */
function siteorigin_panels_preview() {
	if ( isset( $_GET['siteorigin_panels_preview'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ppb-panels-preview' ) ) {
		global $siteorigin_panels_is_preview;
		$siteorigin_panels_is_preview = true;
		// Set the panels home state to true
		$post_id = filter_input( INPUT_POST, 'post_id' );
		if ( empty( $post_id ) ) {
			$GLOBALS['siteorigin_panels_is_panels_home'] = true;
		}
		add_action( 'option_siteorigin_panels_home_page', 'siteorigin_panels_preview_load_data' );
		locate_template( siteorigin_panels_setting( 'home-template' ), true );
		exit();
	}
}

add_action( 'template_redirect', 'siteorigin_panels_preview' );

/**
 * Is this a preview.
 *
 * @return bool
 */
function siteorigin_panels_is_preview() {
	global $siteorigin_panels_is_preview;

	return (bool) $siteorigin_panels_is_preview;
}

/**
 * Hide the admin bar for panels previews.
 *
 * @param $show
 *
 * @return bool
 */
function siteorigin_panels_preview_adminbar( $show ) {
	if ( ! $show ) {
		return false;
	}

	return ! ( isset( $_GET['siteorigin_panels_preview'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ppb-panels-preview' ) );
}

add_filter( 'show_admin_bar', 'siteorigin_panels_preview_adminbar' );

/**
 * This is a way to show previews of panels, especially for the home page.
 *
 * @param $val
 *
 * @return array
 */
function siteorigin_panels_preview_load_data( $val ) {
	if ( isset( $_GET['siteorigin_panels_preview'] ) ) {

		$val = siteorigin_panels_get_panels_data_from_post( $_POST );
	}

	return $val;
}

/**
 * Add all the necessary body classes.
 *
 * @param $classes
 *
 * @return array
 */
function siteorigin_panels_body_class( $classes ) {

	if ( ppb_is_panel() ) {
		$classes[] = 'ppb-panels';
	}

	return $classes;
}

add_filter( 'body_class', 'siteorigin_panels_body_class' );

/**
 * Add current pages as cloneable pages
 *
 * // register this to override canvas script
 * @param $layouts
 *
 * @return mixed
 */
function siteorigin_panels_cloned_page_layouts( $layouts ) {
	$pages = get_posts( array(
		'post_type'   => 'page',
		'post_status' => array( 'publish', 'draft' ),
		'numberposts' => 200,
	) );

	foreach ( $pages as $page ) {
		$panels_data = get_post_meta( $page->ID, 'panels_data', true );
		$panels_data = apply_filters( 'siteorigin_panels_data', $panels_data, $page->ID );

		if ( empty( $panels_data ) ) {
			continue;
		}

		$name = empty( $page->post_title ) ? __( 'Untitled', 'ppb-panels' ) : $page->post_title;
		if ( $page->post_status != 'publish' ) {
			$name .= ' ( ' . __( 'Unpublished', 'ppb-panels' ) . ' )';
		}

		if ( current_user_can( 'edit_post', $page->ID ) ) {
			$layouts[ 'post-' . $page->ID ] = wp_parse_args(
				array(
					'name' => sprintf( __( 'Clone Page: %s', 'ppb-panels' ), $name )
				),
				$panels_data
			);
		}
	}

	// Include the current home page in the clone pages.
	$home_data = get_option( 'siteorigin_panels_home_page', null );
	if ( ! empty( $home_data ) ) {

		$layouts['current-home-page'] = wp_parse_args(
			array(
				'name' => __( 'Clone: Current Home Page', 'ppb-panels' ),
			),
			$home_data
		);
	}

	return $layouts;
}

add_filter( 'siteorigin_panels_prebuilt_layouts', 'siteorigin_panels_cloned_page_layouts', 20 );

/**
 * Add a filter to import panels_data meta key. This fixes serialized PHP.
 */
function siteorigin_panels_wp_import_post_meta( $post_meta ) {
	foreach ( $post_meta as $i => $meta ) {
		if ( $meta['key'] == 'panels_data' ) {
			$value = $meta['value'];
			$value = preg_replace( "/[\r\n]/", "<<<br>>>", $value );
			$value = preg_replace( '!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $value );
			$value = unserialize( $value );
			$value = array_map( 'siteorigin_panels_wp_import_post_meta_map', $value );

			$post_meta[ $i ]['value'] = $value;
		}
	}

	return $post_meta;
}

add_filter( 'wp_import_post_meta', 'siteorigin_panels_wp_import_post_meta' );

/**
 * A callback that replaces temporary break tag with actual line breaks.
 *
 * @param $val
 *
 * @return array|mixed
 */
function siteorigin_panels_wp_import_post_meta_map( $val ) {
	if ( is_string( $val ) ) {
		return str_replace( '<<<br>>>', "\n", $val );
	} else {
		return array_map( 'siteorigin_panels_wp_import_post_meta_map', $val );
	}
}

/**
 * Admin ajax handler for loading a prebuilt layout.
 */
function siteorigin_panels_ajax_action_prebuilt() {
	// Get any layouts that the current user could edit.
	$layouts = apply_filters( 'siteorigin_panels_prebuilt_layouts', array() );

	if ( empty( $_GET['layout'] ) ) {
		exit();
	}
	if ( empty( $layouts[ $_GET['layout'] ] ) ) {
		exit();
	}

	header( 'content-type: application/json' );

	$layout = ! empty( $layouts[ $_GET['layout'] ] ) ? $layouts[ $_GET['layout'] ] : array();
	$layout = apply_filters( 'siteorigin_panels_prebuilt_layout', $layout );

	echo json_encode( $layout );
	exit();
}

add_action( 'wp_ajax_so_panels_prebuilt', 'siteorigin_panels_ajax_action_prebuilt' );

function ppb_woocommerce_tab() {
	?>
	Using WooCommerce? You can now build a stunning shop with Page Builder. Just get our WooCommerce extension and start building!
<?php
}
add_action( 'ppb_add_content_woocommerce_tab', 'ppb_woocommerce_tab' );

/**
 * Display a widget form with the provided data
 */
function ppb_print_editor_panel( $request = null ) {

	?>
	<div class="ppb-cool-panel-wrap">
		<ul class="ppb-acp-sidebar">

			<li>
				<a class="ppb-tabs-anchors ppb-block-anchor ppb-editor" <?php selected( true ) ?> href="#pootle-editor-tab">
					<?php echo apply_filters( 'ppb_content_block_editor_title', 'Editor', $request ); ?>
				</a>
			</li>

			<?php if ( class_exists( 'WooCommerce' ) ) { ?>
				<li><a class="ppb-tabs-anchors" href="#pootle-wc-tab">WooCommerce</a></li>
			<?php } ?>

			<li class="ppb-seperator"></li>

			<li><a class="ppb-tabs-anchors" href="#pootle-style-tab">Style</a></li>

			<li><a class="ppb-tabs-anchors" href="#pootle-advanced-tab">Advanced</a></li>
		</ul>

		<?php ?>
		<div id="pootle-editor-tab" class="pootle-content-module tab-contents content-block">

			<?php echo do_action( 'ppb_content_block_editor_form', $request ); ?>

		</div>

		<div id="pootle-style-tab" class="pootle-style-fields pootle-content-module tab-contents">
			<?php
			pp_pb_widget_styles_dialog_form();
			?>
		</div>

		<div id="pootle-advanced-tab" class="pootle-style-fields pootle-content-module tab-contents">
			<?php
			pp_pb_widget_styles_dialog_form( 'inline-css' );
			?>
		</div>

		<?php if ( class_exists( 'WooCommerce' ) ) { ?>
			<div id="pootle-wc-tab" class="pootle-content-module tab-contents">
				<?php do_action( 'ppb_add_content_woocommerce_tab' ); ?>
			</div>
		<?php } ?>

	</div>
<?php

}

function ppb_panels_ajax_widget_form(){

	$request = array_map( 'stripslashes_deep', $_REQUEST );

	ppb_print_editor_panel( $request );

	exit();
}
add_action( 'wp_ajax_ppb_panels_editor_form', 'ppb_panels_ajax_widget_form' );

/**
 * Add some action links.
 *
 * @param $links
 * @TODO Shramee Use this
 * @return array
 */
function siteorigin_panels_plugin_action_links( $links ) {
	$links[] = '<a href="http://siteorigin.com/threads/plugin-page-builder/">' . __( 'Support Forum', 'ppb-panels' ) . '</a>';
	$links[] = '<a href="http://siteorigin.com/page-builder/#newsletter">' . __( 'Newsletter', 'ppb-panels' ) . '</a>';

	return $links;
}

//add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'siteorigin_panels_plugin_action_links' );

function pp_pb_load_slider_js( $doLoad ) {
	return true;
}

add_filter( 'woo_load_slider_js', 'pp_pb_load_slider_js' );

function pp_pb_generate_font_css( $option, $em = '1' ) {

	// Test if font-face is a Google font
	global $google_fonts;
	if ( is_array( $google_fonts ) ) {
		foreach ( $google_fonts as $google_font ) {

			// Add single quotation marks to font name and default arial sans-serif ending
			if ( $option['face'] == $google_font['name'] ) {
				$option['face'] = "'" . $option['face'] . "', arial, sans-serif";
			}

		} // END foreach
	}

	if ( ! @$option['style'] && ! @$option['size'] && ! @$option['unit'] && ! @$option['color'] ) {
		return 'font-family: ' . stripslashes( $option["face"] ) . ' !important;';
	} else {
		return 'font:' . $option['style'] . ' ' . $option['size'] . $option['unit'] . '/' . $em . 'em ' . stripslashes( $option['face'] ) . ' !important; color:' . $option['color'] . ' !important;';
	}
} // End pp_pb_generate_font_css( )

function pp_pb_widget_styling_fields() {
	return array(
		'background-color'   => array(
			'name' => 'Background color',
			'type' => 'color',
			'css'  => 'background-color',
		),
		'border'             => array(
			'name' => 'Border',
			'type' => 'border',
			'css'  => 'border'
		),
		'padding' => array(
			'name' => 'Padding',
			'type' => 'number',
			'min'  => '0',
			'max'  => '100',
			'step' => '1',
			'unit' => 'px',
			'css'  => array( 'padding' )
		),
		'rounded-corners'    => array(
			'name' => 'Rounded corners',
			'type' => 'number',
			'min'  => '0',
			'max'  => '100',
			'step' => '1',
			'unit' => 'px',
			'css'  => 'border-radius'
		),
		'inline-css'         => array(
			'name' => 'Inline Styles',
			'type' => 'textarea',
			'css'  => ''
		),
	);
}

$PootlePageFile = __FILE__;

add_action( 'init', 'pp_pootlepage_updater' );
function pp_pootlepage_updater() {
	if ( ! function_exists( 'get_plugin_data' ) ) {
		include( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	$data                          = get_plugin_data( __FILE__ );
	$wptuts_plugin_current_version = $data['Version'];
	$wptuts_plugin_remote_path     = 'http://www.pootlepress.com/?updater=1';
	$wptuts_plugin_slug            = plugin_basename( __FILE__ );
	new Pootlepress_Updater ( $wptuts_plugin_current_version, $wptuts_plugin_remote_path, $wptuts_plugin_slug );
}

add_action( 'in_plugin_update_message-page-builder-for-canvas-master/page-builder-for-canvas.php', 'pp_pb_in_plugin_update_message', 10, 2 );

//$r is a object
function pp_pb_in_plugin_update_message( $args, $r ) {
	if ( $args['update'] ) {
		$transient_name = 'pp_pb_upgrade_notice_' . $args['Version'];

		if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {

			$response = wp_remote_post( $args['url'], array(
				'body' => array(
					'action' => 'upgrade-notice',
					'plugin' => $args['slug']
				)
			) );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) && $response['body'] != 'false' ) {

				// Output Upgrade Notice
				$upgrade_notice = '';

				// css from WooCommerce
				$upgrade_notice .= '<style>.wc_plugin_upgrade_notice{font-weight:400;color:#fff;background:#d54d21;padding:1em;margin:9px 0}.wc_plugin_upgrade_notice a{color:#fff;text-decoration:underline}.wc_plugin_upgrade_notice:before{content:"\f348";display:inline-block;font:400 18px/1 dashicons;speak:none;margin:0 8px 0 -2px;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;vertical-align:top}</style>';

				$upgrade_notice .= '<div class="wc_plugin_upgrade_notice">';

				$upgrade_notice .= $response['body'];

				$upgrade_notice .= '</div> ';

				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}

		echo $upgrade_notice;
	}
}

//No admin notices on our settings page
function ppb_no_admin_notices() {
	global $pagenow;

	if ( 'options-general.php' == $pagenow && 'page_builder' == filter_input( INPUT_GET, 'page' ) ) {
		remove_all_actions( 'admin_notices' );
	}
}

add_action( 'admin_notices', 'ppb_no_admin_notices', 0 );

function ppb_wp_seo_filter( $content, $post ) {

	$id          = $post->ID;
	$panels_data = get_post_meta( $id, 'panels_data', true );
	if ( ! empty( $panels_data['widgets'] ) ) {
		foreach ( $panels_data['widgets'] as $widget ) {
			if ( ! empty( $widget['text'] ) ) {
				$content .= $widget['text'] . "\n\n";
			}
		}
	}

	return $content;

}

add_filter( 'wpseo_pre_analysis_post_content', 'ppb_wp_seo_filter', 10, 2 );

function ppb_remove_content_blocks_from_widgets() {
	echo '<style>
	 .widgets-php [id*="ppb-panels-postloop"], .widgets-php [id*="black-studio-tinymce"]{
	 display:none;
	 }
  </style>';
}

add_action( 'admin_head', 'ppb_remove_content_blocks_from_widgets' );
