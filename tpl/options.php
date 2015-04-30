<?php
do_action( 'wf_screen_get_header', 'woothemes', 'themes' );


$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
if ( empty( $tab ) ) {
	$tab = 'general';
}
?>


	<ul id="page-builder-sections" class="subsubsub">
		<li><a href="?page=page_builder&tab=general" class="tab <?php echo $tab == 'general' ? 'current' : '' ?> ">General</a> | </li>
		<li><a href="?page=page_builder&tab=display" class="tab <?php echo $tab == 'display' ? 'current' : '' ?> ">Display</a> | </li>
		<li><a href="?page=page_builder&tab=widgets" class="tab <?php echo $tab == 'widgets' ? 'current' : '' ?> ">Widget Selection</a> | </li>
		<li><a href="?page=page_builder&tab=styling" class="tab <?php echo $tab == 'styling' ? 'current' : '' ?> ">Widget Styling</a></li>
	</ul>

	<div class="clear"></div>

	<?php
	if ( $tab != 'styling' ) {

		?><form action='options.php' method="POST"><?php

		do_settings_sections( 'pootlepage-' . $tab );
		settings_fields( 'pootlepage-' . $tab );
		submit_button();

		?></form><?php
	} else {
		global $PP_PB_WF_Settings;
		$PP_PB_WF_Settings->settings_screen();
	}
	?>


<?php
do_action( 'wf_screen_get_footer', 'woothemes', 'themes' );
?>
