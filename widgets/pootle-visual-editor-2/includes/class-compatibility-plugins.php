<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that provides compatibility code with other plugins
 *
 * @package Black_Studio_TinyMCE_Widget
 * @since 2.0.0
 */

if ( ! class_exists( 'Black_Studio_TinyMCE_Compatibility_Plugins' ) ) {

	final class Black_Studio_TinyMCE_Compatibility_Plugins extends Black_Studio_TinyMCE_Compatibility_Wp_Plugins {

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
		 * @param string[] $plugins
		 *
		 * @return object
		 * @since 2.0.0
		 */
		public static function instance( $plugins = array() ) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $plugins );
			}

			return self::$_instance;
		}

		/**
		 * Class constructor
		 *
		 * @param string[] $plugins
		 *
		 * @since 2.0.0
		 */
		protected function __construct( $plugins ) {
			foreach ( $plugins as $plugin ) {
				if ( is_callable( array( $this, $plugin ), false ) ) {
					$this->$plugin();
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
		 * Compatibility with WPML
		 *
		 * @uses add_filter()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function wpml() {
			add_filter( 'black_studio_tinymce_widget_update', array( $this, 'wpml_widget_update' ), 10, 2 );
			add_filter( 'widget_text', array( $this, 'wpml_widget_text' ), 2, 3 );
		}

		/**
		 * Add widget text to WPML String translation
		 *
		 * @uses icl_register_string() Part of WPML
		 *
		 * @param mixed[] $instance
		 * @param object $widget
		 *
		 * @return mixed[]
		 * @since 2.0.0
		 */
		public function wpml_widget_update( $instance, $widget ) {
			if ( function_exists( 'icl_register_string' ) && ! empty( $widget->number ) ) {
				icl_register_string( 'Widgets', 'widget body - ' . $widget->id_base . '-' . $widget->number, $instance['text'] );
			}

			return $instance;
		}

		/**
		 * Translate widget text
		 *
		 * @uses icl_t() Part of WPML
		 *
		 * @param string $text
		 * @param mixed[]|null $instance
		 * @param object|null $widget
		 *
		 * @return string
		 * @since 2.0.0
		 */
		public function wpml_widget_text( $text, $instance = null, $widget = null ) {
			if ( bstw()->check_widget( $widget ) && ! empty( $instance ) ) {
				if ( function_exists( 'icl_t' ) ) {
					$text = icl_t( 'Widgets', 'widget body - ' . $widget->id_base . '-' . $widget->number, $text );
				}
			}

			return $text;
		}

		/**
		 * Compatibility for WP Page Widget plugin
		 *
		 * @uses add_action()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function wp_page_widget() {
			add_action( 'admin_init', array( $this, 'wp_page_widget_admin_init' ) );
		}

		/**
		 * Initialize compatibility for WP Page Widget plugin ( only for WordPress 3.3+ )
		 *
		 * @uses add_filter()
		 * @uses add_action()
		 * @uses is_plugin_active()
		 * @uses get_bloginfo()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function wp_page_widget_admin_init() {
			if ( is_admin() && is_plugin_active( 'wp-page-widget/wp-page-widgets.php' ) && version_compare( get_bloginfo( 'version' ), '3.3', '>=' ) ) {
				add_filter( 'black_studio_tinymce_enable_pages', array( $this, 'wp_page_widget_enable_pages' ) );
				add_action( 'admin_print_scripts', array( $this, 'wp_page_widget_enqueue_script' ) );
			}
		}

		/**
		 * Enable filter for WP Page Widget plugin
		 *
		 * @param string[] $pages
		 *
		 * @return string[]
		 * @since 2.0.0
		 */
		public function wp_page_widget_enable_pages( $pages ) {
			$pages[] = 'post-new.php';
			$pages[] = 'post.php';
			if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
				$pages[] = 'edit-tags.php';
			}
			if ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'pw-front-page', 'pw-search-page' ) ) ) {
				$pages[] = 'admin.php';
			}

			return $pages;
		}

		/**
		 * Enqueue script for WP Page Widget plugin
		 *
		 * @uses apply_filters()
		 * @uses wp_enqueue_script()
		 * @uses plugins_url()
		 * @uses SCRIPT_DEBUG
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function wp_page_widget_enqueue_script() {

			$main_script = apply_filters( 'black-studio-tinymce-widget-script', 'black-studio-tinymce-widget' );

			wp_enqueue_script( 'wp-page-widget', plugins_url( 'js/wp-page-widget.min.js', dirname( __FILE__ ) ), array(
				'jquery',
				'editor',
				'quicktags',
				$main_script
			), bstw()->get_version(), true );
		}

		/**
		 * Compatibility with Jetpack After the deadline
		 *
		 * @uses add_action()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function jetpack_after_the_deadline() {
			add_action( 'black_studio_tinymce_load', array( $this, 'jetpack_after_the_deadline_load' ) );
		}

		/**
		 * Load Jetpack After the deadline scripts
		 *
		 * @uses add_filter()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function jetpack_after_the_deadline_load() {
			add_filter( 'atd_load_scripts', '__return_true' );
		}

	} // END class Black_Studio_TinyMCE_Compatibility_Plugins

} // END class_exists check
