<?php
/**
 * Created by PhpStorm.
 * User: shramee
 * Date: 30/4/15
 * Time: 4:43 PM
 */

class SiteOrigin_Panels_Widget_Helper {

	/**
	 * Outputs field for SiteOrigin_Panels_Widget::form
	 *
	 * @param string $field_id
	 * @param array $field_args
	 * @param object $class The widget class
	 *
	 * @return string|void
	 */
	public function form_field_output( $field_id, $field_args, $class ) {

		if ( ! isset( $instance[$field_id] ) ) { $instance[ $field_id ] = false; }
		?><p><label for="<?php echo $class->get_field_id( $field_id ); ?>"><?php echo esc_html( $field_args['label'] ) ?></label><?php
		if ( $field_args['type'] != 'checkbox' ) echo '<br />';

		switch( $field_args['type'] ) {
			case 'text' :
				?><input type="text" class="widefat" id="<?php echo $class->get_field_id( $field_id ); ?>" name="<?php echo $class->get_field_name( $field_id ); ?>" value="<?php echo esc_attr( $instance[$field_id] ) ?>" /><?php
				break;
			case 'textarea' :
				if ( empty( $field_args['height'] ) ) $field_args['height'] = 6;
				?><textarea class="widefat" id="<?php echo $class->get_field_id( $field_id ); ?>" name="<?php echo $class->get_field_name( $field_id ); ?>" rows="<?php echo intval( $field_args['height'] ) ?>"><?php echo esc_textarea( $instance[$field_id] ) ?></textarea><?php
				break;
			case 'number' :
				?><input type="number" class="small-text" id="<?php echo $class->get_field_id( $field_id ); ?>" name="<?php echo $class->get_field_name( $field_id ); ?>" value="<?php echo floatval( $instance[$field_id] ) ?>" /><?php
				break;
			case 'checkbox' :
				?><input type="checkbox" class="small-text" id="<?php echo $class->get_field_id( $field_id ); ?>" name="<?php echo $class->get_field_name( $field_id ); ?>" <?php checked( ! empty( $instance[$field_id] ) ) ?>/><?php
				break;
			case 'select' :
				?>
				<select id="<?php echo $class->get_field_id( $field_id ); ?>" name="<?php echo $class->get_field_name( $field_id ); ?>">
					<?php foreach ( $field_args['options'] as $k => $v ) : ?>
						<option value="<?php echo esc_attr( $k ) ?>" <?php selected( $instance[$field_id], $k ) ?>><?php echo esc_html( $v ) ?></option>
					<?php endforeach; ?>
				</select>
				<?php
				break;
		}
		if ( ! empty( $field_args['description'] ) ) echo '<small class="description">'.esc_html( $field_args['description'] ).'</small>';
		?></p><?php
	}

	/**
	 * Returns $style, $preset and $template for SiteOrigin_Panels_Widget::widget
	 *
	 * @param array $instance
	 * @param object $class The widget class
	 * @return array
	 */
	function widget_style_preset( $instance, $class ) {

		if ( ! empty( $instance['origin_style'] ) ) {

			list( $style, $preset ) = explode( ':', $instance['origin_style'] );
			$style = sanitize_file_name( $style );
			$preset = sanitize_file_name( $preset );

			$data = $class->get_style_data( $style );
			$template = $data['Template'];

		} else {

			$style = 'default';
			$preset = 'default';

		}

		if ( empty( $template ) ) $template = 'default';
		return array( $style, $preset, $template );

	}

	/**
	 * Returns Checks if $template file exists and returns it for SiteOrigin_Panels_Widget::widget
	 *
	 * @param array $instance
	 * @param object $class The widget class
	 * @return bool|string
	 */
	function widget_check_template( $args, $template, $class ) {

		$template_file = false;
		$paths = $class->get_widget_paths();

		foreach ( $paths as $path ) {
			if ( file_exists( $path.'/'.$class->origin_id.'/tpl/'.$template.'.php' ) ) {
				$template_file = $path.'/'.$class->origin_id.'/tpl/'.$template.'.php';
				break;
			}
		}
		if ( empty( $template_file ) ) {
			echo $args['before_widget'];
			echo 'Template not found';
			echo $args['after_widget'];
			return false;
		}

		return $template_file;

	}

	/**
	 * Dynamically generates CSS for for SiteOrigin_Panels_Widget::widget
	 *
	 * @param $style
	 * @param $preset
	 * @param object $class The widget class
	 */
	function widget_dynamically_generate_css( $style, $preset, $instance, $class ) {

		if ( ! empty( $instance['origin_style'] ) ) {
			$filename = $class->origin_id.'-'.$style.'-'.$preset;
			static $inlined_css = array();
			if ( empty( $inlined_css[$filename] ) ) {
				$inlined_css[$filename] = true;
				?><style type = "text/css" media = "all"><?php echo origin_widgets_generate_css( get_class( $class ), $style, $preset ) ?></style><?php
			}
		}
	}

	/**
	 * Dynamically generates CSS for for SiteOrigin_Panels_Widget::widget
	 *
	 * @param $style
	 * @param $preset
	 * @param object $class The widget class
	 *
	 * @return mixed
	 */
	function widget_classes( $style, $preset, $instance, $class ) {

		$widget_classes = apply_filters( 'siteorigin_widgets_classes', array(
			'origin-widget',
			'origin-widget-'.$class->origin_id,
			'origin-widget-'.$class->origin_id.'-'. $style .'-' . $preset,
		), $instance );

		if ( method_exists( $class, 'widget_classes' ) ) {
			$widget_classes = $class->widget_classes( array(
				'origin-widget',
				'origin-widget-'.$class->origin_id,
				'origin-widget-'.$class->origin_id.'-'. $style .'-' . $preset,
			), $instance );
		}

		return $widget_classes;

	}
}