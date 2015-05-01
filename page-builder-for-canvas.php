<?php
/*
Plugin Name: Canvas Extension - Page Builder for Canvas
Plugin URI: http://pootlepress.com/
Description: A page builder for WooThemes Canvas.
Version: 2.2.6
Author: PootlePress
Author URI: http://pootlepress.com/
License: GPL version 3
*/


define( 'POOTLEPAGE_VERSION', '2.2.6' );
define( 'POOTLEPAGE_BASE_FILE', __FILE__ );

add_action( 'admin_init', 'pp_pb_check_for_conflict' );

function pp_pb_check_for_conflict( ) {
    if ( is_plugin_active( 'wx-pootle-text-widget/pootlepress-text-widget.php' ) ||
        is_plugin_active( 'pootle-text-widget-master/pootlepress-text-widget.php' ) ) {

        $pluginFile =  __FILE__;
        $plugin = plugin_basename( $pluginFile );
        if ( is_plugin_active( $plugin ) ) {
            deactivate_plugins( $plugin );
            wp_die( "ERROR: <strong>Page Builder</strong> cannot be activated if Pootle Text Widget is also activated. " .
                "Page Builder is unable to continue and has been deactivated. " .
                "<br /><br />Back to the WordPress <a href='".get_admin_url( null, 'plugins.php' )."'>Plugins page</a>." );
        }
    }
}

include plugin_dir_path( __FILE__ ) . 'widgets/basic.php';

include plugin_dir_path( __FILE__ ) . 'inc/options.php';
include plugin_dir_path( __FILE__ ) . 'inc/revisions.php';
include plugin_dir_path( __FILE__ ) . 'inc/copy.php';
include plugin_dir_path( __FILE__ ) . 'inc/styles.php';
include plugin_dir_path( __FILE__ ) . 'inc/legacy.php';
include plugin_dir_path( __FILE__ ) . 'inc/notice.php';
include plugin_dir_path( __FILE__ ) . 'inc/vantage-extra.php';
include plugin_dir_path( __FILE__ ) . 'inc/class-pootlepress-updater.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-pootlepage-font-utility.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-pootlepage-customizer.php';

if ( defined( 'SITEORIGIN_PANELS_DEV' ) && SITEORIGIN_PANELS_DEV ) include plugin_dir_path( __FILE__ ).'inc/debug.php';

/**
 * Hook for activation of Page Builder.
 */
function siteorigin_panels_activate( ) {
	add_option( 'siteorigin_panels_initial_version', POOTLEPAGE_VERSION, '', 'no' );
}
register_activation_hook( __FILE__, 'siteorigin_panels_activate' );

/**
 * Initialize the Page Builder.
 */
function siteorigin_panels_init( ) {
	$display_settings = get_option( 'siteorigin_panels_display', array( ) );
	if ( isset( $display_settings['bundled-widgets'] ) && !$display_settings['bundled-widgets'] ) return;

	if ( !defined( 'SITEORIGIN_PANELS_LEGACY_WIDGETS_ACTIVE' ) && ( !is_admin( ) || basename( $_SERVER["SCRIPT_FILENAME"] ) != 'plugins.php' ) ) {
		// Include the bundled widgets if the Legacy Widgets plugin isn't active.
		include plugin_dir_path( __FILE__ ).'widgets/widgets.php';
	}
}
add_action( 'plugins_loaded', 'siteorigin_panels_init' );

/**
 * Initialize the language files
 */
function siteorigin_panels_init_lang( ) {
	load_plugin_textdomain( 'siteorigin-panels', false, dirname( plugin_basename( __FILE__ ) ). '/lang/' );
}
add_action( 'plugins_loaded', 'siteorigin_panels_init_lang' );

/**
 * Add the admin menu entries
 */
function siteorigin_panels_admin_menu( ) {
	if ( !siteorigin_panels_setting( 'home-page' ) ) return;

	add_theme_page(
		__( 'Custom Home Page Builder', 'siteorigin-panels' ),
		__( 'Home Page', 'siteorigin-panels' ),
		'edit_theme_options',
		'so_panels_home_page',
		'siteorigin_panels_render_admin_home_page'
	 );
}
add_action( 'admin_menu', 'siteorigin_panels_admin_menu' );

/**
 * Render the page used to build the custom home page.
 */
function siteorigin_panels_render_admin_home_page( ) {
	add_meta_box( 'so-panels-panels', __( 'Page Builder', 'siteorigin-panels' ), 'siteorigin_panels_metabox_render', 'appearance_page_so_panels_home_page', 'advanced', 'high' );
	include plugin_dir_path( __FILE__ ).'tpl/admin-home-page.php';
}

/**
 * Callback to register the Panels Metaboxes
 */
function siteorigin_panels_metaboxes( ) {
	foreach( siteorigin_panels_setting( 'post-types' ) as $type ) {
		add_meta_box( 'so-panels-panels', __( 'Page Builder', 'siteorigin-panels' ), 'siteorigin_panels_metabox_render', $type, 'advanced', 'high' );
	}
}
add_action( 'add_meta_boxes', 'siteorigin_panels_metaboxes' );

/**
 * Save home page
 */
function siteorigin_panels_save_home_page( ) {
	if ( !isset( $_POST['_sopanels_home_nonce'] ) || !wp_verify_nonce( $_POST['_sopanels_home_nonce'], 'save' ) ) return;
	if ( empty( $_POST['panels_js_complete'] ) ) return;
	if ( !current_user_can( 'edit_theme_options' ) ) return;

	update_option( 'siteorigin_panels_home_page', siteorigin_panels_get_panels_data_from_post( $_POST ) );
	update_option( 'siteorigin_panels_home_page_enabled', $_POST['siteorigin_panels_home_enabled'] == 'true' ? true : '' );

	// If we've enabled the panels home page, change show_on_front to posts, this is required for the home page to work properly
	if ( $_POST['siteorigin_panels_home_enabled'] == 'true' ) update_option( 'show_on_front', 'posts' );
}
add_action( 'admin_init', 'siteorigin_panels_save_home_page' );

/**
 * Modify the front page template
 *
 * @param $template
 * @return string
 */
function siteorigin_panels_filter_home_template( $template ) {
	if (
		!get_option( 'siteorigin_panels_home_page_enabled', siteorigin_panels_setting( 'home-page-default' ) )
		|| !siteorigin_panels_setting( 'home-page' )
	 ) return $template;

	$GLOBALS['siteorigin_panels_is_panels_home'] = true;
	return locate_template( array(
		'home-panels.php',
		$template
	 ) );
}
add_filter( 'home_template', 'siteorigin_panels_filter_home_template' );

/**
 * If this is the main query, store that we're accessing the front page
 * @param $wp_query
 */
function siteorigin_panels_render_home_page_prepare( $wp_query ) {
	if ( !$wp_query->is_main_query( ) ) return;
	if ( !get_option( 'siteorigin_panels_home_page_enabled', siteorigin_panels_setting( 'home-page-default' ) ) ) return;

	$GLOBALS['siteorigin_panels_is_home'] = @ $wp_query->is_front_page( );
}
add_action( 'pre_get_posts', 'siteorigin_panels_render_home_page_prepare' );

/**
 * This fixes a rare case where pagination for a home page loop extends further than post pagination.
 */
function siteorigin_panels_render_home_page( ) {
	if (
		empty( $GLOBALS['siteorigin_panels_is_home'] ) ||
		!is_404( ) ||
		!get_option( 'siteorigin_panels_home_page_enabled', siteorigin_panels_setting( 'home-page-default' ) )
	 ) return;

	// This query was for the home page, but because of pagination we're getting a 404
	// Create a fake query so the home page keeps working with the post loop widget
	$paged = get_query_var( 'paged' );
	if ( empty( $paged ) ) return;

	query_posts( array( ) );
	set_query_var( 'paged', $paged );

	// Make this query the main one
	$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
	status_header( 200 ); // Overwrite the 404 header we set earlier.
}
add_action( 'template_redirect', 'siteorigin_panels_render_home_page' );

/**
 * @return mixed|void Are we currently viewing the home page
 */
function siteorigin_panels_is_home( ) {
	$home = ( is_home( ) && get_option( 'siteorigin_panels_home_page_enabled', siteorigin_panels_setting( 'home-page-default' ) ) );
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
 * @return bool
 */
function siteorigin_panels_is_panel( $can_edit = false ) {
	// Check if this is a panel
	$is_panel =  ( siteorigin_panels_is_home( ) || ( is_singular( ) && get_post_meta( get_the_ID( ), 'panels_data', false ) != '' ) );
	return $is_panel && ( !$can_edit || ( ( is_singular( ) && current_user_can( 'edit_post', get_the_ID( ) ) ) || ( siteorigin_panels_is_home( ) && current_user_can( 'edit_theme_options' ) ) ) );
}

/**
 * Render a panel metabox.
 *
 * @param $post
 */
function siteorigin_panels_metabox_render( $post ) {
	include plugin_dir_path( __FILE__ ).'tpl/metabox-panels.php';
}


/**
 * Enqueue the panels admin scripts
 *
 * @action admin_print_scripts-post-new.php
 * @action admin_print_scripts-post.php
 * @action admin_print_scripts-appearance_page_so_panels_home_page
 */
function siteorigin_panels_admin_enqueue_scripts( $prefix ) {
	$screen = get_current_screen( );

	if ( ( $screen->base == 'post' && in_array( $screen->id, siteorigin_panels_setting( 'post-types' ) ) ) || $screen->base == 'appearance_page_so_panels_home_page' ) {
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-button' );

		wp_enqueue_script( 'so-undomanager', plugin_dir_url( __FILE__ ) . 'js/undomanager.min.js', array( ), 'fb30d7f' );

        // check if "chosen" is already used, e.g. by WooCommerce
        if ( !wp_script_is( 'chosen' ) ) {
            wp_enqueue_script( 'so-panels-chosen', plugin_dir_url( __FILE__ ) . 'js/chosen/chosen.jquery.min.min.js', array( 'jquery' ), POOTLEPAGE_VERSION );
        }

		wp_enqueue_script( 'so-panels-admin', plugin_dir_url( __FILE__ ) . 'js/panels.admin.js', array( 'jquery' ), POOTLEPAGE_VERSION );
		wp_enqueue_script( 'so-panels-admin-panels', plugin_dir_url( __FILE__ ) . 'js/panels.admin.panels.js', array( 'jquery' ), POOTLEPAGE_VERSION );
		wp_enqueue_script( 'so-panels-admin-grid', plugin_dir_url( __FILE__ ) . 'js/panels.admin.grid.js', array( 'jquery' ), POOTLEPAGE_VERSION );
		wp_enqueue_script( 'so-panels-admin-prebuilt', plugin_dir_url( __FILE__ ) . 'js/panels.admin.prebuilt.js', array( 'jquery' ), POOTLEPAGE_VERSION );
		wp_enqueue_script( 'so-panels-admin-tooltip', plugin_dir_url( __FILE__ ) . 'js/panels.admin.tooltip.min.js', array( 'jquery' ), POOTLEPAGE_VERSION );
		wp_enqueue_script( 'so-panels-admin-media', plugin_dir_url( __FILE__ ) . 'js/panels.admin.media.min.js', array( 'jquery' ), POOTLEPAGE_VERSION );
		wp_enqueue_script( 'so-panels-admin-styles', plugin_dir_url( __FILE__ ) . 'js/panels.admin.styles.js', array( 'jquery' ), POOTLEPAGE_VERSION );

        wp_enqueue_script( 'row-options', plugin_dir_url( __FILE__ ) . 'js/row.options.admin.js', array( 'jquery' ) );

		wp_localize_script( 'so-panels-admin', 'panels', array(
			'previewUrl' => wp_nonce_url( add_query_arg( 'siteorigin_panels_preview', 'true', get_home_url( ) ), 'siteorigin-panels-preview' ),
			'i10n' => array(
				'buttons' => array(
					'insert' => __( 'Insert', 'siteorigin-panels' ),
					'cancel' => __( 'cancel', 'siteorigin-panels' ),
					'delete' => __( 'Delete', 'siteorigin-panels' ),
					'duplicate' => __( 'Duplicate', 'siteorigin-panels' ),
                    'style' => __( 'Style', 'siteorigin-panels' ),
					'edit' => __( 'Edit', 'siteorigin-panels' ),
					'done' => __( 'Done', 'siteorigin-panels' ),
					'undo' => __( 'Undo', 'siteorigin-panels' ),
					'add' => __( 'Add', 'siteorigin-panels' ),
				 ),
				'messages' => array(
					'deleteColumns' => __( 'Columns deleted', 'siteorigin-panels' ),
					'deleteWidget' => __( 'Widget deleted', 'siteorigin-panels' ),
					'confirmLayout' => __( 'Are you sure you want to load this layout? It will overwrite your current page.', 'siteorigin-panels' ),
					'editWidget' => __( 'Edit %s Widget', 'siteorigin-panels' ),
                    'styleWidget' => __( 'Style Widget', 'siteorigin-panels' )
				 ),
			 ),
		 ) );

        // this is the data of the widget and row that have been setup
		$panels_data = siteorigin_panels_get_current_admin_panels_data( );

		// Remove any widgets with classes that don't exist
		if ( !empty( $panels_data['panels'] ) ) {
			foreach ( $panels_data['panels'] as $i => $panel ) {
				if ( !class_exists( $panel['info']['class'] ) ) unset( $panels_data['panels'][$i] );
			}
		}

		// Add in the forms
		if ( count( $panels_data ) > 0 ) {

			foreach ( $panels_data['widgets'] as $i => $widget ) {
				if ( !class_exists( $widget['info']['class'] ) ) unset( $panels_data['widgets'][$i] );

				// bring over the hide title check box from old Pootle Visual Editor
				if ( $widget['info']['class'] == 'Pootle_Text_Widget' ) {
					if ( isset( $widget['hide-title'] ) && $widget['hide-title'] == '1' ) {

						$widgetStyle = isset( $widget['info']['style'] ) ? json_decode( $widget['info']['style'], true ) : pp_get_default_widget_style( );

						$widgetStyle['hide-title'] = 'none';

						$panels_data['widgets'][$i]['info']['style'] = json_encode( $widgetStyle );
					}
				}
			}

            // load all data even if no widget inside, so row styling will be loaded
			wp_localize_script( 'so-panels-admin', 'panelsData', $panels_data );
		}

		// Set up the row styles
		wp_localize_script( 'so-panels-admin', 'panelsStyleFields', siteorigin_panels_style_get_fields( ) );

        // we definitely need to use color picker
		//if ( siteorigin_panels_style_is_using_color( ) ) {

            wp_dequeue_script( "iris" );

            wp_enqueue_script( "pp-pb-iris", plugin_dir_url( __FILE__ ) . '/js/iris.js', array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ) );
            wp_enqueue_script( 'pp-pb-color-picker', plugin_dir_url( __FILE__ ) . '/js/color-picker-custom.js', array( 'pp-pb-iris' ) );

            wp_localize_script( 'pp-pb-color-picker', 'wpColorPickerL10n', array(
                'clear' => __( 'Clear' ),
                'defaultString' => __( 'Default' ),
                'pick' => __( 'Select Color' ),
                'current' => __( 'Current Color' ),
           ) );

			wp_enqueue_style( 'wp-color-picker' );
		//}

		// Render all the widget forms. A lot of widgets use this as a chance to enqueue their scripts
		$original_post = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null; // Make sure widgets don't change the global post.
		foreach( $GLOBALS['wp_widget_factory']->widgets as $class => $widget_obj ) {
			ob_start( );
			$widget_obj->form( array( ) );
			ob_clean( );
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
 * Enqueue the admin panel styles
 *
 * @action admin_print_styles-post-new.php
 * @action admin_print_styles-post.php
 */
function siteorigin_panels_admin_enqueue_styles( ) {
	$screen = get_current_screen( );
	if ( in_array( $screen->id, siteorigin_panels_setting( 'post-types' ) ) || $screen->base == 'appearance_page_so_panels_home_page' ) {
		wp_enqueue_style( 'so-panels-admin', plugin_dir_url( __FILE__ ) . 'css/admin.css', array( ), POOTLEPAGE_VERSION );

		global $wp_version;
		if ( version_compare( $wp_version, '3.9.beta.1', '<' ) ) {
			// Versions before 3.9 need some custom jQuery UI styling
			wp_enqueue_style( 'so-panels-admin-jquery-ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css', array( ), POOTLEPAGE_VERSION );
		}
		else{
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
		}

		wp_enqueue_style( 'so-panels-chosen', plugin_dir_url( __FILE__ ) . 'js/chosen/chosen.css', array( ), POOTLEPAGE_VERSION );
		do_action( 'siteorigin_panel_enqueue_admin_styles' );
	}
}
add_action( 'admin_print_styles-post-new.php', 'siteorigin_panels_admin_enqueue_styles' );
add_action( 'admin_print_styles-post.php', 'siteorigin_panels_admin_enqueue_styles' );
add_action( 'admin_print_styles-appearance_page_so_panels_home_page', 'siteorigin_panels_admin_enqueue_styles' );

function pootlepage_option_page_styles( ) {
    // using $screen->id is not reliable, because it can change if using child theme
    global $pagenow;
    if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] == 'page_builder' ) {
        wp_enqueue_style( 'pootlepage-option-admin', plugin_dir_url( __FILE__ ) . 'css/option-admin.css', array( ), POOTLEPAGE_VERSION );
    }
}

add_action( 'admin_print_styles', 'pootlepage_option_page_styles' );

function pootlepage_option_page_scripts( ) {

    // using $screen->id is not reliable, because it can change if using child theme
    global $pagenow;
    if ( $pagenow == 'admin.php' && isset( $_GET['page'] ) && $_GET['page'] == 'page_builder' ) {
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'pootlepage-option-admin', plugin_dir_url( __FILE__ ) . 'js/option-admin.js', array( 'jquery' ), POOTLEPAGE_VERSION );
    }
}

add_action( 'admin_print_scripts', 'pootlepage_option_page_scripts' );

/**
 * Add a help tab to pages with panels.
 */
function siteorigin_panels_add_help_tab( $prefix ) {
	$screen = get_current_screen( );
	if (
		( $screen->base == 'post' && ( in_array( $screen->id, siteorigin_panels_setting( 'post-types' ) ) || $screen->id == '' ) )
		|| ( $screen->id == 'appearance_page_so_panels_home_page' )
	 ) {
		$screen->add_help_tab( array(
			'id' => 'panels-help-tab', //unique id for the tab
			'title' => __( 'Page Builder', 'siteorigin-panels' ), //unique visible title for the tab
			'callback' => 'siteorigin_panels_add_help_tab_content'
		 ) );
	}
}
add_action( 'load-page.php', 'siteorigin_panels_add_help_tab', 12 );
add_action( 'load-post-new.php', 'siteorigin_panels_add_help_tab', 12 );
add_action( 'load-appearance_page_so_panels_home_page', 'siteorigin_panels_add_help_tab', 12 );

/**
 * Display the content for the help tab.
 */
function siteorigin_panels_add_help_tab_content( ) {
	include plugin_dir_path( __FILE__ ) . 'tpl/help.php';
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
	if ( empty( $_POST['_sopanels_nonce'] ) || !wp_verify_nonce( $_POST['_sopanels_nonce'], 'save' ) ) return;
	if ( empty( $_POST['panels_js_complete'] ) ) return;
	if ( !current_user_can( 'edit_post', $post_id ) ) return;
	//Don't Save panels if $post_id is not same as current post ID
	//( Prevents population product panels data in saving Tabs via Meta )
	if ( $_POST['post_ID'] != $post_id ) return;

	$panels_data = siteorigin_panels_get_panels_data_from_post( $_POST );
	if ( function_exists( 'wp_slash' ) ) $panels_data = wp_slash( $panels_data );
	update_post_meta( $post_id, 'panels_data', $panels_data );

//    if ( isset( $_POST['page-settings'] ) ) {
//        $pageSettingsJson = $_POST['page-settings'];
//        update_post_meta( $post_id, 'pootlepage-page-settings', $pageSettingsJson );
//    }
//    if ( isset( $_POST['hide-elements'] ) ) {
//        $hideElementsJson = $_POST['hide-elements'];
//        update_post_meta( $post_id, 'pootlepage-hide-elements', $hideElementsJson );
//    }
}
add_action( 'save_post', 'siteorigin_panels_save_post', 10, 2 );

/**
 * Get the home page panels layout data.
 *
 * @return mixed|void
 */
function siteorigin_panels_get_home_page_data( ) {
	$panels_data = get_option( 'siteorigin_panels_home_page', null );
	if ( is_null( $panels_data ) ) {
		// Load the default layout
		$layouts = apply_filters( 'siteorigin_panels_prebuilt_layouts', array( ) );
		$panels_data = !empty( $layouts['default_home'] ) ? $layouts['default_home'] : current( $layouts );
	}

	return $panels_data;
}

/**
 * Get the Page Builder data for the current admin page.
 *
 * @return array
 */
function siteorigin_panels_get_current_admin_panels_data( ) {
	$screen = get_current_screen( );

	// Localize the panels with the panels data
	if ( $screen->base == 'appearance_page_so_panels_home_page' ) {
		$panels_data = get_option( 'siteorigin_panels_home_page', null );
		if ( is_null( $panels_data ) ) {
			// Load the default layout
			$layouts = apply_filters( 'siteorigin_panels_prebuilt_layouts', array( ) );

			$home_name = siteorigin_panels_setting( 'home-page-default' ) ? siteorigin_panels_setting( 'home-page-default' ) : 'home';
			$panels_data = !empty( $layouts[$home_name] ) ? $layouts[$home_name] : current( $layouts );
		}
		$panels_data = apply_filters( 'siteorigin_panels_data', $panels_data, 'home' );
	}
	else{
		global $post;
		$panels_data = get_post_meta( $post->ID, 'panels_data', true );
		$panels_data = apply_filters( 'siteorigin_panels_data', $panels_data, $post->ID );
	}

	if ( empty( $panels_data ) ) $panels_data = array( );

    // widget style is new addition, it may not be present in panel data in database before,
    // so set a default widget when loading panel data
    if ( isset( $panels_data['widgets'] ) ) {
        foreach ( $panels_data['widgets'] as &$widget ) {
            if ( isset( $widget['info'] ) ) {
                if ( !isset( $widget['info']['style'] ) ) {
                    $widget['info']['style'] = pp_get_default_widget_style( );
                }
            }
        }
    }


	return $panels_data;
}

function pp_get_default_widget_style( ) {
    $widgetStyleFields = pp_pb_widget_styling_fields( );

    $result = array( );
    foreach ( $widgetStyleFields as $key => $field ) {
        if ( $field['type'] == 'border' ) {
            $result[$key . '-width'] = 0;
            $result[$key . '-color'] = '';
        } elseif ( $field['type'] == 'number' ) {
            $result[$key] = 0;
		} elseif ( $field['type'] == 'checkbox' ) {
			$result[$key] = '';
        } else{
            $result[$key] = '';
        }
    }
    return $result;

//    $result = array(
//        'backgroundColor' => '',
//        'borderWidth' => 0,
//        'borderColor' => '',
//        'paddingTop' => 0,
//        'paddingBottom' => 0,
//        'paddingLeft' => 0,
//        'paddingRight' => 0,
//        'marginTop' => 0,
//        'marginBottom' => 0,
//        'marginLeft' => 0,
//        'marginRight' => 0,
//        'borderRadius' => 0
//   );
//
//    return $result;
}

/**
 * Echo the CSS for the current panel
 *
 * @action init
 */
function siteorigin_panels_css( ) {
	if ( !isset( $_GET['post'] ) || !isset( $_GET['ver'] ) ) return;

	if ( $_GET['post'] == 'home' ) $panels_data = siteorigin_panels_get_home_page_data( );
	else$panels_data = get_post_meta( $_GET['post'], 'panels_data', true );
	$post_id = $_GET['post'];

	header( "Content-type: text/css" );
	echo siteorigin_panels_generate_css( $_GET['post'], $panels_data );
	exit( );
}
add_action( 'wp_ajax_siteorigin_panels_post_css', 'siteorigin_panels_css' );
add_action( 'wp_ajax_nopriv_siteorigin_panels_post_css', 'siteorigin_panels_css' );

/**
 * Generate the actual CSS.
 *
 * @param $post_id
 * @param $panels_data
 * @return string
 */
function siteorigin_panels_generate_css( $post_id, $panels_data ) {
	// Exit if we don't have panels data
	if ( empty( $panels_data ) || empty( $panels_data['grids'] ) ) return;

	$settings = siteorigin_panels_setting( );

	$panels_mobile_width = $settings['mobile-width'];
	$panels_margin_bottom = $settings['margin-bottom'];

	$css = array( );
	$css[1920] = array( );
	$css[ $panels_mobile_width ] = array( ); // This is a mobile resolution

	// Add the grid sizing
	$ci = 0;
	foreach ( $panels_data['grids'] as $gi => $grid ) {
		$cell_count = intval( $grid['cells'] );
		for ( $i = 0; $i < $cell_count; $i++ ) {
			$cell = $panels_data['grid_cells'][$ci++];

			if ( $cell_count > 1 ) {
				$css_new = 'width:' . round( $cell['weight'] * 100, 3 ) . '%';
				if ( empty( $css[1920][$css_new] ) ) $css[1920][$css_new] = array( );
				$css[1920][$css_new][] = '#pgc-' . $post_id . '-' . $gi  . '-' . $i;
			}
		}

		// Add the bottom margin to any grids that aren't the last
		if ( $gi != count( $panels_data['grids'] )-1 ) {
			$css[1920]['margin-bottom: '.$panels_margin_bottom.'px'][] = '#pg-' . $post_id . '-' . $gi;
		}

        // do not set float, use inline-block instead
//		if ( $cell_count > 1 ) {
//			if ( empty( $css[1920]['float:left'] ) ) $css[1920]['float:left'] = array( );
//			$css[1920]['float:left'][] = '#pg-' . $post_id . '-' . $gi . ' .panel-grid-cell';
//		}

		if ( $settings['responsive'] ) {
			// Mobile Responsive
			$mobile_css = array( 'float:none', 'width:auto' );
			foreach ( $mobile_css as $c ) {
				if ( empty( $css[ $panels_mobile_width ][ $c ] ) ) $css[ $panels_mobile_width ][ $c ] = array( );
				$css[ $panels_mobile_width ][ $c ][] = '#pg-' . $post_id . '-' . $gi . ' .panel-grid-cell';
			}

			for ( $i = 0; $i < $cell_count; $i++ ) {
				if ( $i != $cell_count - 1 ) {
					$css_new = 'margin-bottom:' . $panels_margin_bottom . 'px';
					if ( empty( $css[$panels_mobile_width][$css_new] ) ) $css[$panels_mobile_width][$css_new] = array( );
					$css[$panels_mobile_width][$css_new][] = '#pgc-' . $post_id . '-' . $gi . '-' . $i;
				}
			}
		}
	}

	if ( $settings['responsive'] ) {
		// Add CSS to prevent overflow on mobile resolution.
		$panel_grid_css = 'margin-left: 0 !important; margin-right: 0 !important;';
		$panel_grid_cell_css = 'padding: 0 !important; width: 100% !important;';// TODO copy changes back to folio
		if ( empty( $css[ $panels_mobile_width ][ $panel_grid_css ] ) ) $css[ $panels_mobile_width ][ $panel_grid_css ] = array( );
		if ( empty( $css[ $panels_mobile_width ][ $panel_grid_cell_css ] ) ) $css[ $panels_mobile_width ][ $panel_grid_cell_css ] = array( );
		$css[ $panels_mobile_width ][ $panel_grid_css ][] = '.panel-grid';
		$css[ $panels_mobile_width ][ $panel_grid_cell_css ][] = '.panel-grid-cell';
	} else{
        // TODO Copy changes back to Folio
        $panel_grid_cell_css = 'display: inline-block !important; vertical-align: top !important;';

        if ( empty( $css[ $panels_mobile_width ][ $panel_grid_cell_css ] ) ) $css[ $panels_mobile_width ][ $panel_grid_cell_css ] = array( );

        $css[ $panels_mobile_width ][ $panel_grid_cell_css ][] = '.panel-grid-cell';
	}

	// Add the bottom margin
	$bottom_margin = 'margin-bottom: '.$panels_margin_bottom.'px';
	$bottom_margin_last = 'margin-bottom: 0 !important';
	if ( empty( $css[ 1920 ][ $bottom_margin ] ) ) $css[ 1920 ][ $bottom_margin ] = array( );
	if ( empty( $css[ 1920 ][ $bottom_margin_last ] ) ) $css[ 1920 ][ $bottom_margin_last ] = array( );
	$css[ 1920 ][ $bottom_margin ][] = '.panel-grid-cell .panel';
	$css[ 1920 ][ $bottom_margin_last ][] = '.panel-grid-cell .panel:last-child';

	// This is for the side margins
	$magin_half = $settings['margin-sides']/2;
	$side_margins = "margin: 0 -{$magin_half}px 0 -{$magin_half}px";
	$side_paddings = "padding: 0 {$magin_half}px 0";
	if ( empty( $css[ 1920 ][ $side_margins ] ) ) $css[ 1920 ][ $side_margins ] = array( );
	if ( empty( $css[ 1920 ][ $side_paddings ] ) ) $css[ 1920 ][ $side_paddings ] = array( );
	$css[ 1920 ][ $side_margins ][] = '.panel-grid';
	$css[ 1920 ][ $side_paddings ][] = '.panel-grid-cell';

	/**
	 * Filter the unprocessed CSS array
	 */
	$css = apply_filters( 'siteorigin_panels_css', $css );

	// Build the CSS
	$css_text = '';
	krsort( $css );
	foreach ( $css as $res => $def ) {
		if ( empty( $def ) ) continue;

		if ( $res < 1920 ) {
			$css_text .= '@media ( max-width:' . $res . 'px )';
			$css_text .= ' { ';
		}

		foreach ( $def as $property => $selector ) {
			$selector = array_unique( $selector );
			$css_text .= implode( ' , ', $selector ) . ' { ' . $property . ' } ';
		}

		if ( $res < 1920 ) $css_text .= ' } ';
	}

	return $css_text;
}

/**
 * Prepare the panels data early so widgets can enqueue their scripts and styles for the header.
 */
function siteorigin_panels_prepare_home_content( ) {
	if ( siteorigin_panels_is_home( ) ) {
		global $siteorigin_panels_cache;
		if ( empty( $siteorigin_panels_cache ) ) $siteorigin_panels_cache = array( );
		$siteorigin_panels_cache['home'] = siteorigin_panels_render( 'home' );
	}
}
//add_action( 'wp_enqueue_scripts', 'siteorigin_panels_prepare_home_content', 11 );

function siteorigin_panels_prepare_single_post_content( ) {
	if ( is_singular( ) ) {
		global $siteorigin_panels_cache;
		if ( empty( $siteorigin_panels_cache[ get_the_ID( ) ] ) ) {
			$siteorigin_panels_cache[ get_the_ID( ) ] = siteorigin_panels_render( get_the_ID( ) );
		}
	}
}
//add_action( 'wp_enqueue_scripts', 'siteorigin_panels_prepare_single_post_content' );

/**
 * Filter the content of the panel, adding all the widgets.
 *
 * @param $content
 * @return string
 *
 * @filter the_content
 */
function siteorigin_panels_filter_content( $content ) {

    $isWooCommerceInstalled =
        function_exists( 'is_shop' ) && function_exists( 'wc_get_page_id' );

    if ( $isWooCommerceInstalled ) {
        // prevent Page Builder overwrite taxonomy description with widget content
        if ( is_tax( array( 'product_cat', 'product_tag' ) ) && get_query_var( 'paged' ) == 0 ) {
            return $content;
        }

        if ( is_post_type_archive( ) && !is_shop( ) ) {
            return $content;
        }

        if ( is_shop( ) ) {
            $postID = wc_get_page_id( 'shop' );
        } else{
            $postID = get_the_ID( );
        }
    } else{
        if ( is_post_type_archive( ) ) {
            return $content;
        }

        $postID = get_the_ID( );
    }

	//If product done once set $postID to Tabs Post ID
 	if ( isset( $GLOBALS['canvasPB_ProductDoneOnce'] ) ) {
		global $wpdb;
		$results = $wpdb->get_results(
		    "SELECT ID FROM "
		  . $wpdb->posts
		  . " WHERE "
		  . "post_content LIKE '"
		  . esc_sql( $content )
		  . "'"
		  . " AND post_type LIKE 'wc_product_tab'"
		  . " AND post_status LIKE 'publish'" );
		foreach( $results as $id ) {
		$postID = $id->ID;
		}
	}
	//If its product set canvasPB_ProductDoneOnce to skip this for TAB
	if ( function_exists( 'is_product' ) ) {
		if ( is_single( ) && is_product( ) ) {$GLOBALS['canvasPB_ProductDoneOnce']=TRUE;}
	}

    $post = get_post( $postID );

	if ( empty( $post ) ) return $content;
	if ( in_array( $post->post_type, siteorigin_panels_setting( 'post-types' ) ) ) {
		$panel_content = siteorigin_panels_render( $post->ID );

		if ( !empty( $panel_content ) ) $content = $panel_content;
	}

	return $content;
}

// set priority to 5 so Paid Membership Pro can filter it later, and overwrite Page Builder content
// set to 0 to execute this before any other actions and get raw $content for db lookup for tab CPT
add_filter( 'the_content', 'siteorigin_panels_filter_content', 0 );

/**
 * Render the panels
 *
 * @param int|string|bool $post_id The Post ID or 'home'.
 * @param bool $enqueue_css Should we also enqueue the layout CSS.
 * @param array|bool $panels_data Existing panels data. By default load from settings or post meta.
 * @return string
 */
function siteorigin_panels_render( $post_id = false, $enqueue_css = true, $panels_data = false ) {
	if ( empty( $post_id ) ) $post_id = get_the_ID( );

	global $siteorigin_panels_current_post;
	$old_current_post = $siteorigin_panels_current_post;
	$siteorigin_panels_current_post = $post_id;

	// Try get the cached panel from in memory cache.
	//global $siteorigin_panels_cache;
	//if ( !empty( $siteorigin_panels_cache ) && !empty( $siteorigin_panels_cache[$post_id] ) )
	//	return $siteorigin_panels_cache[$post_id];

	if ( empty( $panels_data ) ) {
		if ( $post_id == 'home' ) {
			$panels_data = get_option( 'siteorigin_panels_home_page', get_theme_mod( 'panels_home_page', null ) );

			if ( is_null( $panels_data ) ) {
				// Load the default layout
				$layouts = apply_filters( 'siteorigin_panels_prebuilt_layouts', array( ) );
				$panels_data = !empty( $layouts['home'] ) ? $layouts['home'] : current( $layouts );
			}
		}
		else{
			//Allowing rendering for password protected Tab( wc_product_tab ) post types
			if ( post_password_required( $post_id ) && get_post_type( $post_id )!='wc_product_tab' ) return false;
			$panels_data = get_post_meta( $post_id, 'panels_data', true );
		}
	}

	$panels_data = apply_filters( 'siteorigin_panels_data', $panels_data, $post_id );
	if ( empty( $panels_data ) || empty( $panels_data['grids'] ) ) return '';

	//Removing filters for proper functionality
	remove_filter( 'the_content', 'wptexturize' 	 );	//wptexturize : Replaces each & with &#038; unless it already looks like an entity
	remove_filter( 'the_content', 'convert_chars'	 );	//convert_chars : Converts lone & characters into &#38; ( a.k.a. &amp; )
	remove_filter( 'the_content', 'wpautop' );	//wpautop : Adds the Stupid Paragraphs for two line breaks


	// Create the skeleton of the grids
	$grids = array( );
	if ( !empty( $panels_data['grids'] ) && !empty( $panels_data['grids'] ) ) {
		foreach ( $panels_data['grids'] as $gi => $grid ) {
			$gi = intval( $gi );
			$grids[$gi] = array( );
			for ( $i = 0; $i < $grid['cells']; $i++ ) {
				$grids[$gi][$i] = array( );
			}
		}
	}

	if ( !empty( $panels_data['widgets'] ) && is_array( $panels_data['widgets'] ) ) {
		foreach ( $panels_data['widgets'] as $widget ) {
			$grids[intval( $widget['info']['grid'] )][intval( $widget['info']['cell'] )][] = $widget;
		}
	}

	ob_start( );

	global $siteorigin_panels_inline_css;
	if ( empty( $siteorigin_panels_inline_css ) ) $siteorigin_panels_inline_css = '';

	if ( $enqueue_css ) {
		if ( siteorigin_panels_setting( 'inline-css' ) ) {
			wp_enqueue_style( 'siteorigin-panels-front' );
			$siteorigin_panels_inline_css .= siteorigin_panels_generate_css( $post_id, $panels_data );
		}
		else{
			// This is the CSS for the page layout.
			wp_enqueue_style(
				'siteorigin-panels-post-css-'.$post_id,
				add_query_arg(
					array(
						'action' => 'siteorigin_panels_post_css',
						'post' => $post_id,
						// Include this to ensure changes don't get cached by the browser
						'layout' => substr( md5( serialize( $panels_data ) ), 0, 8 )
					 ),
					admin_url( 'admin-ajax.php' )
				 ),
				array( 'siteorigin-panels-front' ),
				POOTLEPAGE_VERSION
			 );
		}
	}

	foreach ( $grids as $gi => $cells ) {

		// This allows other themes and plugins to add html before the row
		echo apply_filters( 'siteorigin_panels_before_row', '', $panels_data['grids'][$gi] );

		$grid_classes = apply_filters( 'siteorigin_panels_row_classes', array( 'panel-grid' ), $panels_data['grids'][$gi] );
		$grid_attributes = apply_filters( 'siteorigin_panels_row_attributes', array(
			'class' => implode( ' ', $grid_classes ),
			'id' => 'pg-' . $post_id . '-' . $gi
		 ), $panels_data['grids'][$gi] );

		echo '<div ';
		foreach ( $grid_attributes as $name => $value ) {
			echo $name.'="'.esc_attr( $value ).'" ';
		}
		echo '>';

		$style_attributes = array( );
		if ( !empty( $panels_data['grids'][$gi]['style']['class'] ) ) {
			$style_attributes['class'] = array( 'panel-row-style-'.$panels_data['grids'][$gi]['style']['class'] );
		}

		// Themes can add their own attributes to the style wrapper
        $styleArray = !empty( $panels_data['grids'][$gi]['style'] ) ? $panels_data['grids'][$gi]['style'] : array( );
		$style_attributes = apply_filters( 'siteorigin_panels_row_style_attributes', $style_attributes, $styleArray );
		if ( !empty( $style_attributes ) ) {
			if ( empty( $style_attributes['class'] ) ) $style_attributes['class'] = array( );
			$style_attributes['class'][] = 'panel-row-style';
			$style_attributes['class'] = array_unique( $style_attributes['class'] );

			echo '<div ';
			foreach ( $style_attributes as $name => $value ) {
				if ( is_array( $value ) ) {
					echo $name.'="'.esc_attr( implode( " ", array_unique( $value ) ) ).'" ';
				}
				else{
					echo $name.'="'.esc_attr( $value ).'" ';
				}
			}
			echo '>';
		}

        if ( isset( $styleArray['background'] ) && isset( $styleArray['background_color_over_image'] ) && $styleArray['background_color_over_image'] == true ) {
			$rowID = '#pg-' . $post_id . '-' . $gi;
            ?>

            <style>
            	/* make this sit under .panel-row-style:before, so background color will be on top on background image */
                <?php echo esc_attr( $rowID ) ?> > .panel-row-style:before {
                	background-color: <?php echo $styleArray['background'] ?>;
                }
				<?php echo esc_attr( $rowID ) ?> > .panel-row-style {
					position: relative;
					z-index: 10;
				}
				<?php echo esc_attr( $rowID ) ?> > .panel-row-style:before {
					position: absolute;
					width: 100%;
					height: 100%;
					content: "";
					top: 0;
					left: 0;
					z-index: 20;
				}
				.panel-grid-cell-container {
					position: relative;
					z-index: 30; /* row content needs to be on top of row background color */
				}
				</style>
				<?php
        }

        echo "<div class='panel-grid-cell-container'>";

		foreach ( $cells as $ci => $widgets ) {
			// Themes can add their own styles to cells
            $cellId = 'pgc-' . $post_id . '-' . $gi  . '-' . $ci;
			$cell_classes = apply_filters( 'siteorigin_panels_row_cell_classes', array( 'panel-grid-cell' ), $panels_data );
			$cell_attributes = apply_filters( 'siteorigin_panels_row_cell_attributes', array(
				'class' => implode( ' ', $cell_classes ),
				'id' => $cellId
			 ), $panels_data );

			echo '<div ';
			foreach ( $cell_attributes as $name => $value ) {
				echo $name.'="'.esc_attr( $value ).'" ';
			}
			echo '>';

			foreach ( $widgets as $pi => $widget_info ) {
				$data = $widget_info;
                $widgetStyle = isset( $data['info']['style'] ) ? json_decode( $data['info']['style'], true ) : pp_get_default_widget_style( );

				unset( $data['info'] );

				// don't do shortcode or it will mess up shortcodes when WP do shortcode at the end
				if ( $widget_info['info']['class'] == 'Pootle_Text_Widget' ) {
					remove_filter( 'widget_text', 'do_shortcode' );

					if ( isset( $widget_info['hide-title'] ) && $widget_info['hide-title'] == '1' ) {
						$widgetStyle['hide-title'] = 'none';
					}
				}

				siteorigin_panels_the_widget( $widget_info['info']['class'], $data, $widgetStyle, $gi, $ci, $pi, $pi == 0, $pi == count( $widgets ) - 1, $post_id );

				if ( $widget_info['info']['class'] == 'Pootle_Text_Widget' ) {
					add_filter( 'widget_text', 'do_shortcode' );
				}

                // post loop css for multiple columns
                if ( $widget_info['info']['class'] == "SiteOrigin_Panels_Widgets_PostLoop" ) {
                    $css = '';

                    if ( isset( $widget_info['column_count'] ) ) {
                        $count = ( int )$widget_info['column_count'];
                        // fix division by zero
                        if ( $count < 1 ) {
                            $count = 1;
                        }
                        $width = ( 100 / $count ) . "%";
                        $cssId = 'panel-' . $post_id . '-' . $gi . '-' . $ci . '-' . $pi;

                        $css .= "#$cssId {\n";
                        $css .= "\t" . "font-size: 0; \n";
                        $css .= "}\n";

                        $css .= "#$cssId > article {\n";
                        $css .= "\t" . "width: " . $width . ";\n";
                        $css .= "\t" . 'display: inline-block;' . "\n";
                        $css .= "\t" . 'box-sizing: border-box;' . "\n";
                        $css .= "\t" . 'padding-right: 10px;' . "\n";
                        $css .= "\t" . 'vertical-align: top;' . "\n";
                        $css .= "}\n";
                    }

                    if ( isset( $widget_info['post_meta_enable'] ) ) {
                        if ( $widget_info['post_meta_enable'] != '1' ) {
                            $cssId = 'panel-' . $post_id . '-' . $gi . '-' . $ci . '-' . $pi;

                            $css .= "#$cssId > article > .post-meta {\n";
                            $css .= "\t" . "display: none;\n";
                            $css .= "}\n";
                        }
                    }
                    echo "<style>\n" . $css . "</style>\n";
                }

                if ( $widget_info['info']['class'] == "Woo_Widget_Component" ) {
                    wp_reset_query( );
                }
			}
			if ( empty( $widgets ) ) echo '&nbsp;';
			echo '</div>';
		}
        echo "</div>";
		echo '</div>';

		if ( !empty( $style_attributes ) ) {
			echo '</div>';
		}

		// This allows other themes and plugins to add html after the row
		echo apply_filters( 'siteorigin_panels_after_row', '', $panels_data['grids'][$gi] );
	}

	$html = ob_get_clean( );

	// Reset the current post
	$siteorigin_panels_current_post = $old_current_post;

	return apply_filters( 'siteorigin_panels_render', $html, $post_id, !empty( $post ) ? $post : null );
}

/**
 * Print inline CSS in the header and footer.
 */
function siteorigin_panels_print_inline_css( ) {
	global $siteorigin_panels_inline_css;

	if ( !empty( $siteorigin_panels_inline_css ) ) {
		?><style type="text/css" media="all"><?php echo $siteorigin_panels_inline_css ?></style><?php
	}

	$siteorigin_panels_inline_css = '';
}
add_action( 'wp_head', 'siteorigin_panels_print_inline_css', 12 );
add_action( 'wp_footer', 'siteorigin_panels_print_inline_css' );


//add_action( 'wp_head', 'pootlepage_page_css', 100 );

add_action( 'after_setup_theme', 'pootlepage_after_setup_theme' );

function pootlepage_after_setup_theme( ) {
    if ( class_exists( 'WF' ) && class_exists( 'WF_Meta' ) ) {
        add_action( 'admin_print_scripts', 'pootlepage_fix_framework_js_error' );
    }
}

function pootlepage_fix_framework_js_error( ) {
    echo "<script>var wooSelectedShortcodeType = typeof wooSelectedShortcodeType == 'undefined' ? '' : wooSelectedShortcodeType;</script>\n";
}

function pootlepage_page_css( ) {
    global $post;

    if ( !is_page( ) ) {
        return;
    }

    $pageSettingsJson = get_post_meta( $post->ID, 'pootlepage-page-settings', true );
    if ( empty( $pageSettingsJson ) ) {
        $pageSettingsJson = '{}';
    }
    $pageSettings = json_decode( $pageSettingsJson, true );

    $backgroundColor = isset( $pageSettings['background'] ) ? $pageSettings['background'] : false;
    $backgroundImage = isset( $pageSettings['background_image'] ) ? $pageSettings['background_image'] : false;
    $backgroundImageRepeat = isset( $pageSettings['background_image_repeat'] ) ? $pageSettings['background_image_repeat'] : false;
    $backgroundImagePosition = isset( $pageSettings['background_image_position'] ) ? $pageSettings['background_image_position'] : false;
    $backgroundImageAttachment = isset( $pageSettings['background_image_attachment'] ) ? $pageSettings['background_image_attachment'] : false;
    $removeSideBar = isset( $pageSettings['remove_sidebar'] ) ? $pageSettings['remove_sidebar'] : false;
    $fullWidth = isset( $pageSettings['full_width'] ) ? $pageSettings['full_width'] : false;
    $keepContentAtSiteWidth = isset( $pageSettings['keep_content_at_site_width'] ) ? $pageSettings['keep_content_at_site_width'] : false;

    $css = '';

    $theme = get_stylesheet( );
    $parentTheme = get_template( );
    if ( $theme == 'twentyfourteen' ) {
        $css .= "#page {\n";
    } elseif ( $theme == 'twentythirteen' ) {
        $css .= "#page {\n";
    } elseif ( $parentTheme == 'make' ) {
        $css .= "#site-header, #site-content { float: none !important; }\n"; // do this orelse#site-wrapper is height 0px
        $css .= "body, #site-header > .site-header-main, #site-content { background: initial !important; }\n";
        $css .= "#site-wrapper {\n";
    } else{
        $css .= "body {\n";
    }

    if ( $backgroundColor ) {
        $css .= "\t" . 'background-color: ' . $backgroundColor . " !important;\n";
    }
    if ( $backgroundImage ) {
        $css .= "\t" . 'background-image: url( "' . $backgroundImage . "\" ) !important;\n";
    }
    if ( $backgroundImageRepeat ) {
        $css .= "\t" . 'background-repeat: repeat' . " !important;\n";
    } else{
        $css .= "\t" . 'background-repeat: no-repeat' . " !important;\n";
    }
    if ( $backgroundImagePosition ) {
        $css .= "\t" . 'background-position: ' . $backgroundImagePosition . " !important;\n";
    }
    if ( $backgroundImageAttachment ) {
        $css .= "\t" . 'background-attachment: ' . $backgroundImageAttachment . " !important;\n";
    }
    $css .= "}\n";

    if ( $removeSideBar ) {
        if ( $theme == 'twentythirteen' ) {
            $css .= "#tertiary { display: none !important; }\n";
        } elseif ( $parentTheme == 'genesis' ) {
            $css .= ".site-inner .sidebar { display: none !important; }\n";
        } elseif ( $parentTheme == 'make' ) {
            $css .= "#sidebar-left, #sidebar-right { display: none !important; }\n";
            $css .= "#site-main { width: 100% !important; margin-left: 0 !important; }\n";
        } else{
            $css .= "#sidebar { display: none !important ; }\n";
        }
    }

    if ( $theme == 'twentyfourteen' ) {
        // theme specific fix
        $css .= ".panel-grid-cell .panel { box-sizing: border-box !important; }\n";
    }

    if ( $fullWidth ) {

        if ( $theme == 'twentyfourteen' ) {
            // if theme is twentyfourteen

            $css .= "#content > article > .entry-content { \n";
            $css .= "\t" . "margin-left: 0; margin-right: 0; width: 100%; max-width: 100%; padding-left: 0; padding-right: 0;\n";
            $css .= "}\n";

            $css .= ".panel-grid { margin-left: 0; margin-right: 0; }\n";
            $css .= ".panel-grid-cell:first-child { padding-left: 0; }\n";
            $css .= ".panel-grid-cell:last-child { padding-right: 0; }\n";

            if ( $keepContentAtSiteWidth ) {
                global $content_width;
                $css .= ".panel-grid-cell-container { margin-left: auto; margin-right: auto; width: " . $content_width . "px; }\n";
            }

        } elseif ( $theme == 'twentythirteen' ) {
            // if theme is twentythirteen

            $css .= "#content > article > .entry-content { padding-left: 0; padding-right: 0; margin-left: 0; margin-right: 0; width: 100%; max-width: 100% }\n";

            $css .= ".panel-grid { margin-left: 0; margin-right: 0; }\n";
            $css .= ".panel-grid-cell:first-child { padding-left: 0; }\n";
            $css .= ".panel-grid-cell:last-child { padding-right: 0; }\n";

            if ( $keepContentAtSiteWidth ) {
                global $content_width;
                $css .= ".panel-grid-cell-container { margin-left: auto; margin-right: auto; width: " . $content_width . "px; }\n";
            }
        } elseif ( $parentTheme == 'genesis' ) {

            $css .= ".site-inner { max-width: 100%; width: 100%; }\n";
            $css .= "main.content { width: 100%; }\n";
            $css .= "main.content > article { padding-left: 0; padding-right: 0; }\n";

            if ( $keepContentAtSiteWidth ) {
                $css .= ".panel-grid-cell-container { margin-left: auto; margin-right: auto; width: 960px; }\n";
            }
        } elseif ( $parentTheme == 'make' ) {

            $css .= "#site-content > .container { \n";
            $css .= "\t" . 'margin-left: 0; margin-right: 0; padding-left: 0; padding-right: 0; width: 100%; max-width: 100%;' . "\n";
            $css .= "}\n";
            $css .= ".panel-grid { margin-left: 0; margin-right: 0; }\n";
            $css .= ".panel-grid-cell { padding-left: 0; padding-right: 0; }\n";

            if ( $keepContentAtSiteWidth ) {
                $css .= ".panel-grid-cell-container { margin-left: auto; margin-right: auto; width: 960px; }\n";
            }
        } else{

            $css .= "#content, #wrapper { max-width: 100% !important; width:100%; margin-left:0; margin-right:0; padding-left: 0 !important; padding-right: 0 !important; }\n";
            $css .= ".panel-grid { margin-left: 0 !important; margin-right: 0 !important; }\n";
            $css .= ".panel-grid-cell { padding: 0 !important; }\n";

            if ( $keepContentAtSiteWidth ) {
                $siteWidth = get_option( 'woo_layout_width', 960 );
                if ( $siteWidth ) {
                    $css .= ".panel-grid-cell-container { margin-left: auto; margin-right: auto; width: " . $siteWidth . "px; }\n";
                }
            }
        }

    }

    $hideElementsJson = get_post_meta( $post->ID, 'pootlepage-hide-elements', true );
    if ( empty( $hideElementsJson ) ) {
        $hideElementsJson = '{}';
    }
    $hideElements = json_decode( $hideElementsJson, true );

    $hideLogoStrapLine = isset( $hideElements['hide_logo_strapline'] ) ? $hideElements['hide_logo_strapline'] : false;
    $hideHeader = isset( $hideElements['hide_header'] ) ? $hideElements['hide_header'] : false;
    $hideMainNavigation = isset( $hideElements['hide_main_navigation'] ) ? $hideElements['hide_main_navigation'] : false;
    $hidePageTitle = isset( $hideElements['hide_page_title'] ) ? $hideElements['hide_page_title'] : false;
    $hideFooterWidgets = isset( $hideElements['hide_footer_widgets'] ) ? $hideElements['hide_footer_widgets'] : false;
    $hideFooter = isset( $hideElements['hide_footer'] ) ? $hideElements['hide_footer'] : false;

    if ( $theme == 'twentyfourteen' ) {
        if ( $hideLogoStrapLine ) {
            $css .= ".site-title { display: none !important; }\n";
            $css .= "#secondary .site-description { display: none !important; }\n";
        }
        if ( $hideHeader ) {
            $css .= "#site-header { display: none !important; }\n";
        }
        if ( $hideMainNavigation ) {
            $css .= "#primary-navigation { display: none !important;  }\n";
        }
        if ( $hidePageTitle ) {
            $css .= "#content > article > header.entry-header { display: none !important; }\n";
        }
        if ( $hideFooterWidgets ) {
            $css .= "#footer-sidebar { display: none !important; }\n";
        }
        if ( $hideFooter ) {
            $css .= "#colophon > .site-info { display: none !important; }\n";
        }
    } elseif ( $theme == 'twentythirteen' ) {
        if ( $hideLogoStrapLine ) {
            $css .= "#masthead .site-title { display: none !important; }\n";
            $css .= "#masthead .site-description { display: none !important; }\n";
        }
        if ( $hideHeader ) {
            $css .= "#masthead .home-link { display: none !important; }\n";
        }
        if ( $hideMainNavigation ) {
            $css .= "#navbar { display: none !important;  }\n";
        }
        if ( $hidePageTitle ) {
            $css .= "#content > article > header.entry-header { display: none !important; }\n";
        }
        if ( $hideFooterWidgets ) {
            $css .= "#secondary { display: none !important; }\n";
        }
        if ( $hideFooter ) {
            $css .= "#colophon > .site-info { display: none !important; }\n";
        }
    } elseif ( $parentTheme == 'genesis' ) {
        if ( $hideLogoStrapLine ) {
            $css .= ".title-area { display: none !important; }\n";
        }
        if ( $hideHeader ) {
            $css .= ".site-header { display: none !important; }\n";
        }
        if ( $hideMainNavigation ) {
            $css .= ".nav-primary { display: none !important; }\n";
        }
        if ( $hidePageTitle ) {
            $css .= ".site-inner .content > article > .entry-header { display: none !important; }\n";
        }
        if ( $hideFooterWidgets ) {
            $css .= ".footer-widgets { display: none !important; }\n";
        }
        if ( $hideFooter ) {
            $css .= ".site-footer { display: none !important; }\n";
        }
    } elseif ( $theme == 'make' ) {

        if ( $hideLogoStrapLine ) {
            $css .= ".site-branding { display: none !important; }\n";
        }
        if ( $hideHeader ) {
            $css .= "#site-header { display: none !important; }\n";
        }
        if ( $hideMainNavigation ) {
            $css .= "#site-navigation { display: none !important; }\n";
        }
        if ( $hidePageTitle ) {
            $css .= "#site-main > article > .entry-header { display: none !important; }\n";
        }
        if ( $hideFooterWidgets ) {
            $css .= "#site-footer .footer-widget-container { display: none !important; }\n";
        }
        if ( $hideFooter ) {
            $css .= "#site-footer { display: none !important; }\n";
        }

    } else{
        if ( $hideLogoStrapLine ) {
            $css .= "#logo { visibility: hidden !important; }\n";
        }
        if ( $hideHeader ) {
            $css .= "#header { display: none !important; }\n";
        }
        if ( $hideMainNavigation ) {
            $css .= "#nav-container, #navigation { display: none !important;  }\n";
        }
        if ( $hidePageTitle ) {
            $css .= "#main > article > header > .entry-title { display: none !important; }\n";
        }
        if ( $hideFooterWidgets ) {
            $css .= "#footer-widgets { display: none !important; }\n";
        }
        if ( $hideFooter ) {
            $css .= "#footer { display: none !important; }\n";
        }
    }

    echo "<style>\n" . $css . "</style>\n";
}

add_filter( 'body_class', 'pootlepage_body_class', 100 );

function pootlepage_body_class( $classes ) {
    // possible layout classes added by Canvas is
    $allLayouts = array( 'one-col', 'two-col-left', 'two-col-right', 'three-col-left', 'three-col-middle', 'three-col-right' );

    global $post;

    if ( !is_page( ) ) {
        return $classes;
    }

    $pageSettingsJson = get_post_meta( $post->ID, 'pootlepage-page-settings', true );
    if ( empty( $pageSettingsJson ) ) {
        $pageSettingsJson = '{}';
    }
    $pageSettings = json_decode( $pageSettingsJson, true );
    $removeSideBar = isset( $pageSettings['remove_sidebar'] ) ? $pageSettings['remove_sidebar'] : false;

    if ( $removeSideBar ) {
        $newClasses = array( );
        for ( $i = 0; $i < count( $classes ); ++$i ) {
            if ( !in_array( $classes[$i], $allLayouts ) ) {
                 $newClasses[] = $classes[$i];
            }
        }
        $newClasses[] = 'one-col';

        return $newClasses;
    } else{
        return $classes;
    }
}

/**
 * Render the widget.
 *
 * @param string $widget The widget class name.
 * @param array $instance The widget instance
 * @param int $grid The grid number.
 * @param int $cell The cell number.
 * @param int $panel the panel number.
 * @param bool $is_first Is this the first widget in the cell.
 * @param bool $is_last Is this the last widget in the cell.
 * @param bool $post_id
 */
function siteorigin_panels_the_widget( $widget, $instance, $widgetStyle, $grid, $cell, $panel, $is_first, $is_last, $post_id = false ) {
	if ( !class_exists( $widget ) ) return;
	if ( empty( $post_id ) ) $post_id = get_the_ID( );

    $panelData = get_post_meta( $post_id, 'panels_data', true );
    if ( !is_array( $panelData ) ) {
        $panelData = array( );
    }

	$the_widget = new $widget;

	$classes = array( 'panel', 'widget' );
	if ( !empty( $the_widget->id_base ) ) $classes[] = 'widget_' . $the_widget->id_base . ' ' . $the_widget->id_base;
	if ( $is_first ) $classes[] = 'panel-first-child';
	if ( $is_last ) $classes[] = 'panel-last-child';
	$id = 'panel-' . $post_id . '-' . $grid . '-' . $cell . '-' . $panel;

    $styleArray = $widgetStyle;
    $inlineStyle = '';

    $widgetStyleFields = pp_pb_widget_styling_fields( );

	$styleWithSelector = '';

    foreach ( $widgetStyleFields as $key => $field ) {

        if ( $field['type'] == 'border' ) {
            // a border field has 2 settings
            $key1 = $key . '-width';
            $key2 = $key . '-color';

            if ( isset( $styleArray[$key1] ) && $styleArray[$key1] != '' ) {
                if ( !is_array( $field['css'] ) ) {
                    $cssArr = array( $field['css'] );
                } else{
                    $cssArr = $field['css'];
                }

                foreach ( $cssArr as $cssProperty ) {
                    $inlineStyle .= $cssProperty . '-width: ' . $styleArray[$key1] . 'px; border-style: solid;';
                }
            }

            if ( isset( $styleArray[$key2] ) && $styleArray[$key2] != '' ) {
                if ( !is_array( $field['css'] ) ) {
                    $cssArr = array( $field['css'] );
                } else{
                    $cssArr = $field['css'];
                }

                foreach ( $cssArr as $cssProperty ) {
                    $inlineStyle .= $cssProperty . '-color: ' . $styleArray[$key2] . ';';
                }
            }

        } else{


            if ( isset( $styleArray[$key] ) && $styleArray[$key] != '' ) {
                if ( !is_array( $field['css'] ) ) {
                    $cssArr = array( $field['css'] );
                } else{
                    $cssArr = $field['css'];
                }

                foreach ( $cssArr as $cssProperty ) {
                    if ( isset( $field['unit'] ) ) {
                        $unit = $field['unit'];
                    } else{
                        $unit = '';
                    }

					if ( !isset( $field['selector'] ) ) {
						$inlineStyle .= $cssProperty . ': ' . $styleArray[$key] . $unit . ';';
					} else{
						$styleWithSelector .= '#' . $id . ' > ' . $field['selector'] . ' { ' .$cssProperty . ': ' . $styleArray[$key] . $unit . '; }';
					}

                }
            }
        }
    }

	$the_widget->widget( array(
		'before_widget' => '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" id="' . $id . '" style="' . $inlineStyle . '" >',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
		'widget_id' => 'widget-' . $grid . '-' . $cell . '-' . $panel
	 ), $instance );

	if ( $styleWithSelector != '' ) {
		echo "<style>\n";
		echo str_replace( 'display','display:none;display',$styleWithSelector );
		echo "</style>\n";
	}

    // Add js file for WooTabs widget
    if ( $widget == 'Woo_Widget_WooTabs' ) {
        if ( function_exists( 'woo_widget_tabs_js' ) ) {
            add_action( 'wp_footer','woo_widget_tabs_js' );
        }
    }
}

/**
 * Add the Edit Home Page item to the admin bar.
 *
 * @param WP_Admin_Bar $admin_bar
 * @return WP_Admin_Bar
 */
function siteorigin_panels_admin_bar_menu( $admin_bar ) {
	/**
	 * @var WP_Query $wp_query
	 */
	global $wp_query;

	if ( ( $wp_query->is_home( ) && $wp_query->is_main_query( ) ) || siteorigin_panels_is_home( ) ) {
		// Check that we support the home page
		if ( !siteorigin_panels_setting( 'home-page' ) || !current_user_can( 'edit_theme_options' ) ) return $admin_bar;
		if ( !get_option( 'siteorigin_panels_home_page_enabled', siteorigin_panels_setting( 'home-page-default' ) ) ) return $admin_bar;

		$admin_bar->add_node( array(
			'id' => 'edit-home-page',
			'title' => __( 'Edit Home Page', 'siteorigin-panels' ),
			'href' => admin_url( 'themes.php?page=so_panels_home_page' )
		 ) );
	}

	return $admin_bar;
}
add_action( 'admin_bar_menu', 'siteorigin_panels_admin_bar_menu', 100 );

/**
 * Handles creating the preview.
 */
function siteorigin_panels_preview( ) {
	if ( isset( $_GET['siteorigin_panels_preview'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'siteorigin-panels-preview' ) ) {
		global $siteorigin_panels_is_preview;
		$siteorigin_panels_is_preview = true;
		// Set the panels home state to true
		if ( empty( filter_input( INPUT_POST, 'post_id' ) ) ) $GLOBALS['siteorigin_panels_is_panels_home'] = true;
		add_action( 'option_siteorigin_panels_home_page', 'siteorigin_panels_preview_load_data' );
		locate_template( siteorigin_panels_setting( 'home-template' ), true );
		exit( );
	}
}
add_action( 'template_redirect', 'siteorigin_panels_preview' );

/**
 * Is this a preview.
 *
 * @return bool
 */
function siteorigin_panels_is_preview( ) {
	global $siteorigin_panels_is_preview;
	return ( bool ) $siteorigin_panels_is_preview;
}

/**
 * Hide the admin bar for panels previews.
 *
 * @param $show
 * @return bool
 */
function siteorigin_panels_preview_adminbar( $show ) {
	if ( !$show ) return false;
	return !( isset( $_GET['siteorigin_panels_preview'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'siteorigin-panels-preview' ) );
}
add_filter( 'show_admin_bar', 'siteorigin_panels_preview_adminbar' );

/**
 * This is a way to show previews of panels, especially for the home page.
 *
 * @param $val
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
 * @return array
 */
function siteorigin_panels_body_class( $classes ) {
	if ( siteorigin_panels_is_panel( ) ) $classes[] = 'siteorigin-panels';
	if ( siteorigin_panels_is_home( ) ) $classes[] = 'siteorigin-panels-home';

	if ( isset( $_GET['siteorigin_panels_preview'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'siteorigin-panels-preview' ) ) {
		// This is a home page preview
		$classes[] = 'siteorigin-panels';
		$classes[] = 'siteorigin-panels-home';
	}

	return $classes;
}
add_filter( 'body_class', 'siteorigin_panels_body_class' );

/**
 * Enqueue the required styles
 */
function siteorigin_panels_enqueue_styles( ) {
	wp_register_style( 'siteorigin-panels-front', plugin_dir_url( __FILE__ ) . 'css/front.css', array( ), POOTLEPAGE_VERSION );
}
add_action( 'wp_enqueue_scripts', 'siteorigin_panels_enqueue_styles', 1 );

function siteorigin_panels_enqueue_scripts( ) {
    $isWooCommerceInstalled = /* isset( $GLOBALS['woocommerce'] ) && */
        function_exists( 'is_product' );

    if ( $isWooCommerceInstalled ) {
        if ( is_product( ) )
            wp_dequeue_script( 'wc-single-product' );
            wp_enqueue_script( 'pb-wc-single-product', plugin_dir_url( __FILE__ ) . 'js/wc-single-product.js', array( 'jquery' ) );
            wp_localize_script( 'pb-wc-single-product', 'wc_single_product_params', apply_filters( 'wc_single_product_params', array(
            'i18n_required_rating_text' => esc_attr__( 'Please select a rating', 'woocommerce' ),
            'review_rating_required'    => get_option( 'woocommerce_review_rating_required' ),
       ) ) );
    }
    wp_register_script( 'general', plugin_dir_url( __FILE__ ) . '/js/canvas-general.js', array( 'jquery', 'third-party' ) );

}
add_action( 'wp_enqueue_scripts', 'siteorigin_panels_enqueue_scripts', 100 );

/**
 * Add current pages as cloneable pages
 *
    // register this to override canvas script

 * @param $layouts
 * @return mixed
 */
function siteorigin_panels_cloned_page_layouts( $layouts ) {
	$pages = get_posts( array(
		'post_type' => 'page',
		'post_status' => array( 'publish', 'draft' ),
		'numberposts' => 200,
	 ) );

	foreach( $pages as $page ) {
		$panels_data = get_post_meta( $page->ID, 'panels_data', true );
		$panels_data = apply_filters( 'siteorigin_panels_data', $panels_data, $page->ID );

		if ( empty( $panels_data ) ) continue;

		$name =  empty( $page->post_title ) ? __( 'Untitled', 'siteorigin-panels' ) : $page->post_title;
		if ( $page->post_status != 'publish' ) $name .= ' ( ' . __( 'Unpublished', 'siteorigin-panels' ) . ' )';

		if ( current_user_can( 'edit_post', $page->ID ) ) {
			$layouts['post-'.$page->ID] = wp_parse_args(
				array(
					'name' => sprintf( __( 'Clone Page: %s', 'siteorigin-panels' ), $name )
				 ),
				$panels_data
			 );
		}
	}

	// Include the current home page in the clone pages.
	$home_data = get_option( 'siteorigin_panels_home_page', null );
	if ( !empty( $home_data ) ) {

		$layouts['current-home-page'] = wp_parse_args(
			array(
				'name' => __( 'Clone: Current Home Page', 'siteorigin-panels' ),
			 ),
			$home_data
		 );
	}

	return $layouts;
}
add_filter( 'siteorigin_panels_prebuilt_layouts', 'siteorigin_panels_cloned_page_layouts', 20 );

/**
 * Add a link to recommended plugins and widgets.
 */
function siteorigin_panels_recommended_widgets( ) {
	// This filter can be used to hide the recommended plugins button.
	if ( ! apply_filters( 'siteorigin_panels_show_recommended', true ) || is_multisite( ) ) return;

	?>
	<p id="so-panels-recommended-plugins">
		<a href="<?php echo admin_url( 'plugin-install.php?tab=favorites&user=siteorigin-pagebuilder' ) ?>" target="_blank"><?php _e( 'Recommended Plugins and Widgets', 'siteorigin-panels' ) ?></a>
		<small><?php _e( 'Free plugins that work well with Page Builder', 'siteorigin-panels' ) ?></small>
	</p>
	<?php
}
add_action( 'siteorigin_panels_after_widgets', 'siteorigin_panels_recommended_widgets' );

add_filter( 'siteorigin_panels_show_recommended', '__return_false' );

/**
 * Add a filter to import panels_data meta key. This fixes serialized PHP.
 */
function siteorigin_panels_wp_import_post_meta( $post_meta ) {
	foreach( $post_meta as $i => $meta ) {
		if ( $meta['key'] == 'panels_data' ) {
			$value = $meta['value'];
			$value = preg_replace( "/[\r\n]/", "<<<br>>>", $value );
			$value = preg_replace( '!s:( \d+ ):"( .*? )";!e', "'s:'.strlen( '$2' ).':\"$2\";'", $value );
			$value = unserialize( $value );
			$value = array_map( 'siteorigin_panels_wp_import_post_meta_map', $value );

			$post_meta[$i]['value'] = $value;
		}
	}

	return $post_meta;
}
add_filter( 'wp_import_post_meta', 'siteorigin_panels_wp_import_post_meta' );

/**
 * A callback that replaces temporary break tag with actual line breaks.
 *
 * @param $val
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
function siteorigin_panels_ajax_action_prebuilt( ) {
	// Get any layouts that the current user could edit.
	$layouts = apply_filters( 'siteorigin_panels_prebuilt_layouts', array( ) );

	if ( empty( $_GET['layout'] ) ) exit( );
	if ( empty( $layouts[$_GET['layout']] ) ) exit( );

	header( 'content-type: application/json' );

	$layout = !empty( $layouts[$_GET['layout']] ) ? $layouts[$_GET['layout']] : array( );
	$layout = apply_filters( 'siteorigin_panels_prebuilt_layout', $layout );

	echo json_encode( $layout );
	exit( );
}
add_action( 'wp_ajax_so_panels_prebuilt', 'siteorigin_panels_ajax_action_prebuilt' );

/**
 * Display a widget form with the provided data
 */
function siteorigin_panels_ajax_widget_form( ) {
	$request = array_map( 'stripslashes_deep', $_REQUEST );
	if ( empty( $request['widget'] ) ) exit( );

	echo siteorigin_panels_render_form( $request['widget'], !empty( $request['instance'] ) ? json_decode( $request['instance'], true ) : array( ), $_REQUEST['raw'] );
	exit( );
}
add_action( 'wp_ajax_so_panels_widget_form', 'siteorigin_panels_ajax_widget_form' );

/**
 * Render a form with all the Page Builder specific fields
 *
 * @param string $widget The class of the widget
 * @param array $instance Widget values
 * @param bool $raw
 * @return mixed|string The form
 */
function siteorigin_panels_render_form( $widget, $instance = array( ), $raw = false ) {
	global $wp_widget_factory;
	if ( empty( $wp_widget_factory->widgets[$widget] ) ) return '';

	$widget_obj = $wp_widget_factory->widgets[$widget];
	if ( !is_a( $widget_obj, 'WP_Widget' ) )
		return;

	if ( $raw && method_exists( $widget_obj, 'update' ) ) $instance = $widget_obj->update( $instance, $instance );

	$widget_obj->id = 'temp';
	$widget_obj->number = '{$id}';

	ob_start( );
	$widget_obj->form( $instance );
	$form = ob_get_clean( );

	// Convert the widget field naming into ones that Page Builder uses
	$exp = preg_quote( $widget_obj->get_field_name( '____' ) );
	$exp = str_replace( '____', '( .*? )', $exp );
	$form = preg_replace( '/'.$exp.'/', 'widgets[{$id}][$1]', $form );

	// Add all the information fields
	return $form;
}

/**
 * Add some action links.
 *
 * @param $links
 * @return array
 */
function siteorigin_panels_plugin_action_links( $links ) {
	$links[] = '<a href="http://siteorigin.com/threads/plugin-page-builder/">'.__( 'Support Forum', 'siteorigin-panels' ).'</a>';
	$links[] = '<a href="http://siteorigin.com/page-builder/#newsletter">'.__( 'Newsletter', 'siteorigin-panels' ).'</a>';
	return $links;
}
//add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'siteorigin_panels_plugin_action_links' );

function pp_pb_load_slider_js( $doLoad ) {
    return true;
}

add_filter( 'woo_load_slider_js', 'pp_pb_load_slider_js' );

require_once( 'page-builder-for-canvas-functions.php' );
// Remove "Canvas Extensions" section from Canvas theme setting page
//add_action( 'init', 'check_main_heading', 0 );

// no longer add the options using old method
//add_filter( 'option_woo_template', 'pp_pb_add_theme_options' );

function pp_pb_add_theme_options ( $options ) {

    $options_pixels = array( );
    $total_possible_numbers = intval( apply_filters( 'woo_total_possible_numbers', 20 ) );
    for ( $i = 0; $i <= $total_possible_numbers; $i++ ) {
        $options_pixels[] = $i . 'px';
    }

    $options[] = array(
        'name' => 'Widget Styling',
        'type' => 'heading'
   );

    $options[] = array(
        'name' => 'Page Builder Widgets',
        'type' => 'subheading'
   );

    $shortname = 'page_builder';

    $options[] = array( "name" => __( 'Page Builder Widget Background Color', 'woothemes' ),
        "desc" => __( 'Pick a custom color for the widget background or add a hex color code e.g. #cccccc', 'woothemes' ),
        "id" => $shortname."_widget_bg",
        "std" => "",
        "type" => "color" );

    $options[] = array( "name" => __( 'Page Builder Widget Border', 'woothemes' ),
        "desc" => __( 'Specify border properties for widgets.', 'woothemes' ),
        "id" => $shortname."_widget_border",
        "std" => array( 'width' => '0','style' => 'solid','color' => '#dbdbdb' ),
        "type" => "border" );

    $options[] = array( "name" => __( 'Page Builder Widget Padding', 'woothemes' ),
        "desc" => __( 'Enter an integer value i.e. 20 for the desired widget padding.', 'woothemes' ),
        "id" => $shortname."_widget_padding",
        "std" => "",
        "type" => array(
            array( 'id' => $shortname. '_widget_padding_tb',
                'type' => 'text',
                'std' => '',
                'meta' => __( 'Top/Bottom', 'woothemes' ) ),
            array( 'id' => $shortname. '_widget_padding_lr',
                'type' => 'text',
                'std' => '',
                'meta' => __( 'Left/Right', 'woothemes' ) )
       ) );

    $options[] = array( "name" => __( 'Page Builder Widget Title', 'woothemes' ),
        "desc" => __( 'Select the typography you want for the widget title.', 'woothemes' ),
        "id" => $shortname."_widget_font_title",
        "std" => array( 'size' => '14','unit' => 'px', 'face' => 'Helvetica, Arial, sans-serif','style' => 'bold','color' => '#555555' ),
        "type" => "typography" );

    $options[] = array( "name" => __( 'Page Builder Widget Title Bottom Border', 'woothemes' ),
        "desc" => __( 'Specify border property for the widget title.', 'woothemes' ),
        "id" => $shortname."_widget_title_border",
        "std" => array( 'width' => '1','style' => 'solid','color' => '#e6e6e6' ),
        "type" => "border" );

    $options[] = array( "name" => __( 'Page Builder Widget Text', 'woothemes' ),
        "desc" => __( 'Select the typography you want for the widget text.', 'woothemes' ),
        "id" => $shortname."_widget_font_text",
        "std" => array( 'size' => '13','unit' => 'px', 'face' => 'Helvetica, Arial, sans-serif','style' => 'thin','color' => '#555555' ),
        "type" => "typography" );

    $options[] = array( "name" => __( 'Page Builder Widget Rounded Corners', 'woothemes' ),
        "desc" => __( 'Set amount of pixels for border radius ( rounded corners ). Will only show in CSS3 compatible browser.', 'woothemes' ),
        "id" => $shortname."_widget_border_radius",
        "type" => "select",
        "options" => $options_pixels );

    $options[] = array( "name" => __( 'Page Builder Tabs Widget Background color', 'woothemes' ),
        "desc" => __( 'Pick a custom color for the tabs widget or add a hex color code e.g. #cccccc', 'woothemes' ),
        "id" => $shortname."_widget_tabs_bg",
        "std" => "",
        "type" => "color" );

    $options[] = array( "name" => __( 'Page Builder Tabs Widget Inside Background Color', 'woothemes' ),
        "desc" => __( 'Pick a custom color for the tabs widget or add a hex color code e.g. #cccccc', 'woothemes' ),
        "id" => $shortname."_widget_tabs_bg_inside",
        "std" => "",
        "type" => "color" );

    $options[] = array( "name" => __( 'Page Builder Tabs Widget Title', 'woothemes' ),
        "desc" => __( 'Select the typography you want for the widget text.', 'woothemes' ),
        "id" => $shortname."_widget_tabs_font",
        "std" => array( 'size' => '12','unit' => 'px', 'face' => 'Helvetica, Arial, sans-serif','style' => 'bold','color' => '#555555' ),
        "type" => "typography" );

    $options[] = array( "name" => __( 'Page Builder Tabs Widget Meta / Tabber Font', 'woothemes' ),
        "desc" => __( 'Select the typography you want for the widget text.', 'woothemes' ),
        "id" => $shortname."_widget_tabs_font_meta",
        "std" => array( 'size' => '11','unit' => 'px', 'face' => 'Helvetica, Arial, sans-serif','style' => 'thin','color' => '#999999' ),
        "type" => "typography" );

    return $options;
}

add_action( 'wp_head', 'pp_pb_option_css' );

function pp_pb_option_css( )
{

    $output = '';

    // Widget Styling
    $widget_font_title = get_option( 'page_builder_widget_font_title', array( 'size' => '14', 'unit' => 'px', 'face' => 'Helvetica, Arial, sans-serif', 'style' => 'bold', 'color' => '#555555' ) );
    $widget_font_text = get_option( 'page_builder_widget_font_text', array( 'size' => '13', 'unit' => 'px', 'face' => 'Helvetica, Arial, sans-serif', 'style' => 'thin', 'color' => '#555555' ) );
    $widget_padding_tb = get_option( 'page_builder_widget_padding_tb', '0' );
    $widget_padding_lr = get_option( 'page_builder_widget_padding_lr', '0' );
    $widget_bg = get_option( 'page_builder_widget_bg', 'transparent' );
    $widget_border = get_option( 'page_builder_widget_border', array( 'width' => '0', 'style' => 'solid', 'color' => '#dbdbdb' ) );
    $widget_title_border = get_option( 'page_builder_widget_title_border', array( 'width' => '1', 'style' => 'solid', 'color' => '#e6e6e6' ) );
    $widget_border_radius = get_option( 'page_builder_widget_border_radius', '0px' );

    // in Visual Editor, dont set underline for h3
    $output .= '.widget_pootle-text-widget > .textwidget h3 { border-bottom: none !important; }';

    $widget_title_css = '';
    if ( $widget_font_title )
        $widget_title_css .= 'font:'.$widget_font_title["style"].' '.$widget_font_title["size"].$widget_font_title["unit"].'/1.2em '.stripslashes( $widget_font_title["face"] ).';color:'.$widget_font_title["color"].';';
    if ( $widget_title_border )
        $widget_title_css .= 'border-bottom:'.$widget_title_border["width"].'px '.$widget_title_border["style"].' '.$widget_title_border["color"].' !important;';
    if ( isset( $widget_title_border["width"] ) AND $widget_title_border["width"] == 0 )
        $widget_title_css .= 'margin-bottom:0 !important;';

    if ( $widget_title_css != '' )
        $output .= '.panel-grid-cell .widget h3.widget-title {'. $widget_title_css . '}'. "\n";


    if ( $widget_title_border )
        $output .= '.panel-grid-cell .widget_recent_comments li{ border-color: '.$widget_title_border["color"].';}'. "\n";

    if ( $widget_font_text )
        $output .= '.panel-grid-cell .widget p, .panel-grid-cell .widget .textwidget { ' . pp_pb_generate_font_css( $widget_font_text, 1.5 ) . ' }' . "\n";

    $widget_css = '';
    if ( $widget_font_text )
        $widget_css .= 'font:'.$widget_font_text["style"].' '.$widget_font_text["size"].$widget_font_text["unit"].'/1.5em '.stripslashes( $widget_font_text["face"] ).';color:'.$widget_font_text["color"].';';

    if ( !$widget_padding_lr ) {
        $widget_css .= 'padding-left: 0; padding-right: 0;';
    } else{
        $widget_css .= 'padding-left: ' . $widget_padding_lr . 'px ; padding-right: ' . $widget_padding_lr . 'px;';
    }
    if ( !$widget_padding_tb ) {
        $widget_css .= 'padding-top: 0; padding-bottom: 0;';
    } else{
        $widget_css .= 'padding-top: ' . $widget_padding_tb . 'px ; padding-bottom: ' . $widget_padding_tb . 'px;';
    }

    if ( $widget_bg ) {
        $widget_css .= 'background-color:'.$widget_bg.';';
    } else{
        $widget_css .= 'background-color: transparent;';
    }


    if ( $widget_border["width"] > 0 )
        $widget_css .= 'border:'.$widget_border["width"].'px '.$widget_border["style"].' '.$widget_border["color"].';';
    if ( $widget_border_radius )
        $widget_css .= 'border-radius:'.$widget_border_radius.';-moz-border-radius:'.$widget_border_radius.';-webkit-border-radius:'.$widget_border_radius.';';

    if ( $widget_css != '' )
        $output .= '.panel-grid-cell .widget {'. $widget_css . '}'. "\n";

    if ( $widget_border["width"] > 0 )
        $output .= '.panel-grid-cell #tabs {border:'.$widget_border["width"].'px '.$widget_border["style"].' '.$widget_border["color"].';}'. "\n";

    // Tabs Widget
    $widget_tabs_bg = get_option( 'page_builder_widget_tabs_bg', 'transparent' );
    $widget_tabs_bg_inside = get_option( 'page_builder_widget_tabs_bg_inside', '' );
    $widget_tabs_font = get_option( 'page_builder_widget_tabs_font', array( 'size' => '12','unit' => 'px', 'face' => 'Helvetica, Arial, sans-serif','style' => 'bold','color' => '#555555' ) );
    $widget_tabs_font_meta = get_option( 'page_builder_widget_tabs_font_meta', array( 'size' => '11','unit' => 'px', 'face' => 'Helvetica, Arial, sans-serif','style' => 'thin','color' => '' ) );

    if ( $widget_tabs_bg ) {
        $output .= '.panel-grid-cell #tabs, .panel-grid-cell .widget_woodojo_tabs .tabbable {background-color:' . $widget_tabs_bg . ';}' . "\n";
    } else{
        $output .= '.panel-grid-cell #tabs, .panel-grid-cell .widget_woodojo_tabs .tabbable {background-color: transparent;}' . "\n";
    }

    if ( $widget_tabs_bg_inside ) {
        $output .= '.panel-grid-cell #tabs .inside, .panel-grid-cell #tabs ul.wooTabs li a.selected, .panel-grid-cell #tabs ul.wooTabs li a:hover {background-color:' . $widget_tabs_bg_inside . ';}' . "\n";
    } else{
        //$output .= '.panel-grid-cell #tabs .inside, .panel-grid-cell #tabs ul.wooTabs li a.selected, .panel-grid-cell #tabs ul.wooTabs li a:hover {background-color: transparent; }' . "\n";
    }

    if ( $widget_tabs_font )
        $output .= '.panel-grid-cell #tabs .inside li a, .panel-grid-cell .widget_woodojo_tabs .tabbable .tab-pane li a { ' . pp_pb_generate_font_css( $widget_tabs_font, 1.5 ) . ' }'. "\n";
    if ( $widget_tabs_font_meta )
        $output .= '.panel-grid-cell #tabs .inside li span.meta, .panel-grid-cell .widget_woodojo_tabs .tabbable .tab-pane li span.meta { ' . pp_pb_generate_font_css( $widget_tabs_font_meta, 1.5 ) . ' }'. "\n";
    $output .= '.panel-grid-cell #tabs ul.wooTabs li a, .panel-grid-cell .widget_woodojo_tabs .tabbable .nav-tabs li a { ' . pp_pb_generate_font_css( $widget_tabs_font_meta, 2 ) . ' }'. "\n";


//    global $siteorigin_panels_inline_css;
//    if ( !empty( $siteorigin_panels_inline_css ) ) {
//        $output .= $siteorigin_panels_inline_css;
//    }

	$removeListSetting = siteorigin_panels_setting( 'remove-list-padding' );
	if ( $removeListSetting == true ) {
		$output .= ".entry .panel-grid .widget ul, .entry .panel-grid .widget ol { padding-left: 0; }\n";
	}

    echo "<style>\n" . $output . "\n" . "</style>\n";
}

function pp_pb_generate_font_css( $option, $em = '1' ) {

    // Test if font-face is a Google font
    global $google_fonts;
    if ( is_array( $google_fonts ) ) {
        foreach ( $google_fonts as $google_font ) {

            // Add single quotation marks to font name and default arial sans-serif ending
            if ( $option['face'] == $google_font['name'] )
                $option['face'] = "'" . $option['face'] . "', arial, sans-serif";

        } // END foreach
    }

    if ( !@$option['style'] && !@$option['size'] && !@$option['unit'] && !@$option['color'] )
        return 'font-family: '.stripslashes( $option["face"] ).' !important;';
   else
        return 'font:'.$option['style'].' '.$option['size'].$option['unit'].'/'.$em.'em '.stripslashes( $option['face'] ).' !important; color:'.$option['color'].' !important;';
} // End pp_pb_generate_font_css( )

add_action( 'admin_init', 'pp_pb_widget_area_init' );
function pp_pb_widget_area_init( ) {

    global $woo_shortcode_generator;
    global $pagenow;

    if ( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) && get_user_option( 'rich_editing' ) == 'true' && ( in_array( $pagenow, array( 'widgets.php' ) ) ) )  {

        // Output the markup in the footer.
        add_action( 'admin_footer', array( $woo_shortcode_generator, 'output_dialog_markup' ) );

        // Add the tinyMCE buttons and plugins.
        add_filter( 'mce_buttons', array( $woo_shortcode_generator, 'filter_mce_buttons' ) );
        add_filter( 'mce_external_plugins', array( $woo_shortcode_generator, 'filter_mce_external_plugins' ) );

        // Register the colourpicker JavaScript.
        wp_register_script( 'woo-colourpicker', esc_url( $woo_shortcode_generator->framework_url( ) . 'js/colorpicker.js' ), array( 'jquery' ), '3.6', true ); // Loaded into the footer.
        wp_enqueue_script( 'woo-colourpicker' );

        // Register the colourpicker CSS.
        wp_register_style( 'woo-colourpicker', esc_url( $woo_shortcode_generator->framework_url( ) . 'css/colorpicker.css' ) );
        wp_enqueue_style( 'woo-colourpicker' );

        wp_register_style( 'woo-shortcode-icon', esc_url( $woo_shortcode_generator->framework_url( ) . 'css/shortcode-icon.css' ) );
        wp_enqueue_style( 'woo-shortcode-icon' );

        // Register the custom CSS styles.
        wp_register_style( 'woo-shortcode-generator', esc_url( $woo_shortcode_generator->framework_url( ) . 'css/shortcode-generator.css' ) );
        wp_enqueue_style( 'woo-shortcode-generator' );

//        add_action( 'admin_head', 'pp_pb_widget_area_head' );
    }
} // End init( )

function pp_pb_widget_styling_fields( ) {
    return array(
		'hide-title' => array(
			'name' => 'Hide widget title on site',
			'type' => 'checkbox',
			'value' => 'none',
			'selector' => '.widget-title',
			'css' => 'display'
		 ),
        'background-color' => array(
            'name' => 'Widget background color',
            'type' => 'color',
            'css' => 'background-color',
       ),
        'border' => array(
            'name' => 'Widget border',
            'type' => 'border',
            'css' => 'border'
       ),
        'padding-top-bottom' => array(
            'name' => 'Widget top/bottom padding',
            'type' => 'number',
            'min' => '0',
            'max' => '100',
            'step' => '1',
            'unit' => '%',
            'css' => array( 'padding-top', 'padding-bottom' )
       ),
        'padding-left-right' => array(
            'name' => 'Widget left/right padding',
            'type' => 'number',
            'min' => '0',
            'max' => '100',
            'step' => '1',
            'unit' => '%',
            'css' => array( 'padding-left', 'padding-right' )
       ),
        'rounded-corners' => array(
            'name' => 'Widget rounded corners',
            'type' => 'number',
            'min' => '0',
            'max' => '100',
            'step' => '1',
            'unit' => 'px',
            'css' => 'border-radius'
       ),
   );
}

// No need to fix, since same as normal post edit screen
//function pp_pb_widget_area_head( ) {
//    echo "<style>\n" .
//        "#TB_ajaxContent { width: auto !important; height: auto !important; }\n" .
//    "</style>\n";
//}

//$pootlepageCustomizer = new PootlePage_Customizer( );
$PootlePageFile = __FILE__;

add_action( 'after_setup_theme', 'pp_pb_wf_settings' );

function pp_pb_wf_settings( ) {
    require_once plugin_dir_path( __FILE__ ) . 'inc/class-pp-pb-wf-fields.php';
    require_once plugin_dir_path( __FILE__ ) . 'inc/class-pp-pb-wf-fields-settings.php';
    require_once plugin_dir_path( __FILE__ ) . 'inc/class-pp-pb-wf-settings.php';
    $GLOBALS['PP_PB_WF_Settings'] = new PP_PB_WF_Settings( );
}


add_action( 'init', 'pp_pootlepage_updater' );
function pp_pootlepage_updater( )
{
    if ( !function_exists( 'get_plugin_data' ) ) {
        include( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $data = get_plugin_data( __FILE__ );
    $wptuts_plugin_current_version = $data['Version'];
    $wptuts_plugin_remote_path = 'http://www.pootlepress.com/?updater=1';
    $wptuts_plugin_slug = plugin_basename( __FILE__ );
    new Pootlepress_Updater ( $wptuts_plugin_current_version, $wptuts_plugin_remote_path, $wptuts_plugin_slug );
}

add_action( 'in_plugin_update_message-page-builder-for-canvas-master/page-builder-for-canvas.php', 'pp_pb_in_plugin_update_message', 10, 2 );

//$r is a object
function pp_pb_in_plugin_update_message( $args, $r ) {
    if ( $args['update'] ) {
        $transient_name = 'pp_pb_upgrade_notice_' . $args['Version'];

        if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {

            $response = wp_remote_post( $args['url'], array( 'body' => array( 'action' => 'upgrade-notice', 'plugin' => $args['slug'] ) ) );

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
