<?php
/***************************************************************************
 *                              admin_board.php
 *                            -------------------
 *   begin                : Thursday, Jul 12, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if (!empty($setmodules))
{
	$file = basename(__FILE__);
	$module['GENERAL']['COOKIE_SETTINGS'] = ($auth->acl_get('a_cookies')) ? "$file$SID&amp;mode=cookie" : '';
	$module['GENERAL']['BOARD_DEFAULTS'] = ($auth->acl_get('a_defaults')) ? "$file$SID&amp;mode=default" : '';
	$module['GENERAL']['BOARD_SETTINGS'] = ($auth->acl_get('a_board')) ? "$file$SID&amp;mode=setting" : '';
	$module['GENERAL']['AVATAR_SETTINGS'] = ($auth->acl_get('a_board')) ? "$file$SID&amp;mode=avatar" : '';
	$module['GENERAL']['EMAIL_SETTINGS'] = ($auth->acl_get('a_server')) ? "$file$SID&amp;mode=email" : '';
	$module['GENERAL']['SERVER_SETTINGS'] = ($auth->acl_get('a_server')) ? "$file$SID&amp;mode=server" : '';
	$module['GENERAL']['AUTH_SETTINGS'] = ($auth->acl_get('a_server')) ? "$file$SID&amp;mode=auth" : '';
	$module['GENERAL']['LOAD_SETTINGS'] = ($auth->acl_get('a_server')) ? "$file$SID&amp;mode=load" : '';
	return;
}

define('IN_PHPBB', 1);
// Load default header
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

// Get mode
$mode = (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : '';

// Check permissions/set title
switch ($mode)
{
	case 'cookie':
		$l_title = 'COOKIE_SETTINGS';
		$which_auth = 'a_cookies';
		break;
	case 'default':
		$l_title = 'BOARD_DEFAULTS';
		$which_auth = 'a_defaults';
		break;
	case 'avatar':
		$l_title = 'AVATAR_SETTINGS';
		$which_auth = 'a_board';
		break;
	case 'setting':
		$l_title = 'BOARD_SETTINGS';
		$which_auth = 'a_board';
		break;
	case 'email':
		$l_title = 'EMAIL_SETTINGS';
		$which_auth = 'a_server';
		break;
	case 'server':
		$l_title = 'SERVER_SETTINGS';
		$which_auth = 'a_server';
		break;
	case 'load':
		$l_title = 'LOAD_SETTINGS';
		$which_auth = 'a_server';
		break;
	case 'auth':
		$l_title = 'AUTH_SETTINGS';
		$which_auth = 'a_server';
		break;
	default:
		return;
}

// Check permissions
if (!$auth->acl_get($which_auth))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Pull all config data
$sql = "SELECT *
	FROM " . CONFIG_TABLE;
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$config_name = $row['config_name'];
	$config_value = $row['config_value'];

	$default_config[$config_name] = $config_value;
	$new[$config_name] = (isset($_POST[$config_name])) ? $_POST[$config_name] : $default_config[$config_name];

	if (isset($_POST['submit']))
	{
		set_config($config_name, stripslashes($new[$config_name]));
	}
}

if (isset($_POST['submit']))
{
	add_log('admin', 'LOG_' . strtoupper($mode) . '_CONFIG');
	trigger_error($user->lang['Config_updated']);
}

adm_page_header($user->lang[$l_title]);

?>

<h1><?php echo $user->lang[$l_title]; ?></h1>

<p><?php echo $user->lang[$l_title . '_EXPLAIN']; ?></p>

<form action="<?php echo "admin_board.$phpEx$SID&amp;mode=$mode"; ?>" method="post"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang[$l_title]; ?></th>
	</tr>
<?php

// Output relevant page
switch ($mode)
{
	case 'cookie':

		$cookie_secure_yes = ($new['cookie_secure']) ? 'checked="checked"' : '';
		$cookie_secure_no = (!$new['cookie_secure']) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['COOKIE_DOMAIN']; ?>: </td>
		<td class="row2"><input type="text" maxlength="255" name="cookie_domain" value="<?php echo $new['cookie_domain']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['COOKIE_NAME']; ?>: </td>
		<td class="row2"><input type="text" maxlength="16" name="cookie_name" value="<?php echo $new['cookie_name']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['COOKIE_PATH']; ?>: </td>
		<td class="row2"><input type="text" maxlength="255" name="cookie_path" value="<?php echo $new['cookie_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['COOKIE_SECURE']; ?>: <br /><span class="gensmall"><?php echo $user->lang['COOKIE_SECURE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="cookie_secure" value="0"<?php echo $cookie_secure_no; ?> /><?php echo $user->lang['DISABLED']; ?>&nbsp; &nbsp;<input type="radio" name="cookie_secure" value="1"<?php echo $cookie_secure_yes; ?> /><?php echo $user->lang['ENABLED']; ?></td>
	</tr>
<?php

		break;

	case 'avatar':

		$avatars_local_yes = ($new['allow_avatar_local']) ? 'checked="checked"' : '';
		$avatars_local_no = (!$new['allow_avatar_local']) ? 'checked="checked"' : '';
		$avatars_remote_yes = ($new['allow_avatar_remote']) ? 'checked="checked"' : '';
		$avatars_remote_no = (!$new['allow_avatar_remote']) ? 'checked="checked"' : '';
		$avatars_upload_yes = ($new['allow_avatar_upload']) ? 'checked="checked"' : '';
		$avatars_upload_no = (!$new['allow_avatar_upload']) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['ALLOW_LOCAL']; ?>: </td>
		<td class="row2"><input type="radio" name="allow_avatar_local" value="1"<?php echo $avatars_local_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_avatar_local" value="0"<?php echo $avatars_local_no; ?>  /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ALLOW_REMOTE']; ?>: <br /><span class="gensmall"><?php echo $user->lang['ALLOW_REMOTE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="allow_avatar_remote" value="1"<?php echo $avatars_remote_yes; ?>  /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_avatar_remote" value="0"<?php echo $avatars_remote_no; ?>  /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ALLOW_UPLOAD']; ?>: </td>
		<td class="row2"><input type="radio" name="allow_avatar_upload" value="1"<?php echo $avatars_upload_yes; ?>  /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_avatar_upload" value="0"<?php echo $avatars_upload_no; ?>  /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['MAX_FILESIZE']; ?>: <br /><span class="gensmall"><?php echo $user->lang['MAX_FILESIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="4" maxlength="10" name="avatar_filesize" value="<?php echo $new['avatar_filesize']; ?>" /> Bytes</td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['MAX_AVATAR_SIZE']; ?>: <br /><span class="gensmall"><?php echo $user->lang['MAX_AVATAR_SIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="avatar_max_height" value="<?php echo $new['avatar_max_height']; ?>" /> x <input type="text" size="3" maxlength="4" name="avatar_max_width" value="<?php echo $new['avatar_max_width']; ?>"></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['AVATAR_STORAGE_PATH']; ?>: <br /><span class="gensmall"><?php echo $user->lang['AVATAR_STORAGE_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="avatar_path" value="<?php echo $new['avatar_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['AVATAR_GALLERY_PATH']; ?>: <br /><span class="gensmall"><?php echo $user->lang['AVATAR_GALLERY_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="avatar_gallery_path" value="<?php echo $new['avatar_gallery_path']; ?>" /></td>
	</tr>
<?php

		break;

	case 'default':

		$style_select = style_select($new['default_style']);
		$lang_select = language_select($new['default_lang']);
		$timezone_select = tz_select($new['board_timezone']);

		$override_user_style_yes = ($new['override_user_style']) ? 'checked="checked"' : '';
		$override_user_style_no = (!$new['override_user_style']) ? 'checked="checked"' : '';

		$dst_yes = ($new['board_dst']) ? 'checked="checked"' : '';
		$dst_no = (!$new['board_dst']) ? 'checked="checked"' : '';

		$topic_notify_yes = ($new['allow_topic_notify']) ? 'checked="checked"' : '';
		$topic_notify_no = (!$new['allow_topic_notify']) ? 'checked="checked"' : '';

		$forum_notify_yes = ($new['allow_forum_notify']) ? 'checked="checked"' : '';
		$forum_notify_no = (!$new['allow_forum_notify']) ? 'checked="checked"' : '';

		$html_yes = ($new['allow_html']) ? 'checked="checked"' : '';
		$html_no = (!$new['allow_html']) ? 'checked="checked"' : '';

		$bbcode_yes = ($new['allow_bbcode']) ? 'checked="checked"' : '';
		$bbcode_no = (!$new['allow_bbcode']) ? 'checked="checked"' : '';

		$smile_yes = ($new['allow_smilies']) ? 'checked="checked"' : '';
		$smile_no = (!$new['allow_smilies']) ? 'checked="checked"' : '';

		$sig_yes = ($new['allow_sig']) ? 'checked="checked"' : '';
		$sig_no = (!$new['allow_sig']) ? 'checked="checked"' : '';

		$censors_yes = ($new['allow_nocensors']) ? 'checked="checked"' : '';
		$censors_no = (!$new['allow_nocensors']) ? 'checked="checked"' : '';

		$namechange_yes = ($new['allow_namechange']) ? 'checked="checked"' : '';
		$namechange_no = (!$new['allow_namechange']) ? 'checked="checked"' : '';

		$attachments_yes = ($new['allow_attachments']) ? 'checked="checked"' : '';
		$attachments_no = (!$new['allow_attachments']) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['DEFAULT_STYLE']; ?></td>
		<td class="row2"><select name="default_style"><?php echo $style_select; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['OVERRIDE_STYLE']; ?>: <br /><span class="gensmall"><?php echo $user->lang['OVERRIDE_STYLE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="override_user_style" value="1" <?php echo $override_user_style_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="override_user_style" value="0" <?php echo $override_user_style_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['DEFAULT_LANGUAGE']; ?>: </td>
		<td class="row2"><select name="default_lang"><?php echo $lang_select; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['DATE_FORMAT']; ?>: <br /><span class="gensmall"><?php echo $user->lang['DATE_FORMAT_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" name="default_dateformat" value="<?php echo $new['default_dateformat']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['SYSTEM_TIMEZONE']; ?>: </td>
		<td class="row2"><select name="board_timezone"><?php echo $timezone_select; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['SYSTEM_DST']; ?>: </td>
		<td class="row2"><input type="radio" name="board_dst" value="1" <?php echo $dst_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="board_dst" value="0" <?php echo $dst_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['CHAR_LIMIT']; ?>: <br /><span class="gensmall"><?php echo $user->lang['CHAR_LIMIT_EXPLAIN']; ?></span</td>
		<td class="row2"><input type="text" size="4" maxlength="4" name="max_post_chars" value="<?php echo $new['max_post_chars']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['SMILIES_LIMIT']; ?>: <br /><span class="gensmall"><?php echo $user->lang['SMILIES_LIMIT_EXPLAIN']; ?></span</td>
		<td class="row2"><input type="text" size="4" maxlength="4" name="max_post_smilies" value="<?php echo $new['max_post_smilies']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ALLOW_TOPIC_NOTIFY']; ?>: </td>
		<td class="row2"><input type="radio" name="allow_topic_notify" value="1" <?php echo $topic_notify_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_topic_notify" value="0" <?php echo $topic_notify_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ALLOW_FORUM_NOTIFY']; ?>: </td>
		<td class="row2"><input type="radio" name="allow_forum_notify" value="1" <?php echo $forum_notify_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_forum_notify" value="0" <?php echo $forum_notify_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ALLOW_NAME_CHANGE']; ?>: </td>
		<td class="row2"><input type="radio" name="allow_namechange" value="1" <?php echo $namechange_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_namechange" value="0" <?php echo $namechange_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ALLOW_ATTACHMENTS']; ?>: </td>
		<td class="row2"><input type="radio" name="allow_attachments" value="1" <?php echo $attachments_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_attachments" value="0" <?php echo $attachments_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ALLOW_HTML']; ?>: </td>
		<td class="row2"><input type="radio" name="allow_html" value="1" <?php echo $html_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_html" value="0" <?php echo $html_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ALLOWED_TAGS']; ?>: <br /><span class="gensmall"><?php echo $user->lang['ALLOWED_TAGS_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="30" maxlength="255" name="allow_html_tags" value="<?php echo $new['allow_html_tags']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ALLOW_BBCODE']; ?>: </td>
		<td class="row2"><input type="radio" name="allow_bbcode" value="1" <?php echo $bbcode_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_bbcode" value="0" <?php echo $bbcode_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ALLOW_SMILIES']; ?>: </td>
		<td class="row2"><input type="radio" name="allow_smilies" value="1" <?php echo $smile_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_smilies" value="0" <?php echo $smile_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ALLOW_SIG']; ?>: </td>
		<td class="row2"><input type="radio" name="allow_sig" value="1" <?php echo $sig_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_sig" value="0" <?php echo $sig_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['MAX_SIG_LENGTH']; ?>: <br /><span class="gensmall"><?php echo $user->lang['MAX_SIG_LENGTH_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="5" maxlength="4" name="max_sig_chars" value="<?php echo $new['max_sig_chars']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ALLOW_NO_CENSORS']; ?>: <br /><span class="gensmall"><?php echo $user->lang['ALLOW_NO_CENSORS_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="allow_nocensors" value="1" <?php echo $censors_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="allow_nocensors" value="0" <?php echo $censors_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php

		break;

	case 'setting':

		$disable_board_yes = ($new['board_disable']) ? 'checked="checked"' : '';
		$disable_board_no = (!$new['board_disable']) ? 'checked="checked"' : '';

		$confirm_enabled = ($new['enable_confirm']) ? 'checked="checked"' : '';
		$confirm_disabled = (!$new['enable_confirm']) ? 'checked="checked"' : '';
		
		$coppa_enable_yes = ($new['coppa_enable']) ? 'checked="checked"' : '';
		$coppa_enable_no = (!$new['coppa_enable']) ? 'checked="checked"' : '';

		$activation_none = ($new['require_activation'] == USER_ACTIVATION_NONE) ? 'checked="checked"' : '';
		$activation_user = ($new['require_activation'] == USER_ACTIVATION_SELF) ? 'checked="checked"' : '';
		$activation_admin = ($new['require_activation'] == USER_ACTIVATION_ADMIN) ? 'checked="checked"' : '';
		$activation_disable = ($new['require_activation'] == USER_ACTIVATION_DISABLE) ? 'checked="checked"' : '';

		$privmsg_on = (!$new['privmsg_disable']) ? 'checked="checked"' : '';
		$privmsg_off = ($new['privmsg_disable']) ? 'checked="checked"' : '';

		$prune_yes = ($new['prune_enable']) ? 'checked="checked"' : '';
		$prune_no = (!$new['prune_enable']) ? 'checked="checked"' : '';

		$display_last_edited_yes = ($new['display_last_edited']) ? 'checked="checked"' : '';
		$display_last_edited_no = (!$new['display_last_edited']) ? 'checked="checked"' : '';
?>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['SITE_NAME']; ?>: </td>
		<td class="row2"><input type="text" size="40" maxlength="255" name="sitename" value="<?php echo htmlentities($new['sitename']); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['SITE_DESC']; ?>: </td>
		<td class="row2"><input type="text" size="40" maxlength="255" name="site_desc" value="<?php echo htmlentities($new['site_desc']); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['BOARD_DISABLE']; ?>: <br /><span class="gensmall"><?php echo $user->lang['BOARD_DISABLE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="board_disable" value="1" <?php echo $disable_board_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="board_disable" value="0" <?php echo $disable_board_no; ?> /> <?php echo $user->lang['NO']; ?><br /><input type="text" name="board_disable_msg" maxlength="255" size="40" value="<?php echo $new['board_disable_msg']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ACC_ACTIVATION']; ?>: <br /><span class="gensmall"><?php echo $user->lang['ACC_ACTIVATION_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="require_activation" value="<?php echo USER_ACTIVATION_NONE; ?>" <?php echo $activation_none; ?> /><?php echo $user->lang['ACC_NONE']; ?>&nbsp; &nbsp;<input type="radio" name="require_activation" value="<?php echo USER_ACTIVATION_SELF; ?>" <?php echo $activation_user; ?> /><?php echo $user->lang['ACC_USER']; ?>&nbsp; &nbsp;<input type="radio" name="require_activation" value="<?php echo USER_ACTIVATION_ADMIN; ?>" <?php echo $activation_admin; ?> /><?php echo $user->lang['ACC_ADMIN']; ?>&nbsp; &nbsp;<input type="radio" name="require_activation" value="<?php echo USER_ACTIVATION_DISABLE; ?>" <?php echo $activation_disable; ?> /><?php echo $user->lang['ACC_DISABLE']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['VISUAL_CONFIRM']; ?>: <br /><span class="gensmall"><?php echo $user->lang['VISUAL_CONFIRM_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="enable_confirm" value="1"<?php echo $confirm_enabled ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="enable_confirm" value="0" <?php echo $confirm_disabled ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	
	<tr>
		<td class="row1"><?php echo $user->lang['ENABLE_COPPA']; ?>: <br /><span class="gensmall"><?php echo $user->lang['ENABLE_COPPA_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="coppa_enable" value="1" <?php echo $coppa_enable_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="coppa_enable" value="0" <?php echo $coppa_enable_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['COPPA_FAX']; ?>: </td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="coppa_fax" value="<?php echo $new['coppa_fax']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['COPPA_MAIL']; ?>: <br /><span class="gensmall"><?php echo $user->lang['COPPA_MAIL_EXPLAIN']; ?></span></td>
		<td class="row2"><textarea name="coppa_mail" rows="5" cols="40"><?php echo $new['coppa_mail']; ?></textarea></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['BOARD_PM']; ?>: <br /><span class="gensmall"><?php echo $user->lang['BOARD_PM_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="privmsg_disable" value="0" <?php echo $privmsg_on; ?> /><?php echo $user->lang['ENABLED']; ?>&nbsp; &nbsp;<input type="radio" name="privmsg_disable" value="1" <?php echo $privmsg_off; ?> /><?php echo $user->lang['DISABLED']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['BOXES_MAX']; ?>: <br /><span class="gensmall"><?php echo $user->lang['BOXES_MAX_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" maxlength="4" size="4" name="pm_max_boxes" value="<?php echo $new['pm_max_boxes']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['BOXES_LIMIT']; ?>: <br /><span class="gensmall"><?php echo $user->lang['BOXES_LIMIT_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" maxlength="4" size="4" name="pm_max_msgs" value="<?php echo $new['pm_max_msgs']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['EDIT_TIME']; ?>: <br /><span class="gensmall"><?php echo $user->lang['EDIT_TIME_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" maxlength="3" size="3" name="edit_time" value="<?php echo $new['edit_time']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['DISPLAY_LAST_EDITED']; ?>: <br /><span class="gensmall"><?php echo $user->lang['DISPLAY_LAST_EDITED_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="display_last_edited" value="1" <?php echo $display_last_edited_yes; ?> /><?php echo $user->lang['YES']; ?>&nbsp; &nbsp;<input type="radio" name="display_last_edited" value="0" <?php echo $display_last_edited_no; ?> /><?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['FLOOD_INTERVAL']; ?>: <br /><span class="gensmall"><?php echo $user->lang['FLOOD_INTERVAL_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="flood_interval" value="<?php echo $new['flood_interval']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['MIN_SEARCH_CHARS']; ?>: <br /><span class="gensmall"><?php echo $user->lang['MIN_SEARCH_CHARS_EXPLAIN']; ?></span</td>
		<td class="row2"><input type="text" size="3" maxlength="3" name="min_search_chars" value="<?php echo $new['min_search_chars']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['MAX_SEARCH_CHARS']; ?>: <br /><span class="gensmall"><?php echo $user->lang['MAX_SEARCH_CHARS_EXPLAIN']; ?></span</td>
		<td class="row2"><input type="text" size="3" maxlength="3" name="max_search_chars" value="<?php echo $new['max_search_chars']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['TOPICS_PER_PAGE']; ?>: </td>
		<td class="row2"><input type="text" name="topics_per_page" size="3" maxlength="4" value="<?php echo $new['topics_per_page']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['POSTS_PER_PAGE']; ?>: </td>
		<td class="row2"><input type="text" name="posts_per_page" size="3" maxlength="4" value="<?php echo $new['posts_per_page']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['HOT_THRESHOLD']; ?>: </td>
		<td class="row2"><input type="text" name="hot_threshold" size="3" maxlength="4" value="<?php echo $new['hot_threshold']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['MAX_POLL_OPTIONS']; ?>: </td>
		<td class="row2"><input type="text" name="max_poll_options" size="4" maxlength="4" value="<?php echo $new['max_poll_options']; ?>" /></td>
	</tr>
<?php

		break;

	case 'email':

		$email_yes = ($new['email_enable']) ? 'checked="checked"' : '';
		$email_no = (!$new['email_enable']) ? 'checked="checked"' : '';

		$board_email_form_yes = ($new['board_email_form']) ? 'checked="checked"' : '';
		$board_email_form_no = (!$new['board_email_form']) ? 'checked="checked"' : '';

		$smtp_yes = ($new['smtp_delivery']) ? 'checked="checked"' : '';
		$smtp_no = (!$new['smtp_delivery']) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1"><?php echo $user->lang['ENABLE_EMAIL']; ?>: <br /><span class="gensmall"><?php echo $user->lang['ENABLE_EMAIL_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="email_enable" value="1" <?php echo $email_yes; ?> /> <?php echo $user->lang['ENABLED']; ?>&nbsp;&nbsp;<input type="radio" name="email_enable" value="0" <?php echo $email_no; ?> /> <?php echo $user->lang['DISABLED']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['BOARD_EMAIL_FORM']; ?>: <br /><span class="gensmall"><?php echo $user->lang['BOARD_EMAIL_FORM_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="board_email_form" value="1" <?php echo $board_email_form_yes; ?> /> <?php echo $user->lang['ENABLED']; ?>&nbsp;&nbsp;<input type="radio" name="board_email_form" value="0" <?php echo $board_email_form_no; ?> /> <?php echo $user->lang['DISABLED']; ?></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['CONTACT_EMAIL']; ?>: <br /><span class="gensmall"><?php echo $user->lang['CONTACT_EMAIL_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="board_contact" value="<?php echo $new['board_contact']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['ADMIN_EMAIL']; ?>: <br /><span class="gensmall"><?php echo $user->lang['ADMIN_EMAIL_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="board_email" value="<?php echo $new['board_email']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['EMAIL_SIG']; ?>: <br /><span class="gensmall"><?php echo $user->lang['EMAIL_SIG_EXPLAIN']; ?></span></td>
		<td class="row2"><textarea name="board_email_sig" rows="5" cols="30"><?php echo $new['board_email_sig']; ?></textarea></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['USE_SMTP']; ?>: <br /><span class="gensmall"><?php echo $user->lang['USE_SMTP_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="smtp_delivery" value="1" <?php echo $smtp_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="smtp_delivery" value="0" <?php echo $smtp_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['SMTP_SERVER']; ?>: </td>
		<td class="row2"><input type="text" name="smtp_host" value="<?php echo $new['smtp_host']; ?>" size="25" maxlength="50" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['SMTP_PORT']; ?>: <br /><span class="gensmall"><?php echo $user->lang['SMTP_PORT_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" name="smtp_port" value="<?php echo $new['smtp_port']; ?>" size="4" maxlength="5" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['SMTP_USERNAME']; ?>: <br /><span class="gensmall"><?php echo $user->lang['SMTP_USERNAME_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" name="smtp_username" value="<?php echo $new['smtp_username']; ?>" size="25" maxlength="255" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['SMTP_PASSWORD']; ?>: <br /><span class="gensmall"><?php echo $user->lang['SMTP_PASSWORD_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="password" name="smtp_password" value="<?php echo $new['smtp_password']; ?>" size="25" maxlength="255" /></td>
	</tr>
<?php

		break;

	case 'server':

		$ip_all = ($new['ip_check'] == 4) ? 'checked="checked"' : '';
		$ip_classc = ($new['ip_check'] == 3) ? 'checked="checked"' : '';
		$ip_classb = ($new['ip_check'] == 2) ? 'checked="checked"' : '';
		$ip_none = ($new['ip_check'] == 0) ? 'checked="checked"' : '';

		$browser_yes = ($new['browser_check']) ? 'checked="checked"' : '';
		$browser_no = (!$new['browser_check']) ? 'checked="checked"' : '';

		$gzip_yes = ($new['gzip_compress']) ? 'checked="checked"' : '';
		$gzip_no = (!$new['gzip_compress']) ? 'checked="checked"' : '';
?>
	<tr>
		<td class="row1"><?php echo $user->lang['SERVER_NAME']; ?>: <br /><span class="gensmall"><?php echo $user->lang['SERVER_NAME_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" maxlength="255" size="40" name="server_name" value="<?php echo $new['server_name']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['SERVER_PORT']; ?>: <br /><span class="gensmall"><?php echo $user->lang['SERVER_PORT_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" maxlength="5" size="5" name="server_port" value="<?php echo $new['server_port']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['SCRIPT_PATH']; ?>: <br /><span class="gensmall"><?php echo $user->lang['SCRIPT_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" maxlength="255" name="script_path" value="<?php echo $new['script_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['IP_VALID']; ?>: <br /><span class="gensmall"><?php echo $user->lang['IP_VALID_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="ip_check" value="4" <?php echo $ip_all; ?> /> <?php echo $user->lang['ALL']; ?>&nbsp;&nbsp;<input type="radio" name="ip_check" value="3" <?php echo $ip_classc; ?> /> <?php echo $user->lang['CLASS_C']; ?>&nbsp;&nbsp;<input type="radio" name="ip_check" value="2" <?php echo $ip_classb; ?> /> <?php echo $user->lang['CLASS_B']; ?>&nbsp;&nbsp;<input type="radio" name="ip_check" value="0" <?php echo $ip_none; ?> /> <?php echo $user->lang['NONE']; ?>&nbsp;&nbsp;</td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['BROWSER_VALID']; ?>: <br /><span class="gensmall"><?php echo $user->lang['BROWSER_VALID_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="browser_check" value="1" <?php echo $browser_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="browser_check" value="0" <?php echo $browser_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ENABLE_GZIP']; ?>: </td>
		<td class="row2"><input type="radio" name="gzip_compress" value="1" <?php echo $gzip_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="gzip_compress" value="0" <?php echo $gzip_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['SMILIES_PATH']; ?>: <br /><span class="gensmall"><?php echo $user->lang['SMILIES_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="smilies_path" value="<?php echo $new['smilies_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ICONS_PATH']; ?>: <br /><span class="gensmall"><?php echo $user->lang['ICONS_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="icons_path" value="<?php echo $new['icons_path']; ?>" /></td>
	</tr>
<?php

	case 'load':

		$load_db_track_yes = ($new['load_db_track']) ? 'checked="checked"' : '';
		$load_db_track_no = (!$new['load_db_track']) ? 'checked="checked"' : '';
		$load_db_lastread_yes = ($new['load_db_lastread']) ? 'checked="checked"' : '';
		$load_db_lastread_no = (!$new['load_db_lastread']) ? 'checked="checked"' : '';
		$load_online_yes = ($new['load_online']) ? 'checked="checked"' : '';
		$load_online_no = (!$new['load_online']) ? 'checked="checked"' : '';
		$moderators_yes = ($new['load_moderators']) ? 'checked="checked"' : '';
		$moderators_no = (!$new['load_moderators']) ? 'checked="checked"' : '';
		$search_yes = ($new['load_search']) ? 'checked="checked"' : '';
		$search_no = (!$new['load_search']) ? 'checked="checked"' : '';
		$search_update_yes = ($new['load_search_upd']) ? 'checked="checked"' : '';
		$search_update_no = (!$new['load_search_upd']) ? 'checked="checked"' : '';
		$search_phrase_yes = ($new['load_search_phr']) ? 'checked="checked"' : '';
		$search_phrase_no = (!$new['load_search_phr']) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['LIMIT_LOAD']; ?>: <br /><span class="gensmall"><?php echo $user->lang['LIMIT_LOAD_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="4" maxlength="4" name="limit_load" value="<?php echo $new['limit_load']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['SESSION_LENGTH']; ?>: </td>
		<td class="row2"><input type="text" maxlength="5" size="5" name="session_length" value="<?php echo $new['session_length']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['LIMIT_SESSIONS']; ?>: <br /><span class="gensmall"><?php echo $user->lang['LIMIT_SESSIONS_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="4" maxlength="4" name="active_sessions" value="<?php echo $new['active_sessions']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['YES_POST_MARKING']; ?>: <br /><span class="gensmall"><?php echo $user->lang['YES_POST_MARKING_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="load_db_track" value="1"<?php echo $load_db_track_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_db_track" value="0" <?php echo $load_db_track_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['YES_READ_MARKING']; ?>: <br /><span class="gensmall"><?php echo $user->lang['YES_READ_MARKING_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="load_db_lastread" value="1"<?php echo $load_db_lastread_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_db_lastread" value="0" <?php echo $load_db_lastread_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['YES_ONLINE']; ?>: <br /><span class="gensmall"><?php echo $user->lang['YES_ONLINE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="load_online" value="1"<?php echo $load_online_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_online" value="0" <?php echo $load_online_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['VIEW_ONLINE_TIME']; ?>: <br /><span class="gensmall"><?php echo $user->lang['VIEW_ONLINE_TIME_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="4" maxlength="3" name="load_online_time" value="<?php echo $new['load_online_time']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['YES_MODERATORS']; ?>: </td>
		<td class="row2"><input type="radio" name="load_moderators" value="1"<?php echo $moderators_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_moderators" value="0" <?php echo $moderators_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['YES_SEARCH']; ?>: <br /><span class="gensmall"><?php echo $user->lang['YES_SEARCH_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="load_search" value="1"<?php echo $search_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_search" value="0" <?php echo $search_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['SEARCH_INTERVAL']; ?>: <br /><span class="gensmall"><?php echo $user->lang['SEARCH_INTERVAL_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="search_interval" value="<?php echo $new['search_interval']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['YES_SEARCH_UPDATE']; ?>: <br /><span class="gensmall"><?php echo $user->lang['YES_SEARCH_UPDATE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="load_search_upd" value="1"<?php echo $search_update_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_search_upd" value="0" <?php echo $search_update_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['YES_SEARCH_PHRASE']; ?>: <br /><span class="gensmall"><?php echo $user->lang['YES_SEARCH_PHRASE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="load_search_phr" value="1"<?php echo $search_phrase_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp; &nbsp;<input type="radio" name="load_search_phr" value="0" <?php echo $search_phrase_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php

		break;

	case 'auth':

		$auth_plugins = array();

		$dp = opendir($phpbb_root_path . 'includes/auth');
		while ($file = readdir($dp))
		{
			if (preg_match('#^auth_(.*?)\.' . $phpEx . '$#', $file))
			{
				$auth_plugins[] = preg_replace('#^auth_(.*?)\.' . $phpEx . '$#', '\1', $file);
			}
		}

		sort($auth_plugins);

		$auth_select = '';
		foreach ($auth_plugins as $method)
		{
			$selected = ($config['auth_method'] == $method) ? ' selected="selected"' : '';
			$auth_select .= '<option value="' . $method . '"' . $selected . '>' . ucfirst($method) . '</option>';
		}

?>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['AUTH_METHOD']; ?>:</td>
		<td class="row2"><select name="auth_method"><?php echo $auth_select; ?></select></td>
	</tr>
<?php

		foreach ($auth_plugins as $method)
		{
			if ($method && file_exists($phpbb_root_path . 'includes/auth/auth_' . $method . '.' . $phpEx))
			{
				include_once($phpbb_root_path . 'includes/auth/auth_' . $method . '.' . $phpEx);

				$method = 'admin_' . $method;
				if (function_exists($method))
				{
					if ($config_fields = $method($new))
					{
						//
						// Check if we need to create config fields for this plugin
						//
						foreach($config_fields as $field)
						{
							if (!isset($config[$field]))
							{
								set_config($field, '');
							}
						}
					}

					unset($config_fields);
				}
			}
		}

		break;
}

?>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="liteoption" /></td>
	</tr>
</table></form>

<?php

adm_page_footer();

?>