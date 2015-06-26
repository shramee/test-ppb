<?php
/**
 * Created by PhpStorm.
 * User: shramee
 * Date: 26/6/15
 * Time: 6:46 PM
 */
class Pootle_Page_Builder_Public extends Pootle_Page_Builder_Abstract {
	/**
	 * @var Pootle_Page_Builder_Public instance of Pootle_Page_Builder
	 */
	protected static $instance;

	/**
	 * Magic __construct
	 *
	 */
	protected function __construct() {
		$this->includes();
		$this->actions();
	}

	protected function includes() {
		require_once POOTLEPAGE_DIR . 'inc/class-render-layout.php';
		require_once POOTLEPAGE_DIR . 'inc/class-front-css-js.php';
	}

	protected function actions(){

		add_filter( 'body_class', array( $this, 'body_class' ) );
	}

	/**
	 * Add all the necessary body classes.
	 * @param $classes
	 * @return array
	 */
	function body_class( $classes ) {

		if ( ppb_is_panel() ) {
			$classes[] = 'ppb-panels';
		}

		return $classes;
	}
}