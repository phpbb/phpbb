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

namespace phpbb\topic\view;

class topic_list_renderer
{
	public function __construct()
	{

	}

	public function render_topic_list(array $rowset, array $forum_tracking_info, $config, \phpbb\user $user, array $topic_tracking_info, $s_display_active, array $forum_data, array $tracking_topics, $forum_id, array $topic_list, \phpbb\content_visibility $phpbb_content_visibility, $auth, $phpbb_root_path, $phpEx, $icons, \phpbb\event\dispatcher $phpbb_dispatcher, \phpbb\template\template $template, \phpbb\pagination $pagination)
	{
		$mark_forum_read = true;
		$mark_time_forum = 0;

		// Generate topic forum list...
		$topic_forum_list = array();
		foreach ($rowset as $t_id => $row)
		{
			if (isset($forum_tracking_info[$row['forum_id']]))
			{
				$row['forum_mark_time'] = $forum_tracking_info[$row['forum_id']];
			}

			$topic_forum_list[$row['forum_id']]['forum_mark_time'] = ($config['load_db_lastread'] && $user->data['is_registered'] && isset($row['forum_mark_time'])) ? $row['forum_mark_time'] : 0;
			$topic_forum_list[$row['forum_id']]['topics'][] = (int) $t_id;
		}

		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			foreach ($topic_forum_list as $f_id => $topic_row)
			{
				$topic_tracking_info += get_topic_tracking($f_id, $topic_row['topics'], $rowset, array($f_id => $topic_row['forum_mark_time']));
			}
		}
		else if ($config['load_anon_lastread'] || $user->data['is_registered'])
		{
			foreach ($topic_forum_list as $f_id => $topic_row)
			{
				$topic_tracking_info += get_complete_topic_tracking($f_id, $topic_row['topics']);
			}
		}

		unset($topic_forum_list);

		if (!$s_display_active)
		{
			if ($config['load_db_lastread'] && $user->data['is_registered'])
			{
				$mark_time_forum = (!empty($forum_data['mark_time'])) ? $forum_data['mark_time'] : $user->data['user_lastmark'];
			}
			else if ($config['load_anon_lastread'] || $user->data['is_registered'])
			{
				if (!$user->data['is_registered'])
				{
					$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? (int) (base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate']) : 0;
				}
				$mark_time_forum = (isset($tracking_topics['f'][$forum_id])) ? (int) (base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate']) : $user->data['user_lastmark'];
			}
		}

		$s_type_switch = 0;
		foreach ($topic_list as $topic_id)
		{
			list($rowset, $row, $mark_forum_read, $s_type_switch) = $this->render_topic_list_element($rowset, $config, $user, $topic_tracking_info, $forum_data, $forum_id, $phpbb_content_visibility, $auth, $phpbb_root_path, $phpEx, $icons, $phpbb_dispatcher, $template, $pagination, $topic_id, $row, $s_type_switch, $mark_forum_read);
		}
		return array($mark_forum_read, $mark_time_forum, $forum_data);
	}

	public function render_topic_list_element(array $rowset, $config, \phpbb\user $user, array $topic_tracking_info, array $forum_data, $forum_id, \phpbb\content_visibility $phpbb_content_visibility, $auth, $phpbb_root_path, $phpEx, $icons, \phpbb\event\dispatcher $phpbb_dispatcher, \phpbb\template\template $template, \phpbb\pagination $pagination, $topic_id, $row, $s_type_switch, $mark_forum_read)
	{
		$row = &$rowset[$topic_id];

		$topic_forum_id = ($row['forum_id']) ? (int) $row['forum_id'] : $forum_id;

		// This will allow the style designer to output a different header
		// or even separate the list of announcements from sticky and normal topics
		$s_type_switch_test = ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL) ? 1 : 0;

		// Replies
		$replies = $phpbb_content_visibility->get_count('topic_posts', $row, $topic_forum_id) - 1;
		// Correction for case of unapproved topic visible to poster
		if ($replies < 0)
		{
			$replies = 0;
		}

		if ($row['topic_status'] == ITEM_MOVED)
		{
			$topic_id = $row['topic_moved_id'];
			$unread_topic = false;
		}
		else
		{
			$unread_topic = (isset($topic_tracking_info[$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;
		}

		// Get folder img, topic status/type related information
		$folder_img = $folder_alt = $topic_type = '';
		topic_status($row, $replies, $unread_topic, $folder_img, $folder_alt, $topic_type);

		// Generate all the URIs ...
		$view_topic_url_params = 'f=' . $row['forum_id'] . '&amp;t=' . $topic_id;
		$view_topic_url = $auth->acl_get('f_read', $forum_id) ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", $view_topic_url_params) : false;

		$topic_unapproved = (($row['topic_visibility'] == ITEM_UNAPPROVED || $row['topic_visibility'] == ITEM_REAPPROVE) && $auth->acl_get('m_approve', $row['forum_id']));
		$posts_unapproved = ($row['topic_visibility'] == ITEM_APPROVED && $row['topic_posts_unapproved'] && $auth->acl_get('m_approve', $row['forum_id']));
		$topic_deleted = $row['topic_visibility'] == ITEM_DELETED;

		$u_mcp_queue = ($topic_unapproved || $posts_unapproved) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=' . (($topic_unapproved) ? 'approve_details' : 'unapproved_posts') . "&amp;t=$topic_id", true, $user->session_id) : '';
		$u_mcp_queue = (!$u_mcp_queue && $topic_deleted) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=deleted_topics&amp;t=' . $topic_id, true, $user->session_id) : $u_mcp_queue;

		// Send vars to template
		$topic_row = array(
			'FORUM_ID'					=> $row['forum_id'],
			'TOPIC_ID'					=> $topic_id,
			'TOPIC_AUTHOR'				=> get_username_string('username', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'TOPIC_AUTHOR_COLOUR'		=> get_username_string('colour', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'TOPIC_AUTHOR_FULL'			=> get_username_string('full', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'FIRST_POST_TIME'			=> $user->format_date($row['topic_time']),
			'FIRST_POST_TIME_RFC3339'	=> gmdate(DATE_RFC3339, $row['topic_time']),
			'LAST_POST_SUBJECT'			=> censor_text($row['topic_last_post_subject']),
			'LAST_POST_TIME'			=> $user->format_date($row['topic_last_post_time']),
			'LAST_POST_TIME_RFC3339'	=> gmdate(DATE_RFC3339, $row['topic_last_post_time']),
			'LAST_VIEW_TIME'			=> $user->format_date($row['topic_last_view_time']),
			'LAST_VIEW_TIME_RFC3339'	=> gmdate(DATE_RFC3339, $row['topic_last_view_time']),
			'LAST_POST_AUTHOR'			=> get_username_string('username', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
			'LAST_POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
			'LAST_POST_AUTHOR_FULL'		=> get_username_string('full', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),

			'REPLIES'			=> $replies,
			'VIEWS'				=> $row['topic_views'],
			'TOPIC_TITLE'		=> censor_text($row['topic_title']),
			'TOPIC_TYPE'		=> $topic_type,
			'FORUM_NAME'		=> (isset($row['forum_name'])) ? $row['forum_name'] : $forum_data['forum_name'],

			'TOPIC_IMG_STYLE'		=> $folder_img,
			'TOPIC_FOLDER_IMG'		=> $user->img($folder_img, $folder_alt),
			'TOPIC_FOLDER_IMG_ALT'	=> $user->lang[$folder_alt],

			'TOPIC_ICON_IMG'		=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : '',
			'TOPIC_ICON_IMG_WIDTH'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['width'] : '',
			'TOPIC_ICON_IMG_HEIGHT'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['height'] : '',
			'ATTACH_ICON_IMG'		=> ($auth->acl_get('u_download') && $auth->acl_get('f_download', $row['forum_id']) && $row['topic_attachment']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',
			'UNAPPROVED_IMG'		=> ($topic_unapproved || $posts_unapproved) ? $user->img('icon_topic_unapproved', ($topic_unapproved) ? 'TOPIC_UNAPPROVED' : 'POSTS_UNAPPROVED') : '',

			'S_TOPIC_TYPE'			=> $row['topic_type'],
			'S_USER_POSTED'			=> (isset($row['topic_posted']) && $row['topic_posted']) ? true : false,
			'S_UNREAD_TOPIC'		=> $unread_topic,
			'S_TOPIC_REPORTED'		=> (!empty($row['topic_reported']) && $auth->acl_get('m_report', $row['forum_id'])) ? true : false,
			'S_TOPIC_UNAPPROVED'	=> $topic_unapproved,
			'S_POSTS_UNAPPROVED'	=> $posts_unapproved,
			'S_TOPIC_DELETED'		=> $topic_deleted,
			'S_HAS_POLL'			=> ($row['poll_start']) ? true : false,
			'S_POST_ANNOUNCE'		=> ($row['topic_type'] == POST_ANNOUNCE) ? true : false,
			'S_POST_GLOBAL'			=> ($row['topic_type'] == POST_GLOBAL) ? true : false,
			'S_POST_STICKY'			=> ($row['topic_type'] == POST_STICKY) ? true : false,
			'S_TOPIC_LOCKED'		=> ($row['topic_status'] == ITEM_LOCKED) ? true : false,
			'S_TOPIC_MOVED'			=> ($row['topic_status'] == ITEM_MOVED) ? true : false,

			'U_NEWEST_POST'			=> $auth->acl_get('f_read', $forum_id) ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", $view_topic_url_params . '&amp;view=unread') . '#unread' : false,
			'U_LAST_POST'			=> $auth->acl_get('f_read', $forum_id)  ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", $view_topic_url_params . '&amp;p=' . $row['topic_last_post_id']) . '#p' . $row['topic_last_post_id'] : false,
			'U_LAST_POST_AUTHOR'	=> get_username_string('profile', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
			'U_TOPIC_AUTHOR'		=> get_username_string('profile', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'U_VIEW_TOPIC'			=> $view_topic_url,
			'U_VIEW_FORUM'			=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $row['forum_id']),
			'U_MCP_REPORT'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports&amp;mode=reports&amp;f=' . $row['forum_id'] . '&amp;t=' . $topic_id, true, $user->session_id),
			'U_MCP_QUEUE'			=> $u_mcp_queue,

			'S_TOPIC_TYPE_SWITCH'	=> ($s_type_switch == $s_type_switch_test) ? -1 : $s_type_switch_test,
		);

		/**
		 * Modify the topic data before it is assigned to the template
		 *
		 * @event core.viewforum_modify_topicrow
		 * @var	array	row					Array with topic data
		 * @var	array	topic_row			Template array with topic data
		 * @var	bool	s_type_switch		Flag indicating if the topic type is [global] announcement
		 * @var	bool	s_type_switch_test	Flag indicating if the test topic type is [global] announcement
		 * @since 3.1.0-a1
		 *
		 * @changed 3.1.10-RC1 Added s_type_switch, s_type_switch_test
		 */
		$vars = array('row', 'topic_row', 's_type_switch', 's_type_switch_test');
		extract($phpbb_dispatcher->trigger_event('core.viewforum_modify_topicrow', compact($vars)));

		$template->assign_block_vars('topicrow', $topic_row);

		$pagination->generate_template_pagination($view_topic_url, 'topicrow.pagination', 'start', $replies + 1, $config['posts_per_page'], 1, true, true);

		$s_type_switch = ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL) ? 1 : 0;

		/**
		 * Event after the topic data has been assigned to the template
		 *
		 * @event core.viewforum_topic_row_after
		 * @var	array	row				Array with the topic data
		 * @var	array	rowset			Array with topics data (in topic_id => topic_data format)
		 * @var	bool	s_type_switch	Flag indicating if the topic type is [global] announcement
		 * @var	int		topic_id		The topic ID
		 * @var	array	topic_list		Array with current viewforum page topic ids
		 * @var	array	topic_row		Template array with topic data
		 * @since 3.1.3-RC1
		 */
		$vars = array(
			'row',
			'rowset',
			's_type_switch',
			'topic_id',
			'topic_list',
			'topic_row',
		);
		extract($phpbb_dispatcher->trigger_event('core.viewforum_topic_row_after', compact($vars)));

		if ($unread_topic)
		{
			$mark_forum_read = false;
		}

		unset($rowset[$topic_id]);

		return array($rowset, $row, $mark_forum_read, $s_type_switch);
	}
}
