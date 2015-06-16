<?php

if ( ! class_exists( 'PootlePage_Border_Control' ) ) :
	if ( ! class_exists( 'WP_Customize_Control' ) ) {
		require_once( ABSPATH . '/wp-includes/class-wp-customize-control.php' );
	}

	class PootlePage_Border_Control extends WP_Customize_Control {

		public $option_name;
		public $type = 'border';

		public $default;
		/**
		 * Constructor.
		 *
		 * If $args['settings'] is not defined, use the $id as the setting ID.
		 *
		 * @since 3.4.0
		 * @uses WP_Customize_Upload_Control::__construct()
		 *
		 * @param WP_Customize_Manager $manager
		 * @param string $id
		 * @param array $args
		 */
		public function __construct( $manager, $id, $args = array() ) {

			$this->default = $args['defaults'];

			parent::__construct( $manager, $id, $args );

		}

		public function get_border_width_control() {

			// Variables used in view
			$value		  = $this->value( 'border_width' );
			$step		   = 1;
			$min_range	  = 0;
			$max_range	  = 20;
			$default_amount = $this->default['border_width'];
			$default_unit   = 'px';//$this->defaults['font_size']['unit'];

			$current_amount = isset( $value ) ? $value : $default_amount;
			$current_unit   = $default_unit;

			// Get control view
			?>
			<label><span><?php _e( 'Border Width', 'scratch' ); ?></span>

				<input class='pp-border-width-number' type="number" min="<?php echo $min_range ?>"
					   max="<?php echo $max_range ?>" step="<?php echo $step ?>" value="<?php echo $current_amount ?>"
					   default="<?php echo $default_amount ?>"
					<?php $this->link( 'border_width' ) ?>
					/>
				px

			</label>

		<?php
		}

		public function get_border_color_control() {
			// Variables for color control
			$value		 = $this->value( 'border_color' );
			$default_color = $this->default['border_color'];
			// Output the color control
			pootlepage_color_control( 'Border Color', $value, $default_color, $this->get_link( 'border_color' ) );

		}


		/**
		 * Render Control Content
		 *
		 * Renders the control in the WordPress Customizer.
		 * Each section of the control has been split up
		 * in functions in order to make them easier to
		 * manage and update.
		 * 
		 * @since 1.2
		 * @version 1.3.1
		 * 
		 */
		public function render_content() {
			?>
			<label class="pootlepage-control">
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<div class="customize-control-content">

					<?php $this->get_border_width_control(); ?>

					<div class="separator"></div>

					<?php $this->get_border_color_control(); ?>

				</div>
			</label>
			<?php
		}
	}
endif;