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

namespace phpbb\ucp\controller;

class manage
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache_service;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

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
	 * @param \phpbb\controller\helper			$helper					Controller helper object
	 * @param \phpbb\language\language			$language				Language object
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
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
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
		$this->helper				= $helper;
		$this->language				= $language;
		$this->pagination			= $pagination;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	/**
	 * Handle various modes for the UCPs "Manage" category.
	 *
	 * @param string	$mode		The mode (subscriptions|bookmarks|drafts|remember_me)
	 * @param int		$page		The page number
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function main($mode, $page = 1)
	{
		switch ($mode)
		{
			case 'subscriptions':
				if (!function_exists('topic_status'))
				{
					include($this->root_path . 'includes/functions_display.' . $this->php_ext);
				}

				$this->language->add_lang('viewforum');

				$form_key = 'ucp_front_subscribed';
				add_form_key($form_key);

				$unwatch = $this->request->is_set_post('unwatch');

				/**
				 * Read and potentially modify the post data used to remove subscriptions to forums/topics
				 *
				 * @event core.ucp_main_subscribed_post_data
				 * @since 3.1.10-RC1
				 */
				$this->dispatcher->dispatch('core.ucp_main_subscribed_post_data');

				if ($unwatch)
				{
					$return = '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $this->helper->route('ucp_manage_subscriptions') . '">', '</a>');

					if (check_form_key($form_key))
					{
						$forums = array_keys($this->request->variable('f', [0 => 0]));
						$topics = array_keys($this->request->variable('t', [0 => 0]));

						if (!empty($forums) || !empty($topics))
						{
							$l_unwatch = '';
							if (!empty($forums))
							{
								$sql = 'DELETE FROM ' . $this->tables['forums_watch'] . '
									WHERE ' . $this->db->sql_in_set('forum_id', $forums) . '
										AND user_id = ' . (int) $this->user->data['user_id'];
								$this->db->sql_query($sql);

								$l_unwatch .= '_FORUMS';
							}

							if (!empty($topics))
							{
								$sql = 'DELETE FROM ' . $this->tables['topics_watch'] . '
									WHERE ' . $this->db->sql_in_set('topic_id', $topics) . '
										AND user_id = ' . (int) $this->user->data['user_id'];
								$this->db->sql_query($sql);

								$l_unwatch .= '_TOPICS';
							}

							$this->helper->assign_meta_refresh_var(3, $this->helper->route('ucp_manage_subscriptions'));

							return $this->helper->message($this->language->lang('UNWATCHED' . $l_unwatch) . $return);
						}
						else
						{
							return trigger_error($this->language->lang('NO_WATCHED_SELECTED') . $return, E_USER_WARNING);
						}
					}
					else
					{
						return trigger_error($this->language->lang('FORM_INVALID') . $return, E_USER_WARNING);
					}
				}

				$forbidden_forums = [];

				if ($this->config['allow_forum_notify'])
				{
					$forbidden_forums = $this->auth->acl_getf('!f_read', true);
					$forbidden_forums = array_unique(array_keys($forbidden_forums));

					$sql_array = [
						'SELECT'	=> 'f.*',

						'FROM'		=> [
							$this->tables['forums_watch']	=> 'fw',
							$this->tables['forums']			=> 'f',
						],

						'WHERE'		=> 'fw.user_id = ' . (int) $this->user->data['user_id'] . '
							AND f.forum_id = fw.forum_id
							AND ' . $this->db->sql_in_set('f.forum_id', $forbidden_forums, true, true),

						'ORDER_BY'	=> 'left_id',
					];

					if ($this->config['load_db_lastread'])
					{
						$sql_array['LEFT_JOIN'] = [
							[
								'FROM'	=> [$this->tables['forums_track'] => 'ft'],
								'ON'	=> 'ft.user_id = ' . $this->user->data['user_id'] . ' AND ft.forum_id = f.forum_id',
							],
						];

						$sql_array['SELECT'] .= ', ft.mark_time ';
					}
					else
					{
						$tracking_topics = $this->request->variable($this->config['cookie_name'] . '_track', '', true, \phpbb\request\request_interface::COOKIE);
						$tracking_topics = $tracking_topics ? tracking_unserialize($tracking_topics) : [];
					}

					/**
					 * Modify the query used to retrieve a list of subscribed forums
					 *
					 * @event core.ucp_main_subscribed_forums_modify_query
					 * @var array	sql_array			The subscribed forums query
					 * @var array	forbidden_forums	The list of forbidden forums
					 * @since 3.1.10-RC1
					 */
					$vars = ['sql_array', 'forbidden_forums'];
					extract($this->dispatcher->trigger_event('core.ucp_main_subscribed_forums_modify_query', compact($vars)));

					$sql = $this->db->sql_build_query('SELECT', $sql_array);
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$forum_id = (int) $row['forum_id'];

						if ($this->config['load_db_lastread'])
						{
							$forum_check = !empty($row['mark_time']) ? $row['mark_time'] : $this->user->data['user_lastmark'];
						}
						else
						{
							$forum_check = !empty($tracking_topics['f'][$forum_id]) ? (int) (base_convert($tracking_topics['f'][$forum_id], 36, 10) + $this->config['board_startdate']) : $this->user->data['user_lastmark'];
						}

						$unread_forum = $row['forum_last_post_time'] > $forum_check;

						// Which folder should we display?
						if ($row['forum_status'] == ITEM_LOCKED)
						{
							$folder_image = $unread_forum ? 'forum_unread_locked' : 'forum_read_locked';
							$folder_alt = 'FORUM_LOCKED';
						}
						else
						{
							$folder_image = $unread_forum ? 'forum_unread' : 'forum_read';
							$folder_alt = $unread_forum ? 'UNREAD_POSTS' : 'NO_UNREAD_POSTS';
						}

						// Create last post link information, if appropriate
						if ($row['forum_last_post_id'])
						{
							$last_post_time = $this->user->format_date($row['forum_last_post_time']);
							$last_post_url = append_sid("{$this->root_path}viewtopic.$this->php_ext", "f=$forum_id&amp;p=" . $row['forum_last_post_id']) . '#p' . $row['forum_last_post_id'];
						}
						else
						{
							$last_post_time = $last_post_url = '';
						}

						$template_vars = [
							'FORUM_ID'				=> $forum_id,
							'FORUM_IMG_STYLE'		=> $folder_image,
							'FORUM_FOLDER_IMG'		=> $this->user->img($folder_image, $folder_alt),
							'FORUM_IMAGE'			=> $row['forum_image'] ? '<img src="' . $this->root_path . $row['forum_image'] . '" alt="' . $this->language->lang($folder_alt) . '" />' : '',
							'FORUM_IMAGE_SRC'		=> $row['forum_image'] ? $this->root_path . $row['forum_image'] : '',
							'FORUM_NAME'			=> $row['forum_name'],
							'FORUM_DESC'			=> generate_text_for_display($row['forum_desc'], $row['forum_desc_uid'], $row['forum_desc_bitfield'], $row['forum_desc_options']),
							'LAST_POST_SUBJECT'		=> $row['forum_last_post_subject'],
							'LAST_POST_TIME'		=> $last_post_time,

							'LAST_POST_AUTHOR'			=> get_username_string('username', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
							'LAST_POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
							'LAST_POST_AUTHOR_FULL'		=> get_username_string('full', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
							'U_LAST_POST_AUTHOR'		=> get_username_string('profile', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),

							'S_UNREAD_FORUM'		=> $unread_forum,

							'U_LAST_POST'			=> $last_post_url,
							'U_VIEWFORUM'			=> append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $row['forum_id']),
						];

						/**
						 * Add template variables to a subscribed forum row.
						 *
						 * @event core.ucp_main_subscribed_forum_modify_template_vars
						 * @var array	template_vars	Array containing the template variables for the row
						 * @var array	row				Array containing the subscribed forum row data
						 * @var int		forum_id		Forum ID
						 * @var string	folder_image	Folder image
						 * @var string	folder_alt		Alt text for the folder image
						 * @var bool	unread_forum	Whether the forum has unread content or not
						 * @var string	last_post_time	The time of the most recent post, expressed as a formatted date string
						 * @var string	last_post_url	The URL of the most recent post in the forum
						 * @since 3.1.10-RC1
						 */
						$vars = [
							'template_vars',
							'row',
							'forum_id',
							'folder_image',
							'folder_alt',
							'unread_forum',
							'last_post_time',
							'last_post_url',
						];
						extract($this->dispatcher->trigger_event('core.ucp_main_subscribed_forum_modify_template_vars', compact($vars)));

						$this->template->assign_block_vars('forumrow', $template_vars);
					}
					$this->db->sql_freeresult($result);
				}

				// Subscribed Topics
				if ($this->config['allow_topic_notify'])
				{
					if (empty($forbidden_forums))
					{
						$forbidden_forums = $this->auth->acl_getf('!f_read', true);
						$forbidden_forums = array_unique(array_keys($forbidden_forums));
					}
					$this->assign_topiclist('subscriptions', $page, $forbidden_forums);
				}

				$this->template->assign_vars([
					'S_TOPIC_NOTIFY'	=> $this->config['allow_topic_notify'],
					'S_FORUM_NOTIFY'	=> $this->config['allow_forum_notify'],
				]);
			break;

			case 'bookmarks':
				if (!$this->config['allow_bookmarks'])
				{
					$this->template->assign_vars(['S_NO_DISPLAY_BOOKMARKS' => true]);
					break;
				}

				if (!function_exists('topic_status'))
				{
					include($this->root_path . 'includes/functions_display.' . $this->php_ext);
				}

				$this->language->add_lang('viewforum');

				$return = '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $this->helper->route('ucp_manage_bookmarks') . '">', '</a>');

				if ($this->request->is_set_post('unbookmark'))
				{
					$s_hidden_fields = ['unbookmark' => 1];
					$topics = $this->request->is_set_post('t') ? array_keys($this->request->variable('t', [0 => 0])) : [];

					if (empty($topics))
					{
						return trigger_error($this->language->lang('NO_BOOKMARKS_SELECTED') . $return, E_USER_WARNING);
					}

					foreach ($topics as $topic_id)
					{
						$s_hidden_fields['t'][$topic_id] = 1;
					}

					if (confirm_box(true))
					{
						$sql = 'DELETE FROM ' . $this->tables['bookmarks'] . '
							WHERE user_id = ' . (int) $this->user->data['user_id'] . '
								AND ' . $this->db->sql_in_set('topic_id', $topics);
						$this->db->sql_query($sql);

						$this->helper->assign_meta_refresh_var(3, $this->helper->route('ucp_manage_bookmarks'));

						return $this->helper->message($this->language->lang('BOOKMARKS_REMOVED') . $return);
					}
					else
					{
						confirm_box(false, 'REMOVE_SELECTED_BOOKMARKS', build_hidden_fields($s_hidden_fields));

						return redirect($this->helper->route('ucp_manage_bookmarks'));
					}
				}

				$forbidden_forums = $this->auth->acl_getf('!f_read', true);
				$forbidden_forums = array_unique(array_keys($forbidden_forums));

				$this->assign_topiclist('bookmarks', $page, $forbidden_forums);
			break;

			case 'remember_me':
				$form_key = 'ucp_autologin_keys';
				add_form_key($form_key);

				if ($this->request->is_set_post('submit'))
				{
					$keys = $this->request->variable('keys', ['']);

					if (!check_form_key($form_key))
					{
						$this->template->assign_var('ERROR', $this->language->lang('FORM_INVALID'));
					}
					else
					{
						if (!empty($keys))
						{
							foreach ($keys as $key => $id)
							{
								$keys[$key] = $this->db->sql_like_expression($id . $this->db->get_any_char());
							}
							$sql_where = '(key_id ' . implode(' OR key_id ', $keys) . ')';
							$sql = 'DELETE FROM ' . $this->tables['sessions_keys'] . '
								WHERE user_id = ' . (int) $this->user->data['user_id'] . '
								AND ' . $sql_where ;
							$this->db->sql_query($sql);

							$u_return = $this->helper->route('ucp_manage_remember_me');
							$return = '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $u_return . '">', '</a>');

							$this->helper->assign_meta_refresh_var(3, $u_return);

							return $this->helper->message($this->language->lang('AUTOLOGIN_SESSION_KEYS_DELETED') . $return);
						}
					}
				}

				$sql = 'SELECT key_id, last_ip, last_login
					FROM ' . $this->tables['sessions_keys'] . '
					WHERE user_id = ' . (int) $this->user->data['user_id'] . '
					ORDER BY last_login ASC';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->template->assign_block_vars('sessions', [
						'IP'			=> $row['last_ip'],
						'KEY'			=> substr($row['key_id'], 0, 8),
						'LOGIN_TIME'	=> $this->user->format_date($row['last_login']),
					]);
				}

				$this->db->sql_freeresult($result);
			break;
		}

		$l_mode = $this->language->lang('UCP_MANAGE_' . utf8_strtoupper($mode));
		$t_mode = $mode === 'remember_me' ? 'ucp_profile_autologin_keys.html' : ($mode === 'subscriptions' ? 'ucp_main_subscribed.html' : 'ucp_main_bookmarks.html');

		$this->template->assign_vars([
			'L_TITLE'				=> $l_mode,

			'LAST_POST_IMG'			=> $this->user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
			'NEWEST_POST_IMG'		=> $this->user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),

			'S_DISPLAY_MARK_ALL'	=> true,
			'S_HIDDEN_FIELDS'		=> isset($s_hidden_fields) ? $s_hidden_fields : '',
			'S_UCP_ACTION'			=> $this->helper->route("ucp_manage_{$mode}"),
		]);

		return $this->helper->render($t_mode, $l_mode);
	}

	/**
	 * Build and assign topiclist for bookmarks/subscribed topics.
	 *
	 * @param string	$mode					The mode (subscriptions|bookmarks)
	 * @param int		$page					The page number
	 * @param array		$forbidden_forum_ary
	 * @return void
	 */
	protected function assign_topiclist($mode = 'subscriptions', $page = 1, array $forbidden_forum_ary = [])
	{
		$limit = (int) $this->config['topics_per_page'];
		$start = ($page - 1) * $limit;
		$table = $mode === 'subscriptions' ? $this->tables['topics_watch'] : $this->tables['bookmarks'];

		// Grab icons
		$icons = $this->cache_service->obtain_icons();

		$sql_array = [
			'SELECT'	=> 'COUNT(t.topic_id) as topics_count',

			'FROM'		=> [
				$table					=> 'i',
				$this->tables['topics']	=> 't',
			],

			'WHERE'		=>	'i.topic_id = t.topic_id
				AND i.user_id = ' . (int) $this->user->data['user_id'] . '
				AND ' . $this->db->sql_in_set('t.forum_id', $forbidden_forum_ary, true, true),
		];

		/**
		 * Modify the query used to retrieve the count of subscribed/bookmarked topics
		 *
		 * @event core.ucp_main_topiclist_count_modify_query
		 * @var array	sql_array				The subscribed/bookmarked topics query
		 * @var array	forbidden_forum_ary		The list of forbidden forums
		 * @var string	mode					The type of topic list ('subscribed' or 'bookmarks')
		 * @since 3.1.10-RC1
		 * @changed 4.0.0						The 'subscribed' mode changed to 'subscriptions'
		 */
		$vars = ['sql_array', 'forbidden_forum_ary', 'mode'];
		extract($this->dispatcher->trigger_event('core.ucp_main_topiclist_count_modify_query', compact($vars)));

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$topics_count = (int) $this->db->sql_fetchfield('topics_count');
		$this->db->sql_freeresult($result);

		if ($topics_count)
		{
			$start = $this->pagination->validate_start($start, $limit, $topics_count);

			$this->pagination->generate_template_pagination([
				'routes' => ["ucp_manage_{$mode}", "ucp_manage_{$mode}_pagination"],
			], 'pagination', 'page', $topics_count, $limit, $start);

			$this->template->assign_var('TOTAL_TOPICS', $this->language->lang('VIEW_FORUM_TOPICS', (int) $topics_count));
		}

		if ($mode === 'subscriptions')
		{
			$sql_array = [
				'SELECT'	=> 't.*, f.forum_name',

				'FROM'		=> [
					$this->tables['topics_watch']	=> 'tw',
					$this->tables['topics']			=> 't',
				],

				'WHERE'		=> 'tw.user_id = ' . $this->user->data['user_id'] . '
					AND t.topic_id = tw.topic_id
					AND ' . $this->db->sql_in_set('t.forum_id', $forbidden_forum_ary, true, true),

				'ORDER_BY'	=> 't.topic_last_post_time DESC, t.topic_last_post_id DESC',
			];

			$sql_array['LEFT_JOIN'] = [];
		}
		else
		{
			$sql_array = [
				'SELECT'	=> 't.*, f.forum_name, b.topic_id as b_topic_id',

				'FROM'		=> [
					$this->tables['bookmarks']		=> 'b',
				],

				'WHERE'		=> 'b.user_id = ' . (int) $this->user->data['user_id'] . '
					AND ' . $this->db->sql_in_set('f.forum_id', $forbidden_forum_ary, true, true),

				'ORDER_BY'	=> 't.topic_last_post_time DESC, t.topic_last_post_id DESC',
			];

			$sql_array['LEFT_JOIN'] = [];
			$sql_array['LEFT_JOIN'][] = [
				'FROM'	=> [$this->tables['topics'] => 't'],
				'ON'	=> 'b.topic_id = t.topic_id',
			];
		}

		$sql_array['LEFT_JOIN'][] = [
			'FROM'	=> [$this->tables['forums'] => 'f'],
			'ON'	=> 't.forum_id = f.forum_id',
		];

		if ($this->config['load_db_lastread'])
		{
			$sql_array['SELECT'] .= ', tt.mark_time, ft.mark_time AS forum_mark_time';
			$sql_array['LEFT_JOIN'][] = [
				'FROM'	=> [$this->tables['forums_track'] => 'ft'],
				'ON'	=> 'ft.forum_id = t.forum_id AND ft.user_id = ' . $this->user->data['user_id'],
			];
			$sql_array['LEFT_JOIN'][] = [
				'FROM'	=> [$this->tables['topics_track'] => 'tt'],
				'ON'	=> 'tt.topic_id = t.topic_id AND tt.user_id = ' . $this->user->data['user_id'],
			];
		}

		if ($this->config['load_db_track'])
		{
			$sql_array['SELECT'] .= ', tp.topic_posted';
			$sql_array['LEFT_JOIN'][] = [
				'FROM'	=> [$this->tables['topics_posted'] => 'tp'],
				'ON'	=> 'tp.topic_id = t.topic_id AND tp.user_id = ' . $this->user->data['user_id'],
			];
		}

		/**
		 * Modify the query used to retrieve the list of subscribed/bookmarked topics
		 *
		 * @event core.ucp_main_topiclist_modify_query
		 * @var array	sql_array				The subscribed/bookmarked topics query
		 * @var array	forbidden_forum_ary		The list of forbidden forums
		 * @var string	mode					The type of topic list ('subscribed' or 'bookmarks')
		 * @since 3.1.10-RC1
		 * @changed 4.0.0						The 'subscribed' mode changed to 'subscriptions'
		 */
		$vars = ['sql_array', 'forbidden_forum_ary', 'mode'];
		extract($this->dispatcher->trigger_event('core.ucp_main_topiclist_modify_query', compact($vars)));

		$topic_list = $topic_forum_list = [];
		$global_announce_list = $rowset = [];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$topic_id = isset($row['b_topic_id']) ? (int) $row['b_topic_id'] : (int) $row['topic_id'];

			$topic_list[] = $topic_id;
			$rowset[$topic_id] = $row;

			$topic_forum_list[$row['forum_id']]['forum_mark_time'] = $this->config['load_db_lastread'] ? $row['forum_mark_time'] : 0;
			$topic_forum_list[$row['forum_id']]['topics'][] = $topic_id;

			if ($row['topic_type'] == POST_GLOBAL)
			{
				$global_announce_list[] = $topic_id;
			}
		}
		$this->db->sql_freeresult($result);

		$topic_tracking_info = [];

		if ($this->config['load_db_lastread'])
		{
			foreach ($topic_forum_list as $f_id => $topic_row)
			{
				$topic_tracking_info += get_topic_tracking($f_id, $topic_row['topics'], $rowset, [$f_id => $topic_row['forum_mark_time']]);
			}
		}
		else
		{
			foreach ($topic_forum_list as $f_id => $topic_row)
			{
				$topic_tracking_info += get_complete_topic_tracking($f_id, $topic_row['topics']);
			}
		}

		foreach ($topic_list as $topic_id)
		{
			$row = &$rowset[$topic_id];

			$forum_id = (int) $row['forum_id'];
			$topic_id = isset($row['b_topic_id']) ? (int) $row['b_topic_id'] : (int) $row['topic_id'];

			$unread_topic = (isset($topic_tracking_info[$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;

			// Replies
			$replies = $this->content_visibility->get_count('topic_posts', $row, $forum_id) - 1;

			if ($row['topic_status'] == ITEM_MOVED && !empty($row['topic_moved_id']))
			{
				$topic_id = (int) $row['topic_moved_id'];
			}

			// Get folder img, topic status/type related information
			$folder_img = $folder_alt = $topic_type = '';
			topic_status($row, $replies, $unread_topic, $folder_img, $folder_alt, $topic_type);

			$view_topic_url_params = "f=$forum_id&amp;t=$topic_id";
			$view_topic_url = append_sid("{$this->root_path}viewtopic.$this->php_ext", $view_topic_url_params);

			// Send vars to template
			$template_vars = [
				'FORUM_ID'					=> $forum_id,
				'TOPIC_ID'					=> $topic_id,
				'FIRST_POST_TIME'			=> $this->user->format_date($row['topic_time']),
				'LAST_POST_SUBJECT'			=> $row['topic_last_post_subject'],
				'LAST_POST_TIME'			=> $this->user->format_date($row['topic_last_post_time']),
				'LAST_VIEW_TIME'			=> $this->user->format_date($row['topic_last_view_time']),

				'TOPIC_AUTHOR'				=> get_username_string('username', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
				'TOPIC_AUTHOR_COLOUR'		=> get_username_string('colour', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
				'TOPIC_AUTHOR_FULL'			=> get_username_string('full', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
				'U_TOPIC_AUTHOR'			=> get_username_string('profile', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),

				'LAST_POST_AUTHOR'			=> get_username_string('username', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
				'LAST_POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
				'LAST_POST_AUTHOR_FULL'		=> get_username_string('full', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
				'U_LAST_POST_AUTHOR'		=> get_username_string('profile', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),

				'S_DELETED_TOPIC'		=> (bool) !$row['topic_id'],

				'REPLIES'				=> $replies,
				'VIEWS'					=> $row['topic_views'],
				'TOPIC_TITLE'			=> censor_text($row['topic_title']),
				'TOPIC_TYPE'			=> $topic_type,
				'FORUM_NAME'			=> $row['forum_name'],

				'TOPIC_IMG_STYLE'		=> $folder_img,
				'TOPIC_FOLDER_IMG'		=> $this->user->img($folder_img, $folder_alt),
				'TOPIC_FOLDER_IMG_ALT'	=> $this->language->lang($folder_alt),
				'TOPIC_ICON_IMG'		=> !empty($icons[$row['icon_id']]) ? $icons[$row['icon_id']]['img'] : '',
				'TOPIC_ICON_IMG_WIDTH'	=> !empty($icons[$row['icon_id']]) ? $icons[$row['icon_id']]['width'] : '',
				'TOPIC_ICON_IMG_HEIGHT'	=> !empty($icons[$row['icon_id']]) ? $icons[$row['icon_id']]['height'] : '',
				'ATTACH_ICON_IMG'		=> ($this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $forum_id) && $row['topic_attachment']) ? $this->user->img('icon_topic_attach', $this->language->lang('TOTAL_ATTACHMENTS')) : '',

				'S_TOPIC_TYPE'			=> $row['topic_type'],
				'S_USER_POSTED'			=> !empty($row['topic_posted']),
				'S_UNREAD_TOPIC'		=> $unread_topic,

				'U_NEWEST_POST'			=> append_sid("{$this->root_path}viewtopic.$this->php_ext", $view_topic_url_params . '&amp;view=unread') . '#unread',
				'U_LAST_POST'			=> append_sid("{$this->root_path}viewtopic.$this->php_ext", $view_topic_url_params . '&amp;p=' . $row['topic_last_post_id']) . '#p' . $row['topic_last_post_id'],
				'U_VIEW_TOPIC'			=> $view_topic_url,
				'U_VIEW_FORUM'			=> append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $forum_id),
			];

			/**
			 * Add template variables to a subscribed/bookmarked topic row.
			 *
			 * @event core.ucp_main_topiclist_topic_modify_template_vars
			 * @var array	template_vars	Array containing the template variables for the row
			 * @var array	row				Array containing the subscribed/bookmarked topic row data
			 * @var int		forum_id		ID of the forum containing the topic
			 * @var int		topic_id		Topic ID
			 * @var int		replies			Number of replies in the topic
			 * @var string	topic_type		Topic type
			 * @var string	folder_img		Folder image
			 * @var string	folder_alt		Alt text for the folder image
			 * @var array	icons			Array containing topic icons
			 * @var bool	unread_topic	Whether the topic has unread content or not
			 * @var string	view_topic_url	The URL of the topic
			 * @since 3.1.10-RC1
			 */
			$vars = [
				'template_vars',
				'row',
				'forum_id',
				'topic_id',
				'replies',
				'topic_type',
				'folder_img',
				'folder_alt',
				'icons',
				'unread_topic',
				'view_topic_url',
			];
			extract($this->dispatcher->trigger_event('core.ucp_main_topiclist_topic_modify_template_vars', compact($vars)));

			$this->template->assign_block_vars('topicrow', $template_vars);

			$this->pagination->generate_template_pagination(append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $row['forum_id'] . "&amp;t=$topic_id"), 'topicrow.pagination', 'start', $replies + 1, $this->config['posts_per_page'], 1, true, true);
		}
	}
}
