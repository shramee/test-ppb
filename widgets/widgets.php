<?php
// Include all the basic widgets
include plugin_dir_path( __FILE__ ) . '/less/functions.php';
include 'widget-helper.php';

/**
 * Include all the widget files and register their widgets
 */
function origin_widgets_init() {
	foreach ( glob( plugin_dir_path( __FILE__ ).'/widgets/*/*.php' ) as $file ) {
		include_once ( $file );

		$p = pathinfo( $file );
		$class = $p[ 'filename' ];
		$class = str_replace( '-', ' ', $class );
		$class = ucwords( $class );
		$class = str_replace( ' ', '_', $class );

		$class = 'SiteOrigin_Panels_Widget_'.$class;
		if ( class_exists( $class ) ) register_widget( $class );
	}
}
//add_action( 'widgets_init', 'origin_widgets_init' );

function origin_widgets_enqueue( $prefix ) {
	if ( $prefix == 'widgets.php' ) wp_enqueue_script( 'origin-widgets-admin-script', plugin_dir_url( POOTLEPAGE_BASE_FILE ) . 'widgets/js/admin.min.js', array( 'jquery' ), POOTLEPAGE_VERSION );
}
add_action( 'admin_enqueue_scripts', 'origin_widgets_enqueue' );

function origin_widgets_display_css() {
	if ( is_admin() ) return;
	if ( empty( $_GET[ 'action' ] ) || $_GET[ 'action' ] != 'origin_widgets_css' ) return;
	if ( empty( $_GET[ 'class' ] ) || empty( $_GET[ 'style' ] ) || empty( $_GET[ 'preset' ] ) ) return;
	if ( strpos( $_GET[ 'class' ], 'SiteOrigin_Panels_Widget_' ) !== 0 ) return;

	header( "Content-type: text/css" );
	echo origin_widgets_generate_css( filter_input( INPUT_GET, 'class' ), filter_input( INPUT_GET, 'style' ), filter_input( INPUT_GET, 'preset' ), filter_input( INPUT_GET, 'ver' ) );
	exit();
}
add_action( 'init', 'origin_widgets_display_css' );

function origin_widgets_generate_css( $class, $style, $preset, $version = null ) {
	$widget = new $class();
	if ( ! is_subclass_of( $widget, 'SiteOrigin_Panels_Widget' ) ) return '';
	if ( empty( $version ) ) $version = POOTLEPAGE_VERSION;

	$id = str_replace( '_', '', strtolower( str_replace( 'SiteOrigin_Panels_Widget_', '', $class ) ) );
	$key = strtolower( $id.'-'.$style.'-'. $preset.'-'.str_replace( '.', '', $version ) );

	$css = get_site_transient( 'origin_wcss:'.$key );
	if ( $css === false || ( defined( 'SITEORIGIN_PANELS_NOCACHE' ) && SITEORIGIN_PANELS_NOCACHE ) ) {

		echo "/* Regenerate Cache */\n\n";
		// Recreate the CSS
		$css = $widget->create_css( $style, $preset );
		$css = preg_replace( '#/\*.*?\*/#s', '', $css );
		$css = preg_replace( '/\s*( [ {}|:;, ] )\s+/', '$1', $css );
		$css = preg_replace( '/\s\s+( .* )/', '$1', $css );
		$css = str_replace( ';}', '}', $css );

		set_site_transient( 'origin_wcss:'.$key, $css, 86400 );
	}

	return $css;
}

/**
 * Class SiteOrigin_Panels_Widget
 */
abstract class SiteOrigin_Panels_Widget extends WP_Widget{
	public $form_args;
	protected $demo;
	protected $origin_id;
	public $sub_widgets;

	private $styles;

	/**
	 * Create the widget
	 *
	 * @param string $name Name for the widget displayed on the configuration page.
	 * @param array $widget_options Optional Passed to wp_register_sidebar_widget()
	 *	 - description: shown on the configuration page
	 *	 - classname
	 * @param array $control_options Optional Passed to wp_register_widget_control()
	 *	 - width: required if more than 250px
	 *	 - height: currently not used but may be needed in the future
	 * @param array $form Form arguments.
	 * @param array $demo Values for the demo of the page builder widget.
	 * @internal param string $id_base
	 */
	function __construct( $name, $widget_options = array(), $control_options = array(), $form = array(), $demo = array() ) {
		$id_base = str_replace( 'SiteOrigin_Panels_Widget_', '', get_class( $this ) );
		$id_base = strtolower( str_replace( '_', '-', $id_base ) );

		parent::__construct( 'origin_'.$id_base, $name, $widget_options, $control_options );
		$this->origin_id = $id_base;

		$this->form_args = $form;
		$this->demo = $demo;
		$this->styles = array();
		$this->sub_widgets = array();
		$this->helper = new SiteOrigin_Panels_Widget_Helper();
	}

	/**
	 * Update the widget and save the new CSS.
	 *
	 * @param array $old
	 * @param array $new
	 * @return array
	 */
	function update( $new, $old ) {

		// We wont clear cache if this is a preview
		if ( ! siteorigin_panels_is_preview() ) {
			// Remove the old CSS file
			if ( ! empty( $old[ 'origin_style' ] ) ) {
				list( $style, $preset ) = explode( ':', $old[ 'origin_style' ] );
				$this->clear_css_cache( $style, $preset );
			}

			// Clear the cache for all sub widgets
			if ( ! empty( $this->sub_widgets ) ) {
				global $wp_widget_factory;
				foreach ( $this->sub_widgets as $id => $sub ) {
					if ( empty( $old[ 'origin_style_'.$id ] ) ) continue;
					$the_widget = $wp_widget_factory->widgets[ $sub[ 1 ] ];
					list( $style, $preset ) = explode( ':', $old[ 'origin_style_'.$id ] );

					$the_widget->clear_css_cache( $style, $preset );
				}
			}



		}

		foreach ( $this->form_args as $field_id => $field_args ) {
			if ( $field_args[ 'type' ] == 'checkbox' ) {
				$new[ $field_id ] = ! empty( $new[ $field_id ] );
			}
		}

		return $new;
	}

	/**
	 * Display the form for the widget. Auto generated from form array.
	 *
	 * @param array $instance
	 * @return string|void
	 */
	public function form( $instance ) {
		?>
		<p>
			<?php _e( "We're moving this widget into a separate plugin in order to keep Page Builder core light weight.", 'siteorigin-panels' ) ?>
			<?php
			printf(
				__( "Either find an alternative in our <a href='%s' target='_blank'>recommended widgets</a> or install the <a href='%s' target='_blank'>Legacy Widgets plugin</a> to continue using it.", 'siteorigin-panels' ),
				admin_url( 'plugin-install.php?tab=favorites&user=siteorigin-pagebuilder' ),
				'http://siteorigin.com/page-builder-legacy-widgets/'
			)
			?>
		</p>
		<?php

		foreach ( $this->form_args as $field_id => $field_args ) {
			$this->helper->form_field_output( $field_id, $field_args, $this );
		}

		if ( ! isset( $instance[ 'origin_style' ] ) ) {
			$instance[ 'origin_style' ] = ! empty( $this->widget_options[ 'default_style' ] ) ? $this->widget_options[ 'default_style' ] : false;
		}

		do_action( 'siteorigin_panels_widget_before_styles', $this, $instance );

		// Now, lets add the style options.
		$styles = $this->get_styles();
		if ( ! empty( $styles ) ) {
			$this->form_style_options( $instance, $this );
		}

		do_action( 'siteorigin_panels_widget_before_substyles', $this, $instance );

		foreach ( $this->sub_widgets as $id => $sub ) {
			global $wp_widget_factory;
			$the_widget = $wp_widget_factory->widgets[ $sub[ 1 ] ];

			if ( ! isset( $instance[ 'origin_style_'.$id ] ) ) $instance[ 'origin_style_'.$id ] = ! empty( $this->widget_options[ 'default_style_'.$id ] ) ? $this->widget_options[ 'default_style_'.$id ] : false;
			$this->form_style_options( $instance, $the_widget, 'origin_style_'.$id, $sub[ 0 ] );
		}
		do_action( 'siteorigin_panels_widget_after_styles', $this, $instance );
	}

	public function form_style_options( $instance, $the_widget, $id = 'origin_style', $sub = '' ){
		?>
		<p>
			<label for="<?php echo $this->get_field_id( $id ) ?>"><?php printf( __( '%s Style', 'siteorigin-panels' ), $sub ) ?></label>
			<select name="<?php echo $this->get_field_name( $id ) ?>" id="<?php echo $this->get_field_id( $id ) ?>">
				<?php foreach( $the_widget->get_styles() as $style_id => $style_info ) : $presets = $the_widget->get_style_presets( $style_id ); ?>
					<?php if ( ! empty( $presets ) ) : foreach( $presets as $preset_id => $preset ) : ?>
						<option value="<?php echo esc_attr( $style_id.':'.$preset_id ) ?>" <?php selected( $style_id.':'.$preset_id, $instance[ $id ] ) ?>>
							<?php echo esc_html( $style_info['Name'] . ' - ' . ucwords( str_replace( '_', ' ', $preset_id ) ) ) ?>
						</option>
					<?php endforeach; endif; ?>
				<?php endforeach ?>
			</select>
		</p>
	<?php
	}

	/**
	 * Render the widget.
	 *
	 * @param array $args
	 * @param array $instance
	 * @return bool|void
	 */
	function widget( $args, $instance ) {

		// Set up defaults for all the widget args
		foreach ( $this->form_args as $field_id => $field_args ) {
			if ( isset( $field_args[ 'default' ] ) && ! isset( $instance[ $field_id ] ) ) {
				$instance[ $field_id ] = $field_args[ 'default' ];
			}
			if ( ! isset( $instance[ $field_id ] ) ) $instance[ $field_id ] = false;
		}

		// Filter the title
		if ( ! empty( $instance[ 'title' ] ) ) {
			$instance[ 'title' ] = apply_filters( 'widget_title', $instance[ 'title' ], $instance, $this->id_base );
		}

		list( $style, $preset, $template ) = $this->widget_style_preset( $instance, $this );

		$template_file = $this->helper->widget_check_template( $args, $template, $this );
		if( empty( $template_file ) ) { return; }

		//Dynamically generate the CSS
		$this->helper->widget_dynamically_generate_css( $style, $preset, $instance, $this );

		if ( method_exists( $this, 'enqueue_scripts' ) ) { $this->enqueue_scripts(); }

		$widget_classes = $this->helper->widget_dynamically_generate_css( $style, $preset, $instance, $this );

		echo $args[ 'before_widget' ];
		echo '<div class = "'.esc_attr( implode( ' ', $widget_classes ) ).'">';
		include $template_file;
		echo '</div>';
		echo $args[ 'after_widget' ];
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Extra functions specific to a SiteOrigin widget.
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * A sub widget is a widget that's style is required by this widget
	 *
	 * @param $id
	 * @param $instance
	 */
	function sub_widget( $id, $instance ) {
		$sub = $this->sub_widgets[ $id ];
		global $wp_widget_factory;
		$the_widget = $wp_widget_factory->widgets[ $sub[ 1 ] ];
		$the_widget->widget( array( 'before_widget' => '', 'after_widget' => '' ), $instance );
	}

	/**
	 * Get the CSS for the given style and preset
	 *
	 * @param $style
	 * @param $preset
	 * @return string
	 */
	function create_css( $style, $preset ) {
		$paths = $this->get_widget_paths();
		$style_file = false;

		// Find the file - exit if it can't be found.
		foreach ( $paths as $path ) {
			if ( file_exists( $path.'/'.$this->origin_id.'/styles/'.$style.'.less' ) ) {
				$style_file = $path.'/'.$this->origin_id.'/styles/'.$style.'.less';
				break;
			}
		}
		if ( empty( $style_file ) ) return '';

		if ( ! class_exists( 'lessc' ) ) include plugin_dir_path( __FILE__ ).'lib/lessc.inc.php';

		foreach ( $this->get_widget_folders() as $folder => $folder_url ) {
			$filename = rtrim( $folder, '/' ) . '/' . $this->origin_id.'/styles/'.$style.'.less';
			if ( file_exists( $filename ) ) {
				$less = file_get_contents( $filename );
				break;
			}
		}
		// Add in the mixins
		$less = str_replace(
			'@import "../../../less/mixins";',
			"\n\n".file_get_contents( plugin_dir_path( __FILE__ ).'less/mixins.less' ),
			$less
		);

		// Apply the preset variables to the LESS file
		$presets = $this->get_style_presets( $style );
		if ( ! empty( $presets[ $preset ] ) && is_array( $presets[ $preset ] ) ) {
			foreach ( $presets[ $preset ] as $k => $v ) {
				$less = preg_replace( '/@'.preg_quote( $k ).':( .* );/', '@'.$k.': '.$v.';', $less );
			}
		}

		// Scope the CSS with the wrapper we'll be adding
		$less = '.origin-widget.origin-widget-'.$this->origin_id.'-'.$style.'-'.$preset.' {' . $less . '}';
		$lc = new lessc();
		$lc->setPreserveComments( false );

		$lc->registerFunction( 'lumlighten', 'origin_widgets_less_lumlighten' );
		$lc->registerFunction( 'lumdarken', 'origin_widgets_less_lumdarken' );
		$lc->registerFunction( 'texture', 'origin_widgets_less_texture' );
		$lc->registerFunction( 'widgetimage', 'origin_widgets_less_widgetimage' );

		// Create the CSS
		return $lc->compile( $less );
	}

	/**
	 * Removes a CSS file
	 *
	 * @param $style
	 * @param $preset
	 */
	function clear_css_cache( $style, $preset ) {
		$filename = $this->origin_id.'-'.$style.'-'.$preset;
		delete_site_transient( 'origin_widgets_css_cache:'.$filename );
	}

	/**
	 * Get all the paths where we'll look for widgets.
	 *
	 * @return array
	 */
	function get_widget_paths() {
		static $paths = array();

		if ( empty( $paths ) ) {
			$paths = array_keys( $this->get_widget_folders() );
		}

		return $paths;
	}

	/**
	 * Get all the folders where we'll look for widgets
	 *
	 * @return mixed|void
	 */
	static function get_widget_folders() {
		static $folders = array();

		if ( empty( $folders ) ) {
			$folders = array(
				get_stylesheet_directory().'/widgets' => get_stylesheet_directory_uri().'/widgets/widgets',
				get_template_directory().'/widgets' => get_template_directory_uri().'/widgets',
				plugin_dir_path( POOTLEPAGE_BASE_FILE ).'widgets/widgets' => plugin_dir_url( POOTLEPAGE_BASE_FILE ).'widgets/widgets',
			);
			$folders = apply_filters( 'siteorigin_widget_folders', $folders );
		}

		return $folders;
	}

	/**
	 * Get all the folders where we'll look for widget images
	 *
	 * @return mixed|void
	 */
	static function get_image_folders() {
		static $folders = array();
		if ( empty( $folders ) ) {
			$folders = array(
				get_stylesheet_directory().'/widgets/img' => get_stylesheet_directory_uri().'/widgets/img',
				get_template_directory().'/widgets/img' => get_template_directory_uri().'/widgets/img',
				plugin_dir_path( POOTLEPAGE_BASE_FILE ).'widgets/img' => plugin_dir_url( POOTLEPAGE_BASE_FILE ).'widgets/img',
			);
			$folders = apply_filters( 'siteorigin_widget_image_folders', $folders );
		}

		return $folders;
	}

	/**
	 * Get all the styles for this widget.
	 *
	 * @return array
	 */
	public function get_styles() {
		if ( empty( $this->styles ) ) {
			// We can add extra paths here
			foreach ( $this->get_widget_paths() as $path ) {
				if ( ! is_dir( $path ) ) continue;

				$files = glob( $path.'/'.$this->origin_id.'/styles/*.less' );
				if ( ! empty( $files ) ) {
					foreach ( glob( $path.'/'.$this->origin_id.'/styles/*.less' ) as $file ) {
						$p = pathinfo( $file );
						$this->styles[ $p[ 'filename' ] ] = $this->get_style_data( $p[ 'filename' ] );
					}
				}
			}
		}

		return $this->styles;
	}

	/**
	 * Get the presets for a given style
	 *
	 * @param $style_id
	 * @return mixed|void
	 */
	public function get_style_presets( $style_id ) {

		$presets = array();

		foreach ( $this->get_widget_folders() as $folder => $folder_uri ) {
			$filename = rtrim( $folder, '/' ) . '/' . $this->origin_id.'/presets/'.sanitize_file_name( $style_id ).'.php';

			if ( file_exists( $filename ) ) {
				// This file should register a filter that adds the presets
				$new_presets = include( $filename );
				$presets = array_merge( $presets, $new_presets );
			}
		}


		return apply_filters( 'origin_widget_presets_'.$this->origin_id.'_'.$style_id, $presets );
	}

	/**
	 * Get data for the style.
	 *
	 * @param $name
	 * @return array
	 */
	public function get_style_data( $name ) {
		$paths = $this->get_widget_paths();

		foreach ( $paths as $path ) {
			$filename = $path.'/'.$this->origin_id.'/styles/'.sanitize_file_name( $name ).'.less';
			if ( ! file_exists( $filename ) ) continue;

			$data = get_file_data( $filename, array(
				'Name' => 'Name',
				'Template' => 'Template',
				'Author' => 'Author',
				'Author URI' => 'Author URI',
			), 'origin_widget' );
			return $data;
		}
		return false;
	}

	/**
	 * Render a demo of the widget.
	 *
	 * @param array $args
	 */
	function render_demo( $args = array() ) {
		$this->widget( $args, $this->demo );
	}

	/**
	 * Register a widget that we'll be using inside this widget.
	 *
	 * @param $id
	 * @param $name
	 * @param $class
	 */
	function add_sub_widget( $id, $name, $class ) {
		$this->sub_widgets[ $id ] = array( $name, $class );
	}

	/**
	 * Add the fields required to query the posts.
	 */
	function add_post_query_fields() {
		// Add the posts type field
		$post_types = get_post_types( array( 'public' => true ) );
		$post_types = array_values( $post_types );
		$this->form_args[ 'query_post_type' ] = array(
			'type' => 'select',
			'options' => $post_types,
			'label' => __( 'Post Type', 'siteorigin-panels' )
		);

		// Add the posts per page field
		$this->form_args[ 'query_posts_per_page' ] = array(
			'type' => 'number',
			'default' => 10,
			'label' => __( 'Posts Per Page', 'siteorigin-panels' ),
		);

		$this->form_args[ 'query_orderby' ] = array(
			'type' => 'select',
			'label' => __( 'Order By', 'siteorigin-panels' ),
			'options' => array(
				'none' => __( 'None', 'siteorigin-panels' ),
				'ID' => __( 'Post ID', 'siteorigin-panels' ),
				'author' => __( 'Author', 'siteorigin-panels' ),
				'name' => __( 'Name', 'siteorigin-panels' ),
				'name' => __( 'Name', 'siteorigin-panels' ),
				'date' => __( 'Date', 'siteorigin-panels' ),
				'modified' => __( 'Modified', 'siteorigin-panels' ),
				'parent' => __( 'Parent', 'siteorigin-panels' ),
				'rand' => __( 'Random', 'siteorigin-panels' ),
				'comment_count' => __( 'Comment Count', 'siteorigin-panels' ),
				'menu_order' => __( 'Menu Order', 'siteorigin-panels' ),
			)
		);

		$this->form_args[ 'query_order' ] = array(
			'type' => 'select',
			'label' => __( 'Order', 'siteorigin-panels' ),
			'options' => array(
				'ASC' => __( 'Ascending', 'siteorigin-panels' ),
				'DESC' => __( 'Descending', 'siteorigin-panels' ),
			)
		);

		$this->form_args[ 'query_sticky' ] = array(
			'type' => 'select',
			'label' => __( 'Sticky Posts', 'siteorigin-panels' ),
			'options' => array(
				'' => __( 'Default', 'siteorigin-panels' ),
				'ignore' => __( 'Ignore Sticky', 'siteorigin-panels' ),
				'exclude' => __( 'Exclude Sticky', 'siteorigin-panels' ),
				'only' => __( 'Only Sticky', 'siteorigin-panels' ),
			)
		);

		$this->form_args[ 'query_additional' ] = array(
			'type' => 'text',
			'label' => __( 'Additional Arguments', 'siteorigin-panels' ),
			'description' => sprintf( __( 'Additional query arguments. See <a href = "%s" target = "_blank">query_posts</a>.', 'siteorigin-panels' ), 'http://codex.wordpress.org/Function_Reference/query_posts' ),
		);
	}

	/**
	 * Get all the posts for the current query
	 *
	 * @param $instance
	 * @return WP_Query
	 */
	static function get_query_posts( $instance ) {
		$query_args = array();
		foreach ( $instance as $k => $v ) {
			if ( strpos( $k, 'query_' ) === 0 ) {
				$query_args[ preg_replace( '/query_/', '', $k, 1 ) ] = $v;
			}
		}
		$query = $query_args;
		unset( $query[ 'additional' ] );
		unset( $query[ 'sticky' ] );

		// Add the additional arguments
		$query = wp_parse_args( $query_args[ 'additional' ], $query );

		// Add the sticky posts if required
		switch( $query_args[ 'sticky' ] ) {
			case 'ignore' :
				$query[ 'ignore_sticky_posts' ] = 1;
				break;
			case 'only' :
				$query[ 'post__in' ] = get_option( 'sticky_posts' );
				break;
			case 'exclude' :
				$query[ 'post__not_in' ] = get_option( 'sticky_posts' );
				break;
		}

		// Add the current page
		global $wp_query;
		$query[ 'paged' ] = $wp_query->get( 'paged' );

		return new WP_Query( $query );
	}
}