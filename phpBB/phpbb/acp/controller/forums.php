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

namespace phpbb\acp\controller;

use phpbb\exception\back_exception;

class forums
{
	/** @var \phpbb\attachment\manager */
	protected $attachment_manager;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\passwords\manager */
	protected $password_manager;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB admin path */
	protected $admin_path;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpBB web path */
	protected $web_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\attachment\manager				$attachment_manager		Attachment manager object
	 * @param \phpbb\auth\auth						$auth					Auth object
	 * @param \phpbb\cache\driver\driver_interface	$cache					Cache object
	 * @param \phpbb\config\config					$config					Config object
	 * @param \phpbb\db\driver\driver_interface		$db						Database object
	 * @param \phpbb\event\dispatcher				$dispatcher				Event dispatcher object
	 * @param \phpbb\acp\helper\controller			$helper					ACP Controller helper object
	 * @param \phpbb\language\language				$lang					Language object
	 * @param \phpbb\log\log						$log					Log object
	 * @param \phpbb\passwords\manager				$password_manager		Password manager object
	 * @param \phpbb\path_helper					$path_helper			Path helper object
	 * @param \phpbb\request\request				$request				Request object
	 * @param \phpbb\template\template				$template				Template object
	 * @param \phpbb\user							$user					User object
	 * @param string								$admin_path				phpBB admin path
	 * @param string								$root_path				phpBB root path
	 * @param string								$php_ext				php File extension
	 * @param array									$tables					phpBB tables
	 */
	public function __construct(
		\phpbb\attachment\manager $attachment_manager,
		\phpbb\auth\auth $auth,
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\passwords\manager $password_manager,
		\phpbb\path_helper $path_helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$admin_path,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->attachment_manager	= $attachment_manager;
		$this->auth					= $auth;
		$this->cache				= $cache;
		$this->config				= $config;
		$this->db					= $db;
		$this->dispatcher			= $dispatcher;
		$this->helper				= $helper;
		$this->lang					= $lang;
		$this->log					= $log;
		$this->password_manager		= $password_manager;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->admin_path			= $admin_path;
		$this->root_path			= $root_path;
		$this->web_path				= $path_helper->update_web_root_path($root_path);
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	public function main($p = 0, $action = '', $f = 0)
	{
		$this->lang->add_lang('acp/forums');

		$action		= $action ? $action : $this->request->variable('action', '');
		$forum_id	= $f;
		$parent_id	= $p;
		$update		= $this->request->is_set_post('update');
		$errors		= [];
		$forum_data	= [];

		$form_key = 'acp_forums';
		add_form_key($form_key);

		if ($update && !check_form_key($form_key))
		{
			$update = false;
			$errors[] = $this->lang->lang('FORM_INVALID');
		}

		// Check additional permissions
		switch ($action)
		{
			case 'progress_bar':
				$start = $this->request->variable('start', 0);
				$total = $this->request->variable('total', 0);

				return $this->display_progress_bar($start, $total);
			break;

			case 'delete':
				if (!$this->auth->acl_get('a_forumdel'))
				{
					throw new back_exception(403, 'NO_PERMISSION_FORUM_DELETE', ['acp_forums_manage', 'p' => $parent_id]);
				}
			break;

			case 'add':
				if (!$this->auth->acl_get('a_forumadd'))
				{
					throw new back_exception(403, 'NO_PERMISSION_FORUM_ADD', ['acp_forums_manage', 'p' => $parent_id]);
				}
			break;
		}

		// Major routines
		if ($update)
		{
			switch ($action)
			{
				case 'delete':
					$action_subforums	= $this->request->variable('action_subforums', '');
					$subforums_to_id	= $this->request->variable('subforums_to_id', 0);
					$action_posts		= $this->request->variable('action_posts', '');
					$posts_to_id		= $this->request->variable('posts_to_id', 0);

					$errors = $this->delete_forum($forum_id, $action_posts, $action_subforums, $posts_to_id, $subforums_to_id);

					if (!empty($errors))
					{
						break;
					}

					$this->auth->acl_clear_prefetch();
					$this->cache->destroy('sql', $this->tables['forums']);

					return $this->helper->message_back('FORUM_DELETED', 'acp_forums_manage', ['p' => $parent_id]);
				break;

				/** @noinspection PhpMissingBreakStatementInspection */
				case 'edit':
					$forum_data = [
						'forum_id'		=>	$forum_id,
					];
				// No break here

				case 'add':
					$forum_data += [
						'parent_id'				=> $this->request->variable('forum_parent_id', $parent_id),
						'forum_type'			=> $this->request->variable('forum_type', FORUM_POST),
						'type_action'			=> $this->request->variable('type_action', ''),
						'forum_status'			=> $this->request->variable('forum_status', ITEM_UNLOCKED),
						'forum_parents'			=> '',
						'forum_name'			=> $this->request->variable('forum_name', '', true),
						'forum_link'			=> $this->request->variable('forum_link', ''),
						'forum_link_track'		=> $this->request->variable('forum_link_track', false),
						'forum_desc'			=> $this->request->variable('forum_desc', '', true),
						'forum_desc_uid'		=> '',
						'forum_desc_options'	=> 7,
						'forum_desc_bitfield'	=> '',
						'forum_rules'			=> $this->request->variable('forum_rules', '', true),
						'forum_rules_uid'		=> '',
						'forum_rules_options'	=> 7,
						'forum_rules_bitfield'	=> '',
						'forum_rules_link'		=> $this->request->variable('forum_rules_link', ''),
						'forum_image'			=> $this->request->variable('forum_image', ''),
						'forum_style'			=> $this->request->variable('forum_style', 0),
						'display_subforum_list'	=> $this->request->variable('display_subforum_list', false),
						'display_on_index'		=> $this->request->variable('display_on_index', false),
						'forum_topics_per_page'	=> $this->request->variable('topics_per_page', 0),
						'enable_indexing'		=> $this->request->variable('enable_indexing', true),
						'enable_icons'			=> $this->request->variable('enable_icons', false),
						'enable_prune'			=> $this->request->variable('enable_prune', false),
						'enable_post_review'	=> $this->request->variable('enable_post_review', true),
						'enable_quick_reply'	=> $this->request->variable('enable_quick_reply', false),
						'enable_shadow_prune'	=> $this->request->variable('enable_shadow_prune', false),
						'prune_days'			=> $this->request->variable('prune_days', 7),
						'prune_viewed'			=> $this->request->variable('prune_viewed', 7),
						'prune_freq'			=> $this->request->variable('prune_freq', 1),
						'prune_old_polls'		=> $this->request->variable('prune_old_polls', false),
						'prune_announce'		=> $this->request->variable('prune_announce', false),
						'prune_sticky'			=> $this->request->variable('prune_sticky', false),
						'prune_shadow_days'		=> $this->request->variable('prune_shadow_days', 7),
						'prune_shadow_freq'		=> $this->request->variable('prune_shadow_freq', 1),
						'forum_password'		=> $this->request->variable('forum_password', '', true),
						'forum_password_confirm'=> $this->request->variable('forum_password_confirm', '', true),
						'forum_password_unset'	=> $this->request->variable('forum_password_unset', false),
					];

					/**
					 * Request forum data and operate on it (parse texts, etc.)
					 *
					 * @event core.acp_manage_forums_request_data
					 * @var string	action		Type of the action: add|edit
					 * @var array	forum_data	Array with new forum data
					 * @since 3.1.0-a1
					 */
					$vars = ['action', 'forum_data'];
					extract($this->dispatcher->trigger_event('core.acp_manage_forums_request_data', compact($vars)));

					// On add, add empty forum_options... else do not consider it (not updating it)
					if ($action === 'add')
					{
						$forum_data['forum_options'] = 0;
					}

					// Use link_display_on_index setting if forum type is link
					if ($forum_data['forum_type'] == FORUM_LINK)
					{
						$forum_data['display_on_index'] = $this->request->variable('link_display_on_index', false);
					}

					// Linked forums and categories are not able to be locked...
					if ($forum_data['forum_type'] == FORUM_LINK || $forum_data['forum_type'] == FORUM_CAT)
					{
						$forum_data['forum_status'] = ITEM_UNLOCKED;
					}

					$forum_data['show_active'] = ($forum_data['forum_type'] == FORUM_POST) ? $this->request->variable('display_recent', true) : $this->request->variable('display_active', false);

					// Get data for forum rules if specified...
					if ($forum_data['forum_rules'])
					{
						generate_text_for_storage(
							$forum_data['forum_rules'],
							$forum_data['forum_rules_uid'],
							$forum_data['forum_rules_bitfield'],
							$forum_data['forum_rules_options'],
							$this->request->variable('rules_parse_bbcode', false),
							$this->request->variable('rules_parse_urls', false),
							$this->request->variable('rules_parse_smilies', false)
						);
					}

					// Get data for forum description if specified
					if ($forum_data['forum_desc'])
					{
						generate_text_for_storage(
							$forum_data['forum_desc'],
							$forum_data['forum_desc_uid'],
							$forum_data['forum_desc_bitfield'],
							$forum_data['forum_desc_options'],
							$this->request->variable('desc_parse_bbcode', false),
							$this->request->variable('desc_parse_urls', false),
							$this->request->variable('desc_parse_smilies', false)
						);
					}

					$errors = $this->update_forum_data($forum_data, $parent_id);

					if (empty($errors))
					{
						$forum_perm_from = $this->request->variable('forum_perm_from', 0);
						$this->cache->destroy('sql', $this->tables['forums']);

						$copied_permissions = false;
						// Copy permissions?
						if ($forum_perm_from && $forum_perm_from != $forum_data['forum_id'] &&
							($action !== 'edit' || empty($forum_id) || ($this->auth->acl_get('a_fauth') && $this->auth->acl_get('a_authusers') && $this->auth->acl_get('a_authgroups') && $this->auth->acl_get('a_mauth'))))
						{
							copy_forum_permissions($forum_perm_from, $forum_data['forum_id'], ($action === 'edit') ? true : false);
							phpbb_cache_moderators($this->db, $this->cache, $this->auth);
							$copied_permissions = true;
						}

						$this->auth->acl_clear_prefetch();

						$message = $action === 'add' ? $this->lang->lang('FORUM_CREATED') : $this->lang->lang('FORUM_UPDATED');

						// redirect directly to permission settings screen if authed
						if ($action === 'add' && !$copied_permissions && $this->auth->acl_get('a_fauth'))
						{
							$acl_url = $this->helper->route('acp_permissions_forum', ['forum_id[]' => $forum_data['forum_id']]);

							$message .= '<br /><br />' . $this->lang->lang('REDIRECT_ACL', '<a href="' . $acl_url . '">', '</a>');

							meta_refresh(4, $acl_url);
						}

						return $this->helper->message_back($message, 'acp_forums_manage', ['p' => $parent_id]);
					}
				break;
			}
		}

		switch ($action)
		{
			case 'move_up':
			case 'move_down':
				if (!$forum_id)
				{
					throw new back_exception(400, 'NO_FORUM', ['acp_forums_manage', ['p' => $parent_id]]);
				}

				$sql = 'SELECT *
					FROM ' . $this->tables['forums'] . '
					WHERE forum_id = ' . (int) $forum_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row === false)
				{
					throw new back_exception(404, 'NO_FORUM', ['acp_forums_manage', ['p' => $parent_id]]);
				}

				$move_forum_name = $this->move_forum_by($row, $action, 1);

				if ($move_forum_name !== false)
				{
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUM_' . strtoupper($action), false, [$row['forum_name'], $move_forum_name]);
					$this->cache->destroy('sql', $this->tables['forums']);
				}

				if ($this->request->is_ajax())
				{
					$json_response = new \phpbb\json_response;
					$json_response->send(['success' => ($move_forum_name !== false)]);
				}
			break;

			case 'sync':
				if (!$forum_id)
				{
					throw new back_exception(400, 'NO_FORUM', ['acp_forums_manage', ['p' => $parent_id]]);
				}

				@set_time_limit(0);

				$sql = 'SELECT forum_name, (forum_topics_approved + forum_topics_unapproved + forum_topics_softdeleted) AS total_topics
					FROM ' . $this->tables['forums'] . '
					WHERE forum_id = ' . (int) $forum_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row === false)
				{
					throw new back_exception(404, 'NO_FORUM', ['acp_forums_manage', ['p' => $parent_id]]);
				}

				if ($row['total_topics'])
				{
					$sql = 'SELECT MIN(topic_id) as min_topic_id, MAX(topic_id) as max_topic_id
						FROM ' . $this->tables['topics'] . '
						WHERE forum_id = ' . $forum_id;
					$result = $this->db->sql_query($sql);
					$row2 = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					// Typecast to int if there is no data available
					$row2['min_topic_id'] = (int) $row2['min_topic_id'];
					$row2['max_topic_id'] = (int) $row2['max_topic_id'];

					$start = $this->request->variable('start', $row2['min_topic_id']);

					$batch_size = 2000;
					$end = $start + $batch_size;

					// Sync all topics in batch mode...
					sync('topic', 'range', 'topic_id BETWEEN ' . $start . ' AND ' . $end, true, true);

					if ($end < $row2['max_topic_id'])
					{
						// We really need to find a way of showing statistics... no progress here
						$sql = 'SELECT COUNT(topic_id) as num_topics
							FROM ' . $this->tables['topics'] . '
							WHERE forum_id = ' . $forum_id . '
								AND topic_id BETWEEN ' . $start . ' AND ' . $end;
						$result = $this->db->sql_query($sql);
						$topics_done = $this->request->variable('topics_done', 0) + (int) $this->db->sql_fetchfield('num_topics');
						$this->db->sql_freeresult($result);

						$start += $batch_size;

						$url = $this->helper->route('acp_forums_manage', [
							'p'				=> $parent_id,
							'f'				=> $forum_id,
							'action'		=> 'sync',
							'start'			=> $start,
							'total'			=> $row['total_topics'],
							'topics_done'	=> $topics_done,
						]);

						meta_refresh(0, $url);

						$this->template->assign_vars([
							'S_CONTINUE_SYNC'		=> true,
							'L_PROGRESS_EXPLAIN'	=> $this->lang->lang('SYNC_IN_PROGRESS_EXPLAIN', $topics_done, $row['total_topics']),
							'U_PROGRESS_BAR'		=> $this->helper->route('acp_forums_manage', ['p' => 0, 'action' => 'progress_bar', 'start' => $topics_done, 'total' => $row['total_topics']]),
							'UA_PROGRESS_BAR'		=> addslashes($this->helper->route('acp_forums_manage', ['p' => 0, 'action' => 'progress_bar', 'start' => $topics_done, 'total' => $row['total_topics']])),
						]);
					}
				}

				$url = $this->helper->route('acp_forums_manage', ['p' => $parent_id, 'f' => $forum_id, 'action' => 'sync_forum']);
				meta_refresh(0, $url);

				$this->template->assign_vars([
					'S_CONTINUE_SYNC'		=> true,
					'L_PROGRESS_EXPLAIN'	=> $this->lang->lang('SYNC_IN_PROGRESS_EXPLAIN', 0 , $row['total_topics']),
					'U_PROGRESS_BAR'		=> $this->helper->route('acp_forums_manage', ['p' => $parent_id, 'action' => 'progress_bar']),
					'UA_PROGRESS_BAR'		=> addslashes($this->helper->route('acp_forums_manage', ['p' => $parent_id, 'action' => 'progress_bar'])),
				]);

				return $this->helper->render('acp_forums.html', 'ACP_FORUMS_MANAGE');
			break;

			case 'sync_forum':
				$sql = 'SELECT forum_name, forum_type
					FROM ' . $this->tables['forums'] . '
					WHERE forum_id = ' . (int) $forum_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row === false)
				{
					throw new back_exception(400, 'NO_FORUM', ['acp_forums_manage', ['p' => $parent_id]]);
				}

				sync('forum', 'forum_id', $forum_id, false, true);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUM_SYNC', false, [$row['forum_name']]);

				$this->cache->destroy('sql', $this->tables['forums']);

				$this->template->assign_var('L_FORUM_RESYNCED', $this->lang->lang('FORUM_RESYNCED', $row['forum_name']));
			break;

			case 'add':
			case 'edit':
				if ($update)
				{
					$forum_data['forum_flags'] = 0;
					$forum_data['forum_flags'] += ($this->request->variable('forum_link_track', false)) ? FORUM_FLAG_LINK_TRACK : 0;
					$forum_data['forum_flags'] += ($this->request->variable('prune_old_polls', false)) ? FORUM_FLAG_PRUNE_POLL : 0;
					$forum_data['forum_flags'] += ($this->request->variable('prune_announce', false)) ? FORUM_FLAG_PRUNE_ANNOUNCE : 0;
					$forum_data['forum_flags'] += ($this->request->variable('prune_sticky', false)) ? FORUM_FLAG_PRUNE_STICKY : 0;
					$forum_data['forum_flags'] += ($forum_data['show_active']) ? FORUM_FLAG_ACTIVE_TOPICS : 0;
					$forum_data['forum_flags'] += ($this->request->variable('enable_post_review', true)) ? FORUM_FLAG_POST_REVIEW : 0;
					$forum_data['forum_flags'] += ($this->request->variable('enable_quick_reply', false)) ? FORUM_FLAG_QUICK_REPLY : 0;
				}

				// Show form to create/modify a forum
				if ($action === 'edit')
				{
					$row = $this->get_forum_info($forum_id);

					$old_forum_type = $row['forum_type'];

					if (!$update)
					{
						$forum_data = $row;
					}
					else
					{
						$forum_data['left_id'] = $row['left_id'];
						$forum_data['right_id'] = $row['right_id'];
					}

					// Make sure no direct child forums are able to be selected as parents.
					$exclude_forums = [];
					foreach (get_forum_branch($forum_id, 'children') as $row)
					{
						$exclude_forums[] = $row['forum_id'];
					}

					$parents_list = make_forum_select($forum_data['parent_id'], $exclude_forums, false, false, false);

					$forum_data['forum_password_confirm'] = $forum_data['forum_password'];
				}
				else
				{
					// Initialise $row, so we always have it in the event
					$row = [];

					$forum_id = $parent_id;
					$parents_list = make_forum_select($parent_id, false, false, false, false);

					// Fill forum data with default values
					if (!$update)
					{
						$forum_data = [
							'parent_id'				=> $parent_id,
							'forum_type'			=> FORUM_POST,
							'forum_status'			=> ITEM_UNLOCKED,
							'forum_name'			=> $this->request->variable('forum_name', '', true),
							'forum_link'			=> '',
							'forum_link_track'		=> false,
							'forum_desc'			=> '',
							'forum_rules'			=> '',
							'forum_rules_link'		=> '',
							'forum_image'			=> '',
							'forum_style'			=> 0,
							'display_subforum_list'	=> true,
							'display_on_index'		=> false,
							'forum_topics_per_page'	=> 0,
							'enable_indexing'		=> true,
							'enable_icons'			=> false,
							'enable_prune'			=> false,
							'prune_days'			=> 7,
							'prune_viewed'			=> 7,
							'prune_freq'			=> 1,
							'enable_shadow_prune'		=> false,
							'prune_shadow_days'		=> 7,
							'prune_shadow_freq'		=> 1,
							'forum_flags'			=> FORUM_FLAG_POST_REVIEW + FORUM_FLAG_ACTIVE_TOPICS,
							'forum_options'			=> 0,
							'forum_password'		=> '',
							'forum_password_confirm'=> '',
						];
					}
				}

				/**
				 * Initialise data before we display the add/edit form
				 *
				 * @event core.acp_manage_forums_initialise_data
				 * @var string	action			Type of the action: add|edit
				 * @var bool	update			Do we display the form only or did the user press submit
				 * @var int		forum_id		When editing: the forum id, when creating: the parent forum id
				 * @var array	row				Array with current forum data empty when creating new forum
				 * @var array	forum_data		Array with new forum data
				 * @var string	parents_list	List of parent options
				 * @since 3.1.0-a1
				 */
				$vars = ['action', 'update', 'forum_id', 'row', 'forum_data', 'parents_list'];
				extract($this->dispatcher->trigger_event('core.acp_manage_forums_initialise_data', compact($vars)));

				$forum_rules_data = [
					'text'			=> $forum_data['forum_rules'],
					'allow_bbcode'	=> true,
					'allow_smilies'	=> true,
					'allow_urls'	=> true,
				];

				$forum_desc_data = [
					'text'			=> $forum_data['forum_desc'],
					'allow_bbcode'	=> true,
					'allow_smilies'	=> true,
					'allow_urls'	=> true,
				];

				$forum_rules_preview = '';

				// Parse rules if specified
				if ($forum_data['forum_rules'])
				{
					if (!isset($forum_data['forum_rules_uid']))
					{
						// Before we are able to display the preview and plane text, we need to parse our $this->request->variable()'d value...
						$forum_data['forum_rules_uid'] = '';
						$forum_data['forum_rules_bitfield'] = '';
						$forum_data['forum_rules_options'] = 0;

						generate_text_for_storage(
							$forum_data['forum_rules'],
							$forum_data['forum_rules_uid'],
							$forum_data['forum_rules_bitfield'],
							$forum_data['forum_rules_options'],
							$this->request->variable('rules_allow_bbcode', false),
							$this->request->variable('rules_allow_urls', false),
							$this->request->variable('rules_allow_smilies', false)
						);
					}

					// Generate preview content
					$forum_rules_preview = generate_text_for_display($forum_data['forum_rules'], $forum_data['forum_rules_uid'], $forum_data['forum_rules_bitfield'], $forum_data['forum_rules_options']);

					// decode...
					$forum_rules_data = generate_text_for_edit($forum_data['forum_rules'], $forum_data['forum_rules_uid'], $forum_data['forum_rules_options']);
				}

				// Parse description if specified
				if ($forum_data['forum_desc'])
				{
					if (!isset($forum_data['forum_desc_uid']))
					{
						// Before we are able to display the preview and plane text, we need to parse our $this->request->variable()'d value...
						$forum_data['forum_desc_uid'] = '';
						$forum_data['forum_desc_bitfield'] = '';
						$forum_data['forum_desc_options'] = 0;

						generate_text_for_storage(
							$forum_data['forum_desc'],
							$forum_data['forum_desc_uid'],
							$forum_data['forum_desc_bitfield'],
							$forum_data['forum_desc_options'],
							$this->request->variable('desc_allow_bbcode', false),
							$this->request->variable('desc_allow_urls', false),
							$this->request->variable('desc_allow_smilies', false)
						);
					}

					// decode...
					$forum_desc_data = generate_text_for_edit($forum_data['forum_desc'], $forum_data['forum_desc_uid'], $forum_data['forum_desc_options']);
				}

				$forum_type_options = '';
				$forum_type_ary = [FORUM_CAT => 'CAT', FORUM_POST => 'FORUM', FORUM_LINK => 'LINK'];

				foreach ($forum_type_ary as $value => $lang)
				{
					$forum_type_options .= '<option value="' . $value . '"' . (($value == $forum_data['forum_type']) ? ' selected="selected"' : '') . '>' . $this->lang->lang('TYPE_' . $lang) . '</option>';
				}

				$styles_list = style_select($forum_data['forum_style'], true);

				$status_list = '<option value="' . ITEM_UNLOCKED . '"' . (($forum_data['forum_status'] == ITEM_UNLOCKED) ? ' selected="selected"' : '') . '>' . $this->lang->lang('UNLOCKED') . '</option><option value="' . ITEM_LOCKED . '"' . (($forum_data['forum_status'] == ITEM_LOCKED) ? ' selected="selected"' : '') . '>' . $this->lang->lang('LOCKED') . '</option>';

				$postable_forum_exists = false;

				$sql = 'SELECT forum_id
					FROM ' . $this->tables['forums'] . '
					WHERE forum_type = ' . FORUM_POST . '
						AND forum_id <> ' . (int) $forum_id;
				$result = $this->db->sql_query_limit($sql, 1);
				if ($this->db->sql_fetchrow($result))
				{
					$postable_forum_exists = true;
				}
				$this->db->sql_freeresult($result);

				// Subforum move options
				if ($action === 'edit' && $forum_data['forum_type'] == FORUM_CAT)
				{
					$subforums_id = [];
					$subforums = get_forum_branch($forum_id, 'children');

					foreach ($subforums as $row)
					{
						$subforums_id[] = $row['forum_id'];
					}

					$forums_list = make_forum_select($forum_data['parent_id'], $subforums_id);

					if ($postable_forum_exists)
					{
						$this->template->assign_var('S_MOVE_FORUM_OPTIONS', make_forum_select($forum_data['parent_id'], $subforums_id)); // , false, true, false???
					}

					$this->template->assign_vars([
						'S_HAS_SUBFORUMS'		=> ($forum_data['right_id'] - $forum_data['left_id'] > 1) ? true : false,
						'S_FORUMS_LIST'			=> $forums_list,
					]);
				}
				else if ($postable_forum_exists)
				{
					$this->template->assign_var('S_MOVE_FORUM_OPTIONS', make_forum_select($forum_data['parent_id'], $forum_id, false, true, false));
				}

				$s_show_display_on_index = false;

				if ($forum_data['parent_id'] > 0)
				{
					// if this forum is a subforum put the "display on index" checkbox
					if ($parent_info = $this->get_forum_info($forum_data['parent_id']))
					{
						if ($parent_info['parent_id'] > 0 || $parent_info['forum_type'] == FORUM_CAT)
						{
							$s_show_display_on_index = true;
						}
					}
				}

				if (strlen($forum_data['forum_password']) == 32)
				{
					$errors[] = $this->lang->lang('FORUM_PASSWORD_OLD');
				}

				$s_errors = !empty($errors);

				$template_data = [
					'S_EDIT_FORUM'		=> true,
					'S_ERROR'			=> $s_errors,
					'S_PARENT_ID'		=> $parent_id,
					'S_FORUM_PARENT_ID'	=> $forum_data['parent_id'],
					'S_ADD_ACTION'		=> $action === 'add',

					'U_BACK'			=> $this->helper->route('acp_forums_manage', ['p' => $parent_id]),
					'U_EDIT_ACTION'		=> $this->helper->route('acp_forums_manage', ['p' => $parent_id, 'f' => $forum_id, 'action' => $action]),

					'L_COPY_PERMISSIONS_EXPLAIN'	=> $this->lang->lang('COPY_PERMISSIONS_' . strtoupper($action) . '_EXPLAIN'),
					'L_TITLE'						=> $this->lang->lang($action === 'add' ? 'CREATE_FORUM' : 'EDIT_FORUM'),
					'ERROR_MSG'						=> $s_errors ? implode('<br />', $errors) : '',

					'FORUM_NAME'				=> $forum_data['forum_name'],
					'FORUM_DATA_LINK'			=> $forum_data['forum_link'],
					'FORUM_IMAGE'				=> $forum_data['forum_image'],
					'FORUM_IMAGE_SRC'			=> $forum_data['forum_image'] ? $this->web_path . $forum_data['forum_image'] : '',
					'FORUM_POST'				=> FORUM_POST,
					'FORUM_LINK'				=> FORUM_LINK,
					'FORUM_CAT'					=> FORUM_CAT,
					'PRUNE_FREQ'				=> $forum_data['prune_freq'],
					'PRUNE_DAYS'				=> $forum_data['prune_days'],
					'PRUNE_VIEWED'				=> $forum_data['prune_viewed'],
					'PRUNE_SHADOW_FREQ'			=> $forum_data['prune_shadow_freq'],
					'PRUNE_SHADOW_DAYS'			=> $forum_data['prune_shadow_days'],
					'TOPICS_PER_PAGE'			=> $forum_data['forum_topics_per_page'],
					'FORUM_RULES_LINK'			=> $forum_data['forum_rules_link'],
					'FORUM_RULES'				=> $forum_data['forum_rules'],
					'FORUM_RULES_PREVIEW'		=> $forum_rules_preview,
					'FORUM_RULES_PLAIN'			=> $forum_rules_data['text'],
					'S_BBCODE_CHECKED'			=> (bool) $forum_rules_data['allow_bbcode'],
					'S_SMILIES_CHECKED'			=> (bool) $forum_rules_data['allow_smilies'],
					'S_URLS_CHECKED'			=> (bool) $forum_rules_data['allow_urls'],
					'S_FORUM_PASSWORD_SET'		=> (bool) !empty($forum_data['forum_password']),

					'FORUM_DESC'				=> $forum_desc_data['text'],
					'S_DESC_BBCODE_CHECKED'		=> (bool) $forum_desc_data['allow_bbcode'],
					'S_DESC_SMILIES_CHECKED'	=> (bool) $forum_desc_data['allow_smilies'],
					'S_DESC_URLS_CHECKED'		=> (bool) $forum_desc_data['allow_urls'],

					'S_FORUM_TYPE_OPTIONS'		=> $forum_type_options,
					'S_STATUS_OPTIONS'			=> $status_list,
					'S_PARENT_OPTIONS'			=> $parents_list,
					'S_STYLES_OPTIONS'			=> $styles_list,
					'S_FORUM_OPTIONS'			=> make_forum_select(($action === 'add') ? $forum_data['parent_id'] : false, ($action === 'edit') ? $forum_data['forum_id'] : false, false, false, false),
					'S_SHOW_DISPLAY_ON_INDEX'	=> $s_show_display_on_index,
					'S_FORUM_POST'				=> ($forum_data['forum_type'] == FORUM_POST) ? true : false,
					'S_FORUM_ORIG_POST'			=> (isset($old_forum_type) && $old_forum_type == FORUM_POST) ? true : false,
					'S_FORUM_ORIG_CAT'			=> (isset($old_forum_type) && $old_forum_type == FORUM_CAT) ? true : false,
					'S_FORUM_ORIG_LINK'			=> (isset($old_forum_type) && $old_forum_type == FORUM_LINK) ? true : false,
					'S_FORUM_LINK'				=> ($forum_data['forum_type'] == FORUM_LINK) ? true : false,
					'S_FORUM_CAT'				=> ($forum_data['forum_type'] == FORUM_CAT) ? true : false,
					'S_ENABLE_INDEXING'			=> (bool) $forum_data['enable_indexing'],
					'S_TOPIC_ICONS'				=> (bool) $forum_data['enable_icons'],
					'S_DISPLAY_SUBFORUM_LIST'	=> (bool) $forum_data['display_subforum_list'],
					'S_DISPLAY_ON_INDEX'		=> (bool) $forum_data['display_on_index'],
					'S_PRUNE_ENABLE'			=> (bool) $forum_data['enable_prune'],
					'S_PRUNE_SHADOW_ENABLE'		=> (bool) $forum_data['enable_shadow_prune'],
					'S_FORUM_LINK_TRACK'		=> ($forum_data['forum_flags'] & FORUM_FLAG_LINK_TRACK) ? true : false,
					'S_PRUNE_OLD_POLLS'			=> ($forum_data['forum_flags'] & FORUM_FLAG_PRUNE_POLL) ? true : false,
					'S_PRUNE_ANNOUNCE'			=> ($forum_data['forum_flags'] & FORUM_FLAG_PRUNE_ANNOUNCE) ? true : false,
					'S_PRUNE_STICKY'			=> ($forum_data['forum_flags'] & FORUM_FLAG_PRUNE_STICKY) ? true : false,
					'S_DISPLAY_ACTIVE_TOPICS'	=> ($forum_data['forum_type'] == FORUM_POST) ? ($forum_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS) : true,
					'S_ENABLE_ACTIVE_TOPICS'	=> ($forum_data['forum_type'] == FORUM_CAT) ? ($forum_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS) : false,
					'S_ENABLE_POST_REVIEW'		=> ($forum_data['forum_flags'] & FORUM_FLAG_POST_REVIEW) ? true : false,
					'S_ENABLE_QUICK_REPLY'		=> ($forum_data['forum_flags'] & FORUM_FLAG_QUICK_REPLY) ? true : false,
					'S_CAN_COPY_PERMISSIONS'	=> ($action !== 'edit' || empty($forum_id) || ($this->auth->acl_get('a_fauth') && $this->auth->acl_get('a_authusers') && $this->auth->acl_get('a_authgroups') && $this->auth->acl_get('a_mauth'))) ? true : false,
				];

				/**
				 * Modify forum template data before we display the form
				 *
				 * @event core.acp_manage_forums_display_form
				 * @var string	action			Type of the action: add|edit
				 * @var bool	update			Do we display the form only or did the user press submit
				 * @var int		forum_id		When editing: the forum id, when creating: the parent forum id
				 * @var array	row				Array with current forum data empty when creating new forum
				 * @var array	forum_data		Array with new forum data
				 * @var string	parents_list	List of parent options
				 * @var array	errors			Array of errors, if you add errors
				 *									ensure to update the template variables
				 *									S_ERROR and ERROR_MSG to display it
				 * @var array	template_data	Array with new forum data
				 * @since 3.1.0-a1
				 */
				$vars = [
					'action',
					'update',
					'forum_id',
					'row',
					'forum_data',
					'parents_list',
					'errors',
					'template_data',
				];
				extract($this->dispatcher->trigger_event('core.acp_manage_forums_display_form', compact($vars)));

				$this->template->assign_vars($template_data);

				return $this->helper->render('acp_forums.html', $action === 'add' ? 'CREATE_FORUM' : 'EDIT_FORUM');
			break;

			case 'delete':
				if (!$forum_id)
				{
					throw new back_exception(404, 'NO_FORUM', ['acp_forums_manage', ['p' => $parent_id]]);
				}

				$forum_data = $this->get_forum_info($forum_id);

				$subforums_id = [];
				$subforums = get_forum_branch($forum_id, 'children');

				foreach ($subforums as $row)
				{
					$subforums_id[] = $row['forum_id'];
				}

				$forums_list = make_forum_select($forum_data['parent_id'], $subforums_id);

				$sql = 'SELECT forum_id
					FROM ' . $this->tables['forums'] . '
					WHERE forum_type = ' . FORUM_POST . '
						AND forum_id <> ' . (int) $forum_id;
				$result = $this->db->sql_query_limit($sql, 1);
				if ($this->db->sql_fetchrow($result))
				{
					$this->template->assign_var('S_MOVE_FORUM_OPTIONS', make_forum_select($forum_data['parent_id'], $subforums_id, false, true)); // , false, true, false???
				}
				$this->db->sql_freeresult($result);

				$s_errors = !empty($errors);

				$this->template->assign_vars([
					'S_ERROR'				=> $s_errors,
					'ERROR_MSG'				=> $s_errors ? implode('<br />', $errors) : '',

					'FORUM_NAME'			=> $forum_data['forum_name'],

					'S_FORUM_POST'			=> $forum_data['forum_type'] == FORUM_POST,
					'S_FORUM_LINK'			=> $forum_data['forum_type'] == FORUM_LINK,
					'S_HAS_SUBFORUMS'		=> ($forum_data['right_id'] - $forum_data['left_id'] > 1) ? true : false,
					'S_FORUMS_LIST'			=> $forums_list,
					'S_DELETE_FORUM'		=> true,

					'U_ACTION'				=> $this->helper->route('acp_forums_manage', ['p' => ($parent_id === $forum_id ? 0 : $parent_id), 'f' => $forum_id, 'action' => 'delete']),
					'U_BACK'				=> $this->helper->route('acp_forums_manage', ['p' => $parent_id]),
				]);

				return $this->helper->render('acp_forums.html', 'ACP_FORUMS_MANAGE');
			break;

			case 'copy_perm':
				$forum_perm_from = $this->request->variable('forum_perm_from', 0);

				// Copy permissions?
				if (!empty($forum_perm_from) && $forum_perm_from != $forum_id)
				{
					copy_forum_permissions($forum_perm_from, $forum_id, true);
					phpbb_cache_moderators($this->db, $this->cache, $this->auth);

					$this->auth->acl_clear_prefetch();
					$this->cache->destroy('sql', $this->tables['forums']);

					$message = $this->lang->lang('FORUM_UPDATED');

					// Redirect to permissions
					if ($this->auth->acl_get('a_fauth'))
					{
						$acl_url = $this->helper->route('acp_permissions_forum', ['forum_id[]' => $forum_id]);
						$message .= '<br /><br />' . $this->lang->lang('REDIRECT_ACL', '<a href="' . $acl_url . '">', '</a>');
					}

					return $this->helper->message_back($message, 'acp_forums_manage', ['p' => $parent_id]);
				}
			break;
		}

		// Default management page
		if (!$parent_id)
		{
			$navigation = $this->lang->lang('FORUM_INDEX');
		}
		else
		{
			$navigation = '<a href="' . $this->helper->route('acp_forums_manage') . '">' . $this->lang->lang('FORUM_INDEX') . '</a>';

			$forums_nav = get_forum_branch($parent_id, 'parents', 'descending');
			foreach ($forums_nav as $row)
			{
				if ($row['forum_id'] == $parent_id)
				{
					$navigation .= ' -&gt; ' . $row['forum_name'];
				}
				else
				{
					$navigation .= ' -&gt; <a href="' . $this->helper->route('acp_forums_manage', ['p' => $row['forum_id']]) . '">' . $row['forum_name'] . '</a>';
				}
			}
		}

		// Jumpbox
		$forum_box = make_forum_select($parent_id, false, false, false, false); //make_forum_select($this->parent_id);

		if ($action === 'sync' || $action === 'sync_forum')
		{
			$this->template->assign_var('S_RESYNCED', true);
		}

		$rowset = [];

		$sql = 'SELECT *
			FROM ' . $this->tables['forums'] . '
			WHERE parent_id = ' . (int) $parent_id . '
			ORDER BY left_id';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[(int) $row['forum_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		/**
		 * Modify the forum list data
		 *
		 * @event core.acp_manage_forums_modify_forum_list
		 * @var array	rowset		Array with the forums list data
		 * @since 3.1.10-RC1
		 */
		$vars = ['rowset'];
		extract($this->dispatcher->trigger_event('core.acp_manage_forums_modify_forum_list', compact($vars)));

		if (!empty($rowset))
		{
			$img_url = $this->web_path . $this->admin_path . 'images/';

			foreach ($rowset as $row)
			{
				$forum_type = $row['forum_type'];

				if ($row['forum_status'] == ITEM_LOCKED)
				{
					$folder_image = '<img src="' . $img_url . 'icon_folder_lock.gif" alt="' . $this->lang->lang('LOCKED') . '" />';
				}
				else
				{
					switch ($forum_type)
					{
						case FORUM_LINK:
							$folder_image = '<img src="' . $img_url . 'icon_folder_link.gif" alt="' . $this->lang->lang('LINK') . '" />';
						break;

						default:
							$folder_image = ($row['left_id'] + 1 != $row['right_id'])
								? '<img src="' . $img_url . 'icon_subfolder.gif" alt="' . $this->lang->lang('SUBFORUM') . '" />'
								: '<img src="' . $img_url . 'icon_folder.gif" alt="' . $this->lang->lang('FOLDER') . '" />';
						break;
					}
				}

				$this->template->assign_block_vars('forums', [
					'FOLDER_IMAGE'		=> $folder_image,
					'FORUM_IMAGE'		=> ($row['forum_image']) ? '<img src="' . $this->web_path . $row['forum_image'] . '" alt="" />' : '',
					'FORUM_IMAGE_SRC'	=> ($row['forum_image']) ? $this->web_path . $row['forum_image'] : '',
					'FORUM_NAME'		=> $row['forum_name'],
					'FORUM_DESCRIPTION'	=> generate_text_for_display($row['forum_desc'], $row['forum_desc_uid'], $row['forum_desc_bitfield'], $row['forum_desc_options']),
					'FORUM_TOPICS'		=> $row['forum_topics_approved'],
					'FORUM_POSTS'		=> $row['forum_posts_approved'],

					'S_FORUM_LINK'		=> ($forum_type == FORUM_LINK) ? true : false,
					'S_FORUM_POST'		=> ($forum_type == FORUM_POST) ? true : false,

					// @todo generate_link_hash() for moving
					'U_FORUM'			=> $this->helper->route('acp_forums_manage', ['p' => $row['forum_id']]),
					'U_MOVE_UP'			=> $this->helper->route('acp_forums_manage', ['p' => $parent_id, 'f' => $row['forum_id'], 'action' => 'move_up']),
					'U_MOVE_DOWN'		=> $this->helper->route('acp_forums_manage', ['p' => $parent_id, 'f' => $row['forum_id'], 'action' => 'move_down']),
					'U_EDIT'			=> $this->helper->route('acp_forums_manage', ['p' => $parent_id, 'f' => $row['forum_id'], 'action' => 'edit']),
					'U_DELETE'			=> $this->helper->route('acp_forums_manage', ['p' => $parent_id, 'f' => $row['forum_id'], 'action' => 'delete']),
					'U_SYNC'			=> $this->helper->route('acp_forums_manage', ['p' => $parent_id, 'f' => $row['forum_id'], 'action' => 'sync']),
				]);
			}
		}
		else if ($parent_id)
		{
			$row = $this->get_forum_info($parent_id);

			$this->template->assign_vars([
				'S_NO_FORUMS'		=> true,

				'U_EDIT'			=> $this->helper->route('acp_forums_manage', ['p' => $parent_id, 'f' => $row['forum_id'], 'action' => 'edit']),
				'U_DELETE'			=> $this->helper->route('acp_forums_manage', ['p' => $parent_id, 'f' => $row['forum_id'], 'action' => 'delete']),
				'U_SYNC'			=> $this->helper->route('acp_forums_manage', ['p' => $parent_id, 'f' => $row['forum_id'], 'action' => 'sync']),
			]);
		}
		unset($rowset);

		$this->template->assign_vars([
			'ERROR_MSG'		=> !empty($errors) ? implode('<br />', $errors) : '',

			'NAVIGATION'	=> $navigation,
			'FORUM_BOX'		=> $forum_box,

			'U_SEL_ACTION'		=> $this->helper->route('acp_forums_manage'),
			'U_ACTION'			=> $this->helper->route('acp_forums_manage', ['p' => $parent_id]),
			'U_PROGRESS_BAR'	=> $this->helper->route('acp_forums_manage', ['p' => 0, 'action' => 'progress_bar']),
			'UA_PROGRESS_BAR'	=> addslashes($this->helper->route('acp_forums_manage', ['p' => 0, 'action' => 'progress_bar'])),
		]);

		return $this->helper->render('acp_forums.html', 'ACP_FORUMS_MANAGE');
	}

	/**
	 * Get forum data.
	 *
	 * @param int		$forum_id		The forum identifier
	 * @return array					The forum data
	 */
	protected function get_forum_info($forum_id)
	{
		$sql = 'SELECT *
			FROM ' . $this->tables['forums'] . '
			WHERE forum_id = ' . (int) $forum_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row === false)
		{
			throw new back_exception(400, 'FORUM_NOT_EXIST', 'acp_forums_manage');
		}

		return $row;
	}

	/**
	 * Update forum data
	 *
	 * @param array		$forum_data_ary		The forum data
	 * @param int		$parent_id			The forum's parent identifier
	 * @return array						Array possibly filled with errors
	 */
	protected function update_forum_data(array &$forum_data_ary, $parent_id)
	{
		$errors = [];

		$forum_data = $forum_data_ary;
		/**
		 * Validate the forum data before we create/update the forum
		 *
		 * @event core.acp_manage_forums_validate_data
		 * @var array	forum_data	Array with new forum data
		 * @var array	errors		Array of errors, should be strings and not
		 *							language key.
		 * @since 3.1.0-a1
		 */
		$vars = ['forum_data', 'errors'];
		extract($this->dispatcher->trigger_event('core.acp_manage_forums_validate_data', compact($vars)));
		$forum_data_ary = $forum_data;
		unset($forum_data);

		if ($forum_data_ary['forum_name'] == '')
		{
			$errors[] = $this->lang->lang('FORUM_NAME_EMPTY');
		}

		if (utf8_strlen($forum_data_ary['forum_desc']) > 4000)
		{
			$errors[] = $this->lang->lang('FORUM_DESC_TOO_LONG');
		}

		if (utf8_strlen($forum_data_ary['forum_rules']) > 4000)
		{
			$errors[] = $this->lang->lang('FORUM_RULES_TOO_LONG');
		}

		if ($forum_data_ary['forum_password'] || $forum_data_ary['forum_password_confirm'])
		{
			if ($forum_data_ary['forum_password'] != $forum_data_ary['forum_password_confirm'])
			{
				$forum_data_ary['forum_password'] = $forum_data_ary['forum_password_confirm'] = '';
				$errors[] = $this->lang->lang('FORUM_PASSWORD_MISMATCH');
			}
		}

		if ($forum_data_ary['prune_days'] < 0 || $forum_data_ary['prune_viewed'] < 0 || $forum_data_ary['prune_freq'] < 0)
		{
			$forum_data_ary['prune_days'] = $forum_data_ary['prune_viewed'] = $forum_data_ary['prune_freq'] = 0;
			$errors[] = $this->lang->lang('FORUM_DATA_NEGATIVE');
		}

		$range_test_ary = [
			['lang' => 'FORUM_TOPICS_PAGE', 'value' => $forum_data_ary['forum_topics_per_page'], 'column_type' => 'USINT:0'],
		];

		if (!empty($forum_data_ary['forum_image']) && !file_exists($this->root_path . $forum_data_ary['forum_image']))
		{
			$errors[] = $this->lang->lang('FORUM_IMAGE_NO_EXIST');
		}

		validate_range($range_test_ary, $errors);

		// Set forum flags
		// 1 = link tracking
		// 2 = prune old polls
		// 4 = prune announcements
		// 8 = prune stickies
		// 16 = show active topics
		// 32 = enable post review
		$forum_data_ary['forum_flags'] = 0;
		$forum_data_ary['forum_flags'] += ($forum_data_ary['forum_link_track']) ? FORUM_FLAG_LINK_TRACK : 0;
		$forum_data_ary['forum_flags'] += ($forum_data_ary['prune_old_polls']) ? FORUM_FLAG_PRUNE_POLL : 0;
		$forum_data_ary['forum_flags'] += ($forum_data_ary['prune_announce']) ? FORUM_FLAG_PRUNE_ANNOUNCE : 0;
		$forum_data_ary['forum_flags'] += ($forum_data_ary['prune_sticky']) ? FORUM_FLAG_PRUNE_STICKY : 0;
		$forum_data_ary['forum_flags'] += ($forum_data_ary['show_active']) ? FORUM_FLAG_ACTIVE_TOPICS : 0;
		$forum_data_ary['forum_flags'] += ($forum_data_ary['enable_post_review']) ? FORUM_FLAG_POST_REVIEW : 0;
		$forum_data_ary['forum_flags'] += ($forum_data_ary['enable_quick_reply']) ? FORUM_FLAG_QUICK_REPLY : 0;

		// Unset data that are not database fields
		$forum_data_sql = $forum_data_ary;

		unset($forum_data_sql['forum_link_track']);
		unset($forum_data_sql['prune_old_polls']);
		unset($forum_data_sql['prune_announce']);
		unset($forum_data_sql['prune_sticky']);
		unset($forum_data_sql['show_active']);
		unset($forum_data_sql['enable_post_review']);
		unset($forum_data_sql['enable_quick_reply']);
		unset($forum_data_sql['forum_password_confirm']);

		// What are we going to do tonight Brain? The same thing we do every night,
		// try to take over the world ... or decide whether to continue updating
		// and if so, whether it's a new forum/cat/link or an existing one
		if (!empty($errors))
		{
			return $errors;
		}

		// As we don't know the old password, it's kinda tricky to detect changes
		if ($forum_data_sql['forum_password_unset'])
		{
			$forum_data_sql['forum_password'] = '';
		}
		else if (empty($forum_data_sql['forum_password']))
		{
			unset($forum_data_sql['forum_password']);
		}
		else
		{
			$forum_data_sql['forum_password'] = $this->password_manager->hash($forum_data_sql['forum_password']);
		}
		unset($forum_data_sql['forum_password_unset']);

		$forum_data = $forum_data_ary;
		/**
		 * Remove invalid values from forum_data_sql that should not be updated
		 *
		 * @event core.acp_manage_forums_update_data_before
		 * @var array	forum_data		Array with forum data
		 * @var array	forum_data_sql	Array with data we are going to update
		 *								If forum_data_sql[forum_id] is set, we update
		 *								that forum, otherwise a new one is created.
		 * @since 3.1.0-a1
		 */
		$vars = ['forum_data', 'forum_data_sql'];
		extract($this->dispatcher->trigger_event('core.acp_manage_forums_update_data_before', compact($vars)));
		$forum_data_ary = $forum_data;
		unset($forum_data);

		$is_new_forum = !isset($forum_data_sql['forum_id']);

		if ($is_new_forum)
		{
			// no forum_id means we're creating a new forum
			unset($forum_data_sql['type_action']);

			if ($forum_data_sql['parent_id'])
			{
				$sql = 'SELECT left_id, right_id, forum_type
					FROM ' . $this->tables['forums'] . '
					WHERE forum_id = ' . $forum_data_sql['parent_id'];
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row === false)
				{
					throw new back_exception(400, 'PARENT_NOT_EXIST', ['acp_forums_manage', 'p' => $parent_id]);
				}

				if ($row['forum_type'] == FORUM_LINK)
				{
					$errors[] = $this->lang->lang('PARENT_IS_LINK_FORUM');
					return $errors;
				}

				$sql = 'UPDATE ' . $this->tables['forums'] . '
					SET left_id = left_id + 2, right_id = right_id + 2
					WHERE left_id > ' . $row['right_id'];
				$this->db->sql_query($sql);

				$sql = 'UPDATE ' . $this->tables['forums'] . '
					SET right_id = right_id + 2
					WHERE ' . $row['left_id'] . ' BETWEEN left_id AND right_id';
				$this->db->sql_query($sql);

				$forum_data_sql['left_id'] = $row['right_id'];
				$forum_data_sql['right_id'] = $row['right_id'] + 1;
			}
			else
			{
				$sql = 'SELECT MAX(right_id) AS right_id
					FROM ' . $this->tables['forums'];
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$forum_data_sql['left_id'] = $row['right_id'] + 1;
				$forum_data_sql['right_id'] = $row['right_id'] + 2;
			}

			$sql = 'INSERT INTO ' . $this->tables['forums'] . ' ' . $this->db->sql_build_array('INSERT', $forum_data_sql);
			$this->db->sql_query($sql);

			$forum_data_ary['forum_id'] = $this->db->sql_nextid();

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUM_ADD', false, [$forum_data_ary['forum_name']]);
		}
		else
		{
			$row = $this->get_forum_info($forum_data_sql['forum_id']);

			if ($row['forum_type'] == FORUM_POST && $row['forum_type'] != $forum_data_sql['forum_type'])
			{
				// Has subforums and want to change into a link?
				if ($row['right_id'] - $row['left_id'] > 1 && $forum_data_sql['forum_type'] == FORUM_LINK)
				{
					$errors[] = $this->lang->lang('FORUM_WITH_SUBFORUMS_NOT_TO_LINK');
					return $errors;
				}

				// we're turning a postable forum into a non-postable forum
				if ($forum_data_sql['type_action'] == 'move')
				{
					$to_forum_id = $this->request->variable('to_forum_id', 0);

					if ($to_forum_id)
					{
						$errors = $this->move_forum_content($forum_data_sql['forum_id'], $to_forum_id);
					}
					else
					{
						return [$this->lang->lang('NO_DESTINATION_FORUM')];
					}
				}
				else if ($forum_data_sql['type_action'] == 'delete')
				{
					$errors = $this->delete_forum_content($forum_data_sql['forum_id']);
				}
				else
				{
					return [$this->lang->lang('NO_FORUM_ACTION')];
				}

				$forum_data_sql['forum_posts_approved'] = $forum_data_sql['forum_posts_unapproved'] = $forum_data_sql['forum_posts_softdeleted'] = $forum_data_sql['forum_topics_approved'] = $forum_data_sql['forum_topics_unapproved'] = $forum_data_sql['forum_topics_softdeleted'] = 0;
				$forum_data_sql['forum_last_post_id'] = $forum_data_sql['forum_last_poster_id'] = $forum_data_sql['forum_last_post_time'] = 0;
				$forum_data_sql['forum_last_poster_name'] = $forum_data_sql['forum_last_poster_colour'] = '';
			}
			else if ($row['forum_type'] == FORUM_CAT && $forum_data_sql['forum_type'] == FORUM_LINK)
			{
				// Has subforums?
				if ($row['right_id'] - $row['left_id'] > 1)
				{
					// We are turning a category into a link - but need to decide what to do with the subforums.
					$action_subforums = $this->request->variable('action_subforums', '');
					$subforums_to_id = $this->request->variable('subforums_to_id', 0);

					if ($action_subforums == 'delete')
					{
						$rows = get_forum_branch($row['forum_id'], 'children', 'descending', false);

						foreach ($rows as $_row)
						{
							// Do not remove the forum id we are about to change. ;)
							if ($_row['forum_id'] == $row['forum_id'])
							{
								continue;
							}

							$forum_ids[] = $_row['forum_id'];
							$errors = array_merge($errors, $this->delete_forum_content($_row['forum_id']));
						}

						if (!empty($errors))
						{
							return $errors;
						}

						if (!empty($forum_ids))
						{
							$sql = 'DELETE FROM ' . $this->tables['forums'] . '
								WHERE ' . $this->db->sql_in_set('forum_id', $forum_ids);
							$this->db->sql_query($sql);

							$sql = 'DELETE FROM ' . $this->tables['acl_groups'] . '
								WHERE ' . $this->db->sql_in_set('forum_id', $forum_ids);
							$this->db->sql_query($sql);

							$sql = 'DELETE FROM ' . $this->tables['acl_users'] . '
								WHERE ' . $this->db->sql_in_set('forum_id', $forum_ids);
							$this->db->sql_query($sql);

							// Delete forum ids from extension groups table
							$sql = 'SELECT group_id, allowed_forums
								FROM ' . $this->tables['extension_groups'];
							$result = $this->db->sql_query($sql);

							while ($_row = $this->db->sql_fetchrow($result))
							{
								if (!$_row['allowed_forums'])
								{
									continue;
								}

								$allowed_forums = unserialize(trim($_row['allowed_forums']));
								$allowed_forums = array_diff($allowed_forums, $forum_ids);

								$sql = 'UPDATE ' . $this->tables['extension_groups'] . "
									SET allowed_forums = '" . (!empty($allowed_forums) ? serialize($allowed_forums) : '') . "'
									WHERE group_id = {$_row['group_id']}";
								$this->db->sql_query($sql);
							}
							$this->db->sql_freeresult($result);

							$this->cache->destroy('_extensions');
						}
					}
					else if ($action_subforums == 'move')
					{
						if (!$subforums_to_id)
						{
							return [$this->lang->lang('NO_DESTINATION_FORUM')];
						}

						$sql = 'SELECT forum_name
							FROM ' . $this->tables['forums'] . '
							WHERE forum_id = ' . $subforums_to_id;
						$result = $this->db->sql_query($sql);
						$_row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						if (!$_row)
						{
							return [$this->lang->lang('NO_FORUM')];
						}

						$sql = 'SELECT forum_id
							FROM ' . $this->tables['forums'] . "
							WHERE parent_id = {$row['forum_id']}";
						$result = $this->db->sql_query($sql);

						while ($_row = $this->db->sql_fetchrow($result))
						{
							$this->move_forum($_row['forum_id'], $subforums_to_id);
						}
						$this->db->sql_freeresult($result);

						$sql = 'UPDATE ' . $this->tables['forums'] . "
							SET parent_id = $subforums_to_id
							WHERE parent_id = {$row['forum_id']}";
						$this->db->sql_query($sql);
					}

					// Adjust the left/right id
					$sql = 'UPDATE ' . $this->tables['forums'] . '
						SET right_id = left_id + 1
						WHERE forum_id = ' . $row['forum_id'];
					$this->db->sql_query($sql);
				}
			}
			else if ($row['forum_type'] == FORUM_CAT && $forum_data_sql['forum_type'] == FORUM_POST)
			{
				// Changing a category to a forum? Reset the data (you can't post directly in a cat, you must use a forum)
				$forum_data_sql['forum_posts_approved'] = 0;
				$forum_data_sql['forum_posts_unapproved'] = 0;
				$forum_data_sql['forum_posts_softdeleted'] = 0;
				$forum_data_sql['forum_topics_approved'] = 0;
				$forum_data_sql['forum_topics_unapproved'] = 0;
				$forum_data_sql['forum_topics_softdeleted'] = 0;
				$forum_data_sql['forum_last_post_id'] = 0;
				$forum_data_sql['forum_last_post_subject'] = '';
				$forum_data_sql['forum_last_post_time'] = 0;
				$forum_data_sql['forum_last_poster_id'] = 0;
				$forum_data_sql['forum_last_poster_name'] = '';
				$forum_data_sql['forum_last_poster_colour'] = '';
			}

			if (!empty($errors))
			{
				return $errors;
			}

			if ($row['parent_id'] != $forum_data_sql['parent_id'])
			{
				if ($row['forum_id'] != $forum_data_sql['parent_id'])
				{
					$errors = $this->move_forum($forum_data_sql['forum_id'], $forum_data_sql['parent_id']);
				}
				else
				{
					$forum_data_sql['parent_id'] = $row['parent_id'];
				}
			}

			if (!empty($errors))
			{
				return $errors;
			}

			unset($forum_data_sql['type_action']);

			if ($row['forum_name'] != $forum_data_sql['forum_name'])
			{
				// the forum name has changed, clear the parents list of all forums (for safety)
				$sql = 'UPDATE ' . $this->tables['forums'] . "
					SET forum_parents = ''";
				$this->db->sql_query($sql);
			}

			// Setting the forum id to the forum id is not really received well by some dbs. ;)
			$forum_id = $forum_data_sql['forum_id'];
			unset($forum_data_sql['forum_id']);

			$sql = 'UPDATE ' . $this->tables['forums'] . '
				SET ' . $this->db->sql_build_array('UPDATE', $forum_data_sql) . '
				WHERE forum_id = ' . $forum_id;
			$this->db->sql_query($sql);

			// Add it back
			$forum_data_ary['forum_id'] = $forum_id;

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUM_EDIT', false, [$forum_data_ary['forum_name']]);
		}

		$forum_data = $forum_data_ary;
		/**
		 * Event after a forum was updated or created
		 *
		 * @event core.acp_manage_forums_update_data_after
		 * @var array	forum_data		Array with forum data
		 * @var array	forum_data_sql	Array with data we updated
		 * @var bool	is_new_forum	Did we create a forum or update one
		 *								If you want to overwrite this value,
		 *								ensure to set forum_data_sql[forum_id]
		 * @var array	errors			Array of errors, should be strings and not language key.
		 * @since 3.1.0-a1
		 */
		$vars = ['forum_data', 'forum_data_sql', 'is_new_forum', 'errors'];
		extract($this->dispatcher->trigger_event('core.acp_manage_forums_update_data_after', compact($vars)));
		$forum_data_ary = $forum_data;
		unset($forum_data);

		return $errors;
	}

	/**
	 * Move forum.
	 *
	 * @param int		$from_id		The "from" forum identifier
	 * @param int		$to_id			The "to" forum identifier
	 * @return array					An array possibly filled with errors
	 */
	protected function move_forum($from_id, $to_id)
	{
		$errors = [];

		// Check if we want to move to a parent with link type
		if ($to_id > 0)
		{
			$to_data = $this->get_forum_info($to_id);

			if ($to_data['forum_type'] == FORUM_LINK)
			{
				$errors[] = $this->lang->lang('PARENT_IS_LINK_FORUM');
			}
		}

		/**
		 * Event when we move all children of one forum to another
		 *
		 * This event may be triggered, when a forum is deleted
		 *
		 * @event core.acp_manage_forums_move_children
		 * @var int		from_id		If of the current parent forum
		 * @var int		to_id		If of the new parent forum
		 * @var array	errors		Array of errors, should be strings and not language key.
		 * @since 3.1.0-a1
		 */
		$vars = ['from_id', 'to_id', 'errors'];
		extract($this->dispatcher->trigger_event('core.acp_manage_forums_move_children', compact($vars)));

		// Return if there were errors
		if (!empty($errors))
		{
			return $errors;
		}

		$this->db->sql_transaction('begin');

		$moved_forums = get_forum_branch($from_id, 'children', 'descending');
		$from_data = $moved_forums[0];
		$diff = count($moved_forums) * 2;

		$moved_ids = [];
		for ($i = 0, $size = count($moved_forums); $i < $size; ++$i)
		{
			$moved_ids[] = $moved_forums[$i]['forum_id'];
		}

		// Resync parents
		$sql = 'UPDATE ' . $this->tables['forums'] . "
			SET right_id = right_id - $diff, forum_parents = ''
			WHERE left_id < " . $from_data['right_id'] . "
				AND right_id > " . $from_data['right_id'];
		$this->db->sql_query($sql);

		// Resync right-hand side of tree
		$sql = 'UPDATE ' . $this->tables['forums'] . "
			SET left_id = left_id - $diff, right_id = right_id - $diff, forum_parents = ''
			WHERE left_id > " . $from_data['right_id'];
		$this->db->sql_query($sql);

		if ($to_id > 0)
		{
			// Retrieve $to_data again, it may have been changed...
			$to_data = $this->get_forum_info($to_id);

			// Resync new parents
			$sql = 'UPDATE ' . $this->tables['forums'] . "
				SET right_id = right_id + $diff, forum_parents = ''
				WHERE " . $to_data['right_id'] . ' BETWEEN left_id AND right_id
					AND ' . $this->db->sql_in_set('forum_id', $moved_ids, true);
			$this->db->sql_query($sql);

			// Resync the right-hand side of the tree
			$sql = 'UPDATE ' . $this->tables['forums'] . "
				SET left_id = left_id + $diff, right_id = right_id + $diff, forum_parents = ''
				WHERE left_id > " . $to_data['right_id'] . '
					AND ' . $this->db->sql_in_set('forum_id', $moved_ids, true);
			$this->db->sql_query($sql);

			// Resync moved branch
			$to_data['right_id'] += $diff;

			if ($to_data['right_id'] > $from_data['right_id'])
			{
				$diff = '+ ' . ($to_data['right_id'] - $from_data['right_id'] - 1);
			}
			else
			{
				$diff = '- ' . abs($to_data['right_id'] - $from_data['right_id'] - 1);
			}
		}
		else
		{
			$sql = 'SELECT MAX(right_id) AS right_id
				FROM ' . $this->tables['forums'] . '
				WHERE ' . $this->db->sql_in_set('forum_id', $moved_ids, true);
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$diff = '+ ' . ($row['right_id'] - $from_data['left_id'] + 1);
		}

		$sql = 'UPDATE ' . $this->tables['forums'] . "
			SET left_id = left_id $diff, right_id = right_id $diff, forum_parents = ''
			WHERE " . $this->db->sql_in_set('forum_id', $moved_ids);
		$this->db->sql_query($sql);

		$this->db->sql_transaction('commit');

		return $errors;
	}

	/**
	 * Move forum content from one to another forum.
	 *
	 * @param int		$from_id		The "from" forum identifier
	 * @param int		$to_id			The "to" forum identifier
	 * @param bool		$sync			Whether or not the forums should be resynchronised
	 * @return array					An array possibly filled with errors
	 */
	protected function move_forum_content($from_id, $to_id, $sync = true)
	{
		$errors = [];

		/**
		 * Event when we move content from one forum to another
		 *
		 * @event core.acp_manage_forums_move_content
		 * @var int		from_id		If of the current parent forum
		 * @var int		to_id		If of the new parent forum
		 * @var bool	sync		Shall we sync the "to"-forum's data
		 * @var array	errors		Array of errors, should be strings and not
		 *							language key. If this array is not empty,
		 *							The content will not be moved.
		 * @since 3.1.0-a1
		 */
		$vars = ['from_id', 'to_id', 'sync', 'errors'];
		extract($this->dispatcher->trigger_event('core.acp_manage_forums_move_content', compact($vars)));

		// Return if there were errors
		if (!empty($errors))
		{
			return $errors;
		}

		$table_ary = [$this->tables['log'], $this->tables['posts'], $this->tables['topics'], $this->tables['drafts'], $this->tables['topics_track']];

		/**
		 * Perform additional actions before move forum content
		 *
		 * @event core.acp_manage_forums_move_content_sql_before
		 * @var array	table_ary	Array of tables from which forum_id will be updated
		 * @since 3.2.4-RC1
		 */
		$vars = ['table_ary'];
		extract($this->dispatcher->trigger_event('core.acp_manage_forums_move_content_sql_before', compact($vars)));

		foreach ($table_ary as $table)
		{
			$sql = "UPDATE $table
				SET forum_id = " . (int) $to_id . '
					WHERE forum_id = ' . (int) $from_id;
			$this->db->sql_query($sql);
		}
		unset($table_ary);

		$table_ary = [$this->tables['forums_access'], $this->tables['forums_track'], $this->tables['forums_watch'], $this->tables['moderator_cache']];

		foreach ($table_ary as $table)
		{
			$sql = "DELETE FROM $table
				WHERE forum_id = " . (int) $from_id;
			$this->db->sql_query($sql);
		}

		if ($sync)
		{
			// Delete ghost topics that link back to the same forum then resync counters
			sync('topic_moved');
			sync('forum', 'forum_id', $to_id, false, true);
		}

		return [];
	}

	/**
	 * Remove complete forum.
	 *
	 * @param int		$forum_id			The forum identifier
	 * @param string	$action_posts		The action for the forum's posts
	 * @param string	$action_subforums	The action for the forum's subforums
	 * @param int		$posts_to_id		The "to" forum identifier for the posts action
	 * @param int		$subforums_to_id	The "to" forum identifier for the subforums action
	 * @return array						Array possibly filled with errors
	 */
	protected function delete_forum($forum_id, $action_posts = 'delete', $action_subforums = 'delete', $posts_to_id = 0, $subforums_to_id = 0)
	{
		$errors = [];

		$forum_ids = [$forum_id];
		$forum_data = $this->get_forum_info($forum_id);

		$posts_to_name = $subforums_to_name = '';
		$log_action_posts = $log_action_forums = '';

		if ($action_posts === 'delete')
		{
			$log_action_posts = 'POSTS';
			$errors = array_merge($errors, $this->delete_forum_content($forum_id));
		}
		else if ($action_posts === 'move')
		{
			if (!$posts_to_id)
			{
				$errors[] = $this->lang->lang('NO_DESTINATION_FORUM');
			}
			else
			{
				$log_action_posts = 'MOVE_POSTS';

				$sql = 'SELECT forum_name
					FROM ' . $this->tables['forums'] . '
					WHERE forum_id = ' . $posts_to_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$row)
				{
					$errors[] = $this->lang->lang('NO_FORUM');
				}
				else
				{
					$posts_to_name = $row['forum_name'];
					$errors = array_merge($errors, $this->move_forum_content($forum_id, $posts_to_id));
				}
			}
		}

		if (!empty($errors))
		{
			return $errors;
		}

		$diff = 0;

		if ($action_subforums === 'delete')
		{
			$log_action_forums = 'FORUMS';
			$rows = get_forum_branch($forum_id, 'children', 'descending', false);

			foreach ($rows as $row)
			{
				$forum_ids[] = $row['forum_id'];
				$errors = array_merge($errors, $this->delete_forum_content($row['forum_id']));
			}

			if (!empty($errors))
			{
				return $errors;
			}

			$diff = count($forum_ids) * 2;

			$sql = 'DELETE FROM ' . $this->tables['forums'] . '
				WHERE ' . $this->db->sql_in_set('forum_id', $forum_ids);
			$this->db->sql_query($sql);

			$sql = 'DELETE FROM ' . $this->tables['acl_groups'] . '
				WHERE ' . $this->db->sql_in_set('forum_id', $forum_ids);
			$this->db->sql_query($sql);

			$sql = 'DELETE FROM ' . $this->tables['acl_users'] . '
				WHERE ' . $this->db->sql_in_set('forum_id', $forum_ids);
			$this->db->sql_query($sql);
		}
		else if ($action_subforums === 'move')
		{
			if (!$subforums_to_id)
			{
				$errors[] = $this->lang->lang('NO_DESTINATION_FORUM');
			}
			else
			{
				$log_action_forums = 'MOVE_FORUMS';

				$sql = 'SELECT forum_name
					FROM ' . $this->tables['forums'] . '
					WHERE forum_id = ' . $subforums_to_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$row)
				{
					$errors[] = $this->lang->lang('NO_FORUM');
				}
				else
				{
					$subforums_to_name = $row['forum_name'];

					$sql = 'SELECT forum_id
						FROM ' . $this->tables['forums'] . '
						WHERE parent_id = ' . (int) $forum_id;
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						$this->move_forum($row['forum_id'], $subforums_to_id);
					}
					$this->db->sql_freeresult($result);

					// Grab new forum data for correct tree updating later
					$forum_data = $this->get_forum_info($forum_id);

					$sql = 'UPDATE ' . $this->tables['forums'] . '
						SET parent_id = ' . (int) $subforums_to_id . '
						WHERE parent_id = ' . (int) $forum_id;
					$this->db->sql_query($sql);

					$diff = 2;
					$sql = 'DELETE FROM ' . $this->tables['forums'] . '
						WHERE forum_id = ' . (int) $forum_id;
					$this->db->sql_query($sql);

					$sql = 'DELETE FROM ' . $this->tables['acl_groups'] . '
						WHERE forum_id = ' . (int) $forum_id;
					$this->db->sql_query($sql);

					$sql = 'DELETE FROM ' . $this->tables['acl_users'] . '
						WHERE forum_id = ' . (int) $forum_id;
					$this->db->sql_query($sql);
				}
			}

			if (!empty($errors))
			{
				return $errors;
			}
		}
		else
		{
			$diff = 2;

			$sql = 'DELETE FROM ' . $this->tables['forums'] . '
				WHERE forum_id = ' . (int) $forum_id;
			$this->db->sql_query($sql);

			$sql = 'DELETE FROM ' . $this->tables['acl_groups'] . '
				WHERE forum_id = ' . (int) $forum_id;
			$this->db->sql_query($sql);

			$sql = 'DELETE FROM ' . $this->tables['acl_users'] . '
				WHERE forum_id = ' . (int) $forum_id;
			$this->db->sql_query($sql);
		}

		// Resync tree
		$sql = 'UPDATE ' . $this->tables['forums'] . "
			SET right_id = right_id - $diff
			WHERE left_id < {$forum_data['right_id']} AND right_id > {$forum_data['right_id']}";
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . $this->tables['forums'] . "
			SET left_id = left_id - $diff, right_id = right_id - $diff
			WHERE left_id > {$forum_data['right_id']}";
		$this->db->sql_query($sql);

		// Delete forum ids from extension groups table
		$sql = 'SELECT group_id, allowed_forums
			FROM ' . $this->tables['extension_groups'];
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$row['allowed_forums'])
			{
				continue;
			}

			$allowed_forums = unserialize(trim($row['allowed_forums']));
			$allowed_forums = array_diff($allowed_forums, $forum_ids);

			$sql = 'UPDATE ' . $this->tables['extension_groups'] . "
				SET allowed_forums = '" . (!empty($allowed_forums) ? serialize($allowed_forums) : '') . "'
				WHERE group_id = " . (int) $row['group_id'];
			$this->db->sql_query($sql);
		}
		$this->db->sql_freeresult($result);

		$this->cache->destroy('_extensions');

		$log_action = implode('_', [$log_action_posts, $log_action_forums]);

		switch ($log_action)
		{
			case 'MOVE_POSTS_MOVE_FORUMS':
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUM_DEL_MOVE_POSTS_MOVE_FORUMS', false, [$posts_to_name, $subforums_to_name, $forum_data['forum_name']]);
			break;

			case 'MOVE_POSTS_FORUMS':
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUM_DEL_MOVE_POSTS_FORUMS', false, [$posts_to_name, $forum_data['forum_name']]);
			break;

			case 'POSTS_MOVE_FORUMS':
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUM_DEL_POSTS_MOVE_FORUMS', false, [$subforums_to_name, $forum_data['forum_name']]);
			break;

			case '_MOVE_FORUMS':
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUM_DEL_MOVE_FORUMS', false, [$subforums_to_name, $forum_data['forum_name']]);
			break;

			case 'MOVE_POSTS_':
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUM_DEL_MOVE_POSTS', false, [$posts_to_name, $forum_data['forum_name']]);
			break;

			case 'POSTS_FORUMS':
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUM_DEL_POSTS_FORUMS', false, [$forum_data['forum_name']]);
			break;

			case '_FORUMS':
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUM_DEL_FORUMS', false, [$forum_data['forum_name']]);
			break;

			case 'POSTS_':
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUM_DEL_POSTS', false, [$forum_data['forum_name']]);
			break;

			default:
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUM_DEL_FORUM', false, [$forum_data['forum_name']]);
			break;
		}

		return $errors;
	}

	/**
	 * Delete forum content.
	 *
	 * @param int		$forum_id		The forum identifier
	 * @return array
	 */
	protected function delete_forum_content($forum_id)
	{
		$this->db->sql_transaction('begin');

		$topic_ids = [];

		// Select then delete all attachments
		$sql = 'SELECT a.topic_id
			FROM ' . $this->tables['posts'] . ' p, ' . $this->tables['attachments'] . ' a
			WHERE a.in_message = 0
				AND a.topic_id = p.topic_id
				AND p.forum_id = ' . (int) $forum_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$topic_ids[] = $row['topic_id'];
		}
		$this->db->sql_freeresult($result);

		$this->attachment_manager->delete('topic', $topic_ids, false);

		// Delete shadow topics pointing to topics in this forum
		delete_topic_shadows($forum_id);

		$post_counts = [];

		// Before we remove anything we make sure we are able to adjust the post counts later. ;)
		$sql = 'SELECT poster_id
			FROM ' . $this->tables['posts'] . '
			WHERE forum_id = ' . (int) $forum_id . '
				AND post_postcount = 1
				AND post_visibility = ' . ITEM_APPROVED;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_counts[$row['poster_id']] = !empty($post_counts[$row['poster_id']]) ? $post_counts[$row['poster_id']] + 1 : 1;
		}
		$this->db->sql_freeresult($result);

		switch ($this->db->get_sql_layer())
		{
			case 'mysql4':
			case 'mysqli':
				// Delete everything else and thank MySQL for offering multi-table deletion
				$tables_ary = [
					$this->tables['search_wordmatch']	=> 'post_id',
					$this->tables['reports']			=> 'post_id',
					$this->tables['warnings']			=> 'post_id',
					$this->tables['bookmarks']			=> 'topic_id',
					$this->tables['topics_watch']		=> 'topic_id',
					$this->tables['topics_posted']		=> 'topic_id',
					$this->tables['poll_options']		=> 'topic_id',
					$this->tables['poll_votes']		=> 'topic_id',
				];

				$sql = 'DELETE ' . $this->tables['posts'];
				$sql_using = "\nFROM " . $this->tables['posts'];
				$sql_where = "\nWHERE " . $this->tables['posts'] . ".forum_id = $forum_id\n";

				foreach ($tables_ary as $table => $field)
				{
					$sql .= ", $table ";
					$sql_using .= ", $table ";
					$sql_where .= "\nAND $table.$field = " . $this->tables['posts'] . ".$field";
				}

				$this->db->sql_query($sql . $sql_using . $sql_where);
			break;

			default:
				// Delete everything else and curse your DB for not offering multi-table deletion
				$tables_ary = [
					'post_id'	=>	[
						$this->tables['search_wordmatch'],
						$this->tables['reports'],
						$this->tables['warnings'],
					],

					'topic_id'	=>	[
						$this->tables['bookmarks'],
						$this->tables['topics_watch'],
						$this->tables['topics_posted'],
						$this->tables['poll_options'],
						$this->tables['poll_votes'],
					],
				];

				// Amount of rows we select and delete in one iteration.
				$batch_size = 500;

				foreach ($tables_ary as $field => $tables)
				{
					$start = 0;

					do
					{
						$sql = "SELECT $field
							FROM " . $this->tables['posts'] . '
							WHERE forum_id = ' . $forum_id;
						$result = $this->db->sql_query_limit($sql, $batch_size, $start);

						$ids = [];
						while ($row = $this->db->sql_fetchrow($result))
						{
							$ids[] = $row[$field];
						}
						$this->db->sql_freeresult($result);

						if (!empty($ids))
						{
							$start += count($ids);

							foreach ($tables as $table)
							{
								$this->db->sql_query("DELETE FROM $table WHERE " . $this->db->sql_in_set($field, $ids));
							}
						}
					}
					while (count($ids) === $batch_size);
				}

				unset($ids);
			break;
		}

		$table_ary = [
			$this->tables['forums_access'], $this->tables['forums_track'], $this->tables['forums_watch'],
			$this->tables['moderator_cache'], $this->tables['posts'], $this->tables['log'],
			$this->tables['topics'], $this->tables['topics_track'],
		];

		/**
		 * Perform additional actions before forum content deletion
		 *
		 * @event core.delete_forum_content_before_query
		 * @var array	table_ary	Array of tables from which all rows will be deleted that hold the forum_id
		 * @var int		forum_id	the forum id
		 * @var array	topic_ids	Array of the topic ids from the forum to be deleted
		 * @var array	post_counts	Array of counts of posts in the forum, by poster_id
		 * @since 3.1.6-RC1
		 */
		$vars = [
			'table_ary',
			'forum_id',
			'topic_ids',
			'post_counts',
		];
		extract($this->dispatcher->trigger_event('core.delete_forum_content_before_query', compact($vars)));

		foreach ($table_ary as $table)
		{
			$this->db->sql_query("DELETE FROM $table WHERE forum_id = " . (int) $forum_id);
		}

		// Set forum ids to 0
		$table_ary = [$this->tables['drafts']];

		foreach ($table_ary as $table)
		{
			$this->db->sql_query("UPDATE $table SET forum_id = 0 WHERE forum_id = " . (int) $forum_id);
		}

		// Adjust users post counts
		if (!empty($post_counts))
		{
			foreach ($post_counts as $poster_id => $subtract)
			{
				$sql = 'UPDATE ' . $this->tables['users'] . '
					SET user_posts = 0
					WHERE user_id = ' . (int) $poster_id . '
					AND user_posts < ' . (int) $subtract;
				$this->db->sql_query($sql);

				$sql = 'UPDATE ' . $this->tables['users'] . '
					SET user_posts = user_posts - ' . (int) $subtract . '
					WHERE user_id = ' . (int) $poster_id . '
					AND user_posts >= ' . (int) $subtract;
				$this->db->sql_query($sql);
			}
		}

		$this->db->sql_transaction('commit');

		// Make sure the overall post/topic count is correct...
		$sql = 'SELECT COUNT(post_id) AS stat
			FROM ' . $this->tables['posts'] . '
			WHERE post_visibility = ' . ITEM_APPROVED;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->config->set('num_posts', (int) $row['stat'], false);

		$sql = 'SELECT COUNT(topic_id) AS stat
			FROM ' . $this->tables['topics'] . '
			WHERE topic_visibility = ' . ITEM_APPROVED;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->config->set('num_topics', (int) $row['stat'], false);

		$sql = 'SELECT COUNT(attach_id) as stat
			FROM ' . $this->tables['attachments'];
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->config->set('num_files', (int) $row['stat'], false);

		$sql = 'SELECT SUM(filesize) as stat
			FROM ' . $this->tables['attachments'];
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->config->set('upload_dir_size', (float) $row['stat'], false);

		return [];
	}

	/**
	 * Move forum position by $steps up/down.
	 *
	 * @todo use \phpbb\tree\nestedset ?
	 *
	 * @param array		$forum_row		The forum data
	 * @param string	$action			The move action (move_up|move_down)
	 * @param int		$steps			The step amount
	 * @return string					The targeted forum name
	 */
	protected function move_forum_by(array $forum_row, $action = 'move_up', $steps = 1)
	{
		$target = [];

		/**
		 * Fetch all the siblings between the module's current spot
		 * and where we want to move it to. If there are less than $steps
		 * siblings between the current spot and the target then the
		 * module will move as far as possible
		 */
		$sql = 'SELECT forum_id, forum_name, left_id, right_id
			FROM ' . $this->tables['forums'] . "
			WHERE parent_id = {$forum_row['parent_id']}
				AND " . ($action === 'move_up' ? "right_id < {$forum_row['right_id']} ORDER BY right_id DESC" : "left_id > {$forum_row['left_id']} ORDER BY left_id ASC");
		$result = $this->db->sql_query_limit($sql, $steps);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$target = $row;
		}
		$this->db->sql_freeresult($result);

		if (empty($target))
		{
			// The forum is already on top or bottom
			return false;
		}

		/**
		 * $left_id and $right_id define the scope of the nodes that are affected by the move.
		 * $diff_up and $diff_down are the values to subtract or add to each node's left_id
		 * and right_id in order to move them up or down.
		 * $move_up_left and $move_up_right define the scope of the nodes that are moving
		 * up. Other nodes in the scope of ($left_id, $right_id) are considered to move down.
		 */
		if ($action === 'move_up')
		{
			$left_id = $target['left_id'];
			$right_id = $forum_row['right_id'];

			$diff_up = $forum_row['left_id'] - $target['left_id'];
			$diff_down = $forum_row['right_id'] + 1 - $forum_row['left_id'];

			$move_up_left = $forum_row['left_id'];
			$move_up_right = $forum_row['right_id'];
		}
		else
		{
			$left_id = $forum_row['left_id'];
			$right_id = $target['right_id'];

			$diff_up = $forum_row['right_id'] + 1 - $forum_row['left_id'];
			$diff_down = $target['right_id'] - $forum_row['right_id'];

			$move_up_left = $forum_row['right_id'] + 1;
			$move_up_right = $target['right_id'];
		}

		// Now do the dirty job
		$sql = 'UPDATE ' . $this->tables['forums'] . "
			SET left_id = left_id + CASE
				WHEN left_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			right_id = right_id + CASE
				WHEN right_id BETWEEN {$move_up_left} AND {$move_up_right} THEN -{$diff_up}
				ELSE {$diff_down}
			END,
			forum_parents = ''
			WHERE
				left_id BETWEEN {$left_id} AND {$right_id}
				AND right_id BETWEEN {$left_id} AND {$right_id}";
		$this->db->sql_query($sql);

		return $target['forum_name'];
	}

	/**
	 * Display progress bar for syncing forums.
	 *
	 * @param int		$start
	 * @param int		$total
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function display_progress_bar($start, $total)
	{
		$this->template->assign_vars([
			'L_PROGRESS'			=> $this->lang->lang('SYNC_IN_PROGRESS'),
			'L_PROGRESS_EXPLAIN'	=> ($start && $total) ? $this->lang->lang('SYNC_IN_PROGRESS_EXPLAIN', $start, $total) : $this->lang->lang('SYNC_IN_PROGRESS'),
		]);

		return $this->helper->render('progress_bar.html', 'SYNC_IN_PROGRESS');
	}
}
