<?php
global $wp_widget_factory;
$layouts = apply_filters( 'siteorigin_panels_prebuilt_layouts', array() );
?>

<div id="panels" data-animations="true">

	<?php do_action( 'siteorigin_panels_before_interface' ) ?>

	<div id="panels-container">
	</div>

	<div id="add-to-panels">

		<button class="grid-add add-button ed_button button button-small"><?php _e( 'Add Row', 'ppb-panels' ) ?></button>

		<?php if ( ! empty( $layouts ) ) : ?>
			<button class="prebuilt-set add-button ed_button button button-small"><?php _e( 'Use Existing Page Layout', 'ppb-panels' ) ?></button>
		<?php endif; ?>

		<div class="clear"></div>
	</div>

	<?php // The add row dialog ?>
	<div id="grid-add-dialog" data-title="<?php esc_attr_e( 'Add Row', 'ppb-panels' ) ?>"
	     class="panels-admin-dialog" style="text-align: center">
		<p>
			<label>
				<strong>
					<?php _e( 'How many columns do you want your row to have? ', 'ppb-panels' ) ?><br>
					(<?php _e( 'You can adjust the widths later', 'ppb-panels' ) ?>)
				</strong>
			</label>
		</p>
		<p><input type="number" id="grid-add-dialog-input" name="column_count" class="small-text" value="3"/></p>
	</div>

		<?php

		//Get Current User
		$current_user = wp_get_current_user();

		//Get first name if set
		$username = '';
		if ( ! empty( $current_user->user_firstname ) ) {
			$username = " {$current_user->user_firstname}";
		}

		//Get user's visit count
		$visit_count = get_user_meta( $current_user->ID, 'ppb-visit-count', true );

		//Set welcome message
		if ( empty( $visit_count ) ) {

			$visit_count = 0;
			$message = "Welcome to Page Builder{$username}! Click the 'Add Row' button above to start building your page.";

		} elseif ( 1 == $visit_count ) {
			$message = "Welcome back to Page Builder{$username}! You can now also use existing pages as a template to start your page and save you time!";
		} else {
			$message = "Welcome to Page Builder{$username}! You know what to do.";
		}

		//Print the message
		echo "<div id='ppb-hello-user' class='visit-count-{$visit_count}'> $message </div>";

		//Update user visit count
		$visit_count++;
		global $pagenow;
		if ( 'post-new.php' == $pagenow ){
			update_user_meta( $current_user->ID, 'ppb-visit-count', $visit_count );
		}

		?>

	<div id="remove-row-dialog" data-title="<?php esc_attr_e( "Remove Row", 'ppb-panels' ) ?>"
	     class="panels-admin-dialog">
		<p>Are you sure?</p>
	</div>

	<div id="remove-widget-dialog" data-title="<?php esc_attr_e( "Delete Content", 'ppb-panels' ) ?>"
	     class="panels-admin-dialog">
		<p>Are you sure?</p>
	</div>

	<div id="page-setting-dialog" data-title="<?php esc_attr_e( 'Page Settings', 'ppb-panels' ) ?>"
	     class="panels-admin-dialog">

		<?php
		$pageSettingsFields = pootlepage_page_settings_fields();
		pootlepage_dialog_form_echo( $pageSettingsFields );
		?>

	</div>

	<div id="hide-element-dialog" data-title="<?php esc_attr_e( 'Hide Elements', 'ppb-panels' ) ?>"
	     class="panels-admin-dialog">

		<?php
		$hideElementsFields = pootlepage_hide_elements_fields();
		pootlepage_hide_elements_dialog_echo( $hideElementsFields );
		?>

	</div>

	<?php // The layouts dialog ?>

	<?php if ( ! empty( $layouts ) ) : ?>
		<div id="grid-prebuilt-dialog"
		     data-title="<?php esc_attr_e( 'Use Existing Page Layout', 'ppb-panels' ) ?>"
		     class="panels-admin-dialog">
			<p><label><strong><?php _e( 'Page Layout', 'ppb-panels' ) ?></strong></label></p>

			<p>
				<select type="text" id="grid-prebuilt-input" name="prebuilt_layout" style="width:580px;"
				        placeholder="<?php esc_attr_e( 'Select Layout', 'ppb-panels' ) ?>">
					<option class="empty" <?php selected( true ) ?> value=""></option>
					<?php foreach ( $layouts as $id => $data ) : ?>
						<option id="panel-prebuilt-<?php echo esc_attr( $id ) ?>"
						        data-layout-id="<?php echo esc_attr( $id ) ?>" class="prebuilt-layout">
							<?php echo isset( $data['name'] ) ? $data['name'] : __( 'Untitled Layout', 'ppb-panels' ) ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>
		</div>
	<?php endif; ?>

	<?php // The styles dialog ?>
	<div id="grid-styles-dialog" data-title="<?php esc_attr_e( 'Row Visual Style', 'ppb-panels' ) ?>"
	     class="panels-admin-dialog">
		<div class="ppb-cool-panel-wrap">

			<?php siteorigin_panels_style_dialog_form() ?>

		</div>
	</div>

	<div id="widget-styles-dialog" data-title="Style Widget" class="panels-admin-dialog">
		<?php pp_pb_widget_styles_dialog_form() ?>
	</div>

	<div id="content-loss-dialog" data-title="<?php esc_attr_e( 'Changing to Page Builder', 'ppb-panels' ) ?>"
	     data-button-i-know="<?php esc_attr_e( "I know what I'm doing", 'ppb-panels' ) ?>"
	     data-button-stop="<?php esc_attr_e( "Yep, I'll stop and create a new page", 'ppb-panels' ) ?>"
	     class="panels-admin-dialog">
		<p>
			<?php _e( 'Slow down tiger! Do you realise that changing to Page Builder for this page will make all your page content disappear forever?', 'ppb-panels' ) ?>
			<br><br>
			<?php _e( 'Why not create a new page instead?', 'ppb-panels' ) ?>
		</p>
	</div>

	<div id="layout-loss-dialog"
	     data-title="<?php esc_attr_e( 'Changing to the default editor', 'ppb-panels' ) ?>"
	     data-button-i-know="<?php esc_attr_e( "I know what I'm doing", 'ppb-panels' ) ?>"
	     data-button-stop="<?php esc_attr_e( "I love Page Builder, keep me here", 'ppb-panels' ) ?>"
	     class="panels-admin-dialog">
		<p>
			<?php _e( "Ummm... if you go back to the default editor you'll loose all your content. Are you sure you want to loose all that hard work you've done?", 'ppb-panels' ) ?>
		</p>
	</div>

	<?php
	global $post;
	$pageSettings = get_post_meta( $post->ID, 'pootlepage-page-settings', true );
	if ( empty( $pageSettings ) ) {
		$pageSettings = '{}';
	}

	$hideElements = get_post_meta( $post->ID, 'pootlepage-hide-elements', true );
	if ( empty( $hideElements ) ) {
		$hideElements = '{}';
	}
	?>
	<input type="hidden" id="page-settings" name="page-settings" value="<?php esc_attr_e( $pageSettings ) ?>"/>
	<input type="hidden" id="hide-elements" name="hide-elements" value="<?php esc_attr_e( $hideElements ) ?>"/>

	<div class="ppb-hidden-editor-container" style="display:none;">
		<?php
		$request = null;
		require POOTLEPAGE_DIR . 'tpl/content-block-panel.php';
		?>
	</div>

	<?php wp_nonce_field( 'save', '_sopanels_nonce' ) ?>
	<?php do_action( 'siteorigin_panels_metabox_end' ); ?>
</div>
<?php
if ( 'pootle' == filter_input( INPUT_GET, 'page_builder' ) || 1 == $visit_count ) {
?>
	<style>#wp-content-editor-container{display:none;}</style>
	<script>
		jQuery(document).ready(function($){
			$('#content-panels').click();
		});
	</script>
<?php
}
?>