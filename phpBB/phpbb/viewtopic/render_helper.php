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

namespace phpbb\viewtopic;

class render_helper
{
	/**
	 * @var \phpbb\event\dispatcher_interface
	 */
	protected $dispatcher;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * @var array
	 */
	protected $topic_data;

	/**
	 * @var array
	 */
	protected $poll_data;

	public function __construct(
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\language\language $language,
		\phpbb\template\template $template,
		\phpbb\user $user)
	{
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->template = $template;
		$this->user = $user;

		$this->topic_data = [];
		$this->poll_data = [];
	}

	public function render(array $topic_data, array $poll_data)
	{
		$this->topic_data = $topic_data;
		$this->poll_data = $poll_data;

		$this->render_topic_metadata();

		if (!empty($poll_data))
		{
			$this->render_poll();
		}

		$this->topic_data = [];
		$this->poll_data = [];
	}

	protected function render_topic_metadata()
	{
		/**
		 * Event to modify data before template variables are being assigned
		 *
		 * @event core.viewtopic_assign_template_vars_before
		 * @var	string	base_url			URL to be passed to generate pagination
		 * @var	int		forum_id			Forum ID
		 * @var	int		post_id				Post ID
		 * @var	array	quickmod_array		Array with quick moderation options data
		 * @var	int		start				Pagination information
		 * @var	array	topic_data			Array with topic data
		 * @var	int		topic_id			Topic ID
		 * @var	array	topic_tracking_info	Array with topic tracking data
		 * @var	int		total_posts			Topic total posts count
		 * @var	string	viewtopic_url		URL to the topic page
		 * @since 3.1.0-RC4
		 * @changed 3.1.2-RC1 Added viewtopic_url
		 */
		$vars = array(
			'base_url',
			'forum_id',
			'post_id',
			'quickmod_array',
			'start',
			'topic_data',
			'topic_id',
			'topic_tracking_info',
			'total_posts',
			'viewtopic_url',
		);
		extract($this->dispatcher->trigger_event('core.viewtopic_assign_template_vars_before', compact($vars)));

		//$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_posts, $config['posts_per_page'], $start);

		// Send vars to template
		$this->template->assign_vars([
//			'FORUM_ID' 		=> $forum_id,
//			'FORUM_NAME' 	=> $topic_data['forum_name'],
//			'FORUM_DESC'	=> generate_text_for_display($topic_data['forum_desc'], $topic_data['forum_desc_uid'], $topic_data['forum_desc_bitfield'], $topic_data['forum_desc_options']),
//			'TOPIC_ID' 		=> $topic_id,
//			'TOPIC_TITLE' 	=> $topic_data['topic_title'],
//			'TOPIC_POSTER'	=> $topic_data['topic_poster'],
//
//			'TOPIC_AUTHOR_FULL'		=> get_username_string('full', $topic_data['topic_poster'], $topic_data['topic_first_poster_name'], $topic_data['topic_first_poster_colour']),
//			'TOPIC_AUTHOR_COLOUR'	=> get_username_string('colour', $topic_data['topic_poster'], $topic_data['topic_first_poster_name'], $topic_data['topic_first_poster_colour']),
//			'TOPIC_AUTHOR'			=> get_username_string('username', $topic_data['topic_poster'], $topic_data['topic_first_poster_name'], $topic_data['topic_first_poster_colour']),
//
//			'TOTAL_POSTS'	=> $user->lang('VIEW_TOPIC_POSTS', (int) $total_posts),
//			'U_MCP' 		=> ($auth->acl_get('m_', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=main&amp;mode=topic_view&amp;f=$forum_id&amp;t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start") . ((strlen($u_sort_param)) ? "&amp;$u_sort_param" : ''), true, $user->session_id) : '',
//			'MODERATORS'	=> (isset($forum_moderators[$forum_id]) && count($forum_moderators[$forum_id])) ? implode($user->lang['COMMA_SEPARATOR'], $forum_moderators[$forum_id]) : '',
//
//			'POST_IMG' 			=> ($topic_data['forum_status'] == ITEM_LOCKED) ? $user->img('button_topic_locked', 'FORUM_LOCKED') : $user->img('button_topic_new', 'POST_NEW_TOPIC'),
//			'QUOTE_IMG' 		=> $user->img('icon_post_quote', 'REPLY_WITH_QUOTE'),
//			'REPLY_IMG'			=> ($topic_data['forum_status'] == ITEM_LOCKED || $topic_data['topic_status'] == ITEM_LOCKED) ? $user->img('button_topic_locked', 'TOPIC_LOCKED') : $user->img('button_topic_reply', 'REPLY_TO_TOPIC'),
//			'EDIT_IMG' 			=> $user->img('icon_post_edit', 'EDIT_POST'),
//			'DELETE_IMG' 		=> $user->img('icon_post_delete', 'DELETE_POST'),
//			'DELETED_IMG'		=> $user->img('icon_topic_deleted', 'POST_DELETED_RESTORE'),
//			'INFO_IMG' 			=> $user->img('icon_post_info', 'VIEW_INFO'),
//			'PROFILE_IMG'		=> $user->img('icon_user_profile', 'READ_PROFILE'),
//			'SEARCH_IMG' 		=> $user->img('icon_user_search', 'SEARCH_USER_POSTS'),
//			'PM_IMG' 			=> $user->img('icon_contact_pm', 'SEND_PRIVATE_MESSAGE'),
//			'EMAIL_IMG' 		=> $user->img('icon_contact_email', 'SEND_EMAIL'),
//			'JABBER_IMG'		=> $user->img('icon_contact_jabber', 'JABBER') ,
//			'REPORT_IMG'		=> $user->img('icon_post_report', 'REPORT_POST'),
//			'REPORTED_IMG'		=> $user->img('icon_topic_reported', 'POST_REPORTED'),
//			'UNAPPROVED_IMG'	=> $user->img('icon_topic_unapproved', 'POST_UNAPPROVED'),
//			'WARN_IMG'			=> $user->img('icon_user_warn', 'WARN_USER'),
//
//			'S_IS_LOCKED'			=> ($topic_data['topic_status'] == ITEM_UNLOCKED && $topic_data['forum_status'] == ITEM_UNLOCKED) ? false : true,
//			'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
//			'S_SELECT_SORT_KEY' 	=> $s_sort_key,
//			'S_SELECT_SORT_DAYS' 	=> $s_limit_days,
//			'S_SINGLE_MODERATOR'	=> (!empty($forum_moderators[$forum_id]) && count($forum_moderators[$forum_id]) > 1) ? false : true,
//			'S_TOPIC_ACTION' 		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start")),
//			'S_MOD_ACTION' 			=> $s_quickmod_action,
//
//			'L_RETURN_TO_FORUM'		=> $user->lang('RETURN_TO', $topic_data['forum_name']),
//			'S_VIEWTOPIC'			=> true,
//			'S_UNREAD_VIEW'			=> $view == 'unread',
//			'S_DISPLAY_SEARCHBOX'	=> ($auth->acl_get('u_search') && $auth->acl_get('f_search', $forum_id) && $config['load_search']) ? true : false,
//			'S_SEARCHBOX_ACTION'	=> append_sid("{$phpbb_root_path}search.$phpEx"),
//			'S_SEARCH_LOCAL_HIDDEN_FIELDS'	=> build_hidden_fields($s_search_hidden_fields),
//
//			'S_DISPLAY_POST_INFO'	=> ($topic_data['forum_type'] == FORUM_POST && ($auth->acl_get('f_post', $forum_id) || $user->data['user_id'] == ANONYMOUS)) ? true : false,
//			'S_DISPLAY_REPLY_INFO'	=> ($topic_data['forum_type'] == FORUM_POST && ($auth->acl_get('f_reply', $forum_id) || $user->data['user_id'] == ANONYMOUS)) ? true : false,
//			'S_ENABLE_FEEDS_TOPIC'	=> ($config['feed_topic'] && !phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $topic_data['forum_options'])) ? true : false,
//
//			'U_TOPIC'				=> "{$server_path}viewtopic.$phpEx?f=$forum_id&amp;t=$topic_id",
//			'U_FORUM'				=> $server_path,
//			'U_VIEW_TOPIC' 			=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start") . (strlen($u_sort_param) ? "&amp;$u_sort_param" : '')),
//			'U_CANONICAL'			=> generate_board_url() . '/' . append_sid("viewtopic.$phpEx", "t=$topic_id" . (($start) ? "&amp;start=$start" : ''), true, ''),
//			'U_VIEW_FORUM' 			=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id),
//			'U_VIEW_OLDER_TOPIC'	=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id&amp;view=previous"),
//			'U_VIEW_NEWER_TOPIC'	=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id&amp;view=next"),
//			'U_PRINT_TOPIC'			=> ($auth->acl_get('f_print', $forum_id)) ? $viewtopic_url . '&amp;view=print' : '',
//			'U_EMAIL_TOPIC'			=> ($auth->acl_get('f_email', $forum_id) && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=email&amp;t=$topic_id") : '',
//
//			'U_WATCH_TOPIC'			=> $s_watching_topic['link'],
//			'U_WATCH_TOPIC_TOGGLE'	=> $s_watching_topic['link_toggle'],
//			'S_WATCH_TOPIC_TITLE'	=> $s_watching_topic['title'],
//			'S_WATCH_TOPIC_TOGGLE'	=> $s_watching_topic['title_toggle'],
//			'S_WATCHING_TOPIC'		=> $s_watching_topic['is_watching'],
//
//			'U_BOOKMARK_TOPIC'		=> ($user->data['is_registered'] && $config['allow_bookmarks']) ? $viewtopic_url . '&amp;bookmark=1&amp;hash=' . generate_link_hash("topic_$topic_id") : '',
//			'S_BOOKMARK_TOPIC'		=> ($user->data['is_registered'] && $config['allow_bookmarks'] && $topic_data['bookmarked']) ? $user->lang['BOOKMARK_TOPIC_REMOVE'] : $user->lang['BOOKMARK_TOPIC'],
//			'S_BOOKMARK_TOGGLE'		=> (!$user->data['is_registered'] || !$config['allow_bookmarks'] || !$topic_data['bookmarked']) ? $user->lang['BOOKMARK_TOPIC_REMOVE'] : $user->lang['BOOKMARK_TOPIC'],
//			'S_BOOKMARKED_TOPIC'	=> ($user->data['is_registered'] && $config['allow_bookmarks'] && $topic_data['bookmarked']) ? true : false,
//
//			'U_POST_NEW_TOPIC' 		=> ($auth->acl_get('f_post', $forum_id) || $user->data['user_id'] == ANONYMOUS) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=post&amp;f=$forum_id") : '',
//			'U_POST_REPLY_TOPIC' 	=> ($auth->acl_get('f_reply', $forum_id) || $user->data['user_id'] == ANONYMOUS) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=reply&amp;f=$forum_id&amp;t=$topic_id") : '',
//			'U_BUMP_TOPIC'			=> (bump_topic_allowed($forum_id, $topic_data['topic_bumped'], $topic_data['topic_last_post_time'], $topic_data['topic_poster'], $topic_data['topic_last_poster_id'])) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=bump&amp;f=$forum_id&amp;t=$topic_id&amp;hash=" . generate_link_hash("topic_$topic_id")) : ''
		]);
	}

	protected function render_poll()
	{
		$poll_total = 0;
		$poll_most = 0;

		foreach ($this->poll_data['poll_info'] as $poll_option)
		{
			$poll_total += $poll_option['poll_option_total'];
			$poll_most = max($poll_most, $poll_option['poll_option_total']);
		}

		$parse_flags = (($this->poll_data['poll_info'][0]['bbcode_bitfield']) ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;

		$size = count($this->poll_data['poll_info']);
		for ($i = 0; $i < $size; $i++)
		{
			$this->poll_data['poll_info'][$i]['poll_option_text'] = generate_text_for_display(
				$this->poll_data['poll_info'][$i]['poll_option_text'],
				$this->poll_data['poll_info'][$i]['bbcode_uid'],
				$this->poll_data['poll_info'][$i]['bbcode_bitfield'],
				$parse_flags,
				true
			);
		}

		$this->topic_data['poll_title'] = generate_text_for_display(
			$this->topic_data['poll_title'],
			$this->poll_data['poll_info'][0]['bbcode_uid'],
			$this->poll_data['poll_info'][0]['bbcode_bitfield'],
			$parse_flags,
			true
		);

		$poll_template_data = $poll_options_template_data = [];

		foreach ($this->poll_data['poll_info'] as $poll_option)
		{
			$option_pct = ($poll_total > 0) ? $poll_option['poll_option_total'] / $poll_total : 0;
			$option_pct_txt = sprintf("%.1d%%", round($option_pct * 100));
			$option_pct_rel = ($poll_most > 0) ? $poll_option['poll_option_total'] / $poll_most : 0;
			$option_pct_rel_txt = sprintf("%.1d%%", round($option_pct_rel * 100));
			$option_most_votes = ($poll_option['poll_option_total'] > 0 && $poll_option['poll_option_total'] == $poll_most);

			$poll_options_template_data[] = [
				'POLL_OPTION_ID' 			=> $poll_option['poll_option_id'],
				'POLL_OPTION_CAPTION' 		=> $poll_option['poll_option_text'],
				'POLL_OPTION_RESULT' 		=> $poll_option['poll_option_total'],
				'POLL_OPTION_PERCENT' 		=> $option_pct_txt,
				'POLL_OPTION_PERCENT_REL' 	=> $option_pct_rel_txt,
				'POLL_OPTION_PCT'			=> round($option_pct * 100),
				'POLL_OPTION_WIDTH'     	=> round($option_pct * 250),
				'POLL_OPTION_VOTED'			=> in_array($poll_option['poll_option_id'], $this->poll_data['current_vote_id']),
				'POLL_OPTION_MOST_VOTES'	=> $option_most_votes,
			];
		}

		$poll_end = $this->topic_data['poll_length'] + $this->topic_data['poll_start'];

		$poll_template_data = [
			'POLL_QUESTION'		=> $this->topic_data['poll_title'],
			'TOTAL_VOTES' 		=> $poll_total,
			'POLL_LEFT_CAP_IMG'	=> $this->user->img('poll_left'),
			'POLL_RIGHT_CAP_IMG'=> $this->user->img('poll_right'),

			'L_MAX_VOTES'		=> $this->language->lang('MAX_OPTIONS_SELECT', (int) $this->topic_data['poll_max_options']),
			'L_POLL_LENGTH'		=> ($this->topic_data['poll_length'])
				? sprintf($this->language->lang(($poll_end > time()) ? 'POLL_RUN_TILL' : 'POLL_ENDED_AT'), $this->user->format_date($poll_end))
				: '',

			'S_HAS_POLL'		=> true,
			'S_CAN_VOTE'		=> $this->topic_data['user_can_vote'],
			'S_DISPLAY_RESULTS'	=> $this->topic_data['should_display_poll_results'],
			'S_IS_MULTI_CHOICE'	=> ($this->topic_data['poll_max_options'] > 1),
			'S_POLL_ACTION'		=> $this->topic_data['viewtopic_url'],

			// @todo: this should not look like this...
			'U_VIEW_RESULTS'	=> $this->topic_data['viewtopic_url'] . '&amp;view=viewpoll',
		];

		$viewtopic_url = $this->topic_data['viewtopic_url'];
		$cur_voted_id = $this->poll_data['current_vote_id'];
		$poll_info = $this->poll_data['poll_info'];
		$topic_data = $this->topic_data;
		$vote_counts = $this->poll_data['vote_counts'];
		$voted_id = $this->poll_data['voted_id'];

		/**
		 * Event to add/modify poll template data
		 *
		 * @event core.viewtopic_modify_poll_template_data
		 * @var	array	cur_voted_id					Array with options' IDs current user has voted for
		 * @var	int		poll_end						The poll end time
		 * @var	array	poll_info						Array with the poll information
		 * @var	array	poll_options_template_data		Array with the poll options template data
		 * @var	array	poll_template_data				Array with the common poll template data
		 * @var	int		poll_total						Total poll votes count
		 * @var	int		poll_most						Mostly voted option votes count
		 * @var	array	topic_data						All the information from the topic and forum tables for this topic
		 * @var	string	viewtopic_url					URL to the topic page
		 * @var	array	vote_counts						Array with the vote counts for every poll option
		 * @var	array	voted_id						Array with updated options' IDs current user is voting for
		 * @since 3.1.5-RC1
		 */
		$vars = array(
			'cur_voted_id',
			'poll_end',
			'poll_info',
			'poll_options_template_data',
			'poll_template_data',
			'poll_total',
			'poll_most',
			'topic_data',
			'viewtopic_url',
			'vote_counts',
			'voted_id',
		);
		extract($this->dispatcher->trigger_event('core.viewtopic_modify_poll_template_data', compact($vars)));

		$this->template->assign_block_vars_array('poll_option', $poll_options_template_data);

		$this->template->assign_vars($poll_template_data);

		unset($this->poll_data['poll_info'], $this->poll_data['voted_id']);

		$this->topic_data = $topic_data;

		// @todo: viewtopic_url handling
	}
}
