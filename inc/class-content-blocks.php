<?php
/**
 * Created by PhpStorm.
 * User: shramee
 * Date: 25/6/15
 * Time: 11:29 PM
 */

/**
 * Appends and prepends the contents of paragraphs with line breaks
 *
 * @param $text
 * @since 3.0.0
 * @return mixed
 */
function ppb_content_block_format_p( $text ) {

	return str_replace(
		array( '<p>', '</p>', ), array( "<p>\n", "\n</p>",
	), $text
	);
}
add_filter( 'ppb_content_block', 'ppb_content_block_format_p', 8 );

add_filter( 'ppb_content_block', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );

/**
 * Opens the content block container with styles and classes
 *
 * @param $block_info
 * @param $gi
 * @param $ci
 * @param $pi
 * @param $blocks_num
 * @param $post_id
 */
function ppb_panels_render_content_block_container_open( $block_info, $gi, $ci, $pi, $blocks_num, $post_id ) {

	$styleArray  = $widgetStyle = isset( $block_info['info']['style'] ) ? json_decode( $block_info['info']['style'], true ) : ppb_default_content_block_style();;

	//Classes for this content block
	$classes = array( 'panel' );
	if ( 0 == $pi ) {
		$classes[] = 'panel-first-child';
	}
	if ( ( $blocks_num - 1 ) == $pi ) {
		$classes[] = 'panel-last-child';
	}

	//Id for this content block
	$id = 'panel-' . $post_id . '-' . $gi . '-' . $ci . '-' . $pi;

	$inlineStyle = '';

	$widgetStyleFields = pp_pb_widget_styling_fields();

	$styleWithSelector = '';

	foreach ( $widgetStyleFields as $key => $field ) {
		if ( $field['type'] == 'border' ) {
			// a border field has 2 settings
			$key1 = $key . '-width';
			$key2 = $key . '-color';

			if ( isset( $styleArray[ $key1 ] ) && $styleArray[ $key1 ] != '' ) {
				if ( ! is_array( $field['css'] ) ) {
					$cssArr = array( $field['css'] );
				} else {
					$cssArr = $field['css'];
				}

				foreach ( $cssArr as $cssProperty ) {
					$inlineStyle .= $cssProperty . '-width: ' . $styleArray[ $key1 ] . 'px; border-style: solid;';
				}
			}

			if ( isset( $styleArray[ $key2 ] ) && $styleArray[ $key2 ] != '' ) {
				if ( ! is_array( $field['css'] ) ) {
					$cssArr = array( $field['css'] );
				} else {
					$cssArr = $field['css'];
				}

				foreach ( $cssArr as $cssProperty ) {
					$inlineStyle .= $cssProperty . '-color: ' . $styleArray[ $key2 ] . ';';
				}
			}

		} elseif ( $key == 'inline-css' ) {

			if ( ! empty( $styleArray[ $key ] ) ) {
				$inlineStyle .= $styleArray[ $key ];
			}

		} else {


			if ( isset( $styleArray[ $key ] ) && $styleArray[ $key ] != '' ) {
				if ( ! is_array( $field['css'] ) ) {
					$cssArr = array( $field['css'] );
				} else {
					$cssArr = $field['css'];
				}

				foreach ( $cssArr as $cssProperty ) {
					if ( isset( $field['unit'] ) ) {
						$unit = $field['unit'];
					} else {
						$unit = '';
					}

					if ( ! isset( $field['selector'] ) ) {
						$inlineStyle .= $cssProperty . ': ' . $styleArray[ $key ] . $unit . ';';
					} else {
						$styleWithSelector .= '#' . $id . ' > ' . $field['selector'] . ' { ' . $cssProperty . ': ' . $styleArray[ $key ] . $unit . '; }';
					}
				}
			}
		}
	}

	if ( $styleWithSelector != '' ) {
		echo "<style>\n";
		echo str_replace( 'display', 'display:none;display', $styleWithSelector );
		echo "</style>\n";
	}

	echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" id="' . $id . '" style="' . $inlineStyle . '" >';
}
add_action( 'ppb_panels_render_content_block', 'ppb_panels_render_content_block_container_open', 5, 6 );

/**
 * Closes the content block container
 */
function ppb_panels_render_content_block_container_close(){
	echo '</div>';
}
add_action( 'ppb_panels_render_content_block', 'ppb_panels_render_content_block_container_close', 25 );

/**
 * Render the Content Panel.
 *
 * @param string $widget_info The widget class name.
 */
function ppb_panels_render_content_block( $block_info ) {
	if ( ! empty( $block_info['text'] ) ) echo apply_filters( 'ppb_content_block', $block_info['text'] );
}
add_action( 'ppb_panels_render_content_block', 'ppb_panels_render_content_block' );

/**
 * Print inline CSS in the header and footer.
 */
function siteorigin_panels_print_inline_css() {
	global $ppb_panels_inline_css;

	if ( ! empty( $ppb_panels_inline_css ) ) {
		?>
		<!----------Pootle Page Builder Inline Styles---------->
		<style type="text/css" media="all"><?php echo $ppb_panels_inline_css ?></style><?php
	}

	$ppb_panels_inline_css = '';
}
add_action( 'wp_head', 'siteorigin_panels_print_inline_css', 12 );
add_action( 'wp_footer', 'siteorigin_panels_print_inline_css' );

/**
 * Output TMCE Editor
 * @param $request
 */
function ppb_panels_editor( $request ) {

	$text = '';

	if ( ! empty( $request['instance'] ) ) {
		$instance = json_decode( $request['instance'] );
		if ( ! empty( $instance->text ) )
			$text = $instance->text;
	}

	wp_editor( $text, 'ppbeditor', array(
		'textarea_name'  => 'widgets[{$id}][text]',
		'default_editor' => 'tmce',
		'tinymce' => array(
			'force_p_newlines' => false,
		)
	) );
}
add_action( 'ppb_content_block_editor_form', 'ppb_panels_editor' );

/**
 * Display a widget form with the provided data
 * @param array|null $request Request data ($_POST/$_GET)
 */
function ppb_print_editor_panel( $request = null ) {

	?>
	<div class="ppb-cool-panel-wrap">
		<ul class="ppb-acp-sidebar">

			<li>
				<a class="ppb-tabs-anchors ppb-block-anchor ppb-editor" <?php selected( true ) ?> href="#pootle-editor-tab">
					<?php echo apply_filters( 'ppb_content_block_editor_title', 'Editor', $request ); ?>
				</a>
			</li>

			<?php if ( class_exists( 'WooCommerce' ) ) { ?>
				<li><a class="ppb-tabs-anchors" href="#pootle-wc-tab">WooCommerce</a></li>
			<?php } ?>

			<li class="ppb-seperator"></li>

			<li><a class="ppb-tabs-anchors" href="#pootle-style-tab">Style</a></li>

			<li><a class="ppb-tabs-anchors" href="#pootle-advanced-tab">Advanced</a></li>
		</ul>

		<?php ?>
		<div id="pootle-editor-tab" class="pootle-content-module tab-contents content-block">

			<?php echo do_action( 'ppb_content_block_editor_form', $request ); ?>

		</div>

		<div id="pootle-style-tab" class="pootle-style-fields pootle-content-module tab-contents">
			<?php
			pp_pb_widget_styles_dialog_form();
			?>
		</div>

		<div id="pootle-advanced-tab" class="pootle-style-fields pootle-content-module tab-contents">
			<?php
			pp_pb_widget_styles_dialog_form( 'inline-css' );
			?>
		</div>

		<?php if ( class_exists( 'WooCommerce' ) ) { ?>
			<div id="pootle-wc-tab" class="pootle-content-module tab-contents">
				<?php do_action( 'ppb_add_content_woocommerce_tab' ); ?>
			</div>
		<?php } ?>

	</div>
<?php

}

function ppb_panels_ajax_widget_form(){

	$request = array_map( 'stripslashes_deep', $_REQUEST );

	ppb_print_editor_panel( $request );

	exit();
}
add_action( 'wp_ajax_ppb_panels_editor_form', 'ppb_panels_ajax_widget_form' );

function ppb_woocommerce_tab() {
	?>
	Using WooCommerce? You can now build a stunning shop with Page Builder. Just get our WooCommerce extension and start building!
<?php
}
add_action( 'ppb_add_content_woocommerce_tab', 'ppb_woocommerce_tab' );
