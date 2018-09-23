<?php
/***************************************************************************
 *                                errors.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: errors.php,v 1.1 2010/10/10 15:01:18 orynider Exp $
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
 ***************************************************************************/

define('IN_PHPBB', true);
if (!defined('PHPBB_ROOT_PATH')) define('PHPBB_ROOT_PATH', './');
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
define('PHP_EXT', $phpEx);
include($phpbb_root_path . 'common.'.$phpEx);

// We need to force some vars...
$config['thumbnail_highslide'] = false;
$config['ajax_features'] = false;

//
// Set page ID for session management
//
$userdata = session_pagestart($user_ip, PAGE_LOGIN);
init_userprefs($userdata);
//
// End session management
//

// Errors Configuration Flags

// N = no email / Y = email
$email = array (
	'000' => 'N',
	'400' => 'N',
	'401' => 'N',
	'403' => 'N',
	'404' => 'N',
	'500' => 'N'
);

// N = no log file / Y = log file
$log = array (
	'000' => 'Y',
	'400' => 'Y',
	'401' => 'Y',
	'403' => 'Y',
	'404' => 'Y',
	'500' => 'Y'
);

// Errors description
$errors_english = array (
	'ERRORS_000' => 'Unknown Error',
	'ERRORS_400' => 'Error 400',
	'ERRORS_401' => 'Not Authorized',
	'ERRORS_403' => 'Errore 403',
	'ERRORS_404' => 'File not found',
	'ERRORS_500' => 'Configuration Error'
);

$subject = array (
	'000' => !empty($lang['ERRORS_000']) ? $lang['ERRORS_000'] : $errors_english['ERRORS_000'],
	'400' => !empty($lang['ERRORS_400']) ? $lang['ERRORS_400'] : $errors_english['ERRORS_400'],
	'401' => !empty($lang['ERRORS_401']) ? $lang['ERRORS_401'] : $errors_english['ERRORS_401'],
	'403' => !empty($lang['ERRORS_403']) ? $lang['ERRORS_403'] : $errors_english['ERRORS_403'],
	'404' => !empty($lang['ERRORS_404']) ? $lang['ERRORS_404'] : $errors_english['ERRORS_404'],
	'500' => !empty($lang['ERRORS_500']) ? $lang['ERRORS_500'] : $errors_english['ERRORS_500']
);


//$result = $QUERY_STRING;
$result = request_var('code', 0);

switch($result)
{
	case 400:
		$error_msg = !empty($lang['ERRORS_400_FULL']) ? $lang['ERRORS_400_FULL'] : $errors_english['ERRORS_400_FULL'];
		break;
	case 401:
		$error_msg = !empty($lang['ERRORS_401_FULL']) ? $lang['ERRORS_401_FULL'] : $errors_english['ERRORS_401_FULL'];
		break;
	case 403:
		$error_msg = !empty($lang['ERRORS_403_FULL']) ? $lang['ERRORS_403_FULL'] : $errors_english['ERRORS_403_FULL'];
		break;
	case 404:
		$error_msg = !empty($lang['ERRORS_404_FULL']) ? $lang['ERRORS_404_FULL'] : $errors_english['ERRORS_404_FULL'];
		break;
	case 500:
		$error_msg = !empty($lang['ERRORS_500_FULL']) ? $lang['ERRORS_500_FULL'] : $errors_english['ERRORS_500_FULL'];
		break;
	default:
		$result = '000';
		$error_msg = !empty($lang['ERRORS_000_FULL']) ? $lang['ERRORS_000_FULL'] : $errors_english['ERRORS_000_FULL'];
}

// Error notification details
$server_url = create_server_url();

$notification_email = $config['board_email'];
$sitename = $config['sitename'];
$datecode = gmdate('Ymd');
$logs_path = !empty($config['logs_path']) ? $config['logs_path'] : 'logs';
$errors_log = $logs_path . '/errors_' . $datecode . '.txt';
//$errors_log = 'logs/errors.txt';

if (($config['write_errors_log'] == true) && ($log[$result] == 'Y'))
{
	errors_notification('L', $result, $sitename, $subject, $errors_log, $notification_email);
}

if ($email[$result] == 'Y')
{
	errors_notification('M', $result, $sitename, $subject, $errors_log, $notification_email);
}

// Start output of page
$template->assign_vars(array(
	'ERROR_MESSAGE' => $error_msg
	)
);

send_status_line($result, $error_msg);
include($phpbb_root_path . 'includes/page_header.'.$phpEx);
full_page_generation('errors_body.tpl', $lang['Error'], '', '');
include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

function errors_notification($action, $result, $sitename, $subject, $errors_log, $notification_email)
{
	global $REQUEST_URI, $REMOTE_ADDR, $HTTP_USER_AGENT, $REDIRECT_ERROR_NOTES, $SERVER_NAME, $HTTP_REFERER;
	global $lang;

	$remote_address = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : getenv('REMOTE_ADDR'));
	$remote_address = (!empty($remote_address) && ($remote_address != '::1')) ? $remote_address : '127.0.0.1';
	$user_agent_errors = (!empty($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : (!empty($_ENV['HTTP_USER_AGENT']) ? trim($_ENV['HTTP_USER_AGENT']) : trim(getenv('HTTP_USER_AGENT'))));
	$referer = (!empty($_SERVER['HTTP_REFERER'])) ? (string) $_SERVER['HTTP_REFERER'] : '';
	$referer = preg_replace('/sid=[A-Za-z0-9]{32}/', '', $referer);
	$script_name = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
	$server_name = (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME');

	$date = gmdate('Y/m/d - H:i:s');

	if (($action == 'L') || ($action == 'LM'))
	{
		$message = '[' . $date . ']';
		$message .= ' [URL: ' . $script_name . ' ]';
		$message .= ' [REF: ' . $referer . ' ]';
		$message .= ' [IP: ' . $remote_address . ']';
		$message .= ' [Client: ' . $user_agent_errors . ']';
		$message .= "\n";
		$fp = fopen ($errors_log, "a+");
		fwrite($fp, $message);
		fclose($fp);
	}

	if ( ($action == 'M') || ($action == 'LM') )
	{
		$message_full = "
		==============================================================================
		------------------------------------------------------------------------------
		==============================================================================
		Site:       $sitename ($server_name)
		Error Code: $result $subject[$result]
		Date:       $date
		URL:        $referer
		IP Address: $remote_address
		Browser:    $user_agent_errors
		==============================================================================
		------------------------------------------------------------------------------
		==============================================================================
		";
		$message = $lang['ERRORS_EMAIL_BODY'];
		$subject_prefix = $lang['ERRORS_EMAIL_SUBJECT'];
		$email_from_prefix = $lang['ERRORS_EMAIL_ADDRRESS_PREFIX'];
		mail($notification_email, '[ ' . $subject_prefix . $subject[$result] . ' ]', $message, 'From: ' . $email_from_prefix . @$server_name . "\r\n" . 'X-Mailer: PHP/' . phpversion());
	}
}

?>