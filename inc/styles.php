<?php
/**
 * Code to handle the row styling
 */

/**
 * Get all the row styles.
 *
 * @return array An array defining the row fields.
 */
function siteorigin_panels_style_get_fields() {
	static $fields = false;

	if ( $fields === false ) {
		$fields = array();

		$fields = apply_filters( 'siteorigin_panels_row_style_fields', $fields );
	}

	return $fields;
}

function pootlepage_page_settings_fields() {

	$fields = array();

	$fields['background'] = array(
		'name' => __( 'Background Color', 'pootlepage' ),
		'type' => 'color',
	);

	$fields['background_image'] = array(
		'name' => __( 'Background Image', 'pootlepage' ),
		'type' => 'upload',
	);

	$fields['background_image_repeat'] = array(
		'name' => __( 'Background Image Repeat', 'pootlepage' ),
		'type' => 'checkbox',
	);

	$fields['background_image_position'] = array(
		'name'    => __( 'Background Image Position', 'pootlepage' ),
		'type'    => 'select',
		'options' => array(
			''              => 'default',
			'top left'      => 'top left',
			'top center'    => 'top center',
			'top right'     => 'top right',
			'center left'   => 'center left',
			'center center' => 'center center',
			'center right'  => 'center right',
			'bottom left'   => 'bottom left',
			'bottom center' => 'bottom center',
			'bottom right'  => 'bottom right'
		)
	);

	$fields['background_image_attachment'] = array(
		'name'    => __( 'Background Attachment', 'pootlepage' ),
		'type'    => 'select',
		'options' => array(
			''       => 'default',
			'scroll' => 'scroll',
			'fixed'  => 'fixed'
		)
	);

	$fields['remove_sidebar'] = array(
		'name' => __( 'Remove Sidebar', 'pootlepage' ),
		'type' => 'checkbox',
	);

	$fields['full_width'] = array(
		'name' => __( 'Make page go full width', 'pootlepage' ),
		'type' => 'checkbox',
	);

	$fields['keep_content_at_site_width'] = array(
		'name' => __( 'Keep content at site width', 'pootlepage' ),
		'type' => 'checkbox',
	);

	return $fields;
}

function pootlepage_hide_elements_fields() {

	$fields = array();

	$fields['hide_logo_strapline'] = array(
		'name' => __( 'Hide logo/strapline', 'pootlepage' ),
		'type' => 'checkbox',
	);

	$fields['hide_header'] = array(
		'name' => __( 'Hide header', 'pootlepage' ),
		'type' => 'checkbox',
	);

	$fields['hide_main_navigation'] = array(
		'name' => __( 'Hide main navigation', 'pootlepage' ),
		'type' => 'checkbox',
	);

	$fields['hide_page_title'] = array(
		'name' => __( 'Hide page title', 'pootlepage' ),
		'type' => 'checkbox',
	);

	$fields['hide_footer_widgets'] = array(
		'name' => __( 'Hide footer widgets', 'pootlepage' ),
		'type' => 'checkbox',
	);

	$fields['hide_footer'] = array(
		'name' => __( 'Hide footer', 'pootlepage' ),
		'type' => 'checkbox',
	);

	return $fields;
}

function pootlepage_dialog_form_echo( $fields ) {

	foreach ( $fields as $name => $attr ) {

		echo '<p class="field_' . esc_attr( $name ) . '">';
		echo '<label>' . $attr['name'] . '</label>';

		switch ( $attr['type'] ) {
			case 'select':
				?>
				<select name="panelsStyle[<?php echo esc_attr( $name ) ?>]"
				        data-style-field="<?php echo esc_attr( $name ) ?>"
				        data-style-field-type="<?php echo esc_attr( $attr['type'] ) ?>">
					<?php foreach ( $attr['options'] as $ov => $on ) : ?>
						<option value="<?php echo esc_attr( $ov ) ?>"><?php echo esc_html( $on ) ?></option>
					<?php endforeach ?>
				</select>
				<?php
				break;

			case 'checkbox' :
				?>
				<label class="siteorigin-panels-checkbox-label">
					<input type="checkbox" name="panelsStyle[<?php echo esc_attr( $name ) ?>]"
					       data-style-field="<?php echo esc_attr( $name ) ?>"
					       data-style-field-type="<?php echo esc_attr( $attr['type'] ) ?>"/>
					Enabled
				</label>
				<?php
				break;

			case 'number' :
				?><input type="number" min="<?php echo $attr['min'] ?>" value="<?php echo $attr['default'] ?>"
				         name="panelsStyle[<?php echo esc_attr( $name ) ?>]"
				         data-style-field="<?php echo esc_attr( $name ) ?>"
				         data-style-field-type="<?php echo esc_attr( $attr['type'] ) ?>" /> <?php
				break;

			case 'upload':
				?><input type="text" id="pp-pb-<?php esc_attr_e( $name ) ?>"
				         name="panelsStyle[<?php echo esc_attr( $name ) ?>]"
				         data-style-field="<?php echo esc_attr( $name ) ?>"
				         data-style-field-type="<?php echo esc_attr( $attr['type'] ) ?>" />
				<button class="button upload-button">Select Image</button><?php
				break;

			default :
				?><input type="file" name="panelsStyle[<?php echo esc_attr( $name ) ?>]"
				         data-style-field="<?php echo esc_attr( $name ) ?>"
				         data-style-field-type="<?php echo esc_attr( $attr['type'] ) ?>" />
				<?php
				break;
		}

		echo '</p>';
	}
}

function pootlepage_hide_elements_dialog_echo( $fields ) {

	foreach ( $fields as $name => $attr ) {

		echo '<p>';
		echo '<label>' . $attr['name'] . '</label>';

		switch ( $attr['type'] ) {
			case 'checkbox' :
				?>
				<input type="checkbox" name="panelsStyle[<?php echo esc_attr( $name ) ?>]"
				       data-style-field="<?php echo esc_attr( $name ) ?>"
				       data-style-field-type="<?php echo esc_attr( $attr['type'] ) ?>"/>
				<?php
				break;
			default :
				?><input type="text" name="panelsStyle[<?php echo esc_attr( $name ) ?>]"
				         data-style-field="<?php echo esc_attr( $name ) ?>"
				         data-style-field-type="<?php echo esc_attr( $attr['type'] ) ?>" /> <?php
				break;
		}

		echo '</p>';
	}
}

function siteorigin_panels_style_dialog_form() {
	$fields = siteorigin_panels_style_get_fields();

	$sections['Background'][] = 'background';
	$sections['Background'][] = 'background_toggle';

	$sections['Background'][] = array( '<div class="bg_section bg_image">' );
	$sections['Background'][] = 'background_color_over_image';
	$sections['Background'][] = 'background_image';
	$sections['Background'][] = 'background_image_repeat';
	$sections['Background'][] = 'background_parallax';
	$sections['Background'][] = 'background_image_size';

	/** @hook ppb_row_styles_section_bg_image Add field id in background image sub section */
	$sections['Background'] = apply_filters( 'ppb_row_styles_section_bg_image', $sections['Background'] );
	$sections['Background'][] = array( '</div>' );

	$sections['Background'][] = array( '<div class="bg_section bg_video">' );
	$sections['Background'][] = 'bg_video';
	$sections['Background'][] = 'bg_mobile_image';
	/** @hook ppb_row_styles_section_bg_image Add field id in background video sub section */
	$sections['Background'] = apply_filters( 'ppb_row_styles_section_bg_video', $sections['Background'] );

	$sections['Background'][] = array( '</div>' );

	$sections['Layout'][]     = 'full_width';
	$sections['Layout'][]     = 'row_height';
	/** @hook ppb_row_styles_section_bg_image Add field id in layout section */
	$sections['Layout'] = apply_filters( 'ppb_row_styles_section_layout', $sections['Layout'] );

	$sections['Advanced'][]   = 'style';
	$sections['Advanced'][]   = 'class';
	$sections['Advanced'][]   = 'id';
	/** @hook ppb_row_styles_section_bg_image Add field id in advanced section */
	$sections['Advanced'] = apply_filters( 'ppb_row_styles_section_advanced', $sections['Advanced'] );

	if ( empty( $fields ) ) {
		_e( "Your theme doesn't provide any visual style fields. " );

		return;
	}

	$fields_output = '';

	echo '<ul class="ppb-acp-sidebar">';

	foreach ( $sections as $Sec => $secFields ) {

		$sec = strtolower( $Sec );

		echo "<li><a href='#ppb-style-section-{$sec}'>$Sec</a></li>";

		ob_start();

		echo "<div id='ppb-style-section-{$sec}' class='ppb-style-section'>";

		foreach ( $secFields as $name ) {

			if ( is_array( $name ) ) {
				echo $name[0];
				continue;
			}

			$attr = $fields[ $name ];

			echo '<p class="field_' . esc_attr( $name ) . '">';

			echo '<label>' . $attr['name'] . '</label>';
			pootlepage_render_single_field( $name, $attr );
			echo '</p>';
		}

		echo "</div>";

		$fields_output .= ob_get_clean();
	}

	echo '</ul>';
	echo $fields_output;

}

function pootlepage_render_single_field( $name, $attr ) {

	switch ( $attr['type'] ) {
		case 'select':
			?>
			<select name="panelsStyle[<?php echo esc_attr( $name ) ?>]"
			        data-style-field="<?php echo esc_attr( $name ) ?>"
			        data-style-field-type="<?php echo esc_attr( $attr['type'] ) ?>">
				<?php foreach ( $attr['options'] as $ov => $on ) : ?>
					<option
						value="<?php echo esc_attr( $ov ) ?>" <?php if ( isset( $attr['default'] ) ) selected( $ov, $attr['default'] ) ?>  ><?php echo esc_html( $on ) ?></option>
				<?php endforeach ?>
			</select>
			<?php
			break;

		case 'checkbox' :
			$checked = ( isset( $attr['default'] ) ? checked( $attr['default'], true, false ) : '' );
			?>
			<label class="siteorigin-panels-checkbox-label">
				<input type="checkbox" <?php echo $checked ?> name="panelsStyle[<?php echo esc_attr( $name ) ?>]"
				       data-style-field="<?php echo esc_attr( $name ) ?>"
				       data-style-field-type="<?php echo esc_attr( $attr['type'] ) ?>"/>
			</label>
			<?php
			break;

		case 'number' :
			?><input type="number" min="<?php echo $attr['min'] ?>" value="<?php echo $attr['default'] ?>"
			         name="panelsStyle[<?php echo esc_attr( $name ) ?>]"
			         data-style-field="<?php echo esc_attr( $name ) ?>"
			         data-style-field-type="<?php echo esc_attr( $attr['type'] ) ?>" />
			<?php
			if ( isset( $attr['help-text'] ) ) {
				// don't use div for this or else div will appear outside of <p>
				echo "<span class='small-help-text'>" . esc_html( $attr['help-text'] ) . "</span>";
			}
			break;

		case 'upload':
			?><input type="text" id="pp-pb-<?php esc_attr_e( $name ) ?>"
			         name="panelsStyle[<?php echo esc_attr( $name ) ?>]"
			         data-style-field="<?php echo esc_attr( $name ) ?>"
			         data-style-field-type="<?php echo esc_attr( $attr['type'] ) ?>" />
			<button class="button upload-button">Select Image</button><?php
			break;

		case 'uploadVid':
			?><input type="text" id="pp-pb-<?php esc_attr_e( $name ) ?>"
			         name="panelsStyle[<?php echo esc_attr( $name ) ?>]"
			         data-style-field="<?php echo esc_attr( $name ) ?>"
			         data-style-field-type="<?php echo esc_attr( $attr['type'] ) ?>" />
			<button class="button video-upload-button">Select Video</button><?php
			break;

		case 'textarea':
			?><textarea type="text" name="panelsStyle[<?php echo esc_attr( $name ) ?>]"
			            data-style-field="<?php echo esc_attr( $name ) ?>"
			            data-style-field-type="<?php echo esc_attr( $attr['type'] ) ?>" ></textarea> <?php
			break;

		default :
			?><input type="text" name="panelsStyle[<?php echo esc_attr( $name ) ?>]"
			         data-style-field="<?php echo esc_attr( $name ) ?>"
			         data-style-field-type="<?php echo esc_attr( $attr['type'] ) ?>" /> <?php
			break;
	}

	if ( isset( $attr['help-text'] ) ) {
		// don't use div for this or else div will appear outside of <p>
		echo "<span class='small-help-text'>" . $attr['help-text'] . "</span>";
	}
}

function pp_pb_widget_styles_dialog_form() {
	$fields = pp_pb_widget_styling_fields();

	foreach ( $fields as $key => $field ) {

		echo "<div class='field'>";
		echo "<label>" . esc_html( $field['name'] ) . "</label>";
		echo "<span>";

		switch ( $field['type'] ) {
			case 'color' :
				?><input dialog-field="<?php echo $key ?>" class="widget-<?php echo $key ?>" type="text"
				         data-style-field-type="color"/>
				<?php
				break;
			case 'border' :
				?><input dialog-field="<?php echo $key ?>-width" class="widget-<?php echo $key ?>-width" type="number"
				         min="0" max="100" step="1" value="" /> px
				<input dialog-field="<?php echo $key ?>-color" class="widget-<?php echo $key ?>-color" type="text"
				       data-style-field-type="color"/>
				<?php
				break;
			case 'number' :
				?><input dialog-field="<?php echo $key ?>" class="widget-<?php echo $key ?>" type="number"
				         min="<?php esc_attr_e( $field['min'] ) ?>" max="<?php esc_attr_e( $field['max'] ) ?>"
				         step="<?php esc_attr_e( $field['step'] ) ?>" value="" /> <?php esc_html_e( $field['unit'] ) ?>
				<?php
				break;
			case 'checkbox':
				?><input dialog-field="<?php echo $key ?>" class="widget-<?php echo $key ?>" type="checkbox"
				         value="<?php esc_attr_e( $field['value'] ) ?>" data-style-field-type="checkbox" />
				<?php
				break;
			case 'textarea':
				?><input dialog-field="<?php echo $key ?>" class="widget-<?php echo $key ?>" type="text"
				         data-style-field-type="text"/>
				<?php
				break;
		}

		echo "</span>";
		echo '</div>';
	}
}


/**
 * Check if we're using a color in any of the style fields.
 *
 * @return bool
 */
function siteorigin_panels_style_is_using_color() {
	$fields = siteorigin_panels_style_get_fields();

	foreach ( $fields as $id => $attr ) {
		if ( isset( $attr['type'] ) && $attr['type'] == 'color' ) {
			return true;
		}
	}

	return false;
}

/**
 * Convert the single string attribute of the grid style into an array.
 *
 * @param $panels_data
 *
 * @return mixed
 */
function siteorigin_panels_style_update_data( $panels_data ) {
	if ( empty( $panels_data['grids'] ) ) {
		return $panels_data;
	}

	for ( $i = 0; $i < count( $panels_data['grids'] ); $i ++ ) {

		if ( isset( $panels_data['grids'][ $i ]['style'] ) && is_string( $panels_data['grids'][ $i ]['style'] ) ) {
			$panels_data['grids'][ $i ]['style'] = array( 'class' => $panels_data['grids'][ $i ]['style'] );
		}
	}

	return $panels_data;
}

add_filter( 'siteorigin_panels_data', 'siteorigin_panels_style_update_data' );
add_filter( 'siteorigin_panels_prebuilt_layout', 'siteorigin_panels_style_update_data' );

/**
 * Sanitize all the data that's come from post data
 *
 * @param $panels_data
 */
function siteorigin_panels_style_sanitize_data( $panels_data ) {
	$fields = siteorigin_panels_style_get_fields();

	if ( empty( $fields ) ) {
		return $panels_data;
	}
	if ( empty( $panels_data['grids'] ) || ! is_array( $panels_data['grids'] ) ) {
		return $panels_data;
	}

	for ( $i = 0; $i < count( $panels_data['grids'] ); $i ++ ) {

		foreach ( $fields as $name => $attr ) {
			switch ( $attr['type'] ) {
				case 'checkbox':
					// Convert the checkbox value to true or false.
					$panels_data['grids'][ $i ]['style'][ $name ] = ! empty( $panels_data['grids'][ $i ]['style'][ $name ] );
					break;

				case 'number':
					$panels_data['grids'][ $i ]['style'][ $name ] = intval( $panels_data['grids'][ $i ]['style'][ $name ] );
					break;

				case 'url':
					$panels_data['grids'][ $i ]['style'][ $name ] = esc_url_raw( $panels_data['grids'][ $i ]['style'][ $name ] );
					break;

				case 'select' :
					// Make sure the value is in the options
					if ( ! in_array( $panels_data['grids'][ $i ]['style'][ $name ], array_keys( $attr['options'] ) ) ) {
						$panels_data['grids'][ $i ]['style'][ $name ] = false;
					}
					break;
			}
		}
	}

	return $panels_data;
}

add_filter( 'siteorigin_panels_panels_data_from_post', 'siteorigin_panels_style_sanitize_data' );