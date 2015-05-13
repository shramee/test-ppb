<?php
/**
 * Created by PhpStorm.
 * User: shramee
 * Date: 13/5/15
 * Time: 6:10 PM
 */

/**
 * Takes care of old versions of PootlePage
 *
 * Does following
 * *1 Puts the page builder contents of all non-Page post types in their contents
 * *2 Gives user message that 1 is done
 *
 * Class Pootle_Page_Compatibility
 */
class Pootle_Page_Compatibility {

	public $old_page_builder_posts = array();

	/**
	 * Magic __construct
	 */
	public function __construct( ){

		$this->get_old_page_builder_posts();

		$this->put_page_builder_stuff_in_content();

	}

	/**
	 * Gets non page posts with page builder contents
	 */
	private function get_old_page_builder_posts() {

		$post_types = get_post_types();

		foreach ( array( 'revision', 'page', 'nav_menu_item', ) as $post_type ) {
			unset( $post_types[ $post_type ] );
		}

		$args = array(
			'post_type' => $post_types,
			'meta_query' => array(
				array(
					'key' => 'panels_data',
					'compare' => 'EXISTS' // this should work...
				),
			)
		);
		$query = new WP_Query( $args );

		foreach ( $query->posts as $post ) {

			$this->old_page_builder_posts[] =  $post->ID;

		}

	}

	/**
	 * Puts page builder stuff in post content for unsupported post types
	 */
	private function put_page_builder_stuff_in_content() {

		global $siteorigin_panels_inline_css;

		foreach ( $this->old_page_builder_posts as $id ) {

			$panel_content = siteorigin_panels_render( $id );

			$panel_style = '<style>' . $siteorigin_panels_inline_css . '</style>';

			$updated_post = array(
				'ID'           => $id,
				'post_content' => $panel_style . $panel_content,
			);
			wp_update_post( $updated_post );

			$notices = array();

			$notices['settings-updated'] = array( 'type' => 'update-nag', 'message' => __( "Now we only support page post types, however for your convenience we have put all your existing page builder using posts layout in the content.", 'woothemes' ) );

			update_option( 'pootle_page_admin_notices', $notices );

		}

	}

}