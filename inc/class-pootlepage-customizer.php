<?php
/**
 * Created by Alan on 19/5/2014.
 */

class PootlePage_Customizer {

	private $options;

	public function __construct() {

		$this->init_options();
		$this->output = new PootlePage_Output( $this->options );

		add_action( 'customize_register', array( $this, 'register' ) );

		add_action( 'wp_head', array( $this->output, 'output_css' ), 50 );
		add_action( 'wp_head', array( $this->output, 'google_webfonts' ) );

	}

	public function init_options() {

		$choices = array();
		for ( $i = 0; $i <= 20; ++$i ) {
			$choices[$i] = $i . 'px';
		}

		$this->options = array(
			'pp_widget_bottom_margin' => array(
				'id' => 'pp_widget_bottom_margin',
				'type' => 'number',
				'label' => __( 'Row bottom margin', 'scratch' ),
				'section' => 'pootlepage_section',
				'default' => '0',
				'priority' => 10
			),

			'pp_widget_bg_color' => array(
				'id' => 'pp_widget_bg_color',
				'type' => 'color',
				'label' => __( 'Content Block Background Color', 'scratch' ),
				'section' => 'pootlepage_section',
				'default' => '',
				'priority' => 10
			),

			'pp_widget_border_width' => array(
				'id' => 'pp_widget_border_width',
				'type' => 'number',
				'label' => __( 'Content block border width', 'scratch' ),
				'section' => 'pootlepage_section',
				'default' => '0',
				'priority' => 10
			),

			'pp_widget_border_color' => array(
				'id' => 'pp_widget_border_color',
				'type' => 'color',
				'label' => __( 'Content block border color', 'scratch' ),
				'section' => 'pootlepage_section',
				'default' => '',
				'priority' => 10
			),

			'pp_widget_border_radius' => array(
				'id' => 'pp_widget_border_radius',
				'type' => 'select',
				'label' => __( 'Content Block Rounded Corners', 'scratch' ),
				'section' => 'pootlepage_section',
				'default' => '0',
				'choices' => $choices,
				'priority' => 16
			),
		);
	}

	public function convert_canvas_font_style_to_pp( $style ) {
		if ( $style == '300' ) {
			return '100';
		} else if ( $style == '300 italic' ) {
			return '100italic';
		} else if ( $style == 'normal' ) {
			return '400';
		} else if ( $style == 'italic' ) {
			return '400italic';
		} else if ( $style == 'bold' ) {
			return '700';
		} else if ( $style == 'bolditalic' ) {
			return '700italic';
		} else {
			return '';
		}
	}

	public function convert_pp_font_style_to_canvas( $style ) {
		if ( $style == '100' ) {
			return '300';
		} else if ( $style == '100italic' ) {
			return '300 italic';
		} else if ( $style == '400' ) {
			return 'normal';
		} else if ( $style == '400italic' ) {
			return 'italic';
		} else if ( $style == '700' ) {
			return 'bold';
		} else if ( $style == '700italic' ) {
			return 'bolditalic';
		} else {
			return '';
		}
	}

	public function register( WP_Customize_Manager $customizeManager )
	{

		require_once dirname( __FILE__ ) . '/class-pootlepage-font-control.php';
		require_once dirname( __FILE__ ) . '/class-pootlepage-border-control.php';
		require_once dirname( __FILE__ ) . '/class-pootlepage-padding-control.php';

		// sections
		$customizeManager->add_section( 'pootlepage_section', array(
			'title' => 'Page Builder',
			'priority' => 10
		) );

		foreach ( $this->options as $k => $option ) {

			if ( $option['type'] == 'color' ) {

				$customizeManager->add_setting( $option['id'], array(
					'default' => $option['default'],
					'type' => 'option' // use option instead of theme_mod
				) );

				$customizeManager->add_control( new WP_Customize_Color_Control( $customizeManager, $option['id'], array(
					'label' => $option['label'],
					'section' => $option['section'],
					'settings' => $option['id'],
					'priority' => $option['priority']
				) ) );

			} else if ( $option['type'] == 'border' ) {
				foreach ( $option['settings'] as $key => $settingID ) {
					$defaultValue = $option['defaults'][$key];
					$customizeManager->add_setting( $settingID, array(
						'default' => $defaultValue,
						'type' => 'option'
					) );
				}

				$customizeManager->add_control( new PootlePage_Border_Control( $customizeManager, $option['id'], $option ) );

			} else if ( $option['type'] == 'padding' ) {

				foreach ( $option['settings'] as $key => $settingID ) {
					$defaultValue = $option['defaults'][$key];
					$customizeManager->add_setting( $settingID, array(
						'default' => $defaultValue,
						'type' => 'option'
					) );
				}

				$customizeManager->add_control( new PootlePage_Padding_Control( $customizeManager, $option['id'], $option ) );

			} else if ( $option['type'] == 'font' ) {

				foreach ( $option['settings'] as $key => $settingID ) {
					$defaultValue = $option['defaults'][$key];
					$customizeManager->add_setting( $settingID, array(
						'default' => $defaultValue,
						'type' => 'option'
					) );
				}

				$customizeManager->add_control( new PootlePage_Font_Control( $customizeManager, $option['id'], $option ) );

			} else if ( $option['type'] == 'select' ) {

				$customizeManager->add_setting( $option['id'], array(
					'default' => $option['default'],
					'type' => 'option'
				) );

				$customizeManager->add_control( new WP_Customize_Control( $customizeManager, $option['id'], $option ) );
			} else {

				$customizeManager->add_setting( $option['id'], array(
					'default' => $option['default'],
					'type' => 'option'
				) );

				$customizeManager->add_control( new WP_Customize_Control( $customizeManager, $option['id'], $option ) );
			}

		}

	}
}