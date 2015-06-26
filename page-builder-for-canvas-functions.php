<?php

$health = 'ok';

add_action( 'admin_init', 'poo_commit_suicide' );
if ( ! function_exists( 'poo_commit_suicide' ) ) {
	function poo_commit_suicide() {
		global $health;
		$pluginFile  = str_replace( '-functions', '', __FILE__ );
		$plugin      = plugin_basename( $pluginFile );
		$plugin_data = get_plugin_data( $pluginFile, false );
		if ( $health == 'ng' && is_plugin_active( $plugin ) ) {
			deactivate_plugins( $plugin );
			wp_die( "ERROR: <strong>woo_options_add</strong> function already defined by another plugin. " .
			        $plugin_data['Name'] . " is unable to continue and has been deactivated. " .
			        "<br /><br />Please contact PootlePress at <a href=\"mailto:support@pootlepress.com?subject=Woo_Options_Add Conflict\"> support@pootlepress.com</a> for additional information / assistance." .
			        "<br /><br />Back to the WordPress <a href='" . get_admin_url( null, 'plugins.php' ) . "'>Plugins page</a>." );
		}
	}
}

/**
 * Adds notice to output in next admin_notices actions call
 *
 * @param string $id Unique id for pootle page builder message
 * @param string $message
 * @param string $type Standard WP admin notice types supported defaults 'updated'
 *
 * @since 3.0.0
 */
function ppb_add_admin_notice( $id, $message, $type = 'updated' ) {

	$notices = get_option( 'pootle_page_admin_notices', array() );

	$notices[$id] = array(
		'type'    => $type,
		'message' => $message,
	);

	update_option( 'pootle_page_admin_notices', $notices );
}

/**
 * Renders color picker control
 *
 * @param string $label
 * @param string $value
 * @param string $default_color
 * @param string $link
 */
function pootlepage_color_control( $label, $value, $default_color, $link ) {

	$current_color = isset( $value ) ? $value : $default_color;

	?>
	<label><span><?php _e( $label, 'scratch' ); ?></span>
		<input class="color-picker-hex sc-font-color-text-box" type="text" maxlength="7"
		       placeholder="<?php esc_attr_e( 'Hex Value' ); ?>"
		       value="<?php echo $current_color; ?>" data-default-color="<?php echo $default_color ?>"
			<?php echo $link ?>
			/>
	</label>
<?php
}

/**
 * Test whether or not a typeface has been selected for a "typography" field.
 *
 * @param   string $face The noble warrior ( typeface ) to be tested.
 * @param   string $test_case The test case. Does the warrior pass the ultimate test and reep eternal glory?
 *
 * @return  bool              Whether or not eternal glory shall be achieved by the warrior.
 */
function pootlepage_test_typeface_against_test_case( $face, $test_case ) {
	$response = false;

	$face = stripslashes( str_replace( '"', '', str_replace( '&quot;', '', $face ) ) );

	$parts = explode( ',', $face );

	if ( $test_case == $parts[0] ) {
		$response = true;
	}

	return $response;
}

/**
 * Outputs html for options in font face select field
 *
 * @param $font_faces
 * @param $test_cases
 * @param $value
 *
 * @return string
 */
function pootle_page_output_font_select_options( $value ) {
	global $pootle_page_font;

	$font_faces = $pootle_page_font;
	$test_cases = array();

	if ( function_exists( 'wf_get_system_fonts_test_cases' ) ) {
		$test_cases = wf_get_system_fonts_test_cases();
	}

	$html = '';
	foreach ( $font_faces as $k => $v ) {

		$selected = '';

		// If one of the fonts requires a test case, use that value. Otherwise, use the key as the test case.
		if ( in_array( $k, array_keys( $test_cases ) ) ) {
			$value_to_test = $test_cases[ $k ];
		} else {
			$value_to_test = $k;
		}
		if ( pootlepage_test_typeface_against_test_case( $value, $value_to_test ) ) {
			$selected = ' selected="selected"';
		}
		$html .= '<option value="' . esc_attr( $k ) . '" ' . $selected . '>' . esc_html( $v ) . '</option>' . "\n";
	}

	return $html;

}

/**
 * Converts hex color string to rgb
 *
 * @param $hex
 *
 * @return string red, green, blue
 */
function ppb_hex2rgb( $hex ) {
	$hex = str_replace( "#", "", $hex );

	if ( strlen( $hex ) == 3 ) {
		$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
		$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
		$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
	} else {
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
	}

	return " $r, $g, $b"; // returns an array with the rgb values
}

/**
 * Check if we're currently viewing a panel.
 * @param bool $can_edit Also check if the user can edit this page
 * @return bool
 */
function ppb_is_panel( $can_edit = false ) {
	// Check if this is a panel
	$is_panel = ( is_singular() && get_post_meta( get_the_ID(), 'panels_data', false ) != '' );

	return $is_panel && ( ! $can_edit || ( is_singular() && current_user_can( 'edit_post', get_the_ID() ) ) );
}

function pp_pb_widget_styling_fields() {
	global $content_block_styling_fields;
	return $content_block_styling_fields;
}

/**
 * A callback that replaces temporary break tag with actual line breaks.
 *
 * @param $val
 *
 * @return array|mixed
 */
function siteorigin_panels_wp_import_post_meta_map( $val ) {
	if ( is_string( $val ) ) {
		return str_replace( '<<<br>>>', "\n", $val );
	} else {
		return array_map( 'siteorigin_panels_wp_import_post_meta_map', $val );
	}
}
