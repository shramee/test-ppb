<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once "class-admin-helper.php";

/**
 * Class that provides admin functionalities
 *
 * @package Black_Studio_TinyMCE_Widget
 * @since 2.0.0
 */

if ( ! class_exists( 'Black_Studio_TinyMCE_Admin' ) ) {

	final class Black_Studio_TinyMCE_Admin extends Black_Studio_TinyMCE_Admin_Helper {

		/**
		 * The single instance of the class
		 *
		 * @var object
		 * @since 2.0.0
		 */
		protected static $_instance = null;

		/**
		 * Array containing the plugin links
		 *
		 * @var array
		 * @since 2.0.0
		 */
		protected $links;

		/**
		 * Return the single class instance
		 *
		 * @return object
		 * @since 2.0.0
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Class constructor
		 *
		 * @uses add_action()
		 * @uses add_filter()
		 * @uses get_option()
		 * @uses get_bloginfo()
		 *
		 * @global object $wp_embed
		 * @return void
		 * @since 2.0.0
		 */
		protected function __construct() {
			// Register action and filter hooks
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ), 20 );
		}

		/**
		 * Prevent the class from being cloned
		 *
		 * @return void
		 * @since 2.0.0
		 */
		protected function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; uh?' ), '2.0' );
		}

		/**
		 * Load language files
		 *
		 * @uses load_plugin_textdomain()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'black-studio-tinymce-widget', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
		}

		/**
		 * Checks if the plugin admin code should be loaded
		 *
		 * @uses apply_filters()
		 *
		 * @global string $pagenow
		 * @return void
		 * @since 2.0.0
		 */
		public function enabled() {
			global $pagenow;
			$enabled_pages = apply_filters( 'black_studio_tinymce_enable_pages', array(
				'widgets.php',
				'customize.php',
				'admin-ajax.php'
			) );

			return apply_filters( 'black_studio_tinymce_enable', in_array( $pagenow, $enabled_pages ) );
		}

		/**
		 * Add actions and filters ( only in widgets admin page )
		 *
		 * @uses add_action()
		 * @uses add_filter()
		 * @uses do_action()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function admin_init() {
			$this->init_links();
			add_action( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
			if ( $this->enabled() ) {
				add_action( 'admin_head', array( $this, 'enqueue_media' ) );
				add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ) );
				add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
				add_action( 'admin_print_footer_scripts', array( $this, 'admin_print_footer_scripts' ) );
				add_action( 'black_studio_tinymce_editor', array( $this, 'editor' ), 10, 4 );
				add_action( 'black_studio_tinymce_after_editor', array( $this, 'fix_the_editor_content_filter' ) );
				add_action( 'wp_tiny_mce_init', array( $this, 'wp_tiny_mce_init' ) );
				add_filter( 'wp_editor_settings', array( $this, 'editor_settings' ), 5, 2 );
				add_filter( 'tiny_mce_before_init', array( $this, 'tinymce_fix_rtl' ), 10 );
				add_filter( 'tiny_mce_before_init', array( $this, 'tinymce_fullscreen' ), 10, 2 );
				add_filter( 'quicktags_settings', array( $this, 'quicktags_fullscreen' ), 10, 2 );
				do_action( 'black_studio_tinymce_load' );
			}
		}

		/**
		 * Output the visual editor
		 *
		 * @uses wp_editor()
		 *
		 * @param string $text
		 * @param string $editor_id
		 * @param string $name
		 * @param string $type
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function editor( $text, $editor_id, $name = '', $type = 'visual' ) {
			wp_editor( $text, $editor_id, array(
				'textarea_name'  => $name,
				'default_editor' => $type == 'visual' ? 'tmce' : 'html'
			) );
		}

		/**
		 * Remove editor content filters for multiple editor instances
		 * Workaround for WordPress Core bug #28403 https://core.trac.wordpress.org/ticket/28403
		 *
		 * @uses remove_filter
		 *
		 * @return void
		 * @since 2.1.7
		 */
		function fix_the_editor_content_filter() {
			remove_filter( 'the_editor_content', 'wp_htmledit_pre' );
			remove_filter( 'the_editor_content', 'wp_richedit_pre' );
		}

		/**
		 * Set editor settings
		 *
		 * @param mixed[] $settings
		 * @param string $editor_id
		 *
		 * @return mixed[]
		 * @since 2.0.0
		 */
		public function editor_settings( $settings, $editor_id ) {
			if ( strstr( $editor_id, 'black-studio-tinymce' ) ) {
				$settings['tinymce']       = array(
					'wp_skip_init'       => 'widget-black-studio-tinymce-__i__-text' == $editor_id,
					'add_unload_trigger' => false,
					'wp_autoresize_on'   => false,
				);
				$settings['editor_height'] = 350;
				$settings['dfw']           = true;
				$settings['editor_class']  = 'black-studio-tinymce';
			}

			return $settings;
		}

		/**
		 * Initialize plugin links
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function init_links() {
			$this->links = array(
				/* translators: text used for plugin home link */
				'https://wordpress.org/plugins/black-studio-tinymce-widget/'                    => __( 'Home', 'black-studio-tinymce-widget' ),
				/* translators: text used for support faq link */
				'https://wordpress.org/plugins/black-studio-tinymce-widget/faq/'                => __( 'FAQ', 'black-studio-tinymce-widget' ),
				/* translators: text used for support forum link */
				'https://wordpress.org/support/plugin/black-studio-tinymce-widget'              => __( 'Support', 'black-studio-tinymce-widget' ),
				/* translators: text used for reviews link */
				'https://wordpress.org/support/view/plugin-reviews/black-studio-tinymce-widget' => __( 'Rate', 'black-studio-tinymce-widget' ),
				/* translators: text used for follow on twitter link */
				'https://twitter.com/blackstudioita'                                            => __( 'Follow', 'black-studio-tinymce-widget' ),
				/* translators: text used for donation link */
				'http://www.blackstudio.it/en/wordpress-plugins/black-studio-tinymce-widget/'   => __( 'Donate', 'black-studio-tinymce-widget' ),
			);
		}

		/**
		 * Show row meta on the plugin screen
		 *
		 * @uses esc_html()
		 * @uses esc_url()
		 *
		 * @param string[] $links
		 * @param string $file
		 *
		 * @return string[]
		 * @since 2.0.0
		 */
		public function plugin_row_meta( $links, $file ) {
			if ( $file == bstw()->get_basename() ) {
				foreach ( $this->links as $url => $label ) {
					$links[ $label ] = '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $label ) . '</a>';
				}
			}

			return $links;
		}

		/**
		 * Disable Quicktags default fullscreen
		 *
		 * @param mixed[] $settings
		 * @param string $editor_id
		 *
		 * @return mixed[]
		 * @since 2.1.2
		 */
		public function quicktags_fullscreen( $settings, $editor_id ) {
			if ( strstr( $editor_id, 'black-studio-tinymce' ) ) {
				$settings['buttons'] = str_replace( ',fullscreen', '', $settings['buttons'] );
			}

			return $settings;
		}

	} // END class Black_Studio_TinyMCE_Admin

} // END class_exists check
