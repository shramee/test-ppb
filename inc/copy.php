<?php

/**
 * Ajax handler to get the HTML representation of the request.
 */
function siteorigin_panels_content_save_pre_get() {
	if ( empty( $_POST['grids'] ) || empty( $_POST['grid_cells'] ) || empty( $_POST['widgets'] ) || empty( $_POST['panel_order'] ) ) {
		exit();
	}
	if ( empty( $_POST['_signature'] ) ) {
		exit();
	}

	$sig  = $_POST['_signature'];
	$data = array(
		'grids'       => $_POST['grids'],
		'grid_cells'  => $_POST['grid_cells'],
		'widgets'     => array_map( 'stripslashes_deep', $_POST['widgets'] ),
		'panel_order' => $_POST['panel_order'],
		'action'      => $_POST['action'],
		'post_id'     => $_POST['post_id'],
	);

	// Use the signature to secure the request.
	if ( $sig != sha1( NONCE_SALT . serialize( $data ) ) ) {
		exit();
	}

	// This can cause a fatal error, so handle in a separate request.
	$panels_data = siteorigin_panels_get_panels_data_from_post( $_POST );

	$content = '';
	if ( ! empty( $panels_data['widgets'] ) ) {
		// Save the panels data into post_content for SEO and search plugins
		$content = siteorigin_panels_render( filter_input( INPUT_POST, 'post_id' ), false, $panels_data );
		$content = preg_replace(
			array(
				// Remove invisible content
				'@<head[^>]*?>.*?</head>@siu',
				'@<style[^>]*?>.*?</style>@siu',
				'@<script[^>]*?.*?</script>@siu',
				'@<object[^>]*?.*?</object>@siu',
				'@<embed[^>]*?.*?</embed>@siu',
				'@<applet[^>]*?.*?</applet>@siu',
				'@<noframes[^>]*?.*?</noframes>@siu',
				'@<noscript[^>]*?.*?</noscript>@siu',
				'@<noembed[^>]*?.*?</noembed>@siu',
			),
			array( ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ),
			$content
		);
		$content = strip_tags( $content, '<img><h1><h2><h3><h4><h5><h6><a><p><em><strong>' );
		$content = explode( "\n", $content );
		$content = array_map( 'trim', $content );
		$content = implode( "\n", $content );

		$content = preg_replace( "/[\n]{2,}/", "\n\n", $content );
		$content = trim( $content );
	}

	echo $content;
	exit();
}
add_action( 'wp_ajax_nopriv_siteorigin_panels_get_post_content', 'siteorigin_panels_content_save_pre_get' );

/**
 * Convert form post data into more efficient panels data.
 * @param $form_post
 * @return array
 */
function siteorigin_panels_get_panels_data_from_post( $form_post ) {
	$panels_data            = array();
	$panels_data['widgets'] = array_values( stripslashes_deep( isset( $form_post['widgets'] ) ? $form_post['widgets'] : array() ) );

	foreach ( $panels_data['widgets'] as $i => $widget ) {

		if ( empty( $widget['info'] ) ) {
			continue;
		}

		$info = $widget['info'];

		$widget = json_decode( $widget['data'], true );

		if ( class_exists( $info['class'] ) ) {
			$the_widget = new $info['class'];
			if ( method_exists( $the_widget, 'update' ) && ! empty( $info['raw'] ) ) {
				$widget = $the_widget->update( $widget, $widget );
			}
		}

		unset( $info['raw'] );
		$widget['info'] = $info;

		// if widget style is not present in $_POST, set a default
		if ( ! isset( $info['style'] ) ) {
			$widgetStyle = ppb_default_content_block_style();

			$info['style'] = $widgetStyle;
		}

		$panels_data['widgets'][ $i ] = $widget;

	}

	$panels_data['grids']      = array_values( stripslashes_deep( isset( $form_post['grids'] ) ? $form_post['grids'] : array() ) );
	$panels_data['grid_cells'] = array_values( stripslashes_deep( isset( $form_post['grid_cells'] ) ? $form_post['grid_cells'] : array() ) );

	return apply_filters( 'siteorigin_panels_panels_data_from_post', $panels_data );
}