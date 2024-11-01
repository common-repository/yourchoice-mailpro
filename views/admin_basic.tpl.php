<div class="wrap">
	<h2><?php echo __('YourChoice MailPro', 'yourchoice-mailpro') . ' ' . __('Settings', 'yourchoice-mailpro'); ?></h2>
    <strong><a href="options-general.php?page=mailpro&amp;basic=1"><?php echo __('edit basic settings', 'yourchoice-mailpro'); ?></a></strong>&nbsp;&nbsp;&nbsp;
    <a href="options-general.php?page=mailpro&amp;main=1"><?php echo __('edit main settings', 'yourchoice-mailpro'); ?></a>&nbsp;&nbsp;&nbsp;
    <a href="options-general.php?page=mailpro&amp;recaptcha=1"><?php echo __('edit Google reCAPTCHA v2 settings', 'yourchoice-mailpro'); ?></a>
	<?php if($api_error) { ?>
		<div align="center"><?php echo __('API Error! Please check your APIKey and IDClient', 'yourchoice-mailpro'); ?></div>
	<?php } ?>
	<form action="options.php" method="post">
		<?php settings_fields('ycmp_options'); ?>
		<?php do_settings_sections('ycmpbasic'); ?>
		<input name="Submit" type="submit" value="<?php esc_attr_e(__('Save Changes', 'yourchoice-mailpro')); ?>" />
	</form>
</div>
