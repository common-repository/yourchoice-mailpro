<div class="wrap">
	<h2><?php echo __('YourChoice MailPro', 'yourchoice-mailpro') . ' ' . __('Settings', 'yourchoice-mailpro'); ?></h2>
    <a href="options-general.php?page=mailpro&amp;basic=1"><?php echo __('edit basic settings', 'yourchoice-mailpro'); ?></a>&nbsp;&nbsp;&nbsp;
    <a href="options-general.php?page=mailpro&amp;main=1"><?php echo __('edit main settings', 'yourchoice-mailpro'); ?></a>&nbsp;&nbsp;&nbsp;
    <strong><a href="options-general.php?page=mailpro&amp;recaptcha=1"><?php echo __('edit Google reCAPTCHA v2 settings', 'yourchoice-mailpro'); ?></a></strong>
    <form action="options.php" method="post">
		<?php settings_fields('ycmp_options'); ?>
		<?php do_settings_sections('ycmprecaptcha'); ?>
        <p><?php echo __('Add <a href="https://developers.google.com/recaptcha/intro" target="blank">Google reCAPTCHA v2</a> to verify if user is human. To activate it please enter your <a href="https://www.google.com/recaptcha/admin" target="blank">API keys</a>.', 'yourchoice-mailpro'); ?></p>
		<input name="Submit" type="submit" value="<?php esc_attr_e(__('Save Changes', 'yourchoice-mailpro')); ?>" />
	</form>
</div>
