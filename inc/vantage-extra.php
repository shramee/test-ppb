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

    $fields['id'] = array(
        'name' => __('ID', 'vantage'),
        'type' => 'text',
    );

$fields['top_border'] = array(
'name' => __('Top Border Color', 'vantage'),
'type' => 'color',
);

$fields['top_border_height'] = array(
    'name' => __('Top Border Height', 'pp-pb'),
    'type' => 'number',
    'min' => '0',
    'default' => '0'
);

$fields['bottom_border'] = array(
'name' => __('Bottom Border Color', 'vantage'),
'type' => 'color',
);

$fields['bottom_border_height'] = array(
    'name' => __('Bottom Border Height', 'pp-pb'),
    'type' => 'number',
    'min' => '0',
    'default' => '0'
);

$fields['background'] = array(
'name' => __('Background Color', 'vantage'),
'type' => 'color',
);

$fields['background_image'] = array(
'name' => __('Background Image', 'vantage'),
'type' => 'upload',
);


$fields['height'] = array(
    'name' => __('Height', 'pp-pb'),
    'type' => 'number',
    'min' => '0',
    'default' => ''
);

$fields['background_image_repeat'] = array(
'name' => __('Repeat Background Image', 'vantage'),
'type' => 'checkbox',
);

$fields['background_image_size'] = array(
    'name' => __('Background Image Size', 'vantage'),
    'type' => 'select',
    'options' => array(
        '' => 'No setting',
        '100% auto' => '100% width',
        'cover' => 'Cover'
    )
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

    if(!empty($style['top_border']) || !empty($style['top_border_height'])) {
        $attr['style'] .= 'border-top: ' . $style['top_border_height'] . 'px solid '.$style['top_border'].'; ';
    }
    if(!empty($style['bottom_border']) || !empty($style['bottom_border_height'])) {
        $attr['style'] .= 'border-bottom: ' . $style['bottom_border_height'] . 'px solid '.$style['bottom_border'].'; ';
    }
    if(!empty($style['background'])) $attr['style'] .= 'background-color: '.$style['background'].'; ';
    if(!empty($style['background_image'])) $attr['style'] .= 'background-image: url('.esc_url($style['background_image']).'); ';
    if(!empty($style['background_image_repeat'])) {
        $attr['style'] .= 'background-repeat: repeat; ';
    } else {
        $attr['style'] .= 'background-repeat: no-repeat; ';
    }

    if (!empty($style['background_image_size'])) {
        $attr['style'] .= 'background-size: ' . $style['background_image_size'] . '; ';
    }

    if (!empty($style['height'])) {
        $attr['style'] .= 'height: ' . $style['height'] . 'px;';
    } else {
        $attr['style'] .= 'height: auto;';
    }

    if(empty($attr['style']))
        unset($attr['style']);

    return $attr;
}

add_filter('siteorigin_panels_row_style_attributes', 'pp_vantage_panels_panels_row_style_attributes', 10, 2);

function pp_vantage_panels_panels_row_attributes($attr, $row) {
    if(!empty($row['style']['no_margin'])) {
        if(empty($attr['style']))
            $attr['style'] = '';

        $attr['style'] .= 'margin-bottom: 0px;';
    } else {
        if(empty($attr['style']))
            $attr['style'] = '';

        $attr['style'] .= 'margin-bottom: 30px;';
    }

    $attr['id'] = $row['style']['id'];

return $attr;
}
add_filter('siteorigin_panels_row_attributes', 'pp_vantage_panels_panels_row_attributes', 10, 2);