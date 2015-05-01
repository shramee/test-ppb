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

		/* Size fields */
		$html .= $this->typography_size( $key, $value );

		/* Font Unit */
		$html .= '<select class="woo-typography woo-typography-unit" name="'. esc_attr( $key .'[unit]' ) . '" id="'. esc_attr( $key . '_unit' ) . '">' . "\n";
		$html .= '<option value="px" ' . selected( $unit, 'px', false ) . '">px</option>' . "\n";
		$html .= '<option value="em" ' . selected( $unit, 'em', false ) . '>em</option>' . "\n";
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
		$html .= $this->typography_font_face( $key, $value );

		/* Border Color */
		$html .= '<input id="' . esc_attr( $key . '_color' ) . '" name="' . esc_attr( $key . '[color]' ) . '" size="40" type="text" class="woo-typography-color colour" value="' . esc_attr( $value['color'] ) . '" />' . "\n";

		$html .= '</span>' . "\n";

		return $html;

	}

	/**
	 * Outputs typography size fields
	 *
	 * @param $key
	 * @param $value
	 * @return string
	 */
	public function typography_size( $key, $value ){

		/* Size in Pixels */
		$html = '<select class="woo-typography woo-typography-size woo-typography-size-px hide-if-em" name="'. esc_attr( $key . '[size_px]' ) . '" id="'. esc_attr( $key . '_size' ) . '">' . "\n";
		for ( $i = 9; $i < floatval( apply_filters( 'wf_fields_typography_font_size_px_upper_limit', 71 ) ); $i++ ) {
			$html .= '<option value="'. esc_attr( $i ) .'" ' . selected( floatval( $value['size'] ), $i, false ) . '>'. esc_html( $i ) . '</option>' . "\n";
		}
		$html .= '</select>' . "\n";

		/* Size in EMs */
		$html .= '<select class="woo-typography woo-typography-size woo-typography-size-em hide-if-px" name="'. esc_attr( $key . '[size_em]' ) . '" id="'. esc_attr( $key . '_size' ) . '">' . "\n";
		$em = 0;
		for ( $i = 1; $i < 40; $i++ ) {

			if ( $i <= 25 ){
				$em += 0.1;// up to 2.5em in 0.1 increments
			} else {
				$em += 0.5;// Above 2.5em in 0.5 increments
			}

			$html .= '<option value="' . esc_attr( floatval( $em ) ) . '" ' . selected( strval( $em ), $value['size'], false ) . '>' . esc_html( $em ) . '</option>';
		}
		$html .= '</select>' . "\n";

		return $html;

	}

	/**
	 * Outputs typography font face fields
	 *
	 * @param $key
	 * @param $value
	 * @return string
	 */
	public function typography_font_face( $key, $value ){

		$html = '';

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

			//Font Options for select
			$html .= output_font_select_options( $font_faces, $test_cases, $value['face'] );

			$html .= '</select>' . "\n";
		}

		return $html;

	}

	/**
	 * Filters $data for PP_PB_WF_Fields::validate_fields()
	 *
	 * @param $data
	 * @param $sections_to_scan
	 */
	public function validate_fields_filter_data( &$data, $sections_to_scan ) {

		// Retrieve all fields in this current screen ( main and sub-sections ).
		$fields_by_section = array();

		foreach ( $sections_to_scan as $k => $v ) {
			$field_data = $this->_get_fields_by_section( $v );
			$fields_by_section = array_merge( $fields_by_section, $field_data );
		}

		// Make sure checkboxes and multicheck fields are taken care of.
		if ( 0 < count( $fields_by_section ) ) {
			foreach ( $fields_by_section as $k => $v ) {
				if ( ! in_array( $v['type'], array( 'checkbox', 'multicheck', 'multicheck2' ) ) ) {
					unset( $fields_by_section[$k] );
				}
				if ( ! isset( $data[$k] ) ) {
					$data[$k] = '';
				}
			}
		}
	}

	/**
	 * Prepares sections to scan for PP_PB_WF_Fields::validate_fields()
	 *
	 * @param $section
	 * @param $sections
	 * @return array Sections to scan
	 */
	public function prepare_sections( $section, $sections ){

		$sections_to_scan = array();

		// No section has been applied. Assume it's the first.
		if ( is_array( $sections ) && '' == $section && 0 < count( $sections ) ) {
			foreach ( $sections as $k => $v ) {
				$section = $k;
				break;
			}
		}

		// Store the current top section.
		$sections_to_scan[] = $section;

		// Check if we have sub-sections.
		if ( ! empty( $sections[ $section ]['children'] ) ) {
			foreach ( $sections[ $section ]['children'] as $k => $v ) {
				$sections_to_scan[] = $v['token'];
			}
		}

		return $sections_to_scan;
	}

	/**
	 * Filters $data for PP_PB_WF_Fields::validate_fields()
	 *
	 * @param $data
	 * @param $fields
	 * @param $data_key
	 * @param $data_val
	 */
	public function validate_field( &$data, $fields, $data_key, $data_val ) {

		// Determine if a method is available for validating this field.
		$method = 'validate_field_' . $fields[$data_key]['type'];
		if ( ! method_exists( $this, $method ) ) {
			if ( true == ( bool )apply_filters( 'wf_validate_field_' . $fields[$data_key]['type'] . '_use_default', true ) ) {
				$method = 'validate_field_text';
			} else {
				$method = '';
			}
		}

		// If we have an internal method for validation, filter and apply it.
		if ( '' != $method ) {
			add_filter( 'wf_validate_field_' . $fields[$data_key]['type'], array( $this, $method ), 10, 2 );
		}

		$method_output = apply_filters( 'wf_validate_field_' . $fields[$data_key]['type'], $data_val, $fields[$data_key] );

		if ( ! is_wp_error( $method_output ) ) {
			$data[$data_key] = $method_output;
		}

	}




	/**
	 * Validate the given data, assuming it is from a text input field.
	 * @access  public
	 * @since   6.0.0
	 * @return  void
	 */
	public function validate_field_text ( $v ) {
		return ( string )wp_kses_post( $v );
	} // End validate_field_text()

	/**
	 * Validate the given data, assuming it is from a textarea field.
	 * @access  public
	 * @since   6.0.0
	 * @return  void
	 */
	public function validate_field_textarea ( $v, $k ) {
		// Allow iframe, object and embed tags in textarea fields.
		$allowed = wp_kses_allowed_html( 'post' );
		$allowed['iframe'] = array( 'src' => true, 'width' => true, 'height' => true, 'id' => true, 'class' => true, 'name' => true );
		$allowed['object'] = array( 'src' => true, 'width' => true, 'height' => true, 'id' => true, 'class' => true, 'name' => true );
		$allowed['embed'] = array( 'src' => true, 'width' => true, 'height' => true, 'id' => true, 'class' => true, 'name' => true );

		return wp_kses( $v, $allowed );
	} // End validate_field_textarea()

	/**
	 * Validate the given data, assuming it is from a checkbox input field.
	 * @access public
	 * @since  6.0.0
	 * @param  string $v
	 * @return string
	 */
	public function validate_field_checkbox ( $v ) {
		if ( 'true' != $v ) {
			return 'false';
		} else {
			return 'true';
		}
	} // End validate_field_checkbox()

	/**
	 * Validate the given data, assuming it is from a multicheck field.
	 * @access public
	 * @since  6.0.0
	 * @param  string $v
	 * @return string
	 */
	public function validate_field_multicheck ( $v ) {
		$v = ( array ) $v;

		$v = array_map( 'esc_attr', $v );

		return $v;
	} // End validate_field_multicheck()

	/**
	 * Validate the given data, assuming it is from a multicheck2 field.
	 * @access public
	 * @since  6.0.0
	 * @param  string $v
	 * @return string
	 */
	public function validate_field_multicheck2 ( $v ) {
		$v = ( array ) $v;

		$v = array_map( 'esc_attr', $v );

		return $v;
	} // End validate_field_multicheck2()

	/**
	 * Validate the given data, assuming it is from a slider field.
	 * @access public
	 * @since  6.0.0
	 * @param  string $v
	 * @return string
	 */
	public function validate_field_slider ( $v ) {
		$v = floatval( $v );

		return $v;
	} // End validate_field_slider()

	/**
	 * Validate the given data, assuming it is from a URL field.
	 * @access public
	 * @since  6.0.0
	 * @param  string $v
	 * @return string
	 */
	public function validate_field_url ( $v ) {
		return trim( esc_url( $v ) );
	} // End validate_field_url()

	/**
	 * Validate the given data, assuming it is from a upload field.
	 * @access public
	 * @since  6.0.0
	 * @param  string $v
	 * @return string
	 */
	public function validate_field_upload ( $v ) {
		return trim( esc_url( $v ) );
	} // End validate_field_upload()

	/**
	 * Validate the given data, assuming it is from a upload_min field.
	 * @access public
	 * @since  6.0.0
	 * @param  string $v
	 * @return string
	 */
	public function validate_field_upload_min ( $v ) {
		return trim( esc_url( $v ) );
	} // End validate_field_upload()

	/**
	 * Validate the given data, assuming it is from a upload_field_id field.
	 * @access public
	 * @since  6.0.0
	 * @param  string $v
	 * @return string
	 */
	public function validate_field_upload_field_id ( $v ) {
		return intval( $v );
	} // End validate_field_upload_field_id()

	/**
	 * Validate the given data, assuming it is from a typography field.
	 * @access public
	 * @since  6.0.0
	 * @param  string $v
	 * @return string
	 */
	public function validate_field_typography ( $v ) {
		$defaults = array( 'size' => '', 'unit' => '', 'face' => '', 'style' => '', 'color' => '' );
		$v = wp_parse_args( $v, $defaults );

		if ( isset( $v['size_' . $v['unit']] ) ) {
			$v['size'] = $v['size_' . $v['unit']];
		}

		foreach ( $v as $i => $j ) {
			if ( ! in_array( $i, array_keys( $defaults ) ) ) {
				unset( $v[$i] );
			}
		}

		$v = array_map( 'strip_tags', $v );
		$v = array_map( 'stripslashes', $v );

		return $v;
	} // End validate_field_typography()

	/**
	 * Validate the given data, assuming it is from a border field.
	 * @access public
	 * @since  6.0.0
	 * @param  string $v
	 * @return string
	 */
	public function validate_field_border ( $v ) {
		$defaults = array( 'width' => '', 'style' => '', 'color' => '' );
		$v = wp_parse_args( $v, $defaults );

		foreach ( $v as $i => $j ) {
			if ( ! in_array( $i, array_keys( $defaults ) ) ) {
				unset( $v[$i] );
			}
		}

		$v = array_map( 'esc_html', $v );

		return $v;
	} // End validate_field_border()

	/**
	 * Validate the given data, assuming it is from a timestamp field.
	 * @access public
	 * @since  6.0.0
	 * @param  string $v
	 * @return string
	 */
	public function validate_field_timestamp ( $v ) {
		$defaults = array( 'date' => '', 'hour' => '', 'minute' => '' );
		$v = wp_parse_args( $v, $defaults );

		foreach ( $v as $i => $j ) {
			if ( ! in_array( $i, array_keys( $defaults ) ) ) {
				unset( $v[$i] );
			}
		}

		$date = $v['date'];

		$hour = $v['hour'];
		$minute = $v['minute'];
		// $second = $output[$option_array['id']]['second'];
		$second = '00';

		$day = substr( $date, 3, 2 );
		$month = substr( $date, 0, 2 );
		$year = substr( $date, 6, 4 );

		$timestamp = mktime( $hour, $minute, $second, $month, $day, $year );

		return esc_attr( $timestamp );
	} // End validate_field_timestamp()

}