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
// * Split topic:
//    * As current but need better way of listing all posts
// * Merge topics:
//    * Similar to split(?) but reverse 
// * Find duplicates:
//    * List supiciously similar posts across forum/s
// * "Ban" user/s:
//    * Limit read/post/reply/etc. permissions

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
// End session management

// temp temp temp
very_temporary_lang_strings();
// temp temp temp

//
// Obtain initial var settings
//
$forum_id = (!empty($_REQUEST['f'])) ? intval($_REQUEST['f']) : '';
$topic_id = (!empty($_REQUEST['t'])) ? intval($_REQUEST['t']) : '';
$post_id = (!empty($_REQUEST['p'])) ? intval($_REQUEST['p']) : '';
$start = (!empty($_GET['start'])) ? intval($_GET['start']) : 0;

// Continue var definitions
$forum_data = $topic_data = $post_data = array();
$topic_id_list = ($topic_id) ? array($topic_id) : array();
$post_id_list = ($post_id) ? array($post_id) : array();
$return_mcp = '<br /><br />' . sprintf($user->lang['Click_return_modcp'], '<a href="mcp.' . $phpEx . $SID . '">', '</a>');

$confirm = (!empty($_POST['confirm'])) ? TRUE : FALSE;
$mode = (!empty($_REQUEST['mode'])) ? $_REQUEST['mode'] : '';
$quickmod = (!empty($_REQUEST['quickmod'])) ? TRUE : FALSE;

$post_modes = array('move', 'delete', 'lock', 'unlock', 'merge_posts', 'delete_posts', 'split_all', 'split_beyond', 'select_topic', 'resync');
foreach ($post_modes as $post_mode)
{
	if (isset($_POST[$post_mode]))
	{
		$mode = $post_mode;
	}
}

// Check destination forum or topic if applicable
$to_forum_id = (!empty($_REQUEST['to_forum_id'])) ? intval($_REQUEST['to_forum_id']) : 0;
$to_topic_id = (!empty($_REQUEST['to_topic_id'])) ? intval($_REQUEST['to_topic_id']) : 0;

if ($to_topic_id)
{
	$result = $db->sql_query('SELECT * FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . $to_topic_id);
	$row = $db->sql_fetchrow($result);
	if (empty($row['forum_id']))
	{
		trigger_error($user->lang['Topic_not_exist'] . $return_mcp);
	}
	if (!isset($topic_data[$to_topic_id]))
	{
		$topic_data[$to_topic_id] = $row;
	}

	$to_forum_id = $row['forum_id'];
}

if ($to_forum_id && !$auth->acl_gets('f_post', 'f_reply', 'm_', 'a_', $to_forum_id))
{
	if (!$auth->acl_gets('f_list', $to_forum_id))
	{
		trigger_error($user->lang['Forum_not_exist'] . $return_mcp);
	}
	else
	{
		trigger_error('not authorised to perform this action with destination forum');
	}

	if (!isset($forum_data[$to_forum_id]))
	{
		$forum_data[$to_forum_id] = $row;
	}
}

// Cleanse inputted values then get permissions
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

$selected_post_ids = array();
if (!empty($_GET['post_id_list']))
{
	$len = $_GET['post_id_list']{0};
	for ($i = 1; $i < strlen($_GET['post_id_list']); $i += $len)
	{
		$short = substr($_GET['post_id_list'], $i, $len);
		$selected_post_ids[] = (string) base_convert($short, 36, 10);
		$post_id_list[] = base_convert($short, 36, 10);
	}
}

$topic_id_sql = implode(', ', $topic_id_list);
$post_id_sql = implode(', ', $post_id_list);

// Reset id lists then rebuild them
$forum_id_list = $topic_id_list = $post_id_list = array();

$not_moderator = FALSE;
if ($forum_id)
{
	if ($auth->acl_gets('m_', 'a_', $forum_id))
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
		if ($auth->acl_gets('m_', 'a_', $row['forum_id']))
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
		if ($auth->acl_gets('m_', 'a_', $row['forum_id']))
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

	$forum_id_list = array_unique($forum_id_list);
	$topic_id_list = array_unique($topic_id_list);
	$post_id_list = array_unique($post_id_list);


	// Set infos about current forum/topic/post
	if (count($forum_id_list) == 1)
	{
		$forum_id = $forum_id_list[0];
	}
	if (count($topic_id_list) == 1)
	{
		$topic_id = $topic_id_list[0];
	}
	if (count($post_id_list) == 1)
	{
		$post_id = $post_id_list[0];
	}

	$forum_info = !empty($forum_data[$forum_id]) ? $forum_data[$forum_id] : array();
	$topic_info = !empty($topic_data[$topic_id]) ? $topic_data[$topic_id] : array();
	$post_info = !empty($post_data[$post_id]) ? $post_data[$post_id] : array();
}
else
{
	if ($not_moderator)
	{
		// The user has submitted posts or topics but is not allowed to perform mod actions to them
		trigger_error('Not_Moderator');
	}
	else
	{
		$forumless_modes = array('', 'front', 'post_reports', 'mod_queue');
		if (!in_array($mode, $forumless_modes))
		{
			// The user has submitted invalid post_ids or topic_ids
			trigger_error($user->lang['Topic_post_not_exist'] . $return_mcp);
		}
	}
}

//
// Check if user did or did not confirm
// If they did not, forward them to the last page they were on
//
if (isset($_POST['cancel']))
{
	if ($topic_id)
	{
		$redirect = "viewtopic.$phpEx$SID&t=$topic_id&amp;start=$start";
	}
	elseif ($forum_id)
	{
		$redirect = "viewforum.$phpEx$SID&f=$forum_id&amp;start=$start";
	}
	else
	{
		$redirect = "index.$phpEx$SID";
	}

	redirect($redirect);
}

// Build links and tabs
$mcp_url = "mcp.$phpEx$SID";
$tabs = array(
	'front'			=>	$mcp_url,
	'mod_queue'		=>	$mcp_url . '&amp;mode=mod_queue',
	'post_reports'	=>	$mcp_url . '&amp;mode=post_reports'
);

$mcp_url .= ($forum_id) ? '&amp;f=' . $forum_id : '';
$mcp_url .= ($topic_id) ? '&amp;t=' . $topic_id : '';
$mcp_url .= ($post_id) ? '&amp;p=' . $post_id : '';
$mcp_url .= ($start) ? '&amp;start=' . $start : '';
$url_extra = (!empty($_GET['post_id_list'])) ? '&amp;post_id_list=' . htmlspecialchars($_GET['post_id_list']) : '';
$return_mcp = '<br /><br />' . sprintf($user->lang['Click_return_modcp'], '<a href="' . $mcp_url . '">', '</a>');

if ($forum_id)
{
	$tabs['forum_view'] = $mcp_url . '&amp;mode=forum_view' . $url_extra;
}
if ($topic_id)
{
	$tabs['topic_view'] = $mcp_url . '&amp;mode=topic_view' . $url_extra;
}
if ($post_id)
{
	$tabs['post_view'] = $mcp_url . '&amp;mode=post_view' . $url_extra;
}
if (!empty($_GET['post_id_list']))
{
	$tabs['merge'] = $mcp_url . '&amp;mode=merge' . $url_extra;
}

if (count($forum_id_list) == 1 && !$forum_info['forum_postable'])
{
	trigger_error($user->lang['Forum_not_postable'] . $return_mcp);
}

if (!$mode)
{
	if ($topic_id)
	{
		$mode = 'topic_view';
	}
	elseif ($forum_id)
	{
		$mode = 'forum_view';
	}
	else
	{
		$mode = 'front';
	}
	
}

// Get the current tab from the mode
$tabs_mode = array(
	'mod_queue'		=>	'mod_queue',
	'post_reports'	=>	'post_reports',
	'split'			=>	'topic_view',
	'merge'			=>	(empty($_GET['post_id_list'])) ? 'topic_view' : 'merge',
	'ip'			=>	'post_view',
	'forum_view'	=>	'forum_view',
	'topic_view'	=>	'topic_view',
	'post_view'		=>	'post_view',
	'front'			=>	'front'
);

foreach ($tabs as $tab_name => $tab_link)
{
	$template->assign_block_vars('tab', array(
		'S_IS_SELECTED'	=>	(!empty($tabs_mode[$mode]) && $tab_name == $tabs_mode[$mode]) ? TRUE : FALSE,
		'NAME'			=>	$user->lang['mod_tabs'][$tab_name],
		'U_LINK'		=>	$tab_link
	));
}

//
// Do major work ...
//
switch ($mode)
{
	case 'resync':
		resync('topic', 'topic_id', $topic_id_list);

		$redirect_page = "mcp.$phpEx$SID&amp;f=$forum_id";
		$l_redirect = sprintf($user->lang['Click_return_modcp'], '<a href="mcp.' . $phpEx . $SID . '&amp;f=' . $forum_id . '">', '</a>');

		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="3;url=' . $redirect_page . '">')
		);

		$msg = (count($topic_id_list) == 1) ? $user->lang['TOPIC_RESYNCHRONISED'] : $user->lang['TOPICS_RESYNCHRONISED'];
		trigger_error($msg . '<br /><br />' . $l_redirect);
	break;

	case 'delete_posts':

	// TODO: what happens if the user deletes the first post? Currently, the topic is resync'ed normally and topic time/topic author are updated with the new first post

		if (!$post_id_list)
		{
			trigger_error($user->lang['None_selected']);
		}

		if ($confirm)
		{
			delete_posts('post_id', $post_id_list);

			$redirect_page = "mcp.$phpEx$SID&amp;f=$forum_id";
			$l_redirect = sprintf($user->lang['Click_return_modcp'], '<a href="mcp.' . $phpEx . $SID . '&amp;f=' . $forum_id . '">', '</a>');

			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . $redirect_page . '">')
			);

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
			'MESSAGE_TITLE' => $user->lang['Confirm'],
			'MESSAGE_TEXT' => (count($post_id_list) == 1) ? $user->lang['CONFIRM_DELETE'] : $user->lang['CONFIRM_DELETE_POSTS'],

			'L_YES' => $user->lang['YES'],
			'L_NO' => $user->lang['NO'],

			'S_CONFIRM_ACTION' => $mcp_url . '&amp;mode=delete_posts',
			'S_HIDDEN_FIELDS' => $hidden_fields
		));
	break;

	case 'delete':
		if (!$topic_id_list)
		{
			trigger_error($user->lang['None_selected']);
		}

		if ($confirm)
		{
			delete_topics('topic_id', $topic_id_list);

			if ($quickmod)
			{
				$redirect_page = "";
				$l_redirect = sprintf($user->lang['Click_return_forum'], '<a href="viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_id . '">', '</a>');
			}
			else
			{
				$redirect_page = "mcp.$phpEx$SID&amp;f=$forum_id";
				$l_redirect = sprintf($user->lang['Click_return_modcp'], '<a href="mcp.' . $phpEx . $SID . '&amp;f=' . $forum_id . '">', '</a>');
			}

			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . $redirect_page . '">')
			);

			trigger_error($user->lang['Topics_Removed'] . '<br /><br />' . $l_redirect);
		}

		// Not confirmed, show confirmation message
		$hidden_fields = '<input type="hidden" name="mode" value="delete" />';
		foreach ($topic_id_list as $t_id)
		{
			$hidden_fields .= '<input type="hidden" name="topic_id_list[]" value="' . $t_id . '" />';
		}

		// Set template files
		mcp_header('confirm_body.html');

		$template->assign_vars(array(
			'MESSAGE_TITLE' => $user->lang['Confirm'],
			'MESSAGE_TEXT' => $user->lang['Confirm_delete_topic'],

			'L_YES' => $user->lang['YES'],
			'L_NO' => $user->lang['NO'],

			'S_CONFIRM_ACTION' => $mcp_url . '&amp;mode=delete' . (($quickmod) ? '&amp;quickmod=1' : ''),
			'S_HIDDEN_FIELDS' => $hidden_fields
		));
	break;

	case 'select_topic':
		$max_len = 0;
		$short_id_list = array();

		foreach ($post_id_list as $post_id)
		{
			$short = base_convert($post_id, 10, 36);
			$max_len = max(strlen($short), $max_len);
			$short_id_list[] = $short;
		}

		$post_id_str = $max_len;
		foreach ($short_id_list as $short)
		{
			$post_id_str .= str_pad($short, $max_len, '0', STR_PAD_LEFT);
		}
		
		redirect(str_replace('&amp;', '&', $mcp_url) . '&mode=forum_view&post_id_list=' . $post_id_str);
	break;

	case 'merge':
	case 'split':
	case 'massdelete':
	case 'topic_view':
		mcp_header('mcp_topic.html', TRUE);

		$template->assign_vars(array(
			'FORUM_NAME'		=>	$forum_info['forum_name'],
			'U_VIEW_FORUM'		=>	"viewforum.$phpEx$SID&amp;f=$forum_id",

			'TO_TOPIC_ID'		=>	($to_topic_id) ? $to_topic_id : '',
			'TO_TOPIC_EXPLAIN'	=>	($to_topic_id) ? sprintf($user->lang['Topic_number_is'], $to_topic_id, '<a href="viewtopic.' . $phpEx . $SID . '&amp;t=' . $to_topic_id . '" target="_new">' . htmlspecialchars($topic_data[$to_topic_id]['topic_title']) . '</a>') : '',

			'S_FORM_ACTION'		=>	$mcp_url,
			'S_FORUM_SELECT'	=>	'<select name="new_forum_id">' . make_forum_select() . '</select>',
			'S_ENABLE_SPLIT'	=>	($auth->acl_get('m_split', $forum_id) &&($mode == 'topic_view' || $mode == 'split')) ? TRUE : FALSE,
			'S_ENABLE_MERGE'	=>	($auth->acl_get('m_merge', $forum_id) &&($mode == 'topic_view' || $mode == 'merge')) ? TRUE : FALSE,
			'S_ENABLE_DELETE'	=>	($auth->acl_get('m_delete', $forum_id) &&($mode == 'topic_view' || $mode == 'massdelete')) ? TRUE : FALSE
		));

		$is_first_post = TRUE;

		$sql = "SELECT u.username, p.*, pt.*
			FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
			WHERE p.topic_id = $topic_id
				AND p.poster_id = u.user_id
				AND p.post_id = pt.post_id
			ORDER BY p.post_time ASC";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$poster = (!empty($row['username'])) ? $row['username'] : ((!$row['post_username']) ? $user->lang['Guest'] : $row['post_username']);

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

			$checked = ($mode == 'merge' && in_array($row['post_id'], $selected_post_ids)) ? 'checked="checked" ' : '';
			$s_checkbox = ($is_first_post && $mode == 'split') ? '&nbsp;' : '<input type="checkbox" name="post_id_list[]" value="' . $row['post_id'] . '" ' . $checked . '/>';

			$template->assign_block_vars('postrow', array(
				'POSTER_NAME'	=>	$poster,
				'POST_DATE'		=>	$user->format_date($row['post_time']),
				'POST_SUBJECT'	=>	$post_subject,
				'MESSAGE'		=>	$message,
				'POST_ID'		=>	$row['post_id'],

				'S_CHECKBOX'	=>	$s_checkbox
			));

			$is_first_post = FALSE;
		}
	break;

	case 'move':
		if ($confirm)
		{
			if (!$new_forum_id = intval($_POST['new_forum_id']))
			{
				trigger_error('Forum_not_exist');
			}
			if ($new_forum_id != $forum_id)
			{
				move_topics($topic_id_list, $new_forum_id);
			}
			trigger_error('done');
		}

		foreach ($topic_data as $row)
		{
			$template->assign_block_vars('topiclist', array(
				'TOPIC_TITLE'	=>	$row['topic_title']
			));
		}

		$s_hidden_fields = '';
		foreach ($topic_id_list as $topic_id)
		{
			$s_hidden_fields .= '<input type="hidden" name="topic_id_list[]" value="' . $topic_id . '">';
		}

		$template->assign_vars(array(
			'S_HIDDEN_FIELDS'	=>	$s_hidden_fields,
			'S_FORUM_SELECT'	=>	make_forum_select(0, $forum_id)
		));

		mcp_header('mcp_move.html');
	break;

	case 'lock':
	case 'unlock':
		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET topic_status = ' . (($mode == 'lock') ? ITEM_LOCKED : ITEM_UNLOCKED) . '
			WHERE topic_id IN (' . implode(', ', $topic_id_list) . ')
				AND topic_moved_id = 0';
		$db->sql_query($sql);

		$message = (($mode == 'lock') ? $user->lang['Topics_Locked'] : $user->lang['Topics_Unlocked']) . '<br /><br />';
		if (isset($_GET['quickmod']))
		{
			$redirect_page = "viewtopic.$phpEx$SID&amp;t=$topic_id&amp;start=$start";
			$message .= sprintf($user->lang['Click_return_topic'], '<a href="' . $redirect_page . '">', '</a>');
		}
		else
		{
			$redirect_page = $mcp_url . '&amp;mode=forum_view';
			$message .= sprintf($user->lang['Click_return_modcp'], '<a href="' . $redirect_page . '">', '</a>');
		}

		$message .= '<br \><br \>' . sprintf($user->lang['Click_return_forum'], "<a href=\"viewforum.$phpEx$SID&amp;f=$forum_id\">", '</a>');

		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="3;url=' . $redirect_page . '">'
		));

		trigger_error($message);
	break;

	case 'merge_posts':
		$return_url = '<br /><br />' . sprintf($user->lang['Click_return_topic'], '<a href="viewtopic.' . $phpEx . $SID . '&amp;t=' . $to_topic_id . '">', '</a>');
		move_posts($post_id_list, $to_topic_id);

		trigger_error($user->lang['Posts_merged'] . $return_url . $return_mcp);
	break;

	case 'split':
		$return_url = '<br /><br />' . sprintf($user->lang['Click_return_topic'], '<a href="viewtopic.' . $phpEx . $SID . '&amp;t=' . $to_topic_id . '">', '</a>');
		move_posts($post_id_list, $new_topic_id);

		trigger_error($user->lang['Posts_merged'] . $return_url . $return_mcp);
	break;

	case 'ip':
		mcp_header('mcp_viewip.html');

		$rdns_ip_num = (isset($_GET['rdns'])) ? $_GET['rdns'] : '';

		if (!$post_id)
		{
			trigger_error('No_such_post');
		}

		$ip_this_post = $post_info['poster_ip'];
		$ip_this_post = ($rdns_ip_num == $ip_this_post) ? @gethostbyaddr($ip_this_post) : $ip_this_post;

		$template->assign_vars(array(
			'L_IP_INFO' => $user->lang['IP_info'],
			'L_THIS_POST_IP' => $user->lang['This_posts_IP'],
			'L_OTHER_IPS' => $user->lang['Other_IP_this_user'],
			'L_OTHER_USERS' => $user->lang['Users_this_IP'],
			'L_LOOKUP_IP' => $user->lang['Lookup_IP'],
			'L_SEARCH' => $user->lang['Search'],

			'SEARCH_IMG' => $images['icon_search'],

			'IP' => $ip_this_post,

			'U_LOOKUP_IP' => $mcp_url . '&amp;mode=ip&amp;rdns=' . $ip_this_post
		));

		// Get other IP's this user has posted under
		$sql = 'SELECT poster_ip, COUNT(*) AS postings
			FROM ' . POSTS_TABLE . '
			WHERE poster_id = ' . $post_info['poster_id'] . '
			GROUP BY poster_ip
			ORDER BY postings DESC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['poster_ip'] == $post_info['poster_ip'])
			{
				$template->assign_vars(array(
					'POSTS' => $row['postings'] . ' ' . (($row['postings'] == 1) ? $user->lang['Post'] : $user->lang['Posts'])
				));
				continue;
			}

			$ip = $row['poster_ip'];
			$ip = ($rdns_ip_num == $row['poster_ip'] || $rdns_ip_num == 'all') ? gethostbyaddr($ip) : $ip;

			$template->assign_block_vars('iprow', array(
				'IP' => $ip,
				'POSTS' => $row['postings'] . ' ' . (($row['postings'] == 1) ? $user->lang['Post'] : $user->lang['Posts']),

				'U_LOOKUP_IP' => $mcp_url . '&amp;mode=ip&amp;rdns=' . $row['poster_ip'])
			);
		}
		$db->sql_freeresult($result);

		// Get other users who've posted under this IP
		$sql = "SELECT u.user_id, u.username, COUNT(*) as postings
			FROM " . USERS_TABLE ." u, " . POSTS_TABLE . " p
			WHERE p.poster_id = u.user_id
				AND p.poster_ip = '" . $post_info['poster_ip'] . "'
			GROUP BY u.user_id, u.username
			ORDER BY postings DESC";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$i = 0;
			do
			{
				$id = $row['user_id'];
				$username = (!$id) ? $user->lang['Guest'] : $row['username'];

				$template->assign_block_vars('userrow', array(
					'USERNAME' => $username,
					'POSTS' => $row['postings'] . ' ' . (($row['postings'] == 1) ? $user->lang['Post'] : $user->lang['Posts']),
					'L_SEARCH_POSTS' => sprintf($user->lang['Search_user_posts'], $username),

					'U_PROFILE' => "ucp.$phpEx$SID&amp;mode=viewprofile&amp;u=$id",
					'U_SEARCHPOSTS' => "search.$phpEx$SID&amp;search_author=" . urlencode($username) . "&amp;showresults=topics")
				);

				$i++;
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);
	break;

	case 'forum_view':
		mcp_header('mcp_forum.html', TRUE);

		$template->assign_vars(array(
			'FORUM_NAME' => $forum_info['forum_name'],

			'S_CAN_DELETE'	=>	$auth->acl_get('m_delete', $forum_id),
			'S_CAN_MOVE'	=>	$auth->acl_get('m_move', $forum_id),
			'S_CAN_LOCK'	=>	$auth->acl_get('m_lock', $forum_id),
			'S_CAN_UNLOCK'	=>	$auth->acl_get('m_unlock', $forum_id),
			'S_CAN_RESYNC'	=>	$auth->acl_gets('m_', 'a_', $forum_id),

			'U_VIEW_FORUM' => "viewforum.$phpEx$SID&amp;f=$forum_id",
			'S_HIDDEN_FIELDS' => '<input type="hidden" name="f" value="' . $forum_id . '">',
			'S_MCP_ACTION' => $mcp_url
		));

		// Define censored word matches
		$orig_word = array();
		$replacement_word = array();
		obtain_word_list($orig_word, $replacement_word);

		$sql = "SELECT t.*, u.username, u.user_id
			FROM " . TOPICS_TABLE . " t, " . USERS_TABLE . " u
			WHERE t.forum_id = $forum_id
				AND t.topic_poster = u.user_id
			ORDER BY t.topic_type DESC, t.topic_last_post_time DESC
			LIMIT $start, " . $config['topics_per_page'];
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$topic_title = '';

			if ($row['topic_status'] == ITEM_LOCKED)
			{
				$folder_img = $user->img('folder_locked', 'Topic_locked');
			}
			else
			{
				if ($row['topic_type'] == POST_ANNOUNCE)
				{
					$folder_img = $user->img('folder_announce', 'Announcement');
				}
				else if ($row['topic_type'] == POST_STICKY)
				{
					$folder_img = $user->img('folder_sticky', 'Sticky');
			}
				else
				{
					$folder_img = $user->img('folder', 'No_new_posts');
				}
			}

			if ($row['topic_type'] == POST_ANNOUNCE)
			{
				$topic_type = $user->lang['Topic_Announcement'] . ' ';
			}
			else if ($row['topic_type'] == POST_STICKY)
			{
				$topic_type = $user->lang['Topic_Sticky'] . ' ';
			}
			else if ($row['topic_status'] == ITEM_MOVED)
			{
				$topic_type = $user->lang['Topic_Moved'] . ' ';
			}
			else
			{
				$topic_type = '';
			}

			if (intval($row['poll_start']))
			{
				$topic_type .= $user->lang['Topic_Poll'] . ' ';
			}

			// Shouldn't moderators be allowed to read uncensored title?
			$topic_title = $row['topic_title'];
			if (count($orig_word))
			{
				$topic_title = preg_replace($orig_word, $replacement_word, $topic_title);
			}

			$template->assign_block_vars('topicrow', array(
				'U_VIEW_TOPIC' => $mcp_url . '&amp;t=' . $row['topic_id'] . '&amp;mode=topic_view',

				'S_MERGE_SELECT' => ($topic_id && isset($_GET['post_id_list']) && $row['topic_id'] != $topic_id) ? sprintf($user->lang['Select_for_merge'], '<a href="' . $mcp_url . '&amp;mode=merge&amp;to_topic_id=' . $row['topic_id'] . $url_extra . '">', '</a>') : '',

				'TOPIC_FOLDER_IMG' => $folder_img,
				'TOPIC_TYPE' => $topic_type,
				'TOPIC_TITLE' => $topic_title,
				'REPLIES' => $row['topic_replies'],
				'LAST_POST_TIME' => $user->format_date($row['topic_last_post_time']),
				'TOPIC_ID' => $row['topic_id']
			));
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'PAGINATION' => generate_pagination("mcp.$phpEx$SID&amp;f=$forum_id", $forum_info['forum_topics'], $config['topics_per_page'], $start),
			'PAGE_NUMBER' => sprintf($user->lang['PAGE_OF'], (floor($start / $config['topics_per_page']) + 1), ceil($forum_info ['forum_topics'] / $config['topics_per_page'])),
			'L_GOTO_PAGE' => $user->lang['GOTO_PAGE'])
		);
	break;

	case 'front':
	default:
		mcp_header('mcp_front.html');

}

include($phpbb_root_path . 'includes/page_tail.' . $phpEx);

// -----------------------
// Page specific functions
//
function mcp_header($template_name, $forum_nav = FALSE, $jump_mode = 'forum_view')
{
	global $phpbb_root_path, $phpEx, $SID, $template, $auth, $user, $db, $config;
	global $forum_id, $forum_info;

	$forum_id = (!empty($forum_id)) ? $forum_id : FALSE;
	$extra_form_fields = array(
		'mode'	=>	$jump_mode
	);

	// NOTE: this will stop working if the jumpbox method is changed to POST
	if (!empty($_GET['post_id_list']))
	{
		$extra_form_fields['post_id_list'] = $_GET['post_id_list'];
	}

	$page_title = sprintf($user->lang['MCP'], '', '');
	include($phpbb_root_path . 'includes/page_header.' . $phpEx);

	$template->set_filenames(array(
		'body' => $template_name
	));

	make_jumpbox('mcp.' . $phpEx, $forum_id, $extra_form_fields);

	if ($forum_nav)
	{
		generate_forum_nav($forum_info);
	}
	$template->assign_var('S_FORUM_NAV', $forum_nav);
}

function move_topics($topic_ids, $forum_id, $auto_sync = TRUE)
{
	global $db;

	$forum_ids = array($forum_id);
	$where_sql = (is_array($topic_ids)) ? 'IN (' . implode(', ', $topic_ids) . ')' : '= ' . $topic_ids;

	if ($auto_sync)
	{
		$sql = 'SELECT DISTINCT forum_id
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id ' . $where_sql;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$forum_ids[] = $row['forum_id'];
		}
	}

	$sql = 'DELETE FROM ' . TOPICS_TABLE . "
			WHERE topic_moved_id $where_sql
				AND forum_id = " . $forum_id;
	$db->sql_query($sql);

	$sql = 'UPDATE ' . TOPICS_TABLE . "
		SET forum_id = $forum_id
		WHERE topic_id " . $where_sql;
	$db->sql_query($sql);

	$sql = 'UPDATE ' . POSTS_TABLE . "
		SET forum_id = $forum_id
		WHERE topic_id " . $where_sql;
	$db->sql_query($sql);

	if ($auto_sync)
	{
		resync('forum', 'forum_id', $forum_ids);
	}
}

function move_posts($post_ids, $topic_id, $auto_sync = TRUE)
{
	global $db;
	$topic_ids = array($topic_id);

	if ($auto_sync)
	{
		$sql = 'SELECT DISTINCT topic_id
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id IN (' . implode(', ', $post_ids) . ')';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$topic_ids[] = $row['topic_id'];
		}
	}

	$sql = 'SELECT * FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . $topic_id;
	$result = $db->sql_query($sql);
	if (!$row = $db->sql_fetchrow($result))
	{
		trigger_error('Topic_post_not_exist');
	}

	if (!is_array($post_ids))
	{
		$post_ids = array($post_ids);
	}
	$sql = 'UPDATE ' . POSTS_TABLE . '
		SET forum_id = ' . $row['forum_id'] . ", topic_id = $topic_id
		WHERE post_id IN (" . implode(', ', $post_ids) . ')';
	$db->sql_query($sql);

	if ($auto_sync)
	{
		resync('topic', 'topic_id', $topic_ids);
		resync('forum', 'forum_id', $row['forum_id']);
	}
}

function delete_topics($where_type, $where_ids, $auto_sync = TRUE)
{
	global $db;
	$forum_ids = $topic_ids = array();

	if (is_array($where_ids))
	{
		$where_ids = array_unique($where_ids);
	}
	delete_posts($where_type, $where_ids, FALSE);

	$where_sql = "WHERE $where_type " . ((!is_array($where_ids)) ? "= $where_ids" : 'IN (' . implode(', ', $where_ids) . ')');

	$sql = 'SELECT topic_id, forum_id
		FROM ' . TOPICS_TABLE . "
		WHERE $where_type " . ((!is_array($where_ids)) ? "= $where_ids" : 'IN (' . implode(', ', $where_ids) . ')');

	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$forum_ids[] = $row['forum_id'];
		$topic_ids[] = $row['topic_id'];
	}

	if (!count($topic_ids))
	{
		return;
	}

	// TODO: clean up topics cache if any, last read marking, probably some other stuff too

	$where_sql = ' IN (' . implode(', ', $topic_ids) . ')';

	$db->sql_query('DELETE FROM ' . POLL_VOTES_TABLE . ' WHERE topic_id' . $where_sql);
	$db->sql_query('DELETE FROM ' . POLL_OPTIONS_TABLE . ' WHERE topic_id' . $where_sql);
	$db->sql_query('DELETE FROM ' . TOPICS_WATCH_TABLE . ' WHERE topic_id' . $where_sql);
	$db->sql_query('DELETE FROM ' . TOPICS_TABLE . ' WHERE topic_moved_id' . $where_sql);
	$db->sql_query('DELETE FROM ' . TOPICS_TABLE . ' WHERE topic_id' . $where_sql);

	if ($auto_sync)
	{
		resync('forum', 'forum_id', $forum_ids);
	}
}

function delete_posts($where_type, $where_ids, $auto_sync = TRUE)
{
	global $db;

	if (!$where_ids)
	{
		return;
	}
	if (is_array($where_ids))
	{
		$where_ids = array_unique($where_ids);
	}

	$post_ids = $topic_ids = $forum_ids = array();

	$sql = 'SELECT post_id, topic_id, forum_id
		FROM ' . POSTS_TABLE . "
		WHERE $where_type " . ((!is_array($where_ids)) ? "= $where_ids" : 'IN (' . implode(', ', $where_ids) . ')');

	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$post_ids[] = $row['post_id'];
		$topic_ids[] = $row['topic_id'];
		$forum_ids[] = $row['forum_id'];
	}

	if (!count($post_ids))
	{
		return;
	}

	$where_sql = ' WHERE post_id IN (' . implode(', ', $post_ids) . ')';

	$db->sql_query('DELETE FROM ' . POSTS_TABLE . $where_sql);
	$db->sql_query('DELETE FROM ' . POSTS_TEXT_TABLE . $where_sql);
	$db->sql_query('DELETE FROM ' . RATINGS_TABLE . $where_sql);
	$db->sql_query('DELETE FROM ' . SEARCH_MATCH_TABLE . $where_sql);

	if ($auto_sync)
	{
		resync('topic', 'topic_id', $topic_ids);
		resync('forum', 'forum_id', $forum_ids);
	}
}

//
// Usage:
// sync('topic', 'topic_id', 123);			<= resynch topic #123
// sync('topic', 'forum_id', array(2, 3));	<= resynch topics from forum #2 and #3
// sync('topic');							<= resynch all topics
//
function resync($type, $where_type = '', $where_ids = '')
{
	global $db;

	switch ($where_type)
	{
		case 'topic_id':
		case 'forum_id':
			if (is_array($where_ids))
			{
				$where_ids = array_unique($where_ids);
				$where_sql = 'WHERE ' . $type{0} . ".$where_type IN (" . implode(', ', $where_ids) . ') AND';
			}
			else
			{
				$where_sql = 'WHERE ' . $type{0} . ".$where_type = " . intval($where_ids) . ' AND';
			}
		break;
	
		default:
			$where_sql = 'WHERE';
	}

	switch ($type)
	{
		case 'forum':
			$sql = 'SELECT f.forum_id, f.forum_posts, f.forum_last_post_id, f.forum_last_poster_id, f.forum_last_poster_name, COUNT(p.post_id) AS posts, MAX(p.post_id) AS last_post_id
			FROM ' . FORUMS_TABLE . ' f, ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
			$where_sql p.forum_id = f.forum_id
				AND p.post_approved = 1
				GROUP BY f.forum_id";

			$forum_data = $post_ids = array();

			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$post_ids[] = $row['last_post_id'];
				$forum_data[$row['forum_id']] = $row;
			}

			$sql = 'SELECT t.forum_id, COUNT(t.topic_id) AS forum_topics
				FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f
				$where_sql t.topic_type <> " . ITEM_MOVED . '
					AND t.topic_approved = 1
				GROUP BY t.forum_id';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$forum_data[$row['forum_id']]['topics'] = $row['forum_topics'];
			}

			if (count($post_ids))
			{
				$sql = 'SELECT p.post_id, p.poster_id, p.post_username, p.post_time, u.username
					FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
					WHERE p.post_id IN (' . implode(', ', $post_ids) . ')
						AND p.post_approved = 1
						AND u.user_id = p.poster_id';
				$db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$forum_data[$row['forum_id']]['last_post_time'] = $row['post_time'];
					$forum_data[$row['forum_id']]['last_poster_id'] = $row['poster_id'];
					$forum_data[$row['forum_id']]['last_poster_name'] = ($row['poster_id'] == ANONYMOUS) ? $row['post_username'] : $row['username'];
				}
			}

			$fieldnames = array('posts', 'topics', 'last_post_id', 'last_post_time', 'last_poster_id', 'last_poster_name');

			foreach ($forum_data as $forum_id => $row)
			{
				$need_update = FALSE;

				foreach ($fieldnames as $fieldname)
				{
					verify_data('forum', $fieldname, $need_update, $row);
				}

				if ($need_update)
				{
					$sql = array();
					foreach ($fieldnames as $fieldname)
					{
						if (preg_match('/name$/', $fieldname))
						{
							$sql['forum_' . $fieldname] = (string) $row['forum_' . $fieldname];
						}
						else
						{
							$sql['forum_' . $fieldname] = (int) $row['forum_' . $fieldname];
						}
					}

					$sql = 'UPDATE ' . FORUMS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql) . '
							WHERE forum_id = ' . $forum_id;
					$db->sql_query($sql);
				}
			}
		break;

		case 'topic':
			$topic_data = $post_ids = $topic_ids = array();

			$sql = 'SELECT t.topic_id, t.topic_time, t.topic_replies, t.topic_first_post_id, t.topic_first_poster_name, t.topic_last_post_id, t.topic_last_poster_id, t.topic_last_poster_name, t.topic_last_post_time, COUNT(p.post_id) AS total_posts, MIN(p.post_id) AS first_post_id, MAX(p.post_id) AS last_post_id
				FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
				$where_sql p.topic_id = t.topic_id
				GROUP BY t.topic_id";

			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['total_posts'] == 0)
				{
					$topic_ids[] = $row['topic_id'];
					continue;
				}

				$post_ids[] = $row['last_post_id'];
				if ($row['first_post_id'] != $row['last_post_id'])
				{
					$post_ids[] = $row['first_post_id'];
				}

				$row['replies'] = $row['total_posts'] - 1;
				$topic_data[$row['topic_id']] = $row;
			}

			if (count($topic_ids))
			{
				echo '<pre>';print_r($topic_ids);
				delete_topics($topic_ids);
			}
			if (!count($post_ids))
			{
				// uh-oh
				return;
			}

			$sql = 'SELECT post_id, topic_id, poster_id, post_username, post_time
				FROM ' . POSTS_TABLE . '
				WHERE post_id IN (' . implode(', ', $post_ids) . ')';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{

				if ($row['post_id'] == $topic_data[$row['topic_id']]['last_post_id'])
				{
					$topic_data[$row['topic_id']]['last_poster_id'] = $row['poster_id'];
					$topic_data[$row['topic_id']]['last_post_time'] = $row['post_time'];
					$topic_data[$row['topic_id']]['last_poster_name'] = ($row['poster_id'] == ANONYMOUS) ? $row['post_username'] : '';
				}
				if ($row['post_id'] == $topic_data[$row['topic_id']]['first_post_id'])
				{
					$topic_data[$row['topic_id']]['time'] = $row['post_time'];
					$topic_data[$row['topic_id']]['first_poster_name'] = ($row['poster_id'] == ANONYMOUS) ? $row['post_username'] : '';
				}
			}

			$fieldnames = array('time', 'replies', 'first_post_id', 'first_poster_name', 'last_post_id', 'last_post_time', 'last_poster_id', 'last_poster_name');

			foreach ($topic_data as $topic_id => $row)
			{
				$need_update = FALSE;

				foreach ($fieldnames as $fieldname)
				{
					verify_data('topic', $fieldname, $need_update, $row);
				}

				if ($need_update)
				{
					$sql = array();
					foreach ($fieldnames as $fieldname)
					{
						if (preg_match('/name$/', $fieldname))
						{
							$sql['topic_' . $fieldname] = (string) $row['topic_' . $fieldname];
						}
						else
						{
							$sql['topic_' . $fieldname] = (int) $row['topic_' . $fieldname];
						}
					}

					$sql = 'UPDATE ' . TOPICS_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql) . '
							WHERE topic_id = ' . $topic_id;
					$db->sql_query($sql);
				}
			}
	}

	// Should we resync posts and topics totals in config table?
	/*
	$result = $db->sql_query('SELECT SUM(forum_topics) AS total_topics, SUM(forum_posts) AS total_posts FROM ' . FORUMS_TABLE);
	$row = $db->sql_fetchrow($result);
	set_config('total_posts', $row['total_posts']);
	set_config('total_topics', $row['total_topics']);
	*/
}


function verify_data($type, $fieldname, &$need_update, &$data)
{
	// Check if the corresponding data actually exists. Must not fail when equal to zero.
	if (!isset($data[$fieldname]) || is_null($data[$fieldname]))
	{
		return;
	}

	if ($data[$fieldname] != $data[$type . '_' . $fieldname])
	{
		$need_update = TRUE;
		$data[$type . '_' . $fieldname] = $data[$fieldname];
	}
}
//
// End page specific functions
// ---------------------------

/*****
Temp function
*****/
function very_temporary_lang_strings()
{
	global $user;
	$user->lang['Forum_not_postable'] = 'This forum is not postable';
	$user->lang['SELECTED_TOPICS'] = 'You selected the following topic(s)';
	$user->lang['Topic_not_exist'] = 'The topic you selected does not exist';
	$user->lang['Posts_merged'] = 'The selected posts have been merged';
	$user->lang['Select_for_merge'] = '%sSelect%s';
	$user->lang['Select_topic'] = 'Select topic';
	$user->lang['Topic_number_is'] = 'Topic #%d is %s';
	$user->lang['POST_REMOVED'] = 'The selected post has been successfully removed from the database.';
	$user->lang['POSTS_REMOVED'] = 'The selected posts have been successfully removed from the database.';
	$user->lang['CONFIRM_DELETE_POSTS'] = 'Are you sure you want to delete these posts?';
	$user->lang['TOPIC_RESYNCHRONISED'] = 'The selected topic has been resynchronised';
	$user->lang['TOPICS_RESYNCHRONISED'] = 'The selected topics have been resynchronised';
	$user->lang['RESYNC'] = 'Resync';

	$user->lang['mod_tabs'] = array(
		'front' => 'Front Page',
		'mod_queue' => 'Mod Queue',
		'forum_view' => 'Forum View',
		'topic_view' => 'Topic View',
		'post_view' => 'Post View',
		'post_reports' => 'Reported Posts',
		'merge' => 'Merge'
	);
}
?>