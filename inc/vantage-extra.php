<?php
/**
* Add row styles.
*
* @param $styles
* @return mixed
*/
function pp_vantage_panels_row_styles( $styles ) {
$styles['wide-grey'] = __( 'Wide Grey', 'vantage' );
return $styles;
}
add_filter( 'siteorigin_panels_row_styles', 'pp_vantage_panels_row_styles' );

function pp_vantage_panels_row_style_fields( $fields ) {

	$fields['full_width'] = array(
		'name' => 'Make row go full width',
		'type' => 'checkbox',
		'help-text' => ''
	);

	$fields['background'] = array(
		'name' => __( 'Background Color', 'vantage' ),
		'type' => 'color',
	);

	$fields['background_color_over_image'] = array(
		'name' => 'Put color on top of image',
		'type' => 'checkbox',
		'help-text' => 'Great for adjusting opacity with <a target="_blank" href="http://hex2rgba.devoth.com">rgba colors</a>'
	);

	$fields['background_image'] = array(
		'name' => __( 'Background Image', 'vantage' ),
		'type' => 'upload',
	);

	$fields['background_image_repeat'] = array(
		'name' => __( 'Repeat Background Image', 'vantage' ),
		'type' => 'checkbox',
	);

	$fields['background_parallax'] = array(
		'name' => __( 'Parallax Background Image', 'vantage' ),
		'type' => 'checkbox',
	);

	$fields['background_image_size'] = array(
		'name' => __( 'Background Image Size', 'vantage' ),
		'type' => 'select',
		'options' => array(
			'' => 'No setting',
			'100% auto' => '100% width',
			'cover' => 'Cover'
		),
		'default' => 'cover'
	);

	$fields['bg_video'] = array(
		'name' => __( 'Background Video', 'vantage' ),
		'type' => 'uploadVid',
	);

	$fields['no_margin'] = array(
		'name' => __( 'No Bottom Margin', 'vantage' ),
		'type' => 'checkbox',
		'default' => true
	);

	$fields['style'] = array(
		'name' => __( 'Inline Styles', 'siteorigin-panels' ),
		'type' => 'textarea',
		'default' => '',
	);

	$fields['class'] = array(
		'name' => __( 'Class', 'siteorigin-panels' ),
		'type' => 'text',
		'default' => '',
	);

	$fields['id'] = array(
		'name' => __( 'ID', 'vantage' ),
		'type' => 'text',
	);




return $fields;
}
add_filter( 'siteorigin_panels_row_style_fields', 'pp_vantage_panels_row_style_fields' );

function pp_vantage_panels_panels_row_style_attributes( $attr, $style ) {
$attr['style'] = '';

if ( ! empty( $style['top_border'] ) || ! empty( $style['top_border_height'] ) ) {
	$attr['style'] .= 'border-top: ' . $style['top_border_height'] . 'px solid '.$style['top_border'].'; ';
}
if ( ! empty( $style['bottom_border'] ) || ! empty( $style['bottom_border_height'] ) ) {
	$attr['style'] .= 'border-bottom: ' . $style['bottom_border_height'] . 'px solid '.$style['bottom_border'].'; ';
}
if ( ! empty( $style['background_image'] ) ) $attr['style'] .= 'background-image: url( '.esc_url( $style['background_image'] ).' ); ';

// background-color is set in :before element if color over image is set
if ( ! empty( $style['background'] ) && empty( $style['background_color_over_image'] ) ) {
	$attr['style'] .= 'background-color: '.$style['background'].';';
}

if ( ! empty( $style['background_image_repeat'] ) ) {
	$attr['style'] .= 'background-repeat: repeat; ';
} else {
	$attr['style'] .= 'background-repeat: no-repeat; ';
}

	if ( ! empty( $style['background_image_size'] ) ) {
		$attr['style'] .= 'background-size: ' . $style['background_image_size'] . '; ';
	}

	if ( empty( $attr['style'] ) )
		unset( $attr['style'] );

return $attr;
}

add_filter( 'siteorigin_panels_row_style_attributes', 'pp_vantage_panels_panels_row_style_attributes', 10, 2 );

function pp_vantage_panels_panels_row_attributes( $attr, $row ) {
	if ( ! empty( $row['style']['no_margin'] ) ) {
		if ( empty( $attr['style'] ) ) {
			$attr['style'] = '';
		}

		$attr['style'] .= 'margin-bottom: 0px;';

	} else {
		if ( empty( $attr['style'] ) ) {
			$attr['style'] = '';
		}

		$displayOption = get_option( 'siteorigin_panels_display', array() );
		if ( is_array( $displayOption ) && isset( $displayOption['margin-bottom'] ) ) {
			$attr['style'] .= 'margin-bottom: ' . $displayOption['margin-bottom'] . 'px;';
		} else {
			$attr['style'] .= 'margin-bottom: 30px;';
		}

	}

	if ( isset( $row['style']['id'] ) && ! empty( $row['style']['id'] ) ) {
		$attr['id'] = $row['style']['id'];
	}

	return $attr;
}
add_filter( 'siteorigin_panels_row_attributes', 'pp_vantage_panels_panels_row_attributes', 10, 2 );
