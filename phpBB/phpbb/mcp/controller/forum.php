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

namespace phpbb\mcp\controller;

class forum
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache_service;

	/** @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth					Auth object
	 * @param \phpbb\cache\service				$cache_service			Cache service object
	 * @param \phpbb\config\config				$config					Config object
	 * @param \phpbb\content_visibility			$content_visibility		Content visibility object
	 * @param \phpbb\db\driver\driver_interface	$db						Database object
	 * @param \phpbb\event\dispatcher			$dispatcher				Event dispatcher object
	 * @param \phpbb\language\language			$lang					Language object
	 * @param \phpbb\log\log					$log					Log object
	 * @param \phpbb\pagination					$pagination				Pagination object
	 * @param \phpbb\request\request			$request				Request object
	 * @param \phpbb\template\template			$template				Template object
	 * @param \phpbb\user						$user					User object
	 * @param string							$root_path				phpBB root path
	 * @param string							$php_ext				php File extension
	 * @param array								$tables					phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\cache\service $cache_service,
		\phpbb\config\config $config,
		\phpbb\content_visibility $content_visibility,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth					= $auth;
		$this->cache_service		= $cache_service;
		$this->config				= $config;
		$this->content_visibility	= $content_visibility;
		$this->db					= $db;
		$this->dispatcher			= $dispatcher;
		$this->lang					= $lang;
		$this->log					= $log;
		$this->pagination			= $pagination;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	public function view($id, $mode, $action, $forum_info)
	{
		$this->lang->add_lang(['viewtopic', 'viewforum']);

		include_once($this->root_path . 'includes/functions_display.' . $this->php_ext);

		// merge_topic is the quick mod action, merge_topics is the mcp_forum action, and merge_select is the mcp_topic action
		$merge_select = ($action === 'merge_select' || $action === 'merge_topic' || $action === 'merge_topics') ? true : false;

		$forum_id			= $forum_info['forum_id'];
		$start				= $this->request->variable('start', 0);
		$topic_id_list		= $this->request->variable('topic_id_list', [0]);
		$post_id_list		= $this->request->variable('post_id_list', [0]);
		$source_topic_ids	= [$this->request->variable('t', 0)];
		$to_topic_id		= $this->request->variable('to_topic_id', 0);

		$url_extra = '';
		$url_extra .= $forum_id ? "&amp;f=$forum_id" : '';
		$url_extra .= $GLOBALS['topic_id'] ? '&amp;t=' . $GLOBALS['topic_id'] : '';
		$url_extra .= $GLOBALS['post_id'] ? '&amp;p=' . $GLOBALS['post_id'] : '';
		$url_extra .= $GLOBALS['user_id'] ? '&amp;u=' . $GLOBALS['user_id'] : '';

		$url = append_sid("{$this->root_path}mcp.$this->php_ext?$url_extra");

		// Resync Topics
		switch ($action)
		{
			case 'resync':
				$topic_ids = $this->request->variable('topic_id_list', [0]);
				$this->resync_topics($topic_ids);
			break;

			/** @noinspection PhpMissingBreakStatementInspection */
			case 'merge_topics':
				$source_topic_ids = $topic_id_list;
			// no break;

			case 'merge_topic':
				if ($to_topic_id)
				{
					$this->merge_topics($forum_id, $source_topic_ids, $to_topic_id);
				}
			break;
		}

		/**
		 * Get some data in order to execute other actions.
		 *
		 * @event core.mcp_forum_view_before
		 * @var	string	action				The action
		 * @var	array	forum_info			Array with forum infos
		 * @var	int		start				Start value
		 * @var	array	topic_id_list		Array of topics ids
		 * @var	array	post_id_list		Array of posts ids
		 * @var	array	source_topic_ids	Array of source topics ids
		 * @var	int		to_topic_id			Array of destination topics ids
		 * @since 3.1.6-RC1
		 */
		$vars = [
			'action',
			'forum_info',
			'start',
			'topic_id_list',
			'post_id_list',
			'source_topic_ids',
			'to_topic_id',
		];
		extract($this->dispatcher->trigger_event('core.mcp_forum_view_before', compact($vars)));

		$selected_ids = '';
		if (!empty($post_id_list) && $action !=='merge_topics')
		{
			foreach ($post_id_list as $num => $post_id)
			{
				$selected_ids .= '&amp;post_id_list[' . $num . ']=' . $post_id;
			}
		}
		else if (!empty($topic_id_list) && $action === 'merge_topics')
		{
			foreach ($topic_id_list as $num => $topic_id)
			{
				$selected_ids .= '&amp;topic_id_list[' . $num . ']=' . $topic_id;
			}
		}

		make_jumpbox($url . "&amp;i=$id&amp;action=$action&amp;mode=$mode" . ($merge_select ? $selected_ids : ''), $forum_id, false, 'm_', true);

		$topics_per_page = ($forum_info['forum_topics_per_page']) ? $forum_info['forum_topics_per_page'] : $this->config['topics_per_page'];

		$sort_days = $total = 0;
		$sort_key = $sort_dir = '';
		$sort_by_sql = $sort_order_sql = [];
		phpbb_mcp_sorting('viewforum', $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $forum_id);

		$forum_topics = $total === -1 ? $forum_info['forum_topics_approved'] : $total;
		$limit_time_sql = $sort_days ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';

		$base_url = $url . "&amp;i=$id&amp;action=$action&amp;mode=$mode&amp;sd=$sort_dir&amp;sk=$sort_key&amp;st=$sort_days" . ($merge_select ? $selected_ids : '');
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $forum_topics, $topics_per_page, $start);

		/** @todo Check what the correct replacement is. "a_viewlogs" used for now. */
		// $s_view_forum_logs = $module->loaded('logs');
		$s_view_forum_logs = $this->auth->acl_get('a_viewlogs');

		$this->template->assign_vars([
			'ACTION'				=> $action,
			'FORUM_NAME'			=> $forum_info['forum_name'],
			'FORUM_DESCRIPTION'		=> generate_text_for_display($forum_info['forum_desc'], $forum_info['forum_desc_uid'], $forum_info['forum_desc_bitfield'], $forum_info['forum_desc_options']),

			'REPORTED_IMG'			=> $this->user->img('icon_topic_reported', 'TOPIC_REPORTED'),
			'UNAPPROVED_IMG'		=> $this->user->img('icon_topic_unapproved', 'TOPIC_UNAPPROVED'),
			'LAST_POST_IMG'			=> $this->user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
			'NEWEST_POST_IMG'		=> $this->user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),

			'S_CAN_REPORT'			=> $this->auth->acl_get('m_report', $forum_id),
			'S_CAN_DELETE'			=> $this->auth->acl_get('m_delete', $forum_id),
			'S_CAN_RESTORE'			=> $this->auth->acl_get('m_approve', $forum_id),
			'S_CAN_MERGE'			=> $this->auth->acl_get('m_merge', $forum_id),
			'S_CAN_MOVE'			=> $this->auth->acl_get('m_move', $forum_id),
			'S_CAN_FORK'			=> $this->auth->acl_get('m_', $forum_id),
			'S_CAN_LOCK'			=> $this->auth->acl_get('m_lock', $forum_id),
			'S_CAN_SYNC'			=> $this->auth->acl_get('m_', $forum_id),
			'S_CAN_APPROVE'			=> $this->auth->acl_get('m_approve', $forum_id),
			'S_MERGE_SELECT'		=> $merge_select ? true : false,
			'S_CAN_MAKE_NORMAL'		=> $this->auth->acl_gets('f_sticky', 'f_announce', 'f_announce_global', $forum_id),
			'S_CAN_MAKE_STICKY'		=> $this->auth->acl_get('f_sticky', $forum_id),
			'S_CAN_MAKE_ANNOUNCE'	=> $this->auth->acl_get('f_announce', $forum_id),
			'S_CAN_MAKE_ANNOUNCE_GLOBAL'	=> $this->auth->acl_get('f_announce_global', $forum_id),

			'U_VIEW_FORUM'			=> append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $forum_id),
			'U_VIEW_FORUM_LOGS'		=> ($this->auth->acl_gets('a_', 'm_', $forum_id) && $s_view_forum_logs) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=logs&amp;mode=forum_logs&amp;f=' . $forum_id) : '',

			'S_MCP_ACTION'			=> $url . "&amp;i=$id&amp;forum_action=$action&amp;mode=$mode&amp;start=$start" . ($merge_select ? $selected_ids : ''),

			'TOTAL_TOPICS'			=> $this->lang->lang('VIEW_FORUM_TOPICS', (int) $forum_topics),
		]);

		// Grab icons
		$icons = $this->cache_service->obtain_icons();

		$topic_rows = [];

		if ($this->config['load_db_lastread'])
		{
			$read_tracking_join = ' LEFT JOIN ' . $this->tables['topics_track'] . ' tt ON (tt.topic_id = t.topic_id AND tt.user_id = ' . $this->user->data['user_id'] . ')';
			$read_tracking_select = ', tt.mark_time';
		}
		else
		{
			$read_tracking_join = $read_tracking_select = '';
		}

		$topic_list = $topic_tracking_info = [];

		$sql = 'SELECT t.topic_id
			FROM ' . $this->tables['topics'] . ' t
			WHERE t.forum_id = ' . (int) $forum_id . '
				AND ' . $this->content_visibility->get_visibility_sql('topic', $forum_id, 't.') . "
				$limit_time_sql
			ORDER BY t.topic_type DESC, $sort_order_sql";

		/**
		 * Modify SQL query before MCP forum view topic list is queried
		 *
		 * @event core.mcp_view_forum_modify_sql
		 * @var	string	sql					SQL query for forum view topic list
		 * @var	int		forum_id			ID of the forum
		 * @var	string	limit_time_sql		SQL query part for limit time
		 * @var	string	sort_order_sql		SQL query part for sort order
		 * @var	int		topics_per_page		Number of topics per page
		 * @var	int		start				Start value
		 * @since 3.1.2-RC1
		 */
		$vars = ['sql', 'forum_id', 'limit_time_sql', 'sort_order_sql', 'topics_per_page', 'start'];
		extract($this->dispatcher->trigger_event('core.mcp_view_forum_modify_sql', compact($vars)));

		$result = $this->db->sql_query_limit($sql, $topics_per_page, $start);
		while ($row_ary = $this->db->sql_fetchrow($result))
		{
			$topic_list[] = $row_ary['topic_id'];
		}
		$this->db->sql_freeresult($result);

		$sql = "SELECT t.*$read_tracking_select
			FROM " . $this->tables['topics'] . " t $read_tracking_join
			WHERE " . $this->db->sql_in_set('t.topic_id', $topic_list, false, true);
		$result = $this->db->sql_query($sql);
		while ($row_ary = $this->db->sql_fetchrow($result))
		{
			$topic_rows[$row_ary['topic_id']] = $row_ary;
		}
		$this->db->sql_freeresult($result);

		// If there is more than one page, but we have no topic list, then the start parameter is... erm... out of sync
		if (empty($topic_list) && $forum_topics && $start > 0)
		{
			redirect($url . "&amp;i=$id&amp;action=$action&amp;mode=$mode");
		}

		// Get topic tracking info
		if (!empty($topic_list))
		{
			if ($this->config['load_db_lastread'])
			{
				$topic_tracking_info = get_topic_tracking($forum_id, $topic_list, $topic_rows, [$forum_id => $forum_info['mark_time']]);
			}
			else
			{
				$topic_tracking_info = get_complete_topic_tracking($forum_id, $topic_list);
			}
		}

		foreach ($topic_list as $topic_id)
		{
			$row_ary = &$topic_rows[$topic_id];

			$replies = $this->content_visibility->get_count('topic_posts', $row_ary, $forum_id) - 1;

			if ($row_ary['topic_status'] == ITEM_MOVED)
			{
				$unread_topic = false;
			}
			else
			{
				$unread_topic = (isset($topic_tracking_info[$topic_id]) && $row_ary['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;
			}

			// Get folder img, topic status/type related information
			$folder_img = $folder_alt = $topic_type = '';
			topic_status($row_ary, $replies, $unread_topic, $folder_img, $folder_alt, $topic_type);

			$topic_title = censor_text($row_ary['topic_title']);

			$topic_unapproved	= (($row_ary['topic_visibility'] == ITEM_UNAPPROVED || $row_ary['topic_visibility'] == ITEM_REAPPROVE) && $this->auth->acl_get('m_approve', $row_ary['forum_id'])) ? true : false;
			$posts_unapproved	= ($row_ary['topic_visibility'] == ITEM_APPROVED && $row_ary['topic_posts_unapproved'] && $this->auth->acl_get('m_approve', $row_ary['forum_id'])) ? true : false;
			$topic_deleted		= $row_ary['topic_visibility'] == ITEM_DELETED;
			$u_mcp_queue		= ($topic_unapproved || $posts_unapproved) ? $url . '&amp;i=queue&amp;mode=' . ($topic_unapproved ? 'approve_details' : 'unapproved_posts') . '&amp;t=' . $row_ary['topic_id'] : '';
			$u_mcp_queue		= (!$u_mcp_queue && $topic_deleted) ? $url . '&amp;i=queue&amp;mode=deleted_topics&amp;t=' . $topic_id : $u_mcp_queue;

			$topic_row = [
				'ATTACH_ICON_IMG'		=> ($this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $row_ary['forum_id']) && $row_ary['topic_attachment']) ? $this->user->img('icon_topic_attach', $this->lang->lang('TOTAL_ATTACHMENTS')) : '',
				'TOPIC_IMG_STYLE'		=> $folder_img,
				'TOPIC_FOLDER_IMG'		=> $this->user->img($folder_img, $folder_alt),
				'TOPIC_ICON_IMG'		=> !empty($icons[$row_ary['icon_id']]) ? $icons[$row_ary['icon_id']]['img'] : '',
				'TOPIC_ICON_IMG_WIDTH'	=> !empty($icons[$row_ary['icon_id']]) ? $icons[$row_ary['icon_id']]['width'] : '',
				'TOPIC_ICON_IMG_HEIGHT'	=> !empty($icons[$row_ary['icon_id']]) ? $icons[$row_ary['icon_id']]['height'] : '',
				'UNAPPROVED_IMG'		=> ($topic_unapproved || $posts_unapproved) ? $this->user->img('icon_topic_unapproved', ($topic_unapproved) ? 'TOPIC_UNAPPROVED' : 'POSTS_UNAPPROVED') : '',
				'DELETED_IMG'			=> $topic_deleted ? $this->user->img('icon_topic_deleted', 'TOPIC_DELETED') : '',

				'TOPIC_AUTHOR'				=> get_username_string('username', $row_ary['topic_poster'], $row_ary['topic_first_poster_name'], $row_ary['topic_first_poster_colour']),
				'TOPIC_AUTHOR_COLOUR'		=> get_username_string('colour', $row_ary['topic_poster'], $row_ary['topic_first_poster_name'], $row_ary['topic_first_poster_colour']),
				'TOPIC_AUTHOR_FULL'			=> get_username_string('full', $row_ary['topic_poster'], $row_ary['topic_first_poster_name'], $row_ary['topic_first_poster_colour']),
				'U_TOPIC_AUTHOR'			=> get_username_string('profile', $row_ary['topic_poster'], $row_ary['topic_first_poster_name'], $row_ary['topic_first_poster_colour']),

				'LAST_POST_AUTHOR'			=> get_username_string('username', $row_ary['topic_last_poster_id'], $row_ary['topic_last_poster_name'], $row_ary['topic_last_poster_colour']),
				'LAST_POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row_ary['topic_last_poster_id'], $row_ary['topic_last_poster_name'], $row_ary['topic_last_poster_colour']),
				'LAST_POST_AUTHOR_FULL'		=> get_username_string('full', $row_ary['topic_last_poster_id'], $row_ary['topic_last_poster_name'], $row_ary['topic_last_poster_colour']),
				'U_LAST_POST_AUTHOR'		=> get_username_string('profile', $row_ary['topic_last_poster_id'], $row_ary['topic_last_poster_name'], $row_ary['topic_last_poster_colour']),

				'TOPIC_TYPE'			=> $topic_type,
				'TOPIC_TITLE'			=> $topic_title,
				'REPLIES'				=> $this->content_visibility->get_count('topic_posts', $row_ary, $row_ary['forum_id']) - 1,
				'LAST_POST_TIME'		=> $this->user->format_date($row_ary['topic_last_post_time']),
				'FIRST_POST_TIME'		=> $this->user->format_date($row_ary['topic_time']),
				'LAST_POST_SUBJECT'		=> $row_ary['topic_last_post_subject'],
				'LAST_VIEW_TIME'		=> $this->user->format_date($row_ary['topic_last_view_time']),

				'S_TOPIC_REPORTED'		=> (!empty($row_ary['topic_reported']) && empty($row_ary['topic_moved_id']) && $this->auth->acl_get('m_report', $row_ary['forum_id'])) ? true : false,
				'S_TOPIC_UNAPPROVED'	=> $topic_unapproved,
				'S_POSTS_UNAPPROVED'	=> $posts_unapproved,
				'S_TOPIC_DELETED'		=> $topic_deleted,
				'S_UNREAD_TOPIC'		=> $unread_topic,
			];

			if ($row_ary['topic_status'] == ITEM_MOVED)
			{
				$topic_row = array_merge($topic_row, [
					'TOPIC_ID'			=> (int) $row_ary['topic_moved_id'],
					'S_MOVED_TOPIC'		=> true,
					'U_VIEW_TOPIC'		=> append_sid("{$this->root_path}viewtopic.$this->php_ext", "t={$row_ary['topic_moved_id']}"),
					'U_DELETE_TOPIC'	=> $this->auth->acl_get('m_delete', $forum_id) ? append_sid("{$this->root_path}mcp.$this->php_ext", "i=$id&amp;f=$forum_id&amp;topic_id_list[]={$row_ary['topic_id']}&amp;mode=forum_view&amp;action=delete_topic") : '',
				]);
			}
			else
			{
				if ($action === 'merge_topic' || $action === 'merge_topics')
				{
					$u_select_topic = $url . "&amp;i=$id&amp;mode=forum_view&amp;action=$action&amp;to_topic_id=" . $row_ary['topic_id'] . $selected_ids;
				}
				else
				{
					$u_select_topic = $url . "&amp;i=$id&amp;mode=topic_view&amp;action=merge&amp;to_topic_id=" . $row_ary['topic_id'] . $selected_ids;
				}

				$topic_row = array_merge($topic_row, [
					'TOPIC_ID'			=> (int) $row_ary['topic_id'],

					'S_TOPIC_CHECKED'	=> (bool) ($topic_id_list && in_array($row_ary['topic_id'], $topic_id_list)),
					'S_SELECT_TOPIC'	=> (bool) ($merge_select && !in_array($row_ary['topic_id'], $source_topic_ids)),

					'U_SELECT_TOPIC'	=> $u_select_topic,
					'U_VIEW_TOPIC'		=> append_sid("{$this->root_path}mcp.$this->php_ext", "i=$id&amp;f=$forum_id&amp;t={$row_ary['topic_id']}&amp;mode=topic_view"),
					'U_MCP_QUEUE'		=> $u_mcp_queue,
					'U_MCP_REPORT'		=> $this->auth->acl_get('m_report', $forum_id) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=main&amp;mode=topic_view&amp;t=' . $row_ary['topic_id'] . '&amp;action=reports') : '',
				]);
			}

			$row = $row_ary;
			/**
			 * Modify the topic data before it is assigned to the template in MCP
			 *
			 * @event core.mcp_view_forum_modify_topicrow
			 * @var	array	row		Array with topic data
			 * @var	array	topic_row	Template array with topic data
			 * @since 3.1.0-a1
			 */
			$vars = ['row', 'topic_row'];
			extract($this->dispatcher->trigger_event('core.mcp_view_forum_modify_topicrow', compact($vars)));

			$row_ary = $row;
			unset($row);

			$this->template->assign_block_vars('topicrow', $topic_row);
		}
		unset($topic_rows);
	}

	/**
	 * Resynchronise topics.
	 *
	 * @param array		$topic_ids		The topic identifiers
	 * @return void
	 */
	public function resync_topics(array $topic_ids)
	{
		if (empty($topic_ids))
		{
			trigger_error('NO_TOPIC_SELECTED');
		}

		if (!phpbb_check_ids($topic_ids, $this->tables['topics'], 'topic_id', ['m_']))
		{
			return;
		}

		// Sync everything and perform extra checks separately
		sync('topic_reported', 'topic_id', $topic_ids, false, true);
		sync('topic_attachment', 'topic_id', $topic_ids, false, true);
		sync('topic', 'topic_id', $topic_ids, true, false);

		$sql = 'SELECT topic_id, forum_id, topic_title
			FROM ' . $this->tables['topics'] . '
			WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids);
		$result = $this->db->sql_query($sql);

		// Log this action
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_TOPIC_RESYNC', false, [
				'forum_id' => (int) $row['forum_id'],
				'topic_id' => (int) $row['topic_id'],
				$row['topic_title'],
			]);
		}
		$this->db->sql_freeresult($result);

		$msg = count($topic_ids) === 1 ? $this->lang->lang('TOPIC_RESYNC_SUCCESS') : $this->lang->lang('TOPICS_RESYNC_SUCCESS');

		$redirect = $this->request->variable('redirect', $this->user->data['session_page']);

		meta_refresh(3, $redirect);
		trigger_error($msg . '<br /><br />' . $this->lang->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>'));

		return;
	}

	/**
	 * Merge selected topics into selected topic.
	 *
	 * @param int		$forum_id		The forum identifier
	 * @param array		$topic_ids		The topic identifiers
	 * @param int		$to_topic_id	The "to" topic identifier
	 * @return void
	 */
	public function merge_topics($forum_id, array $topic_ids, $to_topic_id)
	{
		if (empty($topic_ids))
		{
			$this->template->assign_var('MESSAGE', $this->lang->lang('NO_TOPIC_SELECTED'));
			return;
		}

		if (empty($to_topic_id))
		{
			$this->template->assign_var('MESSAGE', $this->lang->lang('NO_FINAL_TOPIC_SELECTED'));
			return;
		}

		$sync_topics = array_merge($topic_ids, [$to_topic_id]);

		$all_topic_data = phpbb_get_topic_data($sync_topics, 'm_merge');

		if (empty($all_topic_data) || empty($all_topic_data[$to_topic_id]))
		{
			$this->template->assign_var('MESSAGE', $this->lang->lang('NO_FINAL_TOPIC_SELECTED'));
			return;
		}

		$sync_forums = [];
		$topic_views = 0;

		foreach ($all_topic_data as $data)
		{
			$sync_forums[$data['forum_id']] = $data['forum_id'];
			$topic_views = max($topic_views, $data['topic_views']);
		}

		$to_topic_data	= $all_topic_data[$to_topic_id];
		$post_id_list	= $this->request->variable('post_id_list', [0]);
		$start			= $this->request->variable('start', 0);

		if (empty($post_id_list) && !empty($topic_ids))
		{
			$post_id_list = [];

			$sql = 'SELECT post_id
				FROM ' . $this->tables['posts'] . '
				WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$post_id_list[] = (int) $row['post_id'];
			}
			$this->db->sql_freeresult($result);
		}

		if (empty($post_id_list))
		{
			$this->template->assign_var('MESSAGE', $this->lang->lang('NO_POST_SELECTED'));
			return;
		}

		if (!phpbb_check_ids($post_id_list, $this->tables['posts'], 'post_id', ['m_merge']))
		{
			return;
		}

		$redirect = $this->request->variable('redirect', "{$this->root_path}mcp.$this->php_ext?f=$forum_id&amp;i=main&amp;mode=forum_view");

		$s_hidden_fields = build_hidden_fields([
			'i'				=> 'main',
			'f'				=> $forum_id,
			'post_id_list'	=> $post_id_list,
			'to_topic_id'	=> $to_topic_id,
			'mode'			=> 'forum_view',
			'action'		=> 'merge_topics',
			'start'			=> $start,
			'redirect'		=> $redirect,
			'topic_id_list'	=> $topic_ids,
		]);

		$return_link = '';

		if (confirm_box(true))
		{
			$to_forum_id = $to_topic_data['forum_id'];

			move_posts($post_id_list, $to_topic_id, false);

			$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_MERGE', false, [
				'forum_id' => (int) $to_forum_id,
				'topic_id' => (int) $to_topic_id,
				$to_topic_data['topic_title'],
			]);

			// Update topic views count
			$sql = 'UPDATE ' . $this->tables['topics'] . '
				SET topic_views = ' . (int) $topic_views . '
				WHERE topic_id = ' . (int) $to_topic_id;
			$this->db->sql_query($sql);

			// Message and return links
			$success_msg = 'POSTS_MERGED_SUCCESS';

			if (!function_exists('phpbb_update_rows_avoiding_duplicates_notify_status'))
			{
				include($this->root_path . 'includes/functions_database_helper.' . $this->php_ext);
			}

			// Update the topic watch table.
			phpbb_update_rows_avoiding_duplicates_notify_status($this->db, $this->tables['topics_watch'], 'topic_id', $topic_ids, $to_topic_id);

			// Update the bookmarks table.
			phpbb_update_rows_avoiding_duplicates($this->db, $this->tables['bookmarks'], 'topic_id', $topic_ids, $to_topic_id);

			// Re-sync the topics and forums because the auto-sync was deactivated in the call of move_posts()
			sync('topic_reported', 'topic_id', $sync_topics);
			sync('topic_attachment', 'topic_id', $sync_topics);
			sync('topic', 'topic_id', $sync_topics, true);
			sync('forum', 'forum_id', $sync_forums, true, true);

			// Link to the new topic
			$return_link .= ($return_link ? '<br /><br />' : '') . $this->lang->lang('RETURN_NEW_TOPIC', '<a href="' . append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $to_forum_id . '&amp;t=' . $to_topic_id) . '">', '</a>');
			$redirect = $this->request->variable('redirect', "{$this->root_path}viewtopic.$this->php_ext?f=$to_forum_id&amp;t=$to_topic_id");
			$redirect = reapply_sid($redirect);

			/**
			 * Perform additional actions after merging topics.
			 *
			 * @event core.mcp_forum_merge_topics_after
			 * @var	array	all_topic_data			The data from all topics involved in the merge
			 * @var	int		to_topic_id				The ID of the topic into which the rest are merged
			 * @since 3.1.11-RC1
			 */
			$vars = [
				'all_topic_data',
				'to_topic_id',
			];
			extract($this->dispatcher->trigger_event('core.mcp_forum_merge_topics_after', compact($vars)));

			meta_refresh(3, $redirect);
			trigger_error($this->lang->lang($success_msg) . '<br /><br />' . $return_link);
		}
		else
		{
			confirm_box(false, 'MERGE_TOPICS', $s_hidden_fields);
		}
	}
}
