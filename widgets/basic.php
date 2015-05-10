<?php

/**
 * Display a loop of posts.
 *
 * Class SiteOrigin_Panels_Widgets_PostLoop
 */
class SiteOrigin_Panels_Widgets_PostLoop extends WP_Widget{
	function __construct() {
		parent::__construct(
			'siteorigin-panels-postloop',
			__( 'Post Loop ( Pootle )', 'siteorigin-panels' ),
			array(
				'description' => __( 'Displays a post loop.', 'siteorigin-panels' ),
			 )
		 );
	}

	private $instance;

	/**
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		require_once "post-loop-public.php";
	}

//	public function option_css() {
//		if ( isset( $this->instance['column_count'] ) ) {
//			$count = ( int )$this->instance['column_count'];
//			if ( $count > 1 ) {
//				$columnWidth = ( 100 / $count ) . "%";
//
////				$css = '';
////				$css .= ".widget_siteorigin-panels-postloop > article {\n";
////				$css .= "\t" . "display: inline-block;" . "\n";
////				$css .= "\t" . "width: " . $columnWidth . ";\n";
////				$css .= "\t" . "padding-right: 10px;" . "\n";
////
////				echo "<style>\n" . $css . "</style>\n";
//			}
//
//		}
//	}

	public function filter_excerpt_length( $length ) {
		if ( isset( $this->instance['excerpt_length'] ) ) {
			$length = ( int )$this->instance['excerpt_length'];
			return $length;
		} else {
			return $length;
		}
	}

	public function filter_title( $title, $id ) {
		if ( isset( $this->instance['titles_show'] ) ) {
			if ( ! $this->instance['titles_show'] ) {
				return '';
			}
		}

		return $title;
	}

	public function loop_before() {
		// if pagination is disabled, still use posts_per_page
		global $wp_query;
		if ( isset( $this->instance['posts_per_page'] ) ) {
			query_posts(
				array_merge(
					$wp_query->query,
					array( 'posts_per_page' => $this->instance['posts_per_page'] )
				)
			);
		}

		if ( isset( $this->instance['pagination_enable'] ) ) {
			if ( ! $this->instance['pagination_enable'] ) {
				$wp_query->max_num_pages = 1;
			}
		}
	}

	public function temporary_set_woo_settings( $settings ) {
		if ( $this->instance['excerpt_enable'] ) {
			$settings['post_content'] = 'excerpt';
		} else {
			$settings['post_content'] = 'content';
		}

		if ( isset( $this->instance['thumbnail_size'] ) ) {
			$size = ( int )$this->instance['thumbnail_size'];
			$settings['thumb_w'] = $size;
			$settings['thumb_h'] = $size;
		}

		if ( isset( $this->instance['thumbnail_position'] ) ) {
			$position = $this->instance['thumbnail_position'];
			if ( $position == 'left' ) {
				$settings['thumb_align'] = 'alignleft';
			} else if ( $position == 'center' ) {
				$settings['thumb_align'] = 'aligncenter';
			} else if ( $position == 'right' ) {
				$settings['thumb_align'] = 'alignright';
			}
		}

		return $settings;
	}
	/**
	 * Update the widget
	 *
	 * @param array $new
	 * @param array $old
	 * @return array
	 */
	function update( $new, $old ) {
		$instance = $old;

		$args = array(
			'title' => '',
			'template' => 'loop.php',

			// Query args
			'post_type' => 'post',
			'posts_per_page' => '5',

			'order' => 'DESC',
			'orderby' => 'date',

			'sticky' => '',

			'additional' => '',

			'thumbnail_enable' => '1',
			'thumbnail_size' => '',
			'thumbnail_position' => '',
			'excerpt_enable' => '1',
			'excerpt_length' => '55',
			'continue_reading_enable' => '0',
			'pagination_enable' => '1',
			'titles_show' => '1',
			'post_meta_enable' => '1',
			'column_count' => '1',
		);

		foreach ( $args as $key => $value ) {
			if ( isset( $new[$key] ) ) {
				$instance[$key] = $new[$key];
			} else {
				// if not found, that means it is a checkbox that is unchecked
				$instance[$key] = '0';
			}
		}

		return $instance;
	}

	/**
	 * Get all the existing files
	 *
	 * @return array
	 */
	function get_loop_templates() {
		$templates = array();

		$template_files = array(
			'loop*.php',
			'*/loop*.php',
			'content*.php',
			'*/content*.php',
		 );

		$template_dirs = array( get_template_directory(), get_stylesheet_directory() );
		$template_dirs = array_unique( $template_dirs );
		foreach( $template_dirs  as $dir ) {
			foreach( $template_files as $template_file ) {
				foreach( ( array ) glob( $dir.'/'.$template_file ) as $file ) {
					if ( file_exists( $file ) ) $templates[] = str_replace( $dir.'/', '', $file );
				}
			}
		}

		$templates = array_unique( $templates );
		sort( $templates );

		return $templates;
	}

	/**
	 * Display the form for the post loop.
	 *
	 * @param array $instance
	 * @return string|void
	 */
	function form( $instance ) {
		$instance = wp_parse_args( $instance, array(
			'title' => '',
			'template' => 'loop.php',

			// Query args
			'post_type' => 'post',
			'posts_per_page' => '5',

			'order' => 'DESC',
			'orderby' => 'date',

			'sticky' => '',

			'additional' => '',

			'thumbnail_enable' => '1',
			'thumbnail_size' => '',
			'thumbnail_position' => '',
			'excerpt_enable' => '1',
			'excerpt_length' => '55',
			'continue_reading_enable' => '0',
			'pagination_enable' => '1',
			'titles_show' => '1',
			'post_meta_enable' => '1',
			'column_count' => '1',

		 ) );

		$instance['template'] = 'loop.php';
		$instance['post_type'] = 'post';

		$templates = $this->get_loop_templates();
		if ( empty( $templates ) ) {
			?><p><?php _e( "Your theme doesn't have any post loops.", 'siteorigin-panels' ) ?></p><?php
			return;
		}

		// Get all the loop template files
		$post_types = get_post_types( array( 'public' => true ) );
		$post_types = array_values( $post_types );
		$post_types = array_diff( $post_types, array( 'attachment', 'revision', 'nav_menu_item' ) );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Title', 'siteorigin-panels' ) ?></label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_id( 'title' ) ?>" value="<?php echo esc_attr( $instance['title'] ) ?>">
		</p>
		<!-- TEMPLATE and POST TYPE are fixed now
		<p>
			<label for="<?php echo $this->get_field_id( 'template' ) ?>"><?php _e( 'Template', 'siteorigin-panels' ) ?></label>
			<select id="<?php echo $this->get_field_id( 'template' ) ?>" name="<?php echo $this->get_field_name( 'template' ) ?>">
				<?php foreach( $templates as $template ) : ?>
					<option value="<?php echo esc_attr( $template ) ?>" <?php selected( $instance['template'], $template ) ?>>
						<?php
						$headers = get_file_data( locate_template( $template ), array(
							'loop_name' => 'Loop Name',
						 ) );
						echo esc_html( ! empty( $headers['loop_name'] ) ? $headers['loop_name'] : $template );
						?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'post_type' ) ?>"><?php _e( 'Post Type', 'siteorigin-panels' ) ?></label>
			<select id="<?php echo $this->get_field_id( 'post_type' ) ?>" name="<?php echo $this->get_field_name( 'post_type' ) ?>" value="<?php echo esc_attr( $instance['post_type'] ) ?>">
				<?php foreach( $post_types as $type ) : ?>
					<option value="<?php echo esc_attr( $type ) ?>" <?php selected( $instance['post_type'], $type ) ?>><?php echo esc_html( $type ) ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		-->
		<p>
			<label for="<?php echo $this->get_field_id( 'posts_per_page' ) ?>"><?php _e( 'Posts Per Page', 'siteorigin-panels' ) ?></label>
			<input type="text" class="small-text" id="<?php echo $this->get_field_id( 'posts_per_page' ) ?>" name="<?php echo $this->get_field_name( 'posts_per_page' ) ?>" value="<?php echo esc_attr( $instance['posts_per_page'] ) ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'orderby' ) ?>" ><?php _e( 'Order By', 'siteorigin-panels' ) ?></label>
			<select id="<?php echo $this->get_field_id( 'orderby' ) ?>" name="<?php echo $this->get_field_name( 'orderby' ) ?>" value="<?php echo esc_attr( $instance['orderby'] ) ?>">
				<option value="none" <?php selected( $instance['orderby'], 'none' ) ?>><?php esc_html_e( 'None', 'siteorigin-panels' ) ?></option>
				<option value="ID" <?php selected( $instance['orderby'], 'ID' ) ?>><?php esc_html_e( 'Post ID', 'siteorigin-panels' ) ?></option>
				<option value="author" <?php selected( $instance['orderby'], 'author' ) ?>><?php esc_html_e( 'Author', 'siteorigin-panels' ) ?></option>
				<option value="name" <?php selected( $instance['orderby'], 'name' ) ?>><?php esc_html_e( 'Name', 'siteorigin-panels' ) ?></option>
				<option value="name" <?php selected( $instance['orderby'], 'name' ) ?>><?php esc_html_e( 'Name', 'siteorigin-panels' ) ?></option>
				<option value="date" <?php selected( $instance['orderby'], 'date' ) ?>><?php esc_html_e( 'Date', 'siteorigin-panels' ) ?></option>
				<option value="modified" <?php selected( $instance['orderby'], 'modified' ) ?>><?php esc_html_e( 'Modified', 'siteorigin-panels' ) ?></option>
				<option value="parent" <?php selected( $instance['orderby'], 'parent' ) ?>><?php esc_html_e( 'Parent', 'siteorigin-panels' ) ?></option>
				<option value="rand" <?php selected( $instance['orderby'], 'rand' ) ?>><?php esc_html_e( 'Random', 'siteorigin-panels' ) ?></option>
				<option value="comment_count" <?php selected( $instance['orderby'], 'comment_count' ) ?>><?php esc_html_e( 'Comment Count', 'siteorigin-panels' ) ?></option>
				<option value="menu_order" <?php selected( $instance['orderby'], 'menu_order' ) ?>><?php esc_html_e( 'Menu Order', 'siteorigin-panels' ) ?></option>
				<option value="menu_order" <?php selected( $instance['orderby'], 'menu_order' ) ?>><?php esc_html_e( 'Menu Order', 'siteorigin-panels' ) ?></option>
				<option value="post__in" <?php selected( $instance['orderby'], 'post__in' ) ?>><?php esc_html_e( 'Post In Order', 'siteorigin-panels' ) ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'order' ) ?>"><?php _e( 'Order', 'siteorigin-panels' ) ?></label>
			<select id="<?php echo $this->get_field_id( 'order' ) ?>" name="<?php echo $this->get_field_name( 'order' ) ?>" value="<?php echo esc_attr( $instance['order'] ) ?>">
				<option value="DESC" <?php selected( $instance['order'], 'DESC' ) ?>><?php esc_html_e( 'Descending', 'siteorigin-panels' ) ?></option>
				<option value="ASC" <?php selected( $instance['order'], 'ASC' ) ?>><?php esc_html_e( 'Ascending', 'siteorigin-panels' ) ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'sticky' ) ?>"><?php _e( 'Sticky Posts', 'siteorigin-panels' ) ?></label>
			<select id="<?php echo $this->get_field_id( 'sticky' ) ?>" name="<?php echo $this->get_field_name( 'sticky' ) ?>" value="<?php echo esc_attr( $instance['sticky'] ) ?>">
				<option value="" <?php selected( $instance['sticky'], '' ) ?>><?php esc_html_e( 'Default', 'siteorigin-panels' ) ?></option>
				<option value="ignore" <?php selected( $instance['sticky'], 'ignore' ) ?>><?php esc_html_e( 'Ignore Sticky', 'siteorigin-panels' ) ?></option>
				<option value="exclude" <?php selected( $instance['sticky'], 'exclude' ) ?>><?php esc_html_e( 'Exclude Sticky', 'siteorigin-panels' ) ?></option>
				<option value="only" <?php selected( $instance['sticky'], 'only' ) ?>><?php esc_html_e( 'Only Sticky', 'siteorigin-panels' ) ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'thumbnail_enable' ) ?>" >
				<?php _e( 'Enable thumbnail', 'siteorigin-panels' ) ?>
			</label>
			<input type="checkbox" <?php checked( $instance['thumbnail_enable'], '1' ) ?>  id="<?php echo $this->get_field_id( 'thumbnail_enable' ) ?>" name="<?php echo $this->get_field_name( 'thumbnail_enable' ) ?>" value="1" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'thumbnail_size' ) ?>"><?php _e( 'Thumbnail size', 'siteorigin-panels' ) ?></label>
			<input type="number" min="0" max="1000" step="1" id="<?php echo $this->get_field_id( 'thumbnail_size' ) ?>" name="<?php echo $this->get_field_name( 'thumbnail_size' ) ?>" value="<?php echo esc_attr( $instance['thumbnail_size'] ) ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'thumbnail_position' ) ?>"><?php _e( 'Thumbnail position', 'siteorigin-panels' ) ?></label>
			<select id="<?php echo $this->get_field_id( 'thumbnail_position' ) ?>" name="<?php echo $this->get_field_name( 'thumbnail_position' ) ?>" >
				<option value="" <?php selected( $instance['thumbnail_position'], '' ) ?>><?php esc_html_e( 'Default', 'siteorigin-panels' ) ?></option>
				<option value="left" <?php selected( $instance['thumbnail_position'], 'left' ) ?>><?php esc_html_e( 'Left', 'siteorigin-panels' ) ?></option>
				<option value="center" <?php selected( $instance['thumbnail_position'], 'center' ) ?>><?php esc_html_e( 'Center', 'siteorigin-panels' ) ?></option>
				<option value="right" <?php selected( $instance['thumbnail_position'], 'right' ) ?>><?php esc_html_e( 'Right', 'siteorigin-panels' ) ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'excerpt_enable' ) ?>" >
				<?php _e( 'Enable excerpt', 'siteorigin-panels' ) ?>
			</label>
			<input type="checkbox" <?php checked( $instance['excerpt_enable'], '1' ) ?>  id="<?php echo $this->get_field_id( 'excerpt_enable' ) ?>" name="<?php echo $this->get_field_name( 'excerpt_enable' ) ?>" value="1" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'excerpt_length' ) ?>" ><?php _e( 'Excerpt length', 'siteorigin-panels' ) ?></label>
			<input type="number" min="0" max="1000" step="1" id="<?php echo $this->get_field_id( 'excerpt_length' ) ?>" name="<?php echo $this->get_field_name( 'excerpt_length' ) ?>" value="<?php echo esc_attr( $instance['excerpt_length'] ) ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'continue_reading_enable' ) ?>">
				<?php _e( 'Enable continue reading', 'siteorigin-panels' ) ?>
			</label>
			<input type="checkbox" <?php checked( $instance['continue_reading_enable'], '1' ) ?> id="<?php echo $this->get_field_id( 'continue_reading_enable' ) ?>" name="<?php echo $this->get_field_name( 'continue_reading_enable' ) ?>" value="1" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'pagination_enable' ) ?>">
				<?php _e( 'Enable pagination', 'siteorigin-panels' ) ?>
			</label>
			<input type="checkbox" <?php checked( $instance['pagination_enable'], '1' ) ?> id="<?php echo $this->get_field_id( 'pagination_enable' ) ?>" name="<?php echo $this->get_field_name( 'pagination_enable' ) ?>" value="1" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'titles_show' ) ?>">
				<?php _e( 'Show titles', 'siteorigin-panels' ) ?>
			</label>
			<input type="checkbox" <?php checked( $instance['titles_show'], '1' ) ?> id="<?php echo $this->get_field_id( 'titles_show' ) ?>" name="<?php echo $this->get_field_name( 'titles_show' ) ?>" value="1" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'column_count' ) ?>"><?php _e( 'Number of columns', 'siteorigin-panels' ) ?></label>
			<input type="number" min="1" max="10" step="1" id="<?php echo $this->get_field_id( 'column_count' ) ?>" name="<?php echo $this->get_field_name( 'column_count' ) ?>" value="<?php echo esc_attr( $instance['column_count'] ) ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'post_meta_enable' ) ?>">
				<?php _e( 'Enable post meta', 'siteorigin-panels' ) ?>
			</label>
			<input type="checkbox" <?php checked( $instance['post_meta_enable'], '1' ) ?> id="<?php echo $this->get_field_id( 'post_meta_enable' ) ?>" name="<?php echo $this->get_field_name( 'post_meta_enable' ) ?>" value="1" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'additional' ) ?>"><?php _e( 'Additional ', 'siteorigin-panels' ) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'additional' ) ?>" name="<?php echo $this->get_field_name( 'additional' ) ?>" value="<?php echo esc_attr( $instance['additional'] ) ?>" />
			<small><?php printf( __( 'Additional query arguments. See <a href="%s" target="_blank">query_posts</a>.', 'siteorigin-panels' ), 'http://codex.wordpress.org/Function_Reference/query_posts' ) ?></small>
		</p>
	<?php
	}
}

/**
 * Register the widgets.
 */
function siteorigin_panels_widgets_init() {
	register_widget( 'SiteOrigin_Panels_Widgets_PostLoop' );
}
// simply plugin by not including these widgets
add_action( 'widgets_init', 'siteorigin_panels_widgets_init' );

/**
 * Enqueue widget compatibility files.
 */
function siteorigin_panels_compatibility_init()
{

//	if ( is_plugin_active( 'wx-pootle-text-widget/pootlepress-text-widget.php' ) ||
//		is_plugin_active( 'pootle-text-widget-master/pootlepress-text-widget.php' )
//	) {
		include plugin_dir_path( __FILE__ ) . '/compat/pootle-text-widget/pootle-text-widget.php';
//	}
}

function pp_page_builder_pootle_text_widget_frontend_style() {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

//	if ( is_plugin_active( 'wx-pootle-text-widget/pootlepress-text-widget.php' ) ||
//		is_plugin_active( 'pootle-text-widget-master/pootlepress-text-widget.php' )
//	) {

		$output = '';

		global $woo_options;

		$font_text = get_option( 'woo_font_text' );
		$font_h1 = get_option( 'woo_font_h1' );
		$font_h2 = $woo_options['woo_font_h2'];
		$font_h3 = $woo_options['woo_font_h3'];
		$font_h4 = $woo_options['woo_font_h4'];
		$font_h5 = $woo_options['woo_font_h5'];
		$font_h6 = $woo_options['woo_font_h6'];

		if ( $font_text )
			$output .= '.panel-grid  .widget_pootle-text-widget p { ' . pp_page_builder_generate_font_css( $font_text, 1.5 ) . ' }' . "\n";
		if ( $font_h1 )
			$output .= '.panel-grid  .widget_pootle-text-widget h1 { ' . pp_page_builder_generate_font_css( $font_h1, 1.2 ) . ' }';
		if ( $font_h2 )
			$output .= '.panel-grid  .widget_pootle-text-widget h2 { ' . pp_page_builder_generate_font_css( $font_h2, 1.2 ) . ' }';
		if ( $font_h3 )
			$output .= '.panel-grid  .widget_pootle-text-widget h3 { ' . pp_page_builder_generate_font_css( $font_h3, 1.2 ) . '; margin-bottom: 0.5em; padding: 0; border-bottom: none; }';
		if ( $font_h4 )
			$output .= '.panel-grid  .widget_pootle-text-widget h4 { ' . pp_page_builder_generate_font_css( $font_h4, 1.2 ) . ' }';
		if ( $font_h5 )
			$output .= '.panel-grid  .widget_pootle-text-widget h5 { ' . pp_page_builder_generate_font_css( $font_h5, 1.2 ) . ' }';
		if ( $font_h6 )
			$output .= '.panel-grid  .widget_pootle-text-widget h6 { ' . pp_page_builder_generate_font_css( $font_h6, 1.2 ) . ' }' . "\n";


		echo "<style>\n $output \n</style>\n";
//	}
}

function pp_page_builder_generate_font_css( $option, $em = '1' ) {

	// Test if font-face is a Google font
	global $google_fonts;
	if ( is_array( $google_fonts ) ) {
		foreach ( $google_fonts as $google_font ) {

			// Add single quotation marks to font name and default arial sans-serif ending
			if ( $option['face'] == $google_font['name'] )
				$option['face'] = "'" . $option['face'] . "', arial, sans-serif";

		} // END foreach
	}

	if ( ! @$option['style'] && ! @$option['size'] && ! @$option['unit'] && ! @$option['color'] )
		return 'font-family: '.stripslashes( $option["face"] ).';';
	else
		return 'font:'.$option['style'].' '.$option['size'].$option['unit'].'/'.$em.'em '.stripslashes( $option['face'] ).';color:'.$option['color'].';';
}

include plugin_dir_path( __FILE__ ) . '/compat/woo-tabs.php';

require_once( 'pootle-visual-editor-2/black-studio-tinymce-widget.php' );