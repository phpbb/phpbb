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
if (!defined('PHPBB_ROOT_PATH')) define('PHPBB_ROOT_PATH', './');
if (!defined('PHP_EXT')) define('PHP_EXT', substr(strrchr(__FILE__, '.'), 1));
include PHPBB_ROOT_PATH . 'common.' . PHP_EXT;
require PHPBB_ROOT_PATH . 'includes/functions_user.' . PHP_EXT;
require PHPBB_ROOT_PATH . 'includes/functions_module.' . PHP_EXT;

// Basic parameter data
$id 	= request_var('i', '');
$mode	= request_var('mode', '');

if ($mode == 'login' || $mode == 'logout' || $mode == 'confirm')
{
	define('IN_LOGIN', true);
}

// Start session management
phpbb::$user->session_begin();
phpbb::$acl->init(phpbb::$user->data);
phpbb::$user->setup('ucp');

// Setting a variable to let the style designer know where he is...
phpbb::$template->assign_var('S_IN_UCP', true);

$module = new p_master();

// Go through basic "global" modes
switch ($mode)
{
	case 'activate':
		$module->load('ucp', 'activate');
		$module->display(phpbb::$user->lang['UCP_ACTIVATE']);

		redirect(append_sid('index'));
	break;

	case 'resend_act':
		$module->load('ucp', 'resend');
		$module->display(phpbb::$user->lang['UCP_RESEND']);
	break;

	case 'sendpassword':
		$module->load('ucp', 'remind');
		$module->display(phpbb::$user->lang['UCP_REMIND']);
	break;

	case 'register':
		if (phpbb::$user->is_registered || phpbb_request::is_set('not_agreed'))
		{
			redirect(append_sid('index'));
		}

		$module->load('ucp', 'register');
		$module->display(phpbb::$user->lang['REGISTER']);
	break;

	case 'confirm':
		$module->load('ucp', 'confirm');
	break;

	case 'login':
		if (phpbb::$user->is_registered)
		{
			phpbb::$url->redirect(phpbb::$url->append_sid('index'));
		}

		login_box(request_var('redirect', 'index'));
	break;

	case 'logout':
		if (!phpbb::$user->is_guest && phpbb_request::variable('sid', '', false, phpbb_request::GET) === phpbb::$user->session_id)
		{
			phpbb::$user->session_kill();
			phpbb::$user->session_begin();
			$message = phpbb::$user->lang['LOGOUT_REDIRECT'];
		}
		else
		{
			$message = (phpbb::$user->is_guest) ? phpbb::$user->lang['LOGOUT_REDIRECT'] : phpbb::$user->lang['LOGOUT_FAILED'];
		}
		phpbb::$url->meta_refresh(3, phpbb::$url->append_sid('index'));

		$message = $message . '<br /><br />' . phpbb::$user->lang('RETURN_INDEX', '<a href="' . phpbb::$url->append_sid('index') . '">', '</a> ');
		trigger_error($message);

	break;

	case 'terms':
	case 'privacy':

		$message = ($mode == 'terms') ? 'TERMS_OF_USE_CONTENT' : 'PRIVACY_POLICY';
		$title = ($mode == 'terms') ? 'TERMS_USE' : 'PRIVACY';

		if (empty(phpbb::$user->lang[$message]))
		{
			if (phpbb::$user->is_registered)
			{
				redirect(append_sid('index'));
			}

			login_box();
		}

		phpbb::$template->set_filenames(array(
			'body'		=> 'ucp_agreement.html',
		));

		// Disable online list
		page_header(phpbb::$user->lang[$title], false);

		phpbb::$template->assign_vars(array(
			'S_AGREEMENT'			=> true,
			'AGREEMENT_TITLE'		=> phpbb::$user->lang[$title],
			'AGREEMENT_TEXT'		=> sprintf(phpbb::$user->lang[$message], phpbb::$config['sitename'], generate_board_url()),
			'U_BACK'				=> append_sid('ucp', 'mode=login'),
			'L_BACK'				=> phpbb::$user->lang['BACK_TO_LOGIN'],
		));

		page_footer();

	break;

	case 'delete_cookies':

		// Delete Cookies with dynamic names (do NOT delete poll cookies)
		if (confirm_box(true))
		{
			$set_time = time() - 31536000;

			$cookies = phpbb_request::variable_names(phpbb_request::COOKIE);
			foreach ($cookies as $cookie_name)
			{
				$cookie_name = str_replace(phpbb::$config['cookie_name'] . '_', '', $cookie_name);

				// Polls are stored as {cookie_name}_poll_{topic_id}, cookie_name_ got removed, therefore checking for poll_
				if (strpos($cookie_name, 'poll_') !== 0)
				{
					phpbb::$user->set_cookie($cookie_name, '', $set_time);
				}
			}

			phpbb::$user->set_cookie('track', '', $set_time);
			phpbb::$user->set_cookie('u', '', $set_time);
			phpbb::$user->set_cookie('k', '', $set_time);
			phpbb::$user->set_cookie('sid', '', $set_time);

			// We destroy the session here, the user will be logged out nevertheless
			phpbb::$user->session_kill();
			phpbb::$user->session_begin();

			meta_refresh(3, append_sid('index'));

			$message = phpbb::$user->lang['COOKIES_DELETED'] . '<br /><br />' . sprintf(phpbb::$user->lang['RETURN_INDEX'], '<a href="' . append_sid('index') . '">', '</a>');
			trigger_error($message);
		}
		else
		{
			confirm_box(false, 'DELETE_COOKIES', '');
		}

		redirect(append_sid('index'));

	break;

	case 'switch_perm':

		$user_id = request_var('u', 0);

		$sql = 'SELECT *
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $user_id;
		$result = phpbb::$db->sql_query($sql);
		$user_row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if (!phpbb::$acl->acl_get('a_switchperm') || !$user_row || $user_id == phpbb::$user->data['user_id'])
		{
			redirect(append_sid('index'));
		}

		include(PHPBB_ROOT_PATH . 'includes/acp/auth.' . PHP_EXT);

		$auth_admin = new auth_admin();
		if (!$auth_admin->ghost_permissions($user_id, phpbb::$user->data['user_id']))
		{
			redirect(append_sid('index'));
		}

		add_log('admin', 'LOG_ACL_TRANSFER_PERMISSIONS', $user_row['username']);

		$message = sprintf(phpbb::$user->lang['PERMISSIONS_TRANSFERRED'], $user_row['username']) . '<br /><br />' . sprintf(phpbb::$user->lang['RETURN_INDEX'], '<a href="' . append_sid('index') . '">', '</a>');
		trigger_error($message);

	break;

	case 'restore_perm':

		if (!phpbb::$user->data['user_perm_from'] || !phpbb::$acl->acl_get('a_switchperm'))
		{
			redirect(append_sid('index'));
		}

		phpbb::$acl->acl_cache(phpbb::$user->data);

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_perm_from = 0
			WHERE user_id = " . phpbb::$user->data['user_id'];
		phpbb::$db->sql_query($sql);

		$sql = 'SELECT username
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . phpbb::$user->data['user_perm_from'];
		$result = phpbb::$db->sql_query($sql);
		$username = phpbb::$db->sql_fetchfield('username');
		phpbb::$db->sql_freeresult($result);

		add_log('admin', 'LOG_ACL_RESTORE_PERMISSIONS', $username);

		$message = phpbb::$user->lang['PERMISSIONS_RESTORED'] . '<br /><br />' . sprintf(phpbb::$user->lang['RETURN_INDEX'], '<a href="' . append_sid('index') . '">', '</a>');
		trigger_error($message);

	break;

	default:

		// Only registered users can go beyond this point
		if (!phpbb::$user->is_registered)
		{
			if (phpbb::$user->is_bot)
			{
				redirect(append_sid('index'));
			}

			login_box('', phpbb::$user->lang['LOGIN_EXPLAIN_UCP']);
		}

		// Instantiate module system and generate list of available modules
		$module->list_modules('ucp');

		// Check if the zebra module is set
		if ($module->is_active('zebra', 'friends'))
		{
			_display_friends();
		}

		// Do not display subscribed topics/forums if not allowed
		if (!phpbb::$config['allow_topic_notify'] && !phpbb::$config['allow_forum_notify'])
		{
			$module->set_display('main', 'subscribed', false);
		}

		// Select the active module
		$module->set_active($id, $mode);

		// Load and execute the relevant module
		$module->load_active();

		// Assign data to the template engine for the list of modules
		$module->assign_tpl_vars(append_sid('ucp'));

		// Generate the page, do not display/query online list
		$module->display($module->get_page_title(), false);

	break;
}

/**
* Output listing of friends online
*/
function _display_friends()
{
	$update_time = phpbb::$config['load_online_time'] * 60;

	$sql = phpbb::$db->sql_build_query('SELECT_DISTINCT', array(
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

		'WHERE'		=> 'z.user_id = ' . phpbb::$user->data['user_id'] . '
			AND z.friend = 1
			AND u.user_id = z.zebra_id',

		'GROUP_BY'	=> 'z.zebra_id, u.user_id, u.username_clean, u.user_colour, u.username',

		'ORDER_BY'	=> 'u.username_clean ASC',
	));

	$result = phpbb::$db->sql_query($sql);

	while ($row = phpbb::$db->sql_fetchrow($result))
	{
		$which = (time() - $update_time < $row['online_time'] && ($row['viewonline'] || phpbb::$acl->acl_get('u_viewonline'))) ? 'online' : 'offline';

		phpbb::$template->assign_block_vars("friends_{$which}", array(
			'USER_ID'		=> $row['user_id'],

			'U_PROFILE'		=> get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']),
			'USER_COLOUR'	=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),
			'USERNAME'		=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
			'USERNAME_FULL'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
		));
	}
	phpbb::$db->sql_freeresult($result);
}

/**
* Function for assigning a template var if the zebra module got included
*/
function _module_zebra($mode, &$module_row)
{
	phpbb::$template->assign_var('S_ZEBRA_ENABLED', true);

	if ($mode == 'friends')
	{
		phpbb::$template->assign_var('S_ZEBRA_FRIENDS_ENABLED', true);
	}

	if ($mode == 'foes')
	{
		phpbb::$template->assign_var('S_ZEBRA_FOES_ENABLED', true);
	}
}

?>