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

namespace phpbb\forum\render;

/**
 * Render helper for forums.
 */
class helper
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\reader_tracking\reader_tracker */
	protected $read_tracker;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\controller\helper $controller_helper,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\language\language $language,
		\phpbb\path_helper $path_helper,
		\phpbb\reader_tracking\reader_tracker $read_tracker,
		\phpbb\template\template $template,
		\phpbb\user $user)
	{
		$this->config = $config;
		$this->controller_helper = $controller_helper;
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->path_helper = $path_helper;
		$this->read_tracker = $read_tracker;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	 * Renders forum rules.
	 *
	 * @param array $forum_data Array of forum metadata.
	 */
	public function render_forum_rules(array $forum_data)
	{
		if (!$forum_data['forum_rules'] && !$forum_data['forum_rules_link'])
		{
			return;
		}

		$this->template->assign_vars([
			'S_FORUM_RULES'	=> true,
			'U_FORUM_RULES'	=> $forum_data['forum_rules_link'],
			'FORUM_RULES'	=> generate_text_for_display($forum_data['forum_rules'], $forum_data['forum_rules_uid'], $forum_data['forum_rules_bitfield'], $forum_data['forum_rules_options'])
		]);
	}

	public function render_forum_data(array $forum_data, array $active_forum_ary)
	{
		$forum_id = (int) $forum_data['forum_id'];

		$s_display_active = ($forum_data['forum_type'] == FORUM_CAT && ($forum_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS));

		$viewforum_route = ''; // append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$forum_id" . (($start == 0) ? '' : "&amp;start=$start")),
		$mark_read_route = ''; // ($this->user->data['is_registered'] || $this->config['load_anon_lastread']) ? append_sid("{$phpbb_root_path}viewforum.$phpEx", 'hash=' . generate_link_hash('global') . "&amp;f=$forum_id&amp;mark=topics&amp;mark_time=" . time()) : '',

		$s_limit_days = $s_sort_dir = $s_sort_key = '<input type="checkbox">';

		$this->template->assign_vars([
			'MODERATORS'	=> [], //$forum_data['moderators'],

			'L_NO_TOPICS'			=> ($forum_data['forum_status'] == ITEM_LOCKED) ? $this->language->lang('POST_FORUM_LOCKED') : $this->language->lang('NO_TOPICS'),

			'S_DISPLAY_POST_INFO'	=> ($forum_data['forum_type'] == FORUM_POST && ($forum_data['user_can_post'] || $this->user->data['user_id'] == ANONYMOUS)),

			'S_IS_POSTABLE'			=> ($forum_data['forum_type'] == FORUM_POST),
			'S_USER_CAN_POST'		=> $forum_data['user_can_post'],
			'S_DISPLAY_ACTIVE'		=> $s_display_active,

			// @todo: move this.
			'S_SELECT_SORT_DIR'		=> $s_sort_dir,
			'S_SELECT_SORT_KEY'		=> $s_sort_key,
			'S_SELECT_SORT_DAYS'	=> $s_limit_days,

			'S_TOPIC_ICONS'			=> ($s_display_active && count($active_forum_ary)) ? max($active_forum_ary['enable_icons']) : (($forum_data['enable_icons']) ? true : false),
			/*'U_WATCH_FORUM_LINK'	=> $s_watching_forum['link'],
			'U_WATCH_FORUM_TOGGLE'	=> $s_watching_forum['link_toggle'],
			'S_WATCH_FORUM_TITLE'	=> $s_watching_forum['title'],
			'S_WATCH_FORUM_TOGGLE'	=> $s_watching_forum['title_toggle'],
			'S_WATCHING_FORUM'		=> $s_watching_forum['is_watching'],*/
			'S_FORUM_ACTION'		=> $viewforum_route,

			'S_DISPLAY_SEARCHBOX'			=> ($forum_data['user_can_search'] && $this->config['load_search']),
			/*'S_SEARCHBOX_ACTION'			=> append_sid("{$phpbb_root_path}search.$phpEx"),
			'S_SEARCH_LOCAL_HIDDEN_FIELDS'	=> build_hidden_fields($s_search_hidden_fields),*/

			'S_IS_LOCKED'			=> ($forum_data['forum_status'] == ITEM_LOCKED),
			'S_VIEWFORUM'			=> true,

			//'U_MCP'				=> ($auth->acl_get('m_', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "f=$forum_id&amp;i=main&amp;mode=forum_view", true, $this->user->session_id) : '',
			//'U_POST_NEW_TOPIC'	=> ($forum_data['user_can_post'] || $this->user->data['user_id'] == ANONYMOUS) ? append_sid("{$phpbb_root_path}posting.$phpEx", 'mode=post&amp;f=' . $forum_id) : '',
			//'U_VIEW_FORUM'		=> append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$forum_id" . ((strlen($u_sort_param)) ? "&amp;$u_sort_param" : '') . (($start == 0) ? '' : "&amp;start=$start")),
			//'U_CANONICAL'		=> generate_board_url() . '/' . append_sid("viewforum.$phpEx", "f=$forum_id" . (($start) ? "&amp;start=$start" : ''), true, ''),
			'U_MARK_TOPICS'		=> $mark_read_route,
		]);
	}

	/**
	 * Render forum list.
	 *
	 * @param array $forum_data				Data of the root forum.
	 * @param array $forum_rows				Array of immediate children of the root forum.
	 * @param array $subforums				Array of subforums of immediate children forums.
	 * @param array $valid_categories		Boolean array of valid categories.
	 * @param array $forum_tracking_info	Read tracking information.
	 * @param array $forum_moderators		Array of forum moderators.
	 */
	public function render_subforums(
		array $forum_data,
		array $forum_rows,
		array $subforums,
		array $valid_categories,
		array $forum_tracking_info,
		array $forum_moderators)
	{
		$asset_root_path = $phpbb_root_path = $this->path_helper->get_web_root_path();
		$phpEx = $this->path_helper->get_php_ext();
		$route_params = '';
		$visible_forums = 0;

		$root_data = $forum_data;

		/**
		 * Event to perform additional actions before the forum list is being generated
		 *
		 * @event core.display_forums_before
		 * @var	array	forum_moderators	Array with forum moderators list
		 * @var	array	forum_rows			Data array of all forums we display
		 * @var	array	root_data			Array with the root forum data
		 * @since 3.1.4-RC1
		 * @changed 3.3.0-a1 Removed active_forum_ary, display_moderators, return_moderators.
		 */
		$vars = [
			'forum_moderators',
			'forum_rows',
			'root_data',
		];
		extract($this->dispatcher->trigger_event('core.display_forums_before', compact($vars)));
		$forum_data = $root_data;

		if (empty($forum_rows))
		{
			$this->template->assign_var('S_HAS_SUBFORUM', false);
		}

		// Used to tell whatever we have to create a dummy category or not.
		$last_catless = true;
		foreach ($forum_rows as $row)
		{
			// Calculate the route.
			$route = $this->controller_helper->route(
				'phpbb_view_forum',
				[
					'forum_id' => $row['forum_id'],
					'parameters' => $route_params
				]
			);

			// Category
			if ($row['parent_id'] == $forum_data['forum_id'] && $row['forum_type'] == FORUM_CAT)
			{
				// Do not display categories without any forums to display
				if (!array_key_exists($row['forum_id'], $valid_categories))
				{
					continue;
				}

				$cat_row = [
					'S_IS_CAT'				=> true,
					'FORUM_ID'				=> $row['forum_id'],
					'FORUM_NAME'			=> $row['forum_name'],
					'FORUM_DESC'			=> generate_text_for_display($row['forum_desc'], $row['forum_desc_uid'], $row['forum_desc_bitfield'], $row['forum_desc_options']),
					'FORUM_FOLDER_IMG'		=> '',
					'FORUM_FOLDER_IMG_SRC'	=> '',
					'FORUM_IMAGE_ALT'		=> $this->language->lang('FORUM_CAT'),
					'FORUM_IMAGE_SRC'		=> ($row['forum_image']) ? $asset_root_path . $row['forum_image'] : '',
					'U_VIEWFORUM'			=> $route,
				];

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
				$vars = [
					'cat_row',
					'last_catless',
					'root_data',
					'row',
				];
				extract($this->dispatcher->trigger_event('core.display_forums_modify_category_template_vars', compact($vars)));

				$this->template->assign_block_vars('forumrow', $cat_row);

				continue;
			}

			$visible_forums++;
			$forum_id = (int) $row['forum_id'];

			$subforums_list = [];

			$forum_unread = (isset($forum_tracking_info[$forum_id]) && $row['orig_forum_last_post_time'] > $forum_tracking_info[$forum_id]);

			$folder_image = '';
			$l_subforums = '';

			// Generate list of subforums if we need to
			if (array_key_exists($forum_id, $subforums))
			{
				foreach ($subforums[$forum_id] as $subforum_id => $subforum_row)
				{
					// Calculate the route.
					$subforum_route = $this->controller_helper->route(
						'phpbb_view_forum',
						[
							'forum_id' => $subforum_id,
							'parameters' => $route_params
						]
					);

					$subforum_unread = (isset($forum_tracking_info[$subforum_id]) && $subforum_row['orig_forum_last_post_time'] > $forum_tracking_info[$subforum_id]);

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
						$subforums_list[] = [
							'link'		=> $subforum_route,
							'name'		=> $subforum_row['name'],
							'unread'	=> $subforum_unread,
							'type'		=> $subforum_row['type'],
						];
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

				$l_subforums = (count($subforums[$forum_id]) == 1) ? $this->language->lang('SUBFORUM') : $this->language->lang('SUBFORUMS');
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

			$last_post_subject = $last_post_time = $last_post_url = $last_post_subject_truncated = '';

			// Create last post link information, if appropriate
			if ($row['forum_last_post_id'])
			{
				if ($row['forum_password_last_post'] === '' && $row['may_display_last_post'])
				{
					$last_post_subject = censor_text($row['forum_last_post_subject']);
					$last_post_subject_truncated = truncate_string(
						$last_post_subject,
						30,
						255,
						false,
						$this->language->lang('ELLIPSIS')
					);
				}
				else
				{
					$last_post_subject = $last_post_subject_truncated = '';
				}
				$last_post_time = $this->user->format_date($row['forum_last_post_time']);
				$last_post_url = $this->get_viewtopic_url(['p' => $row['forum_last_post_id'], 'f' => $row['forum_id_last_post']]);
			}

			$subforums_row = [];
			foreach ($subforums_list as $subforum)
			{
				$subforums_row[] = [
					'U_SUBFORUM'	=> $subforum['link'],
					'SUBFORUM_NAME'	=> $subforum['name'],
					'S_UNREAD'		=> $subforum['unread'],
					'IS_LINK'		=> $subforum['type'] == FORUM_LINK,
				];
			}

			$l_post_click_count = ($row['forum_type'] == FORUM_LINK) ? 'CLICKS' : 'POSTS';
			$post_click_count = ($row['forum_type'] != FORUM_LINK || $row['forum_flags'] & FORUM_FLAG_LINK_TRACK) ? $row['forum_posts'] : '';

			$catless = ($row['parent_id'] == $forum_data['forum_id']);

			$u_viewforum = '';
			$route_param_ary = ['forum_id' => 0, 'parameters' => $route_params];
			if ($row['forum_type'] != FORUM_LINK)
			{
				$route_param_ary['forum_id'] = $row['forum_id'];
			}
			else
			{
				// If the forum is a link and we count redirects we need to visit it
				// If the forum is having a password or no read access we do not expose the link, but instead handle it in viewforum
				if (($row['forum_flags'] & FORUM_FLAG_LINK_TRACK) || $row['forum_password'] || !$row['user_may_read_forum'])
				{
					$route_param_ary['forum_id'] = $row['forum_id'];
				}
				else
				{
					$u_viewforum = $row['forum_link'];
				}
			}

			$u_viewforum = (!empty($u_viewforum)) ? $u_viewforum : $this->controller_helper->route('phpbb_view_forum', $route_param_ary);

			$l_moderator = '';
			$moderators_list = [];
			if (!empty($forum_moderators) && !empty($forum_moderators[$forum_id]))
			{
				$l_moderator = (count($forum_moderators[$forum_id]) == 1) ? $this->language->lang('MODERATOR') : $this->language->lang('MODERATORS');
				$moderators_list = $forum_moderators[$forum_id];
			}

			$forum_row = [
				'S_IS_CAT'			=> false,
				'S_NO_CAT'			=> $catless && !$last_catless,
				'S_IS_LINK'			=> ($row['forum_type'] == FORUM_LINK),
				'S_UNREAD_FORUM'	=> $forum_unread,
				'S_AUTH_READ'		=> $row['user_may_read_forum'],
				'S_LOCKED_FORUM'	=> ($row['forum_status'] == ITEM_LOCKED),
				'S_LIST_SUBFORUMS'	=> ($row['display_subforum_list']) ? true : false,
				'S_SUBFORUMS'		=> (count($subforums_list)) ? true : false,
				'S_DISPLAY_SUBJECT'	=>	($last_post_subject !== '' && $this->config['display_last_subject']),
				'S_FEED_ENABLED'	=> ($this->config['feed_forum'] && !phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $row['forum_options']) && $row['forum_type'] == FORUM_POST),

				'FORUM_ID'						=> $row['forum_id'],
				'FORUM_NAME'					=> $row['forum_name'],
				'FORUM_DESC'					=> generate_text_for_display($row['forum_desc'], $row['forum_desc_uid'], $row['forum_desc_bitfield'], $row['forum_desc_options']),
				'TOPICS'						=> $row['forum_topics'],
				$l_post_click_count				=> $post_click_count,
				'FORUM_IMG_STYLE'				=> $folder_image,
				'FORUM_FOLDER_IMG_ALT'			=> ($this->language->lang($folder_alt) === $folder_alt) ? '' : $this->language->lang($folder_alt),
				'FORUM_IMAGE_SRC'				=> ($row['forum_image']) ? $asset_root_path . $row['forum_image'] : '',
				'LAST_POST_SUBJECT'				=> $last_post_subject,
				'LAST_POST_SUBJECT_TRUNCATED'	=> $last_post_subject_truncated,
				'LAST_POST_TIME'				=> $last_post_time,
				'LAST_POSTER'					=> get_username_string('username', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
				'LAST_POSTER_COLOUR'			=> get_username_string('colour', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
				'LAST_POSTER_FULL'				=> get_username_string('full', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
				'MODERATORS'					=> $moderators_list,

				'L_SUBFORUM_STR'		=> $l_subforums,
				'L_MODERATOR_STR'		=> $l_moderator,

				'U_UNAPPROVED_TOPICS'	=> ($row['forum_id_unapproved_topics']) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=unapproved_topics&amp;f=' . $row['forum_id_unapproved_topics']) : '',
				'U_UNAPPROVED_POSTS'	=> ($row['forum_id_unapproved_posts']) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=unapproved_posts&amp;f=' . $row['forum_id_unapproved_posts']) : '',
				'U_VIEWFORUM'			=> $u_viewforum,
				'U_LAST_POSTER'			=> get_username_string('profile', $row['forum_last_poster_id'], $row['forum_last_poster_name'], $row['forum_last_poster_colour']),
				'U_LAST_POST'			=> $last_post_url,
			];

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
			extract($this->dispatcher->trigger_event('core.display_forums_modify_template_vars', compact($vars)));

			$this->template->assign_block_vars('forumrow', $forum_row);

			// Assign subforums loop for style authors
			$this->template->assign_block_vars_array('forumrow.subforum', $subforums_row);

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
				'subforums_row',
				'catless',
			);
			extract($this->dispatcher->trigger_event('core.display_forums_add_template_data', compact($vars)));

			$last_catless = $catless;
		}

		$mark_forums_url = '';
		if ($this->user->data['is_registered'] || $this->config['load_anon_lastread'])
		{
			$mark_forums_url = $this->controller_helper->route(
				'phpbb_mark_forum_read',
				[
					'forum_id'	=> $forum_data['forum_id'],
					'time'		=> time(),
					'token'		=> generate_link_hash('global')
				]
			);
		}

		$this->template->assign_vars([
			'U_MARK_FORUMS'			=> $mark_forums_url,
			'S_HAS_SUBFORUM'		=> ($visible_forums) ? true : false,
			'L_SUBFORUM'			=> ($visible_forums == 1) ? $this->language->lang('SUBFORUM') : $this->language->lang('SUBFORUMS'),
		]);

		$root_data = $forum_data;
		/**
		 * Event to perform additional actions after the forum list has been generated
		 *
		 * @event core.display_forums_after
		 * @var	array	forum_moderators	Array with forum moderators list
		 * @var	array	forum_rows			Data array of all forums we display
		 * @var	array	root_data			Array with the root forum data
		 * @since 3.1.0-RC5
		 * @changed 3.3.0-a1 Removed active_forum_ary, display_moderators, return_moderators.
		 */
		$vars = array(
			'forum_moderators',
			'forum_rows',
			'root_data',
		);
		extract($this->dispatcher->trigger_event('core.display_forums_after', compact($vars)));
		unset($root_data);
	}

	/**
	 * Generate breadcrumb navigation for the forum.
	 *
	 * @param array $forum_data		Array of forum metadata.
	 * @param array $forum_parents	Array of the parents of the current forum.
	 */
	public function generate_navigation(array $forum_data, array $forum_parents)
	{
		$navlinks_parents = [];

		if (!empty($forum_parents))
		{
			foreach ($forum_parents as $parent_forum_id => $parent_data)
			{
				list($parent_name, $parent_type) = array_values($parent_data);

				$navlinks_parents[] = [
					'S_IS_CAT'			=> ($parent_type == FORUM_CAT) ? true : false,
					'S_IS_LINK'			=> ($parent_type == FORUM_LINK) ? true : false,
					'S_IS_POST'			=> ($parent_type == FORUM_POST) ? true : false,
					'BREADCRUMB_NAME'	=> $parent_name,
					'FORUM_ID'			=> $parent_forum_id,
					'MICRODATA'			=> 'data-forum-id="' . $parent_forum_id . '"',
					'U_BREADCRUMB'		=> $this->controller_helper->route(
						'phpbb_view_forum',
						['forum_id' => $parent_forum_id, 'parameters' => '']
					)
				];
			}
		}

		$navlinks = array(
			'S_IS_CAT'			=> ($forum_data['forum_type'] == FORUM_CAT),
			'S_IS_LINK'			=> ($forum_data['forum_type'] == FORUM_LINK),
			'S_IS_POST'			=> ($forum_data['forum_type'] == FORUM_POST),
			'BREADCRUMB_NAME'	=> $forum_data['forum_name'],
			'FORUM_ID'			=> $forum_data['forum_id'],
			'MICRODATA'			=> 'data-forum-id="' . $forum_data['forum_id'] . '"',
			'U_BREADCRUMB'		=> $this->controller_helper->route(
				'phpbb_view_forum',
				['forum_id' => $forum_data['forum_id'], 'parameters' => '']
			),
		);

		$forum_template_data = array(
			'FORUM_ID' 		=> $forum_data['forum_id'],
			'FORUM_NAME'	=> $forum_data['forum_name'],
			'FORUM_DESC'	=> generate_text_for_display($forum_data['forum_desc'], $forum_data['forum_desc_uid'], $forum_data['forum_desc_bitfield'], $forum_data['forum_desc_options']),

			'S_ENABLE_FEEDS_FORUM'	=> ($this->config['feed_forum'] && $forum_data['forum_type'] == FORUM_POST && !phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $forum_data['forum_options'])),
		);

		$microdata_attr = 'data-forum-id';
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
		 * @changed 3.3.0-a1 Moved and forum data is read only now.
		 */
		$vars = array(
			'forum_data',
			'forum_template_data',
			'microdata_attr',
			'navlinks_parents',
			'navlinks',
		);
		extract($this->dispatcher->trigger_event('core.generate_forum_nav', compact($vars)));

		$this->template->assign_block_vars_array('navlinks', $navlinks_parents);
		$this->template->assign_block_vars('navlinks', $navlinks);
		$this->template->assign_vars($forum_template_data);
	}

	/**
	 * This method renders the forum login box.
	 *
	 * @param array			$forum_data	Array of the forum's data (the forum_id is required).
	 * @param bool|string	$password	Password that the user entered.
	 * @param bool|string	$return_url	URL where the form should be sent to.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response Response object which contains the rendered page.
	 */
	public function render_forum_password_box($forum_data, $password = false, $return_url = false)
	{
		/**
		 * Performing additional actions, load additional data on forum login
		 *
		 * @event core.login_forum_box
		 * @var	array	forum_data		Array with forum data
		 * @var	string	password		Password entered
		 * @since 3.1.0-RC3
		 */
		$vars = array('forum_data', 'password');
		extract($this->dispatcher->trigger_event('core.login_forum_box', compact($vars)));

		$page_title = $this->language->lang('LOGIN');

		if ($password !== false)
		{
			$this->template->assign_var('LOGIN_ERROR', $this->language->lang('WRONG_PASSWORD'));
		}

		if (!$return_url)
		{
			$return_url = $this->controller_helper->route(
				'phpbb_view_forum',
				[
					'forum_id' => $forum_data['forum_id'],
					'parameters' => ''
				]
			);
		}

		$forum_name = isset($forum_data['forum_name']) ? $forum_data['forum_name'] : '';
		$this->template->assign_vars([
			'FORUM_NAME'			=> $forum_name,
			'S_FORUM_LOGIN_ACTION'	=> $return_url,
			'S_HIDDEN_FIELDS'		=> ['f' => $forum_data['forum_id']]
		]);

		// Note: if you change this, please change it as well in login_forum_box()
		// in functions_compatibility.php.
		add_form_key('forum_password_' . $forum_data['forum_id']);

		return $this->controller_helper->render(
			'login_forum.html',
			$page_title
		);
	}

	/**
	 * Returns a viewtopic url.
	 *
	 * @param array	$params		Array of get params.
	 * @param bool	$use_amp	Whether or not to use the &amp; format.
	 *
	 * @return string Url to a viewtopic page.
	 */
	protected function get_viewtopic_url(array $params, $use_amp = true)
	{
		$query_params = '';
		$postfix = '';

		if (array_key_exists('f', $params))
		{
			$query_params = 'f=' . $params['f'] . (($use_amp) ? '&amp;' : '&');
		}

		if (array_key_exists('p', $params))
		{
			$query_params .= 'p=' . $params['p'];
			$postfix = '#p' . $params['p'];
		}
		else if (array_key_exists('t', $params))
		{
			$query_params .= 't=' . $params['t'];
		}
		else
		{
			return '';
		}

		$base_url = $this->path_helper->get_web_root_path() . 'viewtopic.' . $this->path_helper->get_php_ext();
		return append_sid($base_url, $query_params) . $postfix;
	}
}
