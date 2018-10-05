<?php

// eXtreme Styles mod cache. Generated on Mon, 24 Sep 2018 03:28:06 +0000 (time=1537759686)

?><!-- INCLUDEX overall_header.html -->

<form action="<?php echo isset($this->vars['S_LOGIN_ACTION']) ? $this->vars['S_LOGIN_ACTION'] : $this->lang('S_LOGIN_ACTION'); ?>" method="post" id="login" data-focus="<?php if ($this->vars['S_ADMIN_AUTH']) {  ?><?php echo isset($this->vars['PASSWORD_CREDENTIAL']) ? $this->vars['PASSWORD_CREDENTIAL'] : $this->lang('PASSWORD_CREDENTIAL'); ?><?php } else { ?><?php echo isset($this->vars['USERNAME_CREDENTIAL']) ? $this->vars['USERNAME_CREDENTIAL'] : $this->lang('USERNAME_CREDENTIAL'); ?><?php } ?>">
<div class="panel">
	<div class="inner">

	<div class="content">
		<h2 class="login-title"><?php if ($this->vars['LOGIN_EXPLAIN']) {  ?><?php echo isset($this->vars['LOGIN_EXPLAIN']) ? $this->vars['LOGIN_EXPLAIN'] : $this->lang('LOGIN_EXPLAIN'); ?><?php } else { ?><?php echo isset($this->vars['L_LOGIN']) ? $this->vars['L_LOGIN'] : $this->lang('L_LOGIN'); ?><?php } ?></h2>

		<fieldset <?php if (! $this->vars['S_CONFIRM_CODE']) {  ?>class="fields1"<?php } else { ?>class="fields2"<?php } ?>>
		<?php if ($this->vars['LOGIN_ERROR']) {  ?><div class="error"><?php echo isset($this->vars['LOGIN_ERROR']) ? $this->vars['LOGIN_ERROR'] : $this->lang('LOGIN_ERROR'); ?></div><?php } ?>
		<?php  $this->set_filename('xs_include_dd5c1780625b9ca32b4a71c38e19cc25', 'social_connect_link.html', true);  $this->pparse('xs_include_dd5c1780625b9ca32b4a71c38e19cc25');  ?>		
		<dl>
			<dt><label for="<?php echo isset($this->vars['USERNAME_CREDENTIAL']) ? $this->vars['USERNAME_CREDENTIAL'] : $this->lang('USERNAME_CREDENTIAL'); ?>"><?php echo isset($this->vars['L_USERNAME']) ? $this->vars['L_USERNAME'] : $this->lang('L_USERNAME'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
			<dd><input type="text" tabindex="1" name="<?php echo isset($this->vars['USERNAME_CREDENTIAL']) ? $this->vars['USERNAME_CREDENTIAL'] : $this->lang('USERNAME_CREDENTIAL'); ?>" id="<?php echo isset($this->vars['USERNAME_CREDENTIAL']) ? $this->vars['USERNAME_CREDENTIAL'] : $this->lang('USERNAME_CREDENTIAL'); ?>" size="25" value="<?php echo isset($this->vars['USERNAME']) ? $this->vars['USERNAME'] : $this->lang('USERNAME'); ?>" class="inputbox autowidth" /></dd>
		</dl>
		<dl>
			<dt><label for="<?php echo isset($this->vars['PASSWORD_CREDENTIAL']) ? $this->vars['PASSWORD_CREDENTIAL'] : $this->lang('PASSWORD_CREDENTIAL'); ?>"><?php echo isset($this->vars['L_PASSWORD']) ? $this->vars['L_PASSWORD'] : $this->lang('L_PASSWORD'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></label></dt>
			<dd><input type="password" tabindex="2" id="<?php echo isset($this->vars['PASSWORD_CREDENTIAL']) ? $this->vars['PASSWORD_CREDENTIAL'] : $this->lang('PASSWORD_CREDENTIAL'); ?>" name="<?php echo isset($this->vars['PASSWORD_CREDENTIAL']) ? $this->vars['PASSWORD_CREDENTIAL'] : $this->lang('PASSWORD_CREDENTIAL'); ?>" size="25" class="inputbox autowidth" autocomplete="off" /></dd>
			<?php if ($this->vars['S_DISPLAY_FULL_LOGIN'] && ( $this->vars['U_SEND_PASSWORD'] || $this->vars['U_RESEND_ACTIVATION'] )) {  ?>
				<?php if ($this->vars['U_SEND_PASSWORD']) {  ?><dd><a href="<?php echo isset($this->vars['U_SEND_PASSWORD']) ? $this->vars['U_SEND_PASSWORD'] : $this->lang('U_SEND_PASSWORD'); ?>"><?php echo isset($this->vars['L_FORGOT_PASS']) ? $this->vars['L_FORGOT_PASS'] : $this->lang('L_FORGOT_PASS'); ?></a></dd><?php } ?>
				<?php if ($this->vars['U_RESEND_ACTIVATION']) {  ?><dd><a href="<?php echo isset($this->vars['U_RESEND_ACTIVATION']) ? $this->vars['U_RESEND_ACTIVATION'] : $this->lang('U_RESEND_ACTIVATION'); ?>"><?php echo isset($this->vars['L_RESEND_ACTIVATION']) ? $this->vars['L_RESEND_ACTIVATION'] : $this->lang('L_RESEND_ACTIVATION'); ?></a></dd><?php } ?>
			<?php } ?>
		</dl>
		<?php if ($this->vars['CAPTCHA_TEMPLATE'] && $this->vars['S_CONFIRM_CODE']) {  ?>
			<?php $this->_tpldata['DEFINE']['.']['CAPTCHA_TAB_INDEX'] = 3; ?>
			<?php  $this->set_filename('xs_include_c14a568f57fac716de5bdd44bcd43458', '{CAPTCHA_TEMPLATE}', true);  $this->pparse('xs_include_c14a568f57fac716de5bdd44bcd43458');  ?>
		<?php } ?>
		<?php if ($this->vars['S_DISPLAY_FULL_LOGIN']) {  ?>
		<dl>
			<?php if ($this->vars['S_AUTOLOGIN_ENABLED']) {  ?><dd><label for="autologin"><input type="checkbox" name="autologin" id="autologin" tabindex="4" /> <?php echo isset($this->vars['L_LOG_ME_IN']) ? $this->vars['L_LOG_ME_IN'] : $this->lang('L_LOG_ME_IN'); ?></label></dd><?php } ?>
			<dd><label for="viewonline"><input type="checkbox" name="viewonline" id="viewonline" tabindex="5" /> <?php echo isset($this->vars['L_HIDE_ME']) ? $this->vars['L_HIDE_ME'] : $this->lang('L_HIDE_ME'); ?></label></dd>
		</dl>
		<?php } ?>

		<?php echo isset($this->vars['S_LOGIN_REDIRECT']) ? $this->vars['S_LOGIN_REDIRECT'] : $this->lang('S_LOGIN_REDIRECT'); ?>
		<dl>
			<dt>&nbsp;</dt>
			<dd><?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input type="submit" name="login" tabindex="6" value="<?php echo isset($this->vars['L_LOGIN']) ? $this->vars['L_LOGIN'] : $this->lang('L_LOGIN'); ?>" class="button1" /></dd>
		</dl>
		<?php  $this->set_filename('xs_include_11f20ff3dd22a5ae44ba51745c5891b3', 'social_connect.html', true);  $this->pparse('xs_include_11f20ff3dd22a5ae44ba51745c5891b3');  ?>			
		</fieldset>
	</div>

	<?php if (! $this->vars['S_ADMIN_AUTH'] && $this->vars['PROVIDER_TEMPLATE_FILE']) {  ?>
		<?php  $this->set_filename('xs_include_7de9011580d57c27aa1a8a40ff13a20d', '{PROVIDER_TEMPLATE_FILE}', true);  $this->pparse('xs_include_7de9011580d57c27aa1a8a40ff13a20d');  ?>
	<?php } ?>
	</div>
</div>


<?php if (! $this->vars['S_ADMIN_AUTH'] && $this->vars['S_REGISTER_ENABLED']) {  ?>
	<div class="panel">
		<div class="inner">

		<div class="content">
			<h3><?php echo isset($this->vars['L_REGISTER']) ? $this->vars['L_REGISTER'] : $this->lang('L_REGISTER'); ?></h3>
			<p><?php echo isset($this->vars['L_LOGIN_INFO']) ? $this->vars['L_LOGIN_INFO'] : $this->lang('L_LOGIN_INFO'); ?></p>
			<p><strong><a href="<?php echo isset($this->vars['U_TERMS_USE']) ? $this->vars['U_TERMS_USE'] : $this->lang('U_TERMS_USE'); ?>"><?php echo isset($this->vars['L_TERMS_USE']) ? $this->vars['L_TERMS_USE'] : $this->lang('L_TERMS_USE'); ?></a> | <a href="<?php echo isset($this->vars['U_PRIVACY']) ? $this->vars['U_PRIVACY'] : $this->lang('U_PRIVACY'); ?>"><?php echo isset($this->vars['L_PRIVACY']) ? $this->vars['L_PRIVACY'] : $this->lang('L_PRIVACY'); ?></a></strong></p>
			<hr class="dashed" />
			<p><a href="<?php echo isset($this->vars['U_REGISTER']) ? $this->vars['U_REGISTER'] : $this->lang('U_REGISTER'); ?>" class="button2"><?php echo isset($this->vars['L_REGISTER']) ? $this->vars['L_REGISTER'] : $this->lang('L_REGISTER'); ?></a></p>
		</div>

		</div>
	</div>
<?php } ?>

</form>

<!-- INCLUDEX overall_footer.html -->
