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

require_once 'inc/class-abstract.php';

final class Pootle_Page_Builder extends Pootle_Page_Builder_Abstract {

	/**
	 * @var Pootle_Page_Builder instance of Pootle_Page_Builder
	 */
	protected static $instance;

	/**
	 * @var Pootle_Page_Builder_Admin Admin class instance
	 * @access protected
	 */
	protected $admin;

	/**
	 * @var Pootle_Page_Builder_Public Public class instance
	 * @access protected
	 */
	protected $public;

	/**
	 * Magic __construct
	 * @access private
	 * @since 0.9.0
	 */
	protected function __construct() {
		$this->constants();
		$this->includes();
		$this->actions();
	}

	/**
	 * Set the constants
	 * @access private
	 * @since 0.9.0
	 */
	protected function constants() {
		define( 'POOTLEPAGE_VERSION', '2.9.9' );
		define( 'POOTLEPAGE_BASE_FILE', __FILE__ );
		define( 'POOTLEPAGE_DIR', __DIR__ . '/' );
		define( 'POOTLEPAGE_URL', plugin_dir_url( __FILE__ ) );
		// Tracking presence of version older than 3.0.0
		if ( - 1 == version_compare( get_option( 'siteorigin_panels_initial_version' ), '2.5' ) ) {
			define( 'POOTLEPAGE_OLD_V', get_option( 'siteorigin_panels_initial_version' ) );
		}
	}

	/**
	 * Include the required files
	 * @access protected
	 * @since 0.9.0
	 */
	protected function includes() {

		/** Variables used throughout the plugin */
		require_once POOTLEPAGE_DIR . 'inc/vars.php';
		require_once POOTLEPAGE_DIR . 'page-builder-for-canvas-functions.php';
		/** Enhancements */
		require_once POOTLEPAGE_DIR . '/inc/enhancements-and-fixes.php';

		//Admin
		require_once POOTLEPAGE_DIR . 'inc/class-admin.php';
		$this->admin = Pootle_Page_Builder_Admin::instance();

		//Public
		require_once POOTLEPAGE_DIR . 'inc/class-public.php';
		$this->public = Pootle_Page_Builder_Public::instance();

		require_once POOTLEPAGE_DIR . 'inc/cxpb-support.php';

		require_once POOTLEPAGE_DIR . 'widgets/basic.php';
		require_once POOTLEPAGE_DIR . 'inc/vantage-extra.php';
	}

	/**
	 * Adds the actions anf filter hooks for plugin functioning
	 * @access protected
	 * @since 0.9.0
	 */
	private function actions() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		add_action( 'in_plugin_update_message-' . plugin_basename( __FILE__ ), 'plugin_update_message', 10, 2 );
	}

	/**
	 * Hook for activation of Page Builder.
	 * @since 0.9.0
	 */
	public function activate() {
		add_option( 'ppb_initial_version', POOTLEPAGE_VERSION, '', 'no' );

		$current_user = wp_get_current_user();

		//Get first name if set
		$username = '';
		if ( ! empty( $current_user->user_firstname ) ) {
			$username = " {$current_user->user_firstname}";
		}

		$welcome_message = "<b>Hey{$username}! Welcome to Page builder.</b> You're all set to start building stunning pages!<br><a class='button pootle' href='" . admin_url( '/admin.php?page=page_builder_home' ) . "'>Get started</a>";

		ppb_add_admin_notice( 'welcome', $welcome_message, 'updated pootle' );
	}

	/**
	 * Initialize the language files
	 * @action plugins_loaded
	 * @since 0.9.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'ppb-panels', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/**
	 * Enqueue admin scripts and styles
	 * @global $pagenow
	 * @action admin_notices
	 */
	public function enqueue(){
		global $pagenow;

		wp_enqueue_style( 'pootlepage-main-admin', plugin_dir_url( __FILE__ ) . 'css/main-admin.css', array(), POOTLEPAGE_VERSION );

		if ( $pagenow == 'admin.php' && false !== strpos( filter_input( INPUT_GET, 'page' ), 'page_builder' ) ) {
			wp_enqueue_script( 'ppb-settings-script', plugin_dir_url( __FILE__ ) . 'js/settings.js', array() );
			wp_enqueue_style( 'ppb-settings-styles', plugin_dir_url( __FILE__ ) . 'css/settings.css', array() );
			wp_enqueue_style( 'pootlepage-option-admin', plugin_dir_url( __FILE__ ) . 'css/option-admin.css', array(), POOTLEPAGE_VERSION );
			wp_enqueue_script( 'pootlepage-option-admin', plugin_dir_url( __FILE__ ) . 'js/option-admin.js', array( 'jquery' ), POOTLEPAGE_VERSION );
		}
	}

	/**
	 * Outputs admin notices
	 * @since 1.0.0
	 * @action admin_notices
	 */
	public function admin_notices() {

		$notices = get_option( 'pootle_page_admin_notices', array() );

		delete_option( 'pootle_page_admin_notices' );

		if ( 0 < count( $notices ) ) {
			$html = '';
			foreach ( $notices as $k => $v ) {
				$html .= '<div id="' . esc_attr( $k ) . '" class="fade ' . esc_attr( $v['type'] ) . '">' . wpautop( $v['message'] ) . '</div>' . "\n";
			}
			echo $html;
		}
	}

	/**
	 * Add plugin action links.
	 * @param $links
	 * @TODO Shramee Use this
	 * @action plugin_action_links_$file
	 * @return array
	 */
	function siteorigin_panels_plugin_action_links( $links ) {
		$links[] = '<a href="http://siteorigin.com/threads/plugin-page-builder/">' . __( 'Support Forum', 'ppb-panels' ) . '</a>';
		$links[] = '<a href="http://siteorigin.com/page-builder/#newsletter">' . __( 'Newsletter', 'ppb-panels' ) . '</a>';

		return $links;
	}

	/**
	 * Filter update message
	 * @param array $args Plugin metadata.
	 * @param array $r Metadata about the available plugin update
	 * @action in_plugin_update_message-$file
	 */
	public function plugin_update_message( $args, $r ) {
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
} //class Pootle_Page_Builder

//Instantiating Pootle_Page_Builder
Pootle_Page_Builder::instance();