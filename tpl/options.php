<?php

$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'display';
if ( ! in_array( $tab, array( 'display', 'styling', ) ) ) {
	$tab = 'display';
}
?>

<div class="wrap">
	<h2>Pootle Page Builder</h2>
	<?php settings_errors(); ?>
<!--
	<h2 class="nav-tab-wrapper">
		<a href="?page=page_builder&tab=display" class="nav-tab <?php echo $tab == 'display' ? 'nav-tab-active' : '' ?> ">Display</a>
		<a href="?page=page_builder&tab=styling" class="nav-tab <?php echo $tab == 'styling' ? 'nav-tab-active' : '' ?> ">Widget Styling</a>
	</h2>

	<div class="clear"></div>
	<div class="pootle-settings-page">
-->
	<form action='options.php' method="POST"><?php

		do_settings_sections( 'pootlepage-' . $tab );
		settings_fields( 'pootlepage-' . $tab );
		submit_button();

		?></form>
<!--
	</div>
</div>
-->