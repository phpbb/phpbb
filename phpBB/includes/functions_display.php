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
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Obtain list of moderators of each forum
*/
function get_moderators(&$forum_moderators, $forum_id = false)
{
	global $db, $phpbb_root_path, $phpEx, $user, $auth, $phpbb_container;

	$forum_id_ary = array();

	if ($forum_id !== false)
	{
		if (!is_array($forum_id))
		{
			$forum_id = array($forum_id);
		}

		// Exchange key/value pair to be able to faster check for the forum id existence
		$forum_id_ary = array_flip($forum_id);
	}

	$sql_array = array(
		'SELECT'	=> 'm.*, u.user_colour, g.group_colour, g.group_type',

		'FROM'		=> array(
			MODERATOR_CACHE_TABLE	=> 'm',
		),

		'LEFT_JOIN'	=> array(
			array(
				'FROM'	=> array(USERS_TABLE => 'u'),
				'ON'	=> 'm.user_id = u.user_id',
			),
			array(
				'FROM'	=> array(GROUPS_TABLE => 'g'),
				'ON'	=> 'm.group_id = g.group_id',
			),
		),

		'WHERE'		=> 'm.display_on_index = 1',
	);

	/** @var \phpbb\group\helper $group_helper */
	$group_helper = $phpbb_container->get('group_helper');

	// We query every forum here because for caching we should not have any parameter.
	$sql = $db->sql_build_query('SELECT', $sql_array);
	$result = $db->sql_query($sql, 3600);

	while ($row = $db->sql_fetchrow($result))
	{
		$f_id = (int) $row['forum_id'];

		if (!isset($forum_id_ary[$f_id]))
		{
			continue;
		}

		if (!empty($row['user_id']))
		{
			$forum_moderators[$f_id][] = get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);
		}
		else
		{
			$group_name = $group_helper->get_name($row['group_name']);

			if ($user->data['user_id'] != ANONYMOUS && !$auth->acl_get('u_viewprofile'))
			{
				$forum_moderators[$f_id][] = '<span' . (($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . ';"' : '') . '>' . $group_name . '</span>';
			}
			else
			{
				$forum_moderators[$f_id][] = '<a' . (($row['group_colour']) ? ' style="color:#' . $row['group_colour'] . ';"' : '') . ' href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $row['group_id']) . '">' . $group_name . '</a>';
			}
		}
	}
	$db->sql_freeresult($result);

	return;
}

/**
* User authorisation levels output
*
* @param	string	$mode			Can be forum or topic. Not in use at the moment.
* @param	int		$forum_id		The current forum the user is in.
* @param	int		$forum_status	The forums status bit.
*/
function gen_forum_auth_level($mode, $forum_id, $forum_status)
{
	global $template, $auth, $user, $config;

	$locked = ($forum_status == ITEM_LOCKED && !$auth->acl_get('m_edit', $forum_id)) ? true : false;

	$rules = array(
		($auth->acl_get('f_post', $forum_id) && !$locked) ? $user->lang['RULES_POST_CAN'] : $user->lang['RULES_POST_CANNOT'],
		($auth->acl_get('f_reply', $forum_id) && !$locked) ? $user->lang['RULES_REPLY_CAN'] : $user->lang['RULES_REPLY_CANNOT'],
		($user->data['is_registered'] && $auth->acl_gets('f_edit', 'm_edit', $forum_id) && !$locked) ? $user->lang['RULES_EDIT_CAN'] : $user->lang['RULES_EDIT_CANNOT'],
		($user->data['is_registered'] && ($auth->acl_gets('f_delete', 'm_delete', $forum_id) || $auth->acl_gets('f_softdelete', 'm_softdelete', $forum_id)) && !$locked) ? $user->lang['RULES_DELETE_CAN'] : $user->lang['RULES_DELETE_CANNOT'],
	);

	if ($config['allow_attachments'])
	{
		$rules[] = ($auth->acl_get('f_attach', $forum_id) && $auth->acl_get('u_attach') && !$locked) ? $user->lang['RULES_ATTACH_CAN'] : $user->lang['RULES_ATTACH_CANNOT'];
	}

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

	if ($topic_row['topic_status'] == ITEM_MOVED)
	{
		$topic_type = $user->lang['VIEW_TOPIC_MOVED'];
		$folder_img = 'topic_moved';
		$folder_alt = 'TOPIC_MOVED';
	}
	else
	{
		switch ($topic_row['topic_type'])
		{
			case POST_GLOBAL:
				$topic_type = $user->lang['VIEW_TOPIC_GLOBAL'];
				$folder = 'global_read';
				$folder_new = 'global_unread';
			break;

			case POST_ANNOUNCE:
				$topic_type = $user->lang['VIEW_TOPIC_ANNOUNCEMENT'];
				$folder = 'announce_read';
				$folder_new = 'announce_unread';
			break;

			case POST_STICKY:
				$topic_type = $user->lang['VIEW_TOPIC_STICKY'];
				$folder = 'sticky_read';
				$folder_new = 'sticky_unread';
			break;

			default:
				$topic_type = '';
				$folder = 'topic_read';
				$folder_new = 'topic_unread';

				// Hot topic threshold is for posts in a topic, which is replies + the first post. ;)
				if ($config['hot_threshold'] && ($replies + 1) >= $config['hot_threshold'] && $topic_row['topic_status'] != ITEM_LOCKED)
				{
					$folder .= '_hot';
					$folder_new .= '_hot';
				}
			break;
		}

		if ($topic_row['topic_status'] == ITEM_LOCKED)
		{
			$topic_type = $user->lang['VIEW_TOPIC_LOCKED'];
			$folder .= '_locked';
			$folder_new .= '_locked';
		}

		$folder_img = ($unread_topic) ? $folder_new : $folder;
		$folder_alt = ($unread_topic) ? 'UNREAD_POSTS' : (($topic_row['topic_status'] == ITEM_LOCKED) ? 'TOPIC_LOCKED' : 'NO_UNREAD_POSTS');

		// Posted image?
		if (!empty($topic_row['topic_posted']) && $topic_row['topic_posted'])
		{
			$folder_img .= '_mine';
		}
	}

	if ($topic_row['poll_start'] && $topic_row['topic_status'] != ITEM_MOVED)
	{
		$topic_type = $user->lang['VIEW_TOPIC_POLL'];
	}
}

/**
* Assign/Build custom bbcodes for display in screens supporting using of bbcodes
* The custom bbcodes buttons will be placed within the template block 'custom_tags'
*/
function display_custom_bbcodes()
{
	global $db, $template, $user, $phpbb_dispatcher;

	// Start counting from 22 for the bbcode ids (every bbcode takes two ids - opening/closing)
	$num_predefined_bbcodes = NUM_PREDEFINED_BBCODES;

	$sql_ary = array(
		'SELECT'	=> 'b.bbcode_id, b.bbcode_tag, b.bbcode_helpline',
		'FROM'		=> array(BBCODES_TABLE => 'b'),
		'WHERE'		=> 'b.display_on_posting = 1',
		'ORDER_BY'	=> 'b.bbcode_tag',
	);

	/**
	* Event to modify the SQL query before custom bbcode data is queried
	*
	* @event core.display_custom_bbcodes_modify_sql
	* @var	array	sql_ary					The SQL array to get the bbcode data
	* @var	int		num_predefined_bbcodes	The number of predefined core bbcodes
	*										(multiplied by factor of 2)
	* @since 3.1.0-a3
	*/
	$vars = array('sql_ary', 'num_predefined_bbcodes');
	extract($phpbb_dispatcher->trigger_event('core.display_custom_bbcodes_modify_sql', compact($vars)));

	$result = $db->sql_query($db->sql_build_query('SELECT', $sql_ary));

	$i = 0;
	while ($row = $db->sql_fetchrow($result))
	{
		// If the helpline is defined within the language file, we will use the localised version, else just use the database entry...
		if (isset($user->lang[strtoupper($row['bbcode_helpline'])]))
		{
			$row['bbcode_helpline'] = $user->lang[strtoupper($row['bbcode_helpline'])];
		}

		$custom_tags = array(
			'BBCODE_NAME'		=> "'[{$row['bbcode_tag']}]', '[/" . str_replace('=', '', $row['bbcode_tag']) . "]'",
			'BBCODE_ID'			=> $num_predefined_bbcodes + ($i * 2),
			'BBCODE_TAG'		=> $row['bbcode_tag'],
			'BBCODE_TAG_CLEAN'	=> str_replace('=', '-', $row['bbcode_tag']),
			'BBCODE_HELPLINE'	=> $row['bbcode_helpline'],
			'A_BBCODE_HELPLINE'	=> str_replace(array('&amp;', '&quot;', "'", '&lt;', '&gt;'), array('&', '"', "\'", '<', '>'), $row['bbcode_helpline']),
		);

		/**
		* Event to modify the template data block of a custom bbcode
		*
		* This event is triggered once per bbcode
		*
		* @event core.display_custom_bbcodes_modify_row
		* @var	array	custom_tags		Template data of the bbcode
		* @var	array	row				The data of the bbcode
		* @since 3.1.0-a1
		*/
		$vars = array('custom_tags', 'row');
		extract($phpbb_dispatcher->trigger_event('core.display_custom_bbcodes_modify_row', compact($vars)));

		$template->assign_block_vars('custom_tags', $custom_tags);

		$i++;
	}
	$db->sql_freeresult($result);

	/**
	* Display custom bbcodes
	*
	* @event core.display_custom_bbcodes
	* @since 3.1.0-a1
	*/
	$phpbb_dispatcher->dispatch('core.display_custom_bbcodes');
}

/**
* Display user activity (action forum/topic)
*/
function display_user_activity(&$userdata_ary)
{
	global $auth, $template, $db, $user, $config;
	global $phpbb_root_path, $phpEx;
	global $phpbb_container, $phpbb_dispatcher;

	// Do not display user activity for users having too many posts...
	$limit = $config['load_user_activity_limit'];
	if ($userdata_ary['user_posts'] > $limit && $limit != 0)
	{
		return;
	}

	$forum_ary = array();

	$forum_read_ary = $auth->acl_getf('f_read');
	foreach ($forum_read_ary as $forum_id => $allowed)
	{
		if ($allowed['f_read'])
		{
			$forum_ary[] = (int) $forum_id;
		}
	}

	$forum_ary = array_diff($forum_ary, $user->get_passworded_forums());

	$active_f_row = $active_t_row = array();
	if (!empty($forum_ary))
	{
		/* @var $phpbb_content_visibility \phpbb\content_visibility */
		$phpbb_content_visibility = $phpbb_container->get('content.visibility');

		// Obtain active forum
		$sql = 'SELECT forum_id, COUNT(post_id) AS num_posts
			FROM ' . POSTS_TABLE . '
			WHERE poster_id = ' . $userdata_ary['user_id'] . '
				AND post_postcount = 1
				AND ' . $phpbb_content_visibility->get_forums_visibility_sql('post', $forum_ary) . '
			GROUP BY forum_id
			ORDER BY num_posts DESC';
		$result = $db->sql_query_limit($sql, 1);
		$active_f_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!empty($active_f_row))
		{
			$sql = 'SELECT forum_name
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . $active_f_row['forum_id'];
			$result = $db->sql_query($sql, 3600);
			$active_f_row['forum_name'] = (string) $db->sql_fetchfield('forum_name');
			$db->sql_freeresult($result);
		}

		// Obtain active topic
		$sql = 'SELECT topic_id, COUNT(post_id) AS num_posts
			FROM ' . POSTS_TABLE . '
			WHERE poster_id = ' . $userdata_ary['user_id'] . '
				AND post_postcount = 1
				AND ' . $phpbb_content_visibility->get_forums_visibility_sql('post', $forum_ary) . '
			GROUP BY topic_id
			ORDER BY num_posts DESC';
		$result = $db->sql_query_limit($sql, 1);
		$active_t_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!empty($active_t_row))
		{
			$sql = 'SELECT topic_title
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . $active_t_row['topic_id'];
			$result = $db->sql_query($sql);
			$active_t_row['topic_title'] = (string) $db->sql_fetchfield('topic_title');
			$db->sql_freeresult($result);
		}
	}

	$userdata = $userdata_ary;
	$show_user_activity = true;
	/**
	* Alter list of forums and topics to display as active
	*
	* @event core.display_user_activity_modify_actives
	* @var	array	userdata						User's data
	* @var	array	active_f_row					List of active forums
	* @var	array	active_t_row					List of active posts
	* @var	bool	show_user_activity				Show user forum and topic activity
	* @since 3.1.0-RC3
	* @changed 3.2.5-RC1 Added show_user_activity into event
	*/
	$vars = array('userdata', 'active_f_row', 'active_t_row', 'show_user_activity');
	extract($phpbb_dispatcher->trigger_event('core.display_user_activity_modify_actives', compact($vars)));
	$userdata_ary = $userdata;
	unset($userdata);

	$userdata_ary['active_t_row'] = $active_t_row;
	$userdata_ary['active_f_row'] = $active_f_row;

	$active_f_name = $active_f_id = $active_f_count = $active_f_pct = '';
	if (!empty($active_f_row['num_posts']))
	{
		$active_f_name = $active_f_row['forum_name'];
		$active_f_id = $active_f_row['forum_id'];
		$active_f_count = $active_f_row['num_posts'];
		$active_f_pct = ($userdata_ary['user_posts']) ? ($active_f_count / $userdata_ary['user_posts']) * 100 : 0;
	}

	$active_t_name = $active_t_id = $active_t_count = $active_t_pct = '';
	if (!empty($active_t_row['num_posts']))
	{
		$active_t_name = $active_t_row['topic_title'];
		$active_t_id = $active_t_row['topic_id'];
		$active_t_count = $active_t_row['num_posts'];
		$active_t_pct = ($userdata_ary['user_posts']) ? ($active_t_count / $userdata_ary['user_posts']) * 100 : 0;
	}

	$l_active_pct = ($userdata_ary['user_id'] != ANONYMOUS && $userdata_ary['user_id'] == $user->data['user_id']) ? $user->lang['POST_PCT_ACTIVE_OWN'] : $user->lang['POST_PCT_ACTIVE'];

	$template->assign_vars(array(
		'ACTIVE_FORUM'			=> $active_f_name,
		'ACTIVE_FORUM_POSTS'	=> $user->lang('USER_POSTS', (int) $active_f_count),
		'ACTIVE_FORUM_PCT'		=> sprintf($l_active_pct, $active_f_pct),
		'ACTIVE_TOPIC'			=> censor_text($active_t_name),
		'ACTIVE_TOPIC_POSTS'	=> $user->lang('USER_POSTS', (int) $active_t_count),
		'ACTIVE_TOPIC_PCT'		=> sprintf($l_active_pct, $active_t_pct),
		'U_ACTIVE_FORUM'		=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $active_f_id),
		'U_ACTIVE_TOPIC'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . $active_t_id),
		'S_SHOW_ACTIVITY'		=> $show_user_activity)
	);
}

/**
* Topic and forum watching common code
*/
function watch_topic_forum($mode, &$s_watching, $user_id, $forum_id, $topic_id, $notify_status = 'unset', $start = 0, $item_title = '')
{
	global $db, $user, $phpEx, $start, $phpbb_root_path;
	global $request;

	$table_sql = ($mode == 'forum') ? FORUMS_WATCH_TABLE : TOPICS_WATCH_TABLE;
	$where_sql = ($mode == 'forum') ? 'forum_id' : 'topic_id';
	$match_id = ($mode == 'forum') ? $forum_id : $topic_id;
	$u_url = "uid={$user->data['user_id']}";
	$u_url .= ($mode == 'forum') ? '&amp;f' : '&amp;f=' . $forum_id . '&amp;t';
	$is_watching = 0;

	// Is user watching this topic?
	if ($user_id != ANONYMOUS)
	{
		$can_watch = true;

		if ($notify_status == 'unset')
		{
			$sql = "SELECT notify_status
				FROM $table_sql
				WHERE $where_sql = $match_id
					AND user_id = $user_id";
			$result = $db->sql_query($sql);

			$notify_status = ($row = $db->sql_fetchrow($result)) ? $row['notify_status'] : NULL;
			$db->sql_freeresult($result);
		}

		if (!is_null($notify_status) && $notify_status !== '')
		{
			if (isset($_GET['unwatch']))
			{
				$uid = $request->variable('uid', 0);
				$token = $request->variable('hash', '');

				if ($token && check_link_hash($token, "{$mode}_$match_id") || confirm_box(true))
				{
					if ($uid != $user_id || $request->variable('unwatch', '', false, \phpbb\request\request_interface::GET) != $mode)
					{
						$redirect_url = append_sid("{$phpbb_root_path}view$mode.$phpEx", "$u_url=$match_id&amp;start=$start");
						$message = $user->lang['ERR_UNWATCHING'];

						if (!$request->is_ajax())
						{
							$message .= '<br /><br />' . $user->lang('RETURN_' . strtoupper($mode), '<a href="' . $redirect_url . '">', '</a>');
						}
						trigger_error($message);
					}

					$sql = 'DELETE FROM ' . $table_sql . "
						WHERE $where_sql = $match_id
							AND user_id = $user_id";
					$db->sql_query($sql);

					$redirect_url = append_sid("{$phpbb_root_path}view$mode.$phpEx", "$u_url=$match_id&amp;start=$start");
					$message = $user->lang['NOT_WATCHING_' . strtoupper($mode)];

					if (!$request->is_ajax())
					{
						$message .= '<br /><br />' . $user->lang('RETURN_' . strtoupper($mode), '<a href="' . $redirect_url . '">', '</a>');
					}
					meta_refresh(3, $redirect_url);
					trigger_error($message);
				}
				else
				{
					$s_hidden_fields = array(
						'uid'		=> $user->data['user_id'],
						'unwatch'	=> $mode,
						'start'		=> $start,
						'f'			=> $forum_id,
					);
					if ($mode != 'forum')
					{
						$s_hidden_fields['t'] = $topic_id;
					}

					if ($item_title == '')
					{
						$confirm_box_message = 'UNWATCH_' . strtoupper($mode);
					}
					else
					{
						$confirm_box_message = $user->lang('UNWATCH_' . strtoupper($mode) . '_DETAILED', $item_title);
					}
					confirm_box(false, $confirm_box_message, build_hidden_fields($s_hidden_fields));
				}
			}
			else
			{
				$is_watching = true;

				if ($notify_status != NOTIFY_YES)
				{
					$sql = 'UPDATE ' . $table_sql . "
						SET notify_status = " . NOTIFY_YES . "
						WHERE $where_sql = $match_id
							AND user_id = $user_id";
					$db->sql_query($sql);
				}
			}
		}
		else
		{
			if (isset($_GET['watch']))
			{
				$uid = $request->variable('uid', 0);
				$token = $request->variable('hash', '');

				if ($token && check_link_hash($token, "{$mode}_$match_id") || confirm_box(true))
				{
					if ($uid != $user_id || $request->variable('watch', '', false, \phpbb\request\request_interface::GET) != $mode)
					{
						$redirect_url = append_sid("{$phpbb_root_path}view$mode.$phpEx", "$u_url=$match_id&amp;start=$start");
						$message = $user->lang['ERR_WATCHING'];

						if (!$request->is_ajax())
						{
							$message .= '<br /><br />' . $user->lang('RETURN_' . strtoupper($mode), '<a href="' . $redirect_url . '">', '</a>');
						}
						trigger_error($message);
					}

					$is_watching = true;

					$sql = 'INSERT INTO ' . $table_sql . " (user_id, $where_sql, notify_status)
						VALUES ($user_id, $match_id, " . NOTIFY_YES . ')';
					$db->sql_query($sql);

					$redirect_url = append_sid("{$phpbb_root_path}view$mode.$phpEx", "$u_url=$match_id&amp;start=$start");
					$message = $user->lang['ARE_WATCHING_' . strtoupper($mode)];

					if (!$request->is_ajax())
					{
						$message .= '<br /><br />' . $user->lang('RETURN_' . strtoupper($mode), '<a href="' . $redirect_url . '">', '</a>');
					}
					meta_refresh(3, $redirect_url);
					trigger_error($message);
				}
				else
				{
					$s_hidden_fields = array(
						'uid'		=> $user->data['user_id'],
						'watch'		=> $mode,
						'start'		=> $start,
						'f'			=> $forum_id,
					);
					if ($mode != 'forum')
					{
						$s_hidden_fields['t'] = $topic_id;
					}

					$confirm_box_message = (($item_title == '') ? 'WATCH_' . strtoupper($mode) : $user->lang('WATCH_' . strtoupper($mode) . '_DETAILED', $item_title));
					confirm_box(false, $confirm_box_message, build_hidden_fields($s_hidden_fields));
				}
			}
			else
			{
				$is_watching = 0;
			}
		}
	}
	else
	{
		if ((isset($_GET['unwatch']) && $request->variable('unwatch', '', false, \phpbb\request\request_interface::GET) == $mode) ||
			(isset($_GET['watch']) && $request->variable('watch', '', false, \phpbb\request\request_interface::GET) == $mode))
		{
			login_box();
		}
		else
		{
			$can_watch = 0;
			$is_watching = 0;
		}
	}

	if ($can_watch)
	{
		$s_watching['link'] = append_sid("{$phpbb_root_path}view$mode.$phpEx", "$u_url=$match_id&amp;" . (($is_watching) ? 'unwatch' : 'watch') . "=$mode&amp;start=$start&amp;hash=" . generate_link_hash("{$mode}_$match_id"));
		$s_watching['link_toggle'] = append_sid("{$phpbb_root_path}view$mode.$phpEx", "$u_url=$match_id&amp;" . ((!$is_watching) ? 'unwatch' : 'watch') . "=$mode&amp;start=$start&amp;hash=" . generate_link_hash("{$mode}_$match_id"));
		$s_watching['title'] = $user->lang[(($is_watching) ? 'STOP' : 'START') . '_WATCHING_' . strtoupper($mode)];
		$s_watching['title_toggle'] = $user->lang[((!$is_watching) ? 'STOP' : 'START') . '_WATCHING_' . strtoupper($mode)];
		$s_watching['is_watching'] = $is_watching;
	}

	return;
}

/**
* Get user rank title and image
*
* @param array $user_data the current stored users data
* @param int $user_posts the users number of posts
*
* @return array An associative array containing the rank title (title), the rank image as full img tag (img) and the rank image source (img_src)
*
* Note: since we do not want to break backwards-compatibility, this function will only properly assign ranks to guests if you call it for them with user_posts == false
*/
function phpbb_get_user_rank($user_data, $user_posts)
{
	global $ranks, $config, $phpbb_root_path, $phpbb_path_helper, $phpbb_dispatcher;

	$user_rank_data = array(
		'title'		=> null,
		'img'		=> null,
		'img_src'	=> null,
	);

	/**
	* Preparing a user's rank before displaying
	*
	* @event core.modify_user_rank
	* @var	array	user_data		Array with user's data
	* @var	int		user_posts		User_posts to change
	* @since 3.1.0-RC4
	*/

	$vars = array('user_data', 'user_posts');
	extract($phpbb_dispatcher->trigger_event('core.modify_user_rank', compact($vars)));

	if (empty($ranks))
	{
		global $cache;
		$ranks = $cache->obtain_ranks();
	}

	if (!empty($user_data['user_rank']))
	{

		$user_rank_data['title'] = (isset($ranks['special'][$user_data['user_rank']]['rank_title'])) ? $ranks['special'][$user_data['user_rank']]['rank_title'] : '';

		$user_rank_data['img_src'] = (!empty($ranks['special'][$user_data['user_rank']]['rank_image'])) ? $phpbb_path_helper->update_web_root_path($phpbb_root_path . $config['ranks_path'] . '/' . $ranks['special'][$user_data['user_rank']]['rank_image']) : '';

		$user_rank_data['img'] = (!empty($ranks['special'][$user_data['user_rank']]['rank_image'])) ? '<img src="' . $user_rank_data['img_src'] . '" alt="' . $ranks['special'][$user_data['user_rank']]['rank_title'] . '" title="' . $ranks['special'][$user_data['user_rank']]['rank_title'] . '" />' : '';
	}
	else if ($user_posts !== false)
	{
		if (!empty($ranks['normal']))
		{
			foreach ($ranks['normal'] as $rank)
			{
				if ($user_posts >= $rank['rank_min'])
				{
					$user_rank_data['title'] = $rank['rank_title'];
					$user_rank_data['img_src'] = (!empty($rank['rank_image'])) ? $phpbb_path_helper->update_web_root_path($phpbb_root_path . $config['ranks_path'] . '/' . $rank['rank_image']) : '';
					$user_rank_data['img'] = (!empty($rank['rank_image'])) ? '<img src="' . $user_rank_data['img_src'] . '" alt="' . $rank['rank_title'] . '" title="' . $rank['rank_title'] . '" />' : '';
					break;
				}
			}
		}
	}

	/**
	* Modify a user's rank before displaying
	*
	* @event core.get_user_rank_after
	* @var	array	user_data		Array with user's data
	* @var	int		user_posts		User_posts to change
	* @var	array	user_rank_data	User rank data
	* @since 3.1.11-RC1
	*/

	$vars = array(
		'user_data',
		'user_posts',
		'user_rank_data',
	);
	extract($phpbb_dispatcher->trigger_event('core.get_user_rank_after', compact($vars)));

	return $user_rank_data;
}

/**
* Prepare profile data
*/
function phpbb_show_profile($data, $user_notes_enabled = false, $warn_user_enabled = false, $check_can_receive_pm = true)
{
	global $config, $auth, $user, $phpEx, $phpbb_root_path, $phpbb_dispatcher;

	$username = $data['username'];
	$user_id = $data['user_id'];

	$user_rank_data = phpbb_get_user_rank($data, (($user_id == ANONYMOUS) ? false : $data['user_posts']));

	if ((!empty($data['user_allow_viewemail']) && $auth->acl_get('u_sendemail')) || $auth->acl_get('a_user'))
	{
		$email = ($config['board_email_form'] && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=email&amp;u=' . $user_id) : (($config['board_hide_emails'] && !$auth->acl_get('a_user')) ? '' : 'mailto:' . $data['user_email']);
	}
	else
	{
		$email = '';
	}

	if ($config['load_onlinetrack'])
	{
		$update_time = $config['load_online_time'] * 60;
		$online = (time() - $update_time < $data['session_time'] && ((isset($data['session_viewonline']) && $data['session_viewonline']) || $auth->acl_get('u_viewonline'))) ? true : false;
	}
	else
	{
		$online = false;
	}

	if ($data['user_allow_viewonline'] || $auth->acl_get('u_viewonline'))
	{
		$last_active = (!empty($data['session_time'])) ? $data['session_time'] : $data['user_lastvisit'];
	}
	else
	{
		$last_active = '';
	}

	$age = '';

	if ($config['allow_birthdays'] && $data['user_birthday'])
	{
		list($bday_day, $bday_month, $bday_year) = array_map('intval', explode('-', $data['user_birthday']));

		if ($bday_year)
		{
			$now = $user->create_datetime();
			$now = phpbb_gmgetdate($now->getTimestamp() + $now->getOffset());

			$diff = $now['mon'] - $bday_month;
			if ($diff == 0)
			{
				$diff = ($now['mday'] - $bday_day < 0) ? 1 : 0;
			}
			else
			{
				$diff = ($diff < 0) ? 1 : 0;
			}

			$age = max(0, (int) ($now['year'] - $bday_year - $diff));
		}
	}

	if (!function_exists('phpbb_get_banned_user_ids'))
	{
		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
	}

	// Can this user receive a Private Message?
	$can_receive_pm = $check_can_receive_pm && (
		// They must be a "normal" user
		$data['user_type'] != USER_IGNORE &&

		// They must not be deactivated by the administrator
		($data['user_type'] != USER_INACTIVE || $data['user_inactive_reason'] != INACTIVE_MANUAL) &&

		// They must be able to read PMs
		count($auth->acl_get_list($user_id, 'u_readpm')) &&

		// They must not be permanently banned
		!count(phpbb_get_banned_user_ids($user_id, false)) &&

		// They must allow users to contact via PM
		(($auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_')) || $data['user_allow_pm'])
	);

	// Dump it out to the template
	$template_data = array(
		'AGE'			=> $age,
		'RANK_TITLE'	=> $user_rank_data['title'],
		'JOINED'		=> $user->format_date($data['user_regdate']),
		'LAST_ACTIVE'	=> (empty($last_active)) ? ' - ' : $user->format_date($last_active),
		'POSTS'			=> ($data['user_posts']) ? $data['user_posts'] : 0,
		'WARNINGS'		=> isset($data['user_warnings']) ? $data['user_warnings'] : 0,

		'USERNAME_FULL'		=> get_username_string('full', $user_id, $username, $data['user_colour']),
		'USERNAME'			=> get_username_string('username', $user_id, $username, $data['user_colour']),
		'USER_COLOR'		=> get_username_string('colour', $user_id, $username, $data['user_colour']),
		'U_VIEW_PROFILE'	=> get_username_string('profile', $user_id, $username, $data['user_colour']),

		'A_USERNAME'		=> addslashes(get_username_string('username', $user_id, $username, $data['user_colour'])),

		'AVATAR_IMG'		=> phpbb_get_user_avatar($data),
		'ONLINE_IMG'		=> (!$config['load_onlinetrack']) ? '' : (($online) ? $user->img('icon_user_online', 'ONLINE') : $user->img('icon_user_offline', 'OFFLINE')),
		'S_ONLINE'			=> ($config['load_onlinetrack'] && $online) ? true : false,
		'RANK_IMG'			=> $user_rank_data['img'],
		'RANK_IMG_SRC'		=> $user_rank_data['img_src'],
		'S_JABBER_ENABLED'	=> ($config['jab_enable']) ? true : false,

		'S_WARNINGS'	=> ($auth->acl_getf_global('m_') || $auth->acl_get('m_warn')) ? true : false,

		'U_SEARCH_USER' => ($auth->acl_get('u_search')) ? append_sid("{$phpbb_root_path}search.$phpEx", "author_id=$user_id&amp;sr=posts") : '',
		'U_NOTES'		=> ($user_notes_enabled && $auth->acl_getf_global('m_')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $user_id, true, $user->session_id) : '',
		'U_WARN'		=> ($warn_user_enabled && $auth->acl_get('m_warn')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=warn&amp;mode=warn_user&amp;u=' . $user_id, true, $user->session_id) : '',
		'U_PM'			=> ($config['allow_privmsg'] && $auth->acl_get('u_sendpm') && $can_receive_pm) ? append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;mode=compose&amp;u=' . $user_id) : '',
		'U_EMAIL'		=> $email,
		'U_JABBER'		=> ($data['user_jabber'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=contact&amp;action=jabber&amp;u=' . $user_id) : '',

		'USER_JABBER'		=> ($config['jab_enable']) ? $data['user_jabber'] : '',
		'USER_JABBER_IMG'	=> ($config['jab_enable'] && $data['user_jabber']) ? $user->img('icon_contact_jabber', $data['user_jabber']) : '',

		'L_SEND_EMAIL_USER' => $user->lang('SEND_EMAIL_USER', $username),
		'L_CONTACT_USER'	=> $user->lang('CONTACT_USER', $username),
		'L_VIEWING_PROFILE' => $user->lang('VIEWING_PROFILE', $username),
	);

	/**
	* Preparing a user's data before displaying it in profile and memberlist
	*
	* @event core.memberlist_prepare_profile_data
	* @var	array	data				Array with user's data
	* @var	array	template_data		Template array with user's data
	* @since 3.1.0-a1
	*/
	$vars = array('data', 'template_data');
	extract($phpbb_dispatcher->trigger_event('core.memberlist_prepare_profile_data', compact($vars)));

	return $template_data;
}

function phpbb_sort_last_active($first, $second)
{
	global $id_cache, $sort_dir;

	$lesser_than = ($sort_dir === 'd') ? -1 : 1;

	if (isset($id_cache[$first]['group_leader']) && $id_cache[$first]['group_leader'] && (!isset($id_cache[$second]['group_leader']) || !$id_cache[$second]['group_leader']))
	{
		return -1;
	}
	else if (isset($id_cache[$second]['group_leader']) && (!isset($id_cache[$first]['group_leader']) || !$id_cache[$first]['group_leader']) && $id_cache[$second]['group_leader'])
	{
		return 1;
	}
	else
	{
		return $lesser_than * (int) ($id_cache[$first]['last_visit'] - $id_cache[$second]['last_visit']);
	}
}
