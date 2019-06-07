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

use phpbb\exception\http_exception;

class delete
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

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
	 * @param \phpbb\auth\auth					$auth				Auth object
	 * @param \phpbb\content_visibility			$content_visibility	Content visibility object
	 * @param \phpbb\db\driver\driver_interface	$db					Database object
	 * @param \phpbb\controller\helper			$helper				Controller helper object
	 * @param \phpbb\language\language			$lang				Language object
	 * @param \phpbb\log\log					$log				Log object
	 * @param \phpbb\request\request			$request			Request object
	 * @param \phpbb\template\template			$template			Template object
	 * @param \phpbb\user						$user				User object
	 * @param string							$root_path			phpBB root path
	 * @param string							$php_ext			php File extension
	 * @param array								$tables				phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\content_visibility $content_visibility,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth					= $auth;
		$this->content_visibility	= $content_visibility;
		$this->db					= $db;
		$this->helper				= $helper;
		$this->lang					= $lang;
		$this->log					= $log;
		$this->request				= $request;
		$this->template				= $template;
		$this->request				= $request;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	/**
	 * Delete posts.
	 *
	 * @param array		$post_ids				The post identifiers
	 * @param bool		$is_soft				Whether or not we're soft deleting
	 * @param string	$soft_delete_reason		The soft delete reason
	 * @param string	$action					The action
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function delete_posts(array $post_ids, $is_soft = false, $soft_delete_reason = '', $action = 'delete_post')
	{
		if (!phpbb_check_ids($post_ids, $this->tables['posts'], 'post_id', [$is_soft ? 'm_softdelete' : 'm_delete']))
		{
			throw new http_exception(404, 'NO_POST_SELECTED');
		}

		$redirect = $this->request->variable('redirect', build_url(['action', 'quickmod']));
		$forum_id = $this->request->variable('f', 0);

		$return_link = [];
		$topic_id = $affected_topics = $deleted_topics = 0;

		$success_msg = '';

		$s_hidden_fields = [
			'post_id_list'	=> $post_ids,
			'f'				=> $forum_id,
			'action'		=> $action,
			'redirect'		=> $redirect,
		];

		if (confirm_box(true) && $is_soft)
		{
			$post_info = phpbb_get_post_data($post_ids);

			$topic_info = $approve_log = [];

			// Group the posts by topic_id
			foreach ($post_info as $post_id => $post_data)
			{
				if ($post_data['post_visibility'] != ITEM_APPROVED)
				{
					continue;
				}

				$topic_id = (int) $post_data['topic_id'];

				$topic_info[$topic_id]['posts'][] = (int) $post_id;
				$topic_info[$topic_id]['forum_id'] = (int) $post_data['forum_id'];

				if ($post_id == $post_data['topic_first_post_id'])
				{
					$topic_info[$topic_id]['first_post'] = true;
				}

				if ($post_id == $post_data['topic_last_post_id'])
				{
					$topic_info[$topic_id]['last_post'] = true;
				}

				$approve_log[] = [
					'forum_id'		=> $post_data['forum_id'],
					'topic_id'		=> $post_data['topic_id'],
					'post_id'		=> $post_id,
					'post_subject'	=> $post_data['post_subject'],
					'poster_id'		=> $post_data['poster_id'],
					'post_username'	=> $post_data['post_username'],
					'username'		=> $post_data['username'],
				];
			}

			foreach ($topic_info as $topic_id => $topic_data)
			{
				$this->content_visibility->set_post_visibility(ITEM_DELETED, $topic_data['posts'], $topic_id, $topic_data['forum_id'], $this->user->data['user_id'], time(), $soft_delete_reason, isset($topic_data['first_post']), isset($topic_data['last_post']));
			}

			$affected_topics = count($topic_info);

			// None of the topics is really deleted, so a redirect won't hurt much.
			$deleted_topics = 0;

			$success_msg = count($post_info) === 1 ? $this->lang->lang('POST_DELETED_SUCCESS') : $this->lang->lang('POSTS_DELETED_SUCCESS');

			foreach ($approve_log as $row)
			{
				$post_username = ($row['poster_id'] == ANONYMOUS && !empty($row['post_username'])) ? $row['post_username'] : $row['username'];
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_SOFTDELETE_POST', false, [
					'forum_id'	=> (int) $row['forum_id'],
					'topic_id'	=> (int) $row['topic_id'],
					'post_id'	=> (int) $row['post_id'],
					$row['post_subject'],
					$post_username,
					$soft_delete_reason,
				]);
			}

			$topic_id = $this->request->variable('t', 0);

			// Return links
			$return_link = [];

			if ($affected_topics == 1 && $topic_id)
			{
				$return_link[] = $this->lang->lang('RETURN_TOPIC', '<a href="' . append_sid("{$this->root_path}viewtopic.$this->php_ext", "f=$forum_id&amp;t=$topic_id") . '">', '</a>');
			}

			$return_link[] = $this->lang->lang('RETURN_FORUM', '<a href="' . append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $forum_id) . '">', '</a>');
		}
		else if (confirm_box(true))
		{
			if (!function_exists('delete_posts'))
			{
				include($this->root_path . 'includes/functions_admin.' . $this->php_ext);
			}

			// Count the number of topics that are affected
			// I did not use COUNT(DISTINCT ...) because I remember having problems
			// with it on older versions of MySQL -- Ashe
			$topic_id_list = [];

			$sql = 'SELECT DISTINCT topic_id
				FROM ' . $this->tables['posts'] . '
				WHERE ' . $this->db->sql_in_set('post_id', $post_ids);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$topic_id_list[] = (int) $row['topic_id'];
			}
			$this->db->sql_freeresult($result);

			$affected_topics = count($topic_id_list);
			$post_data = phpbb_get_post_data($post_ids);

			foreach ($post_data as $id => $row)
			{
				$post_username = ($row['poster_id'] == ANONYMOUS && !empty($row['post_username'])) ? $row['post_username'] : $row['username'];
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_DELETE_POST', false, [
					'forum_id'	=> (int) $row['forum_id'],
					'topic_id'	=> (int) $row['topic_id'],
					'post_id'	=> (int) $row['post_id'],
					$row['post_subject'],
					$post_username,
					$soft_delete_reason,
				]);
			}

			// Now delete the posts, topics and forums are automatically resync'ed
			delete_posts('post_id', $post_ids);

			$sql = 'SELECT COUNT(topic_id) AS topics_left
				FROM ' . $this->tables['topics'] . '
				WHERE ' . $this->db->sql_in_set('topic_id', $topic_id_list);
			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$deleted_topics = $row !== false ? $affected_topics - $row['topics_left'] : $affected_topics;

			$topic_id = $this->request->variable('t', 0);

			// Return links
			$return_link = [];

			if ($affected_topics == 1 && !$deleted_topics && $topic_id)
			{
				$return_link[] = $this->lang->lang('RETURN_TOPIC', '<a href="' . append_sid("{$this->root_path}viewtopic.$this->php_ext", "f=$forum_id&amp;t=$topic_id") . '">', '</a>');
			}

			$return_link[] = $this->lang->lang('RETURN_FORUM', '<a href="' . append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $forum_id) . '">', '</a>');

			if (count($post_ids) === 1)
			{
				if ($deleted_topics)
				{
					// We deleted the only post of a topic, which in turn has
					// been removed from the database
					$success_msg = $this->lang->lang('TOPIC_DELETED_SUCCESS');
				}
				else
				{
					$success_msg = $this->lang->lang('POST_DELETED_SUCCESS');
				}
			}
			else
			{
				if ($deleted_topics)
				{
					// Some of topics disappeared
					$success_msg = $this->lang->lang('POSTS_DELETED_SUCCESS') . '<br /><br />' . $this->lang->lang('EMPTY_TOPICS_REMOVED_WARNING');
				}
				else
				{
					$success_msg = $this->lang->lang('POSTS_DELETED_SUCCESS');
				}
			}
		}
		else
		{
			$this->lang->add_lang('posting');

			$only_softdeleted = false;
			if ($this->auth->acl_get('m_delete', $forum_id) && $this->auth->acl_get('m_softdelete', $forum_id))
			{
				// If there are only soft deleted posts, we display a message why the option is not available
				$sql = 'SELECT post_id
					FROM ' . $this->tables['posts'] . '
					WHERE ' . $this->db->sql_in_set('post_id', $post_ids) . '
						AND post_visibility <> ' . ITEM_DELETED;
				$result = $this->db->sql_query_limit($sql, 1);
				$only_softdeleted = !$this->db->sql_fetchfield('post_id');
				$this->db->sql_freeresult($result);
			}

			$this->template->assign_vars([
				'DELETE_POST_PERMANENTLY_EXPLAIN'	=> $this->lang->lang('DELETE_POST_PERMANENTLY', (int) count($post_ids)),
				'S_ALLOWED_DELETE'					=> (bool) $this->auth->acl_get('m_delete', $forum_id),
				'S_ALLOWED_SOFTDELETE'				=> (bool) $this->auth->acl_get('m_softdelete', $forum_id),
				'S_SOFTDELETED'						=> (bool) $only_softdeleted,
			]);

			$s_count = count($post_ids);
			$l_confirm = $s_count === 1 ? 'DELETE_POST' : 'DELETE_POSTS';
			if ($only_softdeleted)
			{
				$l_confirm .= '_PERMANENTLY';
				$s_hidden_fields['delete_permanent'] = '1';
			}
			else if (!$this->auth->acl_get('m_softdelete', $forum_id))
			{
				$s_hidden_fields['delete_permanent'] = '1';
			}

			confirm_box(false, [$l_confirm, $s_count], build_hidden_fields($s_hidden_fields), 'confirm_delete_body.html');
		}

		$redirect = $this->request->variable('redirect', "index.$this->php_ext");
		$redirect = reapply_sid($redirect);

		if (!$success_msg)
		{
			return redirect($redirect);
		}
		else
		{
			if ($affected_topics != 1 || $deleted_topics || !$topic_id)
			{
				$redirect = $this->helper->route('mcp_view_forum', ['f' => $forum_id]);
			}

			$this->helper->assign_meta_refresh_var(3, $redirect);

			return $this->helper->message($success_msg . '<br /><br />' . $this->lang->lang('RETURN_PAGE', '<a href="' . $redirect . '">', '</a>') . '<br /><br />' . implode('<br /><br />', $return_link));
		}
	}

	/**
	 * Delete topics.
	 *
	 * @param array		$topic_ids				The topic identifiers
	 * @param bool		$is_soft				Whether or not we're soft deleting
	 * @param string	$soft_delete_reason		The soft delete reason
	 * @param string	$action					The action
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function delete_topics(array $topic_ids, $is_soft = false, $soft_delete_reason = '', $action = 'delete_topic')
	{
		if (!phpbb_check_ids($topic_ids, $this->tables['topics'], 'topic_id', [$is_soft ? 'm_softdelete' : 'm_delete']))
		{
			throw new http_exception(404, 'TOPIC_NOT_EXIST');
		}

		$redirect = $this->request->variable('redirect', build_url(['action', 'quickmod']));
		$forum_id = $this->request->variable('f', 0);

		$s_hidden_fields = [
			'topic_id_list'	=> $topic_ids,
			'f'				=> $forum_id,
			'action'		=> $action,
			'redirect'		=> $redirect,
		];
		$success_msg = '';

		if (confirm_box(true))
		{
			$success_msg = count($topic_ids) === 1 ? 'TOPIC_DELETED_SUCCESS' : 'TOPICS_DELETED_SUCCESS';

			$data = phpbb_get_topic_data($topic_ids);

			foreach ($data as $topic_id => $row)
			{
				if ($row['topic_moved_id'])
				{
					$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_DELETE_SHADOW_TOPIC', false, [
						'forum_id' => (int) $row['forum_id'],
						'topic_id' => (int) $topic_id,
						$row['topic_title'],
					]);
				}
				else
				{
					// Only soft delete non-shadow topics
					if ($is_soft)
					{
						$return = $this->content_visibility->set_topic_visibility(ITEM_DELETED, $topic_id, $row['forum_id'], $this->user->data['user_id'], time(), $soft_delete_reason);
						if (!empty($return))
						{
							$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_SOFTDELETE_TOPIC', false, [
								'forum_id' => (int) $row['forum_id'],
								'topic_id' => (int) $topic_id,
								$row['topic_title'],
								$row['topic_first_poster_name'],
								$soft_delete_reason,
							]);
						}
					}
					else
					{
						$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_DELETE_TOPIC', false, [
							'forum_id' => (int) $row['forum_id'],
							'topic_id' => (int) $topic_id,
							$row['topic_title'],
							$row['topic_first_poster_name'],
							$soft_delete_reason,
						]);
					}
				}
			}

			if (!$is_soft)
			{
				delete_topics('topic_id', $topic_ids);
			}
		}
		else
		{
			$this->lang->add_lang('posting');

			// If there are only shadow topics, we neither need a reason nor softdelete
			$sql = 'SELECT topic_id
				FROM ' . $this->tables['topics'] . '
				WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids) . '
					AND topic_moved_id = 0';
			$result = $this->db->sql_query_limit($sql, 1);
			$only_shadow = !$this->db->sql_fetchfield('topic_id');
			$this->db->sql_freeresult($result);

			$only_softdeleted = false;
			if (!$only_shadow && $this->auth->acl_get('m_delete', $forum_id) && $this->auth->acl_get('m_softdelete', $forum_id))
			{
				// If there are only soft deleted topics, we display a message why the option is not available
				$sql = 'SELECT topic_id
					FROM ' . $this->tables['topics'] . '
					WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids) . '
						AND topic_visibility <> ' . ITEM_DELETED;
				$result = $this->db->sql_query_limit($sql, 1);
				$only_softdeleted = !$this->db->sql_fetchfield('topic_id');
				$this->db->sql_freeresult($result);
			}

			$this->template->assign_vars([
				'DELETE_TOPIC_PERMANENTLY_EXPLAIN'	=> $this->lang->lang('DELETE_TOPIC_PERMANENTLY', count($topic_ids)),
				'S_ALLOWED_DELETE'					=> (bool) $this->auth->acl_get('m_delete', $forum_id),
				'S_ALLOWED_SOFTDELETE'				=> (bool) $this->auth->acl_get('m_softdelete', $forum_id),
				'S_SHADOW_TOPICS'					=> (bool) $only_shadow,
				'S_SOFTDELETED'						=> (bool) $only_softdeleted,
				'S_TOPIC_MODE'						=> true,
			]);

			$l_confirm = count($topic_ids) === 1 ? 'DELETE_TOPIC' : 'DELETE_TOPICS';
			if ($only_softdeleted)
			{
				$l_confirm .= '_PERMANENTLY';
				$s_hidden_fields['delete_permanent'] = '1';
			}
			else if ($only_shadow || !$this->auth->acl_get('m_softdelete', $forum_id))
			{
				$s_hidden_fields['delete_permanent'] = '1';
			}

			confirm_box(false, $l_confirm, build_hidden_fields($s_hidden_fields), 'confirm_delete_body.html', $this->helper->get_current_url());
		}

		$topic_id = $this->request->variable('t', 0);
		if (!$this->request->is_set('quickmod', \phpbb\request\request_interface::REQUEST))
		{
			$redirect = $this->request->variable('redirect', "index.$this->php_ext");
			$redirect = reapply_sid($redirect);
			$redirect_message = 'PAGE';
		}
		else if ($is_soft && $topic_id)
		{
			$redirect = append_sid("{$this->root_path}viewtopic.$this->php_ext", 't=' . $topic_id, true, false, true);
			$redirect_message = 'TOPIC';
		}
		else
		{
			$redirect = append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $forum_id, true, false, true);
			$redirect_message = 'FORUM';
		}

		if (!$success_msg)
		{
			return redirect($redirect);
		}
		else
		{
			$return_page = $this->lang->lang('RETURN_' . $redirect_message, '<a href="' . $redirect . '">', '</a>');

			$this->helper->assign_meta_refresh_var(3, $redirect);

			return $this->helper->message($this->lang->lang($success_msg) . '<br /><br />' . $return_page);
		}
	}
}
