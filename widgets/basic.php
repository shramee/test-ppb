<?php

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