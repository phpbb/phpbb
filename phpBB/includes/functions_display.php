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
* Display Forums
*/
function display_forums($root_data = '', $display_moderators = true, $return_moderators = false)
{
	global $db, $auth, $user, $template;
	global $phpbb_root_path, $phpEx, $config;
	global $request, $phpbb_dispatcher, $phpbb_container;

	$forum_rows = $subforums = $forum_ids = $forum_ids_moderator = $forum_moderators = $active_forum_ary = array();
	$parent_id = $visible_forums = 0;

	// Mark forums read?
	$mark_read = $request->variable('mark', '');

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
		$sql_where = 'left_id > ' . $root_data['left_id'] . ' AND left_id < ' . $root_data['right_id'];
	}

	// Handle marking everything read
	if ($mark_read == 'all')
	{
		$redirect = build_url(array('mark', 'hash', 'mark_time'));
		meta_refresh(3, $redirect);

		if (check_link_hash($request->variable('hash', ''), 'global'))
		{
			markread('all', false, false, $request->variable('mark_time', 0));

			if ($request->is_ajax())
			{
				// Tell the ajax script what language vars and URL need to be replaced
				$data = array(
					'NO_UNREAD_POSTS'	=> $user->lang['NO_UNREAD_POSTS'],
					'UNREAD_POSTS'		=> $user->lang['UNREAD_POSTS'],
					'U_MARK_FORUMS'		=> ($user->data['is_registered'] || $config['load_anon_lastread']) ? append_sid("{$phpbb_root_path}index.$phpEx", 'hash=' . generate_link_hash('global') . '&mark=forums&mark_time=' . time()) : '',
					'MESSAGE_TITLE'		=> $user->lang['INFORMATION'],
					'MESSAGE_TEXT'		=> $user->lang['FORUMS_MARKED']
				);
				$json_response = new \phpbb\json_response();
				$json_response->send($data);
			}

			trigger_error(
				$user->lang['FORUMS_MARKED'] . '<br /><br />' .
				sprintf($user->lang['RETURN_INDEX'], '<a href="' . $redirect . '">', '</a>')
			);
		}
		else
		{
			trigger_error(sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>'));
		}
	}

	// Display list of active topics for this category?
	$show_active = (isset($root_data['forum_flags']) && ($root_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS)) ? true : false;

	$sql_array = array(
		'SELECT'	=> 'f.*',
		'FROM'		=> array(
			FORUMS_TABLE		=> 'f'
		),
		'LEFT_JOIN'	=> array(),
	);

	if ($config['load_db_lastread'] && $user->data['is_registered'])
	{
		$sql_array['LEFT_JOIN'][] = array('FROM' => array(FORUMS_TRACK_TABLE => 'ft'), 'ON' => 'ft.user_id = ' . $user->data['user_id'] . ' AND ft.forum_id = f.forum_id');
		$sql_array['SELECT'] .= ', ft.mark_time';
	}
	else if ($config['load_anon_lastread'] || $user->data['is_registered'])
	{
		$tracking_topics = $request->variable($config['cookie_name'] . '_track', '', true, \phpbb\request\request_interface::COOKIE);
		$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();

		if (!$user->data['is_registered'])
		{
			$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? (int) (base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate']) : 0;
		}
	}

	if ($show_active)
	{
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(FORUMS_ACCESS_TABLE => 'fa'),
			'ON'	=> "fa.forum_id = f.forum_id AND fa.session_id = '" . $db->sql_escape($user->session_id) . "'"
		);

		$sql_array['SELECT'] .= ', fa.user_id';
	}

	$sql_ary = array(
		'SELECT'	=> $sql_array['SELECT'],
		'FROM'		=> $sql_array['FROM'],
		'LEFT_JOIN'	=> $sql_array['LEFT_JOIN'],

		'WHERE'		=> $sql_where,

		'ORDER_BY'	=> 'f.left_id',
	);

	/**
	* Event to modify the SQL query before the forum data is queried
	*
	* @event core.display_forums_modify_sql
	* @var	array	sql_ary		The SQL array to get the data of the forums
	* @since 3.1.0-a1
	*/
	$vars = array('sql_ary');
	extract($phpbb_dispatcher->trigger_event('core.display_forums_modify_sql', compact($vars)));

	$sql = $db->sql_build_query('SELECT', $sql_ary);
	$result = $db->sql_query($sql);

	$forum_tracking_info = $valid_categories = array();
	$branch_root_id = $root_data['forum_id'];

	/* @var $phpbb_content_visibility \phpbb\content_visibility */
	$phpbb_content_visibility = $phpbb_container->get('content.visibility');

	while ($row = $db->sql_fetchrow($result))
	{
		/**
		* Event to modify the data set of a forum
		*
		* This event is triggered once per forum
		*
		* @event core.display_forums_modify_row
		* @var	int		branch_root_id	Last top-level forum
		* @var	array	row				The data of the forum
		* @since 3.1.0-a1
		*/
		$vars = array('branch_root_id', 'row');
		extract($phpbb_dispatcher->trigger_event('core.display_forums_modify_row', compact($vars)));

		$forum_id = $row['forum_id'];

		// Mark forums read?
		if ($mark_read == 'forums')
		{
			if ($auth->acl_get('f_list', $forum_id))
			{
				$forum_ids[] = $forum_id;
			}

			continue;
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

		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			$forum_tracking_info[$forum_id] = (!empty($row['mark_time'])) ? $row['mark_time'] : $user->data['user_lastmark'];
		}
		else if ($config['load_anon_lastread'] || $user->data['is_registered'])
		{
			if (!$user->data['is_registered'])
			{
				$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? (int) (base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate']) : 0;
			}
			$forum_tracking_info[$forum_id] = (isset($tracking_topics['f'][$forum_id])) ? (int) (base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate']) : $user->data['user_lastmark'];
		}

		// Lets check whether there are unapproved topics/posts, so we can display an information to moderators
		$row['forum_id_unapproved_topics'] = ($auth->acl_get('m_approve', $forum_id) && $row['forum_topics_unapproved']) ? $forum_id : 0;
		$row['forum_id_unapproved_posts'] = ($auth->acl_get('m_approve', $forum_id) && $row['forum_posts_unapproved']) ? $forum_id : 0;
		$row['forum_posts'] = $phpbb_content_visibility->get_count('forum_posts', $row, $forum_id);
		$row['forum_topics'] = $phpbb_content_visibility->get_count('forum_topics', $row, $forum_id);

		// Display active topics from this forum?
		if ($show_active && $row['forum_type'] == FORUM_POST && $auth->acl_get('f_read', $forum_id) && ($row['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS))
		{
			if (!isset($active_forum_ary['forum_topics']))
			{
				$active_forum_ary['forum_topics'] = 0;
			}

			if (!isset($active_forum_ary['forum_posts']))
			{
				$active_forum_ary['forum_posts'] = 0;
			}

			$active_forum_ary['forum_id'][]		= $forum_id;
			$active_forum_ary['enable_icons'][]	= $row['enable_icons'];
			$active_forum_ary['forum_topics']	+= $row['forum_topics'];
			$active_forum_ary['forum_posts']	+= $row['forum_posts'];

			// If this is a passworded forum we do not show active topics from it if the user is not authorised to view it...
			if ($row['forum_password'] && $row['user_id'] != $user->data['user_id'])
			{
				$active_forum_ary['exclude_forum_id'][] = $forum_id;
			}
		}

		// Fill list of categories with forums
		if (isset($forum_rows[$row['parent_id']]))
		{
			$valid_categories[$row['parent_id']] = true;
		}

		//
		if ($row['parent_id'] == $root_data['forum_id'] || $row['parent_id'] == $branch_root_id)
		{
			if ($row['forum_type'] != FORUM_CAT)
			{
				$forum_ids_moderator[] = (int) $forum_id;
			}

			// Direct child of current branch
			$parent_id = $forum_id;
			$forum_rows[$forum_id] = $row;

			if ($row['forum_type'] == FORUM_CAT && $row['parent_id'] == $root_data['forum_id'])
			{
				$branch_root_id = $forum_id;
			}
			$forum_rows[$parent_id]['forum_id_last_post'] = $row['forum_id'];
			$forum_rows[$parent_id]['forum_password_last_post'] = $row['forum_password'];
			$forum_rows[$parent_id]['orig_forum_last_post_time'] = $row['forum_last_post_time'];
		}
		else if ($row['forum_type'] != FORUM_CAT)
		{
			$subforums[$parent_id][$forum_id]['display'] = ($row['display_on_index']) ? true : false;
			$subforums[$parent_id][$forum_id]['name'] = $row['forum_name'];
			$subforums[$parent_id][$forum_id]['orig_forum_last_post_time'] = $row['forum_last_post_time'];
			$subforums[$parent_id][$forum_id]['children'] = array();
			$subforums[$parent_id][$forum_id]['type'] = $row['forum_type'];

			if (isset($subforums[$parent_id][$row['parent_id']]) && !$row['display_on_index'])
			{
				$subforums[$parent_id][$row['parent_id']]['children'][] = $forum_id;
			}

			if (!$forum_rows[$parent_id]['forum_id_unapproved_topics'] && $row['forum_id_unapproved_topics'])
			{
				$forum_rows[$parent_id]['forum_id_unapproved_topics'] = $forum_id;
			}

			if (!$forum_rows[$parent_id]['forum_id_unapproved_posts'] && $row['forum_id_unapproved_posts'])
			{
				$forum_rows[$parent_id]['forum_id_unapproved_posts'] = $forum_id;
			}

			$forum_rows[$parent_id]['forum_topics'] += $row['forum_topics'];

			// Do not list redirects in LINK Forums as Posts.
			if ($row['forum_type'] != FORUM_LINK)
			{
				$forum_rows[$parent_id]['forum_posts'] += $row['forum_posts'];
			}

			if ($row['forum_last_post_time'] > $forum_rows[$parent_id]['forum_last_post_time'])
			{
				$forum_rows[$parent_id]['forum_last_post_id'] = $row['forum_last_post_id'];
				$forum_rows[$parent_id]['forum_last_post_subject'] = $row['forum_last_post_subject'];
				$forum_rows[$parent_id]['forum_last_post_time'] = $row['forum_last_post_time'];
				$forum_rows[$parent_id]['forum_last_poster_id'] = $row['forum_last_poster_id'];
				$forum_rows[$parent_id]['forum_last_poster_name'] = $row['forum_last_poster_name'];
				$forum_rows[$parent_id]['forum_last_poster_colour'] = $row['forum_last_poster_colour'];
				$forum_rows[$parent_id]['forum_id_last_post'] = $forum_id;
				$forum_rows[$parent_id]['forum_password_last_post'] = $row['forum_password'];
			}
		}

		/**
		* Event to modify the forum rows data set
		*
		* This event is triggered once per forum
		*
		* @event core.display_forums_modify_forum_rows
		* @var	array	forum_rows		Data array of all forums we display
		* @var	array	subforums		Data array of all subforums we display
		* @var	int		branch_root_id	Current top-level forum
		* @var	int		parent_id		Current parent forum
		* @var	array	row				The data of the forum
		* @since 3.1.0-a1
		*/
		$vars = array('forum_rows', 'subforums', 'branch_root_id', 'parent_id', 'row');
		extract($phpbb_dispatcher->trigger_event('core.display_forums_modify_forum_rows', compact($vars)));
	}
	$db->sql_freeresult($result);

	// Handle marking posts
	if ($mark_read == 'forums')
	{
		$redirect = build_url(array('mark', 'hash', 'mark_time'));
		$token = $request->variable('hash', '');
		if (check_link_hash($token, 'global'))
		{
			markread('topics', $forum_ids, false, $request->variable('mark_time', 0));
			$message = sprintf($user->lang['RETURN_FORUM'], '<a href="' . $redirect . '">', '</a>');
			meta_refresh(3, $redirect);

			if ($request->is_ajax())
			{
				// Tell the ajax script what language vars and URL need to be replaced
				$data = array(
					'NO_UNREAD_POSTS'	=> $user->lang['NO_UNREAD_POSTS'],
					'UNREAD_POSTS'		=> $user->lang['UNREAD_POSTS'],
					'U_MARK_FORUMS'		=> ($user->data['is_registered'] || $config['load_anon_lastread']) ? append_sid("{$phpbb_root_path}viewforum.$phpEx", 'hash=' . generate_link_hash('global') . '&f=' . $root_data['forum_id'] . '&mark=forums&mark_time=' . time()) : '',
					'MESSAGE_TITLE'		=> $user->lang['INFORMATION'],
					'MESSAGE_TEXT'		=> $user->lang['FORUMS_MARKED']
				);
				$json_response = new \phpbb\json_response();
				$json_response->send($data);
			}

			trigger_error($user->lang['FORUMS_MARKED'] . '<br /><br />' . $message);
		}
		else
		{
			$message = sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>');
			meta_refresh(3, $redirect);
			trigger_error($message);
		}

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

	/**
	* Event to perform additional actions before the forum list is being generated
	*
	* @event core.display_forums_before
	* @var	array	active_forum_ary	Array with forum data to display active topics
	* @var	bool	display_moderators	Flag indicating if we display forum moderators
	* @var	array	forum_moderators	Array with forum moderators list
	* @var	array	forum_rows			Data array of all forums we display
	* @var	bool	return_moderators	Flag indicating if moderators list should be returned
	* @var	array	root_data			Array with the root forum data
	* @since 3.1.4-RC1
	*/
	$vars = array(
		'active_forum_ary',
		'display_moderators',
		'forum_moderators',
		'forum_rows',
		'return_moderators',
		'root_data',
	);
	extract($phpbb_dispatcher->trigger_event('core.display_forums_before', compact($vars)));

	// Used to tell whatever we have to create a dummy category or not.
	$last_catless = true;
	foreach ($forum_rows as $row)
	{
		// Category
		if ($row['parent_id'] == $root_data['forum_id'] && $row['forum_type'] == FORUM_CAT)
		{
			// Do not display categories without any forums to display
			if (!isset($valid_categories[$row['forum_id']]))
			{
				continue;
			}

			$cat_row = array(
				'S_IS_CAT'				=> true,
				'FORUM_ID'				=> $row['forum_id'],
				'FORUM_NAME'			=> $row['forum_name'],
				'FORUM_DESC'			=> generate_text_for_display($row['forum_desc'], $row['forum_desc_uid'], $row['forum_desc_bitfield'], $row['forum_desc_options']),
				'FORUM_FOLDER_IMG'		=> '',
				'FORUM_FOLDER_IMG_SRC'	=> '',
				'FORUM_IMAGE'			=> ($row['forum_image']) ? '<img src="' . $phpbb_root_path . $row['forum_image'] . '" alt="' . $user->lang['FORUM_CAT'] . '" />' : '',
				'FORUM_IMAGE_SRC'		=> ($row['forum_image']) ? $phpbb_root_path . $row['forum_image'] : '',
				'U_VIEWFORUM'			=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $row['forum_id']),
			);

			/**
			* Modify the template data block of the 'category'
			*
			* This event is triggered once per 'category'
			*
			* @event core.display_forums_modify_category_template_vars
			* @var	array	cat_row			Template data of the 'category'
			* @var	bool	last_catless	The flag indicating whether the last forum had a parent category
			* @var	array	root_data		Array with the root forum data
			* @var	array	row				The data of the 'category'
			* @since 3.1.0-RC4
			* @changed 3.1.7-RC1 Removed undefined catless variable
			*/
			$vars = array(
				'cat_row',
				'last_catless',
				'root_data',
				'row',
			);
			extract($phpbb_dispatcher->trigger_event('core.display_forums_modify_category_template_vars', compact($vars)));

			$template->assign_block_vars('forumrow', $cat_row);

			continue;
		}

		$visible_forums++;
		$forum_id = $row['forum_id'];

		$forum_unread = (isset($forum_tracking_info[$forum_id]) && $row['orig_forum_last_post_time'] > $forum_tracking_info[$forum_id]) ? true : false;

		$folder_image = $folder_alt = $l_subforums = '';
		$subforums_list = array();

		// Generate list of subforums if we need to
		if (isset($subforums[$forum_id]))
		{
			foreach ($subforums[$forum_id] as $subforum_id => $subforum_row)
			{
				$subforum_unread = (isset($forum_tracking_info[$subforum_id]) && $subforum_row['orig_forum_last_post_time'] > $forum_tracking_info[$subforum_id]) ? true : false;

				if (!$subforum_unread && !empty($subforum_row['children']))
				{
					foreach ($subforum_row['children'] as $child_id)
					{
						if (isset($forum_tracking_info[$child_id]) && $subforums[$forum_id][$child_id]['orig_forum_last_post_time'] > $forum_tracking_info[$child_id])
						{
							// Once we found an unread child forum, we can drop out of this loop
							$subforum_unread = true;
							break;
						}
					}
				}

				if ($subforum_row['display'] && $subforum_row['name'])
				{
					$subforums_list[] = array(
						'link'		=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $subforum_id),
						'name'		=> $subforum_row['name'],
						'unread'	=> $subforum_unread,
						'type'		=> $subforum_row['type'],
					);
				}
				else
				{
					unset($subforums[$forum_id][$subforum_id]);
				}

				// If one subforum is unread the forum gets unread too...
				if ($subforum_unread)
				{
					$forum_unread = true;
				}
			}

			$l_subforums = (count($subforums[$forum_id]) == 1) ? $user->lang['SUBFORUM'] : $user->lang['SUBFORUMS'];
			$folder_image = ($forum_unread) ? 'forum_unread_subforum' : 'forum_read_subforum';
		}
		else
		{
			switch ($row['forum_type'])
			{
				case FORUM_POST:
					$folder_image = ($forum_unread) ? 'forum_unread' : 'forum_read';
				break;

				case FORUM_LINK:
					$folder_image = 'forum_link';
				break;
			}
		}

		// Which folder should we display?
		if ($row['forum_status'] == ITEM_LOCKED)
		{
			$folder_image = ($forum_unread) ? 'forum_unread_locked' : 'forum_read_locked';
			$folder_alt = 'FORUM_LOCKED';
		}
		else
		{
			$folder_alt = ($forum_unread) ? 'UNREAD_POSTS' : 'NO_UNREAD_POSTS';
		}

		// Create last post link information, if appropriate
		if ($row['forum_last_post_id'])
		{
			if ($row['forum_password_last_post'] === '' && $auth->acl_gets('f_read', 'f_list_topics', $row['forum_id_last_post']))
			{
				$last_post_subject = censor_text($row['forum_last_post_subject']);
				$last_post_subject_truncated = truncate_string($last_post_subject, 30, 255, false, $user->lang['ELLIPSIS']);
			}
			else
			{
				$last_post_subject = $last_post_subject_truncated = '';
			}
			$last_post_time = $user->format_date($row['forum_last_post_time']);
			$last_post_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $row['forum_id_last_post'] . '&amp;p=' . $row['forum_last_post_id']) . '#p' . $row['forum_last_post_id'];
		}
		else
		{
			$last_post_subject = $last_post_time = $last_post_url = $last_post_subject_truncated = '';
		}

		// Output moderator listing ... if applicable
		$l_moderator = $moderators_list = '';
		if ($display_moderators && !empty($forum_moderators[$forum_id]))
		{
			$l_moderator = (count($forum_moderators[$forum_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
			$moderators_list = implode($user->lang['COMMA_SEPARATOR'], $forum_moderators[$forum_id]);
		}

		$l_post_click_count = ($row['forum_type'] == FORUM_LINK) ? 'CLICKS' : 'POSTS';
		$post_click_count = ($row['forum_type'] != FORUM_LINK || $row['forum_flags'] & FORUM_FLAG_LINK_TRACK) ? $row['forum_posts'] : '';

		$s_subforums_list = $subforums_row = array();
		foreach ($subforums_list as $subforum)
		{
			$s_subforums_list[] = '<a href="' . $subforum['link'] . '" class="subforum ' . (($subforum['unread']) ? 'unread' : 'read') . '" title="' . (($subforum['unread']) ? $user->lang['UNREAD_POSTS'] : $user->lang['NO_UNREAD_POSTS']) . '">' . $subforum['name'] . '</a>';
			$subforums_row[] = array(
				'U_SUBFORUM'	=> $subforum['link'],
				'SUBFORUM_NAME'	=> $subforum['name'],
				'S_UNREAD'		=> $subforum['unread'],
				'IS_LINK'		=> $subforum['type'] == FORUM_LINK,
			);
		}
		$s_subforums_list = (string) implode($user->lang['COMMA_SEPARATOR'], $s_subforums_list);
		$catless = ($row['parent_id'] == $root_data['forum_id']) ? true : false;

		if ($row['forum_type'] != FORUM_LINK)
		{
			$u_viewforum = append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $row['forum_id']);
		}
		else
		{
			// If the forum is a link and we count redirects we need to visit it
			// If the forum is having a password or no read access we do not expose the link, but instead handle it in viewforum
			if (($row['forum_flags'] & FORUM_FLAG_LINK_TRACK) || $row['forum_password'] || !$auth->acl_get('f_read', $forum_id))
			{
				$u_viewforum = append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $row['forum_id']);
			}
			else
			{
				$u_viewforum = $row['forum_link'];
			}
		}

		$forum_row = array(
			'S_IS_CAT'			=> false,
			'S_NO_CAT'			=> $catless && !$last_catless,
			'S_IS_LINK'			=> ($row['forum_type'] == FORUM_LINK) ? true : false,
			'S_UNREAD_FORUM'	=> $forum_unread,
			'S_AUTH_READ'		=> $auth->acl_get('f_read', $row['forum_id']),
			'S_LOCKED_FORUM'	=> ($row['forum_status'] == ITEM_LOCKED) ? true : false,
			'S_LIST_SUBFORUMS'	=> ($row['display_subforum_list']) ? true : false,
			'S_SUBFORUMS'		=> (count($subforums_list)) ? true : false,
			'S_DISPLAY_SUBJECT'	=>	($last_post_subject !== '' && $config['display_last_subject']) ? true : false,
			'S_FEED_ENABLED'	=> ($config['feed_forum'] && !phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $row['forum_options']) && $row['forum_type'] == FORUM_POST) ? true : false,

			'FORUM_ID'				=> $row['forum_id'],
			'FORUM_NAME'			=> $row['forum_name'],
			'FORUM_DESC'			=> generate_text_for_display($row['forum_desc'], $row['forum_desc_uid'], $row['forum_desc_bitfield'], $row['forum_desc_options']),
			'TOPICS'				=> $row['forum_topics'],
			$l_post_click_count		=> $post_click_count,
			'FORUM_IMG_STYLE'		=> $folder_image,
			'FORUM_FOLDER_IMG'		=> $user->img($folder_image, $folder_alt),
			'FORUM_FOLDER_IMG_ALT'	=> isset($user->lang[$folder_alt]) ? $user->lang[$folder_alt] : '',
			'FORUM_IMAGE'			=> ($row['forum_image']) ? '<img src="' . $phpbb_root_path . $row['forum_image'] . '" alt="' . $user->lang[$folder_alt] . '" />' : '',
			'FORUM_IMAGE_SRC'		=> ($row['forum_image']) ? $phpbb_root_path . $row['forum_image'] : '',
			'LAST_POST_SUBJECT'		=> $last_post_subject,
			'LAST_POST_SUBJECT_TRUNCATED'	=> $last_post_subject_truncated,
			'LAST_POST_TIME'		=> $last_post_time,
			'LAST_POSTER'			=> get_username_string('username', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'LAST_POSTER_COLOUR'	=> get_username_string('colour', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'LAST_POSTER_FULL'		=> get_username_string('full', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'MODERATORS'			=> $moderators_list,
			'SUBFORUMS'				=> $s_subforums_list,

			'L_SUBFORUM_STR'		=> $l_subforums,
			'L_MODERATOR_STR'		=> $l_moderator,

			'U_UNAPPROVED_TOPICS'	=> ($row['forum_id_unapproved_topics']) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=unapproved_topics&amp;f=' . $row['forum_id_unapproved_topics']) : '',
			'U_UNAPPROVED_POSTS'	=> ($row['forum_id_unapproved_posts']) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=unapproved_posts&amp;f=' . $row['forum_id_unapproved_posts']) : '',
			'U_VIEWFORUM'		=> $u_viewforum,
			'U_LAST_POSTER'		=> get_username_string('profile', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'U_LAST_POST'		=> $last_post_url,
		);

		/**
		* Modify the template data block of the forum
		*
		* This event is triggered once per forum
		*
		* @event core.display_forums_modify_template_vars
		* @var	array	forum_row		Template data of the forum
		* @var	array	row				The data of the forum
		* @var	array	subforums_row	Template data of subforums
		* @since 3.1.0-a1
		* @changed 3.1.0-b5 Added var subforums_row
		*/
		$vars = array('forum_row', 'row', 'subforums_row');
		extract($phpbb_dispatcher->trigger_event('core.display_forums_modify_template_vars', compact($vars)));

		$template->assign_block_vars('forumrow', $forum_row);

		// Assign subforums loop for style authors
		$template->assign_block_vars_array('forumrow.subforum', $subforums_row);

		/**
		* Modify and/or assign additional template data for the forum
		* after forumrow loop has been assigned. This can be used
		* to create additional forumrow subloops in extensions.
		*
		* This event is triggered once per forum
		*
		* @event core.display_forums_add_template_data
		* @var	array	forum_row		Template data of the forum
		* @var	array	row				The data of the forum
		* @var	array	subforums_list	The data of subforums
		* @var	array	subforums_row	Template data of subforums
		* @var	bool	catless			The flag indicating whether a forum has a parent category
		* @since 3.1.0-b5
		*/
		$vars = array(
			'forum_row',
			'row',
			'subforums_list',
			'subforums_row',
			'catless',
		);
		extract($phpbb_dispatcher->trigger_event('core.display_forums_add_template_data', compact($vars)));

		$last_catless = $catless;
	}

	$template->assign_vars(array(
		'U_MARK_FORUMS'		=> ($user->data['is_registered'] || $config['load_anon_lastread']) ? append_sid("{$phpbb_root_path}viewforum.$phpEx", 'hash=' . generate_link_hash('global') . '&amp;f=' . $root_data['forum_id'] . '&amp;mark=forums&amp;mark_time=' . time()) : '',
		'S_HAS_SUBFORUM'	=> ($visible_forums) ? true : false,
		'L_SUBFORUM'		=> ($visible_forums == 1) ? $user->lang['SUBFORUM'] : $user->lang['SUBFORUMS'],
		'LAST_POST_IMG'		=> $user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
		'UNAPPROVED_IMG'	=> $user->img('icon_topic_unapproved', 'TOPICS_UNAPPROVED'),
		'UNAPPROVED_POST_IMG'	=> $user->img('icon_topic_unapproved', 'POSTS_UNAPPROVED_FORUM'),
	));

	/**
	* Event to perform additional actions after the forum list has been generated
	*
	* @event core.display_forums_after
	* @var	array	active_forum_ary	Array with forum data to display active topics
	* @var	bool	display_moderators	Flag indicating if we display forum moderators
	* @var	array	forum_moderators	Array with forum moderators list
	* @var	array	forum_rows			Data array of all forums we display
	* @var	bool	return_moderators	Flag indicating if moderators list should be returned
	* @var	array	root_data			Array with the root forum data
	* @since 3.1.0-RC5
	*/
	$vars = array(
		'active_forum_ary',
		'display_moderators',
		'forum_moderators',
		'forum_rows',
		'return_moderators',
		'root_data',
	);
	extract($phpbb_dispatcher->trigger_event('core.display_forums_after', compact($vars)));

	if ($return_moderators)
	{
		return array($active_forum_ary, $forum_moderators);
	}

	return array($active_forum_ary, array());
}

/**
* Create forum rules for given forum
*/
function generate_forum_rules(&$forum_data)
{
	if ($forum_data['forum_rules'])
	{
		$forum_data['forum_rules'] = generate_text_for_display($forum_data['forum_rules'], $forum_data['forum_rules_uid'], $forum_data['forum_rules_bitfield'], $forum_data['forum_rules_options']);
	}

	if (!$forum_data['forum_rules'] && !$forum_data['forum_rules_link'])
	{
		return;
	}

	global $template;

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
function generate_forum_nav(&$forum_data_ary)
{
	global $template, $auth, $config;
	global $phpEx, $phpbb_root_path, $phpbb_dispatcher;

	if (!$auth->acl_get('f_list', $forum_data_ary['forum_id']))
	{
		return;
	}

	$navlinks_parents = $forum_template_data = array();

	// Get forum parents
	$forum_parents = get_forum_parents($forum_data_ary);

	$microdata_attr = 'data-forum-id';

	// Build navigation links
	if (!empty($forum_parents))
	{
		foreach ($forum_parents as $parent_forum_id => $parent_data)
		{
			list($parent_name, $parent_type) = array_values($parent_data);

			// Skip this parent if the user does not have the permission to view it
			if (!$auth->acl_get('f_list', $parent_forum_id))
			{
				continue;
			}

			$navlinks_parents[] = array(
				'S_IS_CAT'		=> ($parent_type == FORUM_CAT) ? true : false,
				'S_IS_LINK'		=> ($parent_type == FORUM_LINK) ? true : false,
				'S_IS_POST'		=> ($parent_type == FORUM_POST) ? true : false,
				'FORUM_NAME'	=> $parent_name,
				'FORUM_ID'		=> $parent_forum_id,
				'MICRODATA'		=> $microdata_attr . '="' . $parent_forum_id . '"',
				'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $parent_forum_id),
			);
		}
	}

	$navlinks = array(
		'S_IS_CAT'		=> ($forum_data_ary['forum_type'] == FORUM_CAT) ? true : false,
		'S_IS_LINK'		=> ($forum_data_ary['forum_type'] == FORUM_LINK) ? true : false,
		'S_IS_POST'		=> ($forum_data_ary['forum_type'] == FORUM_POST) ? true : false,
		'FORUM_NAME'	=> $forum_data_ary['forum_name'],
		'FORUM_ID'		=> $forum_data_ary['forum_id'],
		'MICRODATA'		=> $microdata_attr . '="' . $forum_data_ary['forum_id'] . '"',
		'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_data_ary['forum_id']),
	);

	$forum_template_data = array(
		'FORUM_ID' 		=> $forum_data_ary['forum_id'],
		'FORUM_NAME'	=> $forum_data_ary['forum_name'],
		'FORUM_DESC'	=> generate_text_for_display($forum_data_ary['forum_desc'], $forum_data_ary['forum_desc_uid'], $forum_data_ary['forum_desc_bitfield'], $forum_data_ary['forum_desc_options']),

		'S_ENABLE_FEEDS_FORUM'	=> ($config['feed_forum'] && $forum_data_ary['forum_type'] == FORUM_POST && !phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $forum_data_ary['forum_options'])) ? true : false,
	);

	$forum_data = $forum_data_ary;
	/**
	* Event to modify the navlinks text
	*
	* @event core.generate_forum_nav
	* @var	array	forum_data				Array with the forum data
	* @var	array	forum_template_data		Array with generic forum template data
	* @var	string	microdata_attr			The microdata attribute
	* @var	array	navlinks_parents		Array with the forum parents navlinks data
	* @var	array	navlinks				Array with the forum navlinks data
	* @since 3.1.5-RC1
	*/
	$vars = array(
		'forum_data',
		'forum_template_data',
		'microdata_attr',
		'navlinks_parents',
		'navlinks',
	);
	extract($phpbb_dispatcher->trigger_event('core.generate_forum_nav', compact($vars)));
	$forum_data_ary = $forum_data;
	unset($forum_data);

	$template->assign_block_vars_array('navlinks', $navlinks_parents);
	$template->assign_block_vars('navlinks', $navlinks);
	$template->assign_vars($forum_template_data);

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
* Obtain list of moderators of each forum
*/
function get_moderators(&$forum_moderators, $forum_id = false)
{
	global $db, $phpbb_root_path, $phpEx, $user, $auth;
	global $phpbb_container;

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
* Display reasons
*
* @deprecated 3.2.0-dev
*/
function display_reasons($reason_id = 0)
{
	global $phpbb_container;

	$phpbb_container->get('phpbb.report.report_reason_list_provider')->display_reasons($reason_id);
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
