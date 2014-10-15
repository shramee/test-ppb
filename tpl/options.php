<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2><?php _e('Page Builder for WooThemes Canvas', 'siteorigin-panels') ?></h2>

	<form action="options.php" method="POST">

        <h2 class="nav-tab-wrapper">
            <a href="?page=page_builder&tab=general" class="nav-tab">General</a>
            <a href="?page=page_builder&tab=widgets" class="nav-tab">Widgets</a>
<!--            <a href="?page=page_builder&tab=styling" class="nav-tab">Styling</a>-->
            <a href="?page=page_builder&tab=display" class="nav-tab">Display</a>
        </h2>

        <?php

            $tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
            if (empty($tab)) {
                $tab = 'general';
            }


            do_settings_sections( 'pootlepage-' . $tab );
            settings_fields( 'pootlepage-' . $tab);
            submit_button();
        ?>

	</form>
</div>