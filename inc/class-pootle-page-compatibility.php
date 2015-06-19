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
 * *3 Supports top_border_height, top_border, bottom_border_height and bottom_border row styles
 *
 * Class Pootle_Page_Compatibility
 * @since 3.0.0
 */
class Pootle_Page_Compatibility {

	public $old_page_builder_posts = array();

	public $unsupported_page_builder_posts = array();

	/**
	 * Magic __construct
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->get_old_page_builder_posts();

		$this->put_page_builder_stuff_in_content();

		$this->reorganise_old_panels_data();

		$this->set_old_defaults();
	}

	/**
	 * Gets old posts with page builder contents
	 * Gets unsupported post types using earlier page builders
	 *
	 * @since 3.0.0
	 */
	private function get_old_page_builder_posts() {

		$post_types = get_post_types();

		//Get all posts using page builder
		$args  = array(
			'post_type'  => $post_types,
			'meta_query' => array(
				array(
					'key'     => 'panels_data',
					'compare' => 'EXISTS',
				),
			)
		);
		$query = new WP_Query( $args );

		foreach ( $query->posts as $post ) {

			$this->old_page_builder_posts[ $post->post_type ][] = $post->ID;

			if ( ! in_array( $post->post_type, array( 'revision', 'page', 'nav_menu_item', ) ) ) {
				$this->unsupported_page_builder_posts[] = $post->ID;
			}

		}

	}

	/**
	 * Puts page builder stuff in post content for unsupported post types
	 *
	 * @since 3.0.0
	 */
	private function put_page_builder_stuff_in_content() {

		return;

		global $siteorigin_panels_inline_css;

		foreach ( $this->unsupported_page_builder_posts as $id ) {

			$panel_content = siteorigin_panels_render( $id );

			$panel_style = '<style>' . $siteorigin_panels_inline_css . '</style>';

			$updated_post = array(
				'ID'           => $id,
				'post_content' => $panel_style . $panel_content,
			);
			wp_update_post( $updated_post );

			$notices = array();

			$notices['settings-updated'] = array(
				'type'    => 'update-nag',
				'message' => __( "Now we only support page post types, however for your convenience we have put all your existing page builder using posts layout in the content.", 'woothemes' )
			);

			update_option( 'pootle_page_admin_notices', $notices );

		}

	}

	/**
	 * Refactors old panels data for compatibility with v3
	 *
	 * @since 3.0.0
	 */
	private function reorganise_old_panels_data() {

		if ( empty( $this->old_page_builder_posts['page'] ) or ! is_array( $this->old_page_builder_posts['page'] ) ) {
			return;
		}

		//Get old pages ( we don't support other post types since v3.0.0 )
		$old_pages = $this->old_page_builder_posts['page'];

		foreach ( $old_pages as $id ) {

			//Get panels data
			$panels_data = get_post_meta( $id, 'panels_data', true );

			//Loop through the rows
			foreach ( $panels_data['grids'] as $i => $row ) {

				//Get new style format for rows
				$panels_data['grids'][ $i ]['style'] = $this->new_row_style_format( $row['style'] );

			}

			//Loop through the widgets
			foreach ( $panels_data['widgets'] as $i => $wid ) {

				$panels_data['widgets'][ $i ]['info']['style'] = $this->new_widget_style_format( $wid['info']['style'] );

			}

			//Finally update the post meta with new modified panels data
			update_post_meta( $id, 'panels_data', $panels_data );

		}

	}

	/**
	 * Returns the new row style format from old
	 *
	 * @param $panels_row_styles
	 *
	 * @return array New styles format
	 * @since 3.0.0
	 */
	private function new_row_style_format( $panels_row_styles ) {


		if ( ! empty( $panels_row_styles['style'] ) ) {
			return $panels_row_styles;
		}

		/** @var array $unsupported_styles */
		$unsupported_styles = array(
			'top_border_height',
			'top_border',
			'bottom_border_height',
			'bottom_border',
			'height',
			'no_margin',
		);

		/** @var array $styles_array init new styles array */
		$styles_array = array();

		foreach ( $panels_row_styles as $k => $v ) {
			if ( ! in_array( $k, $unsupported_styles ) ) {
				$styles_array[ $k ] = $v;
			}
		}

		//Put unsupported styles in new Inline Styles field
		$styles_array['style'] = $this->get_unsupported_style_fields_inline_styles( $panels_row_styles );

		return $styles_array;

	}

	private function get_unsupported_style_fields_inline_styles( $panels_row_styles ) {

		/** @var string $styles to put in new Inline Styles field */
		$styles = '';

		if ( ! empty( $panels_row_styles['top_border_height'] ) ) {
			$styles .= "border-top: {$panels_row_styles['top_border_height']}px solid {$panels_row_styles['top_border']} ; ";
		}

		if ( ! empty( $panels_row_styles['bottom_border_height'] ) ) {
			$styles .= "border-bottom: {$panels_row_styles['bottom_border_height']}px solid {$panels_row_styles['bottom_border']} ; ";
		}

		if ( ! empty( $panels_row_styles['height'] ) ) {
			$styles .= "height: {$panels_row_styles['height']}px; ";
		}

		if ( ! empty( $panels_row_styles['no_margin'] ) ) {
			$styles .= "margin-bottom: 0; ";
		}

		return $styles;

	}

	/**
	 * Returns the new widget style format from old
	 *
	 * @param $styles Widget styles
	 *
	 * @return array New styles format
	 * @since 3.0.0
	 */
	private function new_widget_style_format( $styles ) {

		if ( strpos( $styles, 'hide-title":"none' ) ) {

			$styles = str_replace( array( '"hide-title":"none",', '"}' ), array(
					'',
					'"'
				), $styles ) . ',"inline-css":"hide-title:none;"}';

		}

		return $styles;
	}

	/**
	 * Sets old defaults
	 *
	 * @since 3.0.0
	 */
	private function set_old_defaults() {

		$settings = get_option(
			'siteorigin_panels_display',
			array(
				'margin-bottom' => '30',
				'margin-sides'  => '30',
			) );

		update_option( 'siteorigin_panels_display', $settings );

	}
}