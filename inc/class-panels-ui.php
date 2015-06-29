<?php
/**
 * Created by PhpStorm.
 * User: shramee
 * Date: 26/6/15
 * Time: 4:01 PM
 */
final class Pootle_Page_Builder_Admin_UI extends Pootle_Page_Builder_Abstract {
	/**
	 * @var Pootle_Page_Builder_Admin_UI
	 * @access protected
	 */
	protected static $instance;

	/**
	 * Magic __construct
	 * @since 0.9.0
	 */
	protected function __construct() {
		$this->hooks();
	}

	/**
	 * Adds the actions and filter hooks for plugin functioning
	 * @since 0.9.0
	 */
	private function hooks() {
		add_action( 'add_meta_boxes', array( $this, 'metabox' ) );
		add_action( 'admin_print_styles-post-new.php', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_print_styles-post.php', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_print_styles-post-new.php', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_print_styles-post.php', array( $this, 'enqueue_scripts' ) );
		add_filter( 'siteorigin_panels_prebuilt_layouts', array( $this, 'enqueue_color_picker' ) );
		add_action( 'wp_ajax_so_panels_prebuilt', array( $this, 'ajax_action_prebuilt' ) );

	}

	/**
	 * Callback to register the Panels Metaboxes
	 * @since 0.9.0
	 */
	public function metabox() {
		foreach ( pootle_pb_settings( 'post-types' ) as $type ) {
			add_meta_box( 'so-panels-panels', __( 'Page Builder', 'ppb-panels' ), array( $this, 'metabox_render' ), $type, 'advanced', 'high' );
		}
	}

	/**
	 * Render a panel metabox.
	 *
	 * @param $post
	 */
	public function metabox_render( $post ) {
		include POOTLEPAGE_DIR . '/tpl/metabox-panels.php';
	}

	/**
	 * Enqueue the admin panel styles
	 *
	 * @action admin_print_styles-post-new.php
	 * @action admin_print_styles-post.php
	 */
	public function enqueue_styles() {
		$screen = get_current_screen();
		if ( in_array( $screen->id, pootle_pb_settings( 'post-types' ) ) || $screen->base == 'appearance_page_so_panels_home_page' ) {
			wp_enqueue_style( 'so-panels-admin', POOTLEPAGE_URL . 'css/admin.css', array(), POOTLEPAGE_VERSION );
			wp_enqueue_style( 'ppb-chosen-style', POOTLEPAGE_URL . 'js/chosen/chosen.css' );

			global $wp_version;
			if ( version_compare( $wp_version, '3.9.beta.1', '<' ) ) {
				// Versions before 3.9 need some custom jQuery UI styling
				wp_enqueue_style( 'so-panels-admin-jquery-ui', POOTLEPAGE_URL . 'css/jquery-ui.css', array(), POOTLEPAGE_VERSION );
			} else {
				wp_enqueue_style( 'wp-jquery-ui-dialog' );
			}
			do_action( 'siteorigin_panel_enqueue_admin_styles' );
		}
	}

	/**
	 * Enqueue the panels admin scripts
	 *
	 * @action admin_print_scripts-post-new.php
	 * @action admin_print_scripts-post.php
	 * @uses Pootle_Page_Builder_Admin_UI::enqueue_color_picker()
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		if ( $screen->base == 'post' && in_array( $screen->id, pootle_pb_settings( 'post-types' ) ) ) {
			wp_enqueue_script( 'jquery-ui-resizable' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-button' );

			wp_enqueue_script( 'so-undomanager', POOTLEPAGE_URL . 'js/undomanager.min.js', array(), 'fb30d7f' );
			wp_enqueue_script( 'ppb-chosen', POOTLEPAGE_URL . 'js/chosen/chosen.jquery.min.min.js', array( 'jquery' ), POOTLEPAGE_VERSION );

			$deps = array(
				'jquery',
				'jquery-ui-resizable',
				'jquery-ui-sortable',
				'jquery-ui-slider',
				'jquery-ui-dialog',
				'jquery-ui-button',
				'jquery-ui-tabs',
			);

			wp_enqueue_script( 'so-panels-admin', POOTLEPAGE_URL . 'js/panels.admin.js', $deps, POOTLEPAGE_VERSION );
			wp_enqueue_script( 'so-panels-admin-sticky', POOTLEPAGE_URL . 'js/panels.admin.sticky.js', array( 'jquery' ), POOTLEPAGE_VERSION );
			wp_enqueue_script( 'so-panels-admin-panels', POOTLEPAGE_URL . 'js/panels.admin.panels.js', array( 'jquery' ), POOTLEPAGE_VERSION );
			wp_enqueue_script( 'so-panels-admin-grid', POOTLEPAGE_URL . 'js/panels.admin.grid.js', array( 'jquery' ), POOTLEPAGE_VERSION );
			wp_enqueue_script( 'so-panels-admin-prebuilt', POOTLEPAGE_URL . 'js/panels.admin.prebuilt.js', array( 'jquery' ), POOTLEPAGE_VERSION );
			wp_enqueue_script( 'so-panels-admin-tooltip', POOTLEPAGE_URL . 'js/panels.admin.tooltip.min.js', array( 'jquery' ), POOTLEPAGE_VERSION );
			wp_enqueue_script( 'so-panels-admin-media', POOTLEPAGE_URL . 'js/panels.admin.media.min.js', array( 'jquery' ), POOTLEPAGE_VERSION );
			wp_enqueue_script( 'so-panels-admin-styles', POOTLEPAGE_URL . 'js/panels.admin.styles.js', array( 'jquery' ), POOTLEPAGE_VERSION );

			wp_enqueue_script( 'row-options', POOTLEPAGE_URL . 'js/row.options.admin.js', array( 'jquery' ) );

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
						'undo'      => __( 'Want to undo?', 'ppb-panels' ),
						'add'       => __( 'Add', 'ppb-panels' ),
					),
					'messages' => array(
						'deleteColumns' => __( 'Columns deleted', 'ppb-panels' ),
						'deleteWidget'  => __( 'Content deleted', 'ppb-panels' ),
						'confirmLayout' => __( 'Are you sure you want to load this layout? It will overwrite your current page.', 'ppb-panels' ),
						'editWidget'    => __( 'Edit %s Widget', 'ppb-panels' ),
						'styleWidget'   => __( 'Style Widget', 'ppb-panels' )
					),
				),
			) );

			// this is the data of the widget and row that have been setup
			$panels_data = $this->get_current_admin_panels_data();

			// Add in the forms
			if ( count( $panels_data ) > 0 ) {
				// load all data even if no widget inside, so row styling will be loaded
				wp_localize_script( 'so-panels-admin', 'panelsData', $panels_data );
			}

			// Set up the row styles
			wp_localize_script( 'so-panels-admin', 'panelsStyleFields', siteorigin_panels_style_get_fields() );

			$this->enqueue_color_picker();

			wp_localize_script( 'pp-pb-color-picker', 'wpColorPickerL10n', array(
				'clear'         => __( 'Clear' ),
				'defaultString' => __( 'Default' ),
				'pick'          => __( 'Select Color' ),
				'current'       => __( 'Current Color' ),
			) );

			wp_enqueue_style( 'wp-color-picker' );

			// This gives panels a chance to enqueue scripts too, without having to check the screen ID.
			do_action( 'ppb_enqueue_admin_scripts' );
			do_action( 'sidebar_admin_setup' );
		}
	}

	/**
	 * Get the Page Builder data for the current admin page.
	 * @return array
	 * @since 0.9.0
	 */
	public function get_current_admin_panels_data() {
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

	/**
	 * Enqueue rgba supporting color picker
	 * @since 0.9.0
	 */
	public function enqueue_color_picker() {
		wp_dequeue_script( "iris" );
		wp_enqueue_script( "pp-pb-iris", POOTLEPAGE_URL . 'js/iris.js', array(
			'jquery-ui-draggable',
			'jquery-ui-slider',
			'jquery-touch-punch'
		) );
		wp_enqueue_script( 'pp-pb-color-picker', POOTLEPAGE_URL . 'js/color-picker-custom.js', array( 'pp-pb-iris' ) );
	}

	/**
	 * Add current pages as cloneable pages
	 * @param $layouts
	 * @return mixed
	 * @since 0.9.0
	 */
	function cloned_page_layouts( $layouts ) {
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

	/**
	 * Admin ajax handler for loading a prebuilt layout.
	 * @since 0.9.0
	 */
	function ajax_action_prebuilt() {
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
}

//Instantiating Pootle_Page_Builder_Content_Block class
Pootle_Page_Builder_Admin_UI::instance();