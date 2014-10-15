<?php

if ( ! class_exists( 'PootlePage_Padding_Control' ) ) :
    if (!class_exists( 'WP_Customize_Control' )) {
        require_once(ABSPATH . '/wp-includes/class-wp-customize-control.php');
    }

	class PootlePage_Padding_Control extends WP_Customize_Control {

        public $option_name;
        public $type = 'padding';

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


        public function enqueue() {

            global $PootlePageFile;

            wp_enqueue_style('pootlepage-customize-controls', plugin_dir_url($PootlePageFile) . 'css/customize-controls.css');

            parent::enqueue();
        }

        public function get_top_bottom_control() {

            // Variables used in view
            $value          = $this->value('top_bottom_width');
            $step           = 1;
            $min_range      = 0;
            $max_range      = 100;
            $default_amount = $this->default['top_bottom_width'];

            $current_amount = isset( $value ) ? $value : $default_amount;

            // Get control view
            ?>
            <label><?php _e( 'Top/Bottom', 'scratch' ); ?>

                <input class='pp-top-bottom-width-number' type="number" min="<?php echo $min_range ?>"
                       max="<?php echo $max_range ?>" step="<?php echo $step ?>" value="<?php echo $current_amount ?>"
                       default="<?php echo $default_amount ?>"
                    <?php $this->link('top_bottom_width') ?>
                    />
                px

            </label>

        <?php
        }

        public function get_left_right_control() {

            // Variables used in view
            $value          = $this->value('left_right_width');
            $step           = 1;
            $min_range      = 0;
            $max_range      = 100;
            $default_amount = $this->default['left_right_width'];

            $current_amount = isset( $value ) ? $value : $default_amount;

            // Get control view
            ?>
            <label><?php _e( 'Left/Right', 'scratch' ); ?>

                <input class='pp-left-right-width-number' type="number" min="<?php echo $min_range ?>"
                       max="<?php echo $max_range ?>" step="<?php echo $step ?>" value="<?php echo $current_amount ?>"
                       default="<?php echo $default_amount ?>"
                    <?php $this->link('left_right_width') ?>
                    />
                px

            </label>

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

                    <?php $this->get_top_bottom_control(); ?>

                    <div class="separator"></div>

                    <?php $this->get_left_right_control(); ?>

                </div>
            </label>
            <?php
		}
	}
endif;