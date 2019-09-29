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

namespace phpbb\forum\view;

use phpbb\forum\enumeration\forum_feature_flags;
use phpbb\forum\enumeration\forum_types;

class viewforum_renderer
{
	/**
	 * @var \phpbb\auth\auth
	 */
	private $auth;

	/**
	 * @var \phpbb\config\config
	 */
	private $config;

	/**
	 * @var \phpbb\language\language
	 */
	private $language;

	/**
	 * @var \phpbb\template\template
	 */
	private $template;

	/**
	 * @var \phpbb\user
	 */
	private $user;

	/**
	 * @var @string
	 */
	private $phpbb_root_path;

	/**
	 * @var string
	 */
	private $phpEx;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth			$auth
	 * @param \phpbb\config\config		$config
	 * @param \phpbb\language\language	$language
	 * @param \phpbb\template\template	$template
	 * @param \phpbb\user				$user
	 * @param string					$phpbb_root_path
	 * @param string					$phpEx
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\user $user,
		string $phpbb_root_path,
		string $phpEx)
	{
		$this->auth		= $auth;
		$this->config	= $config;
		$this->language	= $language;
		$this->template	= $template;
		$this->user		= $user;

		$this->phpbb_root_path	= $phpbb_root_path;
		$this->phpEx			= $phpEx;
	}

	/**
	 * Pass general information to the view.
	 *
	 * @param array		$forum_data				Array of forum data.
	 * @param array		$moderators				Array of forum moderators.
	 * @param array		$active_forum_ary		Array of forum tracking information.
	 * @param array		$s_watching_forum		Array of forum subscription information.
	 * @param array		$s_search_hidden_fields	Array to generate hidden fields.
	 * @param string	$s_sort_dir				Sort direction selection box.
	 * @param string	$s_sort_key				Sort key selection box.
	 * @param string	$s_limit_days			Time limit selection box.
	 * @param int		$start					The number of the first topic on the page.
	 * @param string	$u_sort_param			URL sort params.
	 */
	public function render_general_information(
		array $forum_data,
		array $moderators,
		array $active_forum_ary,
		array $s_watching_forum,
		array $s_search_hidden_fields,
		string $s_sort_dir,
		string $s_sort_key,
		string $s_limit_days,
		int $start,
		string $u_sort_param)
	{
		$forum_id = $forum_data['forum_id'];
		$post_alt = ($forum_data['forum_status'] == ITEM_LOCKED) ? $this->language->lang('FORUM_LOCKED') : $this->language->lang('POST_NEW_TOPIC');
		$s_display_active = ($forum_data['forum_type'] == forum_types::FORUM_CATEGORY && ($forum_data['forum_flags'] & forum_feature_flags::FORUM_FLAG_ACTIVE_TOPICS));

		$this->template->assign_vars([
			'MODERATORS' => (!empty($moderators[$forum_id])) ? implode($this->language->lang('COMMA_SEPARATOR'), $moderators[$forum_id]) : '',

			'POST_IMG'						=> ($forum_data['forum_status'] == ITEM_LOCKED) ? $this->user->img('button_topic_locked', $post_alt) : $this->user->img('button_topic_new', $post_alt),
			'NEWEST_POST_IMG'				=> $this->user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),
			'LAST_POST_IMG'					=> $this->user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
			'FOLDER_IMG'					=> $this->user->img('topic_read', 'NO_UNREAD_POSTS'),
			'FOLDER_UNREAD_IMG'				=> $this->user->img('topic_unread', 'UNREAD_POSTS'),
			'FOLDER_HOT_IMG'				=> $this->user->img('topic_read_hot', 'NO_UNREAD_POSTS_HOT'),
			'FOLDER_HOT_UNREAD_IMG'			=> $this->user->img('topic_unread_hot', 'UNREAD_POSTS_HOT'),
			'FOLDER_LOCKED_IMG'				=> $this->user->img('topic_read_locked', 'NO_UNREAD_POSTS_LOCKED'),
			'FOLDER_LOCKED_UNREAD_IMG'		=> $this->user->img('topic_unread_locked', 'UNREAD_POSTS_LOCKED'),
			'FOLDER_STICKY_IMG'				=> $this->user->img('sticky_read', 'POST_STICKY'),
			'FOLDER_STICKY_UNREAD_IMG'		=> $this->user->img('sticky_unread', 'POST_STICKY'),
			'FOLDER_ANNOUNCE_IMG'			=> $this->user->img('announce_read', 'POST_ANNOUNCEMENT'),
			'FOLDER_ANNOUNCE_UNREAD_IMG'	=> $this->user->img('announce_unread', 'POST_ANNOUNCEMENT'),
			'FOLDER_MOVED_IMG'				=> $this->user->img('topic_moved', 'TOPIC_MOVED'),
			'REPORTED_IMG'					=> $this->user->img('icon_topic_reported', 'TOPIC_REPORTED'),
			'UNAPPROVED_IMG'				=> $this->user->img('icon_topic_unapproved', 'TOPIC_UNAPPROVED'),
			'DELETED_IMG'					=> $this->user->img('icon_topic_deleted', 'TOPIC_DELETED'),
			'POLL_IMG'						=> $this->user->img('icon_topic_poll', 'TOPIC_POLL'),
			'GOTO_PAGE_IMG'					=> $this->user->img('icon_post_target', 'GOTO_PAGE'),

			'L_NO_TOPICS' => ($forum_data['forum_status'] == ITEM_LOCKED) ? $this->language->lang('POST_FORUM_LOCKED') : $this->language->lang('NO_TOPICS'),

			'S_DISPLAY_POST_INFO' => ($forum_data['forum_type'] == forum_types::FORUM_POST && ($this->auth->acl_get('f_post', $forum_id) || $this->user->data['user_id'] == ANONYMOUS)) ? true : false,

			'S_IS_POSTABLE'					=> ($forum_data['forum_type'] == forum_types::FORUM_POST) ? true : false,
			'S_USER_CAN_POST'				=> ($this->auth->acl_get('f_post', $forum_id)) ? true : false,
			'S_DISPLAY_ACTIVE'				=> $s_display_active,
			'S_SELECT_SORT_DIR'				=> $s_sort_dir,
			'S_SELECT_SORT_KEY'				=> $s_sort_key,
			'S_SELECT_SORT_DAYS'			=> $s_limit_days,
			'S_TOPIC_ICONS'					=> ($s_display_active && count($active_forum_ary)) ? max($active_forum_ary['enable_icons']) : (($forum_data['enable_icons']) ? true : false),
			'U_WATCH_FORUM_LINK'			=> $s_watching_forum['link'],
			'U_WATCH_FORUM_TOGGLE'			=> $s_watching_forum['link_toggle'],
			'S_WATCH_FORUM_TITLE'			=> $s_watching_forum['title'],
			'S_WATCH_FORUM_TOGGLE'			=> $s_watching_forum['title_toggle'],
			'S_WATCHING_FORUM'				=> $s_watching_forum['is_watching'],
			'S_FORUM_ACTION'				=> append_sid("{$this->phpbb_root_path}viewforum.$this->phpEx", "f=$forum_id" . (($start == 0) ? '' : "&amp;start=$start")),
			'S_DISPLAY_SEARCHBOX'			=> ($this->auth->acl_get('u_search') && $this->auth->acl_get('f_search', $forum_id) && $this->config['load_search']) ? true : false,
			'S_SEARCHBOX_ACTION'			=> append_sid("{$this->phpbb_root_path}search.$this->phpEx"),
			'S_SEARCH_LOCAL_HIDDEN_FIELDS'	=> build_hidden_fields($s_search_hidden_fields),
			'S_SINGLE_MODERATOR'			=> (!empty($moderators[$forum_id]) && count($moderators[$forum_id]) > 1) ? false : true,
			'S_IS_LOCKED'					=> ($forum_data['forum_status'] == ITEM_LOCKED) ? true : false,
			'S_VIEWFORUM'					=> true,

			'U_MCP'				=> ($this->auth->acl_get('m_', $forum_id)) ? append_sid("{$this->phpbb_root_path}mcp.$this->phpEx", "f=$forum_id&amp;i=main&amp;mode=forum_view", true, $this->user->session_id) : '',
			'U_POST_NEW_TOPIC'	=> ($this->auth->acl_get('f_post', $forum_id) || $this->user->data['user_id'] == ANONYMOUS) ? append_sid("{$this->phpbb_root_path}posting.$this->phpEx", 'mode=post&amp;f=' . $forum_id) : '',
			'U_VIEW_FORUM'		=> append_sid("{$this->phpbb_root_path}viewforum.{$this->phpEx}", "f=$forum_id" . ((strlen($u_sort_param)) ? "&amp;$u_sort_param" : '') . (($start == 0) ? '' : "&amp;start=$start")),
			'U_CANONICAL'		=> generate_board_url() . '/' . append_sid("viewforum.{$this->phpEx}", "f=$forum_id" . (($start) ? "&amp;start=$start" : ''), true, ''),
			'U_MARK_TOPICS'		=> ($this->user->data['is_registered'] || $this->config['load_anon_lastread']) ? append_sid("{$this->phpbb_root_path}viewforum.$this->phpEx", 'hash=' . generate_link_hash('global') . "&amp;f=$forum_id&amp;mark=topics&amp;mark_time=" . time()) : '',
		]);
	}

	/**
	 * Sets no read access permission flag in the view layer.
	 */
	public function set_has_no_read_access()
	{
		$this->template->assign_var('S_NO_READ_ACCESS', true);
	}

	/**
	 * Sets the viewforum URL in the view layer.
	 *
	 * @param int $forum_id	The forum ID.
	 * @param int $start	The number of the first topic in the list.
	 */
	public function set_viewforum_url(int $forum_id, int $start)
	{
		$this->template->assign_var('U_VIEW_FORUM', append_sid("{$this->phpbb_root_path}viewforum.{$this->phpEx}", "f=$forum_id" . (($start == 0) ? '' : "&amp;start=$start")));
	}

	/**
	 * Sets a flag that the forum does not have subforums.
	 */
	public function set_has_no_subforums()
	{
		$this->template->assign_var('S_HAS_SUBFORUM', false);
	}

	/**
	 * Sets the topic count in the view layer.
	 *
	 * @param bool|string $count The topic count string to display or false.
	 */
	public function set_topic_count($count)
	{
		$this->template->assign_var('TOTAL_TOPICS', $count);
	}
}
