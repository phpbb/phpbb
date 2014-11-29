<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
include($phpbb_root_path . 'includes/functions_mcp.' . $phpEx);
require($phpbb_root_path . 'includes/functions_module.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mcp');

$module = new p_master();

// Setting a variable to let the style designer know where he is...
$template->assign_var('S_IN_MCP', true);

// Basic parameter data
$id = request_var('i', '');

$mode = request_var('mode', array(''));
$mode = sizeof($mode) ? array_shift($mode) : request_var('mode', '');

// Only Moderators can go beyond this point
if (!$user->data['is_registered'])
{
	if ($user->data['is_bot'])
	{
		redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
	}

	login_box('', $user->lang['LOGIN_EXPLAIN_MCP']);
}

$quickmod = (isset($_REQUEST['quickmod'])) ? true : false;
$action = request_var('action', '');
$action_ary = request_var('action', array('' => 0));

$forum_action = request_var('forum_action', '');
if ($forum_action !== '' && $request->variable('sort', false, false, \phpbb\request\request_interface::POST))
{
	$action = $forum_action;
}

if (sizeof($action_ary))
{
	list($action, ) = each($action_ary);
}
unset($action_ary);

if ($mode == 'topic_logs')
{
	$id = 'logs';
	$quickmod = false;
}

$post_id = request_var('p', 0);
$topic_id = request_var('t', 0);
$forum_id = request_var('f', 0);
$report_id = request_var('r', 0);
$user_id = request_var('u', 0);
$username = utf8_normalize_nfc(request_var('username', '', true));

if ($post_id)
{
	// We determine the topic and forum id here, to make sure the moderator really has moderative rights on this post
	$sql = 'SELECT topic_id, forum_id
		FROM ' . POSTS_TABLE . "
		WHERE post_id = $post_id";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$topic_id = (int) $row['topic_id'];
	$forum_id = (int) $row['forum_id'];
}
else if ($topic_id)
{
	$sql = 'SELECT forum_id
		FROM ' . TOPICS_TABLE . "
		WHERE topic_id = $topic_id";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$forum_id = (int) $row['forum_id'];
}

// If the user doesn't have any moderator powers (globally or locally) he can't access the mcp
if (!$auth->acl_getf_global('m_'))
{
	// Except he is using one of the quickmod tools for users
	$user_quickmod_actions = array(
		'lock'			=> 'f_user_lock',
		'make_sticky'	=> 'f_sticky',
		'make_announce'	=> 'f_announce',
		'make_global'	=> 'f_announce',
		'make_normal'	=> array('f_announce', 'f_sticky')
	);

	$allow_user = false;
	if ($quickmod && isset($user_quickmod_actions[$action]) && $user->data['is_registered'] && $auth->acl_gets($user_quickmod_actions[$action], $forum_id))
	{
		$topic_info = phpbb_get_topic_data(array($topic_id));
		if ($topic_info[$topic_id]['topic_poster'] == $user->data['user_id'])
		{
			$allow_user = true;
		}
	}

	if (!$allow_user)
	{
		trigger_error('NOT_AUTHORISED');
	}
}

// if the user cannot read the forum he tries to access then we won't allow mcp access either
if ($forum_id && !$auth->acl_get('f_read', $forum_id))
{
	trigger_error('NOT_AUTHORISED');
}

/**
* Allow applying additional permissions to MCP access besides f_read
*
* @event core.mcp_global_f_read_auth_after
* @var	string		action			The action the user tried to execute
* @var	int			forum_id		The forum the user tried to access
* @var	string		mode			The MCP module the user is trying to access
* @var	p_master	module			Module system class
* @var	bool		quickmod		True if the user is accessing using quickmod tools
* @var	int			topic_id		The topic the user tried to access
* @since 3.1.3-RC1
*/
$vars = array(
	'action',
	'forum_id',
	'mode',
	'module',
	'quickmod',
	'topic_id',
);
extract($phpbb_dispatcher->trigger_event('core.mcp_global_f_read_auth_after', compact($vars)));

if ($forum_id)
{
	$module->acl_forum_id = $forum_id;
}

// Instantiate module system and generate list of available modules
$module->list_modules('mcp');

if ($quickmod)
{
	$mode = 'quickmod';

	switch ($action)
	{
		case 'lock':
		case 'unlock':
		case 'lock_post':
		case 'unlock_post':
		case 'make_sticky':
		case 'make_announce':
		case 'make_global':
		case 'make_normal':
		case 'fork':
		case 'move':
		case 'delete_post':
		case 'delete_topic':
		case 'restore_topic':
			$module->load('mcp', 'main', 'quickmod');
			return;
		break;

		case 'topic_logs':
			// Reset start parameter if we jumped from the quickmod dropdown
			if (request_var('start', 0))
			{
				$request->overwrite('start', 0);
			}

			$module->set_active('logs', 'topic_logs');
		break;

		case 'merge_topic':
			$module->set_active('main', 'forum_view');
		break;

		case 'split':
		case 'merge':
			$module->set_active('main', 'topic_view');
		break;

		default:
			// If needed, the flag can be set to true within event listener
			// to indicate that the action was handled properly
			// and to pass by the trigger_error() call below
			$is_valid_action = false;

			/**
			* This event allows you to add custom quickmod options
			*
			* @event core.modify_quickmod_options
			* @var	object	module			Instance of module system class
			* @var	string	action			Quickmod option
			* @var	bool	is_valid_action	Flag indicating if the action was handled properly
			* @since 3.1.0-a4
			*/
			$vars = array('module', 'action', 'is_valid_action');
			extract($phpbb_dispatcher->trigger_event('core.modify_quickmod_options', compact($vars)));

			if (!$is_valid_action)
			{
				trigger_error($user->lang('QUICKMOD_ACTION_NOT_ALLOWED', $action), E_USER_ERROR);
			}
		break;
	}
}
else
{
	// Select the active module
	$module->set_active($id, $mode);
}

// Hide some of the options if we don't have the relevant information to use them
if (!$post_id)
{
	$module->set_display('main', 'post_details', false);
	$module->set_display('warn', 'warn_post', false);
}

if ($mode == '' || $mode == 'unapproved_topics' || $mode == 'unapproved_posts' || $mode == 'deleted_topics' || $mode == 'deleted_posts')
{
	$module->set_display('queue', 'approve_details', false);
}

if ($mode == '' || $mode == 'reports' || $mode == 'reports_closed' || $mode == 'pm_reports' || $mode == 'pm_reports_closed' || $mode == 'pm_report_details')
{
	$module->set_display('reports', 'report_details', false);
}

if ($mode == '' || $mode == 'reports' || $mode == 'reports_closed' || $mode == 'pm_reports' || $mode == 'pm_reports_closed' || $mode == 'report_details')
{
	$module->set_display('pm_reports', 'pm_report_details', false);
}

if (!$topic_id)
{
	$module->set_display('main', 'topic_view', false);
	$module->set_display('logs', 'topic_logs', false);
}

if (!$forum_id)
{
	$module->set_display('main', 'forum_view', false);
	$module->set_display('logs', 'forum_logs', false);
}

if (!$user_id && $username == '')
{
	$module->set_display('notes', 'user_notes', false);
	$module->set_display('warn', 'warn_user', false);
}

/**
* This event allows you to set display option for custom MCP modules
*
* @event core.modify_mcp_modules_display_option
* @var	p_master	module			Module system class
* @var	string		mode			MCP mode
* @var	int			user_id			User id
* @var	int			forum_id		Forum id
* @var	int			topic_id		Topic id
* @var	int			post_id			Post id
* @var	string		username		User name
* @var	int			id				Parent module id
* @since 3.1.0-b2
*/
$vars = array(
	'module',
	'mode',
	'user_id',
	'forum_id',
	'topic_id',
	'post_id',
	'username',
	'id',
);
extract($phpbb_dispatcher->trigger_event('core.modify_mcp_modules_display_option', compact($vars)));

// Load and execute the relevant module
$module->load_active();

// Assign data to the template engine for the list of modules
$module->assign_tpl_vars(append_sid("{$phpbb_root_path}mcp.$phpEx"));

// Generate urls for letting the moderation control panel being accessed in different modes
$template->assign_vars(array(
	'U_MCP'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=main'),
	'U_MCP_FORUM'	=> ($forum_id) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=main&amp;mode=forum_view&amp;f=$forum_id") : '',
	'U_MCP_TOPIC'	=> ($forum_id && $topic_id) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=main&amp;mode=topic_view&amp;t=$topic_id") : '',
	'U_MCP_POST'	=> ($forum_id && $topic_id && $post_id) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=main&amp;mode=post_details&amp;t=$topic_id&amp;p=$post_id") : '',
));

// Generate the page, do not display/query online list
$module->display($module->get_page_title());
