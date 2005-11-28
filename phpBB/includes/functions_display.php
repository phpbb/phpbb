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
* Display Forums
*/
function display_forums($root_data = '', $display_moderators = true, $return_moderators = false)
{
	global $db, $auth, $user, $template;
	global $phpbb_root_path, $phpEx, $SID, $config;

	$forum_rows = $subforums = $forum_ids = $forum_ids_moderator = $forum_moderators = $active_forum_ary = array();
	$parent_id = $visible_forums = 0;
	$sql_from = $lastread_select = '';
	
	// Mark forums read?
	$mark_read = request_var('mark', '');

	if ($mark_read == 'all')
	{
		$mark_read = '';
	}

	if (!$root_data)
	{
		if ($mark_read == 'forums')
		{
			$mark_read = 'all';
		}

		$root_data = array('forum_id' => 0);
		$sql_where = '';
	}
	else
	{
		$sql_where = ' WHERE left_id > ' . $root_data['left_id'] . ' AND left_id < ' . $root_data['right_id'];
	}

	// Display list of active topics for this category?
	$show_active = (isset($root_data['forum_flags']) && $root_data['forum_flags'] & 16) ? true : false;

	if ($config['load_db_track'] && $user->data['is_registered'])
	{
		$sql_from = FORUMS_TABLE . ' f LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.user_id = ' . $user->data['user_id'] . ' AND ft.forum_id = f.forum_id)';
		$lastread_select = ', ft.mark_time ';
	}
	else
	{
		$sql_from = FORUMS_TABLE . ' f ';
		$lastread_select = $sql_lastread = '';

		$tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? unserialize(stripslashes($_COOKIE[$config['cookie_name'] . '_track'])) : array();

		if (!$user->data['is_registered'])
		{
			$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate'] : 0;
		}
	}

	$sql = "SELECT f.* $lastread_select
		FROM $sql_from
		$sql_where
		ORDER BY f.left_id";
	$result = $db->sql_query($sql);

	$forum_tracking_info = array();
	$branch_root_id = $root_data['forum_id'];
	while ($row = $db->sql_fetchrow($result))
	{
		$forum_id = $row['forum_id'];

		// Mark forums read?
		if ($mark_read == 'forums' || $mark_read == 'all')
		{
			if ($auth->acl_get('f_list', $forum_id))
			{
				$forum_ids[] = $forum_id;
				continue;
			}
		}

		// Category with no members
		if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
		{
			continue;
		}

		// Skip branch
		if (isset($right_id))
		{
			if ($row['left_id'] < $right_id)
			{
				continue;
			}
			unset($right_id);
		}

		if (!$auth->acl_get('f_list', $forum_id))
		{
			// if the user does not have permissions to list this forum, skip everything until next branch
			$right_id = $row['right_id'];
			continue;
		}
		
		$forum_ids[] = $forum_id;

		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			$forum_tracking_info[$forum_id] = (!empty($row['mark_time'])) ? $row['mark_time'] : $user->data['user_lastmark'];
		}
		else
		{
			$forum_tracking_info[$forum_id] = (isset($tracking_topics['f'][$forum_id])) ? base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate'] : $user->data['user_lastmark'];
		}

		// Display active topics from this forum?
		if ($show_active && $row['forum_type'] == FORUM_POST && $auth->acl_get('f_read', $forum_id) && ($row['forum_flags'] & 16))
		{
			$active_forum_ary['forum_id'][]		= $forum_id;
			$active_forum_ary['enable_icons'][] = $row['enable_icons'];
			$active_forum_ary['forum_topics']	+= ($auth->acl_get('m_approve', $forum_id)) ? $row['forum_topics_real'] : $row['forum_topics'];
			$active_forum_ary['forum_posts']	+= $row['forum_posts'];
		}

		//
		if ($row['parent_id'] == $root_data['forum_id'] || $row['parent_id'] == $branch_root_id)
		{
			// Direct child of current branch
			$parent_id = $forum_id;
			$forum_rows[$forum_id] = $row;

			if (!$row['parent_id'] && $row['forum_type'] == FORUM_CAT && $row['parent_id'] == $root_data['forum_id'])
			{
				$branch_root_id = $forum_id;
			}
			$forum_rows[$parent_id]['forum_id_last_post'] = $row['forum_id'];
			$forum_rows[$parent_id]['orig_forum_last_post_time'] = $row['forum_last_post_time'];
		}
		else if ($row['forum_type'] != FORUM_CAT)
		{
			$subforums[$parent_id][$forum_id]['display'] = ($row['display_on_index']) ? true : false;
			$subforums[$parent_id][$forum_id]['name'] = $row['forum_name'];
			$subforums[$parent_id][$forum_id]['orig_forum_last_post_time'] = $row['forum_last_post_time'];
			
			$forum_rows[$parent_id]['forum_topics'] += ($auth->acl_get('m_approve', $forum_id)) ? $row['forum_topics_real'] : $row['forum_topics'];

			// Do not list redirects in LINK Forums as Posts.
			if ($row['forum_type'] != FORUM_LINK)
			{
				$forum_rows[$parent_id]['forum_posts'] += $row['forum_posts'];
			}

			if ($row['forum_last_post_time'] > $forum_rows[$parent_id]['forum_last_post_time'])
			{
				$forum_rows[$parent_id]['forum_last_post_id'] = $row['forum_last_post_id'];
				$forum_rows[$parent_id]['forum_last_post_time'] = $row['forum_last_post_time'];
				$forum_rows[$parent_id]['forum_last_poster_id'] = $row['forum_last_poster_id'];
				$forum_rows[$parent_id]['forum_last_poster_name'] = $row['forum_last_poster_name'];
				$forum_rows[$parent_id]['forum_id_last_post'] = $forum_id;
			}
			else
			{
				$forum_rows[$parent_id]['forum_id_last_post'] = $forum_id;
			}
		}

		$forum_ids_moderator[$parent_id] = $forum_rows[$parent_id]['forum_id_last_post'];

	}
	$db->sql_freeresult($result);

	// Handle marking posts
	if ($mark_read == 'forums' || $mark_read == 'all')
	{
		$redirect = (!empty($_SERVER['REQUEST_URI'])) ? preg_replace('#^(.*?)&(amp;)?mark=.*$#', '\1', htmlspecialchars($_SERVER['REQUEST_URI'])) : "index.$phpEx$SID";

		if ($mark_read == 'all')
		{
			markread('all');

			$message = sprintf($user->lang['RETURN_INDEX'], '<a href="' . $redirect . '">', '</a>');
		}
		else
		{
			markread('topics', $forum_ids);

			$message = sprintf($user->lang['RETURN_FORUM'], '<a href="' . $redirect . '">', '</a>');
		}
		
		meta_refresh(3, $redirect);
		
		$message = $user->lang['FORUMS_MARKED'] . '<br /><br />' . $message;
		trigger_error($message);
	}

	// Grab moderators ... if necessary
	if ($display_moderators)
	{
		if ($return_moderators)
		{
			$forum_ids_moderator[] = $root_data['forum_id'];
		}
		get_moderators($forum_moderators, $forum_ids_moderator);
	}

	foreach ($forum_rows as $row)
	{
		// Empty category
		if (!$row['parent_id'] && $row['forum_type'] == FORUM_CAT)
		{
			$template->assign_block_vars('forumrow', array(
				'S_IS_CAT'			=>	true,
				'FORUM_ID'			=>	$row['forum_id'],
				'FORUM_NAME'		=>	$row['forum_name'],
				'FORUM_DESC'		=>	$row['forum_desc'],
				'U_VIEWFORUM'		=>	"{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=" . $row['forum_id'])
			);

			continue;
		}

		$visible_forums++;
		$forum_id = $row['forum_id'];

		$forum_unread = (isset($forum_tracking_info[$forum_id]) && $row['orig_forum_last_post_time'] > $forum_tracking_info[$forum_id]) ? true : false;

		$folder_image = $folder_alt = $subforums_list = $l_subforums = '';

		// Generate list of subforums if we need to
		if (isset($subforums[$forum_id]))
		{
			foreach ($subforums[$forum_id] as $subforum_id => $subforum_row)
			{
				// Update unread information if needed
				if (!$forum_unread)
				{
					$forum_unread = (isset($forum_tracking_info[$subforum_id]) && $subforum_row['orig_forum_last_post_time'] > $forum_tracking_info[$subforum_id]) ? true : false;
				}

				if ($subforum_row['display'] && $subforum_row['name'])
				{
					$subforums_list .= ($subforums_list == '') ? '' : ', ';
					$subforums_list .= '<a href="' . $phpbb_root_path . "viewforum.$phpEx$SID&amp;f=$subforum_id\">{$subforum_row['name']}</a>";
				}
				else
				{
					unset($subforums[$forum_id][$subforum_id]);
				}
			}
			
			$l_subforums = (sizeof($subforums[$forum_id]) == 1) ? $user->lang['SUBFORUM'] . ': ' : $user->lang['SUBFORUMS'] . ': ';
			$folder_image = ($forum_unread) ? 'sub_forum_new' : 'sub_forum';
		}
		else
		{
			switch ($row['forum_type'])
			{
				case FORUM_POST:
					$folder_image = ($forum_unread) ? 'forum_new' : 'forum';
				break;

				case FORUM_LINK:
					$folder_image = 'forum_link';
				break;
			}
		}

		// Which folder should we display?
		if ($row['forum_status'] == ITEM_LOCKED)
		{
			$folder_image = 'forum_locked';
			$folder_alt = 'FORUM_LOCKED';
		}
		else
		{
			$folder_alt = ($forum_unread) ? 'NEW_POSTS' : 'NO_NEW_POSTS';
		}

		// Create last post link information, if appropriate
		if ($row['forum_last_post_id'])
		{
			$last_post_time = $user->format_date($row['forum_last_post_time']);

			$last_poster = ($row['forum_last_poster_name'] != '') ? $row['forum_last_poster_name'] : $user->lang['GUEST'];
			$last_poster_url = ($row['forum_last_poster_id'] == ANONYMOUS) ? '' : "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u={$row['forum_last_poster_id']}";

			$last_post_url = "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f=" . $row['forum_id_last_post'] . '&amp;p=' . $row['forum_last_post_id'] . '#' . $row['forum_last_post_id'];
		}
		else
		{
			$last_post_time = $last_poster = $last_poster_url = $last_post_url = '';
		}

		// Output moderator listing ... if applicable
		$l_moderator = $moderators_list = '';
		if ($display_moderators && !empty($forum_moderators[$forum_id]))
		{
			$l_moderator = (sizeof($forum_moderators[$forum_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
			$moderators_list = implode(', ', $forum_moderators[$forum_id]);
		}

		$l_post_click_count = ($row['forum_type'] == FORUM_LINK) ? 'CLICKS' : 'POSTS';
		$post_click_count = ($row['forum_type'] != FORUM_LINK || $row['forum_flags'] & 1) ? $row['forum_posts'] : '';

		$template->assign_block_vars('forumrow', array(
			'S_IS_CAT'			=> false,
			'S_IS_LINK'			=> ($row['forum_type'] == FORUM_LINK) ? true : false,

			'FORUM_ID'				=> $row['forum_id'],
			'FORUM_NAME'			=> $row['forum_name'],
			'FORUM_DESC'			=> $row['forum_desc'],
			'TOPICS'				=> $row['forum_topics'],
			$l_post_click_count		=> $post_click_count,
			'FORUM_FOLDER_IMG'		=> ($row['forum_image']) ? '<img src="' . $phpbb_root_path . $row['forum_image'] . '" alt="' . $user->lang['folder_alt'] . '" />' : $user->img($folder_image, $folder_alt),
			'FORUM_FOLDER_IMG_SRC'	=> ($row['forum_image']) ? $phpbb_root_path . $row['forum_image'] : $user->img($folder_image, $folder_alt, false, '', 'src'),
			'SUBFORUMS'				=> $subforums_list,
			'LAST_POST_TIME'		=> $last_post_time,
			'LAST_POSTER'			=> $last_poster,
			'MODERATORS'			=> $moderators_list,

			'L_SUBFORUM_STR'	=> $l_subforums,
			'L_FORUM_FOLDER_ALT'=> $folder_alt,
			'L_MODERATOR_STR'	=> $l_moderator,

			'U_VIEWFORUM'		=> ($row['forum_type'] != FORUM_LINK || $row['forum_flags'] & 1) ? "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f={$row['forum_id']}" : $row['forum_link'],
			'U_LAST_POSTER'		=> $last_poster_url,
			'U_LAST_POST'		=> $last_post_url,
			)
		);
	}

	$template->assign_vars(array(
		'U_MARK_FORUMS'		=> "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=" . $root_data['forum_id'] . '&amp;mark=forums',
		'S_HAS_SUBFORUM'	=> ($visible_forums) ? true : false,
		'L_SUBFORUM'		=> ($visible_forums == 1) ? $user->lang['SUBFORUM'] : $user->lang['SUBFORUMS'],
		'LAST_POST_IMG'		=> $user->img('icon_post_latest', 'VIEW_LATEST_POST'),
		)
	);

	if ($return_moderators)
	{
		return array($active_forum_ary, $forum_moderators);
	}

	return $active_forum_ary;
}

/**
* Create forum rules for given forum
*/
function generate_forum_rules(&$forum_data)
{
	if (!$forum_data['forum_rules'] && !$forum_data['forum_rules_link'])
	{
		return;
	}

	global $template, $phpbb_root_path, $phpEx;

	if ($forum_data['forum_rules'])
	{
		include_once($phpbb_root_path . 'includes/bbcode.' . $phpEx);
		$bbcode = new bbcode($forum_data['forum_rules_bbcode_bitfield']);

		$bbcode->bbcode_second_pass($forum_data['forum_rules'], $forum_data['forum_rules_bbcode_uid']);

		$forum_data['forum_rules'] = smiley_text($forum_data['forum_rules'], !($forum_data['forum_rules_flags'] & 2));
		$forum_data['forum_rules'] = str_replace("\n", '<br />', censor_text($forum_data['forum_rules']));
		unset($bbcode);
	}

	$template->assign_vars(array(
		'S_FORUM_RULES'	=> true,
		'U_FORUM_RULES'	=> $forum_data['forum_rules_link'],
		'FORUM_RULES'	=> $forum_data['forum_rules'])
	);
}

/**
* Create forum navigation links for given forum, create parent
* list if currently null, assign basic forum info to template
*/
function generate_forum_nav(&$forum_data)
{
	global $db, $user, $template, $phpEx, $SID, $phpbb_root_path;

	// Get forum parents
	$forum_parents = get_forum_parents($forum_data);

	// Build navigation links
	foreach ($forum_parents as $parent_forum_id => $parent_data)
	{
		list($parent_name, $parent_type) = array_values($parent_data);

		$template->assign_block_vars('navlinks', array(
			'S_IS_CAT'		=> ($parent_type == FORUM_CAT) ? true : false,
			'S_IS_LINK'		=> ($parent_type == FORUM_LINK) ? true : false,
			'S_IS_POST'		=> ($parent_type == FORUM_POST) ? true : false,
			'FORUM_NAME'	=> $parent_name,
			'FORUM_ID'		=> $parent_forum_id,
			'U_VIEW_FORUM'	=> "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=$parent_forum_id")
		);
	}

	$template->assign_block_vars('navlinks', array(
		'S_IS_CAT'		=> ($forum_data['forum_type'] == FORUM_CAT) ? true : false,
		'S_IS_LINK'		=> ($forum_data['forum_type'] == FORUM_LINK) ? true : false,
		'S_IS_POST'		=> ($forum_data['forum_type'] == FORUM_POST) ? true : false,
		'FORUM_NAME'	=> $forum_data['forum_name'],
		'FORUM_ID'		=> $forum_data['forum_id'],
		'U_VIEW_FORUM'	=> "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=" . $forum_data['forum_id'])
	);

	$template->assign_vars(array(
		'FORUM_ID' 		=> $forum_data['forum_id'],
		'FORUM_NAME'	=> $forum_data['forum_name'],
		'FORUM_DESC'	=> strip_tags($forum_data['forum_desc']))
	);

	return;
}

/**
* Returns forum parents as an array. Get them from forum_data if available, or update the database otherwise
*/
function get_forum_parents(&$forum_data)
{
	global $db;

	$forum_parents = array();

	if ($forum_data['parent_id'] > 0)
	{
		if ($forum_data['forum_parents'] == '')
		{
			$sql = 'SELECT forum_id, forum_name, forum_type
				FROM ' . FORUMS_TABLE . '
				WHERE left_id < ' . $forum_data['left_id'] . '
					AND right_id > ' . $forum_data['right_id'] . '
				ORDER BY left_id ASC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$forum_parents[$row['forum_id']] = array($row['forum_name'], (int) $row['forum_type']);
			}
			$db->sql_freeresult($result);

			$forum_data['forum_parents'] = serialize($forum_parents);

			$sql = 'UPDATE ' . FORUMS_TABLE . "
				SET forum_parents = '" . $db->sql_escape($forum_data['forum_parents']) . "'
				WHERE parent_id = " . $forum_data['parent_id'];
			$db->sql_query($sql);
		}
		else
		{
			$forum_parents = unserialize($forum_data['forum_parents']);
		}
	}

	return $forum_parents;
}

/**
* Get topic author
*/
function topic_topic_author(&$topic_row)
{
	global $phpEx, $SID, $phpbb_root_path, $user;

	$topic_author = ($topic_row['topic_poster'] != ANONYMOUS) ? "<a href=\"{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $topic_row['topic_poster'] . '">' : '';
	$topic_author .= ($topic_row['topic_poster'] != ANONYMOUS) ? $topic_row['topic_first_poster_name'] : (($topic_row['topic_first_poster_name'] != '') ? $topic_row['topic_first_poster_name'] : $user->lang['GUEST']);
	$topic_author .= ($topic_row['topic_poster'] != ANONYMOUS) ? '</a>' : '';

	return $topic_author;
}

/**
* Generate topic pagination
*/
function topic_generate_pagination($replies, $url)
{
	global $config, $user;

	if (($replies + 1) > $config['posts_per_page'])
	{
		$total_pages = ceil(($replies + 1) / $config['posts_per_page']);
		$pagination = '';

		$times = 1;
		for ($j = 0; $j < $replies + 1; $j += $config['posts_per_page'])
		{
			$pagination .= "<a href=\"$url&amp;start=$j\">$times</a>";
			if ($times == 1 && $total_pages > 4)
			{
				$pagination .= ' ... ';
				$times = $total_pages - 3;
				$j += ($total_pages - 4) * $config['posts_per_page'];
			}
			else if ($times < $total_pages)
			{
				$pagination .= $user->theme['primary']['pagination_sep'];
			}
			$times++;
		}
	}
	else
	{
		$pagination = '';
	}

	return $pagination;
}

/**
* Obtain list of moderators of each forum
*/
function get_moderators(&$forum_moderators, $forum_id = false)
{
	global $config, $template, $db, $phpbb_root_path, $phpEx, $SID;

	// Have we disabled the display of moderators? If so, then return
	// from whence we came ...
	if (!$config['load_moderators'])
	{
		return;
	}

	if ($forum_id !== false && is_array($forum_id))
	{
		$forum_sql = 'AND forum_id IN (' . implode(', ', $forum_id) . ')';
	}
	else
	{
		$forum_sql = ($forum_id !== false) ? 'AND forum_id = ' . $forum_id : '';
	}

	$sql = 'SELECT *
		FROM ' . MODERATOR_TABLE . "
		WHERE display_on_index = 1
			$forum_sql";
	$result = $db->sql_query($sql, 3600);

	while ($row = $db->sql_fetchrow($result))
	{
		$forum_moderators[$row['forum_id']][] = (!empty($row['user_id'])) ? '<a href="' . $phpbb_root_path . "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id'] . '">' . $row['username'] . '</a>' : '<a href="' . $phpbb_root_path . "memberlist.$phpEx$SID&amp;mode=group&amp;g=" . $row['group_id'] . '">' . $row['groupname'] . '</a>';
	}
	$db->sql_freeresult($result);

	return;
}

/**
* User authorisation levels output
*/
function gen_forum_auth_level($mode, $forum_id)
{
	global $SID, $template, $auth, $user;

	$rules = array(
		($auth->acl_get('f_post', $forum_id)) ? $user->lang['RULES_POST_CAN'] : $user->lang['RULES_POST_CANNOT'],
		($auth->acl_get('f_reply', $forum_id)) ? $user->lang['RULES_REPLY_CAN'] : $user->lang['RULES_REPLY_CANNOT'],
		($auth->acl_gets('f_edit', 'm_edit', $forum_id)) ? $user->lang['RULES_EDIT_CAN'] : $user->lang['RULES_EDIT_CANNOT'],
		($auth->acl_gets('f_delete', 'm_delete', $forum_id)) ? $user->lang['RULES_DELETE_CAN'] : $user->lang['RULES_DELETE_CANNOT'],
		($auth->acl_get('f_attach', $forum_id) && $auth->acl_get('u_attach', $forum_id)) ? $user->lang['RULES_ATTACH_CAN'] : $user->lang['RULES_ATTACH_CANNOT']
	);

	foreach ($rules as $rule)
	{
		$template->assign_block_vars('rules', array('RULE' => $rule));
	}

	return;
}

/**
* Generate topic status
*/
function topic_status(&$topic_row, $replies, $unread_topic, &$folder_img, &$folder_alt, &$topic_type)
{
	global $user, $config;

	$folder = $folder_new = '';

	if ($topic_row['topic_status'] == ITEM_MOVED)
	{
		$topic_type = $user->lang['VIEW_TOPIC_MOVED'];
		$folder_img = 'folder_moved';
		$folder_alt = 'VIEW_TOPIC_MOVED';
	}
	else
	{
		switch ($topic_row['topic_type'])
		{
			case POST_GLOBAL:
			case POST_ANNOUNCE:
				$topic_type = $user->lang['VIEW_TOPIC_ANNOUNCEMENT'];
				$folder = 'folder_announce';
				$folder_new = 'folder_announce_new';
				break;

			case POST_STICKY:
				$topic_type = $user->lang['VIEW_TOPIC_STICKY'];
				$folder = 'folder_sticky';
				$folder_new = 'folder_sticky_new';
				break;

			default:
				if ($replies >= $config['hot_threshold'])
				{
					$folder = 'folder_hot';
					$folder_new = 'folder_hot_new';
				}
				else
				{
					$folder = 'folder';
					$folder_new = 'folder_new';
				}
				break;
		}

		if ($topic_row['topic_status'] == ITEM_LOCKED)
		{
			$topic_type = $user->lang['VIEW_TOPIC_LOCKED'];
			$folder = 'folder_locked';
			$folder_new = 'folder_locked_new';
		}

		$folder_img = ($unread_topic) ? $folder_new : $folder;
		$folder_alt = ($unread_topic) ? 'NEW_POSTS' : (($topic_row['topic_status'] == ITEM_LOCKED) ? 'TOPIC_LOCKED' : 'NO_NEW_POSTS');

		// Posted image?
		if (!empty($topic_row['topic_posted']) && $topic_row['topic_posted'])
		{
			$folder_img .= '_posted';
		}
	}

	if ($topic_row['poll_start'])
	{
		$topic_type .= $user->lang['VIEW_TOPIC_POLL'];
	}
}

/**
* Display Attachments
*/
function display_attachments($forum_id, $blockname, &$attachment_data, &$update_count, $force_physical = false, $return = false)
{
	global $template, $cache, $user;
	global $attachment_tpl, $extensions, $config, $phpbb_root_path, $phpEx, $SID;

//	$starttime = explode(' ', microtime());
//	$starttime = $starttime[1] + $starttime[0];
	$return_tpl = array();

	$blocks = array(ATTACHMENT_CATEGORY_WM => 'WM_STREAM', ATTACHMENT_CATEGORY_RM => 'RM_STREAM', ATTACHMENT_CATEGORY_THUMB => 'THUMBNAIL', ATTACHMENT_CATEGORY_IMAGE => 'IMAGE');

	if (!isset($attachment_tpl))
	{
		if ($cache->exists('attachment_tpl'))
		{
			$attachment_tpl = $cache->get('attachment_tpl');
		}
		else
		{
			$attachment_tpl = array();

			// Generate Template
			$style = 'primary';

			if (!empty($user->theme['secondary']))
			{
				$style = (file_exists($phpbb_root_path . 'styles/' . $user->theme['primary']['template_path'] . '/template/attachment.html')) ? 'primary' : 'secondary';
			}

			$template_filename = $phpbb_root_path . 'styles/' . $user->theme[$style]['template_path'] . '/template/attachment.html';
			if (!($fp = @fopen($template_filename, 'rb')))
			{
				trigger_error('Could not load template file "' . $template_filename . '"');
			}
			$attachment_template = fread($fp, filesize($template_filename));
			@fclose($fp);

			// replace \ with \\ and then ' with \'.
			$attachment_template = str_replace('\\', '\\\\', $attachment_template);
			$attachment_template = str_replace("'", "\'", $attachment_template);

			preg_match_all('#<!-- BEGIN (.*?) -->(.*?)<!-- END (.*?) -->#s', $attachment_template, $tpl);

			foreach ($tpl[1] as $num => $block_name)
			{
				$attachment_tpl[$block_name] = $tpl[2][$num];
			}
			unset($tpl);

			$cache->put('attachment_tpl', $attachment_tpl);
		}
	}

	if (empty($extensions) || !is_array($extensions))
	{
		$extensions = array();
		$cache->obtain_attach_extensions($extensions);
	}

	foreach ($attachment_data as $attachment)
	{
		// Some basics...
		$attachment['extension'] = strtolower(trim($attachment['extension']));
		$filename = $phpbb_root_path . $config['upload_path'] . '/' . basename($attachment['physical_filename']);
		$thumbnail_filename = $phpbb_root_path . $config['upload_path'] . '/thumb_' . basename($attachment['physical_filename']);

		$upload_image = '';

		if ($user->img('icon_attach', '') && !$extensions[$attachment['extension']]['upload_icon'])
		{
			$upload_image = $user->img('icon_attach', '');
		}
		else if ($extensions[$attachment['extension']]['upload_icon'])
		{
			$upload_image = '<img src="' . $phpbb_root_path . $config['upload_icons_path'] . '/' . trim($extensions[$attachment['extension']]['upload_icon']) . '" alt="" border="0" />';
		}

		$filesize = $attachment['filesize'];
		$size_lang = ($filesize >= 1048576) ? $user->lang['MB'] : ( ($filesize >= 1024) ? $user->lang['KB'] : $user->lang['BYTES'] );

		$filesize = ($filesize >= 1048576) ? round((round($filesize / 1048576 * 100) / 100), 2) : (($filesize >= 1024) ? round((round($filesize / 1024 * 100) / 100), 2) : $filesize);

		$display_name = basename($attachment['real_filename']);
		$comment = str_replace("\n", '<br />', censor_text($attachment['comment']));

		$denied = false;

		if (!extension_allowed($forum_id, $attachment['extension'], $extensions))
		{
			$denied = true;

			$template_array['VAR'] = array('{L_DENIED}');
			$template_array['VAL'] = array(sprintf($user->lang['EXTENSION_DISABLED_AFTER_POSTING'], $attachment['extension']));

			$tpl = str_replace($template_array['VAR'], $template_array['VAL'], $attachment_tpl['DENIED']);

			// Replace {L_*} lang strings
			$tpl = preg_replace('/{L_([A-Z_]+)}/e', "(!empty(\$user->lang['\$1'])) ? \$user->lang['\$1'] : ucwords(strtolower(str_replace('_', ' ', '\$1')))", $tpl);

			if (!$return)
			{
				$template->assign_block_vars($blockname, array(
					'DISPLAY_ATTACHMENT' => $tpl)
				);
			}
			else
			{
				$return_tpl[] = $tpl;
			}
		}

		if (!$denied)
		{
			$l_downloaded_viewed = '';
			$download_link = '';
			$additional_array['VAR'] = $additional_array['VAL'] = array();

			$display_cat = $extensions[$attachment['extension']]['display_cat'];

			if ($display_cat == ATTACHMENT_CATEGORY_IMAGE)
			{
				if ($attachment['thumbnail'])
				{
					$display_cat = ATTACHMENT_CATEGORY_THUMB;
				}
				else
				{
					if ($config['img_display_inlined'])
					{
						if ($config['img_link_width'] || $config['img_link_height'])
						{
							list($width, $height) = getimagesize($filename);

							$display_cat = (!$width && !$height) ? ATTACHMENT_CATEGORY_IMAGE : (($width <= $config['img_link_width'] && $height <= $config['img_link_height']) ? ATTACHMENT_CATEGORY_IMAGE : ATTACHMENT_CATEGORY_NONE);
						}
					}
					else
					{
						$display_cat = ATTACHMENT_CATEGORY_NONE;
					}
				}
			}

			switch ($display_cat)
			{
				// Images
				case ATTACHMENT_CATEGORY_IMAGE:
					$img_source = $filename;
					$update_count[] = $attachment['attach_id'];

					$l_downloaded_viewed = $user->lang['VIEWED'];
					$download_link = $img_source;
					break;

				// Images, but display Thumbnail
				case ATTACHMENT_CATEGORY_THUMB:
					$thumb_source = $thumbnail_filename;

					$l_downloaded_viewed = $user->lang['VIEWED'];
					$download_link = (!$force_physical) ? $phpbb_root_path . "download.$phpEx$SID&amp;id=" . $attachment['attach_id'] : $filename;

					$additional_array['VAR'][] = '{THUMB_IMG}';
					$additional_array['VAL'][] = $thumb_source;
					break;

				// Windows Media Streams
				case ATTACHMENT_CATEGORY_WM:
					$l_downloaded_viewed = $user->lang['VIEWED'];
					$download_link = $filename;

					// Viewed/Heared File ... update the download count (download.php is not called here)
					$update_count[] = $attachment['attach_id'];
					break;

				// Real Media Streams
				case ATTACHMENT_CATEGORY_RM:
					$l_downloaded_viewed = $user->lang['VIEWED'];
					$download_link = $filename;

					$additional_array['VAR'][] = '{U_FORUM}';
					$additional_array['VAL'][] = generate_board_url();
					$additional_array['VAR'][] = '{ATTACH_ID}';
					$additional_array['VAL'][] = $attachment['attach_id'];

					// Viewed/Heared File ... update the download count (download.php is not called here)
					$update_count[] = $attachment['attach_id'];
					break;
/*
				// Macromedia Flash Files
				case SWF_CAT:
					list($width, $height) = swf_getdimension($filename);

					$l_downloaded_viewed = $user->lang['VIEWED'];
					$download_link = $filename;

					$additional_array = array(
						'WIDTH' => $width,
						'HEIGHT' => $height
					);

					// Viewed/Heared File ... update the download count (download.php is not called here)
					$update_count[] = $attachment['attach_id'];
					break;
*/
				default:
					$l_downloaded_viewed = $user->lang['DOWNLOADED'];
					$download_link = (!$force_physical) ? $phpbb_root_path . "download.$phpEx$SID&amp;id=" . $attachment['attach_id'] : $filename;
					break;
			}

			$l_download_count = (!isset($attachment['download_count']) || $attachment['download_count'] == 0) ? $user->lang['DOWNLOAD_NONE'] : (($attachment['download_count'] == 1) ? sprintf($user->lang['DOWNLOAD_COUNT'], $attachment['download_count']) : sprintf($user->lang['DOWNLOAD_COUNTS'], $attachment['download_count']));

			$current_block = ($display_cat) ? $blocks[$display_cat] : 'FILE';

			$template_array['VAR'] = array_merge($additional_array['VAR'], array(
				'{DOWNLOAD_NAME}', '{FILESIZE}', '{SIZE_VAR}', '{COMMENT}', '{U_DOWNLOAD_LINK}', '{UPLOAD_IMG}', '{L_DOWNLOADED_VIEWED}', '{L_DOWNLOAD_COUNT}')
			);

			$template_array['VAL'] = array_merge($additional_array['VAL'], array(
				$display_name, $filesize, $size_lang, $comment, $download_link, $upload_image, $l_downloaded_viewed, $l_download_count)
			);

			$tpl = str_replace($template_array['VAR'], $template_array['VAL'], $attachment_tpl[$current_block]);

			// Replace {L_*} lang strings
			$tpl = preg_replace('/{L_([A-Z_]+)}/e', "(!empty(\$user->lang['\$1'])) ? \$user->lang['\$1'] : ucwords(strtolower(str_replace('_', ' ', '\$1')))", $tpl);

			if (!$return)
			{
				$template->assign_block_vars($blockname, array(
					'DISPLAY_ATTACHMENT' => $tpl)
				);
			}
			else
			{
				$return_tpl[] = $tpl;
			}
		}
	}

	return $return_tpl;
//	$mtime = explode(' ', microtime());
//	$totaltime = $mtime[0] + $mtime[1] - $starttime;
}

?>