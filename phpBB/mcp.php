<?php
/***************************************************************************
 *                                 mcp.php
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

// TODO for 2.2:
//
// * Plug-in based?
// * Add session_id checks for all Moderator ops
// * Tab based system
// * Front page:
//    * Select box listing all forums to which user has moderator rights
//    * Five(?) most recent Moderator log entries (for relevant forum/s)
//    * Five(?) most recent Moderator note entries (for relevant forum/s)
//    * Five(?) most recent Report to Moderator messages (for relevant forum/s)
//    * Note that above three, bar perhaps log entries could be on other tabs but with counters
//      or some such on front page indicating new messages are present
//    * List of topics awaiting Moderator approval (if appropriate and for relevant forum/s)
// * Topic view:
//    * As current(?) plus differing colours for Approved/Unapproved topics/posts
//    * When moving topics to forum for which Mod doesn't have Mod rights set for Mod approval
// * Find duplicates:
//    * List supiciously similar posts across forum/s
// * "Ban" user/s:
//    * Limit read/post/reply/etc. permissions
// * Posts/topics deletion!
//    * Leave a reason for logging purpose?

define('IN_PHPBB', true);
define('NEED_SID', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);


// Start session management
$user->start();
$user->setup();
$auth->acl($user->data);


// Obtain initial var settings
$forum_id = (isset($_REQUEST['f'])) ? max(0, intval($_REQUEST['f'])) : 0;
$topic_id = (!empty($_REQUEST['t'])) ? intval($_REQUEST['t']) : 0;
$post_id = (!empty($_REQUEST['p'])) ? intval($_REQUEST['p']) : 0;
$start = (!empty($_REQUEST['start'])) ? intval($_REQUEST['start']) : 0;

$forum_data = $topic_data = $post_data = array();
$topic_id_list = ($topic_id) ? array($topic_id) : array();
$post_id_list = ($post_id) ? array($post_id) : array();

$to_forum_id = (!empty($_REQUEST['to_forum_id'])) ? intval($_REQUEST['to_forum_id']) : 0;
$to_topic_id = (!empty($_REQUEST['to_topic_id'])) ? intval($_REQUEST['to_topic_id']) : 0;

$confirm = (!empty($_POST['confirm'])) ? TRUE : FALSE;
$mode = (!empty($_REQUEST['mode'])) ? $_REQUEST['mode'] : '';
$action = (!empty($_GET['action'])) ? $_GET['action'] : '';
$quickmod = (!empty($_REQUEST['quickmod'])) ? TRUE : FALSE;


// Check if user did or did not confirm
// If they did not, forward them to the last page they were on
if (isset($_POST['cancel']))
{
	$redirect = '';
	if ($mode == 'forum_info')
	{
		$mode = 'front';
	}
	elseif ($topic_id > 0)
	{
		$redirect = ($quickmod) ? "viewtopic.$phpEx$SID&f=$forum_id&t=$topic_id&start=$start" : "mcp.$phpEx$SID&t=$topic_id&start=$start";
	}
	elseif ($forum_id > 0)
	{
		$redirect = ($quickmod) ? "viewforum.$phpEx$SID&t=$forum_id&start=$start" : "mcp.$phpEx$SID&t=$forum_id&start=$start";
	}
	else
	{
		$redirect = ($quickmod) ? "index.$phpEx$SID" : "mcp.$phpEx$SID";
	}

	if ($redirect)
	{
		redirect($redirect);
	}
}

$subject = (!empty($_REQUEST['subject'])) ? $_REQUEST['subject'] : '';

$post_modes = array('approve', 'disapprove', 'move', 'fork', 'delete_topic', 'lock', 'unlock', 'merge_posts', 'delete_posts', 'split_all', 'split_beyond', 'select_topic', 'resync');
foreach ($post_modes as $post_mode)
{
	if (isset($_POST[$post_mode]))
	{
		$mode = $post_mode;
		break;
	}
}

// Cleanse inputted values
foreach ($_POST['topic_id_list'] as $t_id)
{
	if ($t_id = intval($t_id))
	{
		$topic_id_list[] = $t_id;
	}
}
foreach ($_POST['post_id_list'] as $p_id)
{
	if ($p_id = intval($p_id))
	{
		$post_id_list[] = $p_id;
	}
}

// Build short_id_list and $return string
$selected_post_ids = array();
if (!empty($_GET['post_id_list']))
{
	$len = $_GET['post_id_list']{0};
	for ($i = 1; $i < strlen($_GET['post_id_list']); $i += $len)
	{
		$short = substr($_GET['post_id_list'], $i, $len);
		$selected_post_ids[] = (int) base_convert($short, 36, 10);
		$post_id_list[] = base_convert($short, 36, 10);
	}
}
$url_extra = (!empty($post_id_list)) ? '&amp;post_id_list=' . short_id_list($post_id_list ) : '';
$return_mcp = '<br /><br />' . sprintf($user->lang['RETURN_MCP'], '<a href="mcp.' . $phpEx . $SID . '">', '</a>');

// Build up return links and acl list
// $acl_src contains the acl list for source forum(s)
// $acl_trg contains the acl list for destination forum(s)

$acl_src = 'm_';
$acl_trg = 'm_';
$return_mode = '<br /><br />' . sprintf($user->lang['RETURN_MCP'], '<a href="mcp.' . $phpEx . $SID . '">', '</a>');

switch ($mode)
{
	case 'make_global':
	case 'make_announce':
		$acl_src = 'f_announce';
		break;

	case 'make_sticky':
		$acl_src = 'f_sticky';
		break;

	case 'approve':
	case 'unapprove':
	case 'disapprove':
		$acl_src = 'm_approve';
		break;

	case 'split':
	case 'split_all':
	case 'split_beyond':
		$acl_src = 'a_';
		$acl_trg = 'f_post';

		$return_mode = '<br /><br />' . sprintf($user->lang['RETURN_MCP'], '<a href="mcp.' . $phpEx . $SID . '&amp;mode=split&amp;t=' . $topic_id . $url_extra . '&subject=' . htmlspecialchars($subject) . '">', '</a>');
		break;

	case 'merge':
	case 'merge_posts':
		$acl_src = 'm_merge';
		$acl_trg = 'm_merge';

		$return_mode = '<br /><br />' . sprintf($user->lang['RETURN_MCP'], '<a href="mcp.' . $phpEx . $SID . '&amp;mode=merge&amp;t=' . $topic_id . $url_extra . '">', '</a>');
		break;

	case 'move':
		$acl_src = 'm_move';
		$acl_trg = 'f_post';
		break;

	case 'viewlogs':
		$acl_src = array('m_', 'a_');
		break;

	case 'fork':
		$acl_trg = 'f_post';
}

// Check destination forum or topic if applicable
if ($to_topic_id > 0)
{
	$sql = 'SELECT * 
		FROM ' . TOPICS_TABLE . ' 
		WHERE topic_id = ' . $to_topic_id;
	$result = $db->sql_query($sql);

	if (!$row = $db->sql_fetchrow($result))
	{
		trigger_error($user->lang['TOPIC_NOT_EXIST'] . $return_mode);
	}
	$db->sql_freeresult($result);

	if (!isset($topic_data[$to_topic_id]))
	{
		$topic_data[$to_topic_id] = $row;
	}

	$to_forum_id = $row['forum_id'];
}

if ($to_forum_id > 0)
{
	if (!isset($forum_data[$to_forum_id]))
	{
		$sql = 'SELECT * 
			FROM ' . FORUMS_TABLE . ' 
			WHERE forum_id = ' . $to_forum_id;
		$result = $db->sql_query($sql);

		if (!$row = $db->sql_fetchrow($result))
		{
			trigger_error($user->lang['FORUM_NOT_EXIST'] . $return_mode);
		}
		$db->sql_freeresult($result);

		$forum_data[$to_forum_id] = $row;
	}

	if (!$auth->acl_get('f_list', $to_forum_id))
	{
		trigger_error($user->lang['FORUM_NOT_EXIST'] . $return_mode);
	}
}

// Reset id lists then rebuild them from verified data
$topic_id_sql = implode(', ', array_unique($topic_id_list));
$post_id_sql = implode(', ', array_unique($post_id_list));
$forum_id_list = $topic_id_list = $post_id_list = array();
$not_moderator = FALSE;

if ($forum_id > 0)
{
	if ($auth->acl_gets($acl_src, $forum_id))
	{
		$forum_id_list[] = $forum_id;
	}
	else
	{
		$not_moderator = TRUE;
	}
} 

if ($topic_id_sql)
{
	$sql = 'SELECT *
		FROM ' . TOPICS_TABLE . "
		WHERE topic_id IN ($topic_id_sql)";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if ($auth->acl_gets($acl_src, $row['forum_id']))
		{
			$forum_id_list[] = $row['forum_id'];
			$topic_id_list[] = $row['topic_id'];

			$topic_data[$row['topic_id']] = $row;
		}
		else
		{
			$not_moderator = TRUE;
		}
	}

	$db->sql_freeresult($result);
}

if ($post_id_sql)
{
	$sql = 'SELECT *
		FROM ' . POSTS_TABLE . "
		WHERE post_id IN ($post_id_sql)";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if ($auth->acl_gets($acl_src, $row['forum_id']))
		{
			$forum_id_list[] = $row['forum_id'];
			$topic_id_list[] = $row['topic_id'];
			$post_id_list[] = $row['post_id'];

			$post_data[$row['post_id']] = $row;
		}
		else
		{
			$not_moderator = TRUE;
		}
	}

	$db->sql_freeresult($result);
}

$forum_id_list = array_unique($forum_id_list);
$topic_id_list = array_unique($topic_id_list);
$post_id_list = array_unique($post_id_list);

if (count($forum_id_list))
{
	$sql = 'SELECT *
		FROM ' . FORUMS_TABLE . '
		WHERE forum_id IN (' . implode(', ', $forum_id_list) . ')';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$forum_data[$row['forum_id']] = $row;
	}
	$db->sql_freeresult($result);

	// Set infos about current forum/topic/post
	// Uses each() because array_unique may unset index 0 if it's a duplicate
	if (!isset($_REQUEST['f']) && count($forum_id_list) == 1)
	{
		// Using isset() rather than !empty() because of the jumpbox having f="0" for "All forums"
		list($void, $forum_id) = each($forum_id_list);
	}

	if (!$topic_id && count($topic_id_list) == 1)
	{
		list($void, $topic_id) = each($topic_id_list);
	}

	if (!$post_id && count($post_id_list) == 1)
	{
		list($void, $post_id) = each($post_id_list);
	}

	$forum_info = $forum_data[$forum_id];
	$topic_info = $topic_data[$topic_id];
	$post_info = $post_data[$post_id];
}
else
{
	// There's no forums list available so the user either submitted an empty or invalid list of posts/topics or isn't a moderator

	if ($not_moderator || !get_forum_list('m_', TRUE, TRUE, TRUE))
	{
		trigger_error('NOT_MODERATOR');
	}
	else
	{
		// TODO: drop this and deal with each mode individually?
		$forumless_modes = array('front', 'post_reports', 'mod_queue', 'viewlogs', 'forum_info');
		if ($mode != '' && !in_array($mode, $forumless_modes))
		{
			// The user has submitted invalid post_ids or topic_ids
			trigger_error($user->lang['TOPIC_NOT_EXIST'] . $return_mcp);
		}
	}
}

//
// There we're done validating input.
//
// $post_id_list contains the complete list of post_id's, same for $topic_id_list and $forum_id_list
// $post_id, $topic_id, $forum_id have all been set.
//
// $forum_data is an array where $forum_data[<forum_id>] contains the corresponding row, same for $topic_data and $post_data.
// $forum_info is set to $forum_data[$forum_id] for quick reference, same for topic and post.
//
// We know that the user has m_ or a_ access to all the selected forums/topics/posts but we still have to check for specific authorisations.
//

// Build links and tabs
$mcp_url = "mcp.$phpEx$SID";
$tabs = array(
	array(
		'mode'	=>	'front',
		'title'	=>	$user->lang['FRONT_PAGE'],
		'url'	=>	$mcp_url . '&amp;mode=front'
	),
	array(
		'mode'	=>	'mod_queue',
		'title'	=>	$user->lang['MOD_QUEUE'],
		'url'	=>	$mcp_url . '&amp;f=' . $forum_id . '&amp;mode=mod_queue'
	),
	array(
		'mode'	=>	'post_reports',
		'title'	=>	$user->lang['REPORTED_POSTS'],
		'url'	=>	$mcp_url . '&amp;f=' . $forum_id . '&amp;mode=post_reports'
	)
);

$mcp_url .= ($forum_id) ? '&amp;f=' . $forum_id : '';
$mcp_url .= ($topic_id) ? '&amp;t=' . $topic_id : '';
$mcp_url .= ($post_id) ? '&amp;p=' . $post_id : '';
$return_mcp = '<br /><br />' . sprintf($user->lang['RETURN_MCP'], '<a href="' . $mcp_url . '">', '</a>');

$tabs[] = array(
	'mode'	=>	'viewlogs',
	'title'	=>	($topic_id) ? $user->lang['VIEW_TOPIC_LOGS'] : $user->lang['VIEW_LOGS'],
	'url'	=>	$mcp_url . '&amp;mode=viewlogs'
);

if ($forum_id && $forum_data[$forum_id]['forum_type'] == FORUM_POST && $auth->acl_get('m_', $forum_id))
{
	$tabs[] = array(
		'mode'	=>	'forum_view',
		'title'	=>	$user->lang['VIEW_FORUM'],
		'url'	=>	$mcp_url . '&amp;mode=forum_view'
	);
}

if ($auth->acl_get('m_info', $forum_id))
{
	$tabs[] = array(
		'mode'	=>	'forum_info',
		'title'	=>	$user->lang['FORUM_INFO'],
		'url'	=>	$mcp_url . '&amp;mode=forum_info&amp;f=' . $forum_id
	);
}

if ($topic_id && $auth->acl_gets('m_delete', 'm_split', 'm_merge', 'm_approve', $forum_id))
{
	$tabs[] = array(
		'mode'	=>	'topic_view',
		'title'	=>	$user->lang['VIEW_TOPIC'],
		'url'	=>	$mcp_url . '&amp;mode=topic_view'
	);
}

if ($post_id && $auth->acl_gets('m_', $forum_id))
{
	$tabs[] = array(
		'mode'	=>	'post_details',
		'title'	=>	$user->lang['POST_DETAILS'],
		'url'	=>	$mcp_url . '&amp;mode=post_details'
	);
}

if (!$mode)
{
	if ($post_id)
	{
		$mode = 'post_details';
	}
	elseif ($topic_id)
	{
		$mode = 'topic_view';
	}
	elseif ($forum_id && $forum_data[$forum_id]['forum_type'] == FORUM_POST)
	{
		$mode = 'forum_view';
	}
	else
	{
		$mode = 'front';
	}
}

switch ($mode)
{
	case 'select_topic':
		if ($url_extra)
		{
			$tabs[] = array(
				'mode'	=>	'merge',
				'title'	=>	$user->lang['MERGE_TOPIC'],
				'url'	=>	$mcp_url . '&amp;mode=merge' . $url_extra
			);
		}
	break;

	case 'merge':
	case 'split':
		$tabs[] = array(
			'mode'	=>	$mode,
			'title'	=>	$user->lang[strtoupper($mode) . '_TOPIC'],
			'url'	=>	$mcp_url . '&amp;mode=' . $mode . $url_extra
		);
	break;
}

foreach ($tabs as $tab)
{
	$template->assign_block_vars('tab', array(
		'S_IS_SELECTED'	=>	($tab['mode'] == $mode) ? TRUE : FALSE,
		'NAME'			=>	$tab['title'],
		'U_LINK'		=>	$tab['url'])
	);
}

//
// Do major work ...
//
// Current modes:
// - make_*				Change topic type
// - resync				Resyncs topics
// - delete_posts		Delete posts, displays confirmation if unconfirmed
// - delete_topic		Delete topics, displays confirmation
// - select_topic		Forward the user to forum view to select a destination topic for the merge
// - merge				Topic view, only displays the Merge button
// - split				Topic view, only displays the split buttons
// - delete				Topic view, only displays the Delete button
// - topic_view			Topic view, similar to viewtopic.php
// - forum_view			Forum view, similar to viewforum.php
// - move				Move selected topic(s), displays the forums list for confirmation. Used for quickmod as well
// - lock, unlock		Lock or unlock topic(s). No confirmation. Used for quickmod.
// - merge_posts		Actually merge posts to selected topic. Untested yet.
// - ip					Displays poster's ip and other ips the user has posted from. (imported straight from 2.0.x)
// - split_all			Actually split selected topic
// - split_beyond		Actually split selected topic
// - mod_queue			Displays a list or unapproved posts and/or topics. I haven't designed the interface yet but it will have to be able to filter/order them by type (posts/topics), by timestamp or by forum.
//
// TODO:
// - post_details		Displays post details. Has quick links to (un)approve post.
// - post_reports		Displays a list of reported posts. No interface yet, must be able to order them by priority(?), type, timestamp or forum. Action: view all (default), read, delete.
// - notes				Displays moderators notes for current forum or for all forums the user is a moderator of. Actions: view all (default), read, add, delete, edit(?).
// - a hell lot of other things
//

switch ($mode)
{
	case 'forum_info':
		if (!$auth->acl_get('m_info', $forum_id))
		{
			trigger_error('NOT_MODERATOR');
		}

		if ($confirm)
		{
			$sql_ary = array(
				'forum_name'	=>	(string) (!empty($_POST['forum_name'])) ? $_POST['forum_name'] : $forum_info['forum_name'],
				'forum_desc'	=>	(string) $_POST['forum_desc'],
				'forum_style'	=>	(int) $_POST['forum_style'],
				'forum_status'	=>	(int) $_POST['forum_status']
			);

			$sql = 'UPDATE ' . FORUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE forum_id = ' . $forum_id;
			$db->sql_query($sql);

			$return_mcp = '<br /><br />' . sprintf($user->lang['RETURN_MCP'], '<a href="mcp.' . $phpEx . $SID . '">', '</a>');
			$return_forum = '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], "<a href=\"viewforum.$phpEx$SID&amp;f=$forum_id\">", '</a>');
			trigger_error($user->lang['FORUM_UPDATED'] . $return_forum . $return_mcp);
		}

		mcp_header('mcp_foruminfo.html', 'm_info', TRUE);

		$result = $db->sql_query('SELECT style_id, style_name FROM ' . STYLES_TABLE . ' ORDER BY style_name');

		$style_list = '<option value="0"' . (($forum_info['forum_style'] > 0) ? '>' : ' selected="selected">') . $user->lang['DEFAULT_STYLE'] . "</option>\n";

		while ($row = $db->sql_fetchrow($result))
		{
			$style_list .= '<option value="' . $row['style_id'] . '"' . (($forum_info['forum_style'] > 0 && $row['style_id'] == $forum_info['forum_style']) ? ' selected="selected">' : '>') . $row['style_name'] . "</option>\n";
		}

		$status_list = '<option value="' . ITEM_LOCKED . '"' . (($forum_info['forum_status'] == ITEM_LOCKED) ? ' selected="selected"' : '') . '>' . $user->lang['LOCKED'] . "</option>\n";
		$status_list .= '<option value="' . ITEM_UNLOCKED . '"' . (($forum_info['forum_status'] == ITEM_UNLOCKED) ? ' selected="selected"' : '') . '>' . $user->lang['UNLOCKED'] . "</option>\n";

		$template->assign_vars(array(
			'S_MCP_ACTION'	=>	$mcp_url . '&amp;mode=forum_info',
			'S_FORUM_ID'	=>	$forum_id,

			'FORUM_NAME'	=>	$forum_info['forum_name'],
			'FORUM_DESC'	=>	$forum_info['forum_desc'],
			'STYLE_LIST'	=>	$style_list,
			'STATUS_LIST'	=>	$status_list
		));
	break;

	case 'fork':
		$return_mcp = '<br /><br />' . sprintf($user->lang['RETURN_MCP'], '<a href="' . $mcp_url . '&amp;mode=forum_view&amp;start="' . $start . '">', '</a>');
		$return_forum = '<br /><br />' . sprintf($user->lang['RETURN_NEW_FORUM'], '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $to_forum_id . '">', '</a>');

		if (!count($topic_id_list))
		{
			trigger_error($user->lang['NO_TOPIC_SELECTED'] . $return_mcp);
		}
		if ($to_forum_id < 1)
		{
			$confirm = FALSE;
		}

		if (!$confirm)
		{
			$s_hidden_fields = '';
			foreach ($topic_id_list as $topic_id)
			{
				$s_hidden_fields .= '<input type="hidden" name="topic_id_list[]" value="' . $topic_id . '">';
			}

			$template->assign_vars(array(
				'S_MCP_ACTION'		=>	"mcp.$phpEx$SID&amp;mode=fork&amp;start=$start",
				'S_HIDDEN_FIELDS'	=>	$s_hidden_fields,
				'S_FORUM_SELECT'	=>	make_forum_select(),

				'L_MODE_TITLE'		=>	$user->lang['FORK'],
				'L_MODE_EXPLAIN'	=>	$user->lang['FORK_EXPLAIN']
			));

			mcp_header('mcp_move.html');
		}
		else
		{
			if ($forum_data[$to_forum_id]['forum_type'] == FORUM_CAT)
			{
				trigger_error($user->lang['FORUM_NOT_POSTABLE'] . $return_mcp);
			}

			$total_posts = 0;
			$new_topic_id_list = $post_rows = array();
			foreach ($topic_data as $topic_id => $topic_row)
			{
				$sql_ary = array(
					'forum_id'					=>	(int) $to_forum_id,
					'icon_id'					=>	(int) $topic_row['icon_id'],
					'topic_approved'			=>	1,
					'topic_title'				=>	(string) $topic_row['topic_title'],
					'topic_poster'				=>	(int) $topic_row['topic_poster'],
					'topic_time'				=>	(int) $topic_row['topic_time'],
					'topic_replies'				=>	(int) $topic_row['topic_replies_real'],
					'topic_replies_real'		=>	(int) $topic_row['topic_replies_real'],
					'topic_status'				=>	(int) $topic_row['topic_status'],
					'topic_type'				=>	(int) $topic_row['topic_type'],
					'topic_first_poster_name'	=>	(string) $topic_row['topic_first_poster_name'],
					'topic_last_poster_id'		=>	(int) $topic_row['topic_last_poster_id'],
					'topic_last_poster_name'	=>	(string) $topic_row['topic_last_poster_name'],
					'topic_last_post_time'		=>	(int) $topic_row['topic_last_post_time'],
					'poll_title'				=>	(string) $topic_row['poll_title'],
					'poll_start'				=>	(int) $topic_row['poll_start'],
					'poll_length'				=>	(int) $topic_row['poll_length']
				);

				$db->sql_query('INSERT INTO ' . TOPICS_TABLE . $db->sql_build_array('INSERT', $sql_ary));
				$new_topic_id = $db->sql_nextid();
				$new_topic_id_list[$new_topic_id] = $topic_id;

				if ($topic_row['poll_start'])
				{
					$poll_rows = array();

					$result = $db->sql_query('SELECT * FROM ' . POLL_OPTIONS_TABLE . ' WHERE topic_id = ' . $topic_id);
					while ($row = $db->sql_fetchrow($result))
					{
						$sql = 'INSERT INTO ' . POLL_OPTIONS_TABLE . ' (poll_option_id, topic_id, poll_option_text, poll_option_total)
							VALUES (' . $row['poll_option_id'] . ', ' . $new_topic_id . ", '" . $db->sql_escape($row['poll_option_text']) . "', 0)";

						$db->sql_query($sql);
					}
				}

				$sql = 'SELECT *
					FROM ' . POSTS_TABLE . "
					WHERE topic_id = $topic_id
					ORDER BY post_id ASC";
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$post_rows[] = $row;
				}
				$db->sql_freeresult();

				if (!count($post_rows))
				{
					continue;
				}

				$total_posts += count($post_rows);
				foreach ($post_rows as $row)
				{
					$sql_ary = array(
						'topic_id'			=>	(int) $new_topic_id,
						'forum_id'			=>	(int) $to_forum_id,
						'poster_id'			=>	(int) $row['poster_id'],
						'icon_id'			=>	(int) $row['icon_id'],
						'poster_ip'			=>	(string) $row['poster_ip'],
						'post_time'			=>	(int) $row['post_time'],
						'post_approved'		=>	1,
						'enable_bbcode'		=>	(int) $row['enable_bbcode'],
						'enable_html'		=>	(int) $row['enable_html'],
						'enable_smilies'	=>	(int) $row['enable_smilies'],
						'enable_magic_url'	=>	(int) $row['enable_magic_url'],
						'enable_sig'		=>	(int) $row['enable_sig'],
						'post_username'		=>	(string) $row['post_username'],
						'post_subject'		=>	(string) $row['post_subject'],
						'post_text'			=>	(string) $row['post_text'],
						'post_checksum'		=>	(string) $row['post_checksum'],
						'post_encoding'		=>	(string) $row['post_encoding'],
						'bbcode_bitfield'	=>	(int) $row['bbcode_bitfield'],
						'bbcode_uid'		=>	(string) $row['bbcode_uid']
					);

					$db->sql_query('INSERT INTO ' . POSTS_TABLE . $db->sql_build_array('INSERT', $sql_ary));
				}
			}

			// Sync new topics, parent forums and board stats
			sync('topic', 'topic_id', $topic_id_list, TRUE);
			sync('forum', 'forum_id', $to_forum_id, TRUE);
			set_config('num_topics', $config['num_topics'] + count($topic_id_list));
			set_config('num_posts', $config['num_posts'] + $total_posts);

			foreach ($new_topic_id_list as $new_topic_id => $topic_id)
			{
				add_log('mod', $to_forum_id, $new_topic_id, 'logm_fork', $forum_data[$topic_data[$topic_id]['forum_id']]['forum_name']);
			}

			$msg = (count($topic_id_list) == 1) ? $user->lang['TOPIC_FORKED_SUCCESS'] : $user->lang['TOPICS_FORKED_SUCCESS'];
			trigger_error($msg . $return_forum . $return_mcp);
		}
	break;

	case 'move':
		$return_forum = '<br /><br />' . sprintf($user->lang['RETURN_NEW_FORUM'], '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $to_forum_id . '">', '</a>');
		$return_mcp = '<br /><br />' . sprintf($user->lang['RETURN_MCP'], '<a href="' . $mcp_url . '&amp;mode=forum_view&amp;start=' . $start . '">', '</a>');

		if (!count($topic_id_list))
		{
			trigger_error($user->lang['NO_TOPIC_SELECTED'] . $return_mcp);
		}
		if ($to_forum_id < 1 || $to_forum_id == $forum_id)
		{
			$confirm = FALSE;
		}
		foreach ($topic_data as $row)
		{
			if ($row['forum_id'] == 0)
			{
				trigger_error($user->lang['CANNOT_MOVE_GLOBALS'] . $return_forum . $return_mcp);
			}
		}

		if (!$confirm)
		{
			$s_hidden_fields = '';
			foreach ($topic_id_list as $topic_id)
			{
				$s_hidden_fields .= '<input type="hidden" name="topic_id_list[]" value="' . $topic_id . '">';
			}

			$template->assign_vars(array(
				'S_MCP_ACTION'		=>	"mcp.$phpEx$SID&amp;mode=move&amp;start=$start",
				'S_HIDDEN_FIELDS'	=>	$s_hidden_fields,
				'S_FORUM_SELECT'	=>	make_forum_select(),
				'S_LEAVE_SHADOW'	=>	TRUE,

				'L_MODE_TITLE'		=>	$user->lang['MOVE'],
				'L_MODE_EXPLAIN'	=>	''
			));

			mcp_header('mcp_move.html');
		}
		else
		{
			if ($forum_data[$to_forum_id]['forum_type'] == FORUM_CAT)
			{
				trigger_error($user->lang['FORUM_NOT_POSTABLE'] . $return_mcp);
			}

			move_topics($topic_id_list, $to_forum_id);

			if (!empty($_POST['move_leave_shadow']))
			{
				$shadow = $topic_info;
				$shadow['topic_status'] = ITEM_MOVED;
				$shadow['topic_moved_id'] = $topic_info['topic_id'];
				unset($shadow['topic_id']);

				$db->sql_query('INSERT INTO ' . TOPICS_TABLE . ' ' . $db->sql_build_array('INSERT', $shadow));
			}

			$msg = (count($topic_id_list) == 1) ? $user->lang['TOPIC_MOVED_SUCCESS'] : $user->lang['TOPICS_MOVED_SUCCESS'];

			$forum_ids = array($to_forum_id);
			foreach ($topic_data as $topic_id => $row)
			{
				$forum_ids[] = $row['forum_id'];
				add_log('mod', $to_forum_id, $topic_id, 'logm_move', $forum_data[$row['forum_id']]['forum_name']);
			}

			// Sync forums
			sync('forum', 'forum_id', $forum_ids);

			trigger_error($msg . $return_forum . $return_mcp);
		}
	break;

	case 'make_global':
	case 'make_announce':
	case 'make_sticky':
	case 'make_normal':
		unset($new_forum_id);

		switch ($mode)
		{
			case 'make_global':
				$set_sql = 'topic_type = ' . POST_GLOBAL;
				break;

			case 'make_announce':
				$set_sql = 'topic_type = ' . POST_ANNOUNCE;
				break;

			case 'make_sticky':
				$set_sql = 'topic_type = ' . POST_STICKY;
				break;

			case 'make_normal':
				$set_sql = 'topic_type = ' . POST_NORMAL;
				break;
		}
		if ($topic_info['forum_id'] == 0 && $mode != 'make_global')
		{
			if (!$to_forum_id)
			{
				$template->assign_vars(array(
					'L_MODE_TITLE'		=>	$user->lang['MOVE'],
					'L_MODE_EXPLAIN'	=>	$user->lang['UNGLOBALISE_EXPLAIN'],

					'S_MCP_ACTION'		=>	"mcp.$phpEx$SID&amp;mode=$mode&amp;t=$topic_id",
					'S_FORUM_SELECT'	=>	make_forum_select(),
					'S_HIDDEN_FIELDS'	=>	''
				));

				mcp_header('mcp_move.html');
				page_footer();
			}
			else
			{
				$new_forum_id = $to_forum_id;
			}
		}
		elseif ($topic_info['forum_id'] > 0 && $mode == 'make_global')
		{
			$new_forum_id = 0;
		}

		$sql = 'UPDATE ' . TOPICS_TABLE . "
			SET $set_sql" . ((isset($new_forum_id)) ? ", forum_id = $new_forum_id" : '') . "
			WHERE topic_id = $topic_id";
		$db->sql_query($sql);

		if (isset($new_forum_id))
		{
			$sql = 'UPDATE ' . POSTS_TABLE . "
				SET forum_id = $new_forum_id
				WHERE topic_id = $topic_id";
			$db->sql_query($sql);

			if ($new_forum_id > 0)
			{
				sync('forum', 'forum_id', $new_forum_id, TRUE);
			}
		}

		add_log('mod', $forum_id, $topic_id, 'logm_' . $mode);

		meta_refresh(3, "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;start=$start");

		$message = $user->lang['TOPIC_TYPE_CHANGED'] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;start=$start\">", '</a>') . '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], "<a href=\"viewforum.$phpEx$SID&amp;f=$forum_id\">", '</a>');
		trigger_error($message);
		break;

	case 'disapprove':
		// NOTE: what happens if the user disapproves the first post of the topic? Answer: the topic is deleted
		$redirect_page = "mcp.$phpEx$SID&amp;f=$forum_id";
		$l_redirect = sprintf($user->lang['RETURN_MCP'], "<a href=\"mcp.$phpEx$SID&amp;f=$forum_id\">", '</a>');

		if (!count($post_id_list))
		{
			trigger_error($user->lang['NO_POST_SELECTED'] . '<br /><br />' . $l_redirect);
		}

		if ($confirm)
		{
			$topic_ids = $post_ids = array();
			foreach ($post_id_list as $p_id)
			{
				if ($topic_data[$post_data[$p_id]['topic_id']]['topic_first_post_id'] == $p_id)
				{
					$topic_ids[] = $post_data[$p_id]['topic_id'];
				}
				else
				{
					$post_ids[] = $p_id;
				}
			}

			foreach ($post_id_list as $p_id)
			{
				if (!in_array($topic_ids, $post_data[$p_id]['topic_id']))
				{
					$post_ids[] = $p_id;
				}
			}

			if (count($topic_ids))
			{
				delete_topics('topic_id', $topic_ids);
			}

			if (count($post_ids))
			{
				delete_posts('post_id', $post_ids);
			}

			// TODO: warn the user when post is disapproved

			meta_refresh(3, $redirect_page);

			$msg = (count($post_id_list) == 1) ? $user->lang['POST_REMOVED'] : $user->lang['POSTS_REMOVED'];
			trigger_error($msg . '<br /><br />' . $l_redirect);
		}

		// Not confirmed, show confirmation message
		$hidden_fields = '<input type="hidden" name="mode" value="disapprove" />';
		foreach ($post_id_list as $p_id)
		{
			$hidden_fields .= '<input type="hidden" name="post_id_list[]" value="' . $p_id . '" />';
		}

		// Set template files
		mcp_header('confirm_body.html');

		$template->assign_vars(array(
			'MESSAGE_TITLE'		=> $user->lang['CONFIRM'],
			'MESSAGE_TEXT'		=> (count($post_id_list) == 1) ? $user->lang['CONFIRM_DELETE_POST'] : $user->lang['CONFIRM_DELETE_POSTS'],

			'S_CONFIRM_ACTION'	=> "mcp.$phpEx$SID&amp;mode=disapprove",
			'S_HIDDEN_FIELDS'	=> $hidden_fields)
		);
		break;

	case 'approve':
	case 'unapprove':
		$user_posts = $resync_count = array();
		$value = ($mode == 'approve') ? 1 : 0;

		if (count($post_id_list))
		{
			$sql = 'UPDATE ' . POSTS_TABLE . "
				SET post_approved = $value
				WHERE post_id IN (" . implode(', ', $post_id_list) . ')';
			$db->sql_query($sql);

			if (count($post_id_list) == 1)
			{
				$lang_str = ($mode == 'approve') ? 'POST_APPROVED' : 'POST_UNAPPROVED';
			}
			else
			{
				$lang_str = ($mode == 'approve') ? 'POSTS_APPROVED' : 'POSTS_UNAPPROVED';
			}

			$redirect_page = "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;start=$start";
			$l_redirect = sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $redirect_page. '">', '</a>');

			foreach ($post_id_list as $post_id)
			{
				if ($post_id == $post_data[$post_id]['topic_first_post_id'])
				{
					$logm_mode = ($mode == 'approve') ? 'logm_approve_topic' : 'logm_unapprove_topic';
				}
				else
				{
					$logm_mode = ($mode == 'approve') ? 'logm_approve_post' : 'logm_unapprove_post';
				}

				add_log('mod', $forum_id, $post_data[$post_id]['topic_id'], $logm_mode, $post_id);

				//NOTE: hey, who removed the enable_post_count field?! lol ^ ^
				$forum_data[$post_data[$post_id]['forum_id']]['enable_post_count'] = 1;
				if ($forum_data[$post_data[$post_id]['forum_id']]['enable_post_count'])
				{
					if (isset($user_posts[$post_data[$post_id]['poster_id']]))
					{
						++$user_posts[$post_data[$post_id]['poster_id']];
					}
					else
					{
						$user_posts[$post_data[$post_id]['poster_id']] = 1;
					}
				}
			}
		}
		elseif (count($topic_id_list))
		{
			// TODO: 20030325 - I'm not sure we will ever use this mode, users won't approve whole topics at once, will they?

			$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_approved = $value
				WHERE topic_id IN (" . implode(', ', $topic_id_list) . ')';
			$db->sql_query($sql);

			if (count($topic_id_list) == 1)
			{
				$lang_str = ($mode == 'approve') ? 'TOPIC_APPROVED' : 'TOPIC_UNAPPROVED';
			}
			else
			{
				$lang_str = ($mode == 'approve') ? 'TOPICS_APPROVED' : 'TOPICS_UNAPPROVED';
			}

			$redirect_page = "viewforum.$phpEx$SID&amp;f=$forum_id&amp;start=$start";
			$l_redirect = sprintf($user->lang['RETURN_FORUM'], '<a href="' . $redirect_page. '">', '</a>');

			$logm_mode = ($mode == 'approve') ? 'logm_approve_topic' : 'logm_unapprove_topic';

			foreach ($topic_id_list as $topic_id)
			{
				add_log('mod', $forum_id, $topic_id, $logm_mode);
			}
		}
		else
		{
			trigger_error($user->lang['NO_POST_SELECTED']);
		}

		// Resync last post infos, replies count et caetera
		sync('topic', 'topic_id', $topic_id_list);
		sync('topic_attachment', 'topic_id', $topic_id_list);

		foreach ($user_posts as $user_id => $post_count)
		{
			if (isset($resync_count[$post_count]))
			{
				$resync_count[$post_count][] = $user_id;
			}
			else
			{
				$resync_count[$post_count] = array($user_id);
			}
		}

		foreach ($resync_count as $post_count => $user_list)
		{
			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_posts = user_posts + $post_count
				WHERE user_id IN (" . implode(', ', $user_list) . ')';
			$db->sql_query($sql);
		}

		meta_refresh(3, $redirect_page);

		$return_mcp = '<br /><br />' . sprintf($user->lang['RETURN_MCP'], '<a href="mcp.' . $phpEx . $SID . '&amp;mode=mod_queue">', '</a>');
		trigger_error($user->lang[$lang_str] . '<br /><br />' . $l_redirect . $return_mcp);
	break;

	case 'mod_queue':
		$forum_nav = ($forum_id) ? TRUE : FALSE;
		mcp_header('mcp_queue.html', 'm_approve', $forum_nav);
		mcp_sorting('unapproved', &$sort_days, &$sort_key, &$sort_dir, &$sort_by_sql, &$sort_order_sql, &$total, $forum_id);

		$sql = 'SELECT p.post_id, p.post_subject, p.post_time, p.poster_id, p.post_username, u.username, t.topic_id, t.topic_title, t.topic_first_post_id, f.forum_id, f.forum_name
			FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f, ' . USERS_TABLE . ' u
			WHERE p.forum_id = f.forum_id
				AND p.topic_id = t.topic_id
				AND p.poster_id = u.user_id
				AND p.post_approved = 0
			' . (($forum_id > 0) ? " AND p.forum_id = $forum_id" : '') . '
			ORDER BY ' . $sort_order_sql;

		$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);

		$rowset = array(
			'topic'	=> array(),
			'post'	=> array()
		);
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['post_id'] == $row['topic_first_post_id'])
			{
				$rowset['topic'][] = $row;
			}
			else
			{
				$rowset['post'][] = $row;
			}
		}

		if ($total == -1)
		{
			$sql = 'SELECT COUNT(post_id) AS total_posts
				FROM ' . POSTS_TABLE . '
				WHERE post_approved = 0
				' . (($forum_id > 0) ? " AND forum_id = $forum_id" : '');
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);

			$total = $row['total_posts'];
		}

		$template->assign_vars(array(
			'S_MCP_ACTION'				=>	$mcp_url . '&amp;mode=mod_queue',
			'S_HAS_UNAPPROVED_POSTS'	=>	count($rowset['post']),
			'S_HAS_UNAPPROVED_TOPICS'	=>	count($rowset['topic']),

			'PAGE_NUMBER'	=> on_page($total, $config['topics_per_page'], $start),
			'PAGINATION'	=> generate_pagination("mcp.$phpEx$SID&amp;f=$forum_id&amp;mode=mod_queue&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir", $row['total_posts'], $config['topics_per_page'], $start)
		));

		foreach ($rowset as $type => $rows)
		{
			$block_name = 'unapproved_' . $type . 's';

			foreach ($rows as $row)
			{
				if ($row['poster_id'] == ANONYMOUS)
				{
					$author = ($row['post_username']) ? $row['post_username'] : $user->lang['GUEST'];
				}
				else
				{
					$author = '<a href="memberlist.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['poster_id'] . '">' . $row['username'] . '</a>';
				}

				$template->assign_block_vars($block_name, array(
					'U_POST_DETAILS'	=>	$mcp_url . '&amp;mode=post_details',
					'FORUM'					=>	'<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] . '">' . $row['forum_name'] . '</a>',
					'TOPIC'						=>	'<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . '">' . $row['topic_title'] . '</a>',
					'AUTHOR'					=>	$author,
					'SUBJECT'					=>	'<a href="mcp.' . $phpEx . $SID . '&amp;p=' . $row['post_id'] . '&amp;mode=post_details">' . (($row['post_subject']) ? $row['post_subject'] : $user->lang['NO_SUBJECT']) . '</a>',
					'POST_TIME'				=>	$user->format_date($row['post_time']),
					'S_CHECKBOX'			=>	'<input type="checkbox" name="post_id_list[]" value="' . $row['post_id'] . '">'
				));				
			}
		}
		unset($rowset);
	break;

	case 'resync':
		$redirect_page = "mcp.$phpEx$SID&amp;f=$forum_id";
		$l_redirect = sprintf($user->lang['RETURN_MCP'], '<a href="mcp.' . $phpEx . $SID . '&amp;f=' . $forum_id . '">', '</a>');

		if (!count($topic_id_list))
		{
			trigger_error($user->lang['NO_TOPIC_SELECTED'] . '<br /><br />' . $l_redirect);
		}

		sync('topic', 'topic_id', $topic_id_list);
		sync('reported', 'topic_id', $topic_id_list);
		sync('topic_attachment', 'topic_id', $topic_id_list);

		meta_refresh(3, $redirect_page);

		$msg = (count($topic_id_list) == 1) ? $user->lang['TOPIC_RESYNCHRONISED'] : $user->lang['TOPICS_RESYNCHRONISED'];
		trigger_error($msg . '<br /><br />' . $l_redirect);
	break;

	case 'delete_moved':
		if ($topic_id)
		{
			$sql = 'DELETE FROM ' . TOPICS_TABLE . '
				WHERE topic_moved_id = ' . $topic_d;
		}
		elseif ($forum_id)
		{
			$sql = 'DELETE FROM ' . TOPICS_TABLE . '
				WHERE topic_moved_id > 0
					AND forum_id = ' . $forum_id;
		}

		$db->sql_query($sql);

		$return = ($quickmod) ? sprintf($user->lang['RETURN_TOPIC'], '<a href="viewtopic.' . $phpEx . $SID . '&amp;t=' . $topic_id . '">', '</a>') : sprintf($user->lang['RETURN_MCP'], '<a href="' . $mcp_url . '">', '</a>');
		trigger_error($user->lang['SHADOWS_REMOVED'] . '<br /><br />' . $return);
	break;

	case 'delete_posts':
	// NOTE: what happens if the user deletes the first post of the topic? The topic is resync'ed normally and topic time/topic author are updated by the new first post
		$redirect_page = "mcp.$phpEx$SID&amp;f=$forum_id";
		$l_redirect = sprintf($user->lang['RETURN_MCP'], '<a href="mcp.' . $phpEx . $SID . '&amp;f=' . $forum_id . '">', '</a>');

		if (!count($post_id_list))
		{
			trigger_error($user->lang['NO_POST_SELECTED'] . '<br /><br />' . $l_redirect);
		}

		if ($confirm)
		{
			delete_posts('post_id', $post_id_list);

			$log_mode = (count($post_id_list) == 1) ? 'logm_delete_post' : 'logm_delete_posts';
			add_log('mod', $topic_data[$topic_id]['forum_id'], $topic_id, $log_mode, implode(', ', $post_id_list));

			meta_refresh(3, $redirect_page);

			$msg = (count($post_id_list) == 1) ? $user->lang['POST_REMOVED'] : $user->lang['POSTS_REMOVED'];
			trigger_error($msg . '<br /><br />' . $l_redirect);
		}

		// Not confirmed, show confirmation message
		$hidden_fields = '<input type="hidden" name="mode" value="delete_posts" />';
		foreach ($post_id_list as $p_id)
		{
			$hidden_fields .= '<input type="hidden" name="post_id_list[]" value="' . $p_id . '" />';
		}

		// Set template files
		mcp_header('confirm_body.html');

		$template->assign_vars(array(
			'MESSAGE_TITLE' => $user->lang['CONFIRM'],
			'MESSAGE_TEXT' => (count($post_id_list) == 1) ? $user->lang['CONFIRM_DELETE'] : $user->lang['CONFIRM_DELETE_POSTS'],

			'S_CONFIRM_ACTION' => "mcp.$phpEx$SID&amp;mode=delete_posts",
			'S_HIDDEN_FIELDS' => $hidden_fields
		));
	break;

	case 'delete_topic':
		if ($quickmod)
		{
			$redirect_page = "viewforum.$phpEx$SID&amp;f=$forum_id&amp;start=$start";
			$l_redirect = sprintf($user->lang['RETURN_FORUM'], '<a href="' . $redirect_page. '">', '</a>');
		}
		else
		{
			$redirect_page = "mcp.$phpEx$SID&amp;f=$forum_id&amp;start=$start";
			$l_redirect = sprintf($user->lang['RETURN_MCP'], '<a href="' . $redirect_page. '">', '</a>');
		}

		if (!count($topic_id_list))
		{
			trigger_error($user->lang['NO_TOPIC_SELECTED'] . '<br /><br />' . $l_redirect);
		}

		if ($confirm)
		{
			delete_topics('topic_id', $topic_id_list);

			foreach ($topic_id_list as $topic_id)
			{
				add_log('mod', $topic_data[$topic_id]['forum_id'], $topic_id, 'logm_delete_topic', $topic_data[$topic_id]['topic_title']);
			}

			meta_refresh(3, $redirect_page);
			trigger_error($user->lang['TOPICS_REMOVED'] . '<br /><br />' . $l_redirect);
		}

		// Not confirmed, show confirmation message
		$hidden_fields = '<input type="hidden" name="mode" value="delete_topic" />';
		foreach ($topic_id_list as $t_id)
		{
			$hidden_fields .= '<input type="hidden" name="topic_id_list[]" value="' . $t_id . '" />';
		}

		// Set template files
		mcp_header('confirm_body.html');

		$template->assign_vars(array(
			'MESSAGE_TITLE' => $user->lang['CONFIRM'],
			'MESSAGE_TEXT' => (count($topic_id_list) == 1) ? $user->lang['CONFIRM_DELETE_TOPIC'] : $user->lang['CONFIRM_DELETE_TOPICS'],

			'S_CONFIRM_ACTION' => "mcp.$phpEx$SID&amp;mode=delete_topic" . (($quickmod) ? '&amp;quickmod=1' : ''),
			'S_HIDDEN_FIELDS' => $hidden_fields
		));
	break;

	case 'merge':
	case 'split':
	case 'delete':
	case 'topic_view':
		mcp_header('mcp_topic.html', array('m_merge', 'm_split', 'm_delete', 'm_approve'), TRUE);

		mcp_sorting('viewtopic', &$sort_days, &$sort_key, &$sort_dir, &$sort_by_sql, &$sort_order_sql, &$total, $forum_id, $topic_id);
		if ($total == -1)
		{
			$total = ($auth->acl_get('m_approve')) ? $topic_info['topic_replies_real'] + 1 : $topic_info['topic_replies'] + 1;
		}

		$posts_per_page = (isset($_REQUEST['posts_per_page'])) ? intval($_REQUEST['posts_per_page']) : $config['posts_per_page'];

		$sql = 'SELECT u.username, p.*
			FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
			WHERE p.topic_id = $topic_id
				AND p.poster_id = u.user_id
			ORDER BY $sort_order_sql";
		$result = $db->sql_query_limit($sql, $posts_per_page, $start);

		$i = 0;
		$has_unapproved_posts = FALSE;
		while ($row = $db->sql_fetchrow($result))
		{
			$poster = (!empty($row['username'])) ? $row['username'] : ((!$row['post_username']) ? $user->lang['GUEST'] : $row['post_username']);

			$message = $row['post_text'];
			$post_subject = ($row['post_subject'] != '') ? $row['post_subject'] : $topic_data['topic_title'];

			// If the board has HTML off but the post has HTML
			// on then we process it, else leave it alone
			if (!$config['allow_html'] && $row['enable_html'])
			{
				$message = preg_replace('#(<)([\/]?.*?)(>)#is', '&lt;\\2&gt;', $message);
			}

			if ($row['bbcode_uid'] != '')
			{
//						$message = ($config['allow_bbcode']) ? bbencode_second_pass($message, $row['bbcode_uid']) : preg_replace('/\:[0-9a-z\:]+\]/si', ']', $message);
			}

			$message = nl2br($message);

			$checked = (in_array(intval($row['post_id']), $selected_post_ids)) ? 'checked="checked" ' : '';
			$s_checkbox = ($row['post_id'] == $topic_info['topic_first_post_id'] && $mode == 'split') ? '&nbsp;' : '<input type="checkbox" name="post_id_list[]" value="' . $row['post_id'] . '" ' . $checked . '/>';

			if (!$row['post_approved'])
			{
				$has_unapproved_posts = TRUE;
			}

			$template->assign_block_vars('postrow', array(
				'POSTER_NAME'		=>	$poster,
				'POST_DATE'			=>	$user->format_date($row['post_time']),
				'POST_SUBJECT'		=>	$post_subject,
				'MESSAGE'			=>	$message,
				'POST_ID'			=>	$row['post_id'],

				'S_CHECKBOX'		=>	$s_checkbox,
				'S_DISPLAY_MODES'	=>	($i % 10 == 0) ? TRUE : FALSE,
				'S_ROW_COUNT'		=>	$i++,
				'S_POST_UNAPPROVED'	=>	($row['post_approved']) ? FALSE : TRUE,
				
				'U_POST_DETAILS'	=>	$mcp_url . '&amp;p=' . $row['post_id'] . '&amp;mode=post_details',
				'U_APPROVE'			=>	"mcp.$phpEx$SID&amp;mode=approve&amp;p=" . $row['post_id']
			));
		}

		if ($mode == 'topic_view' || $mode == 'split')
		{
			$icons = array();
			obtain_icons($icons);

			if (sizeof($icons))
			{
				$s_topic_icons = true;

				foreach ($icons as $id => $data)
				{
					if ($data['display'])
					{
						$template->assign_block_vars('topic_icon', array(
							'ICON_ID'		=> $id,
							'ICON_IMG'		=> $config['icons_path'] . '/' . $data['img'],
							'ICON_WIDTH'	=> $data['width'],
							'ICON_HEIGHT' 	=> $data['height']
						));
					}
				}
			}
		}

		$template->assign_vars(array(
			'TOPIC_TITLE'		=>	$topic_info['topic_title'],
			'U_VIEW_TOPIC'		=>	"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id",

			'TO_TOPIC_ID'		=>	($to_topic_id) ? $to_topic_id : '',
			'TO_TOPIC_INFO'	=>	($to_topic_id) ? sprintf($user->lang['TOPIC_NUMBER_IS'], $to_topic_id, '<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;t=' . $to_topic_id . '" target="_new">' . $topic_data[$to_topic_id]['topic_title'] . '</a>') : '',

			'SPLIT_SUBJECT'		=>	$subject,
			'POSTS_PER_PAGE'	=>	$posts_per_page,

			'UNAPPROVED_IMG'	=> $user->img('icon_unapproved', 'POST_NOT_BEEN_APPROVED', FALSE, TRUE),

			'S_FORM_ACTION'		=>	"mcp.$phpEx$SID&amp;mode=$mode&amp;t=$topic_id&amp;start=$start",
			'S_FORUM_SELECT'	=>	'<select name="to_forum_id">' . make_forum_select($to_forum_id) . '</select>',
			'S_CAN_SPLIT'		=>	($auth->acl_get('m_split', $forum_id) &&($mode == 'topic_view' || $mode == 'split')) ? TRUE : FALSE,
			'S_CAN_MERGE'		=>	($auth->acl_get('m_merge', $forum_id) &&($mode == 'topic_view' || $mode == 'merge')) ? TRUE : FALSE,
			'S_CAN_DELETE'		=>	($auth->acl_get('m_delete', $forum_id) &&($mode == 'topic_view' || $mode == 'delete')) ? TRUE : FALSE,
			'S_CAN_APPROVE'		=>	($has_unapproved_posts && $auth->acl_get('m_approve', $forum_id) && $mode == 'topic_view') ? TRUE : FALSE,
			'S_SHOW_TOPIC_ICONS'=>	(!empty($s_topic_icons)) ? TRUE : FALSE,

			'PAGE_NUMBER'		=>	on_page($total, $posts_per_page, $start),
			'PAGINATION'		=>	(!$posts_per_page) ? '' : generate_pagination("mcp.$phpEx$SID&amp;t=$topic_id&amp;mode=$mode&amp;posts_per_page=$posts_per_page&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir", $total, $posts_per_page, $start)
		));
	break;

	case 'post_details':
		mcp_header('mcp_post.html', 'm_', TRUE);
		$rdns_ip_num = (!empty($_GET['rdns'])) ? $_GET['rdns'] : '';

		$template->assign_vars(array(
			'FORUM_NAME'		=>	$forum_info['forum_name'],
			'U_VIEW_FORUM'		=>	"viewforum.$phpEx$SID&amp;f=$forum_id",

			'TOPIC_TITLE'		=>	$topic_info['topic_title'],
			'U_VIEW_TOPIC'		=>	"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id",

			'S_FORM_ACTION'		=>	"mcp.$phpEx$SID"
		));

		$sql = 'SELECT u.username, p.*
			FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
			WHERE p.post_id = $post_id
				AND p.poster_id = u.user_id";
		$result = $db->sql_query($sql);

		if (!$post_info = $db->sql_fetchrow($result))
		{
			trigger_error('POST_NOT_EXIST');
		}
		else
		{
			$poster = (!empty($post_info['username'])) ? $post_info['username'] : ((!$post_info['post_username']) ? $user->lang['GUEST'] : $post_info['post_username']);

			$message = $post_info['post_text'];
			$post_subject = ($post_info['post_subject'] != '') ? $post_info['post_subject'] : $topic_data['topic_title'];

			// If the board has HTML off but the post has HTML
			// on then we process it, else leave it alone
			if (!$config['allow_html'] && $post_info['enable_html'])
			{
				$message = preg_replace('#(<)([\/]?.*?)(>)#is', '&lt;\\2&gt;', $message);
			}
			$message = nl2br($message);


			$template->assign_vars(array(
				'S_CAN_VIEWIP'	=>	$auth->acl_get('m_viewip', $forum_id),

				'POSTER_NAME'	=>	$poster,
				'POST_DATE'		=>	$user->format_date($post_info['post_time']),
				'POST_IP'		=>	$post_info['poster_ip'] . ' (' . @gethostbyaddr($post_info['poster_ip']) . ')',
				'POST_SUBJECT'	=>	$post_subject,
				'MESSAGE'		=>	$message,

				'U_LOOKUP_ALL'	=>	($rdns_ip_num == 'all') ? '' : $mcp_url . '&amp;mode=post_details&amp;rdns=all#ip',

				'SEARCH_IMG'	=>	$user->img('btn_search', 'SEARCH_USER_POSTS')
			));
		}

		// Get other users who've posted under this IP
		$sql = "SELECT u.user_id, u.username, COUNT(*) as postings
			FROM " . USERS_TABLE ." u, " . POSTS_TABLE . " p
			WHERE p.poster_id = u.user_id
				AND p.poster_ip = '" . $post_info['poster_ip'] . "'
			GROUP BY u.user_id, u.username
			ORDER BY postings DESC";
		$result = $db->sql_query($sql);

		$i = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('userrow', array(
				'S_ROW_COUNT'	=>	$i++,

				'USERNAME'		=>	($row['user_id'] == ANONYMOUS) ? $user->lang['GUEST'] : $row['username'],
				'POSTS'			=>	$row['postings'] . ' ' . (($row['postings'] == 1) ? $user->lang['POST'] : $user->lang['POSTS']),

				'U_PROFILE' => "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id'],
				'U_SEARCHPOSTS' => "search.$phpEx$SID&amp;search_author=" . urlencode($username) . "&amp;showresults=topics"
			));

			$i++;
		}
		$db->sql_freeresult($result);

		// Get other IP's this user has posted under
		$sql = 'SELECT poster_ip, COUNT(*) AS postings
			FROM ' . POSTS_TABLE . '
			WHERE poster_id = ' . $post_info['poster_id'] . '
			GROUP BY poster_ip
			ORDER BY postings DESC';
		$result = $db->sql_query($sql);

		$i = 0;
		while ($row = $db->sql_fetchrow($result))
		{
			$ip = ($rdns_ip_num == $row['poster_ip'] || $rdns_ip_num == 'all') ? @gethostbyaddr($row['poster_ip']) . ' (' . $row['poster_ip'] . ')' : $row['poster_ip'];

			$template->assign_block_vars('iprow', array(
				'S_ROW_COUNT'	=>	$i++,
				'IP'			=>	$ip,
				'POSTS'			=>	$row['postings'] . ' ' . (($row['postings'] == 1) ? $user->lang['POST'] : $user->lang['POSTS']),
				'U_LOOKUP_IP'	=>	($rdns_ip_num == $row['poster_ip'] || $rdns_ip_num == 'all') ? '' : $mcp_url . '&amp;mode=post_details&amp;rdns=' . $row['poster_ip'] . '#ip'
			));
		}
		$db->sql_freeresult($result);
	break;

	case 'lock':
	case 'unlock':
		if (count($topic_id_list) == 1)
		{
			$message = ($mode == 'lock') ? $user->lang['TOPIC_LOCKED_SUCCESS'] : $user->lang['TOPIC_UNLOCKED_SUCCESS'];
		}
		else
		{
			$message = ($mode == 'lock') ? $user->lang['TOPICS_LOCKED_SUCCESS'] : $user->lang['TOPICS_UNLOCKED_SUCCESS'];
		}

		if (isset($_GET['quickmod']))
		{
			$redirect_page = "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;start=$start";
			$l_redirect = sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $redirect_page . '">', '</a>');
		}
		else
		{
			$redirect_page = $mcp_url . '&amp;mode=forum_view&amp;start=' . $start;
			$l_redirect = sprintf($user->lang['RETURN_MCP'], '<a href="' . $redirect_page . '">', '</a>');
		}

		if (!count($topic_id_list))
		{
			trigger_error($user->lang['NO_TOPIC_SELECTED'] . '<br /><br />' . $l_redirect);
		}

		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET topic_status = ' . (($mode == 'lock') ? ITEM_LOCKED : ITEM_UNLOCKED) . '
			WHERE topic_id IN (' . implode(', ', $topic_id_list) . ')
				AND topic_moved_id = 0';
		$db->sql_query($sql);

		$message .= '<br /><br />' . $l_redirect . '<br \><br \>' . sprintf($user->lang['RETURN_FORUM'], "<a href=\"viewforum.$phpEx$SID&amp;f=$forum_id\">", '</a>');

		foreach ($topic_id_list as $topic_id)
		{
			add_log('mod', $forum_id, $topic_id, 'logm_' . $mode);
		}

		meta_refresh(3, $redirect_page);
		trigger_error($message);
	break;

	case 'merge_posts':
		if (!$to_topic_id)
		{
			redirect("mcp.$phpEx$SID&mode=select_topic&f=$forum_id" . str_replace('&amp;', '&', $url_extra));
		}
		if (!count($post_id_list))
		{
			trigger_error($user->lang['NO_POST_SELECTED'] . '<br /><br />' . sprintf($user->lang['RETURN_MCP'], '<a href="mcp.' . $phpEx . $SID . '&amp;mode=merge&amp;t=' . $topic_id . '&amp;to_topic_id=' . $to_topic_id . '">', '</a>'));
		}
		$return_url = '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], '<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $to_forum_id . '&amp;t=' . $to_topic_id . '">', '</a>');
		move_posts($post_id_list, $to_topic_id);

		add_log('mod', $to_forum_id, $to_topic_id, 'logm_merge', $topic_id);
		trigger_error($user->lang['POSTS_MERGED'] . $return_url . $return_mcp);
	break;

	case 'split_all':
	case 'split_beyond':
		$return_split = '<br /><br />' . sprintf($user->lang['RETURN_MCP'], '<a href="' . $mcp_url . '&amp;mode=split' . $url_extra . '">', '</a>');

		if (!count($post_id_list))
		{
			trigger_error($user->lang['NO_POST_SELECTED'] . $return_split);
		}
		elseif (in_array($topic_info['topic_first_post_id'], $post_id_list))
		{
			trigger_error($user->lang['CANNOT_SPLIT_FIRST_POST'] . $return_split);
		}

		if (!$subject)
		{
			trigger_error($user->lang['EMPTY_SUBJECT'] . $return_split);
		}
		if ($to_forum_id <= 0)
		{
			trigger_error($user->lang['SELECT_DESTINATION_FORUM'] . $return_split);
		}
		if ($forum_data[$to_forum_id]['forum_type'] == FORUM_CAT)
		{
			trigger_error($user->lang['FORUM_NOT_POSTABLE'] . $return_split);
		}

		if ($mode == 'split_beyond')
		{
			$sql = 'SELECT p.post_id
				FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
				WHERE p.topic_id = $topic_id
					AND p.poster_id = u.user_id
					$limit_posts_time
				ORDER BY $sort_order_sql";
			$result = $db->sql_query_limit($sql, 0, $start);

			$store = FALSE;
			$post_id_list = array();
			while ($row = $db->sql_fetchrow($result))
			{
				// Start to store post_ids as soon as we see the first post that was selected
				if ($row['post_id'] == $post_id)
				{
					$store = TRUE;
				}
				if ($store)
				{
					$post_id_list[] = $row['post_id'];
				}
			}
		}

		if (!count($post_id_list))
		{
			trigger_error($user->lang['NO_POST_SELECTED'] . $return_split);
		}

		$icon_id = (!empty($_POST['icon'])) ? intval($_POST['icon']) : 0;
		$sql = 'INSERT INTO ' . TOPICS_TABLE . " (forum_id, topic_title, icon_id, topic_approved)
			VALUES ($to_forum_id, '" . $db->sql_escape($subject) . "', $icon_id, 1)";
		$db->sql_query($sql);

		$to_topic_id = $db->sql_nextid();
		move_posts($post_id_list, $to_topic_id);

		$return_url = '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], '<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;t=' . $topic_id . '">', '</a>');
		$return_url .= '<br /><br />' . sprintf($user->lang['RETURN_NEW_TOPIC'], '<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $to_forum_id . '&amp;t=' . $to_topic_id . '">', '</a>');
		trigger_error($user->lang['TOPIC_SPLIT'] . $return_url . $return_mcp);
	break;

	case 'select_topic':
	case 'forum_view':
		mcp_header('mcp_forum.html', 'm_', TRUE);
		mcp_sorting('viewforum', &$sort_days, &$sort_key, &$sort_dir, &$sort_by_sql, &$sort_order_sql, &$total, $forum_id);
		$forum_topics = ($total == -1) ? $forum_info['forum_topics'] : $total;
		$limit_time_sql = ($sort_days) ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';

		$template->assign_vars(array(
			'FORUM_NAME' => $forum_info['forum_name'],

			'S_CAN_DELETE'	=>	$auth->acl_get('m_delete', $forum_id),
			'S_CAN_MOVE'	=>	$auth->acl_get('m_move', $forum_id),
			'S_CAN_FORK'	=>	$auth->acl_get('m_', $forum_id),
			'S_CAN_LOCK'	=>	$auth->acl_get('m_lock', $forum_id),
			'S_CAN_SYNC'	=>	$auth->acl_get('m_', $forum_id),

			'U_VIEW_FORUM'		=>	"viewforum.$phpEx$SID&amp;f=$forum_id",
			'S_MCP_ACTION'		=>	"mcp.$phpEx$SID&amp;mode=$mode&ampf=$forum_id&amp;start=$start" . (($mode == 'select_topic') ? $url_extra : ''),

			'PAGINATION' => generate_pagination("mcp.$phpEx$SID&amp;mode=$mode&amp;f=$forum_id" . (($mode == 'select_topic') ? $url_extra : ''), $forum_topics, $config['topics_per_page'], $start),
			'PAGE_NUMBER' => on_page($forum_topics, $config['topics_per_page'], $start)
		));


		// Define censored word matches
		$censors = array();
		obtain_word_list($censors);

		$topic_rows = array();

		$sql = "SELECT t.*
			FROM " . TOPICS_TABLE . " t
			WHERE t.forum_id = $forum_id 
				" . (($auth->acl_gets('m_approve', $forum_id)) ? '' : 'AND t.topic_approved = 1') . "
			AND t.topic_type = " . POST_ANNOUNCE . " 
			$limit_time_sql
		ORDER BY $sort_order_sql";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$topic_rows[] = $row;
		}
		$db->sql_freeresult($result);

		$sql = "SELECT t.*
			FROM " . TOPICS_TABLE . " t
			WHERE t.forum_id = $forum_id 
				" . (($auth->acl_gets('m_approve', $forum_id)) ? '' : 'AND t.topic_approved = 1') . "
			AND t.topic_type <> " . POST_ANNOUNCE . " 
			$limit_time_sql
		ORDER BY t.topic_type DESC, $sort_order_sql";
		$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);

		while ($row = $db->sql_fetchrow($result))
		{
			$topic_rows[] = $row;
		}
		$db->sql_freeresult($result);

		foreach ($topic_rows as $row)
		{
			$topic_title = '';

			if ($row['topic_status'] == ITEM_LOCKED)
			{
				$folder_img = $user->img('folder_locked', 'VIEW_TOPIC_LOCKED');
			}
			else
			{
				if ($row['topic_type'] == POST_ANNOUNCE)
				{
					$folder_img = $user->img('folder_announce', 'VIEW_TOPIC_ANNOUNCEMENT');
				}
				else if ($row['topic_type'] == POST_STICKY)
				{
					$folder_img = $user->img('folder_sticky', 'VIEW_TOPIC_STICKY');
				}
				else
				{
					$folder_img = $user->img('folder', 'NO_NEW_POSTS');
				}
			}

			if ($row['topic_type'] == POST_ANNOUNCE)
			{
				$topic_type = $user->lang['VIEW_TOPIC_ANNOUNCEMENT'] . ' ';
			}
			else if ($row['topic_type'] == POST_STICKY)
			{
				$topic_type = $user->lang['VIEW_TOPIC_STICKY'] . ' ';
			}
			else if ($row['topic_status'] == ITEM_MOVED)
			{
				$topic_type = $user->lang['VIEW_TOPIC_MOVED'] . ' ';
			}
			else
			{
				$topic_type = '';
			}

			if (intval($row['poll_start']))
			{
				$topic_type .= $user->lang['VIEW_TOPIC_POLL'] . ' ';
			}

			// Shouldn't moderators be allowed to read uncensored title?
			$topic_title = $row['topic_title'];
			if (count($censors['match']))
			{
				$topic_title = preg_replace($censors['match'], $censors['replace'], $topic_title);
			}

			$template->assign_block_vars('topicrow', array(
				'U_VIEW_TOPIC'		=>	$mcp_url . '&amp;t=' . $row['topic_id'] . '&amp;mode=topic_view',

				'S_SELECT_TOPIC'	=>	($mode == 'select_topic' && $row['topic_id'] != $topic_id) ? TRUE : FALSE,
				'U_SELECT_TOPIC'	=>	$mcp_url . '&amp;mode=merge&amp;to_topic_id=' . $row['topic_id'] . $url_extra,

				'TOPIC_FOLDER_IMG'	=>	$folder_img,
				'TOPIC_TYPE'		=>	$topic_type,
				'TOPIC_TITLE'		=>	$topic_title,
				'REPLIES'			=>	$row['topic_replies'],
				'LAST_POST_TIME'	=>	$user->format_date($row['topic_last_post_time']),
				'TOPIC_ID'			=>	$row['topic_id']
			));
		}
		unset($topic_rows);
	break;

	case 'viewlogs':
		mcp_header('mcp_viewlogs.html', array('m_', 'a_general'), FALSE);

		// The user used the jumpbox to get there, therefore we do not limit logs to the selected topic
		if (isset($_POST['jumpbox']) || (isset($topic_id) && $topic_info['forum_id'] != $forum_id))
		{
			$topic_id = 0;
		}

		mcp_sorting('viewlogs', &$sort_days, &$sort_key, &$sort_dir, &$sort_by_sql, &$sort_order_sql, &$total, $forum_id, $topic_id);

		$log_count = 0;
		$log = array();

		if (!$forum_id)
		{
			$forum_id = get_forum_list('m_');
			$forum_id[] = 0;
		}
		$min_time = ($sort_days) ? time() - ($sort_days * 86400) : 0;
		view_log('mod', &$log, &$log_count, $config['topics_per_page'], $start, $forum_id, $topic_id, $min_time, $sort_order_sql);

		foreach ($log as $row)
		{
			$template->assign_block_vars('log', array(
				'USERNAME'		=>	$row['username'],
				'IP'			=>	$row['ip'],
				'TIME'			=>	$user->format_date($row['time']),
				'ACTION'		=>	$row['action'],
				'U_VIEWTOPIC'	=>	$row['viewtopic'],
				'U_VIEWLOGS'	=>	$row['viewlogs']
			));
		}

		$template->assign_vars(array(
			'S_MCP_ACTION'		=>	"mcp.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;mode=viewlogs",
			'PAGINATION'		=>	generate_pagination("mcp.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;p=$post_id&amp;mode=viewlogs", $log_count, $config['topics_per_page'], $start),
			'PAGE_NUMBER'		=>	on_page($log_count, $config['topics_per_page'], $start),

			'S_TOPIC_ID'		=>	$topic_id,
			'TOPIC_NAME'		=>	($topic_id) ? $topic_info['topic_title'] : ''
		));
	break;

	case 'front':
	default:
		mcp_header('mcp_front.html', 'm_');
		$template->assign_var('S_MCP_ACTION', $mcp_url);

		// -------------
		// Latest 5 unapproved
		$forum_list = get_forum_list('m_approve');

		$template->assign_var('S_SHOW_UNAPPROVED', (!empty($forum_list)) ? TRUE : FALSE);
		if (!empty($forum_list))
		{
			$where_sql = 'IN (0,' . implode(', ', $forum_list) . ')';

			// KNOWN BUG: won't list posts from a global announcement because of the forum_id
			$sql = 'SELECT p.post_id, p.post_subject, p.post_time, p.poster_id, p.post_username, u.username, t.topic_id, t.topic_title, t.topic_first_post_id, f.forum_id, f.forum_name
				FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u
				LEFT JOIN ' . FORUMS_TABLE . " f ON f.forum_id = p.forum_id
				WHERE p.topic_id = t.topic_id
					AND p.poster_id = u.user_id
					AND p.post_approved = 0
					AND p.forum_id $where_sql
				ORDER BY p.post_time DESC";
			$result = $db->sql_query_limit($sql, 5);

			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['poster_id'] == ANONYMOUS)
				{
					$author = ($row['post_username']) ? $row['post_username'] : $user->lang['GUEST'];
				}
				else
				{
					$author = '<a href="memberlist.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['poster_id'] . '">' . $row['username'] . '</a>';
				}

				$template->assign_block_vars('unapproved', array(
					'U_POST_DETAILS'	=>	$mcp_url . '&amp;mode=post_details',
					'FORUM'				=>	(!empty($row['forum_id'])) ? '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] . '">' . $row['forum_name'] . '</a>' : $user->lang['POST_GLOBAL'],
					'TOPIC'				=>	'<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . '">' . $row['topic_title'] . '</a>',
					'AUTHOR'			=>	$author,
					'SUBJECT'			=>	'<a href="mcp.' . $phpEx . $SID . '&amp;p=' . $row['post_id'] . '&amp;mode=post_details">' . (($row['post_subject']) ? $row['post_subject'] : $user->lang['NO_SUBJECT']) . '</a>',
					'POST_TIME'			=>	$user->format_date($row['post_time'])
				));				
			}

			$result = $db->sql_query('SELECT COUNT(post_id) AS total FROM ' . POSTS_TABLE . ' WHERE post_approved = 0 AND forum_id ' . $where_sql);
			$row = $db->sql_fetchrow($result);

			if ($row['total'] == 0)
			{
				$template->assign_vars(array(
					'L_UNAPPROVED_TOTAL'		=>	$user->lang['UNAPPROVED_POSTS_ZERO_TOTAL'],
					'S_HAS_UNAPPROVED_POSTS'	=>	FALSE
				));
			}
			elseif ($row['total'] == 1)
			{
				$template->assign_vars(array(
					'L_UNAPPROVED_TOTAL'		=>	$user->lang['UNAPPROVED_POST_TOTAL'],
					'S_HAS_UNAPPROVED_POSTS'	=>	TRUE
				));
			}
			else
			{
				$template->assign_vars(array(
					'L_UNAPPROVED_TOTAL'		=>	sprintf($user->lang['UNAPPROVED_POSTS_TOTAL'], $row['total']),
					'S_HAS_UNAPPROVED_POSTS'	=>	TRUE
				));
			}
		}
		// -------------

		// -------------
		// Latest 5 reported
		$forum_list = get_forum_list('m_');
		
		$template->assign_var('S_SHOW_REPORTS', (!empty($forum_list)) ? TRUE : FALSE);
		if (!empty($forum_list))
		{
			$where_sql = 'IN (0, ' . implode(', ', $forum_list) . ')';

			$sql = 'SELECT r.*, p.post_id, p.post_subject, u.username, t.topic_id, t.topic_title, f.forum_id, f.forum_name
				FROM ' . REPORTS_TABLE . ' r, ' . REASONS_TABLE . ' rr,' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . USERS_TABLE . ' u
				LEFT JOIN ' . FORUMS_TABLE . " f ON f.forum_id = p.forum_id
				WHERE r.post_id = p.post_id
					AND r.reason_id = rr.reason_id
					AND p.topic_id = t.topic_id
					AND r.user_id = u.user_id
					AND p.forum_id $where_sql
				ORDER BY p.post_time DESC";
			$result = $db->sql_query_limit($sql, 5);

			while ($row = $db->sql_fetchrow($result))
			{
				$template->assign_block_vars('report', array(
					'U_POST_DETAILS'	=>	$mcp_url . '&amp;mode=post_details',
					'FORUM'				=>	(!empty($row['forum_id'])) ? '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] . '">' . $row['forum_name'] . '</a>' : $user->lang['POST_GLOBAL'],
					'TOPIC'				=>	'<a href="viewtopic.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . '">' . $row['topic_title'] . '</a>',
					'REPORTER'			=>	($row['user_id'] == ANONYMOUS) ? $user->lang['GUEST'] : '<a href="memberlist.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['user_id'] . '">' . $row['username'] . '</a>',
					'SUBJECT'			=>	'<a href="mcp.' . $phpEx . $SID . '&amp;p=' . $row['post_id'] . '&amp;mode=post_details">' . (($row['post_subject']) ? $row['post_subject'] : $user->lang['NO_SUBJECT']) . '</a>',
					'REPORT_TIME'		=>	$user->format_date($row['report_time'])
				));				
			}

			$sql = 'SELECT COUNT(r.report_id) AS total
				FROM ' . REPORTS_TABLE . ' r, ' . POSTS_TABLE . ' p
				WHERE r.post_id = p.post_id
					AND p.forum_id ' . $where_sql;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);

			if ($row['total'] == 0)
			{
				$template->assign_vars(array(
					'L_REPORTS_TOTAL'	=>	$user->lang['REPORTS_ZERO_TOTAL'],
					'S_HAS_REPORTS'		=>	FALSE
				));
			}
			elseif ($row['total'] == 1)
			{
				$template->assign_vars(array(
					'L_REPORTS_TOTAL'	=>	$user->lang['REPORT_TOTAL'],
					'S_HAS_REPORTS'		=>	TRUE
				));
			}
			else
			{
				$template->assign_vars(array(
					'L_REPORTS_TOTAL'	=>	sprintf($user->lang['REPORTS_TOTAL'], $row['total']),
					'S_HAS_REPORTS'		=>	TRUE
				));
			}
		}
		// -------------

		// -------------
		// Latest 5 logs
		$forum_list = get_forum_list(array('m_', 'a_general'));

		if (!empty($forum_list))
		{
			// Add forum_id 0 for global announcements
			$forum_list[] = 0;

			$log_count = 0;
			$log = array();
			view_log('mod', &$log, &$log_count, 5, 0, $forum_list);

			foreach ($log as $row)
			{
				$template->assign_block_vars('log', array(
					'USERNAME'		=>	$row['username'],
					'IP'			=>	$row['ip'],
					'TIME'			=>	$user->format_date($row['time']),
					'ACTION'		=>	$row['action'],
					'U_VIEWTOPIC'	=>	$row['viewtopic'],
					'U_VIEWLOGS'	=>	$row['viewlogs']
				));
			}
		}
		$template->assign_vars(array(
			'S_SHOW_LOGS'	=>	(!empty($forum_list)) ? TRUE : FALSE,
			'S_HAS_LOGS'	=>	(!empty($log)) ? TRUE : FALSE
		));
}

page_footer();

// -----------------------
// Page specific functions
//
function mcp_header($template_name, $jumpbox_acl = FALSE, $forum_nav = FALSE)
{
	global $phpbb_root_path, $phpEx, $SID, $url_extra, $template, $auth, $user, $db, $config;
	global $forum_id, $forum_info, $mode;

	$forum_id = (!empty($forum_id)) ? $forum_id : FALSE;

	page_header(sprintf($user->lang['MCP'], '', ''));

	$template->set_filenames(array(
		'body' => $template_name)
	);

	if (preg_match('#mod_queue|post_reports|viewlogs#', $mode))
	{
		$enable_select_all = TRUE;
	}
	else
	{
		$enable_select_all = FALSE;
	}
	if ($jumpbox_acl)
	{
		mcp_jumpbox('mcp.' . $phpEx . $SID . '&amp;mode=' . $mode . $url_extra, $jumpbox_acl, $forum_id, $enable_select_all);
	}

	if ($forum_nav)
	{
		generate_forum_nav($forum_info);
	}
	$template->assign_var('S_FORUM_NAV', $forum_nav);
}

function mcp_jumpbox($action, $acl_list = 'f_list', $forum_id = false, $enable_select_all = false)
{
	global $auth, $template, $user, $db, $phpEx, $SID;

	$sql = 'SELECT forum_id, forum_name, forum_type, left_id, right_id
		FROM ' . FORUMS_TABLE . '
		ORDER BY left_id ASC';
	$result = $db->sql_query($sql, 120);

	$right = $cat_right = 0;
	$padding = $forum_list = $holding = '';
	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
		{
			// Non-postable forum with no subforums, don't display
			continue;
		}

		if (!$auth->acl_get('f_list', $row['forum_id']))
		{
			// if the user does not have permissions to list this forum skip
			continue;
		}
		if ($row['forum_type'] == FORUM_CAT || !$auth->acl_gets($acl_list, $row['forum_id']))
		{
			$row['forum_id'] = -1;
		}

		if ($row['left_id'] < $right)
		{
			$padding .= '&nbsp; &nbsp;';
		}
		else if ($row['left_id'] > $right + 1)
		{
			$padding = substr($padding, 0, -13 * ($row['left_id'] - $right + 1));
		}

		$right = $row['right_id'];

		$selected = ($row['forum_id'] == $forum_id) ? ' selected="selected"' : '';

		if ($row['right_id'] - $row['left_id'] > 1)
		{
			$cat_right = max($cat_right, $row['right_id']);
			$char = '+ ';
		}
		else
		{
			$char = '- ';
		}
		$template->assign_block_vars('options', array(
			'VALUE'		=>	$row['forum_id'],
			'SELECTED'	=>	$selected,
			'TEXT'		=>	$padding . $char . $row['forum_name'])
		);
	}
	$db->sql_freeresult($result);

	$template->assign_vars(array(
		'S_JUMPBOX_ACTION'		=>	$action,
		'S_ENABLE_SELECT_ALL'	=>	$enable_select_all,
		'S_CURRENT_FORUM'		=>	intval($forum_id)
	));

	return;
}

function short_id_list($id_list)
{
	$max_len = 0;
	$short_id_list = array();

	foreach ($id_list as $id)
	{
		$short = (string) base_convert($id, 10, 36);
		$max_len = max(strlen($short), $max_len);
		$short_id_list[] = $short;
	}

	$id_str = (string) $max_len;
	foreach ($short_id_list as $short)
	{
		$id_str .= str_pad($short, $max_len, '0', STR_PAD_LEFT);
	}

	return $id_str;
}

function mcp_sorting($mode, &$sort_days, &$sort_key, &$sort_dir, &$sort_by_sql, &$sort_order_sql, &$total, $forum_id = 0, $topic_id = 0, $where_sql = 'WHERE')
{
	global $db, $user, $auth, $template;

	$sort_days = (!empty($_REQUEST['st'])) ? max(intval($_REQUEST['st']), 0) : 0;
	$min_time = ($sort_days) ? time() - ($sort_days * 86400) : 0;

	switch ($mode)
	{
		case 'viewforum':
			$type = 'topics';
			$default_dir = 'd';
			$sql = 'SELECT COUNT(topic_id) AS total
				FROM ' . TOPICS_TABLE . "
				$where_sql forum_id = $forum_id
					AND topic_type <> " . POST_ANNOUNCE . "
					AND topic_last_post_time >= $min_time";

			if (!$auth->acl_get('m_approve', $forum_id))
			{
				$sql .= 'AND topic_approved = 1';
			}
		break;

		case 'viewtopic':
			$type = 'posts';
			$default_dir = 'a';
			$sql = 'SELECT COUNT(post_id) AS total
				FROM ' . POSTS_TABLE . "
				$where_sql topic_id = $topic_id
					AND post_time >= $min_time";

			if (!$auth->acl_get('m_approve', $forum_id))
			{
				$sql .= 'AND post_approved = 1';
			}
		break;

		case 'unapproved':
			$type = 'posts';
			$default_dir = 'd';
			$sql = 'SELECT COUNT(post_id) AS total
				FROM ' . POSTS_TABLE . "
				$where_sql forum_id IN (" . (($forum_id) ? $forum_id : implode(', ', get_forum_list('m_approve'))) . ')
					AND post_approved = 0
					AND post_time >= ' . $min_time;
		break;

		case 'reports':
			$type = 'reports';
			$default_dir = 'd';
			$sql = 'SELECT COUNT(report_id) AS total
				FROM ' . REPORTS_TABLE . "
				$where_sql forum_id IN (" . (($forum_id) ? $forum_id : implode(', ', get_forum_list('m_'))) . ')
					AND report_time >= ' . $min_time;
		break;

		case 'viewlogs':
			$type = 'logs';
			$default_dir = 'd';
			$sql = 'SELECT COUNT(log_id) AS total
				FROM ' . LOG_MOD_TABLE . "
				$where_sql forum_id IN (" . (($forum_id) ? $forum_id : implode(', ', get_forum_list('m_'))) . ')
					AND log_time >= ' . $min_time;
		break;
	}

	$sort_key = (!empty($_REQUEST['sk'])) ? htmlspecialchars($_REQUEST['sk']) : 't';
	$sort_dir = (!empty($_REQUEST['sd'])) ? htmlspecialchars($_REQUEST['sd']) : $default_dir;
	$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

	switch ($type)
	{
		case 'topics':
			$limit_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
			$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'tt' => $user->lang['TOPIC_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);

			$sort_by_sql = array('a' => 't.topic_first_poster_name', 't' => 't.topic_last_post_time', 'tt' => 't.topic_time', 'r' => (($auth->acl_get('m_approve', $forum_id)) ? 't.topic_replies_real' : 't.topic_replies'), 's' => 't.topic_title', 'v' => 't.topic_views');
			$limit_time_sql = ($min_time) ? "AND t.topic_last_post_time >= $min_time" : '';
		break;

		case 'posts':
			$limit_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
			$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);
			$sort_by_sql = array('a' => 'u.username', 't' => 'p.post_time', 's' => 'p.post_subject');
			$limit_time_sql = ($min_time) ? "AND p.post_time >= $min_time" : '';
		break;

		case 'reports':
			$limit_days = array(0 => $user->lang['ALL_REPORTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
			$sort_by_text = array('r' => $user->lang['REPORTER'], 't' => $user->lang['REPORT_TIME']);
			$sort_by_sql = array('r' => 'u.username', 't' => 'r.report_time');
			$limit_time_sql = ($min_time) ? "AND r.report_time >= $min_time" : '';
		break;

		case 'logs':
			$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
			$sort_by_text = array('u' => $user->lang['SORT_USERNAME'], 't' => $user->lang['SORT_DATE'], 'i' => $user->lang['SORT_IP'], 'o' => $user->lang['SORT_ACTION']);

			$sort_by_sql = array('u' => 'l.user_id', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation');
			$limit_time_sql = ($min_time) ? "AND l.log_time >= $min_time" : '';
		break;
	}

	$sort_order_sql = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

	$s_limit_days = $s_sort_key = $s_sort_dir = '';
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, &$s_limit_days, &$s_sort_key, &$s_sort_dir);

	$template->assign_vars(array(
		'S_SELECT_SORT_DIR'	=>	$s_sort_dir,
		'S_SELECT_SORT_KEY' =>	$s_sort_key,
		'S_SELECT_SORT_DAYS'=>	$s_limit_days
	));

	if ($sort_days && $mode != 'viewlogs')
	{
		$result = $db->sql_query($sql);
		$total = ($row = $db->sql_fetchrow($result)) ? $row['total'] : 0;
	}
	else
	{
		$total = -1;
	}
}
//
// End page specific functions
// ---------------------------
?>