<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : admin_board.php
// STARTED   : Thu Jul 12, 2001
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

if (!empty($setmodules))
{
	$filename = basename(__FILE__);
	$module['GENERAL']['AUTH_SETTINGS']		= ($auth->acl_get('a_server')) ? "$filename$SID&amp;mode=auth" : '';
	$module['GENERAL']['AVATAR_SETTINGS']	= ($auth->acl_get('a_board')) ? "$filename$SID&amp;mode=avatar" : '';
	$module['GENERAL']['BOARD_DEFAULTS']	= ($auth->acl_get('a_defaults')) ? "$filename$SID&amp;mode=default" : '';
	$module['GENERAL']['BOARD_SETTINGS']	= ($auth->acl_get('a_board')) ? "$filename$SID&amp;mode=setting" : '';
	$module['GENERAL']['COOKIE_SETTINGS']	= ($auth->acl_get('a_cookies')) ? "$filename$SID&amp;mode=cookie" : '';
	$module['GENERAL']['EMAIL_SETTINGS']	= ($auth->acl_get('a_server')) ? "$filename$SID&amp;mode=email" : '';
	$module['GENERAL']['LOAD_SETTINGS']		= ($auth->acl_get('a_server')) ? "$filename$SID&amp;mode=load" : '';
	$module['GENERAL']['SERVER_SETTINGS']	= ($auth->acl_get('a_server')) ? "$filename$SID&amp;mode=server" : '';
	$module['GENERAL']['MESSAGE_SETTINGS']	= ($auth->acl_get('a_defaults')) ? "$filename$SID&amp;mode=message" : '';
	return;
}

define('IN_PHPBB', 1);
// Load default header
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);

// Get mode
$mode	= request_var('mode', '');
$action	= request_var('action', '');
$submit = (isset($_POST['submit'])) ? true : false;

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
//	case 'karma':
//		$l_title = 'KARMA_SETTINGS';
//		$which_auth = 'a_user';
//		break;
	case 'message':
		$l_title = 'MESSAGE_SETTINGS';
		$which_auth = 'a_defaults';
		break;
	case 'message':
		$l_title = 'MESSAGE_SETTINGS';
		$which_auth = 'a_defaults';
		break;
	default:
		return;
}

// Check permissions
if (!$auth->acl_get($which_auth))
{
	trigger_error($user->lang['NO_ADMIN']);
}

$new = $config;
$cfg_array = (isset($_REQUEST['config'])) ? request_var('config', '') : $new;

foreach ($cfg_array as $config_name => $config_value)
{
	$new[$config_name] = $config_value;

	if ($config_name == 'email_function_name')
	{
		$new['email_function_name'] = (empty($new['email_function_name']) || !function_exists($new['email_function_name'])) ? 'mail' : str_replace(array('(', ')'), array('', ''), trim($new['email_function_name']));
	}
	
	if ($submit)
	{
		set_config($config_name, $config_value);
	}
}

if ($submit)
{
	add_log('admin', 'LOG_' . strtoupper($mode) . '_CONFIG');

	trigger_error($user->lang['CONFIG_UPDATED']);
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

		$cookie_secure_yes	= ($new['cookie_secure']) ? 'checked="checked"' : '';
		$cookie_secure_no	= (!$new['cookie_secure']) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1" width="50%"><b><?php echo $user->lang['COOKIE_DOMAIN']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" maxlength="255" name="config[cookie_domain]" value="<?php echo $new['cookie_domain']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['COOKIE_NAME']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" maxlength="16" name="config[cookie_name]" value="<?php echo $new['cookie_name']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['COOKIE_PATH']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" maxlength="255" name="config[cookie_p]" value="<?php echo $new['cookie_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['COOKIE_SECURE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['COOKIE_SECURE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[cookie_secure]" value="0"<?php echo $cookie_secure_no; ?> /><?php echo $user->lang['DISABLED']; ?>&nbsp;&nbsp;<input type="radio" name="config[cookie_secure]" value="1"<?php echo $cookie_secure_yes; ?> /><?php echo $user->lang['ENABLED']; ?></td>
	</tr>
<?php

		break;

	case 'avatar':

		$avatars_local_yes	= ($new['allow_avatar_local']) ? 'checked="checked"' : '';
		$avatars_local_no	= (!$new['allow_avatar_local']) ? 'checked="checked"' : '';
		$avatars_remote_yes = ($new['allow_avatar_remote']) ? 'checked="checked"' : '';
		$avatars_remote_no	= (!$new['allow_avatar_remote']) ? 'checked="checked"' : '';
		$avatars_upload_yes = ($new['allow_avatar_upload']) ? 'checked="checked"' : '';
		$avatars_upload_no	= (!$new['allow_avatar_upload']) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1" width="50%"><b><?php echo $user->lang['ALLOW_LOCAL']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[allow_avatar_local]" value="1"<?php echo $avatars_local_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_avatar_local]" value="0"<?php echo $avatars_local_no; ?>  /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_REMOTE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ALLOW_REMOTE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[allow_avatar_remote]" value="1"<?php echo $avatars_remote_yes; ?>  /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_avatar_remote]" value="0"<?php echo $avatars_remote_no; ?>  /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_UPLOAD']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[allow_avatar_upload]" value="1"<?php echo $avatars_upload_yes; ?>  /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_avatar_upload]" value="0"<?php echo $avatars_upload_no; ?>  /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAX_FILESIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MAX_FILESIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="10" name="config[avatar_filesize]" value="<?php echo $new['avatar_filesize']; ?>" /> Bytes</td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MIN_AVATAR_SIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MIN_AVATAR_SIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="config[avatar_min_height]" value="<?php echo $new['avatar_min_height']; ?>" /> x <input class="post" type="text" size="3" maxlength="4" name="config[avatar_min_width]" value="<?php echo $new['avatar_min_width']; ?>"></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAX_AVATAR_SIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MAX_AVATAR_SIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="config[avatar_max_height]" value="<?php echo $new['avatar_max_height']; ?>" /> x <input class="post" type="text" size="3" maxlength="4" name="config[avatar_max_width]" value="<?php echo $new['avatar_max_width']; ?>"></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['AVATAR_STORAGE_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['AVATAR_STORAGE_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="config[avatar_path]" value="<?php echo $new['avatar_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['AVATAR_GALLERY_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['AVATAR_GALLERY_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="config[avatar_gallery_path]" value="<?php echo $new['avatar_gallery_path']; ?>" /></td>
	</tr>
<?php

		break;

	case 'default':

		$style_select = style_select($new['default_style'], true);
		$lang_select = language_select($new['default_lang']);
		$timezone_select = tz_select($new['board_timezone']);

		$dst_yes = ($new['board_dst']) ? 'checked="checked"' : '';
		$dst_no = (!$new['board_dst']) ? 'checked="checked"' : '';

		$yes_no_switches = array('override_user_style', 'allow_privmsg', 'allow_topic_notify', 'allow_forum_notify', 'allow_html', 'allow_bbcode', 'allow_smilies', 'allow_sig', 'allow_nocensors', 'allow_namechange', 'allow_attachments', 'allow_pm_attach', 'board_dst');

		foreach ($yes_no_switches as $switch)
		{
			$switch_var = str_replace('allow_', '', $switch);
			${$switch_var . '_yes'} = ($new[$switch]) ? ' checked="checked"' : '';
			${$switch_var . '_no'} = (!$new[$switch]) ? ' checked="checked"' : '';
		}

?>
	<tr>
		<td class="row1" width="50%"><b><?php echo $user->lang['DEFAULT_STYLE']; ?></td>
		<td class="row2"><select name="config[default_style]"><?php echo $style_select; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['OVERRIDE_STYLE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['OVERRIDE_STYLE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[override_user_style]" value="1" <?php echo $override_user_style_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[override_user_style]" value="0" <?php echo $override_user_style_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['DEFAULT_LANGUAGE']; ?>: </b></td>
		<td class="row2"><select name="config[default_lang]"><?php echo $lang_select; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['DEFAULT_DATE_FORMAT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['DEFAULT_DATE_FORMAT_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="config[default_dateformat]" value="<?php echo $new['default_dateformat']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SYSTEM_TIMEZONE']; ?>: </b></td>
		<td class="row2"><select name="config[board_timezone]"><?php echo $timezone_select; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SYSTEM_DST']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[board_dst]" value="1" <?php echo $board_dst_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[board_dst]" value="0" <?php echo $board_dst_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['BOARD_PM']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BOARD_PM_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[allow_privmsg]" value="1" <?php echo $privmsg_yes; ?> /><?php echo $user->lang['ENABLED']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_privmsg]" value="0" <?php echo $privmsg_no; ?> /><?php echo $user->lang['DISABLED']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_TOPIC_NOTIFY']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[allow_topic_notify]" value="1" <?php echo $topic_notify_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_topic_notify]" value="0" <?php echo $topic_notify_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_FORUM_NOTIFY']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[allow_forum_notify]" value="1" <?php echo $forum_notify_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_forum_notify]" value="0" <?php echo $forum_notify_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_NAME_CHANGE']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[allow_namechange]" value="1" <?php echo $namechange_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_namechange]" value="0" <?php echo $namechange_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_ATTACHMENTS']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[allow_attachments]" value="1" <?php echo $attachments_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_attachments]" value="0" <?php echo $attachments_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_PM_ATTACHMENTS']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[allow_pm_attach]" value="1" <?php echo $pm_attach_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_pm_attach]" value="0" <?php echo $pm_attach_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_HTML']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[allow_html]" value="1" <?php echo $html_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_html]" value="0" <?php echo $html_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOWED_TAGS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ALLOWED_TAGS_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="30" maxlength="255" name="config[allow_html_tags]" value="<?php echo $new['allow_html_tags']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_BBCODE']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[allow_bbcode]" value="1" <?php echo $bbcode_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_bbcode]" value="0" <?php echo $bbcode_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_SMILIES']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[allow_smilies]" value="1" <?php echo $smilies_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_smilies]" value="0" <?php echo $smilies_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_SIG']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[allow_sig]" value="1" <?php echo $sig_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_sig]" value="0" <?php echo $sig_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAX_SIG_LENGTH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MAX_SIG_LENGTH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="5" maxlength="4" name="config[max_sig_chars]" value="<?php echo $new['max_sig_chars']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_NO_CENSORS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ALLOW_NO_CENSORS_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[allow_nocensors]" value="1" <?php echo $nocensors_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_nocensors]" value="0" <?php echo $nocensors_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php

		break;

	case 'setting':

		$yes_no_switches = array('board_disable', 'enable_confirm', 'coppa_enable', 'display_last_edited', 'allow_emailreuse');

		foreach ($yes_no_switches as $switch)
		{
			$switch_var = str_replace('allow_', '', $switch);
			${$switch_var . '_yes'} = ($new[$switch]) ? ' checked="checked"' : '';
			${$switch_var . '_no'} = (!$new[$switch]) ? ' checked="checked"' : '';
		}

		$activation_none	= ($new['require_activation'] == USER_ACTIVATION_NONE) ? 'checked="checked"' : '';
		$activation_user	= ($new['require_activation'] == USER_ACTIVATION_SELF) ? 'checked="checked"' : '';
		$activation_admin	= ($new['require_activation'] == USER_ACTIVATION_ADMIN) ? 'checked="checked"' : '';
		$activation_disable = ($new['require_activation'] == USER_ACTIVATION_DISABLE) ? 'checked="checked"' : '';

		$s_bump_type = '';
		$types = array('m' => 'MINUTES', 'h' => 'HOURS', 'd' => 'DAYS');
		foreach ($types as $type => $lang)
		{
			$selected = ($new['bump_type'] == $type) ? 'selected="selected" ' : '';
			$s_bump_type .= '<option value="' . $type . '" ' . $selected . '>' . $user->lang[$lang] . '</option>';
		}

		$user_char_ary = array('USERNAME_CHARS_ANY' => '.*', 'USERNAME_ALPHA_ONLY' => '[\w]+', 'USERNAME_ALPHA_SPACERS' => '[\w_\+\. \-\[\]]+');
		$user_char_options = '';
		foreach ($user_char_ary as $lang => $value)
		{
			$selected = ($new['allow_name_chars'] == $value) ? ' selected="selected"' : '';
			$user_char_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang[$lang] . '</option>';
		}

		$pass_type_ary = array('PASS_TYPE_ANY' => '.*', 'PASS_TYPE_CASE' => '[a-zA-Z]', 'PASS_TYPE_ALPHA' => '[a-zA-Z0-9]', 'PASS_TYPE_SYMBOL' => '[a-zA-Z\W]'); 
		$pass_char_options = '';
		foreach ($pass_type_ary as $lang => $value)
		{
			$selected = ($new['pass_complex'] == $value) ? ' selected="selected"' : '';
			$pass_char_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang[$lang] . '</option>';
		}

?>
	<tr>
		<td class="row1" width="50%"><b><?php echo $user->lang['SITE_NAME']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" size="40" maxlength="255" name="config[sitename]" value="<?php echo $new['sitename']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SITE_DESC']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" size="40" maxlength="255" name="config[site_desc]" value="<?php echo $new['site_desc']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['DISABLE_BOARD']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['DISABLE_BOARD_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[board_disable]" value="1" <?php echo $board_disable_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[board_disable]" value="0" <?php echo $board_disable_no; ?> /> <?php echo $user->lang['NO']; ?><br /><input class="post" type="text" name="config[board_disable_msg]" maxlength="255" size="40" value="<?php echo $new['board_disable_msg']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ACC_ACTIVATION']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ACC_ACTIVATION_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[require_activation]" value="<?php echo USER_ACTIVATION_NONE; ?>" <?php echo $activation_none; ?> /> <?php echo $user->lang['ACC_NONE']; ?><?php if ($config['email_enable']) { ?>&nbsp;&nbsp;<input type="radio" name="config[require_activation]" value="<?php echo USER_ACTIVATION_SELF; ?>" <?php echo $activation_user; ?> /> <?php echo $user->lang['ACC_USER']; ?>&nbsp;&nbsp;<input type="radio" name="config[require_activation]" value="<?php echo USER_ACTIVATION_ADMIN; ?>" <?php echo $activation_admin; ?> /> <?php echo $user->lang['ACC_ADMIN']; ?><?php } ?>&nbsp;&nbsp;<input type="radio" name="config[require_activation]" value="<?php echo USER_ACTIVATION_DISABLE; ?>" <?php echo $activation_disable; ?> /> <?php echo $user->lang['ACC_DISABLE']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ENABLE_COPPA']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ENABLE_COPPA_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[coppa_enable]" value="1" <?php echo $enable_confirm_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[coppa_enable]" value="0" <?php echo $enable_confirm_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['COPPA_FAX']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" size="25" maxlength="100" name="config[coppa_fax]" value="<?php echo $new['coppa_fax']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['COPPA_MAIL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['COPPA_MAIL_EXPLAIN']; ?></span></td>
		<td class="row2"><textarea name="config[coppa_mail]" rows="5" cols="40"><?php echo $new['coppa_mail']; ?></textarea></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['VISUAL_CONFIRM']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['VISUAL_CONFIRM_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[enable_confirm]" value="1"<?php echo $enable_confirm_yes ?> /> <?php echo $user->lang['YES'] ?>&nbsp;&nbsp;<input type="radio" name="config[enable_confirm]" value="0" <?php echo $enable_confirm_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['REG_LIMIT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['REG_LIMIT_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="4" size="4" name="config[max_reg_attempts]" value="<?php echo $new['max_reg_attempts']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['USERNAME_LENGTH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['USERNAME_LENGTH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="3" name="config[min_name_chars]" value="<?php echo $new['min_name_chars']; ?>" /> <?php echo $user->lang['MIN_CHARS']; ?>&nbsp;&nbsp;<input class="post" type="text" size="3" maxlength="3" name="config[max_name_chars]" value="<?php echo $new['max_name_chars']; ?>" /> <?php echo $user->lang['MAX_CHARS']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['USERNAME_CHARS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['USERNAME_CHARS_EXPLAIN']; ?></span></td>
		<td class="row2"><select name="config[allow_name_chars]"><?php echo $user_char_options; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['PASSWORD_LENGTH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['PASSWORD_LENGTH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="3" name="config[min_pass_chars]" value="<?php echo $new['min_pass_chars']; ?>" /> <?php echo $user->lang['MIN_CHARS']; ?>&nbsp;&nbsp;<input class="post" type="text" size="3" maxlength="3" name="config[max_pass_chars]" value="<?php echo $new['max_pass_chars']; ?>" /> <?php echo $user->lang['MAX_CHARS']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['PASSWORD_TYPE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['PASSWORD_TYPE_EXPLAIN']; ?></span></td>
		<td class="row2"><select name="config[pass_complex]"><?php echo $pass_char_options; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FORCE_PASS_CHANGE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FORCE_PASS_CHANGE_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="3" name="config[chg_passforce]" value="<?php echo $new['chg_passforce']; ?>" /> <?php echo $user->lang['DAYS']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_EMAIL_REUSE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ALLOW_EMAIL_REUSE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[allow_emailreuse]" value="1" <?php echo $emailreuse_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_emailreuse]" value="0" <?php echo $emailreuse_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['EDIT_TIME']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['EDIT_TIME_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="3" size="3" name="config[edit_time]" value="<?php echo $new['edit_time']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['DISPLAY_LAST_EDITED']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['DISPLAY_LAST_EDITED_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[display_last_edited]" value="1" <?php echo $display_last_edited_yes; ?> /><?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[display_last_edited]" value="0" <?php echo $display_last_edited_no; ?> /><?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FLOOD_INTERVAL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FLOOD_INTERVAL_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="config[flood_interval]" value="<?php echo $new['flood_interval']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['BUMP_INTERVAL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BUMP_INTERVAL_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="config[bump_interval]" value="<?php echo $new['bump_interval'] ?>" />&nbsp;<select name="config[bump_type]"><?php echo $s_bump_type; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['TOPICS_PER_PAGE']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="config[topics_per_page]" size="3" maxlength="4" value="<?php echo $new['topics_per_page']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['POSTS_PER_PAGE']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="config[posts_per_page]" size="3" maxlength="4" value="<?php echo $new['posts_per_page']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['HOT_THRESHOLD']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="config[hot_threshold]" size="3" maxlength="4" value="<?php echo $new['hot_threshold']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAX_POLL_OPTIONS']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="config[max_poll_options]" size="4" maxlength="4" value="<?php echo $new['max_poll_options']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['CHAR_LIMIT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['CHAR_LIMIT_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="6" name="config[max_post_chars]" value="<?php echo $new['max_post_chars']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SMILIES_LIMIT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SMILIES_LIMIT_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="4" name="config[max_post_smilies]" value="<?php echo $new['max_post_smilies']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['QUOTE_DEPTH_LIMIT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['QUOTE_DEPTH_LIMIT_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="4" name="config[max_quote_depth]" value="<?php echo $new['max_quote_depth']; ?>" /></td>
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

		$auth_methods = array('PLAIN', 'LOGIN', 'CRAM-MD5', 'DIGEST-MD5', 'POP-BEFORE-SMTP');
		$s_smtp_auth_options = '';

		foreach ($auth_methods as $method)
		{
			$s_smtp_auth_options .= '<option value="' . $method . '"' . (($new['smtp_auth_method'] == $method) ? ' selected="selected"' : '') . '>' . $user->lang['SMTP_' . str_replace('-', '_', $method)] . '</option>';
		}
?>
	<tr>
		<td class="row1" width="50%"><b><?php echo $user->lang['ENABLE_EMAIL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ENABLE_EMAIL_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[email_enable]" value="1" <?php echo $email_yes; ?> /> <?php echo $user->lang['ENABLED']; ?>&nbsp;&nbsp;<input type="radio" name="config[email_enable]" value="0" <?php echo $email_no; ?> /> <?php echo $user->lang['DISABLED']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['BOARD_EMAIL_FORM']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BOARD_EMAIL_FORM_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[board_email_form]" value="1" <?php echo $board_email_form_yes; ?> /> <?php echo $user->lang['ENABLED']; ?>&nbsp;&nbsp;<input type="radio" name="config[board_email_form]" value="0" <?php echo $board_email_form_no; ?> /> <?php echo $user->lang['DISABLED']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['EMAIL_FUNCTION_NAME']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['EMAIL_FUNCTION_NAME_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="50" name="config[email_function_name]" value="<?php echo $new['email_function_name']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['EMAIL_PACKAGE_SIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['EMAIL_PACKAGE_SIZE_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="5" maxlength="5" name="config[email_package_size]" value="<?php echo $new['email_package_size']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['CONTACT_EMAIL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['CONTACT_EMAIL_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="25" maxlength="100" name="config[board_contact]" value="<?php echo $new['board_contact']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ADMIN_EMAIL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ADMIN_EMAIL_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="25" maxlength="100" name="config[board_email]" value="<?php echo $new['board_email']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['EMAIL_SIG']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['EMAIL_SIG_EXPLAIN']; ?></span></td>
		<td class="row2"><textarea name="config[board_email_sig]" rows="5" cols="30"><?php echo $new['board_email_sig']; ?></textarea></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['USE_SMTP']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['USE_SMTP_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[smtp_delivery]" value="1" <?php echo $smtp_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[smtp_delivery]" value="0" <?php echo $smtp_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SMTP_SERVER']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="config[smtp_host]" value="<?php echo $new['smtp_host']; ?>" size="25" maxlength="50" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SMTP_PORT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SMTP_PORT_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="config[smtp_port]" value="<?php echo $new['smtp_port']; ?>" size="4" maxlength="5" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SMTP_AUTH_METHOD']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SMTP_AUTH_METHOD_EXPLAIN']; ?></span></td>
		<td class="row2"><select name="config[smtp_auth_method]"><?php echo $s_smtp_auth_options; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SMTP_USERNAME']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SMTP_USERNAME_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="config[smtp_username]" value="<?php echo $new['smtp_username']; ?>" size="25" maxlength="255" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SMTP_PASSWORD']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SMTP_PASSWORD_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="password" name="config[smtp_password]" value="<?php echo $new['smtp_password']; ?>" size="25" maxlength="255" /></td>
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
		<td class="row1" width="50%"><b><?php echo $user->lang['SERVER_NAME']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SERVER_NAME_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="255" size="40" name="config[server_name]" value="<?php echo $new['server_name']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SERVER_PORT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SERVER_PORT_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="5" size="5" name="config[server_port]" value="<?php echo $new['server_port']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SCRIPT_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SCRIPT_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="255" name="config[script_path]" value="<?php echo $new['script_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['IP_VALID']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['IP_VALID_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[ip_check]" value="4" <?php echo $ip_all; ?> /> <?php echo $user->lang['ALL']; ?>&nbsp;&nbsp;<input type="radio" name="config[ip_check]" value="3" <?php echo $ip_classc; ?> /> <?php echo $user->lang['CLASS_C']; ?>&nbsp;&nbsp;<input type="radio" name="config[ip_check]" value="2" <?php echo $ip_classb; ?> /> <?php echo $user->lang['CLASS_B']; ?>&nbsp;&nbsp;<input type="radio" name="config[ip_check]" value="0" <?php echo $ip_none; ?> /> <?php echo $user->lang['NONE']; ?>&nbsp;&nbsp;</td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['BROWSER_VALID']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BROWSER_VALID_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[browser_check]" value="1" <?php echo $browser_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[browser_check]" value="0" <?php echo $browser_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ENABLE_GZIP']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[gzip_compress]" value="1" <?php echo $gzip_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[gzip_compress]" value="0" <?php echo $gzip_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SMILIES_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SMILIES_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="config[smilies_path]" value="<?php echo $new['smilies_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ICONS_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ICONS_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="config[icons_path]" value="<?php echo $new['icons_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['UPLOAD_ICONS_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['UPLOAD_ICONS_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="config[upload_icons_path]" value="<?php echo $new['upload_icons_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['RANKS_PATH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['RANKS_PATH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="20" maxlength="255" name="config[ranks_path]" value="<?php echo $new['ranks_path']; ?>" /></td>
	</tr>
<?php

		break;

	case 'load':

		$tplcompile_yes = ($new['load_tplcompile']) ? 'checked="checked"' : '';
		$tplcompile_no = (!$new['load_tplcompile']) ? 'checked="checked"' : '';
		$load_db_track_yes = ($new['load_db_track']) ? 'checked="checked"' : '';
		$load_db_track_no = (!$new['load_db_track']) ? 'checked="checked"' : '';
		$load_db_lastread_yes = ($new['load_db_lastread']) ? 'checked="checked"' : '';
		$load_db_lastread_no = (!$new['load_db_lastread']) ? 'checked="checked"' : '';
		$load_online_yes = ($new['load_online']) ? 'checked="checked"' : '';
		$load_online_no = (!$new['load_online']) ? 'checked="checked"' : '';
		$load_onlinetrack_yes = ($new['load_onlinetrack']) ? 'checked="checked"' : '';
		$load_onlinetrack_no = (!$new['load_onlinetrack']) ? 'checked="checked"' : '';
		$load_birthdays_yes = ($new['load_birthdays']) ? 'checked="checked"' : '';
		$load_birthdays_no = (!$new['load_birthdays']) ? 'checked="checked"' : '';
		$moderators_yes = ($new['load_moderators']) ? 'checked="checked"' : '';
		$moderators_no = (!$new['load_moderators']) ? 'checked="checked"' : '';
		$jumpbox_yes = ($new['load_jumpbox']) ? 'checked="checked"' : '';
		$jumpbox_no = (!$new['load_jumpbox']) ? 'checked="checked"' : '';
		$search_yes = ($new['load_search']) ? 'checked="checked"' : '';
		$search_no = (!$new['load_search']) ? 'checked="checked"' : '';
		$search_update_yes = ($new['load_search_upd']) ? 'checked="checked"' : '';
		$search_update_no = (!$new['load_search_upd']) ? 'checked="checked"' : '';
		$search_phrase_yes = ($new['load_search_phr']) ? 'checked="checked"' : '';
		$search_phrase_no = (!$new['load_search_phr']) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1" width="50%"><b><?php echo $user->lang['LIMIT_LOAD']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['LIMIT_LOAD_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="4" name="config[limit_load]" value="<?php echo $new['limit_load']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SESSION_LENGTH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SESSION_LENGTH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="5" size="5" name="config[session_length]" value="<?php echo $new['session_length']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['LIMIT_SESSIONS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['LIMIT_SESSIONS_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="4" name="config[active_sessions]" value="<?php echo $new['active_sessions']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_POST_MARKING']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['YES_POST_MARKING_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[load_db_track]" value="1"<?php echo $load_db_track_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp;&nbsp;<input type="radio" name="config[load_db_track]" value="0" <?php echo $load_db_track_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_READ_MARKING']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['YES_READ_MARKING_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[load_db_lastread]" value="1"<?php echo $load_db_lastread_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp;&nbsp;<input type="radio" name="config[load_db_lastread]" value="0" <?php echo $load_db_lastread_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_ONLINE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['YES_ONLINE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[load_online]" value="1"<?php echo $load_online_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp;&nbsp;<input type="radio" name="config[load_online]" value="0" <?php echo $load_online_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_ONLINE_TRACK']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['YES_ONLINE_TRACK_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[load_onlinetrack]" value="1"<?php echo $load_onlinetrack_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp;&nbsp;<input type="radio" name="config[load_onlinetrack]" value="0" <?php echo $load_onlinetrack_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ONLINE_LENGTH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ONLINE_LENGTH_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="4" maxlength="3" name="config[load_online_time]" value="<?php echo $new['load_online_time']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_BIRTHDAYS']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[load_birthdays]" value="1"<?php echo $load_birthdays_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp;&nbsp;<input type="radio" name="config[load_birthdays]" value="0" <?php echo $load_birthdays_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_MODERATORS']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[load_moderators]" value="1"<?php echo $moderators_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp;&nbsp;<input type="radio" name="config[load_moderators]" value="0" <?php echo $moderators_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_JUMPBOX']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[load_jumpbox]" value="1"<?php echo $jumpbox_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp;&nbsp;<input type="radio" name="config[load_jumpbox]" value="0" <?php echo $jumpbox_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_SEARCH']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['YES_SEARCH_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[load_search]" value="1"<?php echo $search_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp;&nbsp;<input type="radio" name="config[load_search]" value="0" <?php echo $search_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SEARCH_INTERVAL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SEARCH_INTERVAL_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="4" name="config[search_interval]" value="<?php echo $new['search_interval']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MIN_SEARCH_CHARS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MIN_SEARCH_CHARS_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="3" name="config[min_search_chars]" value="<?php echo $new['min_search_chars']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAX_SEARCH_CHARS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MAX_SEARCH_CHARS_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="3" name="config[max_search_chars]" value="<?php echo $new['max_search_chars']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['YES_SEARCH_UPDATE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['YES_SEARCH_UPDATE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[load_search_upd]" value="1"<?php echo $search_update_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp;&nbsp;<input type="radio" name="config[load_search_upd]" value="0" <?php echo $search_update_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<!--tr>
		<td class="row1"><b><?php echo $user->lang['YES_SEARCH_PHRASE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['YES_SEARCH_PHRASE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[load_search_phr]" value="1"<?php echo $search_phrase_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp;&nbsp;<input type="radio" name="config[load_search_phr]" value="0" <?php echo $search_phrase_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr-->
	<tr>
		<td class="row1"><b><?php echo $user->lang['RECOMPILE_TEMPLATES']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['RECOMPILE_TEMPLATES_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="config[load_tplcompile]" value="1"<?php echo $tplcompile_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp;&nbsp;<input type="radio" name="config[load_tplcompile]" value="0" <?php echo $tplcompile_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php

		break;

	case 'message':

		$yes_no_switches = array('auth_html_pm', 'auth_bbcode_pm', 'auth_smilies_pm', 'auth_download_pm', 'allow_sig_pm', 'enable_karma_pm', 'auth_report_pm', 'auth_quote_pm', 'print_pm', 'email_pm', 'forward_pm', 'auth_img_pm', 'auth_flash_pm', 'enable_pm_icons', 'allow_mass_pm');

		foreach ($yes_no_switches as $switch)
		{
			$switch_var = str_replace(array('allow_', 'auth_'), array('', ''), $switch);
			${$switch_var . '_yes'} = ($new[$switch]) ? ' checked="checked"' : '';
			${$switch_var . '_no'} = (!$new[$switch]) ? ' checked="checked"' : '';
		}
		
?>

	<tr>
		<td class="row1"><b><?php echo $user->lang['BOXES_MAX']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BOXES_MAX_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="4" size="4" name="config[pm_max_boxes]" value="<?php echo $new['pm_max_boxes']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['BOXES_LIMIT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BOXES_LIMIT_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="4" size="4" name="config[pm_max_msgs]" value="<?php echo $new['pm_max_msgs']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FULL_FOLDER_ACTION']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FULL_FOLDER_ACTION_EXPLAIN']; ?></span></td>
		<td class="row2"><select name="config[full_folder_action]"><option value="1"><?php echo $user->lang['DELETE_OLD_MESSAGES']; ?></option><option value="2"><?php echo $user->lang['HOLD_NEW_MESSAGES']; ?></option></select></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['PM_EDIT_TIME']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['PM_EDIT_TIME_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" maxlength="3" size="3" name="config[pm_edit_time]" value="<?php echo $new['pm_edit_time']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_MASS_PM']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[allow_mass_pm]" value="1" <?php echo $mass_pm_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_mass_pm]" value="0" <?php echo $mass_pm_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_HTML_PM']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[auth_html_pm]" value="1" <?php echo $html_pm_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[auth_html_pm]" value="0" <?php echo $html_pm_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_BBCODE_PM']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[auth_bbcode_pm]" value="1" <?php echo $bbcode_pm_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[auth_bbcode_pm]" value="0" <?php echo $bbcode_pm_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_SMILIES_PM']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[auth_smilies_pm]" value="1" <?php echo $smilies_pm_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[auth_smilies_pm]" value="0" <?php echo $smilies_pm_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['AUTH_DOWNLOAD_PM']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[auth_download_pm]" value="1" <?php echo $download_pm_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[auth_download_pm]" value="0" <?php echo $download_pm_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ALLOW_SIG_PM']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[allow_sig_pm]" value="1" <?php echo $sig_pm_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[allow_sig_pm]" value="0" <?php echo $sig_pm_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<!--	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['ENABLE_KARMA_PM']; ?>: </td>
		<td class="row2"><input type="radio" name="config[enable_karma_pm]" value="1"<?php echo $enable_karma_pm_yes ?> /> <?php echo $user->lang['YES'] ?>&nbsp;&nbsp;<input type="radio" name="config[enable_karma_pm]" value="0" <?php echo $enable_karma_pm_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>//-->
	<tr>
		<td class="row1"><b><?php echo $user->lang['AUTH_REPORT_PM']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[auth_report_pm]" value="1" <?php echo $report_pm_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[auth_report_pm]" value="0" <?php echo $report_pm_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['AUTH_QUOTE_PM']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[auth_quote_pm]" value="1" <?php echo $quote_pm_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[auth_quote_pm]" value="0" <?php echo $quote_pm_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['PRINT_PM']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[print_pm]" value="1" <?php echo $print_pm_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[print_pm]" value="0" <?php echo $print_pm_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['EMAIL_PM']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[email_pm]" value="1" <?php echo $email_pm_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[email_pm]" value="0" <?php echo $email_pm_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FORWARD_PM']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[forward_pm]" value="1" <?php echo $forward_pm_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[forward_pm]" value="0" <?php echo $forward_pm_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['AUTH_IMG_PM']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[auth_img_pm]" value="1" <?php echo $img_pm_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[auth_img_pm]" value="0" <?php echo $img_pm_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['AUTH_FLASH_PM']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[auth_flash_pm]" value="1" <?php echo $flash_pm_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[auth_flash_pm]" value="0" <?php echo $flash_pm_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ENABLE_PM_ICONS']; ?>: </b></td>
		<td class="row2"><input type="radio" name="config[enable_pm_icons]" value="1" <?php echo $enable_pm_icons_yes; ?> /> <?php echo $user->lang['YES']; ?>&nbsp;&nbsp;<input type="radio" name="config[enable_pm_icons]" value="0" <?php echo $enable_pm_icons_no; ?> /> <?php echo $user->lang['NO']; ?></td>
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
		<td class="row1" width="50%"><b><?php echo $user->lang['AUTH_METHOD']; ?>: </b></td>
		<td class="row2"><select name="config[auth_method]"><?php echo $auth_select; ?></select></td>
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
						// Check if we need to create config fields for this plugin
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
/*
	case 'karma':

		$enable_karma_yes = ($new['enable_karma']) ? 'checked="checked"' : '';
		$enable_karma_no = (!$new['enable_karma']) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1" width="50%"><?php echo $user->lang['ENABLE_KARMA']; ?>: </td>
		<td class="row2"><input type="radio" name="config[enable_karma]" value="1"<?php echo $enable_karma_yes ?> /><?php echo $user->lang['YES'] ?>&nbsp;&nbsp;<input type="radio" name="config[enable_karma]" value="0" <?php echo $enable_karma_no ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MIN_RATINGS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MIN_RATINGS_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="5" name="config[min_ratings]" value="<?php echo $new['min_ratings']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['KARMA_HIST_WEIGHT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['KARMA_HIST_WEIGHT_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="5" name="config[karma_hist_weight]" value="<?php echo $new['karma_hist_weight']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['KARMA_DAY_WEIGHT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['KARMA_DAY_WEIGHT_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="5" name="config[karma_day_weight]" value="<?php echo $new['karma_30_weight']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['KARMA_REG_WEIGHT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['KARMA_REG_WEIGHT_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="5" name="config[karma_reg_weight]" value="<?php echo $new['karma_reg_weight']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['KARMA_POST_WEIGHT']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['KARMA_POST_WEIGHT_EXPLAIN']; ?></span</td>
		<td class="row2"><input class="post" type="text" size="3" maxlength="5" name="config[karma_post_weight]" value="<?php echo $new['karma_post_weight']; ?>" /></td>
	</tr>
<?php

		break;
*/
}

?>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp;&nbsp;<input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
	</tr>
</table></form>

<?php

adm_page_footer();

?>