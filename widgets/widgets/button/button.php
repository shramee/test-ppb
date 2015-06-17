<?php

class SiteOrigin_Panels_Widget_Button extends SiteOrigin_Panels_Widget {
	function __construct() {
		parent::__construct(
			__( 'Button ( Pootle )', 'ppb-panels' ),
			array(
				'description'   => __( 'A simple button', 'ppb-panels' ),
				'default_style' => 'simple',
			),
			array(),
			array(
				'text'       => array(
					'type'  => 'text',
					'label' => __( 'Text', 'ppb-panels' ),
				),
				'url'        => array(
					'type'  => 'text',
					'label' => __( 'Destination URL', 'ppb-panels' ),
				),
				'new_window' => array(
					'type'  => 'checkbox',
					'label' => __( 'Open In New Window', 'ppb-panels' ),
				),
				'align'      => array(
					'type'    => 'select',
					'label'   => __( 'Button Alignment', 'ppb-panels' ),
					'options' => array(
						'left'    => __( 'Left', 'ppb-panels' ),
						'right'   => __( 'Right', 'ppb-panels' ),
						'center'  => __( 'Center', 'ppb-panels' ),
						'justify' => __( 'Justify', 'ppb-panels' ),
					)
				),
			)
		);
	}

	function widget_classes( $classes, $instance ) {
		$classes[] = 'align-' . ( empty( $instance['align'] ) ? 'none' : $instance['align'] );

		return $classes;
	}
}