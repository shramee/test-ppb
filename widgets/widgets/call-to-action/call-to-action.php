<?php

class SiteOrigin_Panels_Widget_Call_To_Action extends SiteOrigin_Panels_Widget {
	function __construct() {
		parent::__construct(
			__( 'Call To Action ( Pootle )', 'ppb-panels' ),
			array(
				'description'   => __( 'A Call to Action block', 'ppb-panels' ),
				'default_style' => 'simple',
			),
			array(),
			array(
				'title'             => array(
					'type'  => 'text',
					'label' => __( 'Title', 'ppb-panels' ),
				),
				'subtitle'          => array(
					'type'  => 'text',
					'label' => __( 'Sub Title', 'ppb-panels' ),
				),
				'button_text'       => array(
					'type'  => 'text',
					'label' => __( 'Button Text', 'ppb-panels' ),
				),
				'button_url'        => array(
					'type'  => 'text',
					'label' => __( 'Button URL', 'ppb-panels' ),
				),
				'button_new_window' => array(
					'type'  => 'checkbox',
					'label' => __( 'Open In New Window', 'ppb-panels' ),
				),
			)
		);

		// We need the button style
		$this->add_sub_widget( 'button', __( 'Button', 'ppb-panels' ), 'SiteOrigin_Panels_Widget_Button' );
	}
}