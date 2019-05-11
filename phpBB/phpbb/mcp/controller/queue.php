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

class queue
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\mcp\functions\delete */
	protected $mcp_delete;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\report\report_reason_list_provider */
	protected $reason_provider;

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

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth							$auth					Auth object
	 * @param \phpbb\config\config						$config					Config object
	 * @param \phpbb\content_visibility					$content_visibility		Content visibility
	 * @param \phpbb\db\driver\driver_interface			$db						Database object
	 * @param \phpbb\event\dispatcher					$dispatcher				Event dispatcher
	 * @param \phpbb\language\language					$lang					Language object
	 * @param \phpbb\log\log							$log					Log object
	 * @param \phpbb\mcp\functions\delete				$mcp_delete				MCP Delete functions
	 * @param \phpbb\notification\manager				$notification_manager	Notification manager object
	 * @param \phpbb\pagination							$pagination				Pagination object
	 * @param \phpbb\report\report_reason_list_provider	$reason_provider		Report reason list provider object
	 * @param \phpbb\request\request					$request				Request object
	 * @param \phpbb\template\template					$template				Template object
	 * @param \phpbb\user								$user					User object
	 * @param string									$root_path				phpBB root path
	 * @param string									$php_ext				php File extension
	 * @param array										$tables					phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\content_visibility $content_visibility,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\mcp\functions\delete $mcp_delete,
		\phpbb\notification\manager $notification_manager,
		\phpbb\pagination $pagination,
		\phpbb\report\report_reason_list_provider $reason_provider,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth					= $auth;
		$this->config				= $config;
		$this->content_visibility	= $content_visibility;
		$this->db					= $db;
		$this->dispatcher			= $dispatcher;
		$this->lang					= $lang;
		$this->log					= $log;
		$this->mcp_delete			= $mcp_delete;
		$this->notification_manager	= $notification_manager;
		$this->pagination			= $pagination;
		$this->reason_provider		= $reason_provider;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	public function main($id, $mode)
	{
		/** @todo */
		global $action;

		include_once($this->root_path . 'includes/functions_posting.' . $this->php_ext);

		$forum_id = $this->request->variable('f', 0);
		$start = $this->request->variable('start', 0);

		$this->page_title = 'MCP_QUEUE';

		switch ($action)
		{
			case 'approve':
			case 'restore':
				include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);

				$post_id_list = $this->request->variable('post_id_list', [0]);
				$topic_id_list = $this->request->variable('topic_id_list', [0]);

				if (!empty($post_id_list))
				{
					$this->approve_posts($action, $post_id_list, 'queue', $mode);
				}
				else if (!empty($topic_id_list))
				{
					$this->approve_topics($action, $topic_id_list, 'queue', $mode);
				}
				else
				{
					trigger_error('NO_POST_SELECTED');
				}
			break;

			case 'delete':
				$post_id_list = $this->request->variable('post_id_list', [0]);
				$topic_id_list = $this->request->variable('topic_id_list', [0]);
				$delete_reason = $this->request->variable('delete_reason', '', true);

				if (!empty($post_id_list))
				{
					$this->mcp_delete->delete_posts($post_id_list, false, $delete_reason, $action);
				}
				else if (!empty($topic_id_list))
				{
					$this->mcp_delete->delete_topics($topic_id_list, false, $delete_reason, $action);
				}
				else
				{
					trigger_error('NO_POST_SELECTED');
				}
			break;

			case 'disapprove':
				$post_id_list = $this->request->variable('post_id_list', [0]);
				$topic_id_list = $this->request->variable('topic_id_list', [0]);

				if (!empty($topic_id_list) && $mode == 'deleted_topics')
				{
					$this->mcp_delete->delete_topics($topic_id_list, false, '', 'disapprove');
					return;
				}

				if (!class_exists('messenger'))
				{
					include($this->root_path . 'includes/functions_messenger.' . $this->php_ext);
				}

				if (!empty($topic_id_list))
				{
					$post_id_list = [];

					$post_visibility = ($mode == 'deleted_topics') ? ITEM_DELETED : [ITEM_UNAPPROVED, ITEM_REAPPROVE];
					$sql = 'SELECT post_id
						FROM ' . $this->tables['posts'] . '
						WHERE ' . $this->db->sql_in_set('post_visibility', $post_visibility) . '
							AND ' . $this->db->sql_in_set('topic_id', $topic_id_list);
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$post_id_list[] = (int) $row['post_id'];
					}
					$this->db->sql_freeresult($result);
				}

				if (!empty($post_id_list))
				{
					$this->disapprove_posts($post_id_list, 'queue', $mode);
				}
				else
				{
					trigger_error('NO_POST_SELECTED');
				}
			break;
		}

		switch ($mode)
		{
			case 'approve_details':
				$this->tpl_name = 'mcp_post';

				$this->lang->add_lang(['posting', 'viewtopic']);

				$post_id = $this->request->variable('p', 0);
				$topic_id = $this->request->variable('t', 0);

				if ($topic_id)
				{
					$topic_info = phpbb_get_topic_data([$topic_id], 'm_approve');
					if (isset($topic_info[$topic_id]['topic_first_post_id']))
					{
						$post_id = (int) $topic_info[$topic_id]['topic_first_post_id'];

						$this->notification_manager->mark_notifications('topic_in_queue', $topic_id, $this->user->data['user_id']);
					}
					else
					{
						$topic_id = 0;
					}
				}

				$this->notification_manager->mark_notifications('post_in_queue', $post_id, $this->user->data['user_id']);

				$post_info = phpbb_get_post_data([$post_id], 'm_approve', true);

				if (empty($post_info))
				{
					trigger_error('NO_POST_SELECTED');
				}

				$post_info = $post_info[$post_id];

				if ($post_info['topic_first_post_id'] != $post_id && topic_review($post_info['topic_id'], $post_info['forum_id'], 'topic_review', 0, false))
				{
					$this->template->assign_vars([
						'S_TOPIC_REVIEW'	=> true,
						'S_BBCODE_ALLOWED'	=> $post_info['enable_bbcode'],
						'TOPIC_TITLE'		=> $post_info['topic_title'],
					]);
				}

				$attachments = $topic_tracking_info = [];

				// Get topic tracking info
				if ($this->config['load_db_lastread'])
				{
					$tmp_topic_data = [$post_info['topic_id'] => $post_info];
					$topic_tracking_info = get_topic_tracking($post_info['forum_id'], $post_info['topic_id'], $tmp_topic_data, [$post_info['forum_id'] => $post_info['forum_mark_time']]);
					unset($tmp_topic_data);
				}
				else
				{
					$topic_tracking_info = get_complete_topic_tracking($post_info['forum_id'], $post_info['topic_id']);
				}

				$post_unread = (isset($topic_tracking_info[$post_info['topic_id']]) && $post_info['post_time'] > $topic_tracking_info[$post_info['topic_id']]) ? true : false;

				// Process message, leave it uncensored
				$parse_flags = ($post_info['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
				$message = generate_text_for_display($post_info['post_text'], $post_info['bbcode_uid'], $post_info['bbcode_bitfield'], $parse_flags, false);

				if ($post_info['post_attachment'] && $this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $post_info['forum_id']))
				{
					$sql = 'SELECT *
						FROM ' . $this->tables['attachments'] . '
						WHERE post_msg_id = ' . (int) $post_id . '
							AND in_message = 0
						ORDER BY filetime DESC, post_msg_id ASC';
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$attachments[] = $row;
					}
					$this->db->sql_freeresult($result);

					if (!empty($attachments))
					{
						$update_count = [];
						parse_attachments($post_info['forum_id'], $message, $attachments, $update_count);
					}

					// Display not already displayed Attachments for this post, we already parsed them. ;)
					if (!empty($attachments))
					{
						$this->template->assign_var('S_HAS_ATTACHMENTS', true);

						foreach ($attachments as $attachment)
						{
							$this->template->assign_block_vars('attachment', [
								'DISPLAY_ATTACHMENT'	=> $attachment,
							]);
						}
					}
				}

				// Deleting information
				if ($post_info['post_visibility'] == ITEM_DELETED && $post_info['post_delete_user'])
				{
					// User having deleted the post also being the post author?
					if (!$post_info['post_delete_user'] || $post_info['post_delete_user'] == $post_info['poster_id'])
					{
						$display_username = get_username_string('full', $post_info['poster_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']);
					}
					else
					{
						$sql = 'SELECT u.user_id, u.username, u.user_colour
							FROM ' . $this->tables['posts'] . ' p, ' . $this->tables['users'] . ' u
							WHERE p.post_id = ' . (int) $post_info['post_id'] . '
								AND p.post_delete_user = u.user_id';
						$result = $this->db->sql_query($sql);
						$post_delete_userinfo = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						$display_username = get_username_string('full', $post_info['post_delete_user'], $post_delete_userinfo['username'], $post_delete_userinfo['user_colour']);
					}

					$l_deleted_by = $this->lang->lang('DELETED_INFORMATION', $display_username, $this->user->format_date($post_info['post_delete_time'], false, true));
				}
				else
				{
					$l_deleted_by = '';
				}

				$post_url = append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $post_info['forum_id'] . '&amp;p=' . $post_info['post_id'] . '#p' . $post_info['post_id']);
				$topic_url = append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $post_info['forum_id'] . '&amp;t=' . $post_info['topic_id']);

				$post_data = [
					'S_MCP_QUEUE'			=> true,
					'U_APPROVE_ACTION'		=> append_sid("{$this->root_path}mcp.$this->php_ext", "i=queue&amp;p=$post_id&amp;f=$forum_id"),
					'S_CAN_DELETE_POST'		=> $this->auth->acl_get('m_delete', $post_info['forum_id']),
					'S_CAN_VIEWIP'			=> $this->auth->acl_get('m_info', $post_info['forum_id']),
					'S_POST_REPORTED'		=> $post_info['post_reported'],
					'S_POST_UNAPPROVED'		=> $post_info['post_visibility'] == ITEM_UNAPPROVED || $post_info['post_visibility'] == ITEM_REAPPROVE,
					'S_POST_LOCKED'			=> $post_info['post_edit_locked'],
					'S_USER_NOTES'			=> true,
					'S_POST_DELETED'		=> ($post_info['post_visibility'] == ITEM_DELETED),
					'DELETED_MESSAGE'		=> $l_deleted_by,
					'DELETE_REASON'			=> $post_info['post_delete_reason'],

					'U_EDIT'				=> ($this->auth->acl_get('m_edit', $post_info['forum_id'])) ? append_sid("{$this->root_path}posting.$this->php_ext", "mode=edit&amp;f={$post_info['forum_id']}&amp;p={$post_info['post_id']}") : '',
					'U_MCP_APPROVE'			=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=queue&amp;mode=approve_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id),
					'U_MCP_REPORT'			=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=reports&amp;mode=report_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id),
					'U_MCP_USER_NOTES'		=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=notes&amp;mode=user_notes&amp;u=' . $post_info['user_id']),
					'U_MCP_WARN_USER'		=> ($this->auth->acl_get('m_warn')) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=warn&amp;mode=warn_user&amp;u=' . $post_info['user_id']) : '',
					'U_VIEW_POST'			=> $post_url,
					'U_VIEW_TOPIC'			=> $topic_url,

					'MINI_POST_IMG'			=> ($post_unread) ? $this->user->img('icon_post_target_unread', 'UNREAD_POST') : $this->user->img('icon_post_target', 'POST'),

					'RETURN_QUEUE'			=> $this->lang->lang('RETURN_QUEUE', '<a href="' . append_sid("{$this->root_path}mcp.$this->php_ext", 'i=queue' . (($topic_id) ? '&amp;mode=unapproved_topics' : '&amp;mode=unapproved_posts')) . '&amp;start=' . $start . '">', '</a>'),
					'RETURN_POST'			=> $this->lang->lang('RETURN_POST', '<a href="' . $post_url . '">', '</a>'),
					'RETURN_TOPIC_SIMPLE'	=> $this->lang->lang('RETURN_TOPIC_SIMPLE', '<a href="' . $topic_url . '">', '</a>'),
					'REPORTED_IMG'			=> $this->user->img('icon_topic_reported', $this->lang->lang('POST_REPORTED')),
					'UNAPPROVED_IMG'		=> $this->user->img('icon_topic_unapproved', $this->lang->lang('POST_UNAPPROVED')),
					'EDIT_IMG'				=> $this->user->img('icon_post_edit', $this->lang->lang('EDIT_POST')),

					'POST_AUTHOR_FULL'		=> get_username_string('full', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
					'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
					'POST_AUTHOR'			=> get_username_string('username', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
					'U_POST_AUTHOR'			=> get_username_string('profile', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),

					'POST_PREVIEW'			=> $message,
					'POST_SUBJECT'			=> $post_info['post_subject'],
					'POST_DATE'				=> $this->user->format_date($post_info['post_time']),
					'POST_IP'				=> $post_info['poster_ip'],
					'POST_IPADDR'			=> ($this->auth->acl_get('m_info', $post_info['forum_id']) && $this->request->variable('lookup', '')) ? @gethostbyaddr($post_info['poster_ip']) : '',
					'POST_ID'				=> $post_info['post_id'],
					'S_FIRST_POST'			=> ($post_info['topic_first_post_id'] == $post_id),

					'U_LOOKUP_IP'			=> $this->auth->acl_get('m_info', $post_info['forum_id']) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=queue&amp;mode=approve_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id . '&amp;lookup=' . $post_info['poster_ip']) . '#ip' : '',
				];

				/**
				* Alter post awaiting approval template before it is rendered
				*
				* @event core.mcp_queue_approve_details_template
				* @var	int		post_id		Post ID
				* @var	int		topic_id	Topic ID
				* @var	array	topic_info	Topic data
				* @var	array	post_info	Post data
				* @var array	post_data	Post template data
				* @var	string	message		Post message
				* @var	string	post_url	Post URL
				* @var	string	topic_url	Topic URL
				* @since 3.2.2-RC1
				*/
				$vars = [
					'post_id',
					'topic_id',
					'topic_info',
					'post_info',
					'post_data',
					'message',
					'post_url',
					'topic_url',
				];
				extract($this->dispatcher->trigger_event('core.mcp_queue_approve_details_template', compact($vars)));

				$this->template->assign_vars($post_data);
			break;

			case 'unapproved_topics':
			case 'unapproved_posts':
			case 'deleted_topics':
			case 'deleted_posts':
				$m_perm = 'm_approve';
				$is_topics = ($mode == 'unapproved_topics' || $mode == 'deleted_topics') ? true : false;
				$is_restore = ($mode == 'deleted_posts' || $mode == 'deleted_topics') ? true : false;
				$visibility_const = (!$is_restore) ? [ITEM_UNAPPROVED, ITEM_REAPPROVE] : ITEM_DELETED;

				$this->lang->add_lang(['viewtopic', 'viewforum']);

				$topic_id = $this->request->variable('t', 0);
				$topic_info = $forum_info = [];

				if ($topic_id)
				{
					$topic_info = phpbb_get_topic_data([$topic_id]);

					if (empty($topic_info))
					{
						trigger_error('TOPIC_NOT_EXIST');
					}

					$topic_info = $topic_info[$topic_id];
					$forum_id = (int) $topic_info['forum_id'];
				}

				$forum_list_approve = get_forum_list($m_perm, false, true);
				$forum_list_read = array_flip(get_forum_list('f_read', true, true)); // Flipped so we can isset() the forum IDs

				// Remove forums we cannot read
				foreach ($forum_list_approve as $k => $forum_data)
				{
					if (!isset($forum_list_read[$forum_data['forum_id']]))
					{
						unset($forum_list_approve[$k]);
					}
				}
				unset($forum_list_read);

				if (!$forum_id)
				{
					$forum_list = [];
					foreach ($forum_list_approve as $row)
					{
						$forum_list[] = (int) $row['forum_id'];
					}

					if (empty($forum_list))
					{
						trigger_error('NOT_MODERATOR');
					}

					$sql = 'SELECT SUM(forum_topics_approved) as sum_forum_topics
						FROM ' . $this->tables['forums'] . '
						WHERE ' . $this->db->sql_in_set('forum_id', $forum_list);
					$result = $this->db->sql_query($sql);
					$forum_info['forum_topics_approved'] = (int) $this->db->sql_fetchfield('sum_forum_topics');
					$this->db->sql_freeresult($result);
				}
				else
				{
					$forum_info = phpbb_get_forum_data([$forum_id], $m_perm);

					if (empty($forum_info))
					{
						trigger_error('NOT_MODERATOR');
					}

					$forum_list = $forum_id;
				}

				$forum_options = '<option value="0' . ($forum_id === 0 ? '" selected="selected' : '') . '">' . $this->lang->lang('ALL_FORUMS') . '</option>';
				foreach ($forum_list_approve as $row)
				{
					$forum_options .= '<option value="' . $row['forum_id'] . ($forum_id == $row['forum_id'] ? '" selected="selected' : '') . '">' . str_repeat('&nbsp; &nbsp;', $row['padding']) . truncate_string($row['forum_name'], 30, 255, false, $this->lang->lang('ELLIPSIS')) . '</option>';
				}

				$sort_days = $total = 0;
				$sort_key = $sort_dir = '';
				$sort_by_sql = $sort_order_sql = [];
				phpbb_mcp_sorting($mode, $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $forum_id, $topic_id);

				$limit_time_sql = ($sort_days) ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';

				$forum_names = [];

				if (!$is_topics)
				{
					$sql = 'SELECT p.post_id
						FROM ' . $this->tables['posts'] . ' p, 
							' . $this->tables['topics'] . ' t' .
							($sort_order_sql[0] === 'u' ? ', ' . $this->tables['users'] . ' u' : '') . '
						WHERE ' . $this->db->sql_in_set('p.forum_id', $forum_list) . '
							AND ' . $this->db->sql_in_set('p.post_visibility', $visibility_const) . '
							' . ($sort_order_sql[0] === 'u' ? 'AND u.user_id = p.poster_id' : '') . '
							' . ($topic_id ? 'AND p.topic_id = ' . (int) $topic_id : '') . "
							AND t.topic_id = p.topic_id
							AND (t.topic_visibility <> p.post_visibility
								OR t.topic_delete_user = 0)
							$limit_time_sql
						ORDER BY $sort_order_sql";

					/**
					* Alter sql query to get posts in queue to be accepted
					*
					* @event core.mcp_queue_get_posts_query_before
					* @var	string	sql						Associative array with the query to be executed
					* @var	array	forum_list				List of forums that contain the posts
					* @var	int		visibility_const		Integer with one of the possible ITEM_* constant values
					* @var	int		topic_id				If topic_id not equal to 0, the topic id to filter the posts to display
					* @var	string	limit_time_sql			String with the SQL code to limit the time interval of the post (Note: May be empty string)
					* @var	string	sort_order_sql			String with the ORDER BY SQL code used in this query
					* @since 3.1.0-RC3
					*/
					$vars = [
						'sql',
						'forum_list',
						'visibility_const',
						'topic_id',
						'limit_time_sql',
						'sort_order_sql',
					];
					extract($this->dispatcher->trigger_event('core.mcp_queue_get_posts_query_before', compact($vars)));

					$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);

					$i = 0;
					$post_ids = [];
					while ($row = $this->db->sql_fetchrow($result))
					{
						$post_ids[] = (int) $row['post_id'];
						$row_num[(int) $row['post_id']] = $i++;
					}
					$this->db->sql_freeresult($result);

					if (!empty($post_ids))
					{
						$sql = 'SELECT t.topic_id, t.topic_title, t.forum_id, p.post_id, p.post_subject, p.post_username, p.poster_id, p.post_time, p.post_attachment, u.username, u.username_clean, u.user_colour
							FROM ' . $this->tables['posts'] . ' p, 
								' . $this->tables['topics'] . ' t, 
								' . $this->tables['users'] . ' u
							WHERE ' . $this->db->sql_in_set('p.post_id', $post_ids) . '
								AND t.topic_id = p.topic_id
								AND u.user_id = p.poster_id
							ORDER BY ' . $sort_order_sql;

						/**
						* Alter sql query to get information on all posts in queue
						*
						* @event core.mcp_queue_get_posts_for_posts_query_before
						* @var	string	sql						String with the query to be executed
						* @var	array	forum_list				List of forums that contain the posts
						* @var	int		visibility_const		Integer with one of the possible ITEM_* constant values
						* @var	int		topic_id				topic_id in the page request
						* @var	string	limit_time_sql			String with the SQL code to limit the time interval of the post (Note: May be empty string)
						* @var	string	sort_order_sql			String with the ORDER BY SQL code used in this query
						* @since 3.2.3-RC2
						*/
						$vars = [
							'sql',
							'forum_list',
							'visibility_const',
							'topic_id',
							'limit_time_sql',
							'sort_order_sql',
						];
						extract($this->dispatcher->trigger_event('core.mcp_queue_get_posts_for_posts_query_before', compact($vars)));

						$result = $this->db->sql_query($sql);

						$post_data = $rowset = [];
						while ($row = $this->db->sql_fetchrow($result))
						{
							$forum_names[] = (int) $row['forum_id'];
							$post_data[(int) $row['post_id']] = $row;
						}
						$this->db->sql_freeresult($result);

						foreach ($post_ids as $post_id)
						{
							$rowset[] = (int) $post_data[$post_id];
						}
						unset($post_data, $post_ids);
					}
					else
					{
						$rowset = [];
					}
				}
				else
				{
					$sql = 'SELECT t.forum_id, t.topic_id, t.topic_title, t.topic_title AS post_subject, t.topic_time AS post_time, t.topic_poster AS poster_id, t.topic_first_post_id AS post_id, t.topic_attachment AS post_attachment, t.topic_first_poster_name AS username, t.topic_first_poster_colour AS user_colour
						FROM ' . $this->tables['topics'] . ' t
						WHERE ' . $this->db->sql_in_set('forum_id', $forum_list) . '
							AND ' . $this->db->sql_in_set('topic_visibility', $visibility_const) . "
							AND topic_delete_user <> 0
							$limit_time_sql
						ORDER BY $sort_order_sql";

					/**
					* Alter sql query to get information on all topics in the list of forums provided.
					*
					* @event core.mcp_queue_get_posts_for_topics_query_before
					* @var	string	sql						String with the query to be executed
					* @var	array	forum_list				List of forums that contain the posts
					* @var	int		visibility_const		Integer with one of the possible ITEM_* constant values
					* @var	int		topic_id				topic_id in the page request
					* @var	string	limit_time_sql			String with the SQL code to limit the time interval of the post (Note: May be empty string)
					* @var	string	sort_order_sql			String with the ORDER BY SQL code used in this query
					* @since 3.1.0-RC3
					*/
					$vars = [
						'sql',
						'forum_list',
						'visibility_const',
						'topic_id',
						'limit_time_sql',
						'sort_order_sql',
					];
					extract($this->dispatcher->trigger_event('core.mcp_queue_get_posts_for_topics_query_before', compact($vars)));

					$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);

					$rowset = [];
					while ($row = $this->db->sql_fetchrow($result))
					{
						$forum_names[] = (int) $row['forum_id'];
						$rowset[] = $row;
					}
					$this->db->sql_freeresult($result);
				}

				if (!empty($forum_names))
				{
					// Select the names for the forum_ids
					$sql = 'SELECT forum_id, forum_name
						FROM ' . $this->tables['forums'] . '
						WHERE ' . $this->db->sql_in_set('forum_id', $forum_names);
					$result = $this->db->sql_query($sql, 3600);

					$forum_names = [];
					while ($row = $this->db->sql_fetchrow($result))
					{
						$forum_names[(int) $row['forum_id']] = $row['forum_name'];
					}
					$this->db->sql_freeresult($result);
				}

				foreach ($rowset as $row)
				{
					if (empty($row['post_username']))
					{
						$row['post_username'] = $row['username'] ?: $this->lang->lang('GUEST');
					}

					$post_row = [
						'U_TOPIC'			=> append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id']),
						'U_VIEWFORUM'		=> append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $row['forum_id']),
						'U_VIEWPOST'		=> append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $row['forum_id'] . '&amp;p=' . $row['post_id']) . (($mode == 'unapproved_posts') ? '#p' . $row['post_id'] : ''),
						'U_VIEW_DETAILS'	=> append_sid("{$this->root_path}mcp.$this->php_ext", "i=queue&amp;start=$start&amp;mode=approve_details&amp;f={$row['forum_id']}&amp;p={$row['post_id']}" . (($mode == 'unapproved_topics') ? "&amp;t={$row['topic_id']}" : '')),

						'POST_AUTHOR_FULL'		=> get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
						'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
						'POST_AUTHOR'			=> get_username_string('username', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
						'U_POST_AUTHOR'			=> get_username_string('profile', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),

						'POST_ID'		=> $row['post_id'],
						'TOPIC_ID'		=> $row['topic_id'],
						'FORUM_NAME'	=> $forum_names[$row['forum_id']],
						'POST_SUBJECT'	=> ($row['post_subject'] != '') ? $row['post_subject'] : $this->lang->lang('NO_SUBJECT'),
						'TOPIC_TITLE'	=> $row['topic_title'],
						'POST_TIME'		=> $this->user->format_date($row['post_time']),
						'S_HAS_ATTACHMENTS'	=> $this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $row['forum_id']) && $row['post_attachment'],
					];

					/**
					* Alter sql query to get information on all topics in the list of forums provided.
					*
					* @event core.mcp_queue_get_posts_modify_post_row
					* @var	array	post_row	Template variables for current post
					* @var	array	row			Post data
					* @var	array	forum_names	Forum names
					* @since 3.2.3-RC2
					*/
					$vars = [
						'post_row',
						'row',
						'forum_names',
					];
					extract($this->dispatcher->trigger_event('core.mcp_queue_get_posts_modify_post_row', compact($vars)));

					$this->template->assign_block_vars('postrow', $post_row);
				}
				unset($rowset, $forum_names);

				$base_url = $this->u_action . "&amp;f=$forum_id&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir";
				$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $total, $this->config['topics_per_page'], $start);

				// Now display the page
				$this->template->assign_vars([
					'L_TITLE'				=> $this->lang->lang('MCP_QUEUE_' . strtoupper($mode)),
					'L_EXPLAIN'				=> $this->lang->lang('MCP_QUEUE_' . strtoupper($mode) . '_EXPLAIN'),
					'L_DISPLAY_ITEMS'		=> !$is_topics ? $this->lang->lang('DISPLAY_POSTS') : $this->lang->lang('DISPLAY_TOPICS'),
					'L_ONLY_TOPIC'			=> $topic_id ? $this->lang->lang('ONLY_TOPIC', $topic_info['topic_title']) : '',

					'S_FORUM_OPTIONS'		=> $forum_options,
					'S_MCP_ACTION'			=> build_url(['t', 'f', 'sd', 'st', 'sk']),
					'S_TOPICS'				=> (bool) $is_topics,
					'S_RESTORE'				=> (bool) $is_restore,

					'TOPIC_ID'				=> (int) $topic_id,
					'TOTAL'					=> $this->lang->lang(((!$is_topics) ? 'VIEW_TOPIC_POSTS' : 'VIEW_FORUM_TOPICS'), (int) $total),
				]);

				$this->tpl_name = 'mcp_queue';
			break;
		}
	}

	/**
	 * Approve/Restore posts.
	 *
	 * @param string	$action				Action we perform on the posts (approve|restore)
	 * @param array		$post_id_list		The post identifiers
	 * @param string	$id					The active module identifier
	 * @param string	$mode				The active module mode
	 * @return void
	 */
	public function approve_posts($action, array $post_id_list, $id, $mode)
	{
		if (!phpbb_check_ids($post_id_list, $this->tables['posts'], 'post_id', ['m_approve']))
		{
			send_status_line(403, 'Forbidden');
			trigger_error('NOT_AUTHORISED');
		}

		$redirect = $this->request->variable('redirect', build_url(['quickmod']));
		$redirect = reapply_sid($redirect);
		$post_url = '';
		$approve_log = [];
		$num_topics = 0;

		$s_hidden_fields = build_hidden_fields([
			'i'				=> $id,
			'mode'			=> $mode,
			'post_id_list'	=> $post_id_list,
			'action'		=> $action,
			'redirect'		=> $redirect,
		]);

		$post_info = phpbb_get_post_data($post_id_list, 'm_approve');

		if (confirm_box(true))
		{
			$notify_poster = ($action === 'approve' && $this->request->is_set('notify_poster'));

			$topic_info = [];

			// Group the posts by topic_id
			foreach ($post_info as $post_id => $post_data)
			{
				if ($post_data['post_visibility'] == ITEM_APPROVED)
				{
					continue;
				}

				$topic_id = (int) $post_data['topic_id'];

				$topic_info[$topic_id]['posts'][] = (int) $post_id;
				$topic_info[$topic_id]['forum_id'] = (int) $post_data['forum_id'];

				// Refresh the first post, if the time or id is older then the current one
				if ($post_id <= $post_data['topic_first_post_id'] || $post_data['post_time'] <= $post_data['topic_time'])
				{
					$topic_info[$topic_id]['first_post'] = true;
				}

				// Refresh the last post, if the time or id is newer then the current one
				if ($post_id >= $post_data['topic_last_post_id'] || $post_data['post_time'] >= $post_data['topic_last_post_time'])
				{
					$topic_info[$topic_id]['last_post'] = true;
				}

				$post_url = append_sid("{$this->root_path}viewtopic.$this->php_ext", "f={$post_data['forum_id']}&amp;t={$post_data['topic_id']}&amp;p={$post_data['post_id']}") . '#p' . $post_data['post_id'];

				$approve_log[] = [
					'forum_id'		=> (int) $post_data['forum_id'],
					'topic_id'		=> (int) $post_data['topic_id'],
					'post_id'		=> (int) $post_id,
					'post_subject'	=> $post_data['post_subject'],
				];
			}

			foreach ($topic_info as $topic_id => $topic_data)
			{
				$this->content_visibility->set_post_visibility(ITEM_APPROVED, $topic_data['posts'], $topic_id, $topic_data['forum_id'], $this->user->data['user_id'], time(), '', isset($topic_data['first_post']), isset($topic_data['last_post']));
			}

			foreach ($approve_log as $log_data)
			{
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_POST_' . strtoupper($action) . 'D', false, [
					'forum_id'	=> (int) $log_data['forum_id'],
					'topic_id'	=> (int) $log_data['topic_id'],
					'post_id'	=> (int) $log_data['post_id'],
					$log_data['post_subject'],
				]);
			}

			// Only send out the mails, when the posts are being approved
			if ($action === 'approve')
			{
				// Handle notifications
				foreach ($post_info as $post_id => $post_data)
				{
					// A single topic approval may also happen here, so handle deleting the respective notification.
					if (!$post_data['topic_posts_approved'])
					{
						$this->notification_manager->delete_notifications('notification.type.topic_in_queue', $post_data['topic_id']);

						if ($post_data['post_visibility'] == ITEM_UNAPPROVED)
						{
							$this->notification_manager->add_notifications(['notification.type.topic'], $post_data);
						}
						if ($post_data['post_visibility'] != ITEM_APPROVED)
						{
							$num_topics++;
						}
					}
					else
					{
						// Only add notifications, if we are not re-approving post
						// When the topic was already approved, but was edited and
						// now needs re-approval, we don't want to notify the users
						// again.
						if ($post_data['post_visibility'] == ITEM_UNAPPROVED)
						{
							$this->notification_manager->add_notifications([
								'notification.type.bookmark',
								'notification.type.post',
							], $post_data);
						}
					}
					$this->notification_manager->add_notifications(['notification.type.quote'], $post_data);
					$this->notification_manager->delete_notifications('notification.type.post_in_queue', $post_id);

					$this->notification_manager->mark_notifications([
						'notification.type.quote',
						'notification.type.bookmark',
						'notification.type.post',
					], $post_data['post_id'], $this->user->data['user_id']);

					// Notify Poster?
					if ($notify_poster)
					{
						if ($post_data['poster_id'] == ANONYMOUS)
						{
							continue;
						}

						if (!$post_data['topic_posts_approved'])
						{
							$this->notification_manager->add_notifications('notification.type.approve_topic', $post_data);
						}
						else
						{
							$this->notification_manager->add_notifications('notification.type.approve_post', $post_data);
						}
					}
				}
			}

			if ($num_topics >= 1)
			{
				$success_msg = $num_topics === 1 ? 'TOPIC_' . strtoupper($action) . 'D_SUCCESS' : 'TOPICS_' . strtoupper($action) . 'D_SUCCESS';
			}
			else
			{
				$success_msg = count($post_info) === 1 ? 'POST_' . strtoupper($action) . 'D_SUCCESS' : 'POSTS_' . strtoupper($action) . 'D_SUCCESS';
			}

			/**
			 * Perform additional actions during post(s) approval
			 *
			 * @event core.approve_posts_after
			 * @var	string	action				Variable containing the action we perform on the posts ('approve' or 'restore')
			 * @var	array	post_info			Array containing info for all posts being approved
			 * @var	array	topic_info			Array containing info for all parent topics of the posts
			 * @var	int		num_topics			Variable containing number of topics
			 * @var bool	notify_poster		Variable telling if the post should be notified or not
			 * @var	string	success_msg			Variable containing the language key for the success message
			 * @var string	redirect			Variable containing the redirect url
			 * @since 3.1.4-RC1
			 */
			$vars = [
				'action',
				'post_info',
				'topic_info',
				'num_topics',
				'notify_poster',
				'success_msg',
				'redirect',
			];
			extract($this->dispatcher->trigger_event('core.approve_posts_after', compact($vars)));

			meta_refresh(3, $redirect);
			$message = $this->lang->lang($success_msg);

			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send([
					'MESSAGE_TITLE'		=> $this->lang->lang('INFORMATION'),
					'MESSAGE_TEXT'		=> $message,
					'REFRESH_DATA'		=> null,
					'visible'			=> true,
				]);
			}
			$message .= '<br /><br />' . $this->lang->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>');

			// If approving one post, also give links back to post...
			if (count($post_info) === 1 && $post_url)
			{
				$message .= '<br /><br />' . $this->lang->lang('RETURN_POST', '<a href="' . $post_url . '">', '</a>');
			}
			trigger_error($message);
		}
		else
		{
			$show_notify = false;

			if ($action === 'approve')
			{
				foreach ($post_info as $post_data)
				{
					if (!$post_data['topic_posts_approved'])
					{
						$num_topics++;
					}

					if (!$show_notify && $post_data['poster_id'] != ANONYMOUS)
					{
						$show_notify = true;
					}
				}
			}

			$this->template->assign_vars([
				'S_NOTIFY_POSTER'			=> $show_notify,
				'S_' . strtoupper($action)	=> true,
			]);

			// Create the confirm box message
			$action_msg = strtoupper($action);
			$num_posts = count($post_id_list) - $num_topics;
			if ($num_topics > 0 && $num_posts <= 0)
			{
				$action_msg .= '_TOPIC' . (($num_topics == 1) ? '' : 'S');
			}
			else
			{
				$action_msg .= '_POST' . (count($post_id_list) === 1 ? '' : 'S');
			}
			confirm_box(false, $action_msg, $s_hidden_fields, 'mcp_approve.html');
		}

		redirect($redirect);
	}

	/**
	* Approve/Restore topics
	*
	* @param $action		string	Action we perform on the posts ('approve' or 'restore')
	* @param $topic_id_list	array	IDs of the topics to approve/restore
	* @param $id			mixed	Category of the current active module
	* @param $mode			string	Active module
	* @return void
	*/
	public function approve_topics($action, $topic_id_list, $id, $mode)
	{
		if (!phpbb_check_ids($topic_id_list, $this->tables['topics'], 'topic_id', ['m_approve']))
		{
			send_status_line(403, 'Forbidden');
			trigger_error('NOT_AUTHORISED');
		}

		$redirect = $this->request->variable('redirect', build_url(['quickmod']));
		$redirect = reapply_sid($redirect);
		$success_msg = $topic_url = '';
		$approve_log = [];

		$s_hidden_fields = build_hidden_fields([
			'i'				=> $id,
			'mode'			=> $mode,
			'topic_id_list'	=> $topic_id_list,
			'action'		=> $action,
			'redirect'		=> $redirect,
		]);

		$topic_info = phpbb_get_topic_data($topic_id_list, 'm_approve');

		if (confirm_box(true))
		{
			$notify_poster = ($action === 'approve' && $this->request->is_set('notify_poster'));

			$first_post_ids = [];

			foreach ($topic_info as $topic_id => $topic_data)
			{
				$this->content_visibility->set_topic_visibility(ITEM_APPROVED, $topic_id, $topic_data['forum_id'], $this->user->data['user_id'], time(), '');
				$first_post_ids[$topic_id] = (int) $topic_data['topic_first_post_id'];

				$topic_url = append_sid("{$this->root_path}viewtopic.$this->php_ext", "f={$topic_data['forum_id']}&amp;t={$topic_id}");

				$approve_log[] = [
					'forum_id'		=> (int) $topic_data['forum_id'],
					'topic_id'		=> (int) $topic_data['topic_id'],
					'topic_title'	=> $topic_data['topic_title'],
				];
			}

			if (count($topic_info) >= 1)
			{
				$success_msg = count($topic_info) === 1 ? 'TOPIC_' . strtoupper($action) . 'D_SUCCESS' : 'TOPICS_' . strtoupper($action) . 'D_SUCCESS';
			}

			foreach ($approve_log as $log_data)
			{
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_TOPIC_' . strtoupper($action) . 'D', false, [
					'forum_id' => (int) $log_data['forum_id'],
					'topic_id' => (int) $log_data['topic_id'],
					$log_data['topic_title'],
				]);
			}

			// Only send out the mails, when the posts are being approved
			if ($action === 'approve')
			{
				// Grab the first post text as it's needed for the quote notification.
				$sql = 'SELECT topic_id, post_text
					FROM ' . $this->tables['posts'] . '
					WHERE ' . $this->db->sql_in_set('post_id', $first_post_ids);
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$topic_info[$row['topic_id']]['post_text'] = $row['post_text'];
				}
				$this->db->sql_freeresult($result);

				// Handle notifications
				foreach ($topic_info as $topic_id => $topic_data)
				{
					$topic_data = array_merge($topic_data, [
						'post_id'		=> $topic_data['topic_first_post_id'],
						'post_subject'	=> $topic_data['topic_title'],
						'post_time'		=> $topic_data['topic_time'],
						'poster_id'		=> $topic_data['topic_poster'],
						'post_username'	=> $topic_data['topic_first_poster_name'],
					]);

					$this->notification_manager->delete_notifications('notification.type.topic_in_queue', $topic_id);

					// Only add notifications, if we are not re-approving post
					// When the topic was already approved, but was edited and
					// now needs re-approval, we don't want to notify the users
					// again.
					if ($topic_data['topic_visibility'] == ITEM_UNAPPROVED)
					{
						$this->notification_manager->add_notifications([
							'notification.type.quote',
							'notification.type.topic',
						], $topic_data);
					}

					$this->notification_manager->mark_notifications('quote', $topic_data['post_id'], $this->user->data['user_id']);
					$this->notification_manager->mark_notifications('topic', $topic_id, $this->user->data['user_id']);

					if ($notify_poster)
					{
						$this->notification_manager->add_notifications('notification.type.approve_topic', $topic_data);
					}
				}
			}

			/**
			 * Perform additional actions during topics(s) approval
			 *
			 * @event core.approve_topics_after
			 * @var	string	action				Variable containing the action we perform on the posts ('approve' or 'restore')
			 * @var	mixed	topic_info			Array containing info for all topics being approved
			 * @var	array	first_post_ids		Array containing ids of all first posts
			 * @var bool	notify_poster		Variable telling if the poster should be notified or not
			 * @var	string	success_msg			Variable containing the language key for the success message
			 * @var string	redirect			Variable containing the redirect url
			 * @since 3.1.4-RC1
			 */
			$vars = [
				'action',
				'topic_info',
				'first_post_ids',
				'notify_poster',
				'success_msg',
				'redirect',
			];
			extract($this->dispatcher->trigger_event('core.approve_topics_after', compact($vars)));

			meta_refresh(3, $redirect);
			$message = $this->lang->lang($success_msg);

			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send([
					'MESSAGE_TITLE'		=> $this->lang->lang('INFORMATION'),
					'MESSAGE_TEXT'		=> $message,
					'REFRESH_DATA'		=> null,
					'visible'			=> true,
				]);
			}
			$message .= '<br /><br />' . $this->lang->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>');

			// If approving one topic, also give links back to topic...
			if (count($topic_info) === 1 && $topic_url)
			{
				$message .= '<br /><br />' . $this->lang->lang('RETURN_TOPIC', '<a href="' . $topic_url . '">', '</a>');
			}
			trigger_error($message);
		}
		else
		{
			$show_notify = false;

			if ($action === 'approve')
			{
				foreach ($topic_info as $topic_data)
				{
					if ($topic_data['topic_poster'] == ANONYMOUS)
					{
						continue;
					}
					else
					{
						$show_notify = true;
						break;
					}
				}
			}

			$this->template->assign_vars([
				'S_NOTIFY_POSTER'			=> $show_notify,
				'S_' . strtoupper($action)	=> true,
			]);

			confirm_box(false, strtoupper($action) . '_TOPIC' . (count($topic_id_list) === 1 ? '' : 'S'), $s_hidden_fields, 'mcp_approve.html');
		}

		redirect($redirect);
	}

	/**
	* Disapprove Post
	*
	* @param $post_id_list	array	IDs of the posts to disapprove/delete
	* @param $id			mixed	Category of the current active module
	* @param $mode			string	Active module
	* @return void
	*/
	public function disapprove_posts($post_id_list, $id, $mode)
	{
		if (!phpbb_check_ids($post_id_list, $this->tables['posts'], 'post_id', ['m_approve']))
		{
			send_status_line(403, 'Forbidden');
			trigger_error('NOT_AUTHORISED');
		}

		$redirect	= $this->request->variable('redirect', build_url(['t', 'mode', 'quickmod']) . "&amp;mode=$mode");
		$redirect	= reapply_sid($redirect);
		$reason		= $this->request->variable('reason', '', true);
		$reason_id	= $this->request->variable('reason_id', 0);

		$topic_id = 0;
		$additional_msg = '';

		$s_hidden_fields = build_hidden_fields([
			'i'				=> $id,
			'mode'			=> $mode,
			'post_id_list'	=> $post_id_list,
			'action'		=> 'disapprove',
			'redirect'		=> $redirect,
		]);

		$notify_poster = $this->request->is_set('notify_poster');
		$disapprove_reason = '';

		if ($reason_id)
		{
			$sql = 'SELECT reason_title, reason_description
				FROM ' . $this->tables['reports_reasons'] . '
				WHERE reason_id = ' . (int) $reason_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row === false || (!$reason && strtolower($row['reason_title']) == 'other'))
			{
				$additional_msg = $this->lang->lang('NO_REASON_DISAPPROVAL');

				$this->request->overwrite('confirm', null, \phpbb\request\request_interface::POST);
				$this->request->overwrite('confirm_key', null, \phpbb\request\request_interface::POST);
				$this->request->overwrite('confirm_key', null, \phpbb\request\request_interface::REQUEST);
			}
			else
			{
				// If the reason is defined within the language file, we will use the localized version, else just use the database entry...
				$disapprove_reason = (strtolower($row['reason_title']) != 'other') ? ((isset($this->lang->get_lang_array()['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])])) ? $this->lang->get_lang_array()['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])] : $row['reason_description']) : '';
				$disapprove_reason .= ($reason) ? "\n\n" . $reason : '';

				if (isset($this->lang->get_lang_array()['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])]))
				{
					$disapprove_reason_lang = strtoupper($row['reason_title']);
				}
			}
		}

		$post_info = phpbb_get_post_data($post_id_list, 'm_approve');

		$is_disapproving = false;
		foreach ($post_info as $post_id => $post_data)
		{
			if ($post_data['post_visibility'] == ITEM_DELETED)
			{
				continue;
			}

			$is_disapproving = true;
		}

		if (confirm_box(true))
		{
			$disapprove_log_topics = $disapprove_log_posts = [];
			$topic_posts_unapproved = $post_disapprove_list = $topic_information = [];

			// Build a list of posts to be disapproved and get the related topics real replies count
			foreach ($post_info as $post_id => $post_data)
			{
				if ($mode === 'unapproved_topics' && $post_data['post_visibility'] == ITEM_APPROVED)
				{
					continue;
				}

				$post_disapprove_list[$post_id] = $post_data['topic_id'];

				if (!isset($topic_posts_unapproved[$post_data['topic_id']]))
				{
					$topic_information[(int) $post_data['topic_id']] = $post_data;
					$topic_posts_unapproved[(int) $post_data['topic_id']] = 0;
				}

				$topic_posts_unapproved[(int) $post_data['topic_id']]++;
			}

			// Do not try to disapprove if no posts are selected
			if (empty($post_disapprove_list))
			{
				trigger_error('NO_POST_SELECTED');
			}

			// Now we build the log array
			foreach ($post_disapprove_list as $post_id => $topic_id)
			{
				// If the count of disapproved posts for the topic is equal
				// to the number of unapproved posts in the topic, and there are no different
				// posts, we disapprove the hole topic
				if ($topic_information[$topic_id]['topic_posts_approved'] == 0 &&
					$topic_information[$topic_id]['topic_posts_softdeleted'] == 0 &&
					$topic_information[$topic_id]['topic_posts_unapproved'] == $topic_posts_unapproved[$topic_id])
				{
					// Don't write the log more than once for every topic
					if (!isset($disapprove_log_topics[$topic_id]))
					{
						// Build disapproved topics log
						$disapprove_log_topics[$topic_id] = [
							'type'			=> 'topic',
							'post_subject'	=> $post_info[$post_id]['topic_title'],
							'forum_id'		=> $post_info[$post_id]['forum_id'],
							'topic_id'		=> 0, // useless to log a topic id, as it will be deleted
							'post_username'	=> ($post_info[$post_id]['poster_id'] == ANONYMOUS && !empty($post_info[$post_id]['post_username'])) ? $post_info[$post_id]['post_username'] : $post_info[$post_id]['username'],
						];
					}
				}
				else
				{
					// Build disapproved posts log
					$disapprove_log_posts[] = [
						'type'			=> 'post',
						'post_subject'	=> $post_info[$post_id]['post_subject'],
						'forum_id'		=> (int) $post_info[$post_id]['forum_id'],
						'topic_id'		=> (int) $post_info[$post_id]['topic_id'],
						'post_username'	=> ($post_info[$post_id]['poster_id'] == ANONYMOUS && !empty($post_info[$post_id]['post_username'])) ? $post_info[$post_id]['post_username'] : $post_info[$post_id]['username'],
					];

				}
			}

			// Get disapproved posts/topics counts separately
			$num_disapproved_topics = count($disapprove_log_topics);
			$num_disapproved_posts = count($disapprove_log_posts);

			// Build the whole log
			$disapprove_log = array_merge($disapprove_log_topics, $disapprove_log_posts);

			// Unset unneeded arrays
			unset($post_data, $disapprove_log_topics, $disapprove_log_posts);

			// Let's do the job - delete disapproved posts
			if (!empty($post_disapprove_list))
			{
				if (!function_exists('delete_posts'))
				{
					include($this->root_path . 'includes/functions_admin.' . $this->php_ext);
				}

				// We do not check for permissions here, because the moderator allowed approval/disapproval should be allowed to delete the disapproved posts
				// Note: function delete_posts triggers related forums/topics sync,
				// so we don't need to call update_post_information later and to adjust real topic replies or forum topics count manually
				delete_posts('post_id', array_keys($post_disapprove_list));

				foreach ($disapprove_log as $log_data)
				{
					if ($is_disapproving)
					{
						$l_log_message = ($log_data['type'] == 'topic') ? 'LOG_TOPIC_DISAPPROVED' : 'LOG_POST_DISAPPROVED';
						$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, $l_log_message, false, [
							'forum_id' => (int) $log_data['forum_id'],
							'topic_id' => (int) $log_data['topic_id'],
							$log_data['post_subject'],
							$disapprove_reason,
							$log_data['post_username'],
						]);
					}
					else
					{
						$l_log_message = ($log_data['type'] == 'topic') ? 'LOG_DELETE_TOPIC' : 'LOG_DELETE_POST';
						$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, $l_log_message, false, [
							'forum_id' => (int) $log_data['forum_id'],
							'topic_id' => (int) $log_data['topic_id'],
							$log_data['post_subject'],
							$log_data['post_username'],
						]);
					}
				}
			}

			$lang_reasons = [];

			foreach ($post_info as $post_id => $post_data)
			{
				$disapprove_all_posts_in_topic = $topic_information[$topic_id]['topic_posts_approved'] == 0 &&
					$topic_information[$topic_id]['topic_posts_softdeleted'] == 0 &&
					$topic_information[$topic_id]['topic_posts_unapproved'] == $topic_posts_unapproved[$topic_id];

				$this->notification_manager->delete_notifications('notification.type.post_in_queue', $post_id);

				// Do we disapprove the whole topic? Remove potential notifications
				if ($disapprove_all_posts_in_topic)
				{
					$this->notification_manager->delete_notifications('notification.type.topic_in_queue', $post_data['topic_id']);
				}

				// Notify Poster?
				if ($notify_poster)
				{
					if ($post_data['poster_id'] == ANONYMOUS)
					{
						continue;
					}

					$post_data['disapprove_reason'] = $disapprove_reason;
					if (isset($disapprove_reason_lang))
					{
						// Okay we need to get the reason from the posters language
						if (!isset($lang_reasons[$post_data['user_lang']]))
						{
							// Assign the current users translation as the default, this is not ideal but getting the board default adds another layer of complexity.
							$lang_reasons[$post_data['user_lang']] = $this->lang->get_lang_array()['report_reasons']['DESCRIPTION'][$disapprove_reason_lang];

							// Only load up the language pack if the language is different to the current one
							if ($post_data['user_lang'] != $this->lang->get_used_language() && file_exists($this->root_path . '/language/' . $post_data['user_lang'] . '/mcp.' . $this->php_ext))
							{
								// Load up the language pack
								$lang = [];
								@include($this->root_path . '/language/' . basename($post_data['user_lang']) . '/mcp.' . $this->php_ext);

								// If we find the reason in this language pack use it
								if (isset($this->lang->get_lang_array()['report_reasons']['DESCRIPTION'][$disapprove_reason_lang]))
								{
									$lang_reasons[$post_data['user_lang']] = $this->lang->get_lang_array()['report_reasons']['DESCRIPTION'][$disapprove_reason_lang];
								}

								unset($lang); // Free memory
							}
						}

						$post_data['disapprove_reason'] = $lang_reasons[$post_data['user_lang']];
						$post_data['disapprove_reason'] .= ($reason) ? "\n\n" . $reason : '';
					}

					if ($disapprove_all_posts_in_topic && $topic_information[$topic_id]['topic_posts_unapproved'] == 1)
					{
						// If there is only 1 post when disapproving the topic,
						// we send the user a "disapprove topic" notification...
						$this->notification_manager->add_notifications('notification.type.disapprove_topic', $post_data);
					}
					else
					{
						// ... otherwise there are multiple unapproved posts and
						// all of them are disapproved as posts.
						$this->notification_manager->add_notifications('notification.type.disapprove_post', $post_data);
					}
				}
			}

			if ($num_disapproved_topics)
			{
				$success_msg = ($num_disapproved_topics == 1) ? 'TOPIC' : 'TOPICS';
			}
			else
			{
				$success_msg = ($num_disapproved_posts == 1) ? 'POST' : 'POSTS';
			}

			if ($is_disapproving)
			{
				$success_msg .= '_DISAPPROVED_SUCCESS';
			}
			else
			{
				$success_msg .= '_DELETED_SUCCESS';
			}

			// If we came from viewtopic, we try to go back to it.
			if (strpos($redirect, $this->root_path . 'viewtopic.' . $this->php_ext) === 0)
			{
				if ($num_disapproved_topics == 0)
				{
					// So we need to remove the post id part from the Url
					$redirect = str_replace("&amp;p={$post_id_list[0]}#p{$post_id_list[0]}", '', $redirect);
				}
				else
				{
					// However this is only possible if the topic still exists,
					// Otherwise we go back to the viewforum page
					$redirect = append_sid($this->root_path . 'viewforum.' . $this->php_ext, 'f=' . $this->request->variable('f', 0));
				}
			}

			/**
			 * Perform additional actions during post(s) disapproval
			 *
			 * @event core.disapprove_posts_after
			 * @var	array	post_info					Array containing info for all posts being disapproved
			 * @var	array	topic_information			Array containing information for the topics
			 * @var	array	topic_posts_unapproved		Array containing list of topic ids and the count of disapproved posts in them
			 * @var	array	post_disapprove_list		Array containing list of posts and their topic id
			 * @var	int		num_disapproved_topics		Variable containing the number of disapproved topics
			 * @var	int		num_disapproved_posts		Variable containing the number of disapproved posts
			 * @var array	lang_reasons				Array containing the language keys for reasons
			 * @var	string	disapprove_reason			Variable containing the language key for the success message
			 * @var	string	disapprove_reason_lang		Variable containing the language key for the success message
			 * @var bool	is_disapproving				Variable telling if anything is going to be disapproved
			 * @var bool	notify_poster				Variable telling if the post should be notified or not
			 * @var	string	success_msg					Variable containing the language key for the success message
			 * @var string	redirect					Variable containing the redirect url
			 * @since 3.1.4-RC1
			 */
			$vars = [
				'post_info',
				'topic_information',
				'topic_posts_unapproved',
				'post_disapprove_list',
				'num_disapproved_topics',
				'num_disapproved_posts',
				'lang_reasons',
				'disapprove_reason',
				'disapprove_reason_lang',
				'is_disapproving',
				'notify_poster',
				'success_msg',
				'redirect',
			];
			extract($this->dispatcher->trigger_event('core.disapprove_posts_after', compact($vars)));

			unset($lang_reasons, $post_info, $disapprove_reason, $disapprove_reason_lang);

			meta_refresh(3, $redirect);
			$message = $this->lang->lang($success_msg);

			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send([
					'MESSAGE_TITLE'		=> $this->lang->lang('INFORMATION'),
					'MESSAGE_TEXT'		=> $message,
					'REFRESH_DATA'		=> null,
					'visible'			=> false,
				]);
			}
			$message .= '<br /><br />' . $this->lang->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>');
			trigger_error($message);
		}
		else
		{
			$show_notify = false;

			foreach ($post_info as $post_data)
			{
				if ($post_data['poster_id'] == ANONYMOUS)
				{
					continue;
				}
				else
				{
					$show_notify = true;
					break;
				}
			}

			$l_confirm_msg = 'DISAPPROVE_POST';
			$confirm_template = 'mcp_approve.html';
			if ($is_disapproving)
			{
				$this->reason_provider->display_reasons($reason_id);
			}
			else
			{
				$this->lang->add_lang('posting');

				$l_confirm_msg = 'DELETE_POST_PERMANENTLY';
				$confirm_template = 'confirm_delete_body.html';
			}
			$l_confirm_msg .= (count($post_id_list) === 1 ? '' : 'S');

			$this->template->assign_vars([
				'S_NOTIFY_POSTER'	=> $show_notify,
				'S_APPROVE'			=> false,
				'REASON'			=> ($is_disapproving) ? $reason : '',
				'ADDITIONAL_MSG'	=> $additional_msg,
			]);

			confirm_box(false, $l_confirm_msg, $s_hidden_fields, $confirm_template);
		}

		redirect($redirect);
	}
}
