<?php

if ( ! class_exists( 'PootlePage_Font_Utility' ) ) :
	class PootlePage_Font_Utility {
		/**
		 * Instance of this class.
		 *
		 * @var      object
		 * @since    1.2
		 *
		 */
		protected static $instance = null;

		/**
		 * Slug of the plugin screen.
		 *
		 * @var      string
		 * @since    1.2
		 *
		 */
		protected $plugin_screen_hook_suffix = null;


		/**
		 * Constructor Function
		 *
		 * Initialize the plugin by loading admin scripts & styles and adding a
		 * settings page and menu.
		 *
		 * @since 1.2
		 * @version 1.3.1
		 *
		 */
		function __construct() {
			$this->register_actions();
			$this->register_filters();
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return    object    A single instance of this class.
		 *
		 * @since 1.2
		 * @version 1.3.1
		 *
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Register Custom Actions
		 *
		 * Add any custom actions in this function.
		 *
		 * @since 1.2
		 * @version 1.3.1
		 *
		 */
		public function register_actions() {
		}

		/**
		 * Register Custom Filters
		 *
		 * Add any custom filters in this function.
		 *
		 * @since 1.2
		 * @version 1.3.1
		 *
		 */
		public function register_filters() {

		}

		public static function get_all_fonts() {
			global $pootle_page_font;

			return $pootle_page_font;
		}

		/**
		 * Get Individual Fonts
		 *
		 * Takes an id and returns the corresponding font.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/apply_filters    apply_filters()
		 *
		 * Custom Filters:
		 *     - 'tt_font_get_font'
		 *
		 * @return array $fonts - All websafe fonts with their properties
		 *
		 * @since 1.2
		 * @version 1.3.1
		 *
		 */
		public static function get_font( $id = '' ) {
			// Get all fonts
			$default_fonts = self::get_default_fonts();
			$google_fonts  = array(); //self::get_google_fonts();

			// Check if it is set and return if found
			if ( isset( $default_fonts[ $id ] ) ) {

				// Return default font from array if set
				return apply_filters( 'tt_font_get_font', $default_fonts[ $id ] );

			} else if ( isset( $google_fonts[ $id ] ) ) {

				// Return google font from array if set
				return apply_filters( 'tt_font_get_font', $google_fonts[ $id ] );

			} else {
				return false;
			}
		}
	}
endif;