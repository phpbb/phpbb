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

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
	exit;
}

// ---------------------------------------
// Load agreement template since user has not yet
// agreed to registration conditions/coppa
//
function show_coppa()
{
	global $template, $lang, $phpbb_root_path, $phpEx;

	$template->set_filenames(array(
		'body' => 'agreement.html')
	);

	$template->assign_vars(array(
		'REGISTRATION' => $user->lang['Registration'],
		'AGREEMENT' => $user->lang['Reg_agreement'],
		"AGREE_OVER_13" => $user->lang['Agree_over_13'],
		"AGREE_UNDER_13" => $user->lang['Agree_under_13'],
		'DO_NOT_AGREE' => $user->lang['Agree_not'],

		"U_AGREE_OVER13" => "profile.$phpEx$SID&amp;mode=register&amp;agreed=true",
		"U_AGREE_UNDER13" => "profile.$phpEx$SID&amp;mode=register&amp;agreed=true&amp;coppa=true")
	);
}
//
// ---------------------------------------


//
//
//
if ($mode == 'register' && $config['require_activation'] == USER_ACTIVATION_DISABLE)
{
	trigger_error($user->lang['Cannot_register']);
}


//
//
//
$error = FALSE;
$page_title = ($mode == 'editprofile') ? $user->lang['Edit_profile'] : $user->lang['Register'];

if ($mode == 'register' && !isset($_POST['agreed']) && !isset($_GET['agreed']) && $config['enable_coppa'])
{
	include($phpbb_root_path . 'includes/page_header.'.$phpEx);

	show_coppa();

	include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
}

$coppa = ( empty($_POST['coppa']) && empty($_GET['coppa']) ) ? 0 : TRUE;

//
// Check and initialize some variables if needed
//
if (isset($_POST['submit']) || $mode == 'register')
{
	if ($mode == 'editprofile')
	{
		$user_id = intval($_POST['user_id']);
		$current_email = trim(strip_tags(htmlspecialchars($_POST['current_email'])));
	}

	$strip_var_list = array('username' => 'username', 'email' => 'email', 'icq' => 'icq', 'aim' => 'aim', 'msn' => 'msn', 'yim' => 'yim', 'website' => 'website', 'location' => 'location', 'occupation' => 'occupation', 'interests' => 'interests');

	foreach ($strip_var_list as $var => $param)
	{
		if ( !empty($_POST[$param]) )
		{
			$$var = trim(strip_tags($_POST[$param]));
		}
	}

	$trim_var_list = array('password_current' => 'cur_password', 'password' => 'new_password', 'password_confirm' => 'password_confirm', 'signature' => 'signature');

	foreach ($strip_var_list as $var => $param)
	{
		if ( !empty($_POST[$param]) )
		{
			$$var = trim($_POST[$param]);
		}
	}

	$username = str_replace('&nbsp;', '', $username);
	$email = htmlspecialchars($email);
	$signature = str_replace('<br />', "\n", $signature);

	// Run some validation on the optional fields. These are pass-by-ref, so they'll be changed to
	// empty strings if they fail.
	validate_optional_fields($icq, $aim, $msn, $yim, $website, $location, $occupation, $interests, $signature);

	$viewemail = ( isset($_POST['viewemail']) ) ? ( ($_POST['viewemail']) ? TRUE : 0 ) : 0;
	$allowviewonline = ( isset($_POST['hideonline']) ) ? ( ($_POST['hideonline']) ? 0 : TRUE ) : TRUE;
	$notifyreply = ( isset($_POST['notifyreply']) ) ? ( ($_POST['notifyreply']) ? TRUE : 0 ) : 0;
	$notifypm = ( isset($_POST['notifypm']) ) ? ( ($_POST['notifypm']) ? TRUE : 0 ) : TRUE;
	$popuppm = ( isset($_POST['popup_pm']) ) ? ( ($_POST['popup_pm']) ? TRUE : 0 ) : TRUE;

	if ( $mode == 'register' )
	{
		$attachsig = ( isset($_POST['attachsig']) ) ? ( ($_POST['attachsig']) ? TRUE : 0 ) : $config['allow_sig'];

		$allowhtml = ( isset($_POST['allowhtml']) ) ? ( ($_POST['allowhtml']) ? TRUE : 0 ) : $config['allow_html'];
		$allowbbcode = ( isset($_POST['allowbbcode']) ) ? ( ($_POST['allowbbcode']) ? TRUE : 0 ) : $config['allow_bbcode'];
		$allowsmilies = ( isset($_POST['allowsmilies']) ) ? ( ($_POST['allowsmilies']) ? TRUE : 0 ) : $config['allow_smilies'];
	}
	else
	{
		$attachsig = ( isset($_POST['attachsig']) ) ? ( ($_POST['attachsig']) ? TRUE : 0 ) : 0;

		$allowhtml = ( isset($_POST['allowhtml']) ) ? ( ($_POST['allowhtml']) ? TRUE : 0 ) : $user->data['user_allowhtml'];
		$allowbbcode = ( isset($_POST['allowbbcode']) ) ? ( ($_POST['allowbbcode']) ? TRUE : 0 ) : $user->data['user_allowbbcode'];
		$allowsmilies = ( isset($_POST['allowsmilies']) ) ? ( ($_POST['allowsmilies']) ? TRUE : 0 ) : $user->data['user_allowsmiles'];
	}

	$user_style = ( isset($_POST['style']) ) ? intval($_POST['style']) : $config['default_style'];

	if ( !empty($_POST['language']) )
	{
		if ( preg_match('/^[a-z_]+$/i', $_POST['language']) )
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

	$user_timezone = ( isset($_POST['timezone']) ) ? doubleval($_POST['timezone']) : $config['board_timezone'];
	$user_dateformat = ( !empty($_POST['dateformat']) ) ? trim($_POST['dateformat']) : $config['default_dateformat'];

}

//
// Did the user submit? In this case build a query to update the users profile in the DB
//
if (isset($_POST['submit']))
{
	$passwd_sql = '';
	if ( $mode == 'editprofile' )
	{
		if ( $user_id != $user->data['user_id'] )
		{
			$error = TRUE;
			$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $user->lang['Wrong_Profile'];
		}
	}
	else if ( $mode == 'register' )
	{
		if ( empty($username) || empty($password) || empty($password_confirm) || empty($email) )
		{
			$error = TRUE;
			$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $user->lang['Fields_empty'];
		}

	}

	$passwd_sql = '';
	if ( !empty($password) && !empty($password_confirm) )
	{
		if ( $password != $password_confirm )
		{
			$error = TRUE;
			$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $user->lang['Password_mismatch'];
		}
		else if ( strlen($password) > 32 )
		{
			$error = TRUE;
			$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $user->lang['Password_long'];
		}
		else
		{
			if ( $mode == 'editprofile' )
			{
				$sql = "SELECT user_password
					FROM " . USERS_TABLE . "
					WHERE user_id = $user_id";
				$result = $db->sql_query($sql);

				$row = $db->sql_fetchrow($result);

				if ( $row['user_password'] != md5($password_current) )
				{
					$error = TRUE;
					$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $user->lang['Current_password_mismatch'];
				}
			}

			if ( !$error )
			{
				$password = md5($password);
				$passwd_sql = "user_password = '$password', ";
			}
		}
	}
	else if ( ( empty($password) && !empty($password_confirm) ) || ( !empty($password) && empty($password_confirm) ) )
	{
		$error = TRUE;
		$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $user->lang['Password_mismatch'];
	}
	else
	{
		$password = $user->data['user_password'];
	}

	//
	// Do a ban check on this email address
	//
	if ( $email != $user->data['user_email'] || $mode == 'register' )
	{
		if (($result = validate_email($email)) != false)
		{
			$email = $user->data['user_email'];

			$error = TRUE;
			$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $result;
		}

		if ( $mode == 'editprofile' )
		{
			$sql = "SELECT user_password
				FROM " . USERS_TABLE . "
				WHERE user_id = $user_id";
			$result = $db->sql_query($sql);

			$row = $db->sql_fetchrow($result);

			if ( $row['user_password'] != md5($password_current) )
			{
				$email = $user->data['user_email'];

				$error = TRUE;
				$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $user->lang['Current_password_mismatch'];
			}
		}
	}

	$username_sql = '';
	if ( $config['allow_namechange'] || $mode == 'register' )
	{
		if ( empty($username) )
		{
			$error = TRUE;
			$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $user->lang['Username_disallowed'];
		}
		else if ( $username != $user->data['username'] || $mode == 'register' )
		{
			if (($result = validate_username($username)) != false)
			{
				$error = TRUE;
				$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $result;
			}
			else
			{
				$username_sql = "username = '" . sql_quote($username) . "', ";
				if ($mode != 'register')
				{
					$sql = 'UPDATE ' . FORUMS_TABLE . "
							SET forum_last_poster_name = '" . sql_quote($username) . "'
							WHERE forum_last_poster_id = " . $user_id;
					$db->sql_query($sql);
				}
			}
		}
	}

	if ( $signature != '' )
	{
		if ( strlen($signature) > $config['max_sig_chars'] )
		{
			$error = TRUE;
			$error_msg .= ( ( isset($error_msg) ) ? '<br />' : '' ) . $user->lang['Signature_too_long'];
		}

		if ( $signature_bbcode_uid == '' )
		{
//			$signature_bbcode_uid = ( $allowbbcode ) ? make_bbcode_uid() : '';
		}
//		$signature = prepare_message($signature, $allowhtml, $allowbbcode, $allowsmilies, $signature_bbcode_uid);
	}

	if ( !$error )
	{
		if ( ( ( $mode == 'editprofile' && $auth->acl_get('a_') && $email != $current_email ) || ( $mode == 'register' || $coppa ) ) && ( $config['require_activation'] == USER_ACTIVATION_SELF || $config['require_activation'] == USER_ACTIVATION_ADMIN ) )
		{
			$user_actkey = gen_rand_string(true);
			$key_len = 54 - (strlen($server_url));
			$key_len = ( $key_len > 6 ) ? $key_len : 6;

			$user_actkey = substr($user_actkey, 0, $key_len);
			$user_active = 0;

			if ( $user->data['user_id'] != ANONYMOUS )
			{
				$user->destroy();
			}
		}
		else
		{
			$user_active = 1;
			$user_actkey = '';
		}

		$sql_ary = array(
			'username' => $username,
			'user_regdate' => time(),
			'user_password' => $password,
			'user_email' => $email,
			'user_icq' => $icq,
			'user_aim' => $aim,
			'user_yim' => $yim,
			'user_msnm' => $msn,
			'user_website' => $website,
			'user_occ' => $occupation,
			'user_from' => $location,
			'user_interests' => $interests,
			'user_sig' => $signature,
			'user_sig_bbcode_uid' => $signature_bbcode_uid,
			'user_viewemail' => $viewemail,
			'user_attachsig' => $attachsig,
			'user_allowsmile' => $allowsmilies,
			'user_allowhtml' => $allowhtml,
			'user_allowbbcode' => $allowbbcode,
			'user_allow_viewonline' => $allowviewonline,
			'user_notify' => $notifyreply,
			'user_notify_pm' => $notifypm,
			'user_popup_pm' => $popuppm,
			'user_avatar' => $avatar_sql['data'],
			'user_avatar_type' => $avatar_sql['type'],
			'user_timezone' => (float) $user_timezone,
			'user_dateformat' => $user_dateformat,
			'user_lang' => $user_lang,
			'user_style' => $user_style,
			'user_allow_pm' => 1,
			'user_active' => $user_active,
			'user_actkey' => $user_actkey
		);

		if ($mode == 'editprofile')
		{
			$db->sql_query('UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' WHERE user_id = ' . $user_id);

			if ($config['newest_user_id'] == $user_id)
			{
				$sql = 'UPDATE ' . CONFIG_TABLE . "
						SET config_value = '" . sql_quote($username) . "'
						WHERE config_name = 'newest_username'";
				$db->sql_query($sql);
			}

			if ( !$user_active )
			{
				//
				// The users account has been deactivated, send them an email with a new activation key
				//
				include($phpbb_root_path . 'includes/emailer.'.$phpEx);
				$emailer = new emailer($config['smtp_delivery']);

				$email_headers = "From: " . $config['board_email'] . "\r\nReturn-Path: " . $config['board_email'] . "\r\n";

				$emailer->use_template('user_activate', stripslashes($user_lang));
				$emailer->email_address($email);
				$emailer->set_subject();//$user->lang['Reactivate']
				$emailer->extra_headers($email_headers);

				$emailer->assign_vars(array(
					'SITENAME' => $config['sitename'],
					'USERNAME' => $username,
					'EMAIL_SIG' => str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

					'U_ACTIVATE' => $server_url . '?mode=activate&act_key=' . $user_actkey)
				);
				$emailer->send();
				$emailer->reset();

				$message = $user->lang['Profile_updated_inactive'] . '<br /><br />' . sprintf($user->lang['Click_return_index'],  '<a href="' . "index.$phpEx$SID" . '">', '</a>');
			}
			else
			{
				$message = $user->lang['Profile_updated'] . '<br /><br />' . sprintf($user->lang['Click_return_index'],  '<a href="' . "index.$phpEx$SID" . '">', '</a>');
			}

			$template->assign_vars(array(
				"META" => '<meta http-equiv="refresh" content="5;url=' . "index.$phpEx$SID" . '">')
			);
			trigger_error($message);
		}
		else
		{
			$db->sql_transaction();

			$db->sql_query_array('INSERT INTO ' . USERS_TABLE, &$sql_ary);

			$user_id = $db->sql_nextid();

			// Place into appropriate group, either REGISTERED or INACTIVE depending on config
			$group_name = ( $config['require_activation'] == USER_ACTIVATION_NONE ) ? 'REGISTERED' : 'REGISTERED_INACTIVE';
			$sql = "INSERT INTO " . USER_GROUP_TABLE . " (user_id, group_id, user_pending) SELECT $user_id, group_id, 0 FROM " . GROUPS_TABLE . " WHERE group_name = '$group_name'";
			$result = $db->sql_query($sql);

			if ($config['require_activation'] == USER_ACTIVATION_NONE)
			{
				// Sync config
				$sql = "UPDATE " . CONFIG_TABLE . "
					SET config_value = $user_id
					WHERE config_name = 'newest_user_id'";
				$db->sql_query($sql);
				$sql = "UPDATE " . CONFIG_TABLE . "
					SET config_value = '$username'
					WHERE config_name = 'newest_username'";
				$db->sql_query($sql);
				$sql = "UPDATE " . CONFIG_TABLE . "
					SET config_value = " . ($config['num_users'] + 1) . "
					WHERE config_name = 'num_users'";
				$db->sql_query($sql);
			}

			$db->sql_transaction('commit');

			if ( $coppa )
			{
				$message = $user->lang['COPPA'];
				$email_template = 'coppa_welcome_inactive';
			}
			else if ( $config['require_activation'] == USER_ACTIVATION_SELF )
			{
				$message = $user->lang['Account_inactive'];
				$email_template = 'user_welcome_inactive';
			}
			else if ( $config['require_activation'] == USER_ACTIVATION_ADMIN )
			{
				$message = $user->lang['Account_inactive_admin'];
				$email_template = 'admin_welcome_inactive';
			}
			else
			{
				$message = $user->lang['Account_added'];
				$email_template = 'user_welcome';
			}

			include($phpbb_root_path . 'includes/emailer.'.$phpEx);
			$emailer = new emailer($config['smtp_delivery']);

			$email_headers = "From: " . $config['board_email'] . "\nReturn-Path: " . $config['board_email'] . "\r\n";

			$emailer->use_template($email_template, stripslashes($user_lang));
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
					'ICQ' => $icq,
					'AIM' => $aim,
					'YIM' => $yim,
					'MSN' => $msn,
					'WEB_SITE' => $website,
					'FROM' => $location,
					'OCC' => $occupation,
					'INTERESTS' => $interests,
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

			if ( $config['require_activation'] == USER_ACTIVATION_ADMIN )
			{
				$emailer->use_template("admin_activate", stripslashes($user_lang));
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

			$message = $message . '<br /><br />' . sprintf($user->lang['Click_return_index'],  '<a href="' . "index.$phpEx$SID" . '">', '</a>');

			message_die(GENERAL_MESSAGE, $message);
		} // if mode == register
	}
} // End of submit


if ( $error )
{
	//
	// If an error occured we need to stripslashes on returned data
	//
	$username = stripslashes($username);
	$email = stripslashes($email);
	$password = '';
	$password_confirm = '';

	$icq = stripslashes($icq);
	$aim = htmlspecialchars(str_replace('+', ' ', stripslashes($aim)));
	$msn = htmlspecialchars(stripslashes($msn));
	$yim = htmlspecialchars(stripslashes($yim));

	$website = htmlspecialchars(stripslashes($website));
	$location = htmlspecialchars(stripslashes($location));
	$occupation = htmlspecialchars(stripslashes($occupation));
	$interests = htmlspecialchars(stripslashes($interests));
	$signature = stripslashes($signature);

	$user_lang = stripslashes($user_lang);
	$user_dateformat = stripslashes($user_dateformat);
}
else if ( $mode == 'editprofile' )
{
	$user_id = $user->data['user_id'];
	$username = htmlspecialchars($user->data['username']);
	$email = $user->data['user_email'];
	$password = '';
	$password_confirm = '';

	$icq = $user->data['user_icq'];
	$aim = htmlspecialchars(str_replace('+', ' ', $user->data['user_aim']));
	$msn = htmlspecialchars($user->data['user_msnm']);
	$yim = htmlspecialchars($user->data['user_yim']);

	$website = htmlspecialchars($user->data['user_website']);
	$location = htmlspecialchars($user->data['user_from']);
	$occupation = htmlspecialchars($user->data['user_occ']);
	$interests = htmlspecialchars($user->data['user_interests']);
	$signature_bbcode_uid = $user->data['user_sig_bbcode_uid'];
	$signature = ( $signature_bbcode_uid != '' ) ? preg_replace("/\:(([a-z0-9]:)?)$signature_bbcode_uid/si", '', $user->data['user_sig']) : $user->data['user_sig'];

	$viewemail = $user->data['user_viewemail'];
	$notifypm = $user->data['user_notify_pm'];
	$popuppm = $user->data['user_popup_pm'];
	$notifyreply = $user->data['user_notify'];
	$attachsig = $user->data['user_attachsig'];
	$allowhtml = $user->data['user_allowhtml'];
	$allowbbcode = $user->data['user_allowbbcode'];
	$allowsmilies = $user->data['user_allowsmile'];
	$allowviewonline = $user->data['user_allow_viewonline'];

	$user_style = $user->data['user_style'];
	$user_lang = $user->data['user_lang'];
	$user_timezone = $user->data['user_timezone'];
	$user_dateformat = $user->data['user_dateformat'];
}

//
// Default pages
//

if ( $mode == 'editprofile' )
{
	if ( $user_id != $user->data['user_id'] )
	{
		$error = TRUE;
		$error_msg = $user->lang['Wrong_Profile'];
	}
}


	if ( !isset($coppa) )
	{
		$coppa = FALSE;
	}

	if ( !isset($user_template) )
	{
		$selected_template = $config['system_template'];
	}

	$signature = preg_replace('/\:[0-9a-z\:]*?\]/si', ']', $signature);

	$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" /><input type="hidden" name="agreed" value="true" /><input type="hidden" name="coppa" value="' . $coppa . '" />';
	if( $mode == 'editprofile' )
	{
		$s_hidden_fields .= '<input type="hidden" name="user_id" value="' . $user->data['user_id'] . '" />';
		//
		// Send the users current email address. If they change it, and account activation is turned on
		// the user account will be disabled and the user will have to reactivate their account.
		//
		$s_hidden_fields .= '<input type="hidden" name="current_email" value="' . $user->data['user_email'] . '" />';
	}

	if ( !empty($user_avatar_local) )
	{
		$s_hidden_fields .= '<input type="hidden" name="avatarlocal" value="' . $user_avatar_local . '" />';
	}

	$html_status =  ( $user->data['user_allowhtml'] && $config['allow_html'] ) ? $user->lang['HTML_is_ON'] : $user->lang['HTML_is_OFF'];
	$bbcode_status = ( $user->data['user_allowbbcode'] && $config['allow_bbcode']  ) ? $user->lang['BBCode_is_ON'] : $user->lang['BBCode_is_OFF'];
	$smilies_status = ( $user->data['user_allowsmile'] && $config['allow_smilies']  ) ? $user->lang['Smilies_are_ON'] : $user->lang['Smilies_are_OFF'];

	//
	// Let's do an overall check for settings/versions which would prevent
	// us from doing file uploads....
	//
	$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off'|| !$config['allow_avatar_upload']) ? '' : 'enctype="multipart/form-data"';

	$template->assign_vars(array(
		'USERNAME' => $username,
		'EMAIL' => $email,
		'YIM' => $yim,
		'ICQ' => $icq,
		'MSN' => $msn,
		'AIM' => $aim,
		'OCCUPATION' => $occupation,
		'INTERESTS' => $interests,
		'LOCATION' => $location,
		'WEBSITE' => $website,
		'SIGNATURE' => str_replace('<br />', "\n", $signature),
		'VIEW_EMAIL_YES' => ( $viewemail ) ? 'checked="checked"' : '',
		'VIEW_EMAIL_NO' => ( !$viewemail ) ? 'checked="checked"' : '',
		'HIDE_USER_YES' => ( !$allowviewonline ) ? 'checked="checked"' : '',
		'HIDE_USER_NO' => ( $allowviewonline ) ? 'checked="checked"' : '',
		'NOTIFY_PM_YES' => ( $notifypm ) ? 'checked="checked"' : '',
		'NOTIFY_PM_NO' => ( !$notifypm ) ? 'checked="checked"' : '',
		'POPUP_PM_YES' => ( $popuppm ) ? 'checked="checked"' : '',
		'POPUP_PM_NO' => ( !$popuppm ) ? 'checked="checked"' : '',
		'ALWAYS_ADD_SIGNATURE_YES' => ( $attachsig ) ? 'checked="checked"' : '',
		'ALWAYS_ADD_SIGNATURE_NO' => ( !$attachsig ) ? 'checked="checked"' : '',
		'NOTIFY_REPLY_YES' => ( $notifyreply ) ? 'checked="checked"' : '',
		'NOTIFY_REPLY_NO' => ( !$notifyreply ) ? 'checked="checked"' : '',
		'ALWAYS_ALLOW_BBCODE_YES' => ( $allowbbcode ) ? 'checked="checked"' : '',
		'ALWAYS_ALLOW_BBCODE_NO' => ( !$allowbbcode ) ? 'checked="checked"' : '',
		'ALWAYS_ALLOW_HTML_YES' => ( $allowhtml ) ? 'checked="checked"' : '',
		'ALWAYS_ALLOW_HTML_NO' => ( !$allowhtml ) ? 'checked="checked"' : '',
		'ALWAYS_ALLOW_SMILIES_YES' => ( $allowsmilies ) ? 'checked="checked"' : '',
		'ALWAYS_ALLOW_SMILIES_NO' => ( !$allowsmilies ) ? 'checked="checked"' : '',
		'LANGUAGE_SELECT' => language_select($user_lang, 'language'),
		'STYLE_SELECT' => style_select($user_style, 'style'),
		'TIMEZONE_SELECT' => tz_select($user_timezone, 'timezone'),
		'DATE_FORMAT' => $user_dateformat,
		'HTML_STATUS' => $html_status,
		'BBCODE_STATUS' => sprintf($bbcode_status, '<a href="' . "faq.$phpEx$SID&amp;mode=bbcode" . '" target="_phpbbcode">', '</a>'),
		'SMILIES_STATUS' => $smilies_status,

		'L_CURRENT_PASSWORD' => $user->lang['Current_password'],
		'L_NEW_PASSWORD' => ( $mode == 'register' ) ? $user->lang['Password'] : $user->lang['New_password'],
		'L_CONFIRM_PASSWORD' => $user->lang['Confirm_password'],
		'L_CONFIRM_PASSWORD_EXPLAIN' => ( $mode == 'editprofile' ) ? $user->lang['Confirm_password_explain'] : '',
		'L_PASSWORD_IF_CHANGED' => ( $mode == 'editprofile' ) ? $user->lang['password_if_changed'] : '',
		'L_PASSWORD_CONFIRM_IF_CHANGED' => ( $mode == 'editprofile' ) ? $user->lang['password_confirm_if_changed'] : '',
		'L_SUBMIT' => $user->lang['Submit'],
		'L_RESET' => $user->lang['Reset'],
		'L_ICQ_NUMBER' => $user->lang['ICQ'],
		'L_MESSENGER' => $user->lang['MSNM'],
		'L_YAHOO' => $user->lang['YIM'],
		'L_WEBSITE' => $user->lang['Website'],
		'L_AIM' => $user->lang['AIM'],
		'L_LOCATION' => $user->lang['Location'],
		'L_OCCUPATION' => $user->lang['Occupation'],
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

		'L_SIGNATURE' => $user->lang['Signature'],
		'L_SIGNATURE_EXPLAIN' => sprintf($user->lang['Signature_explain'], $config['max_sig_chars']),
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

		'S_PROFILE_EDIT' => ( $mode == 'editprofile' ) ? true : false,
		'S_HIDDEN_FIELDS' => $s_hidden_fields,
		'S_FORM_ENCTYPE' => $form_enctype,
		'S_PROFILE_ACTION' => "profile.$phpEx$SID")
	);

//
//
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => 'profile_add_body.html')
);
make_jumpbox('viewforum.'.$phpEx);

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>