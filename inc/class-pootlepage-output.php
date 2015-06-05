<?php
/**
 * Created by PhpStorm.
 * User: shramee
 * Date: 30/4/15
 * Time: 8:54 PM
 */

/**
 * Outputs PootlePage customizer styles
 * Class PootlePage_Output
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

		$fontOption = $this->options[ $element];

		$fontFamily = get_option( $element . '_font_id', $fontOption['defaults']['font_id'] );

		$fontSize = get_option( $element . '_font_size', $fontOption['defaults']['font_size'] );

		$fontSizeUnit = get_option( $element . '_font_size_unit', $fontOption['defaults']['font_size_unit'] );

		$fontColor = get_option( $element . '_font_color', $fontOption['defaults']['font_color'] );

		$fontWeightStyle = get_option( $element . '_font_weight_style', $fontOption['defaults']['font_weight_style'] );

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
		?>
		<style>
			/* Widget CSS */
			.panel-grid-cell .panel {
			<?php echo $this->widget_css(); ?>
			}
		</style>
		<?php
	}

	/**
	 * Returns the styles for widgets
	 *
	 * @return string $widget_title_css
	 */
	public function widget_css(){

		$widget_css = '';

		$widget_css .= 'background-color:' . get_option( 'pp_widget_bg_color', 'transparent' ) . ';';

		$widget_css .= 'border:' . get_option( 'pp_widget_border_width', 0 ) . 'px solid ' . get_option( 'pp_widget_border_color', '#dbdbdb' ) . ';';

		//CSS3 border radius property
		$widget_border_radius = get_option( 'pp_widget_border_radius', 0 );
		$widget_css .= 'border-radius:' . $widget_border_radius . 'px; ' .
		        '-moz-border-radius:' . $widget_border_radius . 'px; ' .
		        '-webkit-border-radius:' . $widget_border_radius . 'px;';

		//Widget padding styles
//		$widget_css .= $this->widget_padding_css();

		//Widget typography styles
//		$widget_css .= $this->widget_typography_css();

		return $widget_css;
	}

	/**
	 * Returns the styles for widget section
	 *
	 * @return string $widget_title_css
	 */
	public function widget_border_css(){

		$css = '';


		return $css;
	}

	/**
	 * Returns the styles for widget section
	 *
	 * @return string $widget_title_css
	 */
	public function widget_padding_css(){

		$css = '';

		//CSS padding
		$widget_padding_left_right = get_option( 'pp_widget_padding_left_right', 0 );
		$widget_padding_top_bottom = get_option( 'pp_widget_padding_top_bottom', 0 );
		$css .= "padding: {$widget_padding_top_bottom}px {$widget_padding_left_right}px;";

		return $css;
	}

	/**
	 * Returns the styles for widget section
	 *
	 * @return string $widget_title_css
	 */
	public function widget_typography_css(){

		$css = '';

		//CSS font properties
		$widget_text_font = $this->get_font_css_value( 'pp_widget_text' );
		$css .= "font-family: {$widget_text_font['font-family']} !important;";
		$css .= "font-size: {$widget_text_font['font-size']} !important;";
		$css .= "font-style: {$widget_text_font['font-style']} !important;";
		$css .= "font-weight: {$widget_text_font['font-weight']} !important;";
		$css .= "color: {$widget_text_font['color']} !important;";

		return $css;
	}

}