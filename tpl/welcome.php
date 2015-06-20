<?php
/**
 * Created by PhpStorm.
 * User: shramee
 * Date: 19/6/15
 * Time: 11:05 PM
 */
?>
<div class="wrap ppb-welcome about-wrap">

	<h1>Welcome to Pootle Page Builder</h1>

	<div class="about-text ppb-about-text">
		Thank you for using pootle page builder. We've worked day and night to make page builder great. If you love it &amp; want to let us know, Please rate it here. It will take you less than two minutes.<br>
		Thanks!
	</div>

	<div class="ppb-badge"><span class="dashicons dashicons-tagcloud"></span><br>Version <?php echo POOTLEPAGE_VERSION ?></div>

	<p class="ppb-actions">
		<a href="<?php echo admin_url( 'options-general.php?page=page_builder' ) ?>" class="button pootle">Settings</a>
		<a href="http://pootlepress.com/" class="button pootle">Docs</a>
		<a href="http://pootlepress.com/">Love Page Builder? Please rate it.</a>
	</p>

	<hr>
	<h4>How to use page builder</h4>

	<p>Page Builder is easy to use but you can check out the video below to get started.</p>

	<div class="ppb-video-container">
	<iframe src="https://www.youtube.com/embed/hahpE8b6fDI" frameborder="0" allowfullscreen></iframe>
	</div>

	<div class="changelog">
		<div class="feature-section col three-col">
			<div>
				<h4>Geo-locating Customer Location</h4>
				<p>We have added a new option to geolocate the "Default Customer Location". Coupled with ability to show taxes in your store based on this location, you can show relevant prices store-wide. Enable this in the <a href="http://wp/1/wp-admin/admin.php?page=ppb-settings&amp;tab=tax">settings</a>.</p>
			</div>
			<div>
				<h4>Color Customization</h4>
				<p>If you're looking to customise the look and feel of the frontend in 2.3, take a look at the free <a href="https://wordpress.org/plugins/ppb-colors/">WooCommerce Colors plugin</a>. This lets you change the colors with a live preview.</p>
			</div>
			<div class="last-feature">
				<h4>Improved Reports</h4>
				<p>Sales reports can now show net and gross amounts, we've added a print stylesheet, and added extra data on refunds to reports.</p>
			</div>
		</div>
	</div>

	<hr>

	<div class="return-to-dashboard">
		<a href="<?php echo admin_url( 'options-general.php?page=page_builder' ) ?>">Go to Page Builder Settings</a>
	</div>
</div>