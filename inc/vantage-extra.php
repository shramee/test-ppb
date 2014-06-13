<?php
/**
* Add row styles.
*
* @param $styles
* @return mixed
*/
function pp_vantage_panels_row_styles($styles) {
$styles['wide-grey'] = __('Wide Grey', 'vantage');
return $styles;
}
add_filter('siteorigin_panels_row_styles', 'pp_vantage_panels_row_styles');

function pp_vantage_panels_row_style_fields($fields) {

$fields['top_border'] = array(
'name' => __('Top Border Color', 'vantage'),
'type' => 'color',
);

$fields['bottom_border'] = array(
'name' => __('Bottom Border Color', 'vantage'),
'type' => 'color',
);

$fields['background'] = array(
'name' => __('Background Color', 'vantage'),
'type' => 'color',
);

$fields['background_image'] = array(
'name' => __('Background Image', 'vantage'),
'type' => 'url',
);

$fields['background_image_repeat'] = array(
'name' => __('Repeat Background Image', 'vantage'),
'type' => 'checkbox',
);

$fields['no_margin'] = array(
'name' => __('No Bottom Margin', 'vantage'),
'type' => 'checkbox',
);

return $fields;
}
add_filter('siteorigin_panels_row_style_fields', 'pp_vantage_panels_row_style_fields');

function pp_vantage_panels_panels_row_style_attributes($attr, $style) {
$attr['style'] = '';

if(!empty($style['top_border'])) $attr['style'] .= 'border-top: 1px solid '.$style['top_border'].'; ';
if(!empty($style['bottom_border'])) $attr['style'] .= 'border-bottom: 1px solid '.$style['bottom_border'].'; ';
if(!empty($style['background'])) $attr['style'] .= 'background-color: '.$style['background'].'; ';
if(!empty($style['background_image'])) $attr['style'] .= 'background-image: url('.esc_url($style['background_image']).'); ';
if(!empty($style['background_image_repeat'])) $attr['style'] .= 'background-repeat: repeat; ';

if(empty($attr['style'])) unset($attr['style']);
return $attr;
}
add_filter('siteorigin_panels_row_style_attributes', 'pp_vantage_panels_panels_row_style_attributes', 10, 2);

function pp_vantage_panels_panels_row_attributes($attr, $row) {
if(!empty($row['style']['no_margin'])) {
if(empty($attr['style'])) $attr['style'] = '';
$attr['style'] .= 'margin-bottom: 0px;';
}

return $attr;
}
add_filter('siteorigin_panels_row_attributes', 'pp_vantage_panels_panels_row_attributes', 10, 2);