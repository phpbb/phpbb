<?php
/***************************************************************************
 *                           functions_posting.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
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

// Fill smiley templates (or just the variables) with smileys
// Either in a window or inline
function generate_smilies($mode)
{
	global $SID, $auth, $db, $user, $config, $template;
	global $starttime, $phpEx, $phpbb_root_path;

	// TODO: To be added to the schema
	$config['max_smilies_inline'] = 20;

	if ($mode == 'window')
	{
		$page_title = $user->lang['TOPIC_REVIEW'] . " - $topic_title";
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			'body' => 'posting_smilies.html')
		);
	}

	$where_sql = ($mode == 'inline') ? 'WHERE display_on_posting = 1 ' : '';
	$sql = "SELECT emoticon, code, smile_url, smile_width, smile_height
	FROM " . SMILIES_TABLE . "
	$where_sql
	ORDER BY smile_order";

	$result = $db->sql_query($sql);

	$num_smilies = 0;
	$smile_array = array();
	if ($row = $db->sql_fetchrow($result))
	{
		do
		{
			if (!in_array($row['smile_url'], $smile_array))
			{
				if ($mode == 'window' || ($mode == 'inline' && $num_smilies < $config['max_smilies_inline']))
				{
					$template->assign_block_vars('emoticon', array(
						'SMILEY_CODE' 	=> $row['code'],
						'SMILEY_IMG' 	=> $config['smilies_path'] . '/' . $row['smile_url'],
						'SMILEY_WIDTH' 	=> $row['smile_width'],
						'SMILEY_HEIGHT' => $row['smile_height'],
						'SMILEY_DESC' 	=> $row['emoticon'])
					);
				}

				$smile_array[] = $row['smile_url'];
				$num_smilies++;
			}
		}
		while ($row = $db->sql_fetchrow($result));
		$db->sql_freeresult($result);

		if ($mode == 'inline' && $num_smilies >= $config['max_smilies_inline'])
		{
			$template->assign_vars(array(
				'S_SHOW_EMOTICON_LINK' 	=> true,
				'U_MORE_SMILIES' 		=> "posting.$phpEx$SID&amp;mode=smilies")
			);
		}
	}

	if ($mode == 'window')
	{
		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
}

// Generate Topic Icons
function generate_topic_icons($mode, $enable_icons)
{
	global $template, $config;

	if (!$enable_icons)
	{
		return (false);
	}
	
	$result = false;

	// Grab icons
	$icons = array();
	obtain_icons($icons);

	if (sizeof($icons))
	{
		$result = true;

		foreach ($icons as $id => $data)
		{
			if ($data['display'])
			{
				$template->assign_block_vars('topic_icon', array(
					'ICON_ID'		=> $id,
					'ICON_IMG'		=> $phpbb_root_path . $config['icons_path'] . '/' . $data['img'],
					'ICON_WIDTH'	=> $data['width'],
					'ICON_HEIGHT' 	=> $data['height'],

					'S_ICON_CHECKED' => ($id == $icon_id && $mode != 'reply') ? ' checked="checked"' : '')
				);
			}
		}
	}

	return ($result);
}

// DECODE TEXT -> This will/should be handled by bbcode.php eventually
function decode_text(&$message)
{
	global $config, $censors;

	$server_protocol = ($config['cookie_secure']) ? 'https://' : 'http://';
	$server_port = ($config['server_port'] <> 80) ? ':' . trim($config['server_port']) . '/' : '/';

	$match = array(
		'#<!\-\- b \-\-><b>(.*?)</b><!\-\- b \-\->#s',
		'#<!\-\- u \-\-><u>(.*?)</u><!\-\- u \-\->#s',
		'#<!\-\- e \-\-><a href="mailto:(.*?)">.*?</a><!\-\- e \-\->#',
		'#<!\-\- m \-\-><a href="(.*?)" target="_blank">.*?</a><!\-\- m \-\->#',
		'#<!\-\- w \-\-><a href="http:\/\/(.*?)" target="_blank">.*?</a><!\-\- w \-\->#',
		'#<!\-\- l \-\-><a href="(.*?)" target="_blank">.*?</a><!\-\- l \-\->#',
		'#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#',
	);

	$replace = array(
		'[b]\1[/b]',
		'[u]\1[/u]',
		'\1',
		'\1',
		'\1',
		$server_protocol . trim($config['server_name']) . $server_port . preg_replace('/^\/?(.*?)(\/)?$/', '\1', trim($config['script_path'])) . '/\1',
		'\1',
	);

	if (empty($censors))
	{
		$censors = array();
		obtain_word_list($censors);
	}

	$message = preg_replace($match, $replace, $message);

	return;
}

// Quote Text
function quote_text(&$message, $username = '')
{
	$message = ' [quote' . ( (empty($username)) ? ']' : '="' . addslashes(trim($username)) . '"]') . trim($message) . '[/quote] ';
}

// Topic Review
function topic_review($topic_id, $is_inline_review = false)
{
	global $SID, $db, $config, $template, $user, $auth, $phpEx, $phpbb_root_path, $starttime;
	global $censors;

	// Define censored word matches
	if (empty($censors))
	{
		$censors = array();
		obtain_word_list($censors);
	}

	if (!$is_inline_review)
	{
		// Get topic info ...
		$sql = "SELECT t.topic_title, f.forum_id
			FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f
			WHERE t.topic_id = $topic_id
				AND f.forum_id = t.forum_id";
		$result = $db->sql_query($sql);

		if (!($row = $db->sql_fetchrow($result)))
		{
			trigger_error($user->lang['NO_TOPIC']);
		}

		$forum_id = intval($row['forum_id']);
		$topic_title = $row['topic_title'];

		if (!$auth->acl_gets('f_read', 'm_', 'a_', $forum_id))
		{
			trigger_error($user->lang['SORRY_AUTH_READ']);
		}

		if (count($orig_word))
		{
			$topic_title = preg_replace($censors['match'], $censors['replace'], $topic_title);
		}
	}
	else
	{
		$template->assign_vars(array(
			'S_DISPLAY_INLINE'	=> true)
		);
	}

	// Go ahead and pull all data for this topic
	$sql = "SELECT u.username, u.user_id, p.* 
		FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u
		WHERE p.topic_id = $topic_id
			AND p.poster_id = u.user_id
		ORDER BY p.post_time DESC
		LIMIT " . $config['posts_per_page'];
	$result = $db->sql_query($sql);

	// Okay, let's do the loop, yeah come on baby let's do the loop
	// and it goes like this ...
	if ($row = $db->sql_fetchrow($result))
	{
		$i = 0;
		do
		{
			$poster_id = $row['user_id'];
			$poster = $row['username'];

			// Handle anon users posting with usernames
			if($poster_id == ANONYMOUS && $row['post_username'] != '')
			{
				$poster = $row['post_username'];
				$poster_rank = $user->lang['GUEST'];
			}

			$post_subject = ($row['post_subject'] != '') ? $row['post_subject'] : '';

			$message = $row['post_text'];

			if ($row['enable_smilies'])
			{
				$message = str_replace('<img src="{SMILE_PATH}', '<img src="' . $phpbb_root_path . $config['smilies_path'], $message);
			}

			if (count($orig_word))
			{
				$post_subject = preg_replace($censors['match'], $censors['replace'], $post_subject);
				$message = preg_replace($censors['match'], $censors['replace'], $message);
			}

			$template->assign_block_vars('postrow', array(
				'MINI_POST_IMG' 	=> $user->img('goto_post', $user->lang['POST']),
				'POSTER_NAME' 		=> $poster,
				'POST_DATE' 		=> $user->format_date($row['post_time']),
				'POST_SUBJECT' 		=> $post_subject,
				'MESSAGE' 			=> nl2br($message),

				'S_ROW_COUNT'	=> $i++)
			);
		}
		while ($row = $db->sql_fetchrow($result));
	}
	else
	{
		trigger_error($user->lang['NO_TOPIC']);
	}
	$db->sql_freeresult($result);

	$template->assign_vars(array(
		'L_MESSAGE' 	=> $user->lang['MESSAGE'],
		'L_POSTED' 		=> $user->lang['POSTED'],
		'L_POST_SUBJECT'=> $user->lang['POST_SUBJECT'],
		'L_TOPIC_REVIEW'=> $user->lang['TOPIC_REVIEW'])
	);

	if (!$is_inline_review)
	{
		$page_title = $user->lang['TOPIC_REVIEW'] . ' - ' . $topic_title;
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			'body' => 'posting_topic_review.html')
		);

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}
}

// Update Last Post Informations
function update_last_post_information($type, $id)
{
	global $db;

	switch ($type)
	{
		case 'forum':
			$sql_select_add = ', f.forum_parents';
			$sql_table_add = ', ' . FORUMS_TABLE . ' f';
			$sql_where_add = 'AND t.forum_id = f.forum_id AND f.forum_id = ' . $id;
			$sql_update_table = FORUMS_TABLE;
			break;

		case 'topic':
			$sql_select_add = '';
			$sql_table_add = '';
			$sql_where_add = 'AND t.topic_id = ' . $id;
			$sql_update_table = TOPICS_TABLE;
			break;
		default:
			return;
	}

	$sql = "SELECT p.post_id, p.poster_id, p.post_time, u.username, p.post_username " . $sql_select_add . " 
	FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . TOPICS_TABLE . " t" . $sql_table_add . "
	WHERE p.post_approved = 1 AND t.topic_approved = 1 AND p.poster_id = u.user_id AND t.topic_id = p.topic_id " . $sql_where_add . "
	ORDER BY p.post_time DESC LIMIT 1";

	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);

	if ($type == 'forum')
	{
		// Update forums: last post info, topics, posts ... we need to update
		// each parent too ...
		$forum_ids = $id;
		$forum_parents = trim($row['forum_parents']);

		if ($forum_parents != '')
		{
			$forum_parents = unserialize($forum_parents);
			foreach ($forum_parents as $parent_forum_id => $parent_name)
			{
				$forum_ids .= ', ' . $parent_forum_id;
			}
		}
		
		$where_clause = 'forum_id IN (' . $forum_ids . ')';
	}
	else if ($type == 'topic')
	{
		$where_clause = 'topic_id = ' . $id;
	}

	$update_sql = array(
		$type . '_last_post_id' => intval($row['post_id']),
		$type . '_last_post_time' => intval($row['post_time']),
		$type . '_last_poster_id' => intval($row['poster_id']),
		$type . '_last_poster_name' => (intval($row['poster_id']) == ANONYMOUS) ? trim($row['post_username']) : trim($row['username'])
	);

	
	$sql = 'UPDATE ' . $sql_update_table . ' SET ' . $db->sql_build_array('UPDATE', $update_sql) . ' WHERE ' . $where_clause;
	$db->sql_query($sql);
}

?>