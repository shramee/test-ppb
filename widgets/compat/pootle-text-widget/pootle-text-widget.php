<?php
/**
 *  This file adds compatibility with Pootle Text Widget.
 */

/**
 * Add all the required actions for the TinyMCE widget.
 */
function pp_page_builder_pootle_text_widget_admin_init() {
	global $pagenow;

	if (
		in_array($pagenow, array('post-new.php', 'post.php')) ||
		($pagenow == 'themes.php' && isset($_GET['page']) && $_GET['page'] == 'so_panels_home_page' )
	)  {
		add_action( 'admin_head', 'pootle_text_widget_load_tiny_mce' );
		add_filter( 'tiny_mce_before_init', 'pootle_text_widget_init_editor', 20 );
		add_action( 'admin_print_scripts', 'pootle_text_widget_scripts' );
		add_action( 'admin_print_styles', 'pootle_text_widget_styles' );
		add_action( 'admin_print_footer_scripts', 'pootle_text_widget_footer_scripts' );
	}

}
add_action('admin_init', 'pp_page_builder_pootle_text_widget_admin_init');

/**
 * Enqueue all the admin scripts for Black Studio TinyMCE compatibility with Page Builder.
 *
 * @param $page
 */
function pp_page_builder_pootle_text_widget_admin_enqueue($page) {
	$screen = get_current_screen();
	if ( ( $screen->base == 'post' && in_array( $screen->id, siteorigin_panels_setting('post-types') ) ) || $screen->base == 'appearance_page_so_panels_home_page') {

		global $pootle_text_widget_version;
		if(!isset($pootle_text_widget_version)) {
//			if(function_exists('black_studio_tinymce_get_version')) {
//				$pootle_text_widget_version = black_studio_tinymce_get_version();
			//}
            $pootle_text_widget_version = '1.0.0';
		}

//		if( version_compare($pootle_text_widget_version, '1.3.3', '<=') ) {
//			// Use the old compatibility file.
            // pootle-text-widget is based on black-studio 1.3.3
			wp_enqueue_script( 'pootle-text-widget-pp-page-builder', plugin_dir_url( POOTLEPAGE_BASE_FILE ) . 'widgets/compat/pootle-text-widget/pootle-text-widget-pp-page-builder.old.js', array( 'jquery' ), POOTLEPAGE_VERSION );
//		}
//		else {
			// Use the new compatibility file
//			wp_enqueue_script( 'pootle-text-widget-pp-page-builder', plugin_dir_url( POOTLEPAGE_BASE_FILE ) . 'widgets/compat/pootle-text-widget/pootle-text-widget-pp-page-builder.min.js', array( 'jquery' ), POOTLEPAGE_VERSION );
//		}

		wp_enqueue_style('pootle-text-widget-pp-page-builder', plugin_dir_url(POOTLEPAGE_BASE_FILE).'widgets/compat/pootle-text-widget/pootle-text-widget-pp-page-builder.css', array(), POOTLEPAGE_VERSION);


//		if(version_compare($pootle_text_widget_version, '1.2.0', '<=')) {
//			// We also need a modified javascript for older versions of Black Studio TinyMCE
//			wp_enqueue_script('black-studio-tinymce-widget', plugin_dir_url(POOTLEPAGE_BASE_FILE) . 'widgets/compat/pootle-text-widget/pootle-text-widget.min.js', array('jquery'), POOTLEPAGE_VERSION);
//		}
	}
}
add_action('admin_enqueue_scripts', 'pp_page_builder_pootle_text_widget_admin_enqueue', 15);
