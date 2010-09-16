<?php
/**
*
* @package ucp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.' . $phpEx);
require($phpbb_root_path . 'includes/functions_user.' . $phpEx);
require($phpbb_root_path . 'includes/functions_module.' . $phpEx);

// Basic parameter data
$id 	= request_var('i', '');
$mode	= request_var('mode', '');

if (in_array($mode, array('login', 'logout', 'confirm', 'sendpassword', 'activate')))
{
	define('IN_LOGIN', true);
}

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('ucp');

// Setting a variable to let the style designer know where he is...
$template->assign_var('S_IN_UCP', true);

$module = new p_master();
$default = false;

// Basic "global" modes
switch ($mode)
{
	case 'activate':
		$module->load('ucp', 'activate');
		$module->display($user->lang['UCP_ACTIVATE']);

		redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
	break;

	case 'resend_act':
		$module->load('ucp', 'resend');
		$module->display($user->lang['UCP_RESEND']);
	break;

	case 'sendpassword':
		$module->load('ucp', 'remind');
		$module->display($user->lang['UCP_REMIND']);
	break;

	case 'register':
		if ($user->data['is_registered'] || isset($_REQUEST['not_agreed']))
		{
			redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
		}

		$module->load('ucp', 'register');
		$module->display($user->lang['REGISTER']);
	break;

	case 'confirm':
		$module->load('ucp', 'confirm');
	break;

	case 'login':
		if ($user->data['is_registered'])
		{
			redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
		}

		login_box(request_var('redirect', "index.$phpEx"));
	break;

	case 'logout':
		if ($user->data['user_id'] != ANONYMOUS && isset($_GET['sid']) && !is_array($_GET['sid']) && $_GET['sid'] === $user->session_id)
		{
			$user->session_kill();
			$user->session_begin();
			$message = $user->lang['LOGOUT_REDIRECT'];
		}
		else
		{
			$message = ($user->data['user_id'] == ANONYMOUS) ? $user->lang['LOGOUT_REDIRECT'] : $user->lang['LOGOUT_FAILED'];
		}
		meta_refresh(3, append_sid("{$phpbb_root_path}index.$phpEx"));

		$message = $message . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a> ');
		trigger_error($message);

	break;

	case 'terms':
	case 'privacy':

		$message = ($mode == 'terms') ? 'TERMS_OF_USE_CONTENT' : 'PRIVACY_POLICY';
		$title = ($mode == 'terms') ? 'TERMS_USE' : 'PRIVACY';

		if (empty($user->lang[$message]))
		{
			if ($user->data['is_registered'])
			{
				redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
			}

			login_box();
		}

		$template->set_filenames(array(
			'body'		=> 'ucp_agreement.html')
		);

		// Disable online list
		page_header($user->lang[$title], false);

		$template->assign_vars(array(
			'S_AGREEMENT'			=> true,
			'AGREEMENT_TITLE'		=> $user->lang[$title],
			'AGREEMENT_TEXT'		=> sprintf($user->lang[$message], $config['sitename'], generate_board_url()),
			'U_BACK'				=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=login'),
			'L_BACK'				=> $user->lang['BACK_TO_LOGIN'],
		));

		page_footer();

	break;

	case 'delete_cookies':

		// Delete Cookies with dynamic names (do NOT delete poll cookies)
		if (confirm_box(true))
		{
			$set_time = time() - 31536000;

			foreach ($_COOKIE as $cookie_name => $cookie_data)
			{
				// Only delete board cookies, no other ones...
				if (strpos($cookie_name, $config['cookie_name'] . '_') !== 0)
				{
					continue;
				}

				$cookie_name = str_replace($config['cookie_name'] . '_', '', $cookie_name);

				// Polls are stored as {cookie_name}_poll_{topic_id}, cookie_name_ got removed, therefore checking for poll_
				if (strpos($cookie_name, 'poll_') !== 0)
				{
					$user->set_cookie($cookie_name, '', $set_time);
				}
			}

			$user->set_cookie('track', '', $set_time);
			$user->set_cookie('u', '', $set_time);
			$user->set_cookie('k', '', $set_time);
			$user->set_cookie('sid', '', $set_time);

			// We destroy the session here, the user will be logged out nevertheless
			$user->session_kill();
			$user->session_begin();

			meta_refresh(3, append_sid("{$phpbb_root_path}index.$phpEx"));

			$message = $user->lang['COOKIES_DELETED'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
			trigger_error($message);
		}
		else
		{
			confirm_box(false, 'DELETE_COOKIES', '');
		}

		redirect(append_sid("{$phpbb_root_path}index.$phpEx"));

	break;

	case 'switch_perm':

		$user_id = request_var('u', 0);

		$sql = 'SELECT *
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $user_id;
		$result = $db->sql_query($sql);
		$user_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$auth->acl_get('a_switchperm') || !$user_row || $user_id == $user->data['user_id'] || !check_link_hash(request_var('hash', ''), 'switchperm'))
		{
			redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
		}

		include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);

		$auth_admin = new auth_admin();
		if (!$auth_admin->ghost_permissions($user_id, $user->data['user_id']))
		{
			redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
		}

		add_log('admin', 'LOG_ACL_TRANSFER_PERMISSIONS', $user_row['username']);

		$message = sprintf($user->lang['PERMISSIONS_TRANSFERRED'], $user_row['username']) . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
		trigger_error($message);

	break;

	case 'restore_perm':

		if (!$user->data['user_perm_from'] || !$auth->acl_get('a_switchperm'))
		{
			redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
		}

		$auth->acl_cache($user->data);

		$sql = 'SELECT username
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . $user->data['user_perm_from'];
		$result = $db->sql_query($sql);
		$username = $db->sql_fetchfield('username');
		$db->sql_freeresult($result);

		add_log('admin', 'LOG_ACL_RESTORE_PERMISSIONS', $username);

		$message = $user->lang['PERMISSIONS_RESTORED'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
		trigger_error($message);

	break;

	default:
		$default = true;
	break;
}

// We use this approach because it does not impose large code changes
if (!$default)
{
	return true;
}

// Only registered users can go beyond this point
if (!$user->data['is_registered'])
{
	if ($user->data['is_bot'])
	{
		redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
	}

	login_box('', $user->lang['LOGIN_EXPLAIN_UCP']);
}

// Instantiate module system and generate list of available modules
$module->list_modules('ucp');

// Check if the zebra module is set
if ($module->is_active('zebra', 'friends'))
{
	// Output listing of friends online
	$update_time = $config['load_online_time'] * 60;

	$sql = $db->sql_build_query('SELECT_DISTINCT', array(
		'SELECT'	=> 'u.user_id, u.username, u.username_clean, u.user_colour, MAX(s.session_time) as online_time, MIN(s.session_viewonline) AS viewonline',

		'FROM'		=> array(
			USERS_TABLE		=> 'u',
			ZEBRA_TABLE		=> 'z'
		),

		'LEFT_JOIN'	=> array(
			array(
				'FROM'	=> array(SESSIONS_TABLE => 's'),
				'ON'	=> 's.session_user_id = z.zebra_id'
			)
		),

		'WHERE'		=> 'z.user_id = ' . $user->data['user_id'] . '
			AND z.friend = 1
			AND u.user_id = z.zebra_id',

		'GROUP_BY'	=> 'z.zebra_id, u.user_id, u.username_clean, u.user_colour, u.username',

		'ORDER_BY'	=> 'u.username_clean ASC',
	));

	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$which = (time() - $update_time < $row['online_time'] && ($row['viewonline'] || $auth->acl_get('u_viewonline'))) ? 'online' : 'offline';

		$template->assign_block_vars("friends_{$which}", array(
			'USER_ID'		=> $row['user_id'],

			'U_PROFILE'		=> get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']),
			'USER_COLOUR'	=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),
			'USERNAME'		=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
			'USERNAME_FULL'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']))
		);
	}
	$db->sql_freeresult($result);
}

// Do not display subscribed topics/forums if not allowed
if (!$config['allow_topic_notify'] && !$config['allow_forum_notify'])
{
	$module->set_display('main', 'subscribed', false);
}

// Select the active module
$module->set_active($id, $mode);

// Load and execute the relevant module
$module->load_active();

// Assign data to the template engine for the list of modules
$module->assign_tpl_vars(append_sid("{$phpbb_root_path}ucp.$phpEx"));

// Generate the page, do not display/query online list
$module->display($module->get_page_title(), false);

/**
* Function for assigning a template var if the zebra module got included
*/
function _module_zebra($mode, &$module_row)
{
	global $template;

	$template->assign_var('S_ZEBRA_ENABLED', true);

	if ($mode == 'friends')
	{
		$template->assign_var('S_ZEBRA_FRIENDS_ENABLED', true);
	}

	if ($mode == 'foes')
	{
		$template->assign_var('S_ZEBRA_FOES_ENABLED', true);
	}
}

?>