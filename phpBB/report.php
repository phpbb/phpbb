<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : report.php 
// STARTED   : Thu Apr 3, 2003
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);

// Start session management
$user->start();
$auth->acl($user->data);
$user->setup('mcp');

// var definitions
$post_id	= request_var('p', 0);
$msg_id		= request_var('pm', 0);
$reason_id	= request_var('reason_id', 0);
$user_notify= (!empty($_REQUEST['notify']) && $user->data['user_id'] != ANONYMOUS) ? true : false;
$report_text= request_var('report_text', '');

if (!$post_id && !$msg_id)
{
	trigger_error('NO_MODE');
}

$redirect_url	= ($post_id) ? "{$phpbb_root_path}viewtopic.$phpEx$SID&p=$post_id#$post_id" : "{$phpbb_root_path}ucp.$phpEx$SID&i=pm&mode=view_messages&action=view_message&p=$msg_id";

// Has the report been cancelled?
if (isset($_POST['cancel']))
{
	redirect($redirect_url);
}

// Grab all relevant data
if ($post_id)
{
	$sql = 'SELECT f.*, t.*, p.*
		FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
		WHERE p.post_id = $post_id
			AND p.topic_id = t.topic_id
			AND p.forum_id = f.forum_id";
}
else if ($msg_id)
{
	// Only the user itself is able to report his Private Messages
	$sql = 'SELECT p.*, t.*
		FROM ' . PRIVMSGS_TABLE . ' p, ' . PRIVMSGS_TO_TABLE . " t
		WHERE t.msg_id = $msg_id
			AND t.user_id = " . $user->data['user_id'] . '
			AND t.msg_id = p.msg_id';
}
else
{
	trigger_error('INVALID_MODE');
}

$result = $db->sql_query($sql);

if (!($report_data = $db->sql_fetchrow($result)))
{
	trigger_error($user->lang['POST_NOT_EXIST']);
}

if ($post_id)
{
	$forum_id = $report_data['forum_id'];
	$topic_id = $report_data['topic_id'];

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
}
else
{
	if (!$config['auth_report_pm'] || !$auth->acl_get('u_pm_report'))
	{
		trigger_error('USER_CANNOT_REPORT');
	}
}

// Check if the post has already been reported by this user
$sql = 'SELECT *
	FROM ' . REPORTS_TABLE . '
	WHERE ' . (($post_id) ? "post_id = $post_id" : "msg_id = $msg_id") . '
		AND user_id = ' . $user->data['user_id'];
$result = $db->sql_query($sql);

if ($row = $db->sql_fetchrow($result))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		// A report exists, extract $row if we're going to display the form

		if (!empty($_POST['reason_id']))
		{
			$report_id = intval($row['report_id']);
		}
		else
		{
			// Overwrite set variables
			extract($row);
		}
	}
	else
	{
		trigger_error($user->lang['ALREADY_REPORTED'] . '<br /><br />' . sprintf($user->lang[(($post_id) ? 'RETURN_TOPIC' : 'RETURN_MESSAGE')], '<a href="' . $redirect_url . '">', '</a>'));
	}
}
else
{
	$report_id = 0;
}

// Has the report been confirmed?
if (!empty($_POST['reason_id']))
{
	$sql = 'SELECT reason_name 
		FROM ' . REASONS_TABLE . " 
		WHERE reason_id = $reason_id";
	$result = $db->sql_query($sql);

	if (!($row = $db->sql_fetchrow($result)) || (!$report_text && $row['reason_name'] == 'other'))
	{
		trigger_error('EMPTY_REPORT');
	}
	$db->sql_freeresult($result);

	$sql_ary = array(
		'reason_id'		=> (int) $reason_id,
		'post_id'		=> (int) $post_id,
		'msg_id'		=> (int) $msg_id,
		'user_id'		=> (int) $user->data['user_id'],
		'user_notify'	=> (int) $user_notify,
		'report_time'	=> (int) time(),
		'report_text'	=> (string) $report_text
	);

	if ($report_id)
	{
		$sql = 'UPDATE ' . REPORTS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE report_id = ' . $report_id;
		$db->sql_query($sql);
	}
	else
	{
		$sql = 'INSERT INTO ' . REPORTS_TABLE . ' ' . 
			$db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);
	}

	if ($post_id)
	{
		if (!$row['post_reported'])
		{
			$sql = 'UPDATE ' . POSTS_TABLE . ' 
				SET post_reported = 1 
				WHERE post_id = ' . $post_id;
			$db->sql_query($sql);
		}

		if (!$row['topic_reported'])
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . ' 
				SET topic_reported = 1 
				WHERE topic_id = ' . $topic_id;
			$db->sql_query($sql);
		}
	}
	else
	{
		if (!$row['message_reported'])
		{
			$sql = 'UPDATE ' . PRIVMSGS_TABLE . " 
				SET message_reported = 1 
				WHERE msg_id = $msg_id";
			$db->sql_query($sql);
		}
	}

	meta_refresh(3, $redirect_url);

	$message = $user->lang[(($post_id) ? 'POST' : 'MESSAGE') . '_REPORTED_SUCCESS'] . '<br /><br />' . sprintf($user->lang[(($post_id) ? 'RETURN_TOPIC' : 'RETURN_MESSAGE')], '<a href="' . $redirect_url . '">', '</a>');
	trigger_error($message);

	// Which moderators are responsible for private messages? ;)
	/*
		$db->sql_query('INSERT INTO ' . PRIVMSGS_TO_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'msg_id'	=> (int) $msg_id,
			'user_id'	=> (int) $moderator_id,
			'author_id'	=> (int) $row['author_id'],
			'folder_id'	=> PRIVMSGS_NO_BOX,
			'new'		=> 1,
			'unread'	=> 1,
			'forwarded'	=> 0,
			'reported'	=> 1)
		);
	*/
	
	// TODO: warn moderators or something ;)
}


// Generate the form
$sql = 'SELECT * 
	FROM ' . REASONS_TABLE . ' 
	ORDER BY reason_priority ASC';
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$row['reason_name'] = strtoupper($row['reason_name']);

	$reason_title = (!empty($user->lang['report_reasons']['TITLE'][$row['reason_name']])) ? $user->lang['report_reasons']['TITLE'][$row['reason_name']] : ucwords(str_replace('_', ' ', $row['reason_name']));

	$reason_desc = (!empty($user->lang['report_reasons']['DESCRIPTION'][$row['reason_name']])) ? $user->lang['report_reasons']['DESCRIPTION'][$row['reason_name']] : $row['reason_desc'];

	$template->assign_block_vars('reason', array(
		'ID'			=>	$row['reason_id'],
		'NAME'			=>	htmlspecialchars($reason_title),
		'DESCRIPTION'	=>	htmlspecialchars($reason_desc),
		'S_SELECTED'	=>	($row['reason_id'] == $reason_id) ? TRUE : FALSE
	));
}

$u_report = ($post_id) ? "p=$post_id" : "pm=$msg_id";

$template->assign_vars(array(
	'REPORT_TEXT'		=>	$report_text,
	'S_REPORT_ACTION'	=>	"report.$phpEx$SID&amp;$u_report" . (($report_id) ? "&amp;report_id=$report_id" : ''),

	'S_NOTIFY'			=>	(!empty($user_notify)) ? TRUE : FALSE,
	'S_CAN_NOTIFY'		=>	($user->data['user_id'] == ANONYMOUS) ? FALSE : TRUE)
);

if ($post_id)
{
	generate_forum_nav($report_data);
}

// Start output of page
page_header($user->lang['REPORT_TO_ADMIN']);

$template->set_filenames(array(
	'body' => 'report_body.html')
);

page_footer();

?>