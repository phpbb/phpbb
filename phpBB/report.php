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
$user->setup();

// var definitions
$post_id = (!empty($_REQUEST['p'])) ? intval($_REQUEST['p']) : 0;
$reason_id = (!empty($_REQUEST['reason_id'])) ? intval($_REQUEST['reason_id']) : 0;
$user_notify = (!empty($_REQUEST['notify']) && $user->data['user_id'] != ANONYMOUS) ? TRUE : FALSE;
$report_text = (!empty($_REQUEST['report_text'])) ? htmlspecialchars(stripslashes($_REQUEST['report_text'])) : '';

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
		trigger_error($error);
	}
}
unset($acl_check_ary);

// Check if the post has already been reported by this user
$sql = 'SELECT *
	FROM ' . REPORTS_TABLE . "
	WHERE post_id = $post_id
		AND user_id = " . $user->data['user_id'];
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
		$return_topic = '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], "<a href=\"viewtopic.$phpEx$SID&amp;p=$post_id#$post_id\">", '</a>');

		trigger_error($user->lang['ALREADY_REPORTED'] . $return_topic);
	}
}
else
{
	$report_id = 0;
}

// Has the report been confirmed?
if (!empty($_POST['reason_id']))
{
	$sql = 'SELECT reason_title 
		FROM ' . REASONS_TABLE . " 
		WHERE reason_id = $reason_id";
	$result = $db->sql_query($sql);

	if (!($row = $db->sql_fetchrow($result)) || (!$report_text && $row['reason_title'] == 'other'))
	{
		trigger_error('EMPTY_REPORT');
	}
	$db->sql_freeresult($result);

	$sql_ary = array(
		'reason_id'	=>	(int) $reason_id,
		'post_id'		=>	(int) $post_id,
		'user_id'		=>	(int) $user->data['user_id'],
		'user_notify'	=>	(int) $user_notify,
		'report_time'	=>	(int) time(),
		'report_text'	=>	(string) $report_text
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
	$row['reason_title'] = strtoupper($row['reason_title']);

	$reason_title = (!empty($user->lang['report_reasons']['TITLE'][$row['reason_title']])) ? $user->lang['report_reasons']['TITLE'][$row['reason_title']] : ucwords(str_replace('_', ' ', $row['reason_title']));

	$reason_desc = (!empty($user->lang['report_reasons']['DESCRIPTION'][$row['reason_title']])) ? $user->lang['report_reasons']['DESCRIPTION'][$row['reason_title']] : $row['reason_desc'];

	$template->assign_block_vars('reason', array(
		'ID'			=>	$row['reason_id'],
		'NAME'			=>	htmlspecialchars($reason_title),
		'DESCRIPTION'	=>	htmlspecialchars($reason_desc),
		'S_SELECTED'	=>	($row['reason_id'] == $reason_id) ? TRUE : FALSE
	));
}

$template->assign_vars(array(
	'REPORT_TEXT'		=>	$report_text,
	'S_REPORT_ACTION'	=>	"report.$phpEx$SID&amp;p=$post_id" . (($report_id) ? "&amp;report_id=$report_id" : ''),

	'S_NOTIFY'				=>	(!empty($user_notify)) ? TRUE : FALSE,
	'S_CAN_NOTIFY'		=>	($user->data['user_id'] == ANONYMOUS) ? FALSE : TRUE
));


generate_forum_nav($forum_data);


// Start output of page
page_header($user->lang['REPORT_TO_ADMIN']);

$template->set_filenames(array(
	'body' => 'report_body.html')
);

page_footer();

?>