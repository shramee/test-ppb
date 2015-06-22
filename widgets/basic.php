<?php

class SiteOrigin_Panels_Widgets_Gallery extends WP_Widget {
	function __construct() {
		parent::__construct(
			'siteorigin-panels-gallery',
			__( 'Gallery ( Pootle )', 'siteorigin-panels' ),
			array(
				'description' => __( 'Displays a gallery.', 'siteorigin-panels' ),
			 )
		 );
	}

	function widget( $args, $instance ) {
		echo $args['before_widget'];

		$shortcode_attr = array();
		foreach( $instance as $k => $v ) {
			if ( empty( $v ) ) continue;
			$shortcode_attr[] = $k.'="'.esc_attr( $v ).'"';
		}

		echo do_shortcode( '[gallery '.implode( ' ', $shortcode_attr ).']' );

		echo $args['after_widget'];
	}

	function update( $new, $old ) {
		return $new;
	}

	function form( $instance ) {
		global $_wp_additional_image_sizes;

		$types = apply_filters( 'siteorigin_panels_gallery_types', array() );

		$instance = wp_parse_args( $instance, array(
			'ids' => '',
			'size' => apply_filters( 'siteorigin_panels_gallery_default_size', '' ),
			'type' => apply_filters( 'siteorigin_panels_gallery_default_type', '' ),
			'columns' => 3,
			'link' => '',

		 ) );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'ids' ) ?>"><?php _e( 'Gallery Images', 'siteorigin-panels' ) ?></label>
			<a href="#" onclick="return false;" class="so-gallery-widget-select-attachments hidden"><?php _e( 'edit gallery', 'siteorigin-panels' ) ?></a>
			<input type="text" class="widefat" value="<?php echo esc_attr( $instance['ids'] ) ?>" name="<?php echo $this->get_field_name( 'ids' ) ?>" />
		</p>
		<p class="description">
			<?php _e( "Comma separated attachment IDs. Defaults to all current page's attachments." ) ?>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'size' ) ?>"><?php _e( 'Image Size', 'siteorigin-panels' ) ?></label>
			<select name="<?php echo $this->get_field_name( 'size' ) ?>" id="<?php echo $this->get_field_id( 'size' ) ?>">
				<option value="" <?php selected( empty( $instance['size'] ) ) ?>><?php esc_html_e( 'Default', 'siteorigin-panels' ) ?></option>
				<option value="large" <?php selected( 'large', $instance['size'] ) ?>><?php esc_html_e( 'Large', 'siteorigin-panels' ) ?></option>
				<option value="medium" <?php selected( 'medium', $instance['size'] ) ?>><?php esc_html_e( 'Medium', 'siteorigin-panels' ) ?></option>
				<option value="thumbnail" <?php selected( 'thumbnail', $instance['size'] ) ?>><?php esc_html_e( 'Thumbnail', 'siteorigin-panels' ) ?></option>
				<option value="full" <?php selected( 'full', $instance['size'] ) ?>><?php esc_html_e( 'Full', 'siteorigin-panels' ) ?></option>
				<?php if ( ! empty( $_wp_additional_image_sizes ) ) : foreach ( $_wp_additional_image_sizes as $name => $info ) : ?>
					<option value="<?php echo esc_attr( $name ) ?>" <?php selected( $name, $instance['size'] ) ?>><?php echo esc_html( $name ) ?></option>
				<?php endforeach; endif; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'type' ) ?>"><?php _e( 'Gallery Type', 'siteorigin-panels' ) ?></label>
			<input type="text" class="regular" value="<?php echo esc_attr( $instance['type'] ) ?>" name="<?php echo $this->get_field_name( 'type' ) ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'columns' ) ?>"><?php _e( 'Columns', 'siteorigin-panels' ) ?></label>
			<input type="text" class="regular" value="<?php echo esc_attr( $instance['columns'] ) ?>" name="<?php echo $this->get_field_name( 'columns' ) ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'link' ) ?>"><?php _e( 'Link To', 'siteorigin-panels' ) ?></label>
			<select name="<?php echo $this->get_field_name( 'link' ) ?>" id="<?php echo $this->get_field_id( 'link' ) ?>">
				<option value="" <?php selected( '', $instance['link'] ) ?>><?php esc_html_e( 'Attachment Page', 'siteorigin-panels' ) ?></option>
				<option value="file" <?php selected( 'file', $instance['link'] ) ?>><?php esc_html_e( 'File', 'siteorigin-panels' ) ?></option>
				<option value="none" <?php selected( 'none', $instance['link'] ) ?>><?php esc_html_e( 'None', 'siteorigin-panels' ) ?></option>
			</select>
		</p>

		<?php
	}
}

class SiteOrigin_Panels_Widgets_PostContent extends WP_Widget {
	function __construct() {
		parent::__construct(
			'siteorigin-panels-post-content',
			__( 'Post Content ( Pootle )', 'siteorigin-panels' ),
			array(
				'description' => __( 'Displays some form of post content form the current post.', 'siteorigin-panels' ),
			 )
		 );
	}

	function widget( $args, $instance ) {
		if ( is_admin() ) return;

		echo $args['before_widget'];
		$content = apply_filters( 'siteorigin_panels_widget_post_content', $this->default_content( $instance['type'] ) );
		echo $content;
		echo $args['after_widget'];
	}

	/**
	 * The default content for post types
	 * @param $type
	 * @return string
	 */
	function default_content( $type ) {
		global $post;
		if ( empty( $post ) ) return;

		switch( $type ) {
			case 'title' :
				return '<h1 class="entry-title">' . $post->post_title . '</h1>';
			case 'content' :
				return '<div class="entry-content">' . wpautop( $post->post_content ) . '</div>';
			case 'featured' :
				if ( ! has_post_thumbnail() ) return '';
				return '<div class="featured-image">' . get_the_post_thumbnail( $post->ID ) . '</div>';
			default :
				return '';
		}
	}

	function update( $new, $old ) {
		return $new;
	}

	function form( $instance ) {
		$instance = wp_parse_args( $instance, array(
			'type' => 'content',
		 ) );

		$types = apply_filters( 'siteorigin_panels_widget_post_content_types', array(
			'' => __( 'None', 'siteorigin-panels' ),
			'title' => __( 'Title', 'siteorigin-panels' ),
			'featured' => __( 'Featured Image', 'siteorigin-panels' ),
		 ) );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'type' ) ?>"><?php _e( 'Display Content', 'siteorigin-panels' ) ?></label>
			<select id="<?php echo $this->get_field_id( 'type' ) ?>" name="<?php echo $this->get_field_name( 'type' ) ?>">
				<?php foreach ( $types as $type_id => $title ) : ?>
					<option value="<?php echo esc_attr( $type_id ) ?>" <?php selected( $type_id, $instance['type'] ) ?>><?php echo esc_html( $title ) ?></option>
				<?php endforeach ?>
			</select>
		</p>
	<?php
	}
}

class SiteOrigin_Panels_Widgets_Image extends WP_Widget {
	function __construct() {
		parent::__construct(
			'siteorigin-panels-image',
			__( 'Image ( Pootle )', 'siteorigin-panels' ),
			array(
				'description' => __( 'Displays a simple image.', 'siteorigin-panels' ),
			 )
		 );
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['href'] ) ) echo '<a href="' . $instance['href'] . '">';
		echo '<img src="'.esc_url( $instance['src'] ).'" />';
		if ( ! empty( $instance['href'] ) ) echo '</a>';
		echo $args['after_widget'];
	}

	function update( $new, $old ) {
		$new = wp_parse_args( $new, array(
			'src' => '',
			'href' => '',
		 ) );
		return $new;
	}

	function form( $instance ) {
		$instance = wp_parse_args( $instance, array(
			'src' => '',
			'href' => '',
		 ) );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'src' ) ?>"><?php _e( 'Image URL', 'siteorigin-panels' ) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'src' ) ?>" name="<?php echo $this->get_field_name( 'src' ) ?>" value="<?php echo esc_attr( $instance['src'] ) ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'href' ) ?>"><?php _e( 'Destination URL', 'siteorigin-panels' ) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'href' ) ?>" name="<?php echo $this->get_field_name( 'href' ) ?>" value="<?php echo esc_attr( $instance['href'] ) ?>" />
		</p>
	<?php
	}
}

/**
 * Display a loop of posts.
 *
 * Class SiteOrigin_Panels_Widgets_PostLoop
 */
class SiteOrigin_Panels_Widgets_PostLoop extends WP_Widget{

	private $instance;

	function __construct() {
		parent::__construct(
			'siteorigin-panels-postloop',
			__( 'Post Loop ( Pootle )', 'siteorigin-panels' ),
			array(
				'description' => __( 'Displays a post loop.', 'siteorigin-panels' ),
			)
		);
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		require "post-loop-public.php";
	}

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
 * A panel that lets you embed video.
 */
class SiteOrigin_Panels_Widgets_EmbeddedVideo extends WP_Widget {
	function __construct() {
		parent::__construct(
			'siteorigin-panels-embedded-video',
			__( 'Embedded Video ( Pootle )', 'siteorigin-panels' ),
			array(
				'description' => __( 'Embeds a video.', 'siteorigin-panels' ),
			 )
		 );
	}

	/**
	 * Display the video using
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		$embed = new WP_Embed();

		if ( ! wp_script_is( 'fitvids' ) )
			wp_enqueue_script( 'fitvids', plugin_dir_url( POOTLEPAGE_BASE_FILE ) . 'widgets/js/jquery.fitvids.min.js', array( 'jquery' ), POOTLEPAGE_VERSION );

		if ( ! wp_script_is( 'siteorigin-panels-embedded-video' ) )
			wp_enqueue_script( 'siteorigin-panels-embedded-video', plugin_dir_url( POOTLEPAGE_BASE_FILE ) . 'widgets/js/embedded-video.min.js', array( 'jquery', 'fitvids' ), POOTLEPAGE_VERSION );

		echo $args['before_widget'];
		?><div class="siteorigin-fitvids"><?php echo $embed->run_shortcode( '[embed]' . $instance['video'] . '[/embed]' ) ?></div><?php
		echo $args['after_widget'];
	}

	/**
	 * Display the embedded video form.
	 *
	 * @param array $instance
	 * @return string|void
	 */
	function form( $instance ) {
		$instance = wp_parse_args( $instance, array(
			'video' => '',
		 ) )

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'video' ) ?>"><?php _e( 'Video', 'siteorigin-panels' ) ?></label>
			<input type="text" class="widefat" name="<?php echo $this->get_field_name( 'video' ) ?>" id="<?php echo $this->get_field_id( 'video' ) ?>" value="<?php echo esc_attr( $instance['video'] ) ?>" />
		</p>
	<?php
	}

	function update( $new, $old ) {
		$new['video'] = str_replace( 'https://', 'http://', $new['video'] );
		return $new;
	}
}

class SiteOrigin_Panels_Widgets_Video extends WP_Widget {
	function __construct() {
		parent::__construct(
			'siteorigin-panels-video',
			__( 'Self Hosted Video ( Pootle )', 'siteorigin-panels' ),
			array(
				'description' => __( 'A self hosted video player.', 'siteorigin-panels' ),
			 )
		 );
	}

	function widget( $args, $instance ) {
		if ( empty( $instance['url'] ) ) return;
		static $video_widget_id = 1;

		$instance = wp_parse_args( $instance, array(
			'url' => '',
			'poster' => '',
			'skin' => 'siteorigin',
			'ratio' => 1.777,
			'autoplay' => false,
		 ) );

		// Enqueue jPlayer scripts and intializer
		wp_enqueue_script( 'siteorigin-panels-video-jplayer', plugin_dir_url( POOTLEPAGE_BASE_FILE ) . 'video/jplayer/jquery.jplayer.min.min.js', array( 'jquery' ), POOTLEPAGE_VERSION, true );
		wp_enqueue_script( 'siteorigin-panels-video', plugin_dir_url( POOTLEPAGE_BASE_FILE ) . 'video/panels.video.jquery.min.js', array( 'jquery' ), POOTLEPAGE_VERSION, true );

		// Enqueue the SiteOrigin jPlayer skin
		$skin = sanitize_file_name( $instance['skin'] );
		wp_enqueue_style( 'siteorigin-panels-video-jplayer-skin', plugin_dir_url( POOTLEPAGE_BASE_FILE ).'video/jplayer/skins/'.$skin.'/jplayer.'.$skin.'.css', array(), POOTLEPAGE_VERSION );

		$file = $instance['url'];
		$poster = ! empty( $instance['poster'] ) ? $instance['poster'] :  plugin_dir_url( POOTLEPAGE_BASE_FILE ).'video/poster.jpg';
		$instance['ratio'] = floatval( $instance['ratio'] );
		if ( empty( $instance['ratio'] ) ) $instance['ratio'] = 1.777;

		echo $args['before_widget'];

		?>
		<div class="jp-video" id="jp_container_<?php echo $video_widget_id ?>">
			<div class="jp-type-single" id="jp_interface_<?php echo $video_widget_id ?>">
				<div id="jquery_jplayer_<?php echo $video_widget_id ?>" class="jp-jplayer"
					 data-video="<?php echo esc_url( $file ) ?>"
					 data-poster="<?php echo esc_url( $poster ) ?>"
					 data-ratio="<?php echo floatval( $instance['ratio'] ) ?>"
					 data-autoplay="<?php echo esc_attr( $instance['autoplay'] ) ?>"
					 data-swfpath="<?php echo plugin_dir_url( POOTLEPAGE_BASE_FILE ).'video/jplayer/' ?>"
					 data-mobile="<?php echo wp_is_mobile() ? 'true' : 'false' ?>"></div>

				<?php $this->display_gui( $instance['skin'] ) ?>
			</div>
		</div>
		<?php

		$video_widget_id++;
		echo $args['after_widget'];
	}

	function display_gui( $skin ) {
		$file = plugin_dir_path( POOTLEPAGE_BASE_FILE ).'video/jplayer/skins/'.$skin.'/gui.php';
		if ( file_exists( $file ) ) include plugin_dir_path( POOTLEPAGE_BASE_FILE ).'video/jplayer/skins/'.$skin.'/gui.php';
	}

	function update( $new, $old ) {
		$new['skin'] = sanitize_file_name( $new['skin'] );
		$new['ratio'] = floatval( $new['ratio'] );
		$new['autoplay'] = ! empty( $new['autoplay'] ) ? 1 : 0;
		return $new;
	}

	function form( $instance ) {
		$instance = wp_parse_args( $instance, array(
			'url' => '',
			'poster' => '',
			'skin' => 'siteorigin',
			'ratio' => 1.777,
			'autoplay' => false,
		 ) );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'url' ) ?>"><?php _e( 'Video URL', 'siteorigin-panels' ) ?></label>
			<input id="<?php echo $this->get_field_id( 'url' ) ?>" name="<?php echo $this->get_field_name( 'url' ) ?>" type="text" class="widefat" value="<?php echo esc_attr( $instance['url'] ) ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'poster' ) ?>"><?php _e( 'Poster URL', 'siteorigin-panels' ) ?></label>
			<input id="<?php echo $this->get_field_id( 'poster' ) ?>" name="<?php echo $this->get_field_name( 'poster' ) ?>" type="text" class="widefat" value="<?php echo esc_attr( $instance['poster'] ) ?>" />
			<small class="description"><?php _e( 'An image that displays before the video starts playing.', 'siteorigin-panels' ) ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'skin' ) ?>"><?php _e( 'Skin', 'siteorigin-panels' ) ?></label>
			<select id="<?php echo $this->get_field_id( 'skin' ) ?>" name="<?php echo $this->get_field_name( 'skin' ) ?>">
				<option value="siteorigin" <?php selected( $instance['skin'], 'siteorigin' ) ?>><?php esc_html_e( 'SiteOrigin', 'siteorigin-panels' ) ?></option>
				<option value="premium" <?php selected( $instance['skin'], 'premium' ) ?>><?php esc_html_e( 'Premium Pixels', 'siteorigin-panels' ) ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'ratio' ) ?>"><?php _e( 'Aspect Ratio', 'siteorigin-panels' ) ?></label>
			<input id="<?php echo $this->get_field_id( 'ratio' ) ?>" name="<?php echo $this->get_field_name( 'ratio' ) ?>" type="text" class="widefat" value="<?php echo esc_attr( $instance['ratio'] ) ?>" />
			<small class="description"><?php _e( '1.777 is HD standard.', 'siteorigin-panels' ) ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'autoplay' ) ?>">
				<input id="<?php echo $this->get_field_id( 'autoplay' ) ?>" name="<?php echo $this->get_field_name( 'autoplay' ) ?>" type="checkbox" value="1" />
				<?php _e( 'Auto Play Video', 'siteorigin-panels' ) ?>
			</label>
		</p>
		<?php
	}
}

/**
 * A shortcode for self hosted video.
 *
 * @param array $atts
 * @return string
 */
function siteorigin_panels_video_shortcode( $atts ) {
	/**
	 * @var string $url
	 * @var string $poster
	 * @var string $skin
	 */
	$instance = shortcode_atts( array(
		'url' => '',
		'src' => '',
		'poster' => plugin_dir_url( POOTLEPAGE_BASE_FILE ).'video/poster.jpg',
		'skin' => 'siteorigin',
		'ratio' => 1.777,
		'autoplay' => 0,
	 ), $atts );

	if ( ! empty( $instance['src'] ) ) $instance['url'] = $instance['src'];
	if ( empty( $instance['url'] ) ) return;

	ob_start();
	the_widget( 'SiteOrigin_Panels_Widgets_Video', $instance );
	return ob_get_clean();

}
add_shortcode( 'self_video', 'siteorigin_panels_video_shortcode' );


/**
 * Register the widgets.
 */
function siteorigin_panels_widgets_init() {
//	register_widget( 'SiteOrigin_Panels_Widgets_Gallery' );
//	register_widget( 'SiteOrigin_Panels_Widgets_PostContent' );
//	register_widget( 'SiteOrigin_Panels_Widgets_Image' );
	register_widget( 'SiteOrigin_Panels_Widgets_PostLoop' );
//	register_widget( 'SiteOrigin_Panels_Widgets_EmbeddedVideo' );
//	register_widget( 'SiteOrigin_Panels_Widgets_Video' );
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
// Using VE2, this compatibility is for VE1
//add_action( 'admin_init', 'siteorigin_panels_compatibility_init', 5 );

//TODO check this part later
//add_action( 'wp_head', 'pp_page_builder_pootle_text_widget_frontend_style' );

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

//require_once( plugin_dir_path( __FILE__ ) . '/pootle-visual-editor/pootlepress-text-widget.php' );
require_once( plugin_dir_path( __FILE__ ) . '/pootle-visual-editor-2/black-studio-tinymce-widget.php' );