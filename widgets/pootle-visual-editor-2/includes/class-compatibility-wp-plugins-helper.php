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

if ( ! class_exists( 'Black_Studio_TinyMCE_Compatibility_Wp_Plugins' ) ) {

	abstract class Black_Studio_TinyMCE_Compatibility_Wp_Plugins {

		/**
		 * Compatibility for WordPress prior to 3.9
		 * For Black_Studio_TinyMCE_Compatibility_Wordpress
		 *
		 * @uses add_action()
		 * @uses remove_action()
		 * @uses add_filter()
		 * @uses get_bloginfo()
		 * @uses Black_Studio_TinyMCE_Admin::enabled()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function wp_pre_39() {
			$wp_version = get_bloginfo( 'version' );
			if ( bstw()->admin()->enabled() ) {
				add_filter( 'black-studio-tinymce-widget-script', array( $this, 'wp_pre_39_handle' ), 61 );
				add_filter( 'tiny_mce_before_init', array( $this, 'wp_pre_39_tiny_mce_before_init' ), 61 );
				add_action( 'admin_print_footer_scripts', array( $this, 'wp_pre_39_admin_print_footer_scripts' ) );
				remove_action( 'admin_print_footer_scripts', array( bstw()->admin(), 'admin_print_footer_scripts' ) );
				if ( ! version_compare( $wp_version, '3.2', '<' ) ) {
					remove_action( 'admin_print_footer_scripts', array(
						$this,
						'wp_pre_32_admin_print_footer_scripts'
					) );
				}
				if ( ! version_compare( $wp_version, '3.3', '<' ) ) {
					remove_action( 'admin_print_footer_scripts', array(
						$this,
						'wp_pre_33_admin_print_footer_scripts'
					) );
				}
				add_action( 'black_studio_tinymce_editor', array( $this, 'wp_pre_39_editor' ), 10, 4 );
				remove_action( 'black_studio_tinymce_editor', array( bstw()->admin(), 'editor' ), 10, 3 );
			}
		}

		/**
		 * Filter to enqueue style / script for WordPress prior to 3.9
		 * For Black_Studio_TinyMCE_Compatibility_Wordpress
		 *
		 * @return string
		 * @since 2.0.0
		 */
		public function wp_pre_39_handle() {
			return 'black-studio-tinymce-widget-pre39';
		}

		/**
		 * TinyMCE initialization for WordPress prior to 3.9
		 * For Black_Studio_TinyMCE_Compatibility_Wordpress
		 *
		 * @param mixed[] $settings
		 *
		 * @return mixed[]
		 * @since 2.0.0
		 */
		public function wp_pre_39_tiny_mce_before_init( $settings ) {
			$custom_settings = array(
				'remove_linebreaks' => false,
				'convert_newlines_to_brs' => false,
				'force_p_newlines' => true,
				'force_br_newlines' => false,
				'remove_redundant_brs' => false,
				'forced_root_block' => 'p',
				'apply_source_formatting' => true,
			);

			// Return modified settings
			return array_merge( $settings, $custom_settings );
		}

		/**
		 * Enqueue footer scripts for WordPress prior to 3.9
		 * For Black_Studio_TinyMCE_Compatibility_Wordpress
		 *
		 * @uses wp_editor()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function wp_pre_39_admin_print_footer_scripts() {
			if ( function_exists( 'wp_editor' ) ) {
				wp_editor( '', 'black-studio-tinymce-widget' );
			}
		}

		/**
		 * Output the visual editor code for WordPress prior to 3.9
		 * For Black_Studio_TinyMCE_Compatibility_Wordpress
		 *
		 * @uses esc_attr()
		 * @uses esc_textarea()
		 * @uses do_action()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function wp_pre_39_editor( $text, $id, $name = '', $type = 'visual' ) {
			$switch_class = $type == 'visual' ? 'html-active' : 'tmce-active';
			?>
			<div id="<?php echo esc_attr( $id ); ?>-wp-content-wrap"
			     class="wp-core-ui wp-editor-wrap <?php echo esc_attr( $switch_class ); ?> has-dfw">
				<div id="<?php echo esc_attr( $id ); ?>-wp-content-editor-tools" class="wp-editor-tools hide-if-no-js">
					<div class="wp-editor-tabs">
						<a id="<?php echo esc_attr( $id ); ?>-content-html"
						   class="wp-switch-editor switch-html"><?php _e( 'HTML' ); ?></a>
						<a id="<?php echo esc_attr( $id ); ?>-content-tmce"
						   class="wp-switch-editor switch-tmce"><?php _e( 'Visual' ); ?></a>
					</div>
					<div id="<?php esc_attr( $id ); ?>-wp-content-media-buttons" class="wp-media-buttons">
						<?php do_action( 'media_buttons', $id ); ?>
					</div>
				</div>
				<div class="wp-editor-container">
					<textarea class="widefat" rows="20" cols="40" id="<?php echo esc_attr( $id ); ?>"
					          name="<?php echo esc_attr( $name ); ?>"><?php echo esc_textarea( $text ); ?></textarea>
				</div>
			</div>
		<?php
		}

		/**
		 * Compatibility with Page Builder ( SiteOrigin Panels )
		 * For Black_Studio_TinyMCE_Compatibility_Plugins
		 *
		 * @uses add_action()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function siteorigin_panels() {
			add_action( 'admin_init', array( $this, 'siteorigin_panels_disable_compat' ), 7 );
			add_action( 'admin_init', array( $this, 'siteorigin_panels_admin_init' ) );
		}

		/**
		 * Initialize compatibility for Page Builder ( SiteOrigin Panels )
		 * For Black_Studio_TinyMCE_Compatibility_Plugins
		 *
		 * @uses add_filter()
		 * @uses add_action()
		 * @uses remove_filter()
		 * @uses add_action()
		 * @uses is_plugin_active()
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function siteorigin_panels_admin_init() {
//			if ( is_admin() &&
//				( is_plugin_active( 'siteorigin-panels/siteorigin-panels.php' ) ||
//					is_plugin_active( 'page-builder-for-canvas-master/page-builder-for-canvas.php' ) )
			// This VE2 is bundled in Page Builder, so don't need to check for Page Builder
			if ( is_admin() ) {
				add_filter( 'siteorigin_panels_widget_object', array( $this, 'siteorigin_panels_widget_object' ), 10 );
				add_filter( 'black_studio_tinymce_container_selectors', array(
					$this,
					'siteorigin_panels_container_selectors'
				) );
				add_filter( 'black_studio_tinymce_activate_events', array(
					$this,
					'siteorigin_panels_activate_events'
				) );
				add_filter( 'black_studio_tinymce_deactivate_events', array(
					$this,
					'siteorigin_panels_deactivate_events'
				) );
				add_filter( 'black_studio_tinymce_enable_pages', array( $this, 'siteorigin_panels_enable_pages' ) );
				remove_filter( 'widget_text', array( bstw()->text_filters(), 'wpautop' ), 8 );
			}
		}

		/**
		 * Remove widget number to prevent translation when using Page Builder ( SiteOrigin Panels ) + WPML String Translation
		 * For Black_Studio_TinyMCE_Compatibility_Plugins
		 *
		 * @param object $the_widget
		 *
		 * @return object
		 * @since 2.0.0
		 */
		public function siteorigin_panels_widget_object( $the_widget ) {
			if ( isset( $the_widget->id_base ) && $the_widget->id_base == 'black-studio-tinymce' ) {
				$the_widget->number = '';
			}

			return $the_widget;
		}

		/**
		 * Add selector for widget detection for Page Builder ( SiteOrigin Panels )
		 * For Black_Studio_TinyMCE_Compatibility_Plugins
		 *
		 * @param string[] $selectors
		 *
		 * @return string[]
		 * @since 2.0.0
		 */
		public function siteorigin_panels_container_selectors( $selectors ) {
			$selectors[] = 'div.panel-dialog';

			return $selectors;
		}

		/**
		 * Add activate events for Page Builder ( SiteOrigin Panels )
		 * For Black_Studio_TinyMCE_Compatibility_Plugins
		 *
		 * @param string[] $events
		 *
		 * @return string[]
		 * @since 2.0.0
		 */
		public function siteorigin_panels_activate_events( $events ) {
			$events[] = 'panelsopen';

			return $events;
		}

		/**
		 * Add deactivate events for Page Builder ( SiteOrigin Panels )
		 * For Black_Studio_TinyMCE_Compatibility_Plugins
		 *
		 * @param string[] $events
		 *
		 * @return string[]
		 * @since 2.0.0
		 */
		public function siteorigin_panels_deactivate_events( $events ) {
			$events[] = 'panelsdone';

			return $events;
		}

		/**
		 * Add pages filter to enable editor for Page Builder ( SiteOrigin Panels )
		 * For Black_Studio_TinyMCE_Compatibility_Plugins
		 *
		 * @param string[] $pages
		 *
		 * @return string[]
		 * @since 2.0.0
		 */
		public function siteorigin_panels_enable_pages( $pages ) {
			$pages[] = 'post-new.php';
			$pages[] = 'post.php';
			if ( isset( $_GET['page'] ) && $_GET['page'] == 'so_panels_home_page' ) {
				$pages[] = 'themes.php';
			}

			return $pages;
		}

		/**
		 * Disable old compatibility code provided by Page Builder ( SiteOrigin Panels )
		 * For Black_Studio_TinyMCE_Compatibility_Plugins
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function siteorigin_panels_disable_compat() {
			remove_action( 'admin_init', 'siteorigin_panels_black_studio_tinymce_admin_init' );
			remove_action( 'admin_enqueue_scripts', 'siteorigin_panels_black_studio_tinymce_admin_enqueue', 15 );
		}


	} // END class Black_Studio_TinyMCE_Compatibility_Wp_Plugins

} // END class_exists check
