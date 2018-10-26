<?php

// eXtreme Styles mod cache. Generated on Sun, 21 Oct 2018 17:21:38 +0000 (time=1540142498)

?>
<h1><?php echo isset($this->vars['L_CONFIGURATION_TITLE']) ? $this->vars['L_CONFIGURATION_TITLE'] : $this->lang('L_CONFIGURATION_TITLE'); ?></h1>

<p><?php echo isset($this->vars['L_CONFIGURATION_EXPLAIN']) ? $this->vars['L_CONFIGURATION_EXPLAIN'] : $this->lang('L_CONFIGURATION_EXPLAIN'); ?></p>

<form action="<?php echo isset($this->vars['S_CONFIG_ACTION']) ? $this->vars['S_CONFIG_ACTION'] : $this->lang('S_CONFIG_ACTION'); ?>" method="post">
<table width="100%" cellpadding="4" cellspacing="1" border="0" align="center">
	<tr>
		<td>
			<div id="admintabs">
				<ul>
				<li id="gen_sett_tab"><a href="javascript:selectPart('gen_sett')"><?php echo isset($this->vars['L_GENERAL_SETTINGS']) ? $this->vars['L_GENERAL_SETTINGS'] : $this->lang('L_GENERAL_SETTINGS'); ?></a></li>
				<li id="cookie_sett_tab"><a href="javascript:selectPart('cookie_sett')"><?php echo isset($this->vars['L_COOKIE_SETTINGS']) ? $this->vars['L_COOKIE_SETTINGS'] : $this->lang('L_COOKIE_SETTINGS'); ?></a></li>
				<li id="prv_msgs_tab"><a href="javascript:selectPart('prv_msgs')"><?php echo isset($this->vars['L_PRIVATE_MESSAGING']) ? $this->vars['L_PRIVATE_MESSAGING'] : $this->lang('L_PRIVATE_MESSAGING'); ?></a></li>
				<li id="ablts_sett_tab"><a href="javascript:selectPart('ablts_sett')"><?php echo isset($this->vars['L_ABILITIES_SETTINGS']) ? $this->vars['L_ABILITIES_SETTINGS'] : $this->lang('L_ABILITIES_SETTINGS'); ?></a></li>
				<li id="avtr_sett_tab"><a href="javascript:selectPart('avtr_sett')"><?php echo isset($this->vars['L_AVATAR_SETTINGS']) ? $this->vars['L_AVATAR_SETTINGS'] : $this->lang('L_AVATAR_SETTINGS'); ?></a></li>
				<li id="coppa_sett_tab"><a href="javascript:selectPart('coppa_sett')"><?php echo isset($this->vars['L_COPPA_SETTINGS']) ? $this->vars['L_COPPA_SETTINGS'] : $this->lang('L_COPPA_SETTINGS'); ?></a></li>
				<li id="email_sett_tab"><a href="javascript:selectPart('email_sett')"><?php echo isset($this->vars['L_EMAIL_SETTINGS']) ? $this->vars['L_EMAIL_SETTINGS'] : $this->lang('L_EMAIL_SETTINGS'); ?></a></li>
				</ul>
			</div>
		</td>
	</tr>
</table>
<fieldset id="gen_sett">
<table width="99%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
	<tr>
	  <th class="thHead" colspan="2"><?php echo isset($this->vars['L_GENERAL_SETTINGS']) ? $this->vars['L_GENERAL_SETTINGS'] : $this->lang('L_GENERAL_SETTINGS'); ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_SERVER_NAME']) ? $this->vars['L_SERVER_NAME'] : $this->lang('L_SERVER_NAME'); ?></td>
		<td class="row2"><input class="post" type="text" maxlength="255" size="40" name="server_name" value="<?php echo isset($this->vars['SERVER_NAME']) ? $this->vars['SERVER_NAME'] : $this->lang('SERVER_NAME'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_SERVER_PORT']) ? $this->vars['L_SERVER_PORT'] : $this->lang('L_SERVER_PORT'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_SERVER_PORT_EXPLAIN']) ? $this->vars['L_SERVER_PORT_EXPLAIN'] : $this->lang('L_SERVER_PORT_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="5" size="5" name="server_port" value="<?php echo isset($this->vars['SERVER_PORT']) ? $this->vars['SERVER_PORT'] : $this->lang('SERVER_PORT'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_SCRIPT_PATH']) ? $this->vars['L_SCRIPT_PATH'] : $this->lang('L_SCRIPT_PATH'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_SCRIPT_PATH_EXPLAIN']) ? $this->vars['L_SCRIPT_PATH_EXPLAIN'] : $this->lang('L_SCRIPT_PATH_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="255" name="script_path" value="<?php echo isset($this->vars['SCRIPT_PATH']) ? $this->vars['SCRIPT_PATH'] : $this->lang('SCRIPT_PATH'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_SITE_NAME']) ? $this->vars['L_SITE_NAME'] : $this->lang('L_SITE_NAME'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_SITE_NAME_EXPLAIN']) ? $this->vars['L_SITE_NAME_EXPLAIN'] : $this->lang('L_SITE_NAME_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" size="25" maxlength="100" name="sitename" value="<?php echo isset($this->vars['SITENAME']) ? $this->vars['SITENAME'] : $this->lang('SITENAME'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_SITE_DESCRIPTION']) ? $this->vars['L_SITE_DESCRIPTION'] : $this->lang('L_SITE_DESCRIPTION'); ?></td>
		<td class="row2"><input class="post" type="text" size="40" maxlength="255" name="site_desc" value="<?php echo isset($this->vars['SITE_DESCRIPTION']) ? $this->vars['SITE_DESCRIPTION'] : $this->lang('SITE_DESCRIPTION'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_DISABLE_BOARD']) ? $this->vars['L_DISABLE_BOARD'] : $this->lang('L_DISABLE_BOARD'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_DISABLE_BOARD_EXPLAIN']) ? $this->vars['L_DISABLE_BOARD_EXPLAIN'] : $this->lang('L_DISABLE_BOARD_EXPLAIN'); ?></span></td>
		<td class="row2"><input type="radio" name="board_disable" value="1" <?php echo isset($this->vars['S_DISABLE_BOARD_YES']) ? $this->vars['S_DISABLE_BOARD_YES'] : $this->lang('S_DISABLE_BOARD_YES'); ?> /> <?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp;&nbsp;<input type="radio" name="board_disable" value="0" <?php echo isset($this->vars['S_DISABLE_BOARD_NO']) ? $this->vars['S_DISABLE_BOARD_NO'] : $this->lang('S_DISABLE_BOARD_NO'); ?> /> <?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_ACCT_ACTIVATION']) ? $this->vars['L_ACCT_ACTIVATION'] : $this->lang('L_ACCT_ACTIVATION'); ?></td>
		<td class="row2"><input type="radio" name="require_activation" value="<?php echo isset($this->vars['ACTIVATION_NONE']) ? $this->vars['ACTIVATION_NONE'] : $this->lang('ACTIVATION_NONE'); ?>" <?php echo isset($this->vars['ACTIVATION_NONE_CHECKED']) ? $this->vars['ACTIVATION_NONE_CHECKED'] : $this->lang('ACTIVATION_NONE_CHECKED'); ?> /><?php echo isset($this->vars['L_NONE']) ? $this->vars['L_NONE'] : $this->lang('L_NONE'); ?>&nbsp; &nbsp;<input type="radio" name="require_activation" value="<?php echo isset($this->vars['ACTIVATION_USER']) ? $this->vars['ACTIVATION_USER'] : $this->lang('ACTIVATION_USER'); ?>" <?php echo isset($this->vars['ACTIVATION_USER_CHECKED']) ? $this->vars['ACTIVATION_USER_CHECKED'] : $this->lang('ACTIVATION_USER_CHECKED'); ?> /><?php echo isset($this->vars['L_USER']) ? $this->vars['L_USER'] : $this->lang('L_USER'); ?>&nbsp; &nbsp;<input type="radio" name="require_activation" value="<?php echo isset($this->vars['ACTIVATION_ADMIN']) ? $this->vars['ACTIVATION_ADMIN'] : $this->lang('ACTIVATION_ADMIN'); ?>" <?php echo isset($this->vars['ACTIVATION_ADMIN_CHECKED']) ? $this->vars['ACTIVATION_ADMIN_CHECKED'] : $this->lang('ACTIVATION_ADMIN_CHECKED'); ?> /><?php echo isset($this->vars['L_ADMIN']) ? $this->vars['L_ADMIN'] : $this->lang('L_ADMIN'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_VISUAL_CONFIRM']) ? $this->vars['L_VISUAL_CONFIRM'] : $this->lang('L_VISUAL_CONFIRM'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_VISUAL_CONFIRM_EXPLAIN']) ? $this->vars['L_VISUAL_CONFIRM_EXPLAIN'] : $this->lang('L_VISUAL_CONFIRM_EXPLAIN'); ?></span></td>
		<td class="row2"><input type="radio" name="enable_confirm" value="1" <?php echo isset($this->vars['CONFIRM_ENABLE']) ? $this->vars['CONFIRM_ENABLE'] : $this->lang('CONFIRM_ENABLE'); ?> /><?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp; &nbsp;<input type="radio" name="enable_confirm" value="0" <?php echo isset($this->vars['CONFIRM_DISABLE']) ? $this->vars['CONFIRM_DISABLE'] : $this->lang('CONFIRM_DISABLE'); ?> /><?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_ALLOW_AUTOLOGIN']) ? $this->vars['L_ALLOW_AUTOLOGIN'] : $this->lang('L_ALLOW_AUTOLOGIN'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_ALLOW_AUTOLOGIN_EXPLAIN']) ? $this->vars['L_ALLOW_AUTOLOGIN_EXPLAIN'] : $this->lang('L_ALLOW_AUTOLOGIN_EXPLAIN'); ?></span></td>
		<td class="row2"><input type="radio" name="allow_autologin" value="1" <?php echo isset($this->vars['ALLOW_AUTOLOGIN_YES']) ? $this->vars['ALLOW_AUTOLOGIN_YES'] : $this->lang('ALLOW_AUTOLOGIN_YES'); ?> /><?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp; &nbsp;<input type="radio" name="allow_autologin" value="0" <?php echo isset($this->vars['ALLOW_AUTOLOGIN_NO']) ? $this->vars['ALLOW_AUTOLOGIN_NO'] : $this->lang('ALLOW_AUTOLOGIN_NO'); ?> /><?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_AUTOLOGIN_TIME']) ? $this->vars['L_AUTOLOGIN_TIME'] : $this->lang('L_AUTOLOGIN_TIME'); ?> <br /><span class="gensmall"><?php echo isset($this->vars['L_AUTOLOGIN_TIME_EXPLAIN']) ? $this->vars['L_AUTOLOGIN_TIME_EXPLAIN'] : $this->lang('L_AUTOLOGIN_TIME_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="max_autologin_time" value="<?php echo isset($this->vars['AUTOLOGIN_TIME']) ? $this->vars['AUTOLOGIN_TIME'] : $this->lang('AUTOLOGIN_TIME'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_BOARD_EMAIL_FORM']) ? $this->vars['L_BOARD_EMAIL_FORM'] : $this->lang('L_BOARD_EMAIL_FORM'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_BOARD_EMAIL_FORM_EXPLAIN']) ? $this->vars['L_BOARD_EMAIL_FORM_EXPLAIN'] : $this->lang('L_BOARD_EMAIL_FORM_EXPLAIN'); ?></span></td>
		<td class="row2"><input type="radio" name="board_email_form" value="1" <?php echo isset($this->vars['BOARD_EMAIL_FORM_ENABLE']) ? $this->vars['BOARD_EMAIL_FORM_ENABLE'] : $this->lang('BOARD_EMAIL_FORM_ENABLE'); ?> /> <?php echo isset($this->vars['L_ENABLED']) ? $this->vars['L_ENABLED'] : $this->lang('L_ENABLED'); ?>&nbsp;&nbsp;<input type="radio" name="board_email_form" value="0" <?php echo isset($this->vars['BOARD_EMAIL_FORM_DISABLE']) ? $this->vars['BOARD_EMAIL_FORM_DISABLE'] : $this->lang('BOARD_EMAIL_FORM_DISABLE'); ?> /> <?php echo isset($this->vars['L_DISABLED']) ? $this->vars['L_DISABLED'] : $this->lang('L_DISABLED'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_FLOOD_INTERVAL']) ? $this->vars['L_FLOOD_INTERVAL'] : $this->lang('L_FLOOD_INTERVAL'); ?> <br /><span class="gensmall"><?php echo isset($this->vars['L_FLOOD_INTERVAL_EXPLAIN']) ? $this->vars['L_FLOOD_INTERVAL_EXPLAIN'] : $this->lang('L_FLOOD_INTERVAL_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="flood_interval" value="<?php echo isset($this->vars['FLOOD_INTERVAL']) ? $this->vars['FLOOD_INTERVAL'] : $this->lang('FLOOD_INTERVAL'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_SEARCH_FLOOD_INTERVAL']) ? $this->vars['L_SEARCH_FLOOD_INTERVAL'] : $this->lang('L_SEARCH_FLOOD_INTERVAL'); ?> <br /><span class="gensmall"><?php echo isset($this->vars['L_SEARCH_FLOOD_INTERVAL_EXPLAIN']) ? $this->vars['L_SEARCH_FLOOD_INTERVAL_EXPLAIN'] : $this->lang('L_SEARCH_FLOOD_INTERVAL_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="search_flood_interval" value="<?php echo isset($this->vars['SEARCH_FLOOD_INTERVAL']) ? $this->vars['SEARCH_FLOOD_INTERVAL'] : $this->lang('SEARCH_FLOOD_INTERVAL'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_MAX_LOGIN_ATTEMPTS']) ? $this->vars['L_MAX_LOGIN_ATTEMPTS'] : $this->lang('L_MAX_LOGIN_ATTEMPTS'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_MAX_LOGIN_ATTEMPTS_EXPLAIN']) ? $this->vars['L_MAX_LOGIN_ATTEMPTS_EXPLAIN'] : $this->lang('L_MAX_LOGIN_ATTEMPTS_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="max_login_attempts" value="<?php echo isset($this->vars['MAX_LOGIN_ATTEMPTS']) ? $this->vars['MAX_LOGIN_ATTEMPTS'] : $this->lang('MAX_LOGIN_ATTEMPTS'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_LOGIN_RESET_TIME']) ? $this->vars['L_LOGIN_RESET_TIME'] : $this->lang('L_LOGIN_RESET_TIME'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_LOGIN_RESET_TIME_EXPLAIN']) ? $this->vars['L_LOGIN_RESET_TIME_EXPLAIN'] : $this->lang('L_LOGIN_RESET_TIME_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="login_reset_time" value="<?php echo isset($this->vars['LOGIN_RESET_TIME']) ? $this->vars['LOGIN_RESET_TIME'] : $this->lang('LOGIN_RESET_TIME'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_TOPICS_PER_PAGE']) ? $this->vars['L_TOPICS_PER_PAGE'] : $this->lang('L_TOPICS_PER_PAGE'); ?></td>
		<td class="row2"><input class="post" type="text" name="topics_per_page" size="3" maxlength="4" value="<?php echo isset($this->vars['TOPICS_PER_PAGE']) ? $this->vars['TOPICS_PER_PAGE'] : $this->lang('TOPICS_PER_PAGE'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_POSTS_PER_PAGE']) ? $this->vars['L_POSTS_PER_PAGE'] : $this->lang('L_POSTS_PER_PAGE'); ?></td>
		<td class="row2"><input class="post" type="text" name="posts_per_page" size="3" maxlength="4" value="<?php echo isset($this->vars['POSTS_PER_PAGE']) ? $this->vars['POSTS_PER_PAGE'] : $this->lang('POSTS_PER_PAGE'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_HOT_THRESHOLD']) ? $this->vars['L_HOT_THRESHOLD'] : $this->lang('L_HOT_THRESHOLD'); ?></td>
		<td class="row2"><input class="post" type="text" name="hot_threshold" size="3" maxlength="4" value="<?php echo isset($this->vars['HOT_TOPIC']) ? $this->vars['HOT_TOPIC'] : $this->lang('HOT_TOPIC'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_DEFAULT_STYLE']) ? $this->vars['L_DEFAULT_STYLE'] : $this->lang('L_DEFAULT_STYLE'); ?></td>
		<td class="row2"><?php echo isset($this->vars['STYLE_SELECT']) ? $this->vars['STYLE_SELECT'] : $this->lang('STYLE_SELECT'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_OVERRIDE_STYLE']) ? $this->vars['L_OVERRIDE_STYLE'] : $this->lang('L_OVERRIDE_STYLE'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_OVERRIDE_STYLE_EXPLAIN']) ? $this->vars['L_OVERRIDE_STYLE_EXPLAIN'] : $this->lang('L_OVERRIDE_STYLE_EXPLAIN'); ?></span></td>
		<td class="row2"><input type="radio" name="override_user_style" value="1" <?php echo isset($this->vars['OVERRIDE_STYLE_YES']) ? $this->vars['OVERRIDE_STYLE_YES'] : $this->lang('OVERRIDE_STYLE_YES'); ?> /> <?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp;&nbsp;<input type="radio" name="override_user_style" value="0" <?php echo isset($this->vars['OVERRIDE_STYLE_NO']) ? $this->vars['OVERRIDE_STYLE_NO'] : $this->lang('OVERRIDE_STYLE_NO'); ?> /> <?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_DEFAULT_LANGUAGE']) ? $this->vars['L_DEFAULT_LANGUAGE'] : $this->lang('L_DEFAULT_LANGUAGE'); ?></td>
		<td class="row2"><?php echo isset($this->vars['LANG_SELECT']) ? $this->vars['LANG_SELECT'] : $this->lang('LANG_SELECT'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_DATE_FORMAT']) ? $this->vars['L_DATE_FORMAT'] : $this->lang('L_DATE_FORMAT'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_DATE_FORMAT_EXPLAIN']) ? $this->vars['L_DATE_FORMAT_EXPLAIN'] : $this->lang('L_DATE_FORMAT_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" name="default_dateformat" value="<?php echo isset($this->vars['DEFAULT_DATEFORMAT']) ? $this->vars['DEFAULT_DATEFORMAT'] : $this->lang('DEFAULT_DATEFORMAT'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_SYSTEM_TIMEZONE']) ? $this->vars['L_SYSTEM_TIMEZONE'] : $this->lang('L_SYSTEM_TIMEZONE'); ?></td>
		<td class="row2"><?php echo isset($this->vars['TIMEZONE_SELECT']) ? $this->vars['TIMEZONE_SELECT'] : $this->lang('TIMEZONE_SELECT'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_ENABLE_GZIP']) ? $this->vars['L_ENABLE_GZIP'] : $this->lang('L_ENABLE_GZIP'); ?></td>
		<td class="row2"><input type="radio" name="gzip_compress" value="1" <?php echo isset($this->vars['GZIP_YES']) ? $this->vars['GZIP_YES'] : $this->lang('GZIP_YES'); ?> /> <?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp;&nbsp;<input type="radio" name="gzip_compress" value="0" <?php echo isset($this->vars['GZIP_NO']) ? $this->vars['GZIP_NO'] : $this->lang('GZIP_NO'); ?> /> <?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_ENABLE_PRUNE']) ? $this->vars['L_ENABLE_PRUNE'] : $this->lang('L_ENABLE_PRUNE'); ?></td>
		<td class="row2"><input type="radio" name="prune_enable" value="1" <?php echo isset($this->vars['PRUNE_YES']) ? $this->vars['PRUNE_YES'] : $this->lang('PRUNE_YES'); ?> /> <?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp;&nbsp;<input type="radio" name="prune_enable" value="0" <?php echo isset($this->vars['PRUNE_NO']) ? $this->vars['PRUNE_NO'] : $this->lang('PRUNE_NO'); ?> /> <?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2" align="center"><?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input type="submit" name="submit" value="<?php echo isset($this->vars['L_SUBMIT']) ? $this->vars['L_SUBMIT'] : $this->lang('L_SUBMIT'); ?>" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="<?php echo isset($this->vars['L_RESET']) ? $this->vars['L_RESET'] : $this->lang('L_RESET'); ?>" class="liteoption" />
		</td>
	</tr>
</table>
</fieldset>
<fieldset id="cookie_sett">
<table width="100%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
	<tr>
		<th class="thHead" colspan="2"><?php echo isset($this->vars['L_COOKIE_SETTINGS']) ? $this->vars['L_COOKIE_SETTINGS'] : $this->lang('L_COOKIE_SETTINGS'); ?></th>
	</tr>
	<tr>
		<td class="row2" colspan="2"><span class="gensmall"><?php echo isset($this->vars['L_COOKIE_SETTINGS_EXPLAIN']) ? $this->vars['L_COOKIE_SETTINGS_EXPLAIN'] : $this->lang('L_COOKIE_SETTINGS_EXPLAIN'); ?></span></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_COOKIE_DOMAIN']) ? $this->vars['L_COOKIE_DOMAIN'] : $this->lang('L_COOKIE_DOMAIN'); ?></td>
		<td class="row2"><input class="post" type="text" maxlength="255" name="cookie_domain" value="<?php echo isset($this->vars['COOKIE_DOMAIN']) ? $this->vars['COOKIE_DOMAIN'] : $this->lang('COOKIE_DOMAIN'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_COOKIE_NAME']) ? $this->vars['L_COOKIE_NAME'] : $this->lang('L_COOKIE_NAME'); ?></td>
		<td class="row2"><input class="post" type="text" maxlength="16" name="cookie_name" value="<?php echo isset($this->vars['COOKIE_NAME']) ? $this->vars['COOKIE_NAME'] : $this->lang('COOKIE_NAME'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_COOKIE_PATH']) ? $this->vars['L_COOKIE_PATH'] : $this->lang('L_COOKIE_PATH'); ?></td>
		<td class="row2"><input class="post" type="text" maxlength="255" name="cookie_path" value="<?php echo isset($this->vars['COOKIE_PATH']) ? $this->vars['COOKIE_PATH'] : $this->lang('COOKIE_PATH'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_COOKIE_SECURE']) ? $this->vars['L_COOKIE_SECURE'] : $this->lang('L_COOKIE_SECURE'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_COOKIE_SECURE_EXPLAIN']) ? $this->vars['L_COOKIE_SECURE_EXPLAIN'] : $this->lang('L_COOKIE_SECURE_EXPLAIN'); ?></span></td>
		<td class="row2"><input type="radio" name="cookie_secure" value="0" <?php echo isset($this->vars['S_COOKIE_SECURE_DISABLED']) ? $this->vars['S_COOKIE_SECURE_DISABLED'] : $this->lang('S_COOKIE_SECURE_DISABLED'); ?> /><?php echo isset($this->vars['L_DISABLED']) ? $this->vars['L_DISABLED'] : $this->lang('L_DISABLED'); ?>&nbsp; &nbsp;<input type="radio" name="cookie_secure" value="1" <?php echo isset($this->vars['S_COOKIE_SECURE_ENABLED']) ? $this->vars['S_COOKIE_SECURE_ENABLED'] : $this->lang('S_COOKIE_SECURE_ENABLED'); ?> /><?php echo isset($this->vars['L_ENABLED']) ? $this->vars['L_ENABLED'] : $this->lang('L_ENABLED'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_SESSION_LENGTH']) ? $this->vars['L_SESSION_LENGTH'] : $this->lang('L_SESSION_LENGTH'); ?></td>
		<td class="row2"><input class="post" type="text" maxlength="5" size="5" name="session_length" value="<?php echo isset($this->vars['SESSION_LENGTH']) ? $this->vars['SESSION_LENGTH'] : $this->lang('SESSION_LENGTH'); ?>" /></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2" align="center"><?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input type="submit" name="submit" value="<?php echo isset($this->vars['L_SUBMIT']) ? $this->vars['L_SUBMIT'] : $this->lang('L_SUBMIT'); ?>" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="<?php echo isset($this->vars['L_RESET']) ? $this->vars['L_RESET'] : $this->lang('L_RESET'); ?>" class="liteoption" />
		</td>
	</tr>
</table>
</fieldset>
<fieldset id="prv_msgs">
<table width="100%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
	<tr>
		<th class="thHead" colspan="2"><?php echo isset($this->vars['L_PRIVATE_MESSAGING']) ? $this->vars['L_PRIVATE_MESSAGING'] : $this->lang('L_PRIVATE_MESSAGING'); ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_DISABLE_PRIVATE_MESSAGING']) ? $this->vars['L_DISABLE_PRIVATE_MESSAGING'] : $this->lang('L_DISABLE_PRIVATE_MESSAGING'); ?></td>
		<td class="row2"><input type="radio" name="privmsg_disable" value="0" <?php echo isset($this->vars['S_PRIVMSG_ENABLED']) ? $this->vars['S_PRIVMSG_ENABLED'] : $this->lang('S_PRIVMSG_ENABLED'); ?> /><?php echo isset($this->vars['L_ENABLED']) ? $this->vars['L_ENABLED'] : $this->lang('L_ENABLED'); ?>&nbsp; &nbsp;<input type="radio" name="privmsg_disable" value="1" <?php echo isset($this->vars['S_PRIVMSG_DISABLED']) ? $this->vars['S_PRIVMSG_DISABLED'] : $this->lang('S_PRIVMSG_DISABLED'); ?> /><?php echo isset($this->vars['L_DISABLED']) ? $this->vars['L_DISABLED'] : $this->lang('L_DISABLED'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_INBOX_LIMIT']) ? $this->vars['L_INBOX_LIMIT'] : $this->lang('L_INBOX_LIMIT'); ?></td>
		<td class="row2"><input class="post" type="text" maxlength="4" size="4" name="max_inbox_privmsgs" value="<?php echo isset($this->vars['INBOX_LIMIT']) ? $this->vars['INBOX_LIMIT'] : $this->lang('INBOX_LIMIT'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_SENTBOX_LIMIT']) ? $this->vars['L_SENTBOX_LIMIT'] : $this->lang('L_SENTBOX_LIMIT'); ?></td>
		<td class="row2"><input class="post" type="text" maxlength="4" size="4" name="max_sentbox_privmsgs" value="<?php echo isset($this->vars['SENTBOX_LIMIT']) ? $this->vars['SENTBOX_LIMIT'] : $this->lang('SENTBOX_LIMIT'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_SAVEBOX_LIMIT']) ? $this->vars['L_SAVEBOX_LIMIT'] : $this->lang('L_SAVEBOX_LIMIT'); ?></td>
		<td class="row2"><input class="post" type="text" maxlength="4" size="4" name="max_savebox_privmsgs" value="<?php echo isset($this->vars['SAVEBOX_LIMIT']) ? $this->vars['SAVEBOX_LIMIT'] : $this->lang('SAVEBOX_LIMIT'); ?>" /></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2" align="center"><?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input type="submit" name="submit" value="<?php echo isset($this->vars['L_SUBMIT']) ? $this->vars['L_SUBMIT'] : $this->lang('L_SUBMIT'); ?>" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="<?php echo isset($this->vars['L_RESET']) ? $this->vars['L_RESET'] : $this->lang('L_RESET'); ?>" class="liteoption" />
		</td>
	</tr>
</table>
</fieldset>
<fieldset id="ablts_sett">
<table width="100%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
	<tr>
	  <th class="thHead" colspan="2"><?php echo isset($this->vars['L_ABILITIES_SETTINGS']) ? $this->vars['L_ABILITIES_SETTINGS'] : $this->lang('L_ABILITIES_SETTINGS'); ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_MAX_POLL_OPTIONS']) ? $this->vars['L_MAX_POLL_OPTIONS'] : $this->lang('L_MAX_POLL_OPTIONS'); ?></td>
		<td class="row2"><input class="post" type="text" name="max_poll_options" size="4" maxlength="4" value="<?php echo isset($this->vars['MAX_POLL_OPTIONS']) ? $this->vars['MAX_POLL_OPTIONS'] : $this->lang('MAX_POLL_OPTIONS'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_ALLOW_HTML']) ? $this->vars['L_ALLOW_HTML'] : $this->lang('L_ALLOW_HTML'); ?></td>
		<td class="row2"><input type="radio" name="allow_html" value="1" <?php echo isset($this->vars['HTML_YES']) ? $this->vars['HTML_YES'] : $this->lang('HTML_YES'); ?> /> <?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp;&nbsp;<input type="radio" name="allow_html" value="0" <?php echo isset($this->vars['HTML_NO']) ? $this->vars['HTML_NO'] : $this->lang('HTML_NO'); ?> /> <?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_ALLOWED_TAGS']) ? $this->vars['L_ALLOWED_TAGS'] : $this->lang('L_ALLOWED_TAGS'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_ALLOWED_TAGS_EXPLAIN']) ? $this->vars['L_ALLOWED_TAGS_EXPLAIN'] : $this->lang('L_ALLOWED_TAGS_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" size="30" maxlength="255" name="allow_html_tags" value="<?php echo isset($this->vars['HTML_TAGS']) ? $this->vars['HTML_TAGS'] : $this->lang('HTML_TAGS'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_ALLOW_BBCODE']) ? $this->vars['L_ALLOW_BBCODE'] : $this->lang('L_ALLOW_BBCODE'); ?></td>
		<td class="row2"><input type="radio" name="allow_bbcode" value="1" <?php echo isset($this->vars['BBCODE_YES']) ? $this->vars['BBCODE_YES'] : $this->lang('BBCODE_YES'); ?> /> <?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp;&nbsp;<input type="radio" name="allow_bbcode" value="0" <?php echo isset($this->vars['BBCODE_NO']) ? $this->vars['BBCODE_NO'] : $this->lang('BBCODE_NO'); ?> /> <?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_ALLOW_SMILIES']) ? $this->vars['L_ALLOW_SMILIES'] : $this->lang('L_ALLOW_SMILIES'); ?></td>
		<td class="row2"><input type="radio" name="allow_smilies" value="1" <?php echo isset($this->vars['SMILE_YES']) ? $this->vars['SMILE_YES'] : $this->lang('SMILE_YES'); ?> /> <?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp;&nbsp;<input type="radio" name="allow_smilies" value="0" <?php echo isset($this->vars['SMILE_NO']) ? $this->vars['SMILE_NO'] : $this->lang('SMILE_NO'); ?> /> <?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_SMILIES_PATH']) ? $this->vars['L_SMILIES_PATH'] : $this->lang('L_SMILIES_PATH'); ?> <br /><span class="gensmall"><?php echo isset($this->vars['L_SMILIES_PATH_EXPLAIN']) ? $this->vars['L_SMILIES_PATH_EXPLAIN'] : $this->lang('L_SMILIES_PATH_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="smilies_path" value="<?php echo isset($this->vars['SMILIES_PATH']) ? $this->vars['SMILIES_PATH'] : $this->lang('SMILIES_PATH'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_ALLOW_SIG']) ? $this->vars['L_ALLOW_SIG'] : $this->lang('L_ALLOW_SIG'); ?></td>
		<td class="row2"><input type="radio" name="allow_sig" value="1" <?php echo isset($this->vars['SIG_YES']) ? $this->vars['SIG_YES'] : $this->lang('SIG_YES'); ?> /> <?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp;&nbsp;<input type="radio" name="allow_sig" value="0" <?php echo isset($this->vars['SIG_NO']) ? $this->vars['SIG_NO'] : $this->lang('SIG_NO'); ?> /> <?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_MAX_SIG_LENGTH']) ? $this->vars['L_MAX_SIG_LENGTH'] : $this->lang('L_MAX_SIG_LENGTH'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_MAX_SIG_LENGTH_EXPLAIN']) ? $this->vars['L_MAX_SIG_LENGTH_EXPLAIN'] : $this->lang('L_MAX_SIG_LENGTH_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" size="5" maxlength="4" name="max_sig_chars" value="<?php echo isset($this->vars['SIG_SIZE']) ? $this->vars['SIG_SIZE'] : $this->lang('SIG_SIZE'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_ALLOW_NAME_CHANGE']) ? $this->vars['L_ALLOW_NAME_CHANGE'] : $this->lang('L_ALLOW_NAME_CHANGE'); ?></td>
		<td class="row2"><input type="radio" name="allow_namechange" value="1" <?php echo isset($this->vars['NAMECHANGE_YES']) ? $this->vars['NAMECHANGE_YES'] : $this->lang('NAMECHANGE_YES'); ?> /> <?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp;&nbsp;<input type="radio" name="allow_namechange" value="0" <?php echo isset($this->vars['NAMECHANGE_NO']) ? $this->vars['NAMECHANGE_NO'] : $this->lang('NAMECHANGE_NO'); ?> /> <?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2" align="center"><?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input type="submit" name="submit" value="<?php echo isset($this->vars['L_SUBMIT']) ? $this->vars['L_SUBMIT'] : $this->lang('L_SUBMIT'); ?>" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="<?php echo isset($this->vars['L_RESET']) ? $this->vars['L_RESET'] : $this->lang('L_RESET'); ?>" class="liteoption" />
		</td>
	</tr>
</table>
</fieldset>
<fieldset id="avtr_sett">
<table width="100%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
	<tr>
	  <th class="thHead" colspan="2"><?php echo isset($this->vars['L_AVATAR_SETTINGS']) ? $this->vars['L_AVATAR_SETTINGS'] : $this->lang('L_AVATAR_SETTINGS'); ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_ALLOW_LOCAL']) ? $this->vars['L_ALLOW_LOCAL'] : $this->lang('L_ALLOW_LOCAL'); ?></td>
		<td class="row2"><input type="radio" name="allow_avatar_local" value="1" <?php echo isset($this->vars['AVATARS_LOCAL_YES']) ? $this->vars['AVATARS_LOCAL_YES'] : $this->lang('AVATARS_LOCAL_YES'); ?> /> <?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp;&nbsp;<input type="radio" name="allow_avatar_local" value="0" <?php echo isset($this->vars['AVATARS_LOCAL_NO']) ? $this->vars['AVATARS_LOCAL_NO'] : $this->lang('AVATARS_LOCAL_NO'); ?> /> <?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_ALLOW_REMOTE']) ? $this->vars['L_ALLOW_REMOTE'] : $this->lang('L_ALLOW_REMOTE'); ?> <br /><span class="gensmall"><?php echo isset($this->vars['L_ALLOW_REMOTE_EXPLAIN']) ? $this->vars['L_ALLOW_REMOTE_EXPLAIN'] : $this->lang('L_ALLOW_REMOTE_EXPLAIN'); ?></span></td>
		<td class="row2"><input type="radio" name="allow_avatar_remote" value="1" <?php echo isset($this->vars['AVATARS_REMOTE_YES']) ? $this->vars['AVATARS_REMOTE_YES'] : $this->lang('AVATARS_REMOTE_YES'); ?> /> <?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp;&nbsp;<input type="radio" name="allow_avatar_remote" value="0" <?php echo isset($this->vars['AVATARS_REMOTE_NO']) ? $this->vars['AVATARS_REMOTE_NO'] : $this->lang('AVATARS_REMOTE_NO'); ?> /> <?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_ALLOW_UPLOAD']) ? $this->vars['L_ALLOW_UPLOAD'] : $this->lang('L_ALLOW_UPLOAD'); ?></td>
		<td class="row2"><input type="radio" name="allow_avatar_upload" value="1" <?php echo isset($this->vars['AVATARS_UPLOAD_YES']) ? $this->vars['AVATARS_UPLOAD_YES'] : $this->lang('AVATARS_UPLOAD_YES'); ?> /> <?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp;&nbsp;<input type="radio" name="allow_avatar_upload" value="0" <?php echo isset($this->vars['AVATARS_UPLOAD_NO']) ? $this->vars['AVATARS_UPLOAD_NO'] : $this->lang('AVATARS_UPLOAD_NO'); ?> /> <?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_MAX_FILESIZE']) ? $this->vars['L_MAX_FILESIZE'] : $this->lang('L_MAX_FILESIZE'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_MAX_FILESIZE_EXPLAIN']) ? $this->vars['L_MAX_FILESIZE_EXPLAIN'] : $this->lang('L_MAX_FILESIZE_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="10" name="avatar_filesize" value="<?php echo isset($this->vars['AVATAR_FILESIZE']) ? $this->vars['AVATAR_FILESIZE'] : $this->lang('AVATAR_FILESIZE'); ?>" /> Bytes</td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_MAX_AVATAR_SIZE']) ? $this->vars['L_MAX_AVATAR_SIZE'] : $this->lang('L_MAX_AVATAR_SIZE'); ?> <br />
			<span class="gensmall"><?php echo isset($this->vars['L_MAX_AVATAR_SIZE_EXPLAIN']) ? $this->vars['L_MAX_AVATAR_SIZE_EXPLAIN'] : $this->lang('L_MAX_AVATAR_SIZE_EXPLAIN'); ?></span>
		</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="avatar_max_height" value="<?php echo isset($this->vars['AVATAR_MAX_HEIGHT']) ? $this->vars['AVATAR_MAX_HEIGHT'] : $this->lang('AVATAR_MAX_HEIGHT'); ?>" /> x <input class="post" type="text" size="3" maxlength="4" name="avatar_max_width" value="<?php echo isset($this->vars['AVATAR_MAX_WIDTH']) ? $this->vars['AVATAR_MAX_WIDTH'] : $this->lang('AVATAR_MAX_WIDTH'); ?>"></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_AVATAR_STORAGE_PATH']) ? $this->vars['L_AVATAR_STORAGE_PATH'] : $this->lang('L_AVATAR_STORAGE_PATH'); ?> <br /><span class="gensmall"><?php echo isset($this->vars['L_AVATAR_STORAGE_PATH_EXPLAIN']) ? $this->vars['L_AVATAR_STORAGE_PATH_EXPLAIN'] : $this->lang('L_AVATAR_STORAGE_PATH_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="avatar_path" value="<?php echo isset($this->vars['AVATAR_PATH']) ? $this->vars['AVATAR_PATH'] : $this->lang('AVATAR_PATH'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_AVATAR_GALLERY_PATH']) ? $this->vars['L_AVATAR_GALLERY_PATH'] : $this->lang('L_AVATAR_GALLERY_PATH'); ?> <br /><span class="gensmall"><?php echo isset($this->vars['L_AVATAR_GALLERY_PATH_EXPLAIN']) ? $this->vars['L_AVATAR_GALLERY_PATH_EXPLAIN'] : $this->lang('L_AVATAR_GALLERY_PATH_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="avatar_gallery_path" value="<?php echo isset($this->vars['AVATAR_GALLERY_PATH']) ? $this->vars['AVATAR_GALLERY_PATH'] : $this->lang('AVATAR_GALLERY_PATH'); ?>" /></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2" align="center"><?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input type="submit" name="submit" value="<?php echo isset($this->vars['L_SUBMIT']) ? $this->vars['L_SUBMIT'] : $this->lang('L_SUBMIT'); ?>" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="<?php echo isset($this->vars['L_RESET']) ? $this->vars['L_RESET'] : $this->lang('L_RESET'); ?>" class="liteoption" />
		</td>
	</tr>
</table>
</fieldset>
<fieldset id="coppa_sett">
<table width="100%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
	<tr>
	  <th class="thHead" colspan="2"><?php echo isset($this->vars['L_COPPA_SETTINGS']) ? $this->vars['L_COPPA_SETTINGS'] : $this->lang('L_COPPA_SETTINGS'); ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_COPPA_FAX']) ? $this->vars['L_COPPA_FAX'] : $this->lang('L_COPPA_FAX'); ?></td>
		<td class="row2"><input class="post" type="text" size="25" maxlength="100" name="coppa_fax" value="<?php echo isset($this->vars['COPPA_FAX']) ? $this->vars['COPPA_FAX'] : $this->lang('COPPA_FAX'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_COPPA_MAIL']) ? $this->vars['L_COPPA_MAIL'] : $this->lang('L_COPPA_MAIL'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_COPPA_MAIL_EXPLAIN']) ? $this->vars['L_COPPA_MAIL_EXPLAIN'] : $this->lang('L_COPPA_MAIL_EXPLAIN'); ?></span></td>
		<td class="row2"><textarea name="coppa_mail" rows="5" cols="30"><?php echo isset($this->vars['COPPA_MAIL']) ? $this->vars['COPPA_MAIL'] : $this->lang('COPPA_MAIL'); ?></textarea></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2" align="center"><?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input type="submit" name="submit" value="<?php echo isset($this->vars['L_SUBMIT']) ? $this->vars['L_SUBMIT'] : $this->lang('L_SUBMIT'); ?>" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="<?php echo isset($this->vars['L_RESET']) ? $this->vars['L_RESET'] : $this->lang('L_RESET'); ?>" class="liteoption" />
		</td>
	</tr>
</table>
</fieldset>
<fieldset id="email_sett">
<table width="100%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
	<tr>
	  <th class="thHead" colspan="2"><?php echo isset($this->vars['L_EMAIL_SETTINGS']) ? $this->vars['L_EMAIL_SETTINGS'] : $this->lang('L_EMAIL_SETTINGS'); ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_ADMIN_EMAIL']) ? $this->vars['L_ADMIN_EMAIL'] : $this->lang('L_ADMIN_EMAIL'); ?></td>
		<td class="row2"><input class="post" type="text" size="25" maxlength="100" name="board_email" value="<?php echo isset($this->vars['EMAIL_FROM']) ? $this->vars['EMAIL_FROM'] : $this->lang('EMAIL_FROM'); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_EMAIL_SIG']) ? $this->vars['L_EMAIL_SIG'] : $this->lang('L_EMAIL_SIG'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_EMAIL_SIG_EXPLAIN']) ? $this->vars['L_EMAIL_SIG_EXPLAIN'] : $this->lang('L_EMAIL_SIG_EXPLAIN'); ?></span></td>
		<td class="row2"><textarea name="board_email_sig" rows="5" cols="30"><?php echo isset($this->vars['EMAIL_SIG']) ? $this->vars['EMAIL_SIG'] : $this->lang('EMAIL_SIG'); ?></textarea></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_USE_SMTP']) ? $this->vars['L_USE_SMTP'] : $this->lang('L_USE_SMTP'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_USE_SMTP_EXPLAIN']) ? $this->vars['L_USE_SMTP_EXPLAIN'] : $this->lang('L_USE_SMTP_EXPLAIN'); ?></span></td>
		<td class="row2"><input type="radio" name="smtp_delivery" value="1" <?php echo isset($this->vars['SMTP_YES']) ? $this->vars['SMTP_YES'] : $this->lang('SMTP_YES'); ?> /> <?php echo isset($this->vars['L_YES']) ? $this->vars['L_YES'] : $this->lang('L_YES'); ?>&nbsp;&nbsp;<input type="radio" name="smtp_delivery" value="0" <?php echo isset($this->vars['SMTP_NO']) ? $this->vars['SMTP_NO'] : $this->lang('SMTP_NO'); ?> /> <?php echo isset($this->vars['L_NO']) ? $this->vars['L_NO'] : $this->lang('L_NO'); ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_SMTP_SERVER']) ? $this->vars['L_SMTP_SERVER'] : $this->lang('L_SMTP_SERVER'); ?></td>
		<td class="row2"><input class="post" type="text" name="smtp_host" value="<?php echo isset($this->vars['SMTP_HOST']) ? $this->vars['SMTP_HOST'] : $this->lang('SMTP_HOST'); ?>" size="25" maxlength="50" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_SMTP_USERNAME']) ? $this->vars['L_SMTP_USERNAME'] : $this->lang('L_SMTP_USERNAME'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_SMTP_USERNAME_EXPLAIN']) ? $this->vars['L_SMTP_USERNAME_EXPLAIN'] : $this->lang('L_SMTP_USERNAME_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="text" name="smtp_username" value="<?php echo isset($this->vars['SMTP_USERNAME']) ? $this->vars['SMTP_USERNAME'] : $this->lang('SMTP_USERNAME'); ?>" size="25" maxlength="255" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo isset($this->vars['L_SMTP_PASSWORD']) ? $this->vars['L_SMTP_PASSWORD'] : $this->lang('L_SMTP_PASSWORD'); ?><br /><span class="gensmall"><?php echo isset($this->vars['L_SMTP_PASSWORD_EXPLAIN']) ? $this->vars['L_SMTP_PASSWORD_EXPLAIN'] : $this->lang('L_SMTP_PASSWORD_EXPLAIN'); ?></span></td>
		<td class="row2"><input class="post" type="password" name="smtp_password" value="<?php echo isset($this->vars['SMTP_PASSWORD']) ? $this->vars['SMTP_PASSWORD'] : $this->lang('SMTP_PASSWORD'); ?>" size="25" maxlength="255" /></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2" align="center"><?php echo isset($this->vars['S_HIDDEN_FIELDS']) ? $this->vars['S_HIDDEN_FIELDS'] : $this->lang('S_HIDDEN_FIELDS'); ?><input type="submit" name="submit" value="<?php echo isset($this->vars['L_SUBMIT']) ? $this->vars['L_SUBMIT'] : $this->lang('L_SUBMIT'); ?>" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="<?php echo isset($this->vars['L_RESET']) ? $this->vars['L_RESET'] : $this->lang('L_RESET'); ?>" class="liteoption" />
		</td>
	</tr>
</table>
</form>
</fieldset>
<br clear="all" />
<script type="text/javascript">
<!--
function admingetObj(obj)
{
	return ( document.getElementById ? document.getElementById(obj) : ( document.all ? document.all[obj] : null ) );
}
function adminsetNone(part)
{
	admingetObj(part + '_tab').className = '';
	admingetObj(part).style.display = 'none';

}
function adminsetBlock(part)
{
	admingetObj(part + '_tab').className = 'activetab';
	admingetObj(part).style.display = 'block';

}
function selectPart(part)
{
	adminsetNone('gen_sett');
	adminsetNone('cookie_sett');
	adminsetNone('prv_msgs');
	adminsetNone('ablts_sett');
	adminsetNone('avtr_sett');
	adminsetNone('coppa_sett');
	adminsetNone('email_sett');

	adminsetBlock(part);
}

selectPart('gen_sett');

// -->
</script>
