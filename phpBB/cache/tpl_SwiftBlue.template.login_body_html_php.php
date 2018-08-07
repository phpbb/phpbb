<?php

// eXtreme Styles mod cache. Generated on Tue, 07 Aug 2018 01:36:35 +0000 (time=1533605795)

?><!-- INCLUDEX overall_header.html -->

<form action="<?php echo isset($this->vars['S_LOGIN_ACTION']) ? $this->vars['S_LOGIN_ACTION'] : $this->lang('S_LOGIN_ACTION'); ?>" method="post">

<table class="tablebg" width="100--" cellspacing="1">
<tr>
	<?php if (! $this->vars['S_ADMIN_AUTH']) {  ?>
		<th colspan="2"><?php echo isset($this->vars['L_LOGIN']) ? $this->vars['L_LOGIN'] : $this->lang('L_LOGIN'); ?></th>
	<?php } else { ?>
		<th><?php echo isset($this->vars['LOGIN_EXPLAIN']) ? $this->vars['LOGIN_EXPLAIN'] : $this->lang('LOGIN_EXPLAIN'); ?></th>
	<?php } ?>
</tr>
<?php if ($this->vars['LOGIN_EXPLAIN'] && ! $this->vars['S_ADMIN_AUTH']) {  ?>
	<tr>
		<td class="row3" colspan="2" align="center"><span class="genmed"><?php echo isset($this->vars['LOGIN_EXPLAIN']) ? $this->vars['LOGIN_EXPLAIN'] : $this->lang('LOGIN_EXPLAIN'); ?></span></td>
	</tr>
<?php } ?>
<tr><?php if (! $this->vars['S_ADMIN_AUTH'] && $this->vars['S_REGISTER_ENABLED']) {  ?>
	<td class="row1" width="50--">
		<p class="genmed"><?php echo isset($this->vars['L_LOGIN_INFO']) ? $this->vars['L_LOGIN_INFO'] : $this->lang('L_LOGIN_INFO'); ?></p>

		<p class="genmed" align="center">
			<a href="<?php echo isset($this->vars['U_TERMS_USE']) ? $this->vars['U_TERMS_USE'] : $this->lang('U_TERMS_USE'); ?>"><?php echo isset($this->vars['L_TERMS_USE']) ? $this->vars['L_TERMS_USE'] : $this->lang('L_TERMS_USE'); ?></a> | <a href="<?php echo isset($this->vars['U_PRIVACY']) ? $this->vars['U_PRIVACY'] : $this->lang('U_PRIVACY'); ?>"><?php echo isset($this->vars['L_PRIVACY']) ? $this->vars['L_PRIVACY'] : $this->lang('L_PRIVACY'); ?></a>
		</p>
	</td>
	<?php } ?>
	<td class="row2">
	
		<table align="center" cellspacing="1" cellpadding="4" style="width: 100--;">
		<?php if ($this->vars['LOGIN_ERROR']) {  ?>
			<tr>
				<td class="genmed" colspan="2" align="center"><span class="error"><?php echo isset($this->vars['LOGIN_ERROR']) ? $this->vars['LOGIN_ERROR'] : $this->lang('LOGIN_ERROR'); ?></span></td>
			</tr>
		<?php } ?>

		<tr>
			<td valign="top" <?php if ($this->vars['S_ADMIN_AUTH'] || ! $this->vars['S_REGISTER_ENABLED']) {  ?>style="width: 50--; text-align: <?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>;"<?php } ?>><b class="genmed"><?php echo isset($this->vars['L_USERNAME']) ? $this->vars['L_USERNAME'] : $this->lang('L_USERNAME'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></b></td>
			<td><input class="post" type="text" name="<?php echo isset($this->vars['USERNAME_CREDENTIAL']) ? $this->vars['USERNAME_CREDENTIAL'] : $this->lang('USERNAME_CREDENTIAL'); ?>" size="25" value="<?php echo isset($this->vars['USERNAME']) ? $this->vars['USERNAME'] : $this->lang('USERNAME'); ?>" tabindex="1" />
				<?php if (! $this->vars['S_ADMIN_AUTH'] && $this->vars['S_REGISTER_ENABLED']) {  ?>
					<br /><a class="genmed" href="<?php echo isset($this->vars['U_REGISTER']) ? $this->vars['U_REGISTER'] : $this->lang('U_REGISTER'); ?>"><?php echo isset($this->vars['L_REGISTER']) ? $this->vars['L_REGISTER'] : $this->lang('L_REGISTER'); ?></a>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<td valign="top" <?php if ($this->vars['S_ADMIN_AUTH'] || ! $this->vars['S_REGISTER_ENABLED']) {  ?>style="width: 50--; text-align: <?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>;"<?php } ?>><b class="genmed"><?php echo isset($this->vars['L_PASSWORD']) ? $this->vars['L_PASSWORD'] : $this->lang('L_PASSWORD'); ?><?php echo isset($this->vars['L_COLON']) ? $this->vars['L_COLON'] : $this->lang('L_COLON'); ?></b></td>
			<td>
				<input class="post" type="password" name="<?php echo isset($this->vars['PASSWORD_CREDENTIAL']) ? $this->vars['PASSWORD_CREDENTIAL'] : $this->lang('PASSWORD_CREDENTIAL'); ?>" size="25" tabindex="2" autocomplete="off" />
				<?php if ($this->vars['U_SEND_PASSWORD']) {  ?><br /><a class="genmed" href="<?php echo isset($this->vars['U_SEND_PASSWORD']) ? $this->vars['U_SEND_PASSWORD'] : $this->lang('U_SEND_PASSWORD'); ?>"><?php echo isset($this->vars['L_FORGOT_PASS']) ? $this->vars['L_FORGOT_PASS'] : $this->lang('L_FORGOT_PASS'); ?></a><?php } ?>
				<?php if ($this->vars['U_RESEND_ACTIVATION'] && ! $this->vars['S_ADMIN_AUTH']) {  ?><br /><a class="genmed" href="<?php echo isset($this->vars['U_RESEND_ACTIVATION']) ? $this->vars['U_RESEND_ACTIVATION'] : $this->lang('U_RESEND_ACTIVATION'); ?>"><?php echo isset($this->vars['L_RESEND_ACTIVATION']) ? $this->vars['L_RESEND_ACTIVATION'] : $this->lang('L_RESEND_ACTIVATION'); ?></a><?php } ?>
			</td>
		</tr>
		<?php if ($this->vars['S_DISPLAY_FULL_LOGIN']) {  ?>
			<?php if ($this->vars['S_AUTOLOGIN_ENABLED']) {  ?>
			<tr>
				<td>&nbsp;</td>
				<td><input type="checkbox" class="radio" name="autologin" tabindex="3" /> <span class="genmed"><?php echo isset($this->vars['L_LOG_ME_IN']) ? $this->vars['L_LOG_ME_IN'] : $this->lang('L_LOG_ME_IN'); ?></span></td>
			</tr>
			<?php } ?>
			<tr>
				<td>&nbsp;</td>
				<td><input type="checkbox" class="radio" name="viewonline" tabindex="4" /> <span class="genmed"><?php echo isset($this->vars['L_HIDE_ME']) ? $this->vars['L_HIDE_ME'] : $this->lang('L_HIDE_ME'); ?></span></td>
			</tr>
		<?php } ?>
		<?php if (! $this->vars['S_ADMIN_AUTH'] && $this->vars['PROVIDER_TEMPLATE_FILE']) {  ?>
			<?php  $this->set_filename('xs_include_864869eadaa80946c86b996475e44a94', '{PROVIDER_TEMPLATE_FILE}', true);  $this->pparse('xs_include_864869eadaa80946c86b996475e44a94');  ?>
		<?php } ?>
		</table>
	</td>
</tr>

<?php if ($this->vars['CAPTCHA_TEMPLATE'] && $this->vars['S_CONFIRM_CODE']) {  ?>
</table>
<table class="tablebg" width="100--" cellspacing="1">
	<?php $this->_tpldata['DEFINE']['.']['CAPTCHA_TAB_INDEX'] = 4; ?>
	<?php  $this->set_filename('xs_include_7979b70d953ab5d2e0d4183ca7487b8e', '{CAPTCHA_TEMPLATE}', true);  $this->pparse('xs_include_7979b70d953ab5d2e0d4183ca7487b8e');  ?>
<?php } ?>

<?php echo isset($this->vars['S_LOGIN_REDIRECT']) ? $this->vars['S_LOGIN_REDIRECT'] : $this->lang('S_LOGIN_REDIRECT'); ?>
<tr>
	<td class="cat" <?php if (! $this->vars['S_ADMIN_AUTH'] || $this->vars['S_CONFIRM_CODE']) {  ?>colspan="2"<?php } ?> align="center"><?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input type="submit" name="login" class="btnmain" value="<?php echo isset($this->vars['L_LOGIN']) ? $this->vars['L_LOGIN'] : $this->lang('L_LOGIN'); ?>" tabindex="5" /></td>
</tr>
</table>
<?php echo isset($this->vars['S_FORM_TOKEN']) ? $this->vars['S_FORM_TOKEN'] : $this->lang('S_FORM_TOKEN'); ?>
</form>

<br clear="all" />

<?php  $this->set_filename('xs_include_f9442f2528153a7568967c1645f8bf87', 'breadcrumbs.html', true);  $this->pparse('xs_include_f9442f2528153a7568967c1645f8bf87');  ?>

<br clear="all" />

<div align="<?php echo isset($this->vars['S_CONTENT_FLOW_END']) ? $this->vars['S_CONTENT_FLOW_END'] : $this->lang('S_CONTENT_FLOW_END'); ?>"><?php  $this->set_filename('xs_include_49dd3b558401163889abe2ddfd3f7e91', 'jumpbox.html', true);  $this->pparse('xs_include_49dd3b558401163889abe2ddfd3f7e91');  ?></div>

<script type="text/javascript">if (document.getElementsByName)	{ 
// <![CDATA[
	(function()
	{
		var elements = document.getElementsByName("<?php if ($this->vars['S_ADMIN_AUTH']) {  ?><?php echo isset($this->vars['PASSWORD_CREDENTIAL']) ? $this->vars['PASSWORD_CREDENTIAL'] : $this->lang('PASSWORD_CREDENTIAL'); ?><?php } else { ?><?php echo isset($this->vars['USERNAME_CREDENTIAL']) ? $this->vars['USERNAME_CREDENTIAL'] : $this->lang('USERNAME_CREDENTIAL'); ?><?php } ?>");
		for (var i = 0; i < elements.length; ++i)
		{
			if (elements[i].tagName.toLowerCase() == 'input')
			{
				elements[i].focus();
				break;
			}
		}
	})();
// ]]>
 } else if (document.all) { document.all("<?php if ($this->vars['S_ADMIN_AUTH']) {  ?><?php echo isset($this->vars['PASSWORD_CREDENTIAL']) ? $this->vars['PASSWORD_CREDENTIAL'] : $this->lang('PASSWORD_CREDENTIAL'); ?><?php } else { ?><?php echo isset($this->vars['USERNAME_CREDENTIAL']) ? $this->vars['USERNAME_CREDENTIAL'] : $this->lang('USERNAME_CREDENTIAL'); ?><?php } ?>").focus(); }</script>

<!-- INCLUDEX overall_footer.html -->
