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
if (isset($_POST['submit']) || $mode == 'register')
{
	$strip_var_list = array('username' => 'username', 'email' => 'email'); 

	foreach ($strip_var_list as $var => $param)
	{
		if (!empty($_POST[$param]))
		{
			$$var = trim(strip_tags($_POST[$param]));
		}
	}

	$trim_var_list = array('password_current' => 'cur_password', 'password' => 'new_password', 'password_confirm' => 'password_confirm');

	foreach ($trim_var_list as $var => $param)
	{
		if (!empty($_POST[$param]))
		{
			$$var = trim($_POST[$param]);
		}
	}

	$username = str_replace('&nbsp;', '', $username);
	$email = htmlspecialchars($email);

	// Run some validation on the optional fields. These are pass-by-ref, so they'll be changed to
	// empty strings if they fail.
	//validate_optional_fields($icq, $aim, $msn, $yim, $website, $location, $occupation, $interests, $signature);

	$viewemail = (isset($_POST['viewemail'])) ? (($_POST['viewemail']) ? TRUE : 0) : 0;
	$allowviewonline = (isset($_POST['hideonline'])) ? (($_POST['hideonline']) ? 0 : TRUE) : TRUE;
	$notifyreply = (isset($_POST['notifyreply'])) ? (($_POST['notifyreply']) ? TRUE : 0) : 0;
	$notifypm = (isset($_POST['notifypm'])) ? (($_POST['notifypm']) ? TRUE : 0) : TRUE;
	$popuppm = (isset($_POST['popup_pm'])) ? (($_POST['popup_pm']) ? TRUE : 0) : TRUE;

	$attachsig = (isset($_POST['attachsig'])) ? (($_POST['attachsig']) ? TRUE : 0) : $config['allow_sig'];

	$allowhtml = (isset($_POST['allowhtml'])) ? (($_POST['allowhtml']) ? TRUE : 0) : $config['allow_html'];
	$allowbbcode = (isset($_POST['allowbbcode'])) ? (($_POST['allowbbcode']) ? TRUE : 0) : $config['allow_bbcode'];
	$allowsmilies = (isset($_POST['allowsmilies'])) ? (($_POST['allowsmilies']) ? TRUE : 0) : $config['allow_smilies'];

	$user_style = (isset($_POST['style'])) ? intval($_POST['style']) : $config['default_style'];

	if (!empty($_POST['language']))
	{
		if (preg_match('/^[a-z_]+$/i', $_POST['language']))
		{
			$user_lang = $_POST['language'];
		}
		else
		{
			$error = true;
			$error_msg = $user->lang['Fields_empty'];
		}
	}
	else
	{
		$user_lang = $config['default_lang'];
	}

	$user_timezone = (isset($_POST['timezone'])) ? doubleval($_POST['timezone']) : $config['board_timezone'];
	$user_dateformat = (!empty($_POST['dateformat'])) ? trim($_POST['dateformat']) : $config['default_dateformat'];

}


// Did the user submit? In this case build a query to update the users profile in the DB
if (isset($_POST['submit']))
{
	$passwd_sql = '';

	if (empty($username) || empty($password) || empty($password_confirm) || empty($email))
	{
		$error = TRUE;
		$error_msg .= ((isset($error_msg)) ? '<br />' : '') . $user->lang['Fields_empty'];
	}

	$passwd_sql = '';
	if (!empty($password) && !empty($password_confirm))
	{
		if ($password != $password_confirm)
		{
			$error = TRUE;
			$error_msg .= ((isset($error_msg)) ? '<br />' : '') . $user->lang['Password_mismatch'];
		}
		else if (strlen($password) > 32)
		{
			$error = TRUE;
			$error_msg .= ((isset($error_msg)) ? '<br />' : '') . $user->lang['Password_long'];
		}
		else
		{
			if (!$error)
			{
				$password = md5($password);
				$passwd_sql = "user_password = '$password', ";
			}
		}
	}
	else if ((empty($password) && !empty($password_confirm)) || (!empty($password) && empty($password_confirm)))
	{
		$error = TRUE;
		$error_msg .= ((isset($error_msg)) ? '<br />' : '') . $user->lang['Password_mismatch'];
	}
	else
	{
		$password = $user->data['user_password'];
	}

	// Do a ban check on this email address
	if ($email != $user->data['user_email'] || $mode == 'register')
	{
		if (($result = validate_email($email)) != false)
		{
			$email = $user->data['user_email'];

			$error = TRUE;
			$error_msg .= ((isset($error_msg)) ? '<br />' : '') . $result;
		}
	}

	$username_sql = '';

	if (empty($username))
	{
		$error = TRUE;
		$error_msg .= ((isset($error_msg)) ? '<br />' : '') . $user->lang['Username_disallowed'];
	}
	else
	{
		if (($result = validate_username($username)) != false)
		{
			$error = TRUE;
			$error_msg .= ((isset($error_msg)) ? '<br />' : '') . $result;
		}
		else
		{
			$username_sql = "username = '" . $username . "', ";
		}
	}

	// Visual Confirmation handling
	if ($config['enable_confirm'])
	{
		if (empty($_POST['confirm_id']))
		{
			$error = TRUE;
			$error_msg .= ((isset($error_msg)) ? '<br />' : '') . $lang['Confirm_code_wrong'];
		}
		else
		{
			$sql = "SELECT code 
				FROM " . CONFIRM_TABLE . " 
				WHERE confirm_id = '" . $_POST['confirm_id'] . "' 
					AND session_id = '" . $user->data['session_id'] . "'";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				if ($row['code'] != $_POST['confirm_code'])
				{			
					$error = TRUE;
					$error_msg .= ((isset($error_msg)) ? '<br />' : '') . $lang['Confirm_code_wrong'];
				}
			}
			else
			{
				$error = TRUE;
				$error_msg .= ((isset($error_msg)) ? '<br />' : '') . $lang['Confirm_code_wrong'];
			}

			$sql = "DELETE FROM " . CONFIRM_TABLE . " 
				WHERE confirm_id = '" . $_POST['confirm_id'] . "' 
					AND session_id = '" . $userdata['session_id'] . "'";
			$db->sql_query($sql);
		}
	}
	
	if (!$error)
	{
		if ((($mode == 'register' || $coppa)) && ($config['require_activation'] == USER_ACTIVATION_SELF || $config['require_activation'] == USER_ACTIVATION_ADMIN))
		{
			$user_actkey = gen_rand_string(true);
			$key_len = 54 - (strlen($server_url));
			$key_len = ($key_len > 6) ? $key_len : 6;

			$user_actkey = substr($user_actkey, 0, $key_len);
			$user_active = 0;

			if ($user->data['user_id'] != ANONYMOUS)
			{
				$user->destroy();
			}
		}
		else
		{
			$user_active = 1;
			$user_actkey = '';
		}

		// Begin transaction ... should this screw up we can rollback
		$db->sql_transaction();

		$sql_ary = array(
			'user_ip'		=> $this->user_ip, 
			'user_regdate'	=> time(),
			'username'		=> $username, 
			'user_password' => $password,
			'user_email'	=> $email,
			'user_viewemail'	=> $viewemail,
			'user_attachsig'	=> $attachsig,
			'user_allowsmile'	=> $allowsmilies,
			'user_allowhtml'	=> $allowhtml,
			'user_allowbbcode'	=> $allowbbcode,
			'user_allow_viewonline' => $allowviewonline,
			'user_allow_pm'		=> 1,
			'user_notify'	=> $notifyreply,
			'user_notify_pm'=> $notifypm,
			'user_popup_pm' => $popuppm,
			'user_timezone' => (float) $user_timezone,
			'user_dateformat'	=> $user_dateformat,
			'user_lang'			=> $user_lang,
			'user_style'		=> $user_style,
			'user_active' => $user_active,
			'user_actkey' => $user_actkey
		);
//			'user_avatar' => $avatar_sql['data'],
//			'user_avatar_type' => $avatar_sql['type'],

		$sql = 'INSERT INTO ' . USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);
		
		$user_id = $db->sql_nextid();

		// Place into appropriate group, either REGISTERED or INACTIVE depending on config
		$group_name = ($config['require_activation'] == USER_ACTIVATION_NONE) ? 'REGISTERED' : 'INACTIVE';
		$sql = "INSERT INTO " . USER_GROUP_TABLE . " (user_id, group_id, user_pending) 
			SELECT $user_id, group_id, 0 
				FROM " . GROUPS_TABLE . " 
				WHERE group_name = '$group_name' 
					AND group_type = " . GROUP_SPECIAL;
		$result = $db->sql_query($sql);

		if ($config['require_activation'] == USER_ACTIVATION_NONE)
		{
			set_config('newest_user_id', $user_id, TRUE);
			set_config('newest_username', $username, TRUE);
			set_config('num_users', $config['num_users'] + 1, TRUE);
		}

		$db->sql_transaction('commit');


		if ($coppa)
		{
			$message = $user->lang['COPPA'];
			$email_template = 'coppa_welcome_inactive';
		}
		else if ($config['require_activation'] == USER_ACTIVATION_SELF)
		{
			$message = $user->lang['Account_inactive'];
			$email_template = 'user_welcome_inactive';
		}
		else if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
		{
			$message = $user->lang['Account_inactive_admin'];
			$email_template = 'admin_welcome_inactive';
		}
		else
		{
			$message = $user->lang['Account_added'];
			$email_template = 'user_welcome';
		}

/*
		include($phpbb_root_path . 'includes/emailer.'.$phpEx);
		$emailer = new emailer($config['smtp_delivery']);

		// Should we just define this within the email class?
		$email_headers = "From: " . $config['board_email'] . "\nReturn-Path: " . $config['board_email'] . "\r\n";

		$emailer->use_template($email_template, $user->data['user_lang']);
		$emailer->email_address($email);
		$emailer->set_subject();//sprintf($user->lang['Welcome_subject'], $config['sitename'])
		$emailer->extra_headers($email_headers);

		if ($coppa)
		{
			$emailer->assign_vars(array(
				'SITENAME' => $config['sitename'],
				'WELCOME_MSG' => sprintf($user->lang['Welcome_subject'], $config['sitename']),
				'USERNAME' => $username,
				'PASSWORD' => $password_confirm,
				'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

				'U_ACTIVATE' => $server_url . '?mode=activate&act_key=' . $user_actkey,
				'FAX_INFO' => $config['coppa_fax'],
				'MAIL_INFO' => $config['coppa_mail'],
				'EMAIL_ADDRESS' => $email,
				'SITENAME' => $config['sitename']));
		}
		else
		{
			$emailer->assign_vars(array(
				'SITENAME' => $config['sitename'],
				'WELCOME_MSG' => sprintf($user->lang['Welcome_subject'], $config['sitename']),
				'USERNAME' => $username,
				'PASSWORD' => $password_confirm,
				'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),
				'U_ACTIVATE' => $server_url . '?mode=activate&act_key=' . $user_actkey)
			);
		}

		$emailer->send();
		$emailer->reset();

		if ($config['require_activation'] == USER_ACTIVATION_ADMIN)
		{
			$emailer->use_template('admin_activate', stripslashes($user_lang));
			$emailer->email_address($config['board_email']);
			$emailer->set_subject(); //$user->lang['New_account_subject']
			$emailer->extra_headers($email_headers);

			$emailer->assign_vars(array(
				'USERNAME' => $username,
				'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

				'U_ACTIVATE' => $server_url . '?mode=activate&act_key=' . $user_actkey)
			);
			$emailer->send();
			$emailer->reset();
		}
*/
		$message = $message . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'],  '<a href="' . "index.$phpEx$SID" . '">', '</a>');

		trigger_error($message);
	
	}
	else
	{
		trigger_error($error_msg);
	}
}	 // End of submit


if ($error)
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

		$code = gen_png_string(6);
		$confirm_id = md5(uniqid($user_ip));

		$sql = "INSERT INTO " . CONFIRM_TABLE . " (confirm_id, session_id, code) 
			VALUES ('$confirm_id', '" . $user->data['session_id'] . "', '$code')";
		$db->sql_query($sql);
		
		$confirm_image = (@extension_loaded('zlib')) ? '<img src="' . "includes/ucp/usercp_confirm.$phpEx$SID&id=$confirm_id" . '" alt="" title="" />' : '<img src="includes/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=1" alt="" title="" /><img src="includes/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=2" alt="" title="" /><img src="includes/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=3" alt="" title="" /><img src="includes/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=4" alt="" title="" /><img src="includes/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=5" alt="" title="" /><img src="includes/usercp_confirm.$phpEx?$SID&amp;id=$confirm_id&amp;c=6" alt="" title="" />';
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

function gen_png_string($num_chars)
{
	$chars = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',  'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',  'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9');

	list($usec, $sec) = explode(' ', microtime()); 
	mt_srand($sec * $usec); 

	$max_chars = count($chars) - 1;
	$rand_str = '';
	for ($i = 0; $i < $num_chars; $i++)
	{
		$rand_str .= $chars[mt_rand(0, $max_chars)];
	}

	return $rand_str;
}
//
// FUNCTIONS
// ---------

?>