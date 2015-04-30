<?php
/**
 * Created by PhpStorm.
 * User: shramee
 * Date: 30/4/15
 * Time: 4:43 PM
 */

class SiteOrigin_Panels_Widget_Helper {

	/**
	 * Display the form for the widget. Auto generated from form array.
	 *
	 * @param $field_id
	 * @param $field_args
	 *
	 * @return string|void
	 * @internal param array $instance
	 */
	public function form_field_output( $field_id, $field_args ) {

		if ( isset( $field_args['default'] ) && ! isset( $instance[$field_id] ) ) {
			$instance[$field_id] = $field_args['default'];
		}

		if ( ! isset( $instance[$field_id] ) ) { $instance[ $field_id ] = false; }
		?><p><label for="<?php echo $this->get_field_id( $field_id ); ?>"><?php echo esc_html( $field_args['label'] ) ?></label><?php
		if ( $field_args['type'] != 'checkbox' ) echo '<br />';

		switch( $field_args['type'] ) {
			case 'text' :
				?><input type="text" class="widefat" id="<?php echo $this->get_field_id( $field_id ); ?>" name="<?php echo $this->get_field_name( $field_id ); ?>" value="<?php echo esc_attr( $instance[$field_id] ) ?>" /><?php
				break;
			case 'textarea' :
				if ( empty( $field_args['height'] ) ) $field_args['height'] = 6;
				?><textarea class="widefat" id="<?php echo $this->get_field_id( $field_id ); ?>" name="<?php echo $this->get_field_name( $field_id ); ?>" rows="<?php echo intval( $field_args['height'] ) ?>"><?php echo esc_textarea( $instance[$field_id] ) ?></textarea><?php
				break;
			case 'number' :
				?><input type="number" class="small-text" id="<?php echo $this->get_field_id( $field_id ); ?>" name="<?php echo $this->get_field_name( $field_id ); ?>" value="<?php echo floatval( $instance[$field_id] ) ?>" /><?php
				break;
			case 'checkbox' :
				?><input type="checkbox" class="small-text" id="<?php echo $this->get_field_id( $field_id ); ?>" name="<?php echo $this->get_field_name( $field_id ); ?>" <?php checked( ! empty( $instance[$field_id] ) ) ?>/><?php
				break;
			case 'select' :
				?>
				<select id="<?php echo $this->get_field_id( $field_id ); ?>" name="<?php echo $this->get_field_name( $field_id ); ?>">
					<?php foreach( $field_args['options'] as $k => $v ) : ?>
						<option value="<?php echo esc_attr( $k ) ?>" <?php selected( $instance[$field_id], $k ) ?>><?php echo esc_html( $v ) ?></option>
					<?php endforeach; ?>
				</select>
				<?php
				break;
		}
		if ( ! empty( $field_args['description'] ) ) echo '<small class="description">'.esc_html( $field_args['description'] ).'</small>';
		?></p><?php
	}

	function widget_style_preset( $instance ) {

		if ( ! empty( $instance['origin_style'] ) ) {
			list( $style, $preset ) = explode( ':', $instance['origin_style'] );
			$style = sanitize_file_name( $style );
			$preset = sanitize_file_name( $preset );

			$data = $this->get_style_data( $style );
			$template = $data['Template'];
		}
		else {
			$style = 'default';
			$preset = 'default';
		}

		if ( empty( $template ) ) $template = 'default';

		return array( $style, $preset, $template );

	}
}