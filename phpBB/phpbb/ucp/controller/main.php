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

class main
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

	/** @var \phpbb\language\language */
	protected $lang;

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

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

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
		$this->pagination			= $pagination;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	function main($id, $mode)
	{
		switch ($mode)
		{
			case 'front':
				$this->lang->add_lang('memberlist');

				$sql_from = $this->tables['topics'] . ' t ';
				$sql_select = '';

				if ($this->config['load_db_track'])
				{
					$sql_from .= ' LEFT JOIN ' . $this->tables['topics_posted'] . ' tp ON (tp.topic_id = t.topic_id
						AND tp.user_id = ' . $this->user->data['user_id'] . ')';
					$sql_select .= ', tp.topic_posted';
				}

				if ($this->config['load_db_lastread'])
				{
					$sql_from .= ' LEFT JOIN ' . $this->tables['topics_track'] . ' tt ON (tt.topic_id = t.topic_id
						AND tt.user_id = ' . $this->user->data['user_id'] . ')';
					$sql_select .= ', tt.mark_time';

					$sql_from .= ' LEFT JOIN ' . $this->tables['forums_track'] . ' ft ON (ft.forum_id = t.forum_id
						AND ft.user_id = ' . $this->user->data['user_id'] . ')';
					$sql_select .= ', ft.mark_time AS forum_mark_time';
				}

				$topic_type = $this->lang->lang('VIEW_TOPIC_GLOBAL');
				$folder = 'global_read';
				$folder_new = 'global_unread';

				// Get cleaned up list... return only those forums having the f_read permission
				$forum_ary = $this->auth->acl_getf('f_read', true);
				$forum_ary = array_unique(array_keys($forum_ary));
				$topic_list = $rowset = [];

				// If the user can't see any forums, he can't read any posts because fid of 0 is invalid
				if (!empty($forum_ary))
				{
					/**
					 * Modify sql variables before query is processed
					 *
					 * @event core.ucp_main_front_modify_sql
					 * @var string	sql_select	SQL select
					 * @var string	sql_from	SQL from
					 * @var array	forum_ary	Forum array
					 * @since 3.2.4-RC1
					 */
					$vars = [
						'sql_select',
						'sql_from',
						'forum_ary',
					];
					extract($this->dispatcher->trigger_event('core.ucp_main_front_modify_sql', compact($vars)));

					$sql = "SELECT t.* $sql_select
						FROM $sql_from
						WHERE t.topic_type = " . POST_GLOBAL . '
							AND ' . $this->db->sql_in_set('t.forum_id', $forum_ary) . '
						ORDER BY t.topic_last_post_time DESC, t.topic_last_post_id DESC';
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$topic_list[] = $row['topic_id'];
						$rowset[$row['topic_id']] = $row;
					}
					$this->db->sql_freeresult($result);
				}

				$topic_forum_list = [];
				foreach ($rowset as $t_id => $row)
				{
					// @todo variable never defined?
					if (isset($forum_tracking_info[$row['forum_id']]))
					{
						$row['forum_mark_time'] = $forum_tracking_info[$row['forum_id']];
					}

					$topic_forum_list[(int) $row['forum_id']]['forum_mark_time'] = ($this->config['load_db_lastread'] && $this->user->data['is_registered'] && isset($row['forum_mark_time'])) ? $row['forum_mark_time'] : 0;
					$topic_forum_list[(int) $row['forum_id']]['topics'][] = (int) $t_id;
				}

				$topic_tracking_info = $tracking_topics = [];
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
				unset($topic_forum_list);

				foreach ($topic_list as $topic_id)
				{
					$row = &$rowset[$topic_id];

					$forum_id = $row['forum_id'];
					$topic_id = $row['topic_id'];

					$unread_topic = (isset($topic_tracking_info[$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;

					$folder_img = $unread_topic ? $folder_new : $folder;
					$folder_alt = $unread_topic ? 'UNREAD_POSTS' : ($row['topic_status'] == ITEM_LOCKED ? 'TOPIC_LOCKED' : 'NO_UNREAD_POSTS');

					if ($row['topic_status'] == ITEM_LOCKED)
					{
						$folder_img .= '_locked';
					}

					// Posted image?
					if (!empty($row['topic_posted']) && $row['topic_posted'])
					{
						$folder_img .= '_mine';
					}

					$topicrow = [
						'FORUM_ID'					=> $forum_id,
						'TOPIC_ID'					=> $topic_id,
						'TOPIC_AUTHOR'				=> get_username_string('username', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
						'TOPIC_AUTHOR_COLOUR'		=> get_username_string('colour', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
						'TOPIC_AUTHOR_FULL'			=> get_username_string('full', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
						'FIRST_POST_TIME'			=> $this->user->format_date($row['topic_time']),
						'LAST_POST_SUBJECT'			=> censor_text($row['topic_last_post_subject']),
						'LAST_POST_TIME'			=> $this->user->format_date($row['topic_last_post_time']),
						'LAST_VIEW_TIME'			=> $this->user->format_date($row['topic_last_view_time']),
						'LAST_POST_AUTHOR'			=> get_username_string('username', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
						'LAST_POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
						'LAST_POST_AUTHOR_FULL'		=> get_username_string('full', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
						'TOPIC_TITLE'				=> censor_text($row['topic_title']),
						'TOPIC_TYPE'				=> $topic_type,

						'TOPIC_IMG_STYLE'		=> $folder_img,
						'TOPIC_FOLDER_IMG'		=> $this->user->img($folder_img, $folder_alt),
						'ATTACH_ICON_IMG'		=> ($this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $forum_id) && $row['topic_attachment']) ? $this->user->img('icon_topic_attach', '') : '',

						'S_USER_POSTED'			=> (bool) (!empty($row['topic_posted']) && $row['topic_posted']),
						'S_UNREAD'				=> $unread_topic,

						'U_TOPIC_AUTHOR'		=> get_username_string('profile', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
						'U_LAST_POST'			=> append_sid("{$this->root_path}viewtopic.$this->php_ext", "f=$forum_id&amp;t=$topic_id&amp;p=" . $row['topic_last_post_id']) . '#p' . $row['topic_last_post_id'],
						'U_LAST_POST_AUTHOR'	=> get_username_string('profile', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
						'U_NEWEST_POST'			=> append_sid("{$this->root_path}viewtopic.$this->php_ext", "f=$forum_id&amp;t=$topic_id&amp;view=unread") . '#unread',
						'U_VIEW_TOPIC'			=> append_sid("{$this->root_path}viewtopic.$this->php_ext", "f=$forum_id&amp;t=$topic_id"),
					];

					/**
					 * Add template variables to a front topics row.
					 *
					 * @event core.ucp_main_front_modify_template_vars
					 * @var array	topicrow		Array containing the template variables for the row
					 * @var array	row				Array containing the subscribed forum row data
					 * @var int		forum_id		Forum ID
					 * @var string	folder_img		Folder image
					 * @var string	folder_alt		Alt text for the folder image
					 * @since 3.2.4-RC1
					 */
					$vars = [
						'topicrow',
						'row',
						'forum_id',
						'folder_img',
						'folder_alt',
					];
					extract($this->dispatcher->trigger_event('core.ucp_main_front_modify_template_vars', compact($vars)));

					$this->template->assign_block_vars('topicrow', $topicrow);
				}

				if ($this->config['load_user_activity'])
				{
					if (!function_exists('display_user_activity'))
					{
						include_once($this->root_path . 'includes/functions_display.' . $this->php_ext);
					}

					display_user_activity($this->user->data);
				}

				// Do the relevant calculations
				$member_days	= max(1, round((time() - $this->user->data['user_regdate']) / 86400));
				$posts_per_day	= $this->user->data['user_posts'] / $member_days;
				$percentage		= $this->config['num_posts'] ? min(100, ($this->user->data['user_posts'] / $this->config['num_posts']) * 100) : 0;

				$this->template->assign_vars([
					'JOINED'			=> $this->user->format_date($this->user->data['user_regdate']),
					'LAST_ACTIVE'		=> empty($last_active) ? ' - ' : $this->user->format_date($last_active),
					'POSTS'				=> $this->user->data['user_posts'] ? $this->user->data['user_posts'] : 0,
					'POSTS_DAY'			=> $this->lang->lang('POST_DAY', $posts_per_day),
					'POSTS_PCT'			=> $this->lang->lang('POST_PCT', $percentage),
					'USER_COLOR'		=> !empty($this->user->data['user_colour']) ? $this->user->data['user_colour'] : '',
					'WARNINGS'			=> $this->user->data['user_warnings'] ? $this->user->data['user_warnings'] : 0,

					'U_SEARCH_USER'		=> $this->auth->acl_get('u_search') ? append_sid("{$this->root_path}search.$this->php_ext", 'author_id=' . $this->user->data['user_id'] . '&amp;sr=posts') : '',
				]);
			break;

			case 'subscribed':
				include($this->root_path . 'includes/functions_display.' . $this->php_ext);

				$this->lang->add_lang('viewforum');

				add_form_key('ucp_front_subscribed');

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
					if (check_form_key('ucp_front_subscribed'))
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

							$msg = $this->lang->lang('UNWATCHED' . $l_unwatch);
						}
						else
						{
							$msg = $this->lang->lang('NO_WATCHED_SELECTED');
						}
					}
					else
					{
						$msg = $this->lang->lang('FORM_INVALID');
					}

					$message = $msg . '<br /><br />' . $this->lang->lang('RETURN_UCP', '<a href="' . append_sid("{$this->root_path}ucp.$this->php_ext", "i=$id&amp;mode=subscribed") . '">', '</a>');
					meta_refresh(3, append_sid("{$this->root_path}ucp.$this->php_ext", "i=$id&amp;mode=subscribed"));
					trigger_error($message);
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
								'ON'	=> 'ft.user_id = ' . (int) $this->user->data['user_id'] . ' AND ft.forum_id = f.forum_id',
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
					$vars = [
						'sql_array',
						'forbidden_forums',
					];
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
							$forum_check = isset($tracking_topics['f'][$forum_id]) ? (int) (base_convert($tracking_topics['f'][$forum_id], 36, 10) + $this->config['board_startdate']) : $this->user->data['user_lastmark'];
						}

						$unread_forum = (bool) $row['forum_last_post_time'] > $forum_check;

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
							'FORUM_IMAGE'			=> $row['forum_image'] ? '<img src="' . $this->root_path . $row['forum_image'] . '" alt="' . $this->lang->lang($folder_alt) . '" />' : '',
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

					$this->assign_topiclist('subscribed', $forbidden_forums);
				}

				$this->template->assign_vars([
					'S_TOPIC_NOTIFY'	=> $this->config['allow_topic_notify'],
					'S_FORUM_NOTIFY'	=> $this->config['allow_forum_notify'],
				]);
			break;

			case 'bookmarks':
				if (!$this->config['allow_bookmarks'])
				{
					$this->template->assign_var('S_NO_DISPLAY_BOOKMARKS', true);

					break;
				}

				include($this->root_path . 'includes/functions_display.' . $this->php_ext);

				$this->lang->add_lang('viewforum');

				if ($this->request->is_set_post('unbookmark'))
				{
					$s_hidden_fields = ['unbookmark' => 1];
					$topics = $this->request->is_set_post('t') ? array_keys($this->request->variable('t', [0 => 0])) : [];
					$url = $this->u_action;

					if (empty($topics))
					{
						trigger_error('NO_BOOKMARKS_SELECTED');
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

						meta_refresh(3, $url);
						$message = $this->lang->lang('BOOKMARKS_REMOVED') . '<br /><br />' . $this->lang->lang('RETURN_UCP', '<a href="' . $url . '">', '</a>');
						trigger_error($message);
					}
					else
					{
						confirm_box(false, 'REMOVE_SELECTED_BOOKMARKS', build_hidden_fields($s_hidden_fields));
					}
				}

				$forbidden_forums = $this->auth->acl_getf('!f_read', true);
				$forbidden_forums = array_unique(array_keys($forbidden_forums));

				$this->assign_topiclist('bookmarks', $forbidden_forums);
			break;

			case 'drafts':
				$this->lang->add_lang('posting');

				$pm_drafts = false; // @todo ($this->p_master->p_name == 'pm') ? true : false;
				$this->template->assign_var('S_SHOW_DRAFTS', true);

				add_form_key('ucp_draft');

				$edit		= $this->request->is_set('edit');
				$delete		= $this->request->is_set_post('delete');
				$submit		= $this->request->is_set_post('submit');
				$draft_id	= $this->request->variable('edit', 0);

				$draft_subject = $draft_message = '';
				$s_hidden_fields = $edit ? '<input type="hidden" name="edit" value="' . $draft_id . '" />' : '';

				include_once($this->root_path . 'includes/message_parser.' . $this->php_ext);
				$message_parser = new \parse_message();

				if ($delete)
				{
					if (check_form_key('ucp_draft'))
					{
						$drafts = array_keys($this->request->variable('d', [0 => 0]));

						if (!empty($drafts))
						{
							$sql = 'DELETE FROM ' . $this->tables['drafts'] . '
								WHERE ' . $this->db->sql_in_set('draft_id', $drafts) . '
									AND user_id = ' . (int) $this->user->data['user_id'];
							$this->db->sql_query($sql);
						}

						$msg = $this->lang->lang('DRAFTS_DELETED');
						unset($drafts);
					}
					else
					{
						$msg = $this->lang->lang('FORM_INVALID');
					}

					$message = $msg . '<br /><br />' . $this->lang->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>');
					meta_refresh(3, $this->u_action);
					trigger_error($message);
				}

				if ($submit && $edit)
				{
					$draft_subject = $this->request->variable('subject', '', true);
					$draft_message = $this->request->variable('message', '', true);

					if (check_form_key('ucp_draft'))
					{
						if ($draft_message && $draft_subject)
						{
							// $this->auth->acl_gets can't be used here because it will check for global forum permissions in this case
							// In general we don't need too harsh checking here for permissions, as this will be handled later when submitting
							$bbcode_status = $this->auth->acl_get('u_pm_bbcode') || $this->auth->acl_getf_global('f_bbcode');
							$smilies_status = $this->auth->acl_get('u_pm_smilies') || $this->auth->acl_getf_global('f_smilies');
							$img_status = $this->auth->acl_get('u_pm_img') || $this->auth->acl_getf_global('f_img');
							$flash_status = $this->auth->acl_get('u_pm_flash') || $this->auth->acl_getf_global('f_flash');

							$message_parser->message = $draft_message;
							$message_parser->parse($bbcode_status, $this->config['allow_post_links'], $smilies_status, $img_status, $flash_status, true, $this->config['allow_post_links']);

							$draft_row = [
								'draft_subject'	=> $draft_subject,
								'draft_message'	=> $message_parser->message,
							];

							$sql = 'UPDATE ' . $this->tables['drafts'] . '
								SET ' . $this->db->sql_build_array('UPDATE', $draft_row) . '
								WHERE draft_id = ' . (int) $draft_id . '
									AND user_id = ' . (int) $this->user->data['user_id'];
							$this->db->sql_query($sql);

							$message = $this->lang->lang('DRAFT_UPDATED') . '<br /><br />' . $this->lang->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>');

							meta_refresh(3, $this->u_action);
							trigger_error($message);
						}
						else
						{
							$this->template->assign_var('ERROR', $draft_message == '' ? $this->lang->lang('EMPTY_DRAFT') : ($draft_subject == '' ? $this->lang->lang('EMPTY_DRAFT_TITLE') : ''));
						}
					}
					else
					{
						$this->template->assign_var('ERROR', $this->lang->lang('FORM_INVALID'));
					}
				}

				$draft_rows = $topic_ids = [];

				if (!$pm_drafts)
				{
					$sql = 'SELECT d.*, f.forum_name
						FROM ' . $this->tables['drafts'] . ' d, 
							' . $this->tables['forums'] . ' f
						WHERE d.user_id = ' . (int) $this->user->data['user_id'] . ' ' .
							($edit ? 'AND d.draft_id = ' . (int) $draft_id : '') . '
							AND f.forum_id = d.forum_id
						ORDER BY d.save_time DESC';
				}
				else
				{
					$sql = 'SELECT * FROM ' . $this->tables['drafts'] . '
						WHERE user_id = ' . $this->user->data['user_id'] . ' ' .
							($edit ? 'AND d.draft_id = ' . (int) $draft_id : '') . '
							AND forum_id = 0
							AND topic_id = 0
						ORDER BY save_time DESC';
				}

				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					if ($row['topic_id'])
					{
						$topic_ids[] = (int) $row['topic_id'];
					}

					$draft_rows[] = $row;
				}
				$this->db->sql_freeresult($result);

				if (!empty($topic_ids))
				{
					$sql = 'SELECT topic_id, forum_id, topic_title
						FROM ' . $this->tables['topics'] . '
						WHERE ' . $this->db->sql_in_set('topic_id', array_unique($topic_ids));
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$topic_rows[$row['topic_id']] = $row;
					}
					$this->db->sql_freeresult($result);
				}
				unset($topic_ids);

				$this->template->assign_var('S_EDIT_DRAFT', $edit);

				$row_count = 0;
				foreach ($draft_rows as $draft)
				{
					$link_topic = $link_forum = $link_pm = false;
					$insert_url = $view_url = $title = '';

					if (isset($topic_rows[$draft['topic_id']]) && $this->auth->acl_get('f_read', $topic_rows[$draft['topic_id']]['forum_id']))
					{
						$link_topic = true;
						$view_url = append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $topic_rows[$draft['topic_id']]['forum_id'] . '&amp;t=' . $draft['topic_id']);
						$title = $topic_rows[$draft['topic_id']]['topic_title'];

						$insert_url = append_sid("{$this->root_path}posting.$this->php_ext", 'f=' . $topic_rows[$draft['topic_id']]['forum_id'] . '&amp;t=' . $draft['topic_id'] . '&amp;mode=reply&amp;d=' . $draft['draft_id']);
					}
					else if ($this->auth->acl_get('f_read', $draft['forum_id']))
					{
						$link_forum = true;
						$view_url = append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $draft['forum_id']);
						$title = $draft['forum_name'];

						$insert_url = append_sid("{$this->root_path}posting.$this->php_ext", 'f=' . $draft['forum_id'] . '&amp;mode=post&amp;d=' . $draft['draft_id']);
					}
					else if ($pm_drafts)
					{
						$link_pm = true;
						$insert_url = append_sid("{$this->root_path}ucp.$this->php_ext", "i=$id&amp;mode=compose&amp;d=" . $draft['draft_id']);
					}

					if (!$submit)
					{
						$message_parser->message = $draft['draft_message'];
						$message_parser->decode_message();
						$draft_message = $message_parser->message;
					}

					$template_row = [
						'DATE'				=> $this->user->format_date($draft['save_time']),
						'DRAFT_MESSAGE'		=> $draft_message,
						'DRAFT_SUBJECT'		=> $submit ? $draft_subject : $draft['draft_subject'],
						'TITLE'				=> $title,

						'DRAFT_ID'			=> (int) $draft['draft_id'],
						'FORUM_ID'			=> (int) $draft['forum_id'],
						'TOPIC_ID'			=> (int) $draft['topic_id'],

						'S_LINK_TOPIC'		=> $link_topic,
						'S_LINK_FORUM'		=> $link_forum,
						'S_LINK_PM'			=> $link_pm,
						'S_HIDDEN_FIELDS'	=> $s_hidden_fields,

						'U_VIEW'			=> $view_url,
						'U_VIEW_EDIT'		=> $this->u_action . '&amp;edit=' . $draft['draft_id'],
						'U_INSERT'			=> $insert_url,
					];

					$row_count++;

					if ($edit)
					{
						$this->template->assign_vars($template_row);
					}
					else
					{
						$this->template->assign_block_vars('draftrow', $template_row);
					}
				}

				if (!$edit)
				{
					$this->template->assign_var('S_DRAFT_ROWS', $row_count);
				}
			break;
		}

		$this->template->assign_vars([
			'L_TITLE'			=> $this->lang->lang('UCP_MAIN_' . strtoupper($mode)),

			'S_DISPLAY_MARK_ALL'	=> ($mode === 'watched' || ($mode === 'drafts' && !$this->request->is_set('edit', \phpbb\request\request_interface::GET))) ? true : false,
			'S_HIDDEN_FIELDS'		=> isset($s_hidden_fields) ? $s_hidden_fields : '',
			'S_UCP_ACTION'			=> $this->u_action,

			'LAST_POST_IMG'			=> $this->user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
			'NEWEST_POST_IMG'		=> $this->user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),
		]);

		// Set desired template
		$this->tpl_name = 'ucp_main_' . $mode;
		$this->page_title = 'UCP_MAIN_' . strtoupper($mode);
	}

	/**
	 * Build and assign topiclist for bookmarks/subscribed topics.
	 *
	 * @param string	$mode
	 * @param array		$forbidden_forum_ary
	 * @return void
	 */
	function assign_topiclist($mode = 'subscribed', array $forbidden_forum_ary = [])
	{
		$start = $this->request->variable('start', 0);
		$table = $mode === 'subscribed' ? $this->tables['topics_watch'] : $this->tables['bookmarks'];

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
		 */
		$vars = [
			'sql_array',
			'forbidden_forum_ary',
			'mode',
		];
		extract($this->dispatcher->trigger_event('core.ucp_main_topiclist_count_modify_query', compact($vars)));

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$topics_count = (int) $this->db->sql_fetchfield('topics_count');
		$this->db->sql_freeresult($result);

		if ($topics_count)
		{
			$start = $this->pagination->validate_start($start, $this->config['topics_per_page'], $topics_count);
			$this->pagination->generate_template_pagination($this->u_action, 'pagination', 'start', $topics_count, $this->config['topics_per_page'], $start);

			$this->template->assign_var('TOTAL_TOPICS', $this->lang->lang('VIEW_FORUM_TOPICS', (int) $topics_count));
		}

		if ($mode === 'subscribed')
		{
			$sql_array = [
				'SELECT'	=> 't.*, f.forum_name',

				'FROM'		=> [
					$this->tables['topics_watch']	=> 'tw',
					$this->tables['topics']			=> 't',
				],

				'WHERE'		=> 'tw.user_id = ' . (int) $this->user->data['user_id'] . '
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

				'FROM'		=> [$this->tables['bookmarks']	=> 'b'],

				'WHERE'		=> 'b.user_id = ' . (int) $this->user->data['user_id'] . '
					AND ' . $this->db->sql_in_set('f.forum_id', $forbidden_forum_ary, true, true),

				'ORDER_BY'	=> 't.topic_last_post_time DESC, t.topic_last_post_id DESC',
			];

			$sql_array['LEFT_JOIN'] = [];
			$sql_array['LEFT_JOIN'][] = ['FROM' => [$this->tables['topics'] => 't'], 'ON' => 'b.topic_id = t.topic_id'];
		}

		$sql_array['LEFT_JOIN'][] = ['FROM' => [$this->tables['forums'] => 'f'], 'ON' => 't.forum_id = f.forum_id'];

		if ($this->config['load_db_lastread'])
		{
			$sql_array['LEFT_JOIN'][] = ['FROM' => [$this->tables['forums_track'] => 'ft'], 'ON' => 'ft.forum_id = t.forum_id AND ft.user_id = ' . $this->user->data['user_id']];
			$sql_array['LEFT_JOIN'][] = ['FROM' => [$this->tables['topics_track'] => 'tt'], 'ON' => 'tt.topic_id = t.topic_id AND tt.user_id = ' . $this->user->data['user_id']];
			$sql_array['SELECT'] .= ', tt.mark_time, ft.mark_time AS forum_mark_time';
		}

		if ($this->config['load_db_track'])
		{
			$sql_array['LEFT_JOIN'][] = ['FROM' => [$this->tables['topics_posted'] => 'tp'], 'ON' => 'tp.topic_id = t.topic_id AND tp.user_id = ' . $this->user->data['user_id']];
			$sql_array['SELECT'] .= ', tp.topic_posted';
		}

		/**
		 * Modify the query used to retrieve the list of subscribed/bookmarked topics
		 *
		 * @event core.ucp_main_topiclist_modify_query
		 * @var array	sql_array				The subscribed/bookmarked topics query
		 * @var array	forbidden_forum_ary		The list of forbidden forums
		 * @var string	mode					The type of topic list ('subscribed' or 'bookmarks')
		 * @since 3.1.10-RC1
		 */
		$vars = [
			'sql_array',
			'forbidden_forum_ary',
			'mode',
		];
		extract($this->dispatcher->trigger_event('core.ucp_main_topiclist_modify_query', compact($vars)));

		$topic_list = $topic_forum_list = $global_announce_list = $rowset = [];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$topic_id = isset($row['b_topic_id']) ? (int) $row['b_topic_id'] : (int) $row['topic_id'];

			$topic_list[] = $topic_id;
			$rowset[$topic_id] = $row;

			$topic_forum_list[(int) $row['forum_id']]['forum_mark_time'] = $this->config['load_db_lastread'] ? $row['forum_mark_time'] : 0;
			$topic_forum_list[(int) $row['forum_id']]['topics'][] = $topic_id;

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
				'TOPIC_FOLDER_IMG_ALT'	=> $this->lang->lang($folder_alt),
				'TOPIC_ICON_IMG'		=> !empty($icons[$row['icon_id']]) ? $icons[$row['icon_id']]['img'] : '',
				'TOPIC_ICON_IMG_WIDTH'	=> !empty($icons[$row['icon_id']]) ? $icons[$row['icon_id']]['width'] : '',
				'TOPIC_ICON_IMG_HEIGHT'	=> !empty($icons[$row['icon_id']]) ? $icons[$row['icon_id']]['height'] : '',
				'ATTACH_ICON_IMG'		=> ($this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $forum_id) && $row['topic_attachment']) ? $this->user->img('icon_topic_attach', $this->lang->lang('TOTAL_ATTACHMENTS')) : '',

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
