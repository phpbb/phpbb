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
// End session management

// temp temp temp
very_temporary_lang_strings();
// temp temp temp

// var definitions
$post_id = (!empty($_REQUEST['p'])) ? intval($_REQUEST['p']) : 0;
$reason_id = (!empty($_REQUEST['reason_id'])) ? intval($_REQUEST['reason_id']) : 0;
$description = (!empty($_REQUEST['description'])) ? stripslashes($_REQUEST['description']) : '';

// Start output of page
$page_title = $user->lang['REPORT_TO_ADMIN'];
include($phpbb_root_path . 'includes/page_header.' . $phpEx);

$sql = 'SELECT f.*, t.*, p.*
	FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
	WHERE p.post_id = $post_id
		AND p.topic_id = t.topic_id
		AND p.forum_id = f.forum_id";
$result = $db->sql_query($sql);

if (!$row = $db->sql_fetchrow($result))
{
	trigger_error('POST_NOT_EXIST');
}

$forum_id = $row['forum_id'];
$topic_id = $row['topic_id'];

// Checking permissions
if (!$auth->acl_gets('f_list', 'm_', 'a_', $forum_id))
{
	trigger_error('POST_NOT_EXIST');
}
if (!$auth->acl_gets('f_read', 'm_', 'a_', $forum_id))
{
	trigger_error('USER_CANNOT_READ');
}

// Has the report been cancelled?
if (isset($_POST['cancel']))
{
	redirect("viewtopic.$phpEx$SID&p=$post_id#$post_id");
}

// Has the report been confirmed?
if (!empty($_POST['reason_id']))
{
	$sql_ary = array(
		'reason_id'		=>	(int) $reason_id,
		'post_id'		=>	(int) $post_id,
		'user_id'		=>	(int) $user->user_id,
		'report_time'	=>	(int) time(),
		'report_text'	=>	(string) $description
	);

	$sql = 'INSERT INTO ' . REPORTS_TABLE . " (reason_id, post_id, user_id, report_time, report_text)
		VALUES ($reason_id, $post_id, " . $user->data['user_id'] . ', ' . time() . ", '" . $db->sql_escape($description) . "')";
	$db->sql_query($sql);

	if (!$row['post_reported'])
	{
		$db->sql_query('UPDATE ' . POSTS_TABLE . ' SET post_reported = 1 WHERE post_id = ' . $post_id);
	}
	if (!$row['topic_reported'])
	{
		$db->sql_query('UPDATE ' . TOPICS_TABLE . ' SET topic_reported = 1 WHERE topic_id = ' . $topic_id);
	}

	trigger_error($user->lang['POST_REPORTED'] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], "<a href=\"viewtopic.$phpEx$SID&amp;p=$post_id#$post_id\">", '</a>'));
}

// Generate the form

generate_forum_nav($row);

$result = $db->sql_query('SELECT * FROM ' . REASONS_TABLE . ' ORDER BY reason_priority ASC');
while ($row = $db->sql_fetchrow($result))
{
	$reason_name = str_replace('_', ' ', $row['reason_name']);
	$reason_name = ucwords($reason_name);

	if (!empty($user->lang['reports_reasons'][$row['reason_name']]))
	{
		$reason_description = $user->lang['reports_reasons'][$row['reason_name']];
	}
	else
	{
		$reason_description = $row['reason_description'];
	}

	$template->assign_block_vars('reason', array(
		'ID'			=>	$row['reason_id'],
		'NAME'			=>	htmlspecialchars($reason_name),
		'DESCRIPTION'	=>	htmlspecialchars($reason_description)
	));
}

$template->set_filenames(array(
	'body' => 'report.html'
));

include($phpbb_root_path . 'includes/page_tail.' . $phpEx);


function very_temporary_lang_strings()
{
	global $user;
	$user->lang['reports_reasons'] = array(
		'warez'				=>	'The post contains links to illegal or pirated software'
	);


	$lang = array(
		'REASON'					=>	'Reason',
		'ADDITIONAL_INFOS'			=>	'Additional infos',
		'CAN_BE_LEFT_BLANK'			=>	'(can be left blank)',

		'POST_NOT_EXIST'			=>	'The post you requested does not exist',

		'REPORT_TO_ADMIN_EXPLAIN'	=>	'Using this forum you can report the selected post to admins.',

		'REPORT_NOTIFY'				=>	'Notify me when this report is reviewed',
		'POST_REPORTED'				=>	'This post has been successfully reported'
	);

	$user->lang = array_merge($user->lang, $lang);

}

?>