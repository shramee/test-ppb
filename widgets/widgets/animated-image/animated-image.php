<?php

class SiteOrigin_Panels_Widget_Animated_Image extends SiteOrigin_Panels_Widget {
	function __construct() {
		parent::__construct(
			__( 'Animated Image ( Pootle )', 'ppb-panels' ),
			array(
				'description'   => __( 'An image that animates in when it enters the screen.', 'ppb-panels' ),
				'default_style' => 'simple',
			),
			array(),
			array(
				'image'     => array(
					'type'  => 'text',
					'label' => __( 'Image URL', 'ppb-panels' ),
				),
				'animation' => array(
					'type'    => 'select',
					'label'   => __( 'Animation', 'ppb-panels' ),
					'options' => array(
						'fade'        => __( 'Fade In', 'ppb-panels' ),
						'slide-up'    => __( 'Slide Up', 'ppb-panels' ),
						'slide-down'  => __( 'Slide Down', 'ppb-panels' ),
						'slide-left'  => __( 'Slide Left', 'ppb-panels' ),
						'slide-right' => __( 'Slide Right', 'ppb-panels' ),
					)
				),
			)
		);
	}

	function enqueue_scripts() {
		static $enqueued = false;
		if ( ! $enqueued ) {
			wp_enqueue_script( 'siteorigin-widgets-' . $this->origin_id . '-onscreen', plugin_dir_url( __FILE__ ) . 'js/onscreen.js', array( 'jquery' ), POOTLEPAGE_VERSION );
			wp_enqueue_script( 'siteorigin-widgets-' . $this->origin_id, plugin_dir_url( __FILE__ ) . 'js/main.js', array( 'jquery' ), POOTLEPAGE_VERSION );
			$enqueued = true;
		}

	}
}