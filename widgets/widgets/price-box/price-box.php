<?php

class SiteOrigin_Panels_Widget_Price_Box extends SiteOrigin_Panels_Widget {
	function __construct() {
		parent::__construct(
			__( 'Price Box ( Pootle )', 'ppb-panels' ),
			array(
				'description'   => __( 'Displays a bullet list of elements', 'ppb-panels' ),
				'default_style' => 'simple',
			),
			array(),
			array(
				'title'             => array(
					'type'  => 'text',
					'label' => __( 'Title', 'ppb-panels' ),
				),
				'price'             => array(
					'type'  => 'text',
					'label' => __( 'Price', 'ppb-panels' ),
				),
				'per'               => array(
					'type'  => 'text',
					'label' => __( 'Per', 'ppb-panels' ),
				),
				'information'       => array(
					'type'  => 'text',
					'label' => __( 'Information Text', 'ppb-panels' ),
				),
				'features'          => array(
					'type'        => 'textarea',
					'label'       => __( 'Features Text', 'ppb-panels' ),
					'description' => __( 'Start each new point with an asterisk ( * )', 'ppb-panels' ),
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

		$this->add_sub_widget( 'button', __( 'Button', 'ppb-panels' ), 'SiteOrigin_Panels_Widget_Button' );
		$this->add_sub_widget( 'list', __( 'Feature List', 'ppb-panels' ), 'SiteOrigin_Panels_Widget_List' );
	}
}