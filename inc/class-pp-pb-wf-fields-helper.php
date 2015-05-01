<?php
/**
 * Created by PhpStorm.
 * User: shramee
 * Date: 1/5/15
 * Time: 12:48 PM
 */

/**
 * Extracting PP_PB_WF_Fields
 *
 * @Class PP_PB_WF_Fields_Helper
 */
class PP_PB_WF_Fields_Helper {
	public function __construct(){

	}

	/**
	 * Renders the typography field for PP_PB_WF_Fields::render_field_typography()
	 *
	 * @param $value
	 * @param $key
	 *
	 * @return string
	 */
	public function render_field_typography( $key, $value ){

		$html = '';

		// Make sure the size fields are set correctly.
		if ( ! isset( $value['size'] ) ) {
			$value['size'] = $value['size_' . $value['unit']];
		}

		$unit = $value['unit'];

		$html .= '<span class="unit-container ' . esc_attr( 'unit-' . sanitize_title_with_dashes( $unit ) ) . '">' . "\n";

		/* Size in Pixels */
		$html .= '<select class="woo-typography woo-typography-size woo-typography-size-px hide-if-em" name="'. esc_attr( $key . '[size_px]' ) . '" id="'. esc_attr( $key . '_size' ) . '">' . "\n";
		for ( $i = 9; $i < floatval( apply_filters( 'wf_fields_typography_font_size_px_upper_limit', 71 ) ); $i++ ) {
			$html .= '<option value="'. esc_attr( $i ) .'" ' . selected( floatval( $value['size'] ), $i, false ) . '>'. esc_html( $i ) . '</option>' . "\n";
		}
		$html .= '</select>' . "\n";

		/* Size in EMs */
		$html .= '<select class="woo-typography woo-typography-size woo-typography-size-em hide-if-px" name="'. esc_attr( $key . '[size_em]' ) . '" id="'. esc_attr( $key . '_size' ) . '">' . "\n";
		$em = 0;
		for ( $i = 0; $i < 39; $i++ ) {
			if ( $i <= 24 )   // up to 2.0em in 0.1 increments
				$em = $em + 0.1;
			elseif ( $i >= 14 && $i <= 24 )  // Above 2.0em to 3.0em in 0.2 increments
				$em = $em + 0.2;
			elseif ( $i >= 24 )  // Above 3.0em in 0.5 increments
				$em = $em + 0.5;

			$active = '';
			if ( strval( $em ) == $value['size'] ) {
				$active = 'selected="selected"';
			}
			$html .= '<option value="' . esc_attr( floatval( $em ) ) . '" ' . $active . '>' . esc_html( $em ) . '</option>';
		}
		$html .= '</select>' . "\n";

		/* Font Unit */
		$unit = $value['unit'];
		$em = ''; $px = '';
		if ( 'em' == $unit ) { $em = 'selected="selected"'; }
		if ( 'px' == $unit ) { $px = 'selected="selected"'; }
		$html .= '<select class="woo-typography woo-typography-unit" name="'. esc_attr( $key .'[unit]' ) . '" id="'. esc_attr( $key . '_unit' ) . '">' . "\n";
		$html .= '<option value="px" ' . $px . '">px</option>' . "\n";
		$html .= '<option value="em" ' . $em . '>em</option>' . "\n";
		$html .= '</select>' . "\n";

		/* Weights */
		$font_weights = ( array ) apply_filters( 'wf_fields_typography_font_weights', array( '300' => __( 'Thin', 'woothemes' ), '300 italic' => __( 'Thin Italic', 'woothemes' ), 'normal' => __( 'Normal', 'woothemes' ), 'italic' => __( 'Italic', 'woothemes' ), 'bold' => __( 'Bold', 'woothemes' ), 'bold italic' => __( 'Bold/Italic', 'woothemes' ) ) );

		if ( 0 < count( $font_weights ) ) {
			$html .= '<select class="woo-typography woo-typography-font-weight woo-typography-style" name="'. esc_attr( $key . '[style]' ) . '" id="'. esc_attr( $key . '_style' ) . '">' . "\n";
			foreach ( $font_weights as $k => $v ) {
				$html .= '<option value="' . esc_attr( $k ) . '" ' . selected( $value['style'], $k, false ) . '>' . esc_html( $v ) . '</option>' . "\n";
			}
			$html .= '</select>' . "\n";
		}

		/* Font Face */
		$font_faces = wf_get_system_fonts();
		$google_fonts = wf_get_google_fonts();
		if ( 0 < count( $google_fonts ) ) {
			$font_faces[''] = __( '-- Google WebFonts --', 'woothemes' );
			$google_fonts_array = array();
			foreach ( $google_fonts as $k => $v ) {
				$google_fonts_array[$v['name']] = $v['name'];
			}
			asort( $google_fonts_array );
			$font_faces = array_merge( $font_faces, $google_fonts_array );
		}

		if ( 0 < count( $font_faces ) ) {
			$test_cases = wf_get_system_fonts_test_cases();

			$html .= '<select class="woo-typography woo-typography-font-face woo-typography-face" name="'. esc_attr( $key . '[face]' ) . '" id="'. esc_attr( $key . '_face' ) . '">' . "\n";
			foreach ( $font_faces as $k => $v ) {
				$selected = '';
				// If one of the fonts requires a test case, use that value. Otherwise, use the key as the test case.
				if ( in_array( $k, array_keys( $test_cases ) ) ) {
					$value_to_test = $test_cases[$k];
				} else {
					$value_to_test = $k;
				}
				if ( $this->_test_typeface_against_test_case( $value['face'], $value_to_test ) ) $selected = ' selected="selected"';
				$html .= '<option value="' . esc_attr( $k ) . '" ' . $selected . '>' . esc_html( $v ) . '</option>' . "\n";
			}
			$html .= '</select>' . "\n";
		}

		/* Border Color */
		$html .= '<input id="' . esc_attr( $key . '_color' ) . '" name="' . esc_attr( $key . '[color]' ) . '" size="40" type="text" class="woo-typography-color colour" value="' . esc_attr( $value['color'] ) . '" />' . "\n";

		$html .= '</span>' . "\n";

		return $html;

	}

	/**
	 * Test whether or not a typeface has been selected for a "typography" field.
	 * @access  protected
	 * @since   6.0.2
	 * @param   string $face	  The noble warrior ( typeface ) to be tested.
	 * @param   string $test_case The test case. Does the warrior pass the ultimate test and reep eternal glory?
	 * @return  bool	   		  Whether or not eternal glory shall be achieved by the warrior.
	 */
	protected function _test_typeface_against_test_case ( $face, $test_case ) {
		$response = false;

		$face = stripslashes( str_replace( '"', '', str_replace( '&quot;', '', $face ) ) );

		$parts = explode( ',', $face );

		if ( $test_case == $parts[0] ) {
			$response = true;
		}

		return $response;
	}

}