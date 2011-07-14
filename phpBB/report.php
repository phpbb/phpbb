<?php
/**
*
* @package phpBB3
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
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mcp');

$forum_id		= request_var('f', 0);
$post_id		= request_var('p', 0);
$pm_id			= request_var('pm', 0);
$reason_id		= request_var('reason_id', 0);
$report_text	= utf8_normalize_nfc(request_var('report_text', '', true));
$user_notify	= ($user->data['is_registered']) ? request_var('notify', 0) : false;

$submit = (isset($_POST['submit'])) ? true : false;

if (!$post_id && (!$pm_id || !$config['allow_pm_report']))
{
	trigger_error('NO_POST_SELECTED');
}

if ($post_id)
{
	$redirect_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;p=$post_id") . "#p$post_id";
	$return_forum_url = append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$forum_id");
	$pm_id = 0;
}
else
{
	$redirect_url = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=pm&mode=view&p=$pm_id");
	$return_forum_url = '';
	$post_id = 0;
	$forum_id = 0;
}

// Has the report been cancelled?
if (isset($_POST['cancel']))
{
	redirect($redirect_url);
}

if ($post_id)
{
	// Grab all relevant data
	$sql = 'SELECT t.*, p.*
		FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . " t
		WHERE p.post_id = $post_id
			AND p.topic_id = t.topic_id";
	$result = $db->sql_query($sql);
	$report_data = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$report_data)
	{
		trigger_error('POST_NOT_EXIST');
	}

	$forum_id = (int) ($report_data['forum_id']) ? $report_data['forum_id'] : $forum_id;
	$topic_id = (int) $report_data['topic_id'];

	$sql = 'SELECT *
		FROM ' . FORUMS_TABLE . '
		WHERE forum_id = ' . $forum_id;
	$result = $db->sql_query($sql);
	$forum_data = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$forum_data)
	{
		trigger_error('FORUM_NOT_EXIST');
	}

	// Check required permissions
	$acl_check_ary = array('f_list' => 'POST_NOT_EXIST', 'f_read' => 'USER_CANNOT_READ', 'f_report' => 'USER_CANNOT_REPORT');

	foreach ($acl_check_ary as $acl => $error)
	{
		if (!$auth->acl_get($acl, $forum_id))
		{
			trigger_error($error);
		}
	}
	unset($acl_check_ary);

	if ($report_data['post_reported'])
	{
		$message = $user->lang['ALREADY_REPORTED'];
		$message .= '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $redirect_url . '">', '</a>');
		$message .= '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="' . $return_forum_url . '">', '</a>');
		trigger_error($message);
	}
}
else
{
	// Grab all relevant data
	$sql = 'SELECT p.*, pt.*
		FROM ' . PRIVMSGS_TABLE . ' p, ' . PRIVMSGS_TO_TABLE . " pt
		WHERE p.msg_id = $pm_id
			AND p.msg_id = pt.msg_id
			AND (p.author_id = " . $user->data['user_id'] . " OR pt.user_id = " . $user->data['user_id'] . ")";
	$result = $db->sql_query($sql);
	$report_data = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$report_data)
	{
		$user->add_lang('ucp');
		trigger_error('NO_MESSAGE');
	}

	if ($report_data['message_reported'])
	{
		$message = $user->lang['ALREADY_REPORTED_PM'];
		$message .= '<br /><br />' . sprintf($user->lang['RETURN_PM'], '<a href="' . $redirect_url . '">', '</a>');
		trigger_error($message);
	}
}

// Submit report?
if ($submit && $reason_id)
{
	$sql = 'SELECT *
		FROM ' . REPORTS_REASONS_TABLE . "
		WHERE reason_id = $reason_id";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row || (!$report_text && strtolower($row['reason_title']) == 'other'))
	{
		trigger_error('EMPTY_REPORT');
	}

	$sql_ary = array(
		'reason_id'		=> (int) $reason_id,
		'post_id'		=> $post_id,
		'pm_id'			=> $pm_id,
		'user_id'		=> (int) $user->data['user_id'],
		'user_notify'	=> (int) $user_notify,
		'report_closed'	=> 0,
		'report_time'	=> (int) time(),
		'report_text'	=> (string) $report_text
	);

	$sql = 'INSERT INTO ' . REPORTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
	$db->sql_query($sql);
	$report_id = $db->sql_nextid();

	if ($post_id)
	{
		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET post_reported = 1
			WHERE post_id = ' . $post_id;
		$db->sql_query($sql);

		if (!$report_data['topic_reported'])
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_reported = 1
				WHERE topic_id = ' . $report_data['topic_id'] . '
					OR topic_moved_id = ' . $report_data['topic_id'];
			$db->sql_query($sql);
		}

		$lang_return = $user->lang['RETURN_TOPIC'];
		$lang_success = $user->lang['POST_REPORTED_SUCCESS'];
	}
	else
	{
		$sql = 'UPDATE ' . PRIVMSGS_TABLE . '
			SET message_reported = 1
			WHERE msg_id = ' . $pm_id;
		$db->sql_query($sql);

		$sql_ary = array(
			'msg_id'		=> $pm_id,
			'user_id'		=> ANONYMOUS,
			'author_id'		=> (int) $report_data['author_id'],
			'pm_deleted'	=> 0,
			'pm_new'		=> 0,
			'pm_unread'		=> 0,
			'pm_replied'	=> 0,
			'pm_marked'		=> 0,
			'pm_forwarded'	=> 0,
			'folder_id'		=> PRIVMSGS_INBOX,
		);

		$sql = 'INSERT INTO ' . PRIVMSGS_TO_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		$lang_return = $user->lang['RETURN_PM'];
		$lang_success = $user->lang['PM_REPORTED_SUCCESS'];
	}

	meta_refresh(3, $redirect_url);

	$message = $lang_success . '<br /><br />' . sprintf($lang_return, '<a href="' . $redirect_url . '">', '</a>');
	if ($return_forum_url)
	{
		$message .= '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="' . $return_forum_url . '">', '</a>');
	}
	trigger_error($message);
}

// Generate the reasons
display_reasons($reason_id);

$page_title = ($pm_id) ? $user->lang['REPORT_MESSAGE'] : $user->lang['REPORT_POST'];

$template->assign_vars(array(
	'S_REPORT_POST'		=> ($pm_id) ? false : true,
	'REPORT_TEXT'		=> $report_text,
	'S_REPORT_ACTION'	=> append_sid("{$phpbb_root_path}report.$phpEx", 'f=' . $forum_id . '&amp;p=' . $post_id . '&amp;pm=' . $pm_id),

	'S_NOTIFY'			=> $user_notify,
	'S_CAN_NOTIFY'		=> ($user->data['is_registered']) ? true : false)
);

generate_forum_nav($forum_data);

// Start output of page
page_header($page_title);

$template->set_filenames(array(
	'body' => 'report_body.html')
);

page_footer();

?>