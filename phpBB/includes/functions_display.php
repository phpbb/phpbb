<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
	global $request, $phpbb_dispatcher;

	$forum_rows = $subforums = $forum_ids = $forum_ids_moderator = $forum_moderators = $active_forum_ary = array();
	$parent_id = $visible_forums = 0;
	$sql_from = '';

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
		$sql_where = 'left_id > ' . $root_data['left_id'] . ' AND left_id < ' . $root_data['right_id'];
	}

	// Handle marking everything read
	if ($mark_read == 'all')
	{
		$redirect = build_url(array('mark', 'hash', 'mark_time'));
		meta_refresh(3, $redirect);

		if (check_link_hash(request_var('hash', ''), 'global'))
		{
			markread('all', false, false, request_var('mark_time', 0));

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
				$json_response = new phpbb_json_response();
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
		$tracking_topics = $request->variable($config['cookie_name'] . '_track', '', true, phpbb_request_interface::COOKIE);
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
	* @since 3.1-A1
	*/
	$vars = array('sql_ary');
	extract($phpbb_dispatcher->trigger_event('core.display_forums_modify_sql', compact($vars)));

	$sql = $db->sql_build_query('SELECT', $sql_ary);
	$result = $db->sql_query($sql);

	$forum_tracking_info = array();
	$branch_root_id = $root_data['forum_id'];

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
		* @since 3.1-A1
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

		// Count the difference of real to public topics, so we can display an information to moderators
		$row['forum_id_unapproved_topics'] = ($auth->acl_get('m_approve', $forum_id) && ($row['forum_topics_real'] != $row['forum_topics'])) ? $forum_id : 0;
		$row['forum_topics'] = ($auth->acl_get('m_approve', $forum_id)) ? $row['forum_topics_real'] : $row['forum_topics'];

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
			$forum_rows[$parent_id]['orig_forum_last_post_time'] = $row['forum_last_post_time'];
		}
		else if ($row['forum_type'] != FORUM_CAT)
		{
			$subforums[$parent_id][$forum_id]['display'] = ($row['display_on_index']) ? true : false;
			$subforums[$parent_id][$forum_id]['name'] = $row['forum_name'];
			$subforums[$parent_id][$forum_id]['orig_forum_last_post_time'] = $row['forum_last_post_time'];
			$subforums[$parent_id][$forum_id]['children'] = array();

			if (isset($subforums[$parent_id][$row['parent_id']]) && !$row['display_on_index'])
			{
				$subforums[$parent_id][$row['parent_id']]['children'][] = $forum_id;
			}

			if (!$forum_rows[$parent_id]['forum_id_unapproved_topics'] && $row['forum_id_unapproved_topics'])
			{
				$forum_rows[$parent_id]['forum_id_unapproved_topics'] = $forum_id;
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
		* @since 3.1-A1
		*/
		$vars = array('forum_rows', 'subforums', 'branch_root_id', 'parent_id', 'row');
		extract($phpbb_dispatcher->trigger_event('core.display_forums_modify_forum_rows', compact($vars)));
	}
	$db->sql_freeresult($result);

	// Handle marking posts
	if ($mark_read == 'forums')
	{
		$redirect = build_url(array('mark', 'hash', 'mark_time'));
		$token = request_var('hash', '');
		if (check_link_hash($token, 'global'))
		{
			markread('topics', $forum_ids, false, request_var('mark_time', 0));
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
				$json_response = new phpbb_json_response();
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

	// Used to tell whatever we have to create a dummy category or not.
	$last_catless = true;
	foreach ($forum_rows as $row)
	{
		// Empty category
		if ($row['parent_id'] == $root_data['forum_id'] && $row['forum_type'] == FORUM_CAT)
		{
			$template->assign_block_vars('forumrow', array(
				'S_IS_CAT'				=> true,
				'FORUM_ID'				=> $row['forum_id'],
				'FORUM_NAME'			=> $row['forum_name'],
				'FORUM_DESC'			=> generate_text_for_display($row['forum_desc'], $row['forum_desc_uid'], $row['forum_desc_bitfield'], $row['forum_desc_options']),
				'FORUM_FOLDER_IMG'		=> '',
				'FORUM_FOLDER_IMG_SRC'	=> '',
				'FORUM_IMAGE'			=> ($row['forum_image']) ? '<img src="' . $phpbb_root_path . $row['forum_image'] . '" alt="' . $user->lang['FORUM_CAT'] . '" />' : '',
				'FORUM_IMAGE_SRC'		=> ($row['forum_image']) ? $phpbb_root_path . $row['forum_image'] : '',
				'U_VIEWFORUM'			=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $row['forum_id']))
			);

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

			$l_subforums = (sizeof($subforums[$forum_id]) == 1) ? $user->lang['SUBFORUM'] . ': ' : $user->lang['SUBFORUMS'] . ': ';
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
			$last_post_subject = $row['forum_last_post_subject'];
			$last_post_subject_truncated = truncate_string(censor_text($last_post_subject), 30, 255, false, $user->lang['ELLIPSIS']);
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
			$l_moderator = (sizeof($forum_moderators[$forum_id]) == 1) ? $user->lang['MODERATOR'] : $user->lang['MODERATORS'];
			$moderators_list = implode($user->lang['COMMA_SEPARATOR'], $forum_moderators[$forum_id]);
		}

		$l_post_click_count = ($row['forum_type'] == FORUM_LINK) ? 'CLICKS' : 'POSTS';
		$post_click_count = ($row['forum_type'] != FORUM_LINK || $row['forum_flags'] & FORUM_FLAG_LINK_TRACK) ? $row['forum_posts'] : '';

		$s_subforums_list = array();
		foreach ($subforums_list as $subforum)
		{
			$s_subforums_list[] = '<a href="' . $subforum['link'] . '" class="subforum ' . (($subforum['unread']) ? 'unread' : 'read') . '" title="' . (($subforum['unread']) ? $user->lang['UNREAD_POSTS'] : $user->lang['NO_UNREAD_POSTS']) . '">' . $subforum['name'] . '</a>';
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
			'S_SUBFORUMS'		=> (sizeof($subforums_list)) ? true : false,
			'S_DISPLAY_SUBJECT'	=>	($last_post_subject && $config['display_last_subject'] && !$row['forum_password'] && $auth->acl_get('f_read', $row['forum_id'])) ? true : false,
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
			'LAST_POST_SUBJECT'		=> (!$row['forum_password'] && $auth->acl_get('f_read', $row['forum_id'])) ? censor_text($last_post_subject) : "",
			'LAST_POST_SUBJECT_TRUNCATED'	=> (!$row['forum_password'] && $auth->acl_get('f_read', $row['forum_id'])) ? $last_post_subject_truncated : "",
			'LAST_POST_TIME'		=> $last_post_time,
			'LAST_POSTER'			=> get_username_string('username', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'LAST_POSTER_COLOUR'	=> get_username_string('colour', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'LAST_POSTER_FULL'		=> get_username_string('full', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
			'MODERATORS'			=> $moderators_list,
			'SUBFORUMS'				=> $s_subforums_list,

			'L_SUBFORUM_STR'		=> $l_subforums,
			'L_MODERATOR_STR'		=> $l_moderator,

			'U_UNAPPROVED_TOPICS'	=> ($row['forum_id_unapproved_topics']) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=unapproved_topics&amp;f=' . $row['forum_id_unapproved_topics']) : '',
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
		* @since 3.1-A1
		*/
		$vars = array('forum_row', 'row');
		extract($phpbb_dispatcher->trigger_event('core.display_forums_modify_template_vars', compact($vars)));

		$template->assign_block_vars('forumrow', $forum_row);

		// Assign subforums loop for style authors
		foreach ($subforums_list as $subforum)
		{
			$template->assign_block_vars('forumrow.subforum', array(
				'U_SUBFORUM'	=> $subforum['link'],
				'SUBFORUM_NAME'	=> $subforum['name'],
				'S_UNREAD'		=> $subforum['unread'])
			);
		}

		$last_catless = $catless;
	}

	$template->assign_vars(array(
		'U_MARK_FORUMS'		=> ($user->data['is_registered'] || $config['load_anon_lastread']) ? append_sid("{$phpbb_root_path}viewforum.$phpEx", 'hash=' . generate_link_hash('global') . '&amp;f=' . $root_data['forum_id'] . '&amp;mark=forums&amp;mark_time=' . time()) : '',
		'S_HAS_SUBFORUM'	=> ($visible_forums) ? true : false,
		'L_SUBFORUM'		=> ($visible_forums == 1) ? $user->lang['SUBFORUM'] : $user->lang['SUBFORUMS'],
		'LAST_POST_IMG'		=> $user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
		'UNAPPROVED_IMG'	=> $user->img('icon_topic_unapproved', 'TOPICS_UNAPPROVED'),
	));

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
	if (!$forum_data['forum_rules'] && !$forum_data['forum_rules_link'])
	{
		return;
	}

	global $template, $phpbb_root_path, $phpEx;

	if ($forum_data['forum_rules'])
	{
		$forum_data['forum_rules'] = generate_text_for_display($forum_data['forum_rules'], $forum_data['forum_rules_uid'], $forum_data['forum_rules_bitfield'], $forum_data['forum_rules_options']);
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
	global $db, $user, $template, $auth, $config;
	global $phpEx, $phpbb_root_path;

	if (!$auth->acl_get('f_list', $forum_data['forum_id']))
	{
		return;
	}

	// Get forum parents
	$forum_parents = get_forum_parents($forum_data);

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

			$template->assign_block_vars('navlinks', array(
				'S_IS_CAT'		=> ($parent_type == FORUM_CAT) ? true : false,
				'S_IS_LINK'		=> ($parent_type == FORUM_LINK) ? true : false,
				'S_IS_POST'		=> ($parent_type == FORUM_POST) ? true : false,
				'FORUM_NAME'	=> $parent_name,
				'FORUM_ID'		=> $parent_forum_id,
				'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $parent_forum_id))
			);
		}
	}

	$template->assign_block_vars('navlinks', array(
		'S_IS_CAT'		=> ($forum_data['forum_type'] == FORUM_CAT) ? true : false,
		'S_IS_LINK'		=> ($forum_data['forum_type'] == FORUM_LINK) ? true : false,
		'S_IS_POST'		=> ($forum_data['forum_type'] == FORUM_POST) ? true : false,
		'FORUM_NAME'	=> $forum_data['forum_name'],
		'FORUM_ID'		=> $forum_data['forum_id'],
		'U_VIEW_FORUM'	=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_data['forum_id']))
	);

	$template->assign_vars(array(
		'FORUM_ID' 		=> $forum_data['forum_id'],
		'FORUM_NAME'	=> $forum_data['forum_name'],
		'FORUM_DESC'	=> generate_text_for_display($forum_data['forum_desc'], $forum_data['forum_desc_uid'], $forum_data['forum_desc_bitfield'], $forum_data['forum_desc_options']),

		'S_ENABLE_FEEDS_FORUM'	=> ($config['feed_forum'] && $forum_data['forum_type'] == FORUM_POST && !phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $forum_data['forum_options'])) ? true : false,
	));

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
	global $config, $template, $db, $phpbb_root_path, $phpEx, $user, $auth;

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
			$group_name = (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']);

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
		($user->data['is_registered'] && $auth->acl_gets('f_delete', 'm_delete', $forum_id) && !$locked) ? $user->lang['RULES_DELETE_CAN'] : $user->lang['RULES_DELETE_CANNOT'],
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

	$folder = $folder_new = '';

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
* The custom bbcodes buttons will be placed within the template block 'custom_codes'
*/
function display_custom_bbcodes()
{
	global $db, $template, $user, $phpbb_dispatcher;

	// Start counting from 22 for the bbcode ids (every bbcode takes two ids - opening/closing)
	$num_predefined_bbcodes = 22;

	$sql = 'SELECT bbcode_id, bbcode_tag, bbcode_helpline
		FROM ' . BBCODES_TABLE . '
		WHERE display_on_posting = 1
		ORDER BY bbcode_tag';
	$result = $db->sql_query($sql);

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
			'BBCODE_HELPLINE'	=> $row['bbcode_helpline'],
			'A_BBCODE_HELPLINE'	=> str_replace(array('&amp;', '&quot;', "'", '&lt;', '&gt;'), array('&', '"', "\'", '<', '>'), $row['bbcode_helpline']),
		);

		/**
		* Modify the template data block of a bbcode
		*
		* This event is triggered once per bbcode
		*
		* @event core.display_custom_bbcodes_modify_row
		* @var	array	custom_tags		Template data of the bbcode
		* @var	array	row				The data of the bbcode
		* @since 3.1-A1
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
	* @since 3.1-A1
	*/
	$phpbb_dispatcher->dispatch('core.display_custom_bbcodes');
}

/**
* Display reasons
*/
function display_reasons($reason_id = 0)
{
	global $db, $user, $template;

	$sql = 'SELECT *
		FROM ' . REPORTS_REASONS_TABLE . '
		ORDER BY reason_order ASC';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		// If the reason is defined within the language file, we will use the localized version, else just use the database entry...
		if (isset($user->lang['report_reasons']['TITLE'][strtoupper($row['reason_title'])]) && isset($user->lang['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])]))
		{
			$row['reason_description'] = $user->lang['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])];
			$row['reason_title'] = $user->lang['report_reasons']['TITLE'][strtoupper($row['reason_title'])];
		}

		$template->assign_block_vars('reason', array(
			'ID'			=> $row['reason_id'],
			'TITLE'			=> $row['reason_title'],
			'DESCRIPTION'	=> $row['reason_description'],
			'S_SELECTED'	=> ($row['reason_id'] == $reason_id) ? true : false)
		);
	}
	$db->sql_freeresult($result);
}

/**
* Display user activity (action forum/topic)
*/
function display_user_activity(&$userdata)
{
	global $auth, $template, $db, $user;
	global $phpbb_root_path, $phpEx;

	// Do not display user activity for users having more than 5000 posts...
	if ($userdata['user_posts'] > 5000)
	{
		return;
	}

	$forum_ary = array();

	// Do not include those forums the user is not having read access to...
	$forum_read_ary = $auth->acl_getf('!f_read');

	foreach ($forum_read_ary as $forum_id => $not_allowed)
	{
		if ($not_allowed['f_read'])
		{
			$forum_ary[] = (int) $forum_id;
		}
	}

	$forum_ary = array_unique($forum_ary);
	$forum_sql = (sizeof($forum_ary)) ? 'AND ' . $db->sql_in_set('forum_id', $forum_ary, true) : '';

	$fid_m_approve = $auth->acl_getf('m_approve', true);
	$sql_m_approve = (!empty($fid_m_approve)) ? 'OR ' . $db->sql_in_set('forum_id', array_keys($fid_m_approve)) : '';

	// Obtain active forum
	$sql = 'SELECT forum_id, COUNT(post_id) AS num_posts
		FROM ' . POSTS_TABLE . '
		WHERE poster_id = ' . $userdata['user_id'] . "
			AND post_postcount = 1
			AND (post_approved = 1
				$sql_m_approve)
			$forum_sql
		GROUP BY forum_id
		ORDER BY num_posts DESC";
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
	// We need to exclude passworded forums here so we do not leak the topic title
	$forum_ary_topic = array_unique(array_merge($forum_ary, $user->get_passworded_forums()));
	$forum_sql_topic = (!empty($forum_ary_topic)) ? 'AND ' . $db->sql_in_set('forum_id', $forum_ary_topic, true) : '';

	$sql = 'SELECT topic_id, COUNT(post_id) AS num_posts
		FROM ' . POSTS_TABLE . '
		WHERE poster_id = ' . $userdata['user_id'] . "
			AND post_postcount = 1
			AND (post_approved = 1
				$sql_m_approve)
			$forum_sql_topic
		GROUP BY topic_id
		ORDER BY num_posts DESC";
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

	$userdata['active_t_row'] = $active_t_row;
	$userdata['active_f_row'] = $active_f_row;

	$active_f_name = $active_f_id = $active_f_count = $active_f_pct = '';
	if (!empty($active_f_row['num_posts']))
	{
		$active_f_name = $active_f_row['forum_name'];
		$active_f_id = $active_f_row['forum_id'];
		$active_f_count = $active_f_row['num_posts'];
		$active_f_pct = ($userdata['user_posts']) ? ($active_f_count / $userdata['user_posts']) * 100 : 0;
	}

	$active_t_name = $active_t_id = $active_t_count = $active_t_pct = '';
	if (!empty($active_t_row['num_posts']))
	{
		$active_t_name = $active_t_row['topic_title'];
		$active_t_id = $active_t_row['topic_id'];
		$active_t_count = $active_t_row['num_posts'];
		$active_t_pct = ($userdata['user_posts']) ? ($active_t_count / $userdata['user_posts']) * 100 : 0;
	}

	$l_active_pct = ($userdata['user_id'] != ANONYMOUS && $userdata['user_id'] == $user->data['user_id']) ? $user->lang['POST_PCT_ACTIVE_OWN'] : $user->lang['POST_PCT_ACTIVE'];

	$template->assign_vars(array(
		'ACTIVE_FORUM'			=> $active_f_name,
		'ACTIVE_FORUM_POSTS'	=> $user->lang('USER_POSTS', (int) $active_f_count),
		'ACTIVE_FORUM_PCT'		=> sprintf($l_active_pct, $active_f_pct),
		'ACTIVE_TOPIC'			=> censor_text($active_t_name),
		'ACTIVE_TOPIC_POSTS'	=> $user->lang('USER_POSTS', (int) $active_t_count),
		'ACTIVE_TOPIC_PCT'		=> sprintf($l_active_pct, $active_t_pct),
		'U_ACTIVE_FORUM'		=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $active_f_id),
		'U_ACTIVE_TOPIC'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . $active_t_id),
		'S_SHOW_ACTIVITY'		=> true)
	);
}

/**
* Topic and forum watching common code
*/
function watch_topic_forum($mode, &$s_watching, $user_id, $forum_id, $topic_id, $notify_status = 'unset', $start = 0, $item_title = '')
{
	global $template, $db, $user, $phpEx, $start, $phpbb_root_path;
	global $request;

	$table_sql = ($mode == 'forum') ? FORUMS_WATCH_TABLE : TOPICS_WATCH_TABLE;
	$where_sql = ($mode == 'forum') ? 'forum_id' : 'topic_id';
	$match_id = ($mode == 'forum') ? $forum_id : $topic_id;
	$u_url = "uid={$user->data['user_id']}";
	$u_url .= ($mode == 'forum') ? '&amp;f' : '&amp;f=' . $forum_id . '&amp;t';
	$is_watching = 0;

	// Is user watching this thread?
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
				$uid = request_var('uid', 0);
				$token = request_var('hash', '');

				if ($token && check_link_hash($token, "{$mode}_$match_id") || confirm_box(true))
				{
					if ($uid != $user_id || $request->variable('unwatch', '', false, phpbb_request_interface::GET) != $mode)
					{
						$redirect_url = append_sid("{$phpbb_root_path}view$mode.$phpEx", "$u_url=$match_id&amp;start=$start");
						$message = $user->lang['ERR_UNWATCHING'] . '<br /><br />' . sprintf($user->lang['RETURN_' . strtoupper($mode)], '<a href="' . $redirect_url . '">', '</a>');
						trigger_error($message);
					}

					$sql = 'DELETE FROM ' . $table_sql . "
						WHERE $where_sql = $match_id
							AND user_id = $user_id";
					$db->sql_query($sql);

					$redirect_url = append_sid("{$phpbb_root_path}view$mode.$phpEx", "$u_url=$match_id&amp;start=$start");
					$message = $user->lang['NOT_WATCHING_' . strtoupper($mode)] . '<br /><br />';
					$message .= sprintf($user->lang['RETURN_' . strtoupper($mode)], '<a href="' . $redirect_url . '">', '</a>');
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
				$uid = request_var('uid', 0);
				$token = request_var('hash', '');

				if ($token && check_link_hash($token, "{$mode}_$match_id") || confirm_box(true))
				{
					if ($uid != $user_id || $request->variable('watch', '', false, phpbb_request_interface::GET) != $mode)
					{
						$redirect_url = append_sid("{$phpbb_root_path}view$mode.$phpEx", "$u_url=$match_id&amp;start=$start");
						$message = $user->lang['ERR_WATCHING'] . '<br /><br />' . sprintf($user->lang['RETURN_' . strtoupper($mode)], '<a href="' . $redirect_url . '">', '</a>');
						trigger_error($message);
					}

					$is_watching = true;

					$sql = 'INSERT INTO ' . $table_sql . " (user_id, $where_sql, notify_status)
						VALUES ($user_id, $match_id, " . NOTIFY_YES . ')';
					$db->sql_query($sql);

					$redirect_url = append_sid("{$phpbb_root_path}view$mode.$phpEx", "$u_url=$match_id&amp;start=$start");
					$message = $user->lang['ARE_WATCHING_' . strtoupper($mode)] . '<br /><br />' . sprintf($user->lang['RETURN_' . strtoupper($mode)], '<a href="' . $redirect_url . '">', '</a>');
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
		if ((isset($_GET['unwatch']) && $request->variable('unwatch', '', false, phpbb_request_interface::GET) == $mode) ||
			(isset($_GET['watch']) && $request->variable('watch', '', false, phpbb_request_interface::GET) == $mode))
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
* @param int $user_rank the current stored users rank id
* @param int $user_posts the users number of posts
* @param string &$rank_title the rank title will be stored here after execution
* @param string &$rank_img the rank image as full img tag is stored here after execution
* @param string &$rank_img_src the rank image source is stored here after execution
*
* Note: since we do not want to break backwards-compatibility, this function will only properly assign ranks to guests if you call it for them with user_posts == false
*/
function get_user_rank($user_rank, $user_posts, &$rank_title, &$rank_img, &$rank_img_src)
{
	global $ranks, $config, $phpbb_root_path;

	if (empty($ranks))
	{
		global $cache;
		$ranks = $cache->obtain_ranks();
	}

	if (!empty($user_rank))
	{
		$rank_title = (isset($ranks['special'][$user_rank]['rank_title'])) ? $ranks['special'][$user_rank]['rank_title'] : '';
		$rank_img = (!empty($ranks['special'][$user_rank]['rank_image'])) ? '<img src="' . $phpbb_root_path . $config['ranks_path'] . '/' . $ranks['special'][$user_rank]['rank_image'] . '" alt="' . $ranks['special'][$user_rank]['rank_title'] . '" title="' . $ranks['special'][$user_rank]['rank_title'] . '" />' : '';
		$rank_img_src = (!empty($ranks['special'][$user_rank]['rank_image'])) ? $phpbb_root_path . $config['ranks_path'] . '/' . $ranks['special'][$user_rank]['rank_image'] : '';
	}
	else if ($user_posts !== false)
	{
		if (!empty($ranks['normal']))
		{
			foreach ($ranks['normal'] as $rank)
			{
				if ($user_posts >= $rank['rank_min'])
				{
					$rank_title = $rank['rank_title'];
					$rank_img = (!empty($rank['rank_image'])) ? '<img src="' . $phpbb_root_path . $config['ranks_path'] . '/' . $rank['rank_image'] . '" alt="' . $rank['rank_title'] . '" title="' . $rank['rank_title'] . '" />' : '';
					$rank_img_src = (!empty($rank['rank_image'])) ? $phpbb_root_path . $config['ranks_path'] . '/' . $rank['rank_image'] : '';
					break;
				}
			}
		}
	}
}

/**
* Get user avatar
*
* @param array $user_row Row from the users table
* @param string $alt Optional language string for alt tag within image, can be a language key or text
* @param bool $ignore_config Ignores the config-setting, to be still able to view the avatar in the UCP
*
* @return string Avatar html
*/
function phpbb_get_user_avatar($user_row, $alt = 'USER_AVATAR', $ignore_config = false)
{
	$row = phpbb_avatar_manager::clean_row($user_row);
	return phpbb_get_avatar($row, $alt, $ignore_config);
}

/**
* Get group avatar
*
* @param array $group_row Row from the groups table
* @param string $alt Optional language string for alt tag within image, can be a language key or text
* @param bool $ignore_config Ignores the config-setting, to be still able to view the avatar in the UCP
*
* @return string Avatar html
*/
function phpbb_get_group_avatar($user_row, $alt = 'GROUP_AVATAR', $ignore_config = false)
{
	$row = phpbb_avatar_manager::clean_row($user_row);
	return phpbb_get_avatar($row, $alt, $ignore_config);
}

/**
* Get avatar
*
* @param array $row Row cleaned by phpbb_avatar_driver::clean_row
* @param string $alt Optional language string for alt tag within image, can be a language key or text
* @param bool $ignore_config Ignores the config-setting, to be still able to view the avatar in the UCP
*
* @return string Avatar html
*/
function phpbb_get_avatar($row, $alt, $ignore_config = false)
{
	global $user, $config, $cache, $phpbb_root_path, $phpEx;
	global $request;
	global $phpbb_container;

	if (!$config['allow_avatar'] && !$ignore_config)
	{
		return '';
	}

	$avatar_data = array(
		'src' => $row['avatar'],
		'width' => $row['avatar_width'],
		'height' => $row['avatar_height'],
	);

	$phpbb_avatar_manager = $phpbb_container->get('avatar.manager');
	$driver = $phpbb_avatar_manager->get_driver($row['avatar_type'], $ignore_config);
	$html = '';

	if ($driver)
	{
		$html = $driver->get_custom_html($user, $row, $alt);
		if (!empty($html))
		{
			return $html;
		}

		$avatar_data = $driver->get_data($row, $ignore_config);
	}
	else
	{
		$avatar_data['src'] = '';
	}

	if (!empty($avatar_data['src']))
	{
		$html = '<img src="' . $avatar_data['src'] . '" ' .
			($avatar_data['width'] ? ('width="' . $avatar_data['width'] . '" ') : '') .
			($avatar_data['height'] ? ('height="' . $avatar_data['height'] . '" ') : '') .
			'alt="' . ((!empty($user->lang[$alt])) ? $user->lang[$alt] : $alt) . '" />';
	}

	return $html;
}

/**
* Generate a list of archive types available for compressing attachments
*
* @param string $param_key Either topic_id or post_id
* @param string $param_val The value of the topic or post id
* @param string $phpbb_root_path The root path of the phpBB installation
* @param string $phpEx The PHP extension
*
* @return array Array containing the link and the type of compression
*/
function phpbb_gen_download_links($param_key, $param_val, $phpbb_root_path, $phpEx)
{
	if (!class_exists('compress'))
	{
		require $phpbb_root_path . 'includes/functions_compress.' . $phpEx;
	}

	$methods = compress::methods();
	$links = array();

	foreach ($methods as $method)
	{
		$exploded = explode('.', $method);
		$type = array_pop($exploded);
		$params = array('archive' => $method);
		$params[$param_key] = $param_val;

		$links[] = array(
			'LINK' => append_sid("{$phpbb_root_path}download/file.$phpEx", $params),
			'TYPE' => $type,
		);
	}

	return $links;
}
