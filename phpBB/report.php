<?php
/***************************************************************************
 *                                report.php
 *                            -------------------
 *   begin                : July 4, 2001
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

define('IN_PHPBB', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

// Start session management
$user->start();
$user->setup();
$auth->acl($user->data);


// var definitions
$post_id = (!empty($_REQUEST['p'])) ? intval($_REQUEST['p']) : 0;
$reason_id = (!empty($_REQUEST['reason_id'])) ? intval($_REQUEST['reason_id']) : 0;
$notify = (!empty($_REQUEST['notify']) && $user->data['user_id'] != ANONYMOUS) ? TRUE : FALSE;
$description = (!empty($_REQUEST['description'])) ? stripslashes($_REQUEST['description']) : '';


// Has the report been cancelled?
if (isset($_POST['cancel']))
{
	redirect("viewtopic.$phpEx$SID&p=$post_id#$post_id");
}


// Grab all relevant data
$sql = 'SELECT f.*, t.*, p.*
	FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
	WHERE p.post_id = $post_id
		AND p.topic_id = t.topic_id
		AND p.forum_id = f.forum_id";
$result = $db->sql_query($sql);

if (!($forum_data = $db->sql_fetchrow($result)))
{
	trigger_error($user->lang['POST_NOT_EXIST']);
}

$forum_id = $forum_data['forum_id'];
$topic_id = $forum_data['topic_id'];


// Check required permissions
$acl_check_ary = array('f_list' => 'POST_NOT_EXIST', 'f_read' => 'USER_CANNOT_READ', 'f_report' => 'USER_CANNOT_REPORT');
foreach ($acl_check_ary as $acl => $error)
{
	if (!$auth->acl_get($acl, $forum_id))
	{
		trigger_error($user->lang[$error]);
	}
}
unset($acl_check_ary);


// Has the report been confirmed?
if (!empty($_POST['reason_id']))
{
	$sql = 'SELECT reason_name 
		FROM ' . REASONS_TABLE . " 
		WHERE reason_id = $reason_id";
	$result = $db->sql_query($sql);

	if (!($row = $db->sql_fetchrow($result)) || (!$description && $row['reason_name'] == 'other'))
	{
		trigger_error('EMPTY_REPORT');
	}
	$db->sql_freeresult($result);

	$sql_ary = array(
		'reason_id'		=>	(int) $reason_id,
		'post_id'		=>	(int) $post_id,
		'user_id'		=>	(int) $user->user_id,
		'user_notify'	=>	(int) $notify,
		'report_time'	=>	(int) time(),
		'report_text'	=>	(string) $description
	);

	$sql = 'INSERT INTO ' . REPORTS_TABLE . ' ' . 
		$db->sql_build_array('INSERT', $sql_ary);
	$db->sql_query($sql);

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

	meta_refresh(3, "viewtopic.$phpEx$SID&amp;p=$post_id#$post_id");

	$message = $user->lang['POST_REPORTED_SUCCESS'] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], "<a href=\"viewtopic.$phpEx$SID&amp;p=$post_id#$post_id\">", '</a>');
	trigger_error($message);

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

	$reason_name = (!empty($user->lang['REPORT_REASONS']['TITLE'][$row['reason_name']])) ? $user->lang['REPORT_REASONS']['TITLE'][$row['reason_name']] : ucwords(str_replace('_', ' ', $row['reason_name']));

	$reason_description = (!empty($user->lang['REPORT_REASONS']['DESCRIPTION'][$row['reason_name']])) ? $user->lang['REPORT_REASONS']['DESCRIPTION'][$row['reason_name']] : $row['reason_description'];

	$template->assign_block_vars('reason', array(
		'ID'			=>	$row['reason_id'],
		'NAME'			=>	htmlspecialchars($reason_name),
		'DESCRIPTION'	=>	htmlspecialchars($reason_description))
	);
}

$template->assign_var('S_CAN_NOTIFY', ($user->data['user_id'] == ANONYMOUS) ? FALSE : TRUE);


generate_forum_nav($forum_data);


// Start output of page
page_header($user->lang['REPORT_TO_ADMIN']);

$template->set_filenames(array(
	'body' => 'report_body.html')
);

page_tail();

?>