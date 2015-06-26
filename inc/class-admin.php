<?php
/**
 * Created by PhpStorm.
 * User: shramee
 * Date: 26/6/15
 * Time: 6:39 PM
 */
class Pootle_Page_Builder_Admin extends Pootle_Page_Builder_Abstract {
	/**
	 * @var Pootle_Page_Builder_Admin
	 */
	protected static $instance;

	/**
	 * Magic __construct
	 * @since 1.0.0
	 */
	protected function __construct() {
		$this->includes();
		$this->actions();
	}

	protected function includes() {

		/** Create settings pages */
		require_once POOTLEPAGE_DIR . 'inc/options.php';
		/** Pootle Page Builder user interface */
		require_once POOTLEPAGE_DIR . 'inc/class-panels-ui.php';
		/** Content block - Editor panel and output */
		require_once POOTLEPAGE_DIR . 'inc/class-content-blocks.php';
		/** Take care of styling fields */
		require_once POOTLEPAGE_DIR . 'inc/styles.php';
		/** Saving PPB meta data in revision post types. */
		require_once POOTLEPAGE_DIR . 'inc/revisions.php';
		/** Filtering the PB content */
		require_once POOTLEPAGE_DIR . 'inc/copy.php';
		/** Pootle Press Updater */
		require_once POOTLEPAGE_DIR . 'inc/class-pootlepress-updater.php';
		/** More styling */
		require_once POOTLEPAGE_DIR . 'inc/vantage-extra.php';
	}

	/**
	 * Adds the actions anf filter hooks for plugin functioning
	 * @access protected
	 * @since 0.9.0
	 */

	private function actions() {
		add_action( 'load-page.php', array( $this, 'add_help_tab' ), 12 );
		add_action( 'load-post-new.php', array( $this, 'add_help_tab' ), 12 );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		add_action( 'init', array( $this, 'pp_updater' ) );
	}

	/**
	 * Add a help tab to pages with panels.
	 * @param $prefix
	 * @action load-post-new.php, load-page.php
	 */
	public function add_help_tab( $prefix ) {
		$screen = get_current_screen();
		if ( $screen->base == 'post' && in_array( $screen->id, siteorigin_panels_setting( 'post-types' ) ) ) {
			$screen->add_help_tab( array(
				'id'       => 'panels-help-tab', //unique id for the tab
				'title'    => __( 'Page Builder', 'ppb-panels' ), //unique visible title for the tab
				'callback' => array( $this, 'render_help_tab' )
			) );
		}
	}

	/**
	 * Display the content for the help tab.
	 * @TODO Make it more useful
	 */
	public function render_help_tab() {
		echo '<p>';
		_e( 'You can use Pootle Page Builder to create amazing pages, use addons to extend functionality.', 'siteorigin-panels' );
		_e( 'The page layouts are responsive and fully customizable.', 'siteorigin-panels' );
		echo '</p>';
	}

	/**
	 * Save the panels data
	 *
	 * @param $post_id
	 * @param $post
	 *
	 * @action save_post
	 */
	public function save_post( $post_id, $post ) {
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

	/**
	 * Initiates pootlepress updater
	 * @action init
	 */
	public function pp_updater() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			include( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$data                          = get_plugin_data( __FILE__ );
		$wptuts_plugin_current_version = $data['Version'];
		$wptuts_plugin_remote_path     = 'http://www.pootlepress.com/?updater=1';
		$wptuts_plugin_slug            = plugin_basename( __FILE__ );
		new Pootlepress_Updater ( $wptuts_plugin_current_version, $wptuts_plugin_remote_path, $wptuts_plugin_slug );
	}
}