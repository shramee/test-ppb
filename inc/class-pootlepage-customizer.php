<?php

/**
 * Created by Alan on 19/5/2014.
 */
class PootlePage_Customizer {

	private $options;

	public function __construct() {

		$this->init_options();

		$this->output = new PootlePage_Output( $this->options );

		//Add customize fields, settings and section
		add_action( 'customize_register', array( $this, 'register' ) );

		//Enqueue script and styles
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue' ) );

		//Output CSS in head
		add_action( 'wp_head', array( $this->output, 'output_css' ), 50 );

		//Call google font if required
		add_action( 'wp_head', array( $this->output, 'google_webfonts' ) );

		//Multi field settings
		$this->multi_fields = array(
			'border'  => 'PootlePage_Border_Control',
			'padding' => 'PootlePage_Padding_Control',
			'font'    => 'PootlePage_Font_Control',
		);

		//Single field settings
		$this->single_fields = array(
			'color' => 'WP_Customize_Color_Control',
		);

	}

	public function init_options() {

		$choices = array();
		for ( $i = 0; $i <= 20; ++ $i ) {
			$choices[ $i ] = $i . 'px';
		}

		$this->options = array(
			'siteorigin_panels_display[margin-bottom]' => array(
				'id'       => 'siteorigin_panels_display[margin-bottom]',
				'type'     => 'number',
				'label'    => __( 'Row bottom margin', 'scratch' ),
				'section'  => 'pootlepage_section',
				'default'  => '0',
				'priority' => 10
			),
			'pp_widget_bg_color'                       => array(
				'id'       => 'pp_widget_bg_color',
				'type'     => 'color',
				'label'    => __( 'Content Block Background Color', 'scratch' ),
				'section'  => 'pootlepage_section',
				'default'  => '',
				'priority' => 10
			),
			'pp_widget_border_width'                   => array(
				'id'       => 'pp_widget_border_width',
				'type'     => 'number',
				'label'    => __( 'Content block border width', 'scratch' ),
				'section'  => 'pootlepage_section',
				'default'  => '0',
				'priority' => 10
			),
			'pp_widget_border_color'                   => array(
				'id'       => 'pp_widget_border_color',
				'type'     => 'color',
				'label'    => __( 'Content block border color', 'scratch' ),
				'section'  => 'pootlepage_section',
				'default'  => '',
				'priority' => 10
			),
			'pp_widget_border_radius'                  => array(
				'id'       => 'pp_widget_border_radius',
				'type'     => 'select',
				'label'    => __( 'Content Block Rounded Corners', 'scratch' ),
				'section'  => 'pootlepage_section',
				'default'  => '0',
				'choices'  => $choices,
				'priority' => 16
			),
		);
	}

	public function register( WP_Customize_Manager $customizeManager ) {

		require_once dirname( __FILE__ ) . '/class-pootlepage-font-control.php';
		require_once dirname( __FILE__ ) . '/class-pootlepage-border-control.php';
		require_once dirname( __FILE__ ) . '/class-pootlepage-padding-control.php';

		// sections
		$customizeManager->add_section( 'pootlepage_section', array(
			'title'    => 'Page Builder',
			'priority' => 10
		) );

		foreach ( $this->options as $k => $option ) {
			if ( array_key_exists( $option['type'], $this->multi_fields ) ) {

				$this->multi_field_register( $option, $customizeManager );

			} else {

				$this->single_field_register( $option, $customizeManager );

			}
		}

	}

	/**
	 * Adds single field option
	 *
	 * @param array $option Current option
	 * @param WP_Customize_Manager $customizeManager
	 */
	private function single_field_register( $option, $customizeManager ) {

		$customizeManager->add_setting( $option['id'], array(
			'default' => $option['default'],
			'type'    => 'option',
		) );

		$option['settings'] = $option['id'];

		if ( $option['type'] == 'color' ) {

			$customizeManager->add_control(
				new WP_Customize_Color_Control(
					$customizeManager,
					$option['id'],
					$option
				)
			);

		} else {

			$customizeManager->add_control(
				new WP_Customize_Control(
					$customizeManager,
					$option['id'],
					$option
				)
			);
		}
	}

	/**
	 * Adds multi field option
	 *
	 * @param array $option Current option
	 * @param WP_Customize_Manager $customizeManager
	 */
	private function multi_field_register( $option, $customizeManager ) {

		foreach ( $option['settings'] as $key => $settingID ) {

			//Init default
			$defaultValue = '';

			//Get default if set
			if ( ! empty ( $option['defaults'][ $key ] ) ) {
				$defaultValue = $option['defaults'][ $key ];
			}

			//Add setting
			$customizeManager->add_setting(
				$settingID,
				array(
					'default' => $defaultValue,
					'type'    => 'option',
				)
			);
		}

		$className = $this->multi_fields[ $option['type'] ];

		$customizeManager->add_control(
			new $className(
				$customizeManager,
				$option['id'],
				$option
			)
		);
	}

	public function enqueue() {
		global $PootlePageFile;

		wp_enqueue_script( 'wp-color-picker' );

		// load in footer, so will appear after WP customize-base.js and customize-controls.js
		wp_enqueue_script( 'pootlepage-customize-controls', plugin_dir_url( $PootlePageFile ) . 'js/customize-controls.js', array( 'jquery' ), false, true );

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'pootlepage-customize-controls', plugin_dir_url( $PootlePageFile ) . 'css/customize-controls.css' );

	}
}