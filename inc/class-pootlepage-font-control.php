<?php

if ( ! class_exists( 'PootlePage_Font_Control' ) ) :
	if ( ! class_exists( 'WP_Customize_Control' ) ) {
		require_once( ABSPATH . '/wp-includes/class-wp-customize-control.php' );
	}

	class PootlePage_Font_Control extends WP_Customize_Control {

		public $option_name;
		public $type = 'font';

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

		/**
		 * Get Font Family Control
		 *
		 * Gets the font family select control. Will only show
		 * the fonts from the applicable subset if it has been
		 * selected.
		 *
		 * @uses EGF_Font_Utilities::get_google_fonts()    defined in includes\class-egf-font-utilities
		 * @uses EGF_Font_Utilities::get_default_fonts()    defined in includes\class-egf-font-utilities
		 *
		 * @since 1.2
		 * @version 1.3.1
		 *
		 */
		public function get_font_family_control() {

			// Get defaults and current value
			$this_value    = $this->value( 'font_id' );
			$default_value = $this->default['font_id'];
			$current_value = empty( $this_value ) ? '' : $this_value;

			// Get control view
			?>
			<label><?php _e( 'Font Family', 'scratch' ); ?>
				<select class='sc-font-family-list' <?php $this->link( 'font_id' ) ?>
				        data-default-value="<?php echo $default_value ?>" autocomplete="off">
					<option
						value="" <?php selected( $current_value, '' ); ?> ><?php _e( '&mdash; Default &mdash;', 'scratch' ); ?></option>
					<?php
					//Font Options for select
					echo pootle_page_output_font_select_options( $current_value );

					?>
				</select>
			</label>
		<?php
		}

		/**
		 * Get Font Weight Control
		 *
		 * Gets the font family select control. Preselects the
		 * appropriate font weight if is has been selected.
		 *
		 * @uses EGF_Font_Utilities::get_font()    defined in includes\class-egf-font-utilities
		 *
		 * @since 1.2
		 * @version 1.3.1
		 *
		 */
		public function get_font_weight_control() {
			// Get values
			$this_value                = $this->value( 'font_weight_style' );
			$default_font_weight_style = $this->default['font_weight_style'];
			$font_weight_style         = empty( $this_value ) ? '' : $this_value;
			// Get control view
			?>

			<label><?php _e( 'Font Weight/Style', 'scratch' ); ?>
				<select class="sc-font-weight-style-list" <?php $this->link( 'font_weight_style' ) ?>
				        data-default-value="<?php echo $default_font_weight_style; ?>">
					<option
						value="" <?php selected( $font_weight_style, '' ) ?> ><?php _e( '&mdash; Default &mdash;', 'scratch' ); ?></option>
					<option value="100">100</option>
					<option value="100italic">100italic</option>
					<option value="400">400</option>
					<option value="400italic">400italic</option>
					<option value="700">700</option>
					<option value="700italic">700italic</option>
				</select>
			</label>
		<?php
		}

		/**
		 * Get Font Color Control
		 *
		 * Gets the font color input control.
		 *
		 * @since 1.2
		 * @version 1.3.1
		 *
		 */
		public function get_font_color_control() {
			// Variables for color control
			$value         = $this->value( 'font_color' );
			$default_color = $this->default['font_color'];
			// Output the color control
			pootlepage_color_control( 'Font Color', $value, $default_color, $this->get_link( 'font_color' ) );

		}

		/**
		 * Get Font Size Control
		 *
		 * Gets the font size slider input control.
		 *
		 * @since 1.2
		 * @version 1.3.1
		 *
		 */
		public function get_font_size_control() {

			// Variables used in view
			$value          = $this->value( 'font_size' );
			$step           = 1;//$this->font_properties['font_size_step'];
			$min_range      = 10;//$this->font_properties['font_size_min_range'];
			$max_range      = 100;//$this->font_properties['font_size_max_range'];
			$default_amount = $this->default['font_size'];
			$default_unit   = $this->default['font_size_unit'];

			$current_amount = $value;

			$unitValue    = $this->value( 'font_size_unit' );
			$current_unit = $unitValue;

			// Get control view
			?>
			<label><?php _e( 'Font Size', 'scratch' ); ?>

				<input class='sc-font-size-number' type="number" min="<?php echo $min_range ?>"
				       max="<?php echo $max_range ?>" step="<?php echo $step ?>" value="<?php echo $current_amount ?>"
				       default="<?php echo $default_amount ?>"
					<?php $this->link( 'font_size' ) ?>
					/>
				<select class='sc-font-size-unit'
				        data-default-value="<?php echo $default_unit ?>" <?php $this->link( 'font_size_unit' ) ?>  >
					<option value="px" <?php selected( $current_unit, 'px' ) ?> >px</option>
					<option value="em" <?php selected( $current_unit, 'em' ) ?> >em</option>
				</select>

			</label>

		<?php
		}


		/**
		 * Get Hidden Control Input
		 *
		 * This hidden input is used to store all of the
		 * settings that belong to this current font
		 * control.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/wp_parse_args    wp_parse_args()
		 *
		 * @since 1.2
		 * @version 1.3.1
		 *
		 */
		public function get_hidden_control_input() {
			?>
			<input type="hidden" id="<?php echo $this->id; ?>-settings" name="<?php echo $this->id; ?>"
			       value="<?php $this->value(); ?>" data-customize-setting-link="<?php echo $this->option_name; ?>"/>
		<?php
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
			<label>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>

				<div class="customize-control-content">

					<?php $this->get_font_family_control(); ?>

					<div class="separator"></div>

					<?php $this->get_font_weight_control(); ?>

					<div class="separator"></div>

					<?php $this->get_font_color_control(); ?>

					<div class="separator"></div>

					<?php $this->get_font_size_control(); ?>

					<!--					<input type="hidden" class="sc-font-value" value="-->
					<?php //esc_attr_e( $this->value() ) ?><!--" --><?php //$this->link(); ?><!-- />-->
				</div>
			</label>
		<?php
		}
	}
endif;