<?php
/**
 * Created by PhpStorm.
 * User: shramee
 * Date: 30/4/15
 * Time: 8:54 PM
 */

class PootlePage_Output {

	public $options;

	public function __construct( $options ){

		$this->options = $options;

	}

	public function google_webfonts() {

		if ( ! function_exists( 'wf_get_google_fonts' ) ) {
			return;
		}

		$google_fonts = wf_get_google_fonts();

		$fonts_to_load = array();
		$output = '';

		// Go through the options
		if ( ! empty( $this->options ) && ! empty( $google_fonts ) ) {
			foreach ( $this->options as $key => $option ) {

				if ( is_array( $option ) && $option['type'] == 'font' ) {

					$fontFamilySettingId = $option['settings']['font_id'];
					$fontFamilyDefault = $option['defaults']['font_id'];
					$fontFamily = get_option( $fontFamilySettingId, $fontFamilyDefault );

					// Go through the google font array
					foreach ( $google_fonts as $font ) {
						// Check if the google font name exists in the current "face" option
						if ( $fontFamily == $font['name'] && ! in_array( $font['name'], array_keys( $fonts_to_load ) ) ) {
							// Add google font to output
							$variant = '';
							if ( isset( $font['variant'] ) ) $variant = $font['variant'];
							$fonts_to_load[$font['name']] = $variant;
						}
					}
				}
			}

			// Output google font css in header
			if ( 0 < count( $fonts_to_load ) ) {
				$fonts_and_variants = array();
				foreach ( $fonts_to_load as $k => $v ) {
					$fonts_and_variants[] = $k . $v;
				}
				$fonts_and_variants = array_map( 'urlencode', $fonts_and_variants );
				$fonts = join( '|', $fonts_and_variants );

				$output .= "\n<!-- Google Webfonts -->\n";
				$output .= '<link href="http'. ( is_ssl() ? 's' : '' ) .'://fonts.googleapis.com/css?family=' . $fonts .'" rel="stylesheet" type="text/css" />'."\n";

				echo $output;
			}
		}
	}

	private function get_font_css_value( $element ) {
		$fontOption = $this->options[$element];

		$fontFamily = get_option( $element . '_font_id' );
		if ( empty( $fontFamily ) ) {
			$fontFamily = $fontOption['defaults']['font_id'];
		}

		$fontSize = get_option( $element . '_font_size' );
		if ( $fontSize === false ) {
			$fontSize = $fontOption['defaults']['font_size'];
		}

		$fontSizeUnit = get_option( $element . '_font_size_unit' );
		if ( $fontSizeUnit === false ) {
			$fontSizeUnit = $fontOption['defaults']['font_size_unit'];
		}

		$fontColor = get_option( $element . '_font_color' );
		if ( $fontColor === false ) {
			$fontColor = $fontOption['defaults']['font_color'];
		}

		$fontWeightStyle = get_option( $element . '_font_weight_style' );
		if ( empty( $fontWeightStyle ) ) {
			$fontWeightStyle = $fontOption['defaults']['font_weight_style'];
		}

		$fontStyle = ( strpos( $fontWeightStyle, 'italic' ) === false ? 'normal' : 'italic' );
		$fontWeight = str_replace( 'italic', '', $fontWeightStyle );

		if ( empty( $fontWeight ) ) {
			$fontWeight = '400';
		}

		$result = array(
			'font-family' => '"' . $fontFamily . '"',
			'font-size' => $fontSize . $fontSizeUnit,
			'color' => $fontColor,
			'font-style' => $fontStyle,
			'font-weight' => $fontWeight
		);

		return $result;
	}

	public function output_css() {

		$output = '';

		$widget_css = '';

		$widget_bg = get_option( 'pp_widget_bg_color' );

		if ( $widget_bg ) {
			$widget_css .= 'background-color:'.$widget_bg.';';
		} else {
			$widget_css .= 'background-color: transparent;';
		}

		$widget_border_width = get_option( 'pp_widget_border_width', 0 );
		$widget_border_style = get_option( 'pp_widget_border_style', 'solid' );
		$widget_border_color = get_option( 'pp_widget_border_color', '#dbdbdb' );

		if ( $widget_border_width > 0 ) {
			$widget_css .= 'border:'.$widget_border_width.'px '.$widget_border_style . ' ' . $widget_border_color . ';';
		}

		$widget_padding_left_right = get_option( 'pp_widget_padding_left_right', 0 );
		$widget_padding_top_bottom = get_option( 'pp_widget_padding_top_bottom', 0 );

		if ( ! $widget_padding_left_right ) {
			$widget_css .= 'padding-left: 0; padding-right: 0;';
		} else {
			$widget_css .= 'padding-left: ' . $widget_padding_left_right . 'px ; padding-right: ' . $widget_padding_left_right . 'px;';
		}

		if ( ! $widget_padding_top_bottom ) {
			$widget_css .= 'padding-top: 0; padding-bottom: 0;';
		} else {
			$widget_css .= 'padding-top: ' . $widget_padding_top_bottom . 'px ; padding-bottom: ' . $widget_padding_top_bottom . 'px;';
		}

		$widget_text_font = $this->get_font_css_value( 'pp_widget_text' );

		$widget_css .= 'font-family: ' . $widget_text_font['font-family'] .
			' ! important; font-size: ' . $widget_text_font['font-size'] .
			' ! important; font-style: ' . $widget_text_font['font-style'] .
			' ! important; font-weight: ' . $widget_text_font['font-weight'] .
			' ! important; color: ' . $widget_text_font['color'] . ' ! important; ';


		$widget_title_font = $this->get_font_css_value( 'pp_widget_title' );

		$widget_title_css = '';
		$widget_title_css .= 'font-family: ' . $widget_title_font['font-family'] .
			'; font-size: ' . $widget_title_font['font-size'] .
			'; font-style: ' . $widget_title_font['font-style'] .
			'; font-weight: ' . $widget_title_font['font-weight'] .
			'; color: ' . $widget_title_font['color'] . '; ';


		$widget_title_border_width = get_option( 'pp_widget_title_bottom_border_width', 1 );
		$widget_title_border_style = get_option( 'pp_widget_title_bottom_border_style', 'solid' );
		$widget_title_border_color = get_option( 'pp_widget_title_bottom_border_color', '#e6e6e6' );

		if ( $widget_title_border_width > 0 ) {
			$widget_title_css .= 'border-bottom:' . $widget_title_border_width . 'px ' . $widget_title_border_style . ' ' . $widget_title_border_color . ';';
		}
		if ( isset( $widget_title_border_width ) AND $widget_title_border_width == 0 ) {
			$widget_title_css .= 'margin-bottom:0;';
		}

		$widget_border_radius = get_option( 'pp_widget_border_radius', 0 );
		if ( $widget_border_radius > 0 ) {
			$widget_css .= 'border-radius:' . $widget_border_radius . 'px; -moz-border-radius:' . $widget_border_radius . 'px; -webkit-border-radius:' . $widget_border_radius . 'px;';
		}

		if ( $widget_css != '' ) {
			$output .= '.panel-grid-cell .widget {' . $widget_css . '}' . "\n";
		}

		if ( $widget_title_css != '' ) {
			$output .= '.panel-grid-cell .widget > .widget-title {' . $widget_title_css . '}' . "\n";
		}

		?>
		<style>
			<?php echo $output ?>
		</style>
	<?php
	}

}