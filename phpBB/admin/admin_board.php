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

if ( !empty($setmodules) )
{
	if ( !$acl->get_acl_admin('general') )
	{
		return;
	}

	$file = basename(__FILE__);
	$module['General']['Avatar_settings'] = "$file$SID&mode=avatar";
	$module['General']['Cookie_settings'] = "$file$SID&mode=cookie";
	$module['General']['Board_defaults'] = "$file$SID&mode=default";
	$module['General']['Board_settings'] = "$file$SID&mode=setting";
	$module['General']['Email_settings'] = "$file$SID&mode=email";
	$module['General']['Server_settings'] = "$file$SID&mode=server";
	$module['Users']['Defaults'] = "$file$SID&mode=userdefs";
	return;
}

//
// Let's set the root dir for phpBB
//
define('IN_PHPBB', 1);
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

if ( !$acl->get_acl_admin('general') )
{
	message_die(MESSAGE, $lang['No_admin']);
}

if ( isset($HTTP_POST_VARS['mode']) || isset($HTTP_GET_VARS['mode']) )
{
	$mode = ( isset($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	$mode = '';
}

//
// Pull all config data
//
switch ( $mode )
{
	case 'userdefs':
		$sql = "SELECT *
			FROM " . CONFIG_USER_TABLE;
		$result = $db->sql_query($sql);
		break;

	default:
		$sql = "SELECT *
			FROM " . CONFIG_TABLE;
		$result = $db->sql_query($sql);

		while ( $row = $db->sql_fetchrow($result) )
		{
			$config_name = $row['config_name'];
			$config_value = $row['config_value'];
			$default_config[$config_name] = $config_value;
			
			$new[$config_name] = ( isset($HTTP_POST_VARS[$config_name]) ) ? $HTTP_POST_VARS[$config_name] : $default_config[$config_name];

			if ( isset($HTTP_POST_VARS['submit']) )
			{
				$sql = "UPDATE " . CONFIG_TABLE . " SET
					config_value = '" . str_replace("\'", "''", $new[$config_name]) . "'
					WHERE config_name = '$config_name'";
				$db->sql_query($sql);
			}
		}
		break;
}

if ( isset($HTTP_POST_VARS['submit']) )
{
	add_admin_log('log_' . $mode . '_config');
	$message = $lang['Config_updated'] . "<br /><br />" . sprintf($lang['Click_return_config'], '<a href="' . "admin_board.$phpEx$SID&amp;mode=$mode" . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_admin_index'], '<a href="' . "index.$phpEx$SID?pane=right" . '">', '</a>');

	message_die(MESSAGE, $message);
}

//
// Which title?
//
switch ( $mode )
{
	case 'cookie':
		$l_title = 'Cookie_settings';
		break;
	case 'avatar':
		$l_title = 'Avatar_settings';
		break;
	case 'default':
		$l_title = 'Board_defaults';
		break;
	case 'setting':
		$l_title = 'Board_settings';
		break;
	case 'email':
		$l_title = 'Email_settings';
		break;
	case 'server':
		$l_title = 'Server_settings';
		break;
	default:
		return;
}

page_header($lang[$l_title]);

?>

<h1><?php echo $lang[$l_title]; ?></h1>

<p><?php echo $lang[$l_title . '_explain']; ?></p>

<form action="<?php echo "admin_board.$phpEx$SID&amp;mode=$mode"; ?>" method="post"><table class="bg" width="95%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $lang[$l_title]; ?></th>
	</tr>
<?php

//
// Output relevant page
//
switch ( $mode )
{
	case 'cookie':

		$cookie_secure_yes = ( $new['cookie_secure'] ) ? 'checked="checked"' : '';
		$cookie_secure_no = ( !$new['cookie_secure'] ) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1" width="50%"><?php echo $lang['Cookie_domain']; ?></td>
		<td class="row2"><input type="text" maxlength="255" name="cookie_domain" value="<?php echo $new['cookie_domain']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Cookie_name']; ?></td>
		<td class="row2"><input type="text" maxlength="16" name="cookie_name" value="<?php echo $new['cookie_name']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Cookie_path']; ?></td>
		<td class="row2"><input type="text" maxlength="255" name="cookie_path" value="<?php echo $new['cookie_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Cookie_secure']; ?><br /><span class="gensmall"><?php echo $lang['Cookie_secure_explain']; ?></span></td>
		<td class="row2"><input type="radio" name="cookie_secure" value="0"<?php echo $cookie_secure_no; ?> /><?php echo $lang['Disabled']; ?>&nbsp; &nbsp;<input type="radio" name="cookie_secure" value="1"<?php echo $cookie_secure_yes; ?> /><?php echo $lang['Enabled']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Session_length']; ?></td>
		<td class="row2"><input type="text" maxlength="5" size="5" name="session_length" value="<?php echo $new['session_length']; ?>" /></td>
	</tr>
<?php

		break;

	case 'avatar':

		$avatars_local_yes = ( $new['allow_avatar_local'] ) ? 'checked="checked"' : '';
		$avatars_local_no = ( !$new['allow_avatar_local'] ) ? 'checked="checked"' : '';
		$avatars_remote_yes = ( $new['allow_avatar_remote'] ) ? 'checked="checked"' : '';
		$avatars_remote_no = ( !$new['allow_avatar_remote'] ) ? 'checked="checked"' : '';
		$avatars_upload_yes = ( $new['allow_avatar_upload'] ) ? 'checked="checked"' : '';
		$avatars_upload_no = ( !$new['allow_avatar_upload'] ) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1" width="50%"><?php echo $lang['Allow_local']; ?></td>
		<td class="row2"><input type="radio" name="allow_avatar_local" value="1"<?php echo $avatars_local_yes; ?> /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="allow_avatar_local" value="0"<?php echo $avatars_local_no; ?>  /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Allow_remote']; ?> <br /><span class="gensmall"><?php echo $lang['Allow_remote_explain']; ?></span></td>
		<td class="row2"><input type="radio" name="allow_avatar_remote" value="1"<?php echo $avatars_remote_yes; ?>  /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="allow_avatar_remote" value="0"<?php echo $avatars_remote_no; ?>  /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Allow_upload']; ?></td>
		<td class="row2"><input type="radio" name="allow_avatar_upload" value="1"<?php echo $avatars_upload_yes; ?>  /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="allow_avatar_upload" value="0"<?php echo $avatars_upload_no; ?>  /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Max_filesize']; ?><br /><span class="gensmall"><?php echo $lang['Max_filesize_explain']; ?></span></td>
		<td class="row2"><input type="text" size="4" maxlength="10" name="avatar_filesize" value="<?php echo $new['avatar_filesize']; ?>" /> Bytes</td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Max_avatar_size']; ?> <br /><span class="gensmall"><?php echo $lang['Max_avatar_size_explain']; ?></span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="avatar_max_height" value="<?php echo $new['avatar_max_height']; ?>" /> x <input type="text" size="3" maxlength="4" name="avatar_max_width" value="<?php echo $new['avatar_max_width']; ?>"></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Avatar_storage_path']; ?> <br /><span class="gensmall"><?php echo $lang['Avatar_storage_path_explain']; ?></span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="avatar_path" value="<?php echo $new['avatar_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Avatar_gallery_path']; ?> <br /><span class="gensmall"><?php echo $lang['Avatar_gallery_path_explain']; ?></span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="avatar_gallery_path" value="<?php echo $new['avatar_gallery_path']; ?>" /></td>
	</tr>
<?php

		break;

	case 'default':

		$style_select = style_select($new['default_style'], 'default_style', '../templates');
		$lang_select = language_select($new['default_lang'], 'default_lang', '../language');
		$timezone_select = tz_select($new['board_timezone'], 'board_timezone');

		$override_user_style_yes = ( $new['override_user_style'] ) ? 'checked="checked"' : '';
		$override_user_style_no = ( !$new['override_user_style'] ) ? 'checked="checked"' : '';

		$topic_notify_yes = ( $new['allow_topic_notify'] ) ? 'checked="checked"' : '';
		$topic_notify_no = ( !$new['allow_topic_notify'] ) ? 'checked="checked"' : '';

		$forum_notify_yes = ( $new['allow_forum_notify'] ) ? 'checked="checked"' : '';
		$forum_notify_no = ( !$new['allow_forum_notify'] ) ? 'checked="checked"' : '';

		$html_yes = ( $new['allow_html'] ) ? 'checked="checked"' : '';
		$html_no = ( !$new['allow_html'] ) ? 'checked="checked"' : '';

		$bbcode_yes = ( $new['allow_bbcode'] ) ? 'checked="checked"' : '';
		$bbcode_no = ( !$new['allow_bbcode'] ) ? 'checked="checked"' : '';

		$smile_yes = ( $new['allow_smilies'] ) ? 'checked="checked"' : '';
		$smile_no = ( !$new['allow_smilies'] ) ? 'checked="checked"' : '';

		$sig_yes = ( $new['allow_sig'] ) ? 'checked="checked"' : '';
		$sig_no = ( !$new['allow_sig'] ) ? 'checked="checked"' : '';

		$namechange_yes = ( $new['allow_namechange'] ) ? 'checked="checked"' : '';
		$namechange_no = ( !$new['allow_namechange'] ) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1" width="50%"><?php echo $lang['Default_style']; ?></td>
		<td class="row2"><?php echo $style_select; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Override_style']; ?><br /><span class="gensmall"><?php echo $lang['Override_style_explain']; ?></span></td>
		<td class="row2"><input type="radio" name="override_user_style" value="1" <?php echo $override_user_style_yes; ?> /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="override_user_style" value="0" <?php echo $override_user_style_no; ?> /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Default_language']; ?></td>
		<td class="row2"><?php echo $lang_select; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Date_format']; ?><br /><span class="gensmall"><?php echo $lang['Date_format_explain']; ?></span></td>
		<td class="row2"><input type="text" name="default_dateformat" value="<?php echo $new['default_dateformat']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['System_timezone']; ?></td>
		<td class="row2"><?php echo $timezone_select; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Char_limit']; ?><br /><span class="gensmall"><?php echo $lang['Char_limit_explain']; ?></span</td>
		<td class="row2"><input type="text" size="4" maxlength="4" name="max_post_chars" value="<?php echo $new['max_post_chars']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Smilies_limit']; ?><br /><span class="gensmall"><?php echo $lang['Smilies_limit_explain']; ?></span</td>
		<td class="row2"><input type="text" size="4" maxlength="4" name="max_post_smilies" value="<?php echo $new['max_post_smilies']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Allow_topic_notify']; ?></td>
		<td class="row2"><input type="radio" name="allow_topic_notify" value="1" <?php echo $topic_notify_yes; ?> /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="allow_topic_notify" value="0" <?php echo $topic_notify_no; ?> /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Allow_forum_notify']; ?></td>
		<td class="row2"><input type="radio" name="allow_forum_notify" value="1" <?php echo $forum_notify_yes; ?> /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="allow_forum_notify" value="0" <?php echo $forum_notify_no; ?> /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Allow_name_change']; ?></td>
		<td class="row2"><input type="radio" name="allow_namechange" value="1" <?php echo $namechange_yes; ?> /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="allow_namechange" value="0" <?php echo $namechange_no; ?> /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Allow_HTML']; ?></td>
		<td class="row2"><input type="radio" name="allow_html" value="1" <?php echo $html_yes; ?> /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="allow_html" value="0" <?php echo $html_no; ?> /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Allowed_tags']; ?><br /><span class="gensmall"><?php echo $lang['Allowed_tags_explain']; ?></span></td>
		<td class="row2"><input type="text" size="30" maxlength="255" name="allow_html_tags" value="<?php echo $new['allow_html_tags']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Allow_BBCode']; ?></td>
		<td class="row2"><input type="radio" name="allow_bbcode" value="1" <?php echo $bbcode_yes; ?> /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="allow_bbcode" value="0" <?php echo $bbcode_no; ?> /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Allow_smilies']; ?></td>
		<td class="row2"><input type="radio" name="allow_smilies" value="1" <?php echo $smile_yes; ?> /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="allow_smilies" value="0" <?php echo $smile_no; ?> /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Allow_sig']; ?></td>
		<td class="row2"><input type="radio" name="allow_sig" value="1" <?php echo $sig_yes; ?> /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="allow_sig" value="0" <?php echo $sig_no; ?> /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Max_sig_length']; ?><br /><span class="gensmall"><?php echo $lang['Max_sig_length_explain']; ?></span></td>
		<td class="row2"><input type="text" size="5" maxlength="4" name="max_sig_chars" value="<?php echo $new['max_sig_chars']; ?>" /></td>
	</tr>
<?php

		break;

	case 'setting':

		$disable_board_yes = ( $new['board_disable'] ) ? 'checked="checked"' : '';
		$disable_board_no = ( !$new['board_disable'] ) ? 'checked="checked"' : '';

		$coppa_enable_yes = ( $new['coppa_enable'] ) ? 'checked="checked"' : '';
		$coppa_enable_no = ( !$new['coppa_enable'] ) ? 'checked="checked"' : '';

		$activation_none = ( $new['require_activation'] == USER_ACTIVATION_NONE ) ? 'checked="checked"' : '';
		$activation_user = ( $new['require_activation'] == USER_ACTIVATION_SELF ) ? 'checked="checked"' : '';
		$activation_admin = ( $new['require_activation'] == USER_ACTIVATION_ADMIN ) ? 'checked="checked"' : '';
		$activation_disable = ( $new['require_activation'] == USER_ACTIVATION_DISABLE ) ? 'checked="checked"' : '';

		$gzip_yes = ( $new['gzip_compress'] ) ? 'checked="checked"' : '';
		$gzip_no = ( !$new['gzip_compress'] ) ? 'checked="checked"' : '';

		$privmsg_on = ( !$new['privmsg_disable'] ) ? 'checked="checked"' : '';
		$privmsg_off = ( $new['privmsg_disable'] ) ? 'checked="checked"' : '';

		$prune_yes = ( $new['prune_enable'] ) ? 'checked="checked"' : '';
		$prune_no = ( !$new['prune_enable'] ) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1" width="50%"><?php echo $lang['Site_name']; ?></td>
		<td class="row2"><input type="text" size="40" maxlength="255" name="sitename" value="<?php echo htmlentities($new['sitename']); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Site_desc']; ?></td>
		<td class="row2"><input type="text" size="40" maxlength="255" name="site_desc" value="<?php echo htmlentities($new['site_desc']); ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Board_disable']; ?><br /><span class="gensmall"><?php echo $lang['Board_disable_explain']; ?></span></td>
		<td class="row2"><input type="radio" name="board_disable" value="1" <?php echo $disable_board_yes; ?> /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="board_disable" value="0" <?php echo $disable_board_no; ?> /> <?php echo $lang['No']; ?><br /><input type="text" name="board_disable_msg" maxlength="255" size="40" value="<?php echo $new['board_disable_msg']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Limit_load']; ?><br /><span class="gensmall"><?php echo $lang['Limit_load_explain']; ?></span></td>
		<td class="row2"><input type="text" size="4" maxlength="4" name="limit_load" value="<?php echo $new['limit_load']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Limit_sessions']; ?><br /><span class="gensmall"><?php echo $lang['Limit_sessions_explain']; ?></span></td>
		<td class="row2"><input type="text" size="4" maxlength="4" name="active_sessions" value="<?php echo $new['active_sessions']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Enable_gzip']; ?></td>
		<td class="row2"><input type="radio" name="gzip_compress" value="1" <?php echo $gzip_yes; ?> /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="gzip_compress" value="0" <?php echo $gzip_no; ?> /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Enable_prune']; ?></td>
		<td class="row2"><input type="radio" name="prune_enable" value="1" <?php echo $prune_yes; ?> /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="prune_enable" value="0" <?php echo $prune_no; ?> /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Acct_activation']; ?><br /><span class="gensmall"><?php echo $lang['Acct_activation_explain']; ?></span></td>
		<td class="row2"><input type="radio" name="require_activation" value="<?php echo USER_ACTIVATION_NONE; ?>" <?php echo $activation_none; ?> /><?php echo $lang['Acc_None']; ?>&nbsp; &nbsp;<input type="radio" name="require_activation" value="<?php echo USER_ACTIVATION_SELF; ?>" <?php echo $activation_user; ?> /><?php echo $lang['Acc_User']; ?>&nbsp; &nbsp;<input type="radio" name="require_activation" value="<?php echo USER_ACTIVATION_ADMIN; ?>" <?php echo $activation_admin; ?> /><?php echo $lang['Acc_Admin']; ?>&nbsp; &nbsp;<input type="radio" name="require_activation" value="<?php echo USER_ACTIVATION_DISABLE; ?>" <?php echo $activation_disable; ?> /><?php echo $lang['Acc_Disable']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Enable_COPPA']; ?><br /><span class="gensmall"><?php echo $lang['Enable_COPPA_explain']; ?></span></td>
		<td class="row2"><input type="radio" name="coppa_enable" value="1" <?php echo $coppa_enable_yes; ?> /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="coppa_enable" value="0" <?php echo $coppa_enable_no; ?> /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['COPPA_fax']; ?></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="coppa_fax" value="<?php echo $new['coppa_fax']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['COPPA_mail']; ?><br /><span class="gensmall"><?php echo $lang['COPPA_mail_explain']; ?></span></td>
		<td class="row2"><textarea name="coppa_mail" rows="5" cols="40"><?php echo $new['coppa_mail']; ?></textarea></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Private_Messaging']; ?></td>
		<td class="row2"><input type="radio" name="privmsg_disable" value="0" <?php echo $privmsg_on; ?> /><?php echo $lang['Enabled']; ?>&nbsp; &nbsp;<input type="radio" name="privmsg_disable" value="1" <?php echo $privmsg_off; ?> /><?php echo $lang['Disabled']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Boxes_max']; ?><br /><span class="gensmall"><?php echo $lang['Boxes_max_explain']; ?></span></td>
		<td class="row2"><input type="text" maxlength="4" size="4" name="pm_max_boxes" value="<?php echo $new['pm_max_boxes']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Boxes_limit']; ?><br /><span class="gensmall"><?php echo $lang['Boxes_limit_explain']; ?></span></td>
		<td class="row2"><input type="text" maxlength="4" size="4" name="pm_max_msgs" value="<?php echo $new['pm_max_msgs']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Flood_Interval']; ?> <br /><span class="gensmall"><?php echo $lang['Flood_Interval_explain']; ?></span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="flood_interval" value="<?php echo $new['flood_interval']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Search_Interval']; ?> <br /><span class="gensmall"><?php echo $lang['Search_Interval_explain']; ?></span></td>
		<td class="row2"><input type="text" size="3" maxlength="4" name="search_interval" value="<?php echo $new['search_interval']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Smilies_path']; ?> <br /><span class="gensmall"><?php echo $lang['Smilies_path_explain']; ?></span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="smilies_path" value="<?php echo $new['smilies_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Icons_path']; ?> <br /><span class="gensmall"><?php echo $lang['Icons_path_explain']; ?></span></td>
		<td class="row2"><input type="text" size="20" maxlength="255" name="icons_path" value="<?php echo $new['icons_path']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Topics_per_page']; ?></td>
		<td class="row2"><input type="text" name="topics_per_page" size="3" maxlength="4" value="<?php echo $new['topics_per_page']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Posts_per_page']; ?></td>
		<td class="row2"><input type="text" name="posts_per_page" size="3" maxlength="4" value="<?php echo $new['posts_per_page']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Hot_threshold']; ?></td>
		<td class="row2"><input type="text" name="hot_threshold" size="3" maxlength="4" value="<?php echo $new['hot_threshold']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Max_poll_options']; ?></td>
		<td class="row2"><input type="text" name="max_poll_options" size="4" maxlength="4" value="<?php echo $new['max_poll_options']; ?>" /></td>
	</tr>
<?php

		break;

	case 'email':

		$board_email_form_yes = ( $new['board_email_form'] ) ? 'checked="checked"' : '';
		$board_email_form_no = ( !$new['board_email_form'] ) ? 'checked="checked"' : '';

		$smtp_yes = ( $new['smtp_delivery'] ) ? 'checked="checked"' : '';
		$smtp_no = ( !$new['smtp_delivery'] ) ? 'checked="checked"' : '';

?>
	<tr>
		<td class="row1"><?php echo $lang['Board_email_form']; ?><br /><span class="gensmall"><?php echo $lang['Board_email_form_explain']; ?></span></td>
		<td class="row2"><input type="radio" name="board_email_form" value="1" <?php echo $board_email_form_yes; ?> /> <?php echo $lang['Enabled']; ?>&nbsp;&nbsp;<input type="radio" name="board_email_form" value="0" <?php echo $board_email_form_no; ?> /> <?php echo $lang['Disabled']; ?></td>
	</tr>
	<tr>
		<td class="row1" width="50%"><?php echo $lang['Admin_email']; ?></td>
		<td class="row2"><input type="text" size="25" maxlength="100" name="board_email" value="<?php echo $new['board_email']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Email_sig']; ?><br /><span class="gensmall"><?php echo $lang['Email_sig_explain']; ?></span></td>
		<td class="row2"><textarea name="board_email_sig" rows="5" cols="30"><?php echo $new['board_email_sig']; ?></textarea></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Use_SMTP']; ?><br /><span class="gensmall"><?php echo $lang['Use_SMTP_explain']; ?></span></td>
		<td class="row2"><input type="radio" name="smtp_delivery" value="1" <?php echo $smtp_yes; ?> /> <?php echo $lang['Yes']; ?>&nbsp;&nbsp;<input type="radio" name="smtp_delivery" value="0" <?php echo $smtp_no; ?> /> <?php echo $lang['No']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['SMTP_server']; ?></td>
		<td class="row2"><input type="text" name="smtp_host" value="<?php echo $new['smtp_host']; ?>" size="25" maxlength="50" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['SMTP_username']; ?><br /><span class="gensmall"><?php echo $lang['SMTP_username_explain']; ?></span></td>
		<td class="row2"><input type="text" name="smtp_username" value="<?php echo $new['smtp_username']; ?>" size="25" maxlength="255" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['SMTP_password']; ?><br /><span class="gensmall"><?php echo $lang['SMTP_password_explain']; ?></span></td>
		<td class="row2"><input type="password" name="smtp_password" value="<?php echo $new['smtp_password']; ?>" size="25" maxlength="255" /></td>
	</tr>
<?php

		break;

	case 'server':

?>
	<tr>
		<td class="row1"><?php echo $lang['Server_name']; ?><br /><span class="gensmall"><?php echo $lang['Server_name_explain']; ?></span></td>
		<td class="row2"><input type="text" maxlength="255" size="40" name="server_name" value="<?php echo $new['server_name']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Server_port']; ?><br /><span class="gensmall"><?php echo $lang['Server_port_explain']; ?></span></td>
		<td class="row2"><input type="text" maxlength="5" size="5" name="server_port" value="<?php echo $new['server_port']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Script_path']; ?><br /><span class="gensmall"><?php echo $lang['Script_path_explain']; ?></span></td>
		<td class="row2"><input type="text" maxlength="255" name="script_path" value="<?php echo $new['script_path']; ?>" /></td>
	</tr>
<?php

		break;

}

?>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="submit" value="<?php echo $lang['Submit']; ?>" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="<?php echo $lang['Reset']; ?>" class="liteoption" /></td>
	</tr>
</table></form>

<?php

page_footer();

?>