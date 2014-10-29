<?php
global $wp_widget_factory;
$layouts = apply_filters('siteorigin_panels_prebuilt_layouts', array());
?>

<div id="panels" data-animations="<?php echo siteorigin_panels_setting('animations') ? 'true' : 'false' ?>">

	<?php do_action('siteorigin_panels_before_interface') ?>

	<div id="panels-container">
	</div>
	
	<div id="add-to-panels">

        <button class="grid-add add-button button"><?php _e('Add Row', 'siteorigin-panels') ?></button>

		<button class="panels-add add-button button"><?php _e('Add Widget', 'siteorigin-panels') ?></button>
		<?php if(!empty($layouts)) : ?>
			<button class="prebuilt-set add-button button"><?php _e('Add Layout', 'siteorigin-panels') ?></button>
		<?php endif; ?>

<!--        <button class="page-settings button">Page Settings</button>-->
<!--        <button class="hide-elements button">Hide Elements</button>-->
		<div class="clear"></div>
	</div>
	
	<?php // The add new widget dialog ?>
	
	<div id="panels-dialog" data-title="<?php esc_attr_e('Add New Widget','siteorigin-panels') ?>" class="panels-admin-dialog">
		<div id="panels-dialog-inner">
			<div class="panels-text-filter">
				<input type="search" class="widefat" placeholder="Filter" id="panels-text-filter-input" />
			</div>

			<ul class="panel-type-list">

                <?php
                $widgetSettings = get_option('pootlepage-widgets', array());
                if (!is_array($widgetSettings)) {
                    $widgetSettings = array();
                }
                if (!isset($widgetSettings['reorder-widgets'])) {
                    $widgetSettings['reorder-widgets'] = '[]';
                }
                if (!isset($widgetSettings['unused-widgets'])) {
                    $widgetSettings['unused-widgets'] = '[]';
                }

                $widgetSettings['reorder-widgets'] = json_decode($widgetSettings['reorder-widgets'], true);
                $widgetSettings['unused-widgets'] = json_decode($widgetSettings['unused-widgets'], true);

                if (count($widgetSettings['reorder-widgets']) == 0 &&
                    count($widgetSettings['unused-widgets']) == 0
                ) {
                    $widgetSettings['reorder-widgets'] = array('Pootle_Text_Widget',
                        'SiteOrigin_Panels_Widgets_PostLoop', 'Woo_Widget_Component');

                    foreach ($wp_widget_factory->widgets as $class => $widget_obj) {
                        if (!in_array($class, $widgetSettings['reorder-widgets'])) {
                            $widgetSettings['unused-widgets'][] = $class;
                        }
                    }

                    $usedSequence = $widgetSettings['reorder-widgets'];
                    $unusedSequence = $widgetSettings['unused-widgets'];
                } else {

                    $usedSequence = $widgetSettings['reorder-widgets'];
                    $unusedSequence = $widgetSettings['unused-widgets'];

                    foreach ($wp_widget_factory->widgets as $class => $widget_obj) {
                        if (!in_array($class, $widgetSettings['reorder-widgets']) && !in_array($class, $widgetSettings['unused-widgets'])) {
                            $usedSequence[] = $class;
                        }
                    }

                    // make visual editor as first one
                    if (in_array('Pootle_Text_Widget', $usedSequence)) {
                        $temp = array();
                        $temp[] = 'Pootle_Text_Widget';
                        foreach ($usedSequence as $class) {
                            if ($class != 'Pootle_Text_Widget') {
                                $temp[] = $class;
                            }
                        }

                        $usedSequence = $temp;
                    }
                }

                ?>
                <?php

                foreach ($usedSequence as $class) :
                    if (!isset($wp_widget_factory->widgets[$class])) {
                        continue;
                    }
                    $widget_obj = $wp_widget_factory->widgets[$class];

				?>
					<li class="panel-type"
						data-class="<?php echo esc_attr($class) ?>"
						data-title="<?php echo esc_attr($widget_obj->name) ?>"
						>
						<div class="panel-type-wrapper">
							<h3><?php echo esc_html($widget_obj->name) ?></h3>
							<?php if(!empty($widget_obj->widget_options['description'])) : ?>
								<small class="description"><?php echo esc_html($widget_obj->widget_options['description']) ?></small>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>

                <?php
                foreach ($unusedSequence as $class) :
                    if (!isset($wp_widget_factory->widgets[$class])) {
                        continue;
                    }
                $widget_obj = $wp_widget_factory->widgets[$class];

                ?>
                <li class="panel-type unused"
                    data-class="<?php echo esc_attr($class) ?>"
                    data-title="<?php echo esc_attr($widget_obj->name) ?>"
                    >
                    <div class="panel-type-wrapper">
                        <h3><?php echo esc_html($widget_obj->name) ?></h3>
                        <?php if(!empty($widget_obj->widget_options['description'])) : ?>
                            <small class="description"><?php echo esc_html($widget_obj->widget_options['description']) ?></small>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>

				<div class="clear"></div>

			</ul>
            <div class='help-text'>To include more widgets for selection please go to Settings > Page Builder > Widgets and drag widgets into the selection area</div>
			<?php do_action('siteorigin_panels_after_widgets'); ?>
		</div>
		
	</div>

	<?php // The add row dialog ?>
	
	<div id="grid-add-dialog" data-title="<?php esc_attr_e('Add Row','siteorigin-panels') ?>" class="panels-admin-dialog">
		<p><label><strong><?php _e('Columns', 'siteorigin-panels') ?></strong></label></p>
		<p><input type="text" id="grid-add-dialog-input" name="column_count" class="small-text" value="3" /></p>
	</div>

    <div id="remove-row-dialog" data-title="<?php esc_attr_e("Remove Row", 'siteorigin-panels') ?>" class="panels-admin-dialog">
        <p>Are you sure?</p>
    </div>

    <div id="remove-widget-dialog" data-title="<?php esc_attr_e("Remove Widget", 'siteorigin-panels') ?>" class="panels-admin-dialog">
        <p>Are you sure?</p>
    </div>

    <div id="page-setting-dialog" data-title="<?php esc_attr_e('Page Settings', 'siteorigin-panels') ?>" class="panels-admin-dialog">

        <?php
            $pageSettingsFields = pootlepage_page_settings_fields();
            pootlepage_dialog_form_echo($pageSettingsFields);
        ?>

    </div>

    <div id="hide-element-dialog" data-title="<?php esc_attr_e('Hide Elements', 'siteorigin-panels') ?>" class="panels-admin-dialog">

        <?php
            $hideElementsFields = pootlepage_hide_elements_fields();
            pootlepage_hide_elements_dialog_echo($hideElementsFields);
        ?>

    </div>

	<?php // The layouts dialog ?>

	<?php if(!empty($layouts)) : ?>
		<div id="grid-prebuilt-dialog" data-title="<?php esc_attr_e('Insert Prebuilt Page Layout','siteorigin-panels') ?>" class="panels-admin-dialog">
			<p><label><strong><?php _e('Page Layout', 'siteorigin-panels') ?></strong></label></p>
			<p>
				<select type="text" id="grid-prebuilt-input" name="prebuilt_layout" style="width:580px;" placeholder="<?php esc_attr_e('Select Layout', 'siteorigin-panels') ?>" >
					<option class="empty" <?php selected(true) ?> value=""></option>
					<?php foreach($layouts as $id => $data) : ?>
						<option id="panel-prebuilt-<?php echo esc_attr($id) ?>" data-layout-id="<?php echo esc_attr($id) ?>" class="prebuilt-layout">
							<?php echo isset($data['name']) ? $data['name'] : __('Untitled Layout', 'siteorigin-panels') ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>
		</div>
	<?php endif; ?>

	<?php // The styles dialog ?>
	<div id="grid-styles-dialog" data-title="<?php esc_attr_e('Row Visual Style','siteorigin-panels') ?>" class="panels-admin-dialog">
		<?php siteorigin_panels_style_dialog_form() ?>
	</div>

    <div id="widget-styles-dialog" data-title="Style Widget" class="panels-admin-dialog">
        <?php pp_pb_widget_styles_dialog_form() ?>
    </div>

    <?php
        global $post;
        $pageSettings = get_post_meta($post->ID, 'pootlepage-page-settings', true);
        if (empty($pageSettings)) {
            $pageSettings = '{}';
        }

        $hideElements = get_post_meta($post->ID, 'pootlepage-hide-elements', true);
        if (empty($hideElements)) {
            $hideElements = '{}';
        }
    ?>
    <input type="hidden" id="page-settings" name="page-settings" value="<?php esc_attr_e($pageSettings) ?>" />
    <input type="hidden" id="hide-elements" name="hide-elements" value="<?php esc_attr_e($hideElements) ?>" />

	<?php wp_nonce_field('save', '_sopanels_nonce') ?>
	<?php do_action('siteorigin_panels_metabox_end'); ?>
</div>