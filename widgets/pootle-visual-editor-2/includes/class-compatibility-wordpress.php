<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that provides compatibility code with older WordPress versions
 *
 * @package Black_Studio_TinyMCE_Widget
 * @since 2.0.0
 */

if ( ! class_exists( 'Black_Studio_TinyMCE_Compatibility_Wordpress' ) ) {

	final class Black_Studio_TinyMCE_Compatibility_Wordpress extends Black_Studio_TinyMCE_Compatibility_Wp_Plugins {

		/**
		 * The single instance of the class
		 *
		 * @var object
		 * @since 2.0.0
		 */
		protected static $_instance = null;

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
		 * @uses get_bloginfo()
		 * @uses add_action()
		 *
		 * @param object $plugin
		 * @since 2.0.0
		 */
		protected function __construct() {
			$current_version = get_bloginfo( 'version' );
			$previous_versions = array( '3.2', '3.3', '3.5', '3.9' );
			foreach ( $previous_versions as $previous_version ) {
				if ( version_compare( $current_version, $previous_version, '<' ) ) {
					add_action( 'admin_init', array( $this, 'wp_pre_' . str_replace( '.', '', $previous_version ) ), intval( 10 * $previous_version ) );
				}
			}
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
		 * Compatibility for WordPress prior to 3.2
		 *
		 * @uses remove_action()
		 * @uses add_action()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function wp_pre_32() {
			if ( bstw()->admin()->enabled() ) {
				remove_action( 'admin_print_footer_scripts', array( bstw()->admin(), 'admin_print_footer_scripts' ) );
				add_action( 'admin_print_footer_scripts', array( $this, 'wp_pre_32_admin_print_footer_scripts' ) );
			}
		}

		/**
		 * Enqueue footer scripts for WordPress prior to 3.2
		 *
		 * @uses wp_tiny_mce()
		 * @uses wp_tiny_mce_preload_dialogs()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function  wp_pre_32_admin_print_footer_scripts() {
			if ( function_exists( 'wp_tiny_mce' ) ) {
				wp_tiny_mce( false, array() );
			}
			if ( function_exists( 'wp_tiny_mce_preload_dialogs' ) ) {
				wp_tiny_mce_preload_dialogs();
			}
		}

		/**
		 * Compatibility for WordPress prior to 3.3
		 *
		 * @uses add_filter()
		 * @uses add_action()
		 * @uses remove_action()
		 * @uses get_bloginfo()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function wp_pre_33() {
			$wp_version = get_bloginfo( 'version' );
			if ( bstw()->admin()->enabled() ) {
				add_filter( 'tiny_mce_before_init', array( $this, 'wp_pre_33_tiny_mce_before_init' ), 67 );
				add_filter( 'black-studio-tinymce-widget-script', array( $this, 'wp_pre_33_handle' ), 67 );
				add_filter( 'black-studio-tinymce-widget-style', array( $this, 'wp_pre_33_handle' ), 67 );
				remove_action( 'admin_print_styles', array( bstw()->admin(), 'admin_print_styles' ) );
				add_action( 'admin_print_styles', array( $this, 'wp_pre_33_admin_print_styles' ) );
				remove_action( 'admin_print_scripts', array( bstw()->admin(), 'admin_print_scripts' ) );
				add_action( 'admin_print_scripts', array( $this, 'wp_pre_33_admin_print_scripts' ) );
				remove_action( 'admin_print_footer_scripts', array( bstw()->admin(), 'admin_print_footer_scripts' ) );
				if ( ! version_compare( $wp_version, '3.2', '<' ) ) {
					remove_action( 'admin_print_footer_scripts', array( $this, 'wp_pre_32_admin_print_footer_scripts' ) );
				}
				add_action( 'admin_print_footer_scripts', array( $this, 'wp_pre_33_admin_print_footer_scripts' ) );
				remove_action( 'admin_print_scripts', array( bstw()->admin(), 'pointer_load' ) );
				remove_filter( 'black_studio_tinymce_admin_pointers-widgets', array( bstw()->admin(), 'pointer_register' ) );
			}
		}

		/**
		 * Remove WP fullscreen mode and set the native tinyMCE fullscreen mode for WordPress prior to 3.3
		 *
		 * @param mixed[] $settings
		 * @return mixed[]
		 * @since 2.0.0
		 */
		public function wp_pre_33_tiny_mce_before_init( $settings ) {
			$plugins = explode( ',', $settings['plugins'] );
			if ( isset( $plugins['wpfullscreen'] ) ) {
				unset( $plugins['wpfullscreen'] );
			}
			if ( ! isset( $plugins['fullscreen'] ) ) {
				$plugins[] = 'fullscreen';
			}
			$settings['plugins'] = implode( ',', $plugins );
			return $settings;
		}

		/**
		 * Enqueue styles for WordPress prior to 3.3
		 *
		 * @uses wp_enqueue_style()
		 * @uses Black_Studio_TinyMCE_Admin::enqueue_style()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function wp_pre_33_admin_print_styles() {
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_style( 'editor-buttons' );
			bstw()->admin()->enqueue_style();
		}

		/**
		 * Enqueue header scripts for WordPress prior to 3.3
		 *
		 * @uses wp_enqueue_script()
		 * @uses Black_Studio_TinyMCE_Admin::enqueue_script()
		 * @uses Black_Studio_TinyMCE_Admin::localize_script()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function wp_pre_33_admin_print_scripts() {
			wp_enqueue_script( 'media-upload' );
			bstw()->admin()->enqueue_script();
			bstw()->admin()->localize_script();
		}

		/**
		 * Filter to enqueue style / script for WordPress prior to 3.3
		 *
		 * @return string
		 * @since 2.0.0
		 */
		public function wp_pre_33_handle() {
			return 'black-studio-tinymce-widget-pre33';
		}

		/**
		 * Enqueue footer scripts for WordPress prior to 3.3
		 *
		 * @uses wp_tiny_mce()
		 * @uses wp_preload_dialog()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function wp_pre_33_admin_print_footer_scripts() {
			if ( function_exists( 'wp_tiny_mce' ) ) {
				wp_tiny_mce( false, array() );
			}
			if ( function_exists( 'wp_preload_dialogs' ) ) {
				wp_preload_dialogs( array( 'plugins' => 'wpdialogs,wplink,wpfullscreen' ) );
			}
		}

		/**
		 * Compatibility for WordPress prior to 3.5
		 *
		 * @uses add_filter()
		 * @uses Black_Studio_TinyMCE_Admin::enabled()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function wp_pre_35() {
			if ( bstw()->admin()->enabled() ) {
				add_filter( '_upload_iframe_src', array( $this, 'wp_pre_35_upload_iframe_src' ), 65 );
			}
		}

		/**
		 * Enable full media options in upload dialog for WordPress prior to 3.5
		 * ( this is done excluding post_id parameter in Thickbox iframe url )
		 *
		 * @global string $pagenow
		 * @param string $upload_iframe_src
		 * @return string
		 * @since 2.0.0
		 */
		public function wp_pre_35_upload_iframe_src( $upload_iframe_src ) {
			global $pagenow;
			if ( $pagenow == 'widgets.php' ) {
				$upload_iframe_src = str_replace( 'post_id=0', '', $upload_iframe_src );
			}
			return $upload_iframe_src;
		}

	} // END class Black_Studio_TinyMCE_Compatibility_Wordpress

} // END class_exists check
