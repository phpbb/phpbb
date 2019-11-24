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

class front
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

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
	 * @param \phpbb\auth\auth					$auth			Auth object
	 * @param \phpbb\config\config				$config			Config object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\event\dispatcher			$dispatcher		Event dispatcher object
	 * @param \phpbb\controller\helper			$helper			Controller helper object
	 * @param \phpbb\language\language			$language		Language object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->helper		= $helper;
		$this->language		= $language;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	/**
	 * Display the front page of the User Control Panel (UCP).
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function main()
	{
		$this->language->add_lang('memberlist');

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

		$topic_type = $this->language->lang('VIEW_TOPIC_GLOBAL');
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
			'L_TITLE'			=> $this->language->lang('UCP_MAIN_FRONT'),

			'JOINED'			=> $this->user->format_date($this->user->data['user_regdate']),
			'LAST_ACTIVE'		=> empty($last_active) ? ' - ' : $this->user->format_date($last_active),
			'POSTS'				=> $this->user->data['user_posts'] ? $this->user->data['user_posts'] : 0,
			'POSTS_DAY'			=> $this->language->lang('POST_DAY', $posts_per_day),
			'POSTS_PCT'			=> $this->language->lang('POST_PCT', $percentage),
			'USER_COLOR'		=> !empty($this->user->data['user_colour']) ? $this->user->data['user_colour'] : '',
			'WARNINGS'			=> $this->user->data['user_warnings'] ? $this->user->data['user_warnings'] : 0,

			'U_SEARCH_USER'		=> $this->auth->acl_get('u_search') ? append_sid("{$this->root_path}search.$this->php_ext", 'author_id=' . $this->user->data['user_id'] . '&amp;sr=posts') : '',
		]);

		return $this->helper->render('ucp_main_front.html', $this->language->lang('UCP_MAIN_FRONT'));
	}
}
