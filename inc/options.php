<?php

/**
 * Get the settings
 *
 * @param string $key Only get a specific key.
 * @return mixed
 */
function siteorigin_panels_setting( $key = '' ) {

	if ( has_action( 'after_setup_theme' ) ) {
		// Only use static settings if we've initialized the theme
		static $settings;
	}
	else {
		$settings = false;
	}

	if ( empty( $settings ) ) {
		$display_settings = get_option( 'siteorigin_panels_settings', array() ); //This option does not exist
		$display_settings = get_option( 'siteorigin_panels_display', array() );
		
		$generalSettings = get_option( 'siteorigin_panels_general', array() );

		$settings = get_theme_support( 'siteorigin-panels' );
		if ( ! empty( $settings ) ) $settings = $settings[0];
		else $settings = array();


		$settings = wp_parse_args( $settings, array(
			'home-page' => false,																								// Is the home page supported
			'home-page-default' => false,																						// What's the default layout for the home page?
			'home-template' => 'home-panels.php',																				// The file used to render a home page.
			'post-types' => get_option( 'siteorigin_panels_post_types', array( 'page' ) ),									// Post types that can be edited using panels.

			'responsive' => ! isset( $display_settings['responsive'] ) ? true : $display_settings['responsive'] == '1',					// Should we use a responsive layout
			'mobile-width' => ! isset( $display_settings['mobile-width'] ) ? 780 : $display_settings['mobile-width'],			// What is considered a mobile width?

			'margin-bottom' => ! isset( $display_settings['margin-bottom'] ) ? 30 : $display_settings['margin-bottom'],			// Bottom margin of a cell
			'margin-sides' => ! isset( $display_settings['margin-sides'] ) ? 30 : $display_settings['margin-sides'],				// Spacing between 2 cells
			'affiliate-id' => false,																							// Set your affiliate ID
			'copy-content' => ! isset( $generalSettings['copy-content'] ) ? true : $generalSettings['copy-content'] == '1',			// Should we copy across content
			'animations' => ! isset( $generalSettings['animations'] ) ? true : $generalSettings['animations'] == '1',					// Do we need animations
			'inline-css' => ! isset( $display_settings['inline-css'] ) ? true : $display_settings['inline-css'] == '1',					// How to display CSS
			'remove-list-padding' => ! isset( $display_settings['remove-list-padding'] ) ? true : $display_settings['remove-list-padding'] == '1',	// Remove left padding on list
		 ) );

		// Filter these settings
		$settings = apply_filters( 'siteorigin_panels_settings', $settings );
		if ( empty( $settings['post-types'] ) ) $settings['post-types'] = array();
	}

	if ( ! empty( $key ) ) return isset( $settings[$key] ) ? $settings[$key] : null;
	return $settings;
}

/**
 * Add the options page
 */
function siteorigin_panels_options_admin_menu() {
	$hookSuffix = add_options_page( 'Page Builder', 'Page Builder', 'manage_options', 'page_builder', 'pootle_page_options_page' );

	// to be used in PP_PB_WF_Settings, to hook save handler
	$GLOBALS['PP_PB_WF_Settings']->page_hook = $hookSuffix;
}

add_action( 'admin_menu', 'siteorigin_panels_options_admin_menu', 100 );

/**
 * Display the admin page.
 */
function pootle_page_options_page() {
	include plugin_dir_path( POOTLEPAGE_BASE_FILE ) . '/tpl/options.php';
}

/**
 * Register all the settings fields.
 */
function siteorigin_panels_options_init() {
	register_setting( 'pootlepage-general', 'siteorigin_panels_general', 'siteorigin_panels_options_sanitize_general' );
	register_setting( 'pootlepage-display', 'siteorigin_panels_display', 'siteorigin_panels_options_sanitize_display' );
	register_setting( 'pootlepage-widgets', 'pootlepage-widgets' );

	add_settings_section( 'general', __( 'General', 'siteorigin-panels' ), '__return_false', 'pootlepage-general' );
	add_settings_section( 'widgets', __( 'Widget Selection', 'siteorigin-panels' ), '__return_false', 'pootlepage-widgets' );
	add_settings_section( 'styling', __( 'Widget Styling', 'siteorigin-panels' ), 'pp_pb_options_page_styling', 'pootlepage-styling' );
	add_settings_section( 'display', __( 'Display', 'siteorigin-panels' ), '__return_false', 'pootlepage-display' );

	add_settings_field( 'copy-content', __( 'Copy Content to Post Content', 'siteorigin-panels' ), 'siteorigin_panels_options_field_general', 'pootlepage-general', 'general', array( 'type' => 'copy-content' ) );
	add_settings_field( 'animations', __( 'Animations', 'siteorigin-panels' ), 'siteorigin_panels_options_field_general', 'pootlepage-general', 'general', array(
		'type' => 'animations',
		'description' => __( 'Disable animations to improve Page Builder interface performance', 'siteorigin-panels' ),
	 ) );

	// widgets
	add_settings_field( 'reorder-widgets', __( '', 'siteorigin-panels' ), 'pootlepage_reorder_widgets', 'pootlepage-widgets', 'widgets' );
	add_settings_field( 'unused-widgets', __( '', 'siteorigin-panels' ), 'pootlepage_unused_widgets', 'pootlepage-widgets', 'widgets' );

	// The display fields
	add_settings_field( 'responsive', __( 'Responsive', 'siteorigin-panels' ), 'siteorigin_panels_options_field_display', 'pootlepage-display', 'display', array( 'type' => 'responsive' ) );
	add_settings_field( 'mobile-width', __( 'Mobile Width', 'siteorigin-panels' ), 'siteorigin_panels_options_field_display', 'pootlepage-display', 'display', array( 'type' => 'mobile-width' ) );
	add_settings_field( 'margin-sides', __( 'Margin Sides', 'siteorigin-panels' ), 'siteorigin_panels_options_field_display', 'pootlepage-display', 'display', array( 'type' => 'margin-sides' ) );
	add_settings_field( 'margin-bottom', __( 'Margin Bottom', 'siteorigin-panels' ), 'siteorigin_panels_options_field_display', 'pootlepage-display', 'display', array( 'type' => 'margin-bottom' ) );
	add_settings_field( 'inline-css', __( 'Inline CSS', 'siteorigin-panels' ), 'siteorigin_panels_options_field_display', 'pootlepage-display', 'display', array(
		'type' => 'inline-css',
		'description' => __( 'Disabling this will generate CSS using a separate query.', 'siteorigin-panels' ),
	 ) );
	add_settings_field( 'remove-list-padding', __( 'Remove list padding', 'siteorigin-panels' ), 'siteorigin_panels_options_field_display', 'pootlepage-display', 'display', array(
		'type' => 'remove-list-padding',
		'description' => __( 'Remove left padding for list widgets used in page content container.', 'siteorigin-panels' ),
	) );
}
add_action( 'admin_init', 'siteorigin_panels_options_init' );

add_action( 'init', 'pp_pb_check_for_setting_page' );

function pp_pb_check_for_setting_page() {
	if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'page_builder' ) {
		add_action( 'admin_notices', 'pp_pb_admin_notices' );
	}
}

function pp_pb_admin_notices()
{
	$notices = array();

	if ( isset( $_GET['page'] ) && 'page_builder' == $_GET['page'] &&
		( ( isset( $_GET['updated'] ) && 'true' == $_GET['updated'] ) ||
		( isset( $_GET['settings-updated'] ) && 'true' == $_GET['settings-updated'] ) )
	) {
		$notices['settings-updated'] = array( 'type' => 'updated', 'message' => __( 'Settings saved.', 'woothemes' ) );
	}

	if ( 0 < count( $notices ) ) {
		$html = '';
		foreach ( $notices as $k => $v ) {
			$html .= '<div id="' . esc_attr( $k ) . '" class="fade ' . esc_attr( $v['type'] ) . '">' . wpautop( '<strong>' . esc_html( $v['message'] ) . '</strong>' ) . '</div>' . "\n";
		}
		echo $html;
	}
}


function pp_pb_options_page_styling() {
	global $PP_PB_WF_Settings;
	$PP_PB_WF_Settings->settings_screen();
}

function pootlepage_options_page_styling() {
	$customizeUrl = admin_url( 'customize.php' );
	echo "<p>Folio uses the WordPress customizer to allow you to style your widgets and preview them easily. Click <a href='" . esc_attr( $customizeUrl ) . "'>here</a> to go to these settings.</p>";
}

function pootlepage_reorder_widgets() {
	global $wp_widget_factory;

	$widgetSettings = get_option( 'pootlepage-widgets', array() );
	if ( ! is_array( $widgetSettings ) ) {
		$widgetSettings = array();
	}
	if ( ! isset( $widgetSettings['reorder-widgets'] ) ) {
		$widgetSettings['reorder-widgets'] = '[]';
	}
	if ( ! isset( $widgetSettings['unused-widgets'] ) ) {
		$widgetSettings['unused-widgets'] = '[]';
	}

	$widgetSettings['reorder-widgets'] = json_decode( $widgetSettings['reorder-widgets'], true );
	$widgetSettings['unused-widgets'] = json_decode( $widgetSettings['unused-widgets'], true );

	if ( ! is_array( $widgetSettings['reorder-widgets'] ) ) {
		$widgetSettings['reorder-widgets'] = array();
	}
	if ( ! is_array( $widgetSettings['unused-widgets'] ) ) {
		$widgetSettings['unused-widgets'] = array();
	}

	if ( count( $widgetSettings['reorder-widgets'] ) == 0 &&
		count( $widgetSettings['unused-widgets'] ) == 0
	) {
		$widgetSettings['reorder-widgets'] = array( 'Pootle_Text_Widget',
			'SiteOrigin_Panels_Widgets_PostLoop', 'Woo_Widget_Component' );

		foreach ( $wp_widget_factory->widgets as $class => $widget_obj ) {
			if ( ! in_array( $class, $widgetSettings['reorder-widgets'] ) ) {
				$widgetSettings['unused-widgets'][] = $class;
			}
		}

		$usedSequence = $widgetSettings['reorder-widgets'];
	} else {

		$usedSequence = $widgetSettings['reorder-widgets'];

		foreach ( $wp_widget_factory->widgets as $class => $widget_obj ) {
			if ( ! in_array( $class, $widgetSettings['reorder-widgets'] ) && ! in_array( $class, $widgetSettings['unused-widgets'] ) ) {
				$usedSequence[] = $class;
			}
		}

		// make visual editor as first one
		if ( in_array( 'Pootle_Text_Widget', $usedSequence ) ) {
			$temp = array();
			$temp[] = 'Pootle_Text_Widget';
			foreach ( $usedSequence as $class ) {
				if ( $class != 'Pootle_Text_Widget' ) {
					$temp[] = $class;
				}
			}

			$usedSequence = $temp;
		}
	}

	?>
	<h3>Re-order how widgets appear in page builder by dragging them around</h3>
	<ul class="panel-type-list used-list">

		<?php for ( $i = 0; $i < count( $usedSequence ); ++$i ) :
			$class = $usedSequence[$i];
			 if ( isset( $wp_widget_factory->widgets[$class] ) ) {
				 $widget_obj = $wp_widget_factory->widgets[$class];
			?>
			<li class="panel-type"
					data-class="<?php echo esc_attr( $class ) ?>"
					data-title="<?php echo esc_attr( $widget_obj->name ) ?>"
						>
					<div class="panel-type-wrapper">
						<h3><?php echo esc_html( $widget_obj->name ) ?></h3>
			<?php if ( ! empty( $widget_obj->widget_options['description'] ) ) : ?>
				<small class="description"><?php echo esc_html( $widget_obj->widget_options['description'] ) ?></small>
			<?php endif; ?>
			</div>
			</li>
		<?php
			 }

		endfor; ?>

		<div class="clear"></div>
	</ul>

	<?php
		$json = json_encode( $usedSequence );
	?>
	<input type="hidden" id="pootlepage_widgets_used" name="pootlepage-widgets[reorder-widgets]" value="<?php esc_attr_e( $json ) ?>" />

	<?php
}

function pootlepage_unused_widgets() {

	global $wp_widget_factory;

	$widgetSettings = get_option( 'pootlepage-widgets', array() );
	if ( ! is_array( $widgetSettings ) ) {
		$widgetSettings = array();
	}
	if ( ! isset( $widgetSettings['reorder-widgets'] ) ) {
		$widgetSettings['reorder-widgets'] = '[]';
	}
	if ( ! isset( $widgetSettings['unused-widgets'] ) ) {
		$widgetSettings['unused-widgets'] = '[]';
	}

	$widgetSettings['reorder-widgets'] = json_decode( $widgetSettings['reorder-widgets'], true );
	$widgetSettings['unused-widgets'] = json_decode( $widgetSettings['unused-widgets'], true );

	if ( ! is_array( $widgetSettings['reorder-widgets'] ) ) {
		$widgetSettings['reorder-widgets'] = array();
	}
	if ( ! is_array( $widgetSettings['unused-widgets'] ) ) {
		$widgetSettings['unused-widgets'] = array();
	}

	if ( count( $widgetSettings['reorder-widgets'] ) == 0 &&
		count( $widgetSettings['unused-widgets'] ) == 0
	) {
		$widgetSettings['reorder-widgets'] = array( 'Pootle_Text_Widget',
			'SiteOrigin_Panels_Widgets_PostLoop', 'Woo_Widget_Component' );

		foreach ( $wp_widget_factory->widgets as $class => $widget_obj ) {
			if ( ! in_array( $class, $widgetSettings['reorder-widgets'] ) ) {
				$widgetSettings['unused-widgets'][] = $class;
			}
		}

		$usedSequence = $widgetSettings['reorder-widgets'];
	} else {

		$usedSequence = $widgetSettings['reorder-widgets'];

		foreach ( $wp_widget_factory->widgets as $class => $widget_obj ) {
			if ( ! in_array( $class, $widgetSettings['reorder-widgets'] ) && ! in_array( $class, $widgetSettings['unused-widgets'] ) ) {
				$usedSequence[] = $class;
			}
		}

		// make visual editor as first one
		if ( in_array( 'Pootle_Text_Widget', $usedSequence ) ) {
			$temp = array();
			$temp[] = 'Pootle_Text_Widget';
			foreach ( $usedSequence as $class ) {
				if ( $class != 'Pootle_Text_Widget' ) {
					$temp[] = $class;
				}
			}

			$usedSequence = $temp;
		}
	}

	$sequence = $widgetSettings['unused-widgets'];
?>
	<h3>Drag them here if you don't want them to be used with Canvas Page Builder</h3>

	<ul class="panel-type-list unused-list">

		<?php for ( $i = 0; $i < count( $sequence ); ++$i ) :
			$class = $sequence[$i];
			if ( isset( $wp_widget_factory->widgets[$class] ) ) {
				$widget_obj = $wp_widget_factory->widgets[$class];
				?>
				<li class="panel-type"
					data-class="<?php echo esc_attr( $class ) ?>"
					data-title="<?php echo esc_attr( $widget_obj->name ) ?>"
					>
					<div class="panel-type-wrapper">
						<h3><?php echo esc_html( $widget_obj->name ) ?></h3>
						<?php if ( ! empty( $widget_obj->widget_options['description'] ) ) : ?>
							<small class="description"><?php echo esc_html( $widget_obj->widget_options['description'] ) ?></small>
						<?php endif; ?>
					</div>
				</li>
			<?php
			}

		endfor; ?>

		<div class="clear"></div>
	</ul>
	<?php

	$json = json_encode( $sequence );
	?>
	<input type="hidden" id="pootlepage_widgets_unused" name="pootlepage-widgets[unused-widgets]" value="<?php esc_attr_e( $json ) ?>" />
	<?php
}

function siteorigin_panels_options_field_generic( $args, $groupName ) {
	$settings = siteorigin_panels_setting();
	switch( $args['type'] ) {
		case 'responsive' :
		case 'copy-content' :
		case 'animations' :
		case 'inline-css' :
		case 'bundled-widgets' :
		case 'remove-list-padding' :
			?><label><input type="checkbox" name="<?php echo $groupName ?>[<?php echo esc_attr( $args['type'] ) ?>]" <?php checked( $settings[$args['type']] ) ?> value="1" /> <?php _e( 'Enabled', 'siteorigin-panels' ) ?></label><?php
			break;
		case 'margin-bottom' :
		case 'margin-sides' :
		case 'mobile-width' :
			?><input type="text" name="<?php echo $groupName ?>[<?php echo esc_attr( $args['type'] ) ?>]" value="<?php echo esc_attr( $settings[$args['type']] ) ?>" class="small-text" /> <?php _e( 'px', 'siteorigin-panels' ) ?><?php
			break;
	}

	if ( ! empty( $args['description'] ) ) {
		?><p class="description"><?php echo esc_html( $args['description'] ) ?></p><?php
	}
}

/**
 * Display the fields for the other settings.
 *
 * @param $args
 */
function siteorigin_panels_options_field_display( $args ) {
	siteorigin_panels_options_field_generic( $args, 'siteorigin_panels_display' );
}

function siteorigin_panels_options_field_general( $args ) {
	siteorigin_panels_options_field_generic( $args, 'siteorigin_panels_general' );
}

/**
 * Check that we have valid post types
 *
 * @param $types
 * @return array
 */
function siteorigin_panels_options_sanitize_post_types( $types ) {
	if ( empty( $types ) ) return array();
	$all_post_types = get_post_types( array( '_builtin' => false ) );
	$all_post_types = array_merge( array( 'post' => 'post', 'page' => 'page' ), $all_post_types );
	foreach( $types as $type => $val ) {
		if ( ! in_array( $type, $all_post_types ) ) unset( $types[$type] );
		else $types[$type] = ! empty( $types[$type] );
	}
	
	// Only non empty items
	return array_keys( array_filter( $types ) );
}

/**
 * Sanitize the other options fields
 *
 * @param $vals
 * @return mixed
 */

function siteorigin_panels_options_sanitize_general( $vals ) {
	foreach( $vals as $f => $v ) {
		switch( $f ) {
			case 'copy-content' :
			case 'animations' :
				$vals[$f] = ! empty( $vals[$f] );
				break;
		}
	}

	$vals['copy-content'] = ! empty( $vals['copy-content'] );
	$vals['animations'] = ! empty( $vals['animations'] );

	return $vals;
}

function siteorigin_panels_options_sanitize_display( $vals ) {
	foreach( $vals as $f => $v ) {
		switch( $f ) {
			case 'inline-css' :
			case 'remove-list-padding' :
			case 'responsive' :
			case 'copy-content' :
			case 'animations' :
			case 'bundled-widgets' :
				$vals[$f] = ! empty( $vals[$f] );
				break;
			case 'margin-bottom' :
			case 'margin-sides' :
			case 'mobile-width' :
				$vals[$f] = intval( $vals[$f] );
				break;
		}
	}
	$vals['responsive'] = ! empty( $vals['responsive'] );
	$vals['copy-content'] = ! empty( $vals['copy-content'] );
	$vals['animations'] = ! empty( $vals['animations'] );
	$vals['inline-css'] = ! empty( $vals['inline-css'] );
	$vals['remove-list-padding'] = ! empty( $vals['remove-list-padding'] );
	$vals['bundled-widgets'] = ! empty( $vals['bundled-widgets'] );
	return $vals;
}