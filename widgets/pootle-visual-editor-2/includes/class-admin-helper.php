<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extraction
 *
 * @package Black_Studio_TinyMCE_Widget
 * @since 2.0.0
 */

if ( ! class_exists( 'Black_Studio_TinyMCE_Admin_Helper' ) ) {

	abstract class Black_Studio_TinyMCE_Admin_Helper {

		/**
		 * Instantiate tinyMCE editor
		 *
		 * @uses add_thickbox()
		 * @uses wp_enqueue_media()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function enqueue_media() {
			// Add support for thickbox media dialog
			add_thickbox();
			// New media modal dialog ( WP 3.5+ )
			if ( function_exists( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
			}
		}

		/**
		 * Enqueue styles
		 *
		 * @uses wp_enqueue_style()
		 * @uses Black_Studio_TinyMCE_Plugin::enqueue_style()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function admin_print_styles() {
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
			wp_enqueue_style( 'editor-buttons' );
			$this->enqueue_style();
		}

		/**
		 * Helper function to enqueue style
		 *
		 * @uses apply_filters()
		 * @uses wp_enqueue_style()
		 * @uses plugins_url()
		 * @uses SCRIPT_DEBUG
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function enqueue_style() {
			$style  = apply_filters( 'black-studio-tinymce-widget-style', 'black-studio-tinymce-widget' );
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_style(
				$style,
				plugins_url( 'css/' . $style . $suffix . '.css', dirname( __FILE__ ) ),
				array(),
				bstw()->get_version()
			);
		}

		/**
		 * Enqueue header scripts
		 *
		 * @uses wp_enqueue_script()
		 * @uses do_action()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function admin_print_scripts() {
			wp_enqueue_script( 'media-upload' );
			wp_enqueue_script( 'wplink' );
			wp_enqueue_script( 'wpdialogs-popup' );
			$this->enqueue_script();
			$this->localize_script();
			do_action( 'wp_enqueue_editor', array( 'tinymce' => true ) );
		}

		/**
		 * Helper function to enqueue script
		 *
		 * @uses apply_filters()
		 * @uses wp_enqueue_script()
		 * @uses plugins_url()
		 * @uses SCRIPT_DEBUG
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function enqueue_script() {
			$script = apply_filters( 'black-studio-tinymce-widget-script', 'black-studio-tinymce-widget' );
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script(
				$script,
				plugins_url( 'js/' . $script . $suffix . '.js', dirname( __FILE__ ) ),
				array( 'jquery', 'editor', 'quicktags' ),
				bstw()->get_version(),
				true
			);
		}

		/**
		 * Helper function to enqueue localized script
		 *
		 * @uses apply_filters()
		 * @uses wp_localize_script()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function localize_script() {
			$container_selectors = apply_filters( 'black_studio_tinymce_container_selectors', array(
				'div.widget',
				'div.widget-inside'
			) );
			$activate_events     = apply_filters( 'black_studio_tinymce_activate_events', array() );
			$deactivate_events   = apply_filters( 'black_studio_tinymce_deactivate_events', array() );
			$data                = array(
				'container_selectors' => implode( ', ', $container_selectors ),
				'activate_events'     => $activate_events,
				'deactivate_events'   => $deactivate_events,
				/* translators: error message shown when a duplicated widget ID is detected */
				'error_duplicate_id'  => __( 'ERROR: Duplicate widget ID detected. To avoid content loss, please create a new widget with the same content and then delete this one.', 'black-studio-tinymce-widget' )
			);
			wp_localize_script( apply_filters( 'black-studio-tinymce-widget-script', 'black-studio-tinymce-widget' ), 'bstw_data', $data );
		}

		/**
		 * Enqueue footer scripts
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function admin_print_footer_scripts() {
			$this->editor( '', 'black-studio-tinymce-widget', 'black-studio-tinymce-widget' );
		}

		/**
		 * Fix for rtl languages
		 *
		 * @param mixed[] $settings
		 *
		 * @return mixed[]
		 * @since 2.1.0
		 */
		public function tinymce_fix_rtl( $settings ) {
			// This fix has to be applied to all editor instances ( not just BSTW ones )
			if ( is_rtl() && isset( $settings['plugins'] ) && ',directionality' == $settings['plugins'] ) {
				unset( $settings['plugins'] );
			}

			return $settings;
		}

		/**
		 * Apply TinyMCE default fullscreen
		 *
		 * @param mixed[] $settings
		 * @param string $editor_id
		 *
		 * @return mixed[]
		 * @since 2.1.2
		 */
		public function tinymce_fullscreen( $settings, $editor_id ) {
			if ( strstr( $editor_id, 'black-studio-tinymce' ) ) {
				for ( $i = 1; $i <= 4; $i ++ ) {
					$toolbar = 'toolbar' . $i;
					if ( isset( $settings[ $toolbar ] ) ) {
						$settings[ $toolbar ] = str_replace( 'wp_fullscreen', 'wp_fullscreen,fullscreen', $settings[ $toolbar ] );
					}
				}
			}

			return $settings;
		}

		/**
		 * Setup editor instance for event handling
		 *
		 * @return void
		 * @since 2.2.1
		 */
		function wp_tiny_mce_init() {
			$script = 'black-studio-tinymce-widget-setup';
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			echo "\t\t" . '<script type="text/javascript" src="' . plugins_url( 'js/' . $script . $suffix . '.js', dirname( __FILE__ ) ) . '"></script>' . "\n"; // xss ok
		}

	} // END class Black_Studio_TinyMCE_Admin_Helper

} // END class_exists check
