<?php
/***************************************************************************
 *                            usercp_register.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *
 ***************************************************************************/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
	exit;
}

//
if ($mode == 'register' && $config['require_activation'] == USER_ACTIVATION_DISABLE)
{
	trigger_error($user->lang['Cannot_register']);
}

//
$error = FALSE;

$page_title = $user->lang['Register'];

// Class for handling the manipulation of user data
$userdata = new userdata();

if ($mode == 'register')
{
	if(!isset($_POST['agree']) && !isset($_GET['agree']) && !isset($_POST['coppa_over_13']) && !isset($_GET['coppa_over_13'])  && !isset($_POST['coppa_under_13']) && !isset($_GET['coppa_under_13']) && !$_POST['agreed'])
	{
		$agreed = FALSE;
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		// Does this need to be function anymore?
		// Need to remember that COPPA can be disabled
		show_coppa();

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
	else
	{
		$agreed = TRUE;
	}
}
	
$coppa = (empty($_POST['coppa_under_13']) && empty($_GET['coppa_under_13'])) ? 0 : TRUE;


// Need to look at better handling of these vars ... although in practice
// they will be defined on appropriate usercp pages in time I guess 2.0.x
// was incredibly messy in this respect


// Check and initialize some variables if needed
if (isset($_POST['submit']))
{
		
	// User registration is now handled by the userdata class which is in sessions.php.
	$new_user_data = $userdata->add_new_user($_POST, $coppa);
	if($new_user_data['user_id'])
	{
		if ($config['require_activation'] == USER_ACTIVATION_NONE)
		{
			set_config('newest_user_id', $new_user_data['user_id'], TRUE);
			set_config('newest_username', $new_user_data['username'], TRUE);
			set_config('num_users', $config['num_users'] + 1, TRUE);
		}
		
		trigger_error($new_user_data['message']);
	}
	else
	{
		trigger_error($new_user_data['message']);
	}
	
}	 // End of submit




if ($userdata->error)
{
	//
	// If an error occured we need to stripslashes on returned data
	//
	$username = stripslashes($username);
	$email = stripslashes($email);
	$password = '';
	$password_confirm = '';

	$user_lang = stripslashes($user_lang);
	$user_dateformat = stripslashes($user_dateformat);
}

//
// Default pages
//



	if (!isset($coppa))
	{
		$coppa = FALSE;
	}

	if (!isset($user_template))
	{
		$selected_template = $config['system_template'];
	}

	$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" /><input type="hidden" name="agreed" value="true" /><input type="hidden" name="coppa" value="' . $coppa . '" />';
	
	
	if (!empty($user_avatar_local))
	{
		$s_hidden_fields .= '<input type="hidden" name="avatarlocal" value="' . $user_avatar_local . '" />';
	}

	$html_status =  ($user->data['user_allowhtml'] && $config['allow_html']) ? $user->lang['HTML_is_ON'] : $user->lang['HTML_is_OFF'];
	$bbcode_status = ($user->data['user_allowbbcode'] && $config['allow_bbcode']) ? $user->lang['BBCode_is_ON'] : $user->lang['BBCode_is_OFF'];
	$smilies_status = ($user->data['user_allowsmile'] && $config['allow_smilies']) ? $user->lang['Smilies_are_ON'] : $user->lang['Smilies_are_OFF'];

	// Let's do an overall check for settings/versions which would prevent
	// us from doing file uploads....
	$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off'|| !$config['allow_avatar_upload']) ? '' : 'enctype="multipart/form-data"';

	// Visual Confirmation - Show images
	$confirm_image = '';
	if ($mode == 'editprofile')
	{
		// Use IF conditional within template S_EDIT_PROFILE or some such
		$template->assign_block_vars('switch_edit_profile', array());
	}
	else if ($mode == 'register' && !empty($config['enable_confirm']))
	{
		// Use IF conditional within template, send a S_ENABLE_CONFIRM
		$template->assign_block_vars('switch_confirm', array()); 

		$sql = "SELECT session_id 
			FROM " . SESSIONS_TABLE; 
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$confirm_sql = '';
			do
			{
				$confirm_sql .= (($confirm_sql != '') ? ', ' : '') . "'" . $row['session_id'] . "'";
			}
			while ($row = $db->sql_fetchrow($result));
		
			$sql = "DELETE FROM " .  CONFIRM_TABLE . " 
				WHERE session_id NOT IN ($confirm_sql)";
			$db->sql_query($sql);
		}
		$db->sql_freeresult($result);

		$sql = "SELECT COUNT(session_id) AS attempts 
			FROM " . CONFIRM_TABLE . " 
			WHERE session_id = '" . $userdata['session_id'] . "'";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			if ($row['attempts'] > 5)
			{
				trigger_error($user->lang['Too_many_registers']);
			}
		}
		$db->sql_freeresult($result);

		$code = $userdata->gen_png_string(6);
		$confirm_id = md5(uniqid($user_ip));

		$sql = "INSERT INTO " . CONFIRM_TABLE . " (confirm_id, session_id, code) 
			VALUES ('$confirm_id', '" . $user->data['session_id'] . "', '$code')";
		$db->sql_query($sql);
		
		$confirm_image = (@extension_loaded('zlib')) ? '<img src="' . "ucp/usercp_confirm.$phpEx$SID&id=$confirm_id" . '" alt="" title="" />' : '<img src="ucp/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=1" alt="" title="" /><img src="ucp/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=2" alt="" title="" /><img src="ucp/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=3" alt="" title="" /><img src="ucp/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=4" alt="" title="" /><img src="ucp/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=5" alt="" title="" /><img src="ucp/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=6" alt="" title="" />';
		$s_hidden_fields .= '<input type="hidden" name="confirm_id" value="' . $confirm_id . '" />';

	}
	// 
	// End visual confirmation
	//

	
	// No need to send simple language vars to template ... they are
	// picked up automatically (must be named in lang file as they 
	// are in template minus the L_ of course!). Only send lang
	// strings created or modified within source
	$template->assign_vars(array(
		'USERNAME' => $username,
		'EMAIL' => $email,
		'VIEW_EMAIL_YES' => ($viewemail) ? 'checked="checked"' : '',
		'VIEW_EMAIL_NO' => (!$viewemail) ? 'checked="checked"' : '',
		'HIDE_USER_YES' => (!$allowviewonline) ? 'checked="checked"' : '',
		'HIDE_USER_NO' => ($allowviewonline) ? 'checked="checked"' : '',
		'NOTIFY_PM_YES' => ($notifypm) ? 'checked="checked"' : '',
		'NOTIFY_PM_NO' => (!$notifypm) ? 'checked="checked"' : '',
		'POPUP_PM_YES' => ($popuppm) ? 'checked="checked"' : '',
		'POPUP_PM_NO' => (!$popuppm) ? 'checked="checked"' : '',
		'ALWAYS_ADD_SIGNATURE_YES' => ($attachsig) ? 'checked="checked"' : '',
		'ALWAYS_ADD_SIGNATURE_NO' => (!$attachsig) ? 'checked="checked"' : '',
		'NOTIFY_REPLY_YES' => ($notifyreply) ? 'checked="checked"' : '',
		'NOTIFY_REPLY_NO' => (!$notifyreply) ? 'checked="checked"' : '',
		'ALWAYS_ALLOW_BBCODE_YES' => ($allowbbcode) ? 'checked="checked"' : '',
		'ALWAYS_ALLOW_BBCODE_NO' => (!$allowbbcode) ? 'checked="checked"' : '',
		'ALWAYS_ALLOW_HTML_YES' => ($allowhtml) ? 'checked="checked"' : '',
		'ALWAYS_ALLOW_HTML_NO' => (!$allowhtml) ? 'checked="checked"' : '',
		'ALWAYS_ALLOW_SMILIES_YES' => ($allowsmilies) ? 'checked="checked"' : '',
		'ALWAYS_ALLOW_SMILIES_NO' => (!$allowsmilies) ? 'checked="checked"' : '',
		'LANGUAGE_SELECT' => language_select($user_lang, 'language'),
		'STYLE_SELECT' => style_select($user_style, 'style'),
		'TIMEZONE_SELECT' => tz_select($user_timezone, 'timezone'),
		'DATE_FORMAT' => $user_dateformat,
		'HTML_STATUS' => $html_status,
		'BBCODE_STATUS' => sprintf($bbcode_status, '<a href="' . "faq.$phpEx$SID&amp;mode=bbcode" . '" target="_phpbbcode">', '</a>'),
		'SMILIES_STATUS' => $smilies_status,
		
		'CONFIRM_CODE' => $confirm_image,

		'L_CURRENT_PASSWORD' => $user->lang['Current_password'],
		'L_NEW_PASSWORD' => ($mode == 'register') ? $user->lang['Password'] : $user->lang['New_password'],
		'L_CONFIRM_PASSWORD' => $user->lang['Confirm_password'],
		'L_CONFIRM_PASSWORD_EXPLAIN' => ($mode == 'editprofile') ? $user->lang['Confirm_password_explain'] : '',
		'L_PASSWORD_IF_CHANGED' => ($mode == 'editprofile') ? $user->lang['password_if_changed'] : '',
		'L_PASSWORD_CONFIRM_IF_CHANGED' => ($mode == 'editprofile') ? $user->lang['password_confirm_if_changed'] : '',
		'L_SUBMIT' => $user->lang['Submit'],
		'L_RESET' => $user->lang['Reset'],
		'L_BOARD_LANGUAGE' => $user->lang['Board_lang'],
		'L_BOARD_STYLE' => $user->lang['Board_style'],
		'L_TIMEZONE' => $user->lang['Timezone'],
		'L_DATE_FORMAT' => $user->lang['Date_format'],
		'L_DATE_FORMAT_EXPLAIN' => $user->lang['Date_format_explain'],
		'L_YES' => $user->lang['Yes'],
		'L_NO' => $user->lang['No'],
		'L_INTERESTS' => $user->lang['Interests'],
		'L_ALWAYS_ALLOW_SMILIES' => $user->lang['Always_smile'],
		'L_ALWAYS_ALLOW_BBCODE' => $user->lang['Always_bbcode'],
		'L_ALWAYS_ALLOW_HTML' => $user->lang['Always_html'],
		'L_HIDE_USER' => $user->lang['Hide_user'],
		'L_ALWAYS_ADD_SIGNATURE' => $user->lang['Always_add_sig'],

		'L_NOTIFY_ON_REPLY' => $user->lang['Always_notify'],
		'L_NOTIFY_ON_REPLY_EXPLAIN' => $user->lang['Always_notify_explain'],
		'L_NOTIFY_ON_PRIVMSG' => $user->lang['Notify_on_privmsg'],
		'L_POPUP_ON_PRIVMSG' => $user->lang['Popup_on_privmsg'],
		'L_POPUP_ON_PRIVMSG_EXPLAIN' => $user->lang['Popup_on_privmsg_explain'],
		'L_PREFERENCES' => $user->lang['Preferences'],
		'L_PUBLIC_VIEW_EMAIL' => $user->lang['Public_view_email'],
		'L_ITEMS_REQUIRED' => $user->lang['Items_required'],
		'L_REGISTRATION_INFO' => $user->lang['Registration_info'],
		'L_PROFILE_INFO' => $user->lang['Profile_info'],
		'L_PROFILE_INFO_NOTICE' => $user->lang['Profile_info_warn'],
		'L_EMAIL_ADDRESS' => $user->lang['Email_address'],

		'S_PROFILE_EDIT' => ($mode == 'editprofile') ? true : false,
		'S_CONFIRM_CODE' => ($config['enable_confirm']) ? 1 : 0,
		'S_HIDDEN_FIELDS' => $s_hidden_fields,
		'S_FORM_ENCTYPE' => $form_enctype,
		'S_PROFILE_ACTION' => "ucp.$phpEx$SID")
	);

//
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => 'profile_add_body.html')
);
make_jumpbox('viewforum.'.$phpEx);

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

// ---------
// FUNCTIONS
//
function show_coppa()
{
	global $template, $user, $phpbb_root_path, $phpEx, $config;

	$template->set_filenames(array(
		'body' => 'agreement.html')
	);

	$l_reg_cond = '';
	switch ($config['require_activation'])
	{
		case USER_ACTIVATION_SELF:
			$l_reg_cond = $user->lang['Reg_email_activation'];
			break;
		case USER_ACTIVATION_ADMIN:
			$l_reg_conf = $user->lang['Reg_admin_activation'];
			break;
	}

	$template->assign_vars(array(
		'REGISTRATION'	=> $user->lang['REGISTRATION'],
		'AGREEMENT'		=> $user->lang['REG_AGREEMENT'], 
		'REGISTRATION_CONDITIONS' => $l_reg_cond, 
		"AGREE_OVER_13"		=> $user->lang['AGREE_OVER_13'],
		"AGREE_UNDER_13"	=> $user->lang['AGREE_UNDER_13'],
		'DO_NOT_AGREE'		=> $user->lang['AGREE_NOT'],
		'AGREE'				=> $user->lang['AGREE'],
		
		'U_UCP_AGREE' => 'ucp.' . $phpEx,
		"U_AGREE_OVER13" => "ucp.$phpEx?$SID&amp;mode=register&amp;agreed=true",
		"U_AGREE_UNDER13" => "ucp.$phpEx?$SID&amp;mode=register&amp;agreed=true&amp;coppa=true")
	);
}

//
// FUNCTIONS
// ---------

?>