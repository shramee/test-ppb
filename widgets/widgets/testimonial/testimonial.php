<?php

class SiteOrigin_Panels_Widget_Testimonial extends SiteOrigin_Panels_Widget {
	function __construct() {
		parent::__construct(
			__( 'Testimonial ( Pootle )', 'ppb-panels' ),
			array(
				'description'   => __( 'Displays a bullet list of elements', 'ppb-panels' ),
				'default_style' => 'simple',
			),
			array(),
			array(
				'name'       => array(
					'type'  => 'text',
					'label' => __( 'Name', 'ppb-panels' ),
				),
				'location'   => array(
					'type'  => 'text',
					'label' => __( 'Location', 'ppb-panels' ),
				),
				'image'      => array(
					'type'  => 'text',
					'label' => __( 'Image', 'ppb-panels' ),
				),
				'text'       => array(
					'type'  => 'textarea',
					'label' => __( 'Text', 'ppb-panels' ),
				),
				'url'        => array(
					'type'  => 'text',
					'label' => __( 'URL', 'ppb-panels' ),
				),
				'new_window' => array(
					'type'  => 'checkbox',
					'label' => __( 'Open In New Window', 'ppb-panels' ),
				),
			)
		);
	}
}