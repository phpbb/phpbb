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

	/** @var forum */
	protected $mcp_forum;

	/** @var front */
	protected $mcp_front;

	/** @var post */
	protected $mcp_post;

	/** @var topic */
	protected $mcp_topic;

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
	 * @param \phpbb\auth\auth					$auth				Auth object
	 * @param \phpbb\config\config				$config				Config object
	 * @param \phpbb\content_visibility			$content_visibility	Content visibility object
	 * @param \phpbb\db\driver\driver_interface	$db					Database object
	 * @param \phpbb\event\dispatcher			$dispatcher			Event dispatcher object
	 * @param \phpbb\language\language			$lang				Language object
	 * @param \phpbb\log\log					$log				Log object
	 * @param \phpbb\mcp\functions\delete		$mcp_delete			MCP Delete functions object
	 * @param \phpbb\mcp\controller\forum		$mcp_forum			MCP Forum controller object
	 * @param \phpbb\mcp\controller\front		$mcp_front			MCP Front controller object
	 * @param \phpbb\mcp\controller\post		$mcp_post			MCP Post controller	object
	 * @param \phpbb\mcp\controller\topic		$mcp_topic			MCP Topic controller object
	 * @param \phpbb\request\request			$request			Request object
	 * @param \phpbb\template\template			$template			Template object
	 * @param \phpbb\user						$user				User object
	 * @param string							$root_path			phpBB root path
	 * @param string							$php_ext			php File extension
	 * @param array								$tables				phpBB tables
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
		forum $mcp_forum,
		front $mcp_front,
		post $mcp_post,
		topic $mcp_topic,
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
		$this->mcp_forum			= $mcp_forum;
		$this->mcp_front			= $mcp_front;
		$this->mcp_post				= $mcp_post;
		$this->mcp_topic			= $mcp_topic;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	function main($id, $mode)
	{
		/** @todo */
		global $action;

		$quickmod = $mode === 'quickmod';

		switch ($action)
		{
			case 'lock':
			case 'unlock':
				$topic_ids = !$quickmod ? $this->request->variable('topic_id_list', [0]) : [$this->request->variable('t', 0)];

				if (empty($topic_ids))
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				$this->lock_unlock($action, $topic_ids);
			break;

			case 'lock_post':
			case 'unlock_post':
				$post_ids = !$quickmod ? $this->request->variable('post_id_list', [0]) : [$this->request->variable('p', 0)];

				if (empty($post_ids))
				{
					trigger_error('NO_POST_SELECTED');
				}

				$this->lock_unlock($action, $post_ids);
			break;

			case 'make_announce':
			case 'make_sticky':
			case 'make_global':
			case 'make_normal':
				$topic_ids = !$quickmod ? $this->request->variable('topic_id_list', [0]) : [$this->request->variable('t', 0)];

				if (empty($topic_ids))
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				$this->change_topic_type($action, $topic_ids);
			break;

			case 'move':
				$this->lang->add_lang('viewtopic');

				$topic_ids = !$quickmod ? $this->request->variable('topic_id_list', [0]) : [$this->request->variable('t', 0)];

				if (empty($topic_ids))
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				$this->mcp_move_topic($topic_ids);
			break;

			case 'fork':
				$this->lang->add_lang('viewtopic');

				$topic_ids = !$quickmod ? $this->request->variable('topic_id_list', [0]) : [$this->request->variable('t', 0)];

				if (empty($topic_ids))
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				$this->mcp_fork_topic($topic_ids);
			break;

			case 'delete_topic':
				$this->lang->add_lang('viewtopic');

				// f parameter is not reliable for permission usage, however we just use it to decide
				// which permission we will check later on. So if it is manipulated, we will still catch it later on.
				$forum_id = $this->request->variable('f', 0);
				$topic_ids = !$quickmod ? $this->request->variable('topic_id_list', [0]) : [$this->request->variable('t', 0)];
				$soft_delete = (($this->request->is_set_post('confirm') && !$this->request->is_set_post('delete_permanent')) || !$this->auth->acl_get('m_delete', $forum_id)) ? true : false;

				if (empty($topic_ids))
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				$this->mcp_delete->delete_topics($topic_ids, $soft_delete, $this->request->variable('delete_reason', '', true));
			break;

			case 'delete_post':
				$this->lang->add_lang('posting');

				// f parameter is not reliable for permission usage, however we just use it to decide
				// which permission we will check later on. So if it is manipulated, we will still catch it later on.
				$forum_id = $this->request->variable('f', 0);
				$post_ids = !$quickmod ? $this->request->variable('post_id_list', [0]) : [$this->request->variable('p', 0)];
				$soft_delete = (($this->request->is_set_post('confirm') && !$this->request->is_set_post('delete_permanent')) || !$this->auth->acl_get('m_delete', $forum_id)) ? true : false;

				if (empty($post_ids))
				{
					trigger_error('NO_POST_SELECTED');
				}

				$this->mcp_delete->delete_posts($post_ids, $soft_delete, $this->request->variable('delete_reason', '', true));
			break;

			case 'restore_topic':
				$this->lang->add_lang('posting');

				$topic_ids = !$quickmod ? $this->request->variable('topic_id_list', [0]) : [$this->request->variable('t', 0)];

				if (empty($topic_ids))
				{
					trigger_error('NO_TOPIC_SELECTED');
				}

				$this->mcp_restore_topic($topic_ids);
			break;

			default:
				/**
				* This event allows you to handle custom quickmod options
				*
				* @event core.modify_quickmod_actions
				* @var string	action		Topic quick moderation action name
				* @var bool		quickmod	Flag indicating whether MCP is in quick moderation mode
				* @since 3.1.0-a4
				* @changed 3.1.0-RC4 Added variables: action, quickmod
				*/
				$vars = ['action', 'quickmod'];
				extract($this->dispatcher->trigger_event('core.modify_quickmod_actions', compact($vars)));
			break;
		}

		switch ($mode)
		{
			case 'front':
				$this->lang->add_lang('acp/common');

				$this->mcp_front->view($id);

				$this->tpl_name = 'mcp_front';
				$this->page_title = 'MCP_MAIN';
			break;

			case 'forum_view':
				$this->lang->add_lang('viewforum');

				$forum_id = $this->request->variable('f', 0);

				$forum_info = phpbb_get_forum_data($forum_id, 'm_', true);

				if (empty($forum_info))
				{
					$this->main('main', 'front');
					return;
				}

				$forum_info = $forum_info[$forum_id];

				$this->mcp_forum->view($id, $mode, $action, $forum_info);

				$this->tpl_name = 'mcp_forum';
				$this->page_title = 'MCP_MAIN_FORUM_VIEW';
			break;

			case 'topic_view':
				$this->mcp_topic->view($id, $mode, $action);

				$this->tpl_name = 'mcp_topic';
				$this->page_title = 'MCP_MAIN_TOPIC_VIEW';
			break;

			case 'post_details':
				$this->mcp_post->post_details($id, $mode, $action);

				$this->tpl_name = $action === 'whois' ? 'mcp_whois' : 'mcp_post';
				$this->page_title = 'MCP_MAIN_POST_DETAILS';
			break;

			default:
				if ($quickmod)
				{
					switch ($action)
					{
						case 'lock':
						case 'unlock':
						case 'make_announce':
						case 'make_sticky':
						case 'make_global':
						case 'make_normal':
						case 'make_onindex':
						case 'move':
						case 'fork':
						case 'delete_topic':
							trigger_error('TOPIC_NOT_EXIST');
						break;

						case 'lock_post':
						case 'unlock_post':
						case 'delete_post':
							trigger_error('POST_NOT_EXIST');
						break;
					}
				}

				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
	}

	/**
	 * (Un)lock posts or topics.
	 *
	 * @param string	$action		The action (lock|unlock|post_lock|post_unlock)
	 * @param array		$ids		The post|topic identifiers
	 * @return void
	 */
	function lock_unlock($action, array $ids)
	{
		if ($action === 'lock' || $action === 'unlock')
		{
			$table		= $this->tables['topics'];
			$sql_id		= 'topic_id';
			$set_id		= 'topic_status';
			$l_prefix	= 'TOPIC';
		}
		else
		{
			$table		= $this->tables['posts'];
			$sql_id		= 'post_id';
			$set_id		= 'post_edit_locked';
			$l_prefix	= 'POST';
		}

		$orig_ids = $ids;

		if (!phpbb_check_ids($ids, $table, $sql_id, ['m_lock']))
		{
			// Make sure that for f_user_lock only the lock action is triggered.
			if ($action !== 'lock')
			{
				return;
			}

			$ids = $orig_ids;

			if (!phpbb_check_ids($ids, $table, $sql_id, ['f_user_lock']))
			{
				return;
			}
		}
		unset($orig_ids);

		$redirect = $this->request->variable('redirect', build_url(['action', 'quickmod']));
		$redirect = reapply_sid($redirect);

		$s_hidden_fields = build_hidden_fields([
			$sql_id . '_list'	=> $ids,
			'action'			=> $action,
			'redirect'			=> $redirect,
		]);

		if (confirm_box(true))
		{
			$sql = "UPDATE $table
			SET $set_id = " . (($action === 'lock' || $action === 'lock_post') ? ITEM_LOCKED : ITEM_UNLOCKED) . '
			WHERE ' . $this->db->sql_in_set($sql_id, $ids);
			$this->db->sql_query($sql);

			$data = ($action === 'lock' || $action === 'unlock') ? phpbb_get_topic_data($ids) : phpbb_get_post_data($ids);

			foreach ($data as $id => $row)
			{
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_' . strtoupper($action), false, [
					'forum_id'	=> (int) $row['forum_id'],
					'topic_id'	=> (int) $row['topic_id'],
					'post_id'	=> isset($row['post_id']) ? $row['post_id'] : 0,
					$row['topic_title'],
				]);
			}

			/**
			 * Perform additional actions after locking/unlocking posts/topics
			 *
			 * @event core.mcp_lock_unlock_after
			 * @var string	action				Variable containing the action we perform on the posts/topics ('lock', 'unlock', 'lock_post' or 'unlock_post')
			 * @var array	ids					Array containing the post/topic IDs that have been locked/unlocked
			 * @var array	data				Array containing posts/topics data
			 * @since 3.1.7-RC1
			 */
			$vars = [
				'action',
				'ids',
				'data',
			];
			extract($this->dispatcher->trigger_event('core.mcp_lock_unlock_after', compact($vars)));

			$success_msg = $l_prefix . (count($ids) === 1 ? '' : 'S') . '_' . (($action === 'lock' || $action === 'lock_post') ? 'LOCKED' : 'UNLOCKED') . '_SUCCESS';

			meta_refresh(2, $redirect);
			$message = $this->lang->lang($success_msg);

			if (!$this->request->is_ajax())
			{
				$message .= '<br /><br />' . $this->lang->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>');
			}
			trigger_error($message);
		}
		else
		{
			confirm_box(false, strtoupper($action) . '_' . $l_prefix . (count($ids) === 1 ? '' : 'S'), $s_hidden_fields);
		}

		redirect($redirect);
	}

	/**
	 * Fork topic.
	 *
	 * @param array		$topic_ids		The topic identifiers
	 * @return void
	 */
	function mcp_fork_topic(array $topic_ids)
	{
		if (!phpbb_check_ids($topic_ids, $this->tables['topics'], 'topic_id', ['m_']))
		{
			return;
		}

		$to_forum_id = $this->request->variable('to_forum_id', 0);
		$forum_id = $this->request->variable('f', 0);
		$redirect = $this->request->variable('redirect', build_url(['action', 'quickmod']));
		$additional_msg = $success_msg = '';
		$counter = $topic_row = [];
		$search = null;
		$search_mode = '';

		$s_hidden_fields = build_hidden_fields([
			'topic_id_list'	=> $topic_ids,
			'f'				=> $forum_id,
			'action'		=> 'fork',
			'redirect'		=> $redirect,
		]);

		if ($to_forum_id)
		{
			$forum_data = phpbb_get_forum_data($to_forum_id, 'f_post');

			if (empty($topic_ids))
			{
				$additional_msg = $this->lang->lang('NO_TOPIC_SELECTED');
			}
			else if (empty($forum_data))
			{
				$additional_msg = $this->lang->lang('FORUM_NOT_EXIST');
			}
			else
			{
				$forum_data = $forum_data[$to_forum_id];

				if ($forum_data['forum_type'] != FORUM_POST)
				{
					$additional_msg = $this->lang->lang('FORUM_NOT_POSTABLE');
				}
				else if (!$this->auth->acl_get('f_post', $to_forum_id))
				{
					$additional_msg = $this->lang->lang('USER_CANNOT_POST');
				}
			}
		}
		else if ($this->request->is_set_post('confirm'))
		{
			$additional_msg = $this->lang->lang('FORUM_NOT_EXIST');
		}

		if ($additional_msg)
		{
			$this->request->overwrite('confirm', null, \phpbb\request\request_interface::POST);
			$this->request->overwrite('confirm_key', null);
		}

		if (confirm_box(true))
		{
			$topic_data = phpbb_get_topic_data($topic_ids, 'f_post');

			$total_topics = $total_topics_unapproved = $total_topics_softdeleted = 0;
			$total_posts = $total_posts_unapproved = $total_posts_softdeleted = 0;
			$new_topic_id_list = [];

			foreach ($topic_data as $topic_id => $topic_row)
			{
				if (!isset($search_type) && $topic_row['enable_indexing'])
				{
					// Select the search method and do some additional checks to ensure it can actually be utilised
					$search_type = $this->config['search_type'];

					if (!class_exists($search_type))
					{
						trigger_error('NO_SUCH_SEARCH_MODULE');
					}

					$error = false;

					/** @var \phpbb\search\fulltext_mysql $search		@todo Search interface?? */
					$search = new $search_type($error, $this->root_path, $this->php_ext, $this->auth, $this->config, $this->db, $this->user, $this->dispatcher);
					$search_mode = 'post';

					if ($error)
					{
						trigger_error($error);
					}
				}
				else if (!isset($search_type) && !$topic_row['enable_indexing'])
				{
					$search_type = false;
				}

				$sql_ary = [
					'forum_id'					=> (int) $to_forum_id,
					'icon_id'					=> (int) $topic_row['icon_id'],
					'topic_attachment'			=> (int) $topic_row['topic_attachment'],
					'topic_visibility'			=> (int) $topic_row['topic_visibility'],
					'topic_reported'			=> 0,
					'topic_title'				=> (string) $topic_row['topic_title'],
					'topic_poster'				=> (int) $topic_row['topic_poster'],
					'topic_time'				=> (int) $topic_row['topic_time'],
					'topic_posts_approved'		=> (int) $topic_row['topic_posts_approved'],
					'topic_posts_unapproved'	=> (int) $topic_row['topic_posts_unapproved'],
					'topic_posts_softdeleted'	=> (int) $topic_row['topic_posts_softdeleted'],
					'topic_status'				=> (int) $topic_row['topic_status'],
					'topic_type'				=> (int) $topic_row['topic_type'],
					'topic_first_poster_name'	=> (string) $topic_row['topic_first_poster_name'],
					'topic_last_poster_id'		=> (int) $topic_row['topic_last_poster_id'],
					'topic_last_poster_name'	=> (string) $topic_row['topic_last_poster_name'],
					'topic_last_post_time'		=> (int) $topic_row['topic_last_post_time'],
					'topic_last_view_time'		=> (int) $topic_row['topic_last_view_time'],
					'topic_bumped'				=> (int) $topic_row['topic_bumped'],
					'topic_bumper'				=> (int) $topic_row['topic_bumper'],
					'poll_title'				=> (string) $topic_row['poll_title'],
					'poll_start'				=> (int) $topic_row['poll_start'],
					'poll_length'				=> (int) $topic_row['poll_length'],
					'poll_max_options'			=> (int) $topic_row['poll_max_options'],
					'poll_vote_change'			=> (int) $topic_row['poll_vote_change'],
				];

				/**
				 * Perform actions before forked topic is created.
				 *
				 * @event core.mcp_main_modify_fork_sql
				 * @var array	sql_ary		SQL array to be used by $this->db->sql_build_array
				 * @var array	topic_row	Topic data
				 * @since 3.1.11-RC1
				 * @changed 3.1.11-RC1 Added variable: topic_row
				 */
				$vars = [
					'sql_ary',
					'topic_row',
				];
				extract($this->dispatcher->trigger_event('core.mcp_main_modify_fork_sql', compact($vars)));

				$sql = 'INSERT INTO ' . $this->tables['topics'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
				$this->db->sql_query($sql);
				$new_topic_id = $this->db->sql_nextid();
				$new_topic_id_list[$topic_id] = $new_topic_id;

				switch ($topic_row['topic_visibility'])
				{
					case ITEM_APPROVED:
						$total_topics++;
					break;
					case ITEM_UNAPPROVED:
					case ITEM_REAPPROVE:
						$total_topics_unapproved++;
					break;
					case ITEM_DELETED:
						$total_topics_softdeleted++;
					break;
				}

				if ($topic_row['poll_start'])
				{
					$sql = 'SELECT *
					FROM ' . $this->tables['poll_options'] . '
					WHERE topic_id = ' . (int) $topic_id;
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$sql_ary = [
							'poll_option_id'	=> (int) $row['poll_option_id'],
							'topic_id'			=> (int) $new_topic_id,
							'poll_option_text'	=> (string) $row['poll_option_text'],
							'poll_option_total'	=> 0,
						];

						$sql = 'INSERT INTO ' . $this->tables['poll_options'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
						$this->db->sql_query($sql);
					}
					$this->db->sql_freeresult($result);
				}

				$post_rows = [];

				$sql = 'SELECT *
				FROM ' . $this->tables['posts'] . '
				WHERE topic_id = ' . (int) $topic_id . '
				ORDER BY post_time ASC, post_id ASC';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$post_rows[] = $row;
				}
				$this->db->sql_freeresult($result);

				if (empty($post_rows))
				{
					continue;
				}

				foreach ($post_rows as $row)
				{
					$sql_ary = [
						'topic_id'			=> (int) $new_topic_id,
						'forum_id'			=> (int) $to_forum_id,
						'poster_id'			=> (int) $row['poster_id'],
						'icon_id'			=> (int) $row['icon_id'],
						'poster_ip'			=> (string) $row['poster_ip'],
						'post_time'			=> (int) $row['post_time'],
						'post_visibility'	=> (int) $row['post_visibility'],
						'post_reported'		=> 0,
						'enable_bbcode'		=> (int) $row['enable_bbcode'],
						'enable_smilies'	=> (int) $row['enable_smilies'],
						'enable_magic_url'	=> (int) $row['enable_magic_url'],
						'enable_sig'		=> (int) $row['enable_sig'],
						'post_username'		=> (string) $row['post_username'],
						'post_subject'		=> (string) $row['post_subject'],
						'post_text'			=> (string) $row['post_text'],
						'post_edit_reason'	=> (string) $row['post_edit_reason'],
						'post_edit_user'	=> (int) $row['post_edit_user'],
						'post_checksum'		=> (string) $row['post_checksum'],
						'post_attachment'	=> (int) $row['post_attachment'],
						'bbcode_bitfield'	=> $row['bbcode_bitfield'],
						'bbcode_uid'		=> (string) $row['bbcode_uid'],
						'post_edit_time'	=> (int) $row['post_edit_time'],
						'post_edit_count'	=> (int) $row['post_edit_count'],
						'post_edit_locked'	=> (int) $row['post_edit_locked'],
						'post_postcount'	=> $row['post_postcount'],
					];

					// Adjust post count only if the post can be incremented to the user counter
					if ($row['post_postcount'])
					{
						if (isset($counter[$row['poster_id']]))
						{
							++$counter[$row['poster_id']];
						}
						else
						{
							$counter[$row['poster_id']] = 1;
						}
					}

					$sql = 'INSERT INTO ' . $this->tables['posts'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
					$this->db->sql_query($sql);
					$new_post_id = $this->db->sql_nextid();

					/**
					 * Perform actions after forked topic is created.
					 *
					 * @event core.mcp_main_fork_sql_after
					 * @var int		new_topic_id	The newly created topic ID
					 * @var int		to_forum_id		The forum ID where the forked topic has been moved to
					 * @var int		new_post_id		The newly created post ID
					 * @var array	row				Post data
					 * @since 3.2.4-RC1
					 */
					$vars = [
						'new_topic_id',
						'to_forum_id',
						'new_post_id',
						'row',
					];
					extract($this->dispatcher->trigger_event('core.mcp_main_fork_sql_after', compact($vars)));

					switch ($row['post_visibility'])
					{
						case ITEM_APPROVED:
							$total_posts++;
						break;
						case ITEM_UNAPPROVED:
						case ITEM_REAPPROVE:
							$total_posts_unapproved++;
						break;
						case ITEM_DELETED:
							$total_posts_softdeleted++;
						break;
					}

					// Copy whether the topic is dotted
					markread('post', $to_forum_id, $new_topic_id, 0, $row['poster_id']);

					if (!empty($search_type))
					{
						$search->index($search_mode, $new_post_id, $sql_ary['post_text'], $sql_ary['post_subject'], $sql_ary['poster_id'], ($topic_row['topic_type'] == POST_GLOBAL) ? 0 : $to_forum_id);
						$search_mode = 'reply'; // After one we index replies
					}

					// Copy Attachments
					if ($row['post_attachment'])
					{
						$sql_ary = [];

						$sql = 'SELECT * FROM ' . $this->tables['attachments'] . '
						WHERE in_message = 0
							AND post_msg_id = ' . (int) $row['post_id'] . '
							AND topic_id = ' . (int) $topic_id;
						$result = $this->db->sql_query($sql);
						while ($attach_row = $this->db->sql_fetchrow($result))
						{
							$sql_ary[] = [
								'post_msg_id'		=> (int) $new_post_id,
								'topic_id'			=> (int) $new_topic_id,
								'in_message'		=> 0,
								'is_orphan'			=> (int) $attach_row['is_orphan'],
								'poster_id'			=> (int) $attach_row['poster_id'],
								'physical_filename'	=> (string) utf8_basename($attach_row['physical_filename']),
								'real_filename'		=> (string) utf8_basename($attach_row['real_filename']),
								'download_count'	=> (int) $attach_row['download_count'],
								'attach_comment'	=> (string) $attach_row['attach_comment'],
								'extension'			=> (string) $attach_row['extension'],
								'mimetype'			=> (string) $attach_row['mimetype'],
								'filesize'			=> (int) $attach_row['filesize'],
								'filetime'			=> (int) $attach_row['filetime'],
								'thumbnail'			=> (int) $attach_row['thumbnail'],
							];
						}
						$this->db->sql_freeresult($result);

						if (!empty($sql_ary))
						{
							$this->db->sql_multi_insert($this->tables['attachments'], $sql_ary);
						}
					}
				}

				// Copy topic subscriptions to new topic
				$sql_ary = [];

				$sql = 'SELECT user_id, notify_status
				FROM ' . $this->tables['topics_watch'] . '
				WHERE topic_id = ' . (int) $topic_id;
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$sql_ary[] = [
						'topic_id'		=> (int) $new_topic_id,
						'user_id'		=> (int) $row['user_id'],
						'notify_status'	=> (int) $row['notify_status'],
					];
				}
				$this->db->sql_freeresult($result);

				if (!empty($sql_ary))
				{
					$this->db->sql_multi_insert($this->tables['topics_watch'], $sql_ary);
				}

				// Copy bookmarks to new topic
				$sql_ary = [];

				$sql = 'SELECT user_id
				FROM ' . $this->tables['bookmarks'] . '
				WHERE topic_id = ' . (int) $topic_id;
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$sql_ary[] = [
						'topic_id'		=> (int) $new_topic_id,
						'user_id'		=> (int) $row['user_id'],
					];
				}
				$this->db->sql_freeresult($result);

				if (!empty($sql_ary))
				{
					$this->db->sql_multi_insert($this->tables['bookmarks'], $sql_ary);
				}
			}

			// Sync new topics, parent forums and board stats
			$sql = 'UPDATE ' . $this->tables['forums'] . '
			SET forum_posts_approved = forum_posts_approved + ' . $total_posts . ',
				forum_posts_unapproved = forum_posts_unapproved + ' . $total_posts_unapproved . ',
				forum_posts_softdeleted = forum_posts_softdeleted + ' . $total_posts_softdeleted . ',
				forum_topics_approved = forum_topics_approved + ' . $total_topics . ',
				forum_topics_unapproved = forum_topics_unapproved + ' . $total_topics_unapproved . ',
				forum_topics_softdeleted = forum_topics_softdeleted + ' . $total_topics_softdeleted . '
			WHERE forum_id = ' . (int) $to_forum_id;
			$this->db->sql_query($sql);

			if (!empty($counter))
			{
				// Do only one query per user and not a query per post.
				foreach ($counter as $user_id => $count)
				{
					$sql = 'UPDATE ' . $this->tables['users'] . '
					SET user_posts = user_posts + ' . (int) $count . '
					WHERE user_id = ' . (int) $user_id;
					$this->db->sql_query($sql);
				}
			}

			sync('topic', 'topic_id', $new_topic_id_list);
			sync('forum', 'forum_id', $to_forum_id);

			$this->config->increment('num_topics', count($new_topic_id_list), false);
			$this->config->increment('num_posts', $total_posts, false);

			foreach ($new_topic_id_list as $topic_id => $new_topic_id)
			{
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_FORK', false, [
					'forum_id' => (int) $to_forum_id,
					'topic_id' => (int) $new_topic_id,
					$topic_row['forum_name'],
				]);
			}

			$success_msg = count($topic_ids) === 1 ? 'TOPIC_FORKED_SUCCESS' : 'TOPICS_FORKED_SUCCESS';
		}
		else
		{
			$this->template->assign_vars([
				'ADDITIONAL_MSG'		=> $additional_msg,
				'S_CAN_LEAVE_SHADOW'	=> false,
				'S_FORUM_SELECT'		=> make_forum_select($to_forum_id, false, false, true, true, true),
			]);

			confirm_box(false, 'FORK_TOPIC' . (count($topic_ids) === 1 ? '' : 'S'), $s_hidden_fields, 'mcp_move.html');
		}

		$redirect = $this->request->variable('redirect', "index.$this->php_ext");
		$redirect = reapply_sid($redirect);

		if (!$success_msg)
		{
			redirect($redirect);
		}
		else
		{
			$redirect_url = append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $forum_id);
			meta_refresh(3, $redirect_url);
			$return_link = $this->lang->lang('RETURN_FORUM', '<a href="' . $redirect_url . '">', '</a>');

			if ($forum_id != $to_forum_id)
			{
				$return_link .= '<br /><br />' . $this->lang->lang('RETURN_NEW_FORUM', '<a href="' . append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $to_forum_id) . '">', '</a>');
			}

			trigger_error($this->lang->lang($success_msg) . '<br /><br />' . $return_link);
		}
	}

	/**
	 * Move topic
	 *
	 * @param array		$topic_ids		The topic identifiers
	 * @return void
	 */
	function mcp_move_topic(array $topic_ids)
	{
		// Here we limit the operation to one forum only
		$forum_id = phpbb_check_ids($topic_ids, $this->tables['topics'], 'topic_id', ['m_move'], true);
		$forum_data = [];

		if ($forum_id === false)
		{
			return;
		}

		$to_forum_id = $this->request->variable('to_forum_id', 0);
		$redirect = $this->request->variable('redirect', build_url(['action', 'quickmod']));
		$additional_msg = $success_msg = '';

		$s_hidden_fields = build_hidden_fields([
			'topic_id_list'	=> $topic_ids,
			'f'				=> $forum_id,
			'action'		=> 'move',
			'redirect'		=> $redirect,
		]);

		if ($to_forum_id)
		{
			$forum_data = phpbb_get_forum_data($to_forum_id, 'f_post');

			if (empty($forum_data))
			{
				$additional_msg = $this->lang->lang('FORUM_NOT_EXIST');
			}
			else
			{
				$forum_data = $forum_data[$to_forum_id];

				if ($forum_data['forum_type'] != FORUM_POST)
				{
					$additional_msg = $this->lang->lang('FORUM_NOT_POSTABLE');
				}
				else if (!$this->auth->acl_get('f_post', $to_forum_id) || (!$this->auth->acl_get('m_approve', $to_forum_id) && !$this->auth->acl_get('f_noapprove', $to_forum_id)))
				{
					$additional_msg = $this->lang->lang('USER_CANNOT_POST');
				}
				else if ($forum_id == $to_forum_id)
				{
					$additional_msg = $this->lang->lang('CANNOT_MOVE_SAME_FORUM');
				}
			}
		}
		else if ($this->request->is_set_post('confirm'))
		{
			$additional_msg = $this->lang->lang('FORUM_NOT_EXIST');
		}

		if (!$to_forum_id || $additional_msg)
		{
			$this->request->overwrite('confirm', null, \phpbb\request\request_interface::POST);
			$this->request->overwrite('confirm_key', null);
		}

		if (confirm_box(true))
		{
			$topic_data = phpbb_get_topic_data($topic_ids);
			$leave_shadow = $this->request->is_set_post('move_leave_shadow');

			$forum_sync_data = [];

			$forum_sync_data[$forum_id] = current($topic_data);
			$forum_sync_data[$to_forum_id] = $forum_data;

			$topics_moved = $topics_moved_unapproved = $topics_moved_softdeleted = 0;
			$posts_moved = $posts_moved_unapproved = $posts_moved_softdeleted = 0;

			foreach ($topic_data as $topic_id => $topic_info)
			{
				if ($topic_info['topic_visibility'] == ITEM_APPROVED)
				{
					$topics_moved++;
				}
				else if ($topic_info['topic_visibility'] == ITEM_UNAPPROVED || $topic_info['topic_visibility'] == ITEM_REAPPROVE)
				{
					$topics_moved_unapproved++;
				}
				else if ($topic_info['topic_visibility'] == ITEM_DELETED)
				{
					$topics_moved_softdeleted++;
				}

				$posts_moved += $topic_info['topic_posts_approved'];
				$posts_moved_unapproved += $topic_info['topic_posts_unapproved'];
				$posts_moved_softdeleted += $topic_info['topic_posts_softdeleted'];
			}

			$this->db->sql_transaction('begin');

			// Move topics, but do not resync yet
			move_topics($topic_ids, $to_forum_id, false);

			if ($this->request->is_set_post('move_lock_topics') && $this->auth->acl_get('m_lock', $to_forum_id))
			{
				$sql = 'UPDATE ' . $this->tables['topics'] . '
				SET topic_status = ' . ITEM_LOCKED . '
				WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids);
				$this->db->sql_query($sql);
			}

			$shadow_topics = 0;
			$forum_ids = [$to_forum_id];
			foreach ($topic_data as $topic_id => $row)
			{
				// Get the list of forums to resync
				$forum_ids[] = (int) $row['forum_id'];

				// We add the $to_forum_id twice, because 'forum_id' is updated
				// when the topic is moved again later.
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_MOVE', false, [
					'forum_id'		=> (int) $to_forum_id,
					'topic_id'		=> (int) $topic_id,
					$row['forum_name'],
					$forum_data['forum_name'],
					(int) $row['forum_id'],
					(int) $forum_data['forum_id'],
				]);

				// Leave a redirection if required and only if the topic is visible to users
				if ($leave_shadow && $row['topic_visibility'] == ITEM_APPROVED && $row['topic_type'] != POST_GLOBAL)
				{
					$shadow = [
						'forum_id'				=>	(int) $row['forum_id'],
						'icon_id'				=>	(int) $row['icon_id'],
						'topic_attachment'		=>	(int) $row['topic_attachment'],
						'topic_visibility'		=>	ITEM_APPROVED, // a shadow topic is always approved
						'topic_reported'		=>	0, // a shadow topic is never reported
						'topic_title'			=>	(string) $row['topic_title'],
						'topic_poster'			=>	(int) $row['topic_poster'],
						'topic_time'			=>	(int) $row['topic_time'],
						'topic_time_limit'		=>	(int) $row['topic_time_limit'],
						'topic_views'			=>	(int) $row['topic_views'],
						'topic_posts_approved'	=>	(int) $row['topic_posts_approved'],
						'topic_posts_unapproved'=>	(int) $row['topic_posts_unapproved'],
						'topic_posts_softdeleted'=>	(int) $row['topic_posts_softdeleted'],
						'topic_status'			=>	ITEM_MOVED,
						'topic_type'			=>	POST_NORMAL,
						'topic_first_post_id'	=>	(int) $row['topic_first_post_id'],
						'topic_first_poster_colour'=>(string) $row['topic_first_poster_colour'],
						'topic_first_poster_name'=>	(string) $row['topic_first_poster_name'],
						'topic_last_post_id'	=>	(int) $row['topic_last_post_id'],
						'topic_last_poster_id'	=>	(int) $row['topic_last_poster_id'],
						'topic_last_poster_colour'=>(string) $row['topic_last_poster_colour'],
						'topic_last_poster_name'=>	(string) $row['topic_last_poster_name'],
						'topic_last_post_subject'=>	(string) $row['topic_last_post_subject'],
						'topic_last_post_time'	=>	(int) $row['topic_last_post_time'],
						'topic_last_view_time'	=>	(int) $row['topic_last_view_time'],
						'topic_moved_id'		=>	(int) $row['topic_id'],
						'topic_bumped'			=>	(int) $row['topic_bumped'],
						'topic_bumper'			=>	(int) $row['topic_bumper'],
						'poll_title'			=>	(string) $row['poll_title'],
						'poll_start'			=>	(int) $row['poll_start'],
						'poll_length'			=>	(int) $row['poll_length'],
						'poll_max_options'		=>	(int) $row['poll_max_options'],
						'poll_last_vote'		=>	(int) $row['poll_last_vote'],
					];

					/**
					 * Perform actions before shadow topic is created.
					 *
					 * @event core.mcp_main_modify_shadow_sql
					 * @var array	shadow	SQL array to be used by $this->db->sql_build_array
					 * @var array	row		Topic data
					 * @since 3.1.11-RC1
					 * @changed 3.1.11-RC1 Added variable: row
					 */
					$vars = [
						'shadow',
						'row',
					];
					extract($this->dispatcher->trigger_event('core.mcp_main_modify_shadow_sql', compact($vars)));

					$sql = 'INSERT INTO ' . $this->tables['topics'] . $this->db->sql_build_array('INSERT', $shadow);
					$this->db->sql_query($sql);

					// Shadow topics only count on new "topics" and not posts... a shadow topic alone has 0 posts
					$shadow_topics++;
				}
			}
			unset($topic_data);

			$sync_sql = [];
			if ($posts_moved)
			{
				$sync_sql[$to_forum_id][]	= 'forum_posts_approved = forum_posts_approved + ' . (int) $posts_moved;
				$sync_sql[$forum_id][]		= 'forum_posts_approved = forum_posts_approved - ' . (int) $posts_moved;
			}
			if ($posts_moved_unapproved)
			{
				$sync_sql[$to_forum_id][]	= 'forum_posts_unapproved = forum_posts_unapproved + ' . (int) $posts_moved_unapproved;
				$sync_sql[$forum_id][]		= 'forum_posts_unapproved = forum_posts_unapproved - ' . (int) $posts_moved_unapproved;
			}
			if ($posts_moved_softdeleted)
			{
				$sync_sql[$to_forum_id][]	= 'forum_posts_softdeleted = forum_posts_softdeleted + ' . (int) $posts_moved_softdeleted;
				$sync_sql[$forum_id][]		= 'forum_posts_softdeleted = forum_posts_softdeleted - ' . (int) $posts_moved_softdeleted;
			}

			if ($topics_moved)
			{
				$sync_sql[$to_forum_id][]	= 'forum_topics_approved = forum_topics_approved + ' . (int) $topics_moved;

				if ($topics_moved - $shadow_topics > 0)
				{
					$sync_sql[$forum_id][]	= 'forum_topics_approved = forum_topics_approved - ' . (int) ($topics_moved - $shadow_topics);
				}
			}
			if ($topics_moved_unapproved)
			{
				$sync_sql[$to_forum_id][]	= 'forum_topics_unapproved = forum_topics_unapproved + ' . (int) $topics_moved_unapproved;
				$sync_sql[$forum_id][]		= 'forum_topics_unapproved = forum_topics_unapproved - ' . (int) $topics_moved_unapproved;
			}
			if ($topics_moved_softdeleted)
			{
				$sync_sql[$to_forum_id][]	= 'forum_topics_softdeleted = forum_topics_softdeleted + ' . (int) $topics_moved_softdeleted;
				$sync_sql[$forum_id][]		= 'forum_topics_softdeleted = forum_topics_softdeleted - ' . (int) $topics_moved_softdeleted;
			}

			$success_msg = count($topic_ids) === 1 ? 'TOPIC_MOVED_SUCCESS' : 'TOPICS_MOVED_SUCCESS';

			foreach ($sync_sql as $forum_id_key => $array)
			{
				$sql = 'UPDATE ' . $this->tables['forums'] . '
				SET ' . implode(', ', $array) . '
				WHERE forum_id = ' . (int) $forum_id_key;
				$this->db->sql_query($sql);
			}

			$this->db->sql_transaction('commit');

			sync('forum', 'forum_id', [$forum_id, $to_forum_id]);
		}
		else
		{
			$this->template->assign_vars([
				'ADDITIONAL_MSG'		=> $additional_msg,
				'S_CAN_LEAVE_SHADOW'	=> true,
				'S_CAN_LOCK_TOPIC'		=> (bool) $this->auth->acl_get('m_lock', $to_forum_id),
				'S_FORUM_SELECT'		=> make_forum_select($to_forum_id, $forum_id, false, true, true, true),
			]);

			confirm_box(false, 'MOVE_TOPIC' . (count($topic_ids) === 1 ? '' : 'S'), $s_hidden_fields, 'mcp_move.html');
		}

		$redirect = $this->request->variable('redirect', "index.$this->php_ext");
		$redirect = reapply_sid($redirect);

		if (!$success_msg)
		{
			redirect($redirect);
		}
		else
		{
			meta_refresh(3, $redirect);

			$message = $this->lang->lang($success_msg);
			$message .= '<br /><br />' . $this->lang->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>');
			$message .= '<br /><br />' . $this->lang->lang('RETURN_FORUM', '<a href="' . append_sid("{$this->root_path}viewforum.$this->php_ext", "f=$forum_id") . '">', '</a>');
			$message .= '<br /><br />' . $this->lang->lang('RETURN_NEW_FORUM', '<a href="' . append_sid("{$this->root_path}viewforum.$this->php_ext", "f=$to_forum_id") . '">', '</a>');

			trigger_error($message);
		}
	}

	/**
	 * Change topic type.
	 *
	 * @param string	$action			The action (make_announce|make_global|make_sticky|make_normal)
	 * @param array		$topic_ids		The topic identifiers
	 * @return void
	 */
	function change_topic_type($action, array $topic_ids)
	{
		switch ($action)
		{
			case 'make_announce':
				$new_topic_type = POST_ANNOUNCE;
				$check_acl = 'f_announce';
				$l_new_type = count($topic_ids) === 1 ? 'MCP_MAKE_ANNOUNCEMENT' : 'MCP_MAKE_ANNOUNCEMENTS';
			break;

			case 'make_global':
				$new_topic_type = POST_GLOBAL;
				$check_acl = 'f_announce_global';
				$l_new_type = count($topic_ids) === 1 ? 'MCP_MAKE_GLOBAL' : 'MCP_MAKE_GLOBALS';
			break;

			case 'make_sticky':
				$new_topic_type = POST_STICKY;
				$check_acl = 'f_sticky';
				$l_new_type = count($topic_ids) === 1 ? 'MCP_MAKE_STICKY' : 'MCP_MAKE_STICKIES';
			break;

			default:
				$new_topic_type = POST_NORMAL;
				$check_acl = false;
				$l_new_type = count($topic_ids) === 1 ? 'MCP_MAKE_NORMAL' : 'MCP_MAKE_NORMALS';
			break;
		}

		$forum_id = phpbb_check_ids($topic_ids, $this->tables['topics'], 'topic_id', $check_acl, true);

		if ($forum_id === false)
		{
			return;
		}

		$redirect = $this->request->variable('redirect', build_url(['action', 'quickmod']));
		$redirect = reapply_sid($redirect);

		$s_hidden_fields = [
			'topic_id_list'	=> $topic_ids,
			'f'				=> $forum_id,
			'action'		=> $action,
			'redirect'		=> $redirect,
		];

		if (confirm_box(true))
		{
			$sql = 'UPDATE ' . $this->tables['topics'] . "
			SET topic_type = $new_topic_type
			WHERE " . $this->db->sql_in_set('topic_id', $topic_ids);
			$this->db->sql_query($sql);

			if (($new_topic_type == POST_GLOBAL) && !empty($topic_ids))
			{
				// Delete topic shadows for global announcements
				$sql = 'DELETE FROM ' . $this->tables['topics'] . '
				WHERE ' . $this->db->sql_in_set('topic_moved_id', $topic_ids);
				$this->db->sql_query($sql);

				$sql = 'UPDATE ' . $this->tables['topics'] . "
				SET topic_type = $new_topic_type
					WHERE " . $this->db->sql_in_set('topic_id', $topic_ids);
				$this->db->sql_query($sql);
			}

			$success_msg = count($topic_ids) === 1 ? 'TOPIC_TYPE_CHANGED' : 'TOPICS_TYPE_CHANGED';

			if (!empty($topic_ids))
			{
				$data = phpbb_get_topic_data($topic_ids);

				foreach ($data as $topic_id => $row)
				{
					$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_TOPIC_TYPE_CHANGED', false, [
						'forum_id' => (int) $forum_id,
						'topic_id' => (int) $topic_id,
						$row['topic_title'],
					]);
				}
			}

			meta_refresh(2, $redirect);
			$message = $this->lang->lang($success_msg);

			if (!$this->request->is_ajax())
			{
				$message .= '<br /><br />' . $this->lang->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>');
			}
			trigger_error($message);
		}
		else
		{
			confirm_box(false, $l_new_type, build_hidden_fields($s_hidden_fields));
		}

		redirect($redirect);
	}

	/**
	 * Restore topics.
	 *
	 * @param array		$topic_ids		The topic identifiers
	 * @return void
	 */
	function mcp_restore_topic(array $topic_ids)
	{
		if (!phpbb_check_ids($topic_ids, $this->tables['topics'], 'topic_id', ['m_approve']))
		{
			return;
		}

		$redirect = $this->request->variable('redirect', build_url(['action', 'quickmod']));
		$forum_id = $this->request->variable('f', 0);

		$s_hidden_fields = build_hidden_fields([
			'topic_id_list'	=> $topic_ids,
			'f'				=> $forum_id,
			'action'		=> 'restore_topic',
			'redirect'		=> $redirect,
		]);
		$success_msg = '';

		if (confirm_box(true))
		{
			$success_msg = count($topic_ids) === 1 ? 'TOPIC_RESTORED_SUCCESS' : 'TOPICS_RESTORED_SUCCESS';

			$data = phpbb_get_topic_data($topic_ids);

			foreach ($data as $topic_id => $row)
			{
				$return = $this->content_visibility->set_topic_visibility(ITEM_APPROVED, $topic_id, $row['forum_id'], $this->user->data['user_id'], time(), '');
				if (!empty($return))
				{
					$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_RESTORE_TOPIC', false, [
						'forum_id' => (int) $row['forum_id'],
						'topic_id' => (int) $topic_id,
						$row['topic_title'],
						$row['topic_first_poster_name'],
					]);
				}
			}
		}
		else
		{
			confirm_box(false, count($topic_ids) === 1 ? 'RESTORE_TOPIC' : 'RESTORE_TOPICS', $s_hidden_fields);
		}

		$topic_id = $this->request->variable('t', 0);
		if (!$this->request->is_set('quickmod', \phpbb\request\request_interface::REQUEST))
		{
			$redirect = $this->request->variable('redirect', "index.$this->php_ext");
			$redirect = reapply_sid($redirect);
			$redirect_message = 'PAGE';
		}
		else if ($topic_id)
		{
			$redirect = append_sid("{$this->root_path}viewtopic.$this->php_ext", 't=' . $topic_id);
			$redirect_message = 'TOPIC';
		}
		else
		{
			$redirect = append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $forum_id);
			$redirect_message = 'FORUM';
		}

		if (!$success_msg)
		{
			redirect($redirect);
		}
		else
		{
			meta_refresh(3, $redirect);
			trigger_error($this->lang->lang($success_msg) . '<br /><br />' . $this->lang->lang('RETURN_' . $redirect_message, '<a href="' . $redirect . '">', '</a>'));
		}
	}
}
