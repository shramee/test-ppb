<?php

$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
if ( empty( $tab ) ) {
	$tab = 'general';
}
?>

<div class="wrap">
	<h2>Pootle Page Builder</h2>
	<?php settings_errors(); ?>

	<h2 class="nav-tab-wrapper">
		<a href="?page=page_builder&tab=general" class="nav-tab <?php echo $tab == 'general' ? 'nav-tab-active' : '' ?> ">General</a>
		<a href="?page=page_builder&tab=display" class="nav-tab <?php echo $tab == 'display' ? 'nav-tab-active' : '' ?> ">Display</a>
		<a href="?page=page_builder&tab=widgets" class="nav-tab <?php echo $tab == 'widgets' ? 'nav-tab-active' : '' ?> ">Widget Selection</a>
		<a href="?page=page_builder&tab=styling" class="nav-tab <?php echo $tab == 'styling' ? 'nav-tab-active' : '' ?> ">Widget Styling</a>
	</h2>

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
</div>