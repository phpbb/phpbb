<?php
/**
*
* @package controller
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Controller interface
* @package phpBB3
*/
class phpbb_controller_post_revisions
{
	/**
	* Construct method
	*
	* @param phpbb_controller_helper $helper Controller helper object
	* @param phpbb_user $user User object
	* @param phpbb_db_driver $db Database object
	* @param phpbb_config $config Config object
	* @param phpbb_auth $auth Auth object
	* @param phpbb_request $request Request object
	* @param string $phpbb_root_path phpBB root path
	* @param string $php_ext PHP extension
	*/
	public function __construct(phpbb_controller_helper $helper, phpbb_user $user, phpbb_db_driver $db, phpbb_config $config, phpbb_auth $auth, phpbb_request $request, $phpbb_root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->user = $user;
		$this->db = $db;
		$this->config = $config;
		$this->auth = $auth;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->user->add_lang('revisions');
	}

	/**
	* Generate a URL
	*
	* @param string $route The route to travel
	* @return string The URL
	*/
	protected function url($route)
	{
		return append_sid($this->phpbb_root_path . 'app.' . $this->php_ext, array('controller' => $route));
	}

	/**
	* View the revisions a post. By default this compares the current revision
	* to the earliest available revision (usually the original post)
	*
	* This controller method is called directly from the path:
	* /post/{id}/revisions
	*
	* @param int $id Post ID
	* @return Response
	*/
	public function view($id)
	{
		return $this->compare($id);
	}

	/**
	* Compare two revisions
	*
	* This controller method is called directly from the paths:
	* /post/{id}/revisions/{from}...{to}
	* /post/{id}/revisions/{to}
	* It is also called indirectly by the path:
	* /post/{id}/revisions
	* Which supplies it with the revision IDs.
	*
	* @param int $id Post ID
	* @param int $from Starting point in the comparison (a revision ID)
	* @param int $to Ending point in the comparison (a revision ID)
	* @return Response
	*/
	public function compare($id, $from = 0, $to = 0)
	{
		// Ensure that the given post has revisions
		$post = new phpbb_revisions_post($id, $this->db, $this->config, $this->auth);
		$post_data = $post->get_post_data();

		if (!$this->get_view_permission($post_data))
		{
			return $this->helper->error($this->user->lang('ERROR_AUTH_VIEW'), 401);
		}

		$revisions = $post->get_revisions();
		$current = $post->get_current_revision();

		if (0 === $post->get_revision_count())
		{
			return $this->helper->error($this->user->lang('ERROR_NO_POST_REVISIONS', 404));
		}

		// If $from is empty, the earliest available revision is used
		if (!$from)
		{
			$sql = 'SELECT revision_id
				FROM ' . POST_REVISIONS_TABLE . '
				WHERE post_id = ' . (int) $id . '
				ORDER BY revision_id ASC';
			$result = $this->db->sql_query($sql);
			$from = (int) $this->db->sql_fetchfield('revision_id');
			$this->db->sql_freeresult($result);
		}

		$from_revision = new phpbb_revisions_revision($from, $this->db);
		// If $to is empty, the current post is used
		$to_revision = $to ? new phpbb_revisions_revision($to, $this->db) : $post->get_current_revision();

		$comparison = new phpbb_revisions_comparison($from_revision, $to_revision);
		$comparison->ouput_template_block($post, $this->template, $this->user, $this->auth, $this->request, $can_revert, $this->phpbb_root_path, $this->php_ext);

		$this->template->assign_vars(array(
			'POST_USERNAME'		=> get_username_string('full', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
			'U_PROFILE'			=> get_username_string('profile', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
			
			'RANK_TITLE'		=> $post_data['rank_title'],
			'RANK_IMG'			=> $post_data['rank_image'],

			'AVATAR'			=> get_user_avatar($post_data['user_avatar'], $post_data['user_avatar_type'], $post_data['user_avatar_width'], $post_data['user_avatar_height']),

			'POST_DATE'			=> $user->format_date($post_data['post_time']),
			'POST_SUBJECT'		=> $comparison->get_subject_diff_rendered(),
			'CURRENT_SUBJECT' 	=> $current->get_subject(),
			'MESSAGE'			=> $comparison->get_text_diff_rendered(),
			'SIGNATURE'			=> ($post_data['enable_sig']) ? $post_data['user_sig_parsed'] : '',

			'POSTER_JOINED'		=> $this->user->format_date($post_data['user_regdate']),
			'POSTER_POSTS'		=> $post_data['user_posts'],
			'POSTER_LOCATION'	=> $post_data['user_from'],

			'POST_IMG'			=> $user->img('icon_post_target', 'POST'),

			'POST_ID'			=> $post_data['post_id'],
			'POSTER_ID'			=> $post_data['poster_id'],

			'U_VIEW_REVISIONS'	=> append_sid("{$this->phpbb_root_path}app.{$this->php_ext}", array('controller' => "post/$post_id/revisions")),
			'U_VIEW_POST'		=> append_sid("{$this->phpbb_root_path}viewtopic.{$this->php_ext}", array('f' => $post_data['forum_id'], 't' => $post_data['topic_id'], 'p' => $post_id)) . '#p' . $post_id,
		));

		return $this->helper->render('revisions_body.html', $this->user->lang('REVISIONS_COMPARE_TITLE'));
	}

	/**
	* View a given revision ID as if it were the current post on the viewtopic
	* page
	*
	* /post/{id}/revision/{revision_id}
	*
	* @param int $id Post ID
	* @param int $revision_id Revision ID
	* @return Response
	*/
	public function view_revision_as_post($id, $revision_id)
	{
		$post = new phpbb_revisions_post($id, $this->db);
		$revision = !$revision_id ? $post->get_current_revision() : new phpbb_revisions_revision($revision_id, $this->db);

		$this->template->assign_vars(array(
			'POST_USERNAME'		=> get_username_string('full', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
			'U_PROFILE'			=> get_username_string('profile', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
			
			'RANK_TITLE'		=> $post_data['rank_title'],
			'RANK_IMG'			=> $post_data['rank_image'],

			'AVATAR'			=> get_user_avatar($post_data['user_avatar'], $post_data['user_avatar_type'], $post_data['user_avatar_width'], $post_data['user_avatar_height']),

			'POST_DATE'			=> $this->user->format_date($post_data['post_time']),
			'POST_SUBJECT'		=> $revision->get_subject(),
			'MESSAGE'			=> $revision->get_text(),
			'SIGNATURE'			=> ($post_data['enable_sig']) ? $post_data['user_sig_parsed'] : '',

			'POSTER_JOINED'		=> $this->user->format_date($post_data['user_regdate']),
			'POSTER_POSTS'		=> $post_data['user_posts'],
			'POSTER_LOCATION'	=> $post_data['user_from'],

			'POST_IMG'			=> $this->user->img('icon_post_target', 'POST'),

			'POST_ID'			=> $post_data['post_id'],
			'POSTER_ID'			=> $post_data['poster_id'],

			'L_VIEWING_POST_REVISION_EXPLAIN'	=> !$display_comparison ? $user->lang('VIEWING_POST_REVISION_EXPLAIN', $current->get_username() . $current->get_avatar(20, 20), $user->format_date($current->get_time())) : '',

			'U_VIEW_REVISIONS'	=> append_sid("{$this->phpbb_root_path}app.{$this->php_ext}", array('controller' => "post/$post_id/revisions")),
			'U_VIEW_POST'		=> append_sid("{$this->phpbb_root_path}viewtopic.{$this->php_ext}", array('f' => $post_data['forum_id'], 't' => $post_data['topic_id'], 'p' => $post_id)) . '#p' . $post_id,
		));

		return $this->helper->render('revisions_view_body.html', $this->user->lang('REVISIONS_COMPARE_TITLE'));
	}

	/**
	* Set a specific revision to protected or unprotected
	*
	* This controller method is accessed directly from the paths:
	* /post/{id}/revision/{revision_id}/protect
	* /post/{id}/revision/{revision_id}/unprotect
	* $mode is supplied by the route definition as either protect or unprotect
	*
	* @param string $mode Whether to protect or unprotect the revision
	* @param int $id Post ID
	* @param int $revision_id Revision ID
	* @return Response
	*/
	public function protect_unprotect($mode, $id, $revision_id)
	{
		// Ensure that $mode is one of 'protect' or 'unprotect'
		$mode = 'protect' == $mode || 'unprotect' == $mode ? $mode : 'unprotect';
		$post = new phpbb_revisions_post($id, $this->db);
		$post_data = $post->get_post_data();
		$revisions = $post->get_revisions();
		if (!$auth->acl_get('m_protect_revisions', $post_data['forum_id']))
		{
			$error = $mode = 'protect' ? 'NO_AUTH_PROTECT_REVISIONS' : 'NO_AUTH_UNPROTECT_REVISIONS';
			$this->send_ajax_response(array(
				'success'	=> false,
				'message'	=> $this->user->lang($error),
			));

			return $this->helper->error($this->user->lang($error), 401);
		}
		else if (!isset($revisions[$revision_id]))
		{
			$this->send_ajax_response(array(
				'success'	=> false,
				'message'	=> $this->user->lang('ERROR_REVISION_NOT_FOUND'),
			));

			return $this->helper->error($this->user->lang('ERROR_REVISION_NOT_FOUND'), 404);
		}

		// If we are trying to protect a protected revision or unprotect an
		// unprotected revision, let's go no further
		if (($revisions[$revision_id]->is_protected() && $mode == 'protect') ||
			(!$revisions[$revision_id]->is_protected() && $mode == 'unprotect'))
		{
			return $this->view($id);
		}

		switch ($mode)
		{
			case 'protect'
			case 'unprotect':
				$result = $this->$mode($revision_id);
			break;
		}

		$post_data = $post->get_post_data(true);
		$revisions = $post->get_revisions(true);
		$message = $this->user->lang(sizeof($revisions) ? 'REVISION_DELETED_SUCCESS' : 'REVISION_DELETED_SUCCESS_NO_MORE');

		$this->send_ajax_response(array(
			'success'	=> true,
			'message'	=> $message,
		));

		return $this->view($id);
	}

	/**
	* Delete a specific revision
	*
	* This controller method is accessed directly from the path:
	* /post/{id}/revision/{revision_id}/delete
	*
	* @param int $id Post ID
	* @param int $revision_id Revision ID
	* @return Response
	*/
	public function delete($id, $revision_id)
	{
		$post = new phpbb_revisions_post($id, $this->db);
		$post_data = $post->get_post_data();
		$revisions = $post->get_revisions();
		if (!$this->auth->acl_get('m_delete_revisions', $post_data['forum_id']))
		{
			$this->send_ajax_response(array(
				'success'	=> false,
				'message'	=> $this->user->lang('ERROR_AUTH_DELETE_REVISIONS'),
			));

			return $this->helper->error($this->user->lang('ERROR_AUTH_DELETE_REVISIONS'), 401);
		}
		else if (!isset($revisions[$revision_id]))
		{
			$this->send_ajax_response(array(
				'success'	=> false,
				'message'	=> $this->user->lang('ERROR_REVISION_NOT_FOUND'),
			));

			return $this->helper->error($this->user->lang('ERROR_REVISION_NOT_FOUND'), 404);
		}

		$result = $this->perform_delete($revision_id);

		$post_data = $post->get_post_data(true);
		$revisions = $post->get_revisions(true);
		$message = $this->user->lang(sizeof($revisions) ? 'REVISION_DELETED_SUCCESS' : 'REVISION_DELETED_SUCCESS_NO_MORE');

		$this->send_ajax_response(array(
			'success'	=> true,
			'message'	=> $message,
		));

		return $this->view($id);
	}

	/**
	* Helper method for deleting a revision
	*
	* This does NOT check authorization.
	*
	* @param mixed $revision_id Revision ID or array of revision IDs
	* @return bool
	*/
	protected function perform_delete($revision_id)
	{
		if (!is_array($revision_id))
		{
			$revision_id = array($revision_id);
		}
		
		$sql = 'DELETE FROM ' . POST_REVISIONS_TABLE . '
			WHERE ' . $this->db->sql_in_set('revision_id', $revision_id);
		return (bool) $this->db->sql_query($sql);
	}

	/**
	* Protect the specified revision
	*
	* Note: this does not check authorization
	*
	* @param mixed $revision_id Revision ID or array of revision IDs
	*/
	protected function protect($revision_id)
	{
		if (!is_array($revision_id))
		{
			$revision_id = array($revision_id);
		}

		$sql = 'UPDATE ' . POST_REVISIONS_TABLE . '
			SET revision_protected = 1
			WHERE ' . $this->db->sql_in_set('revision_id', $revision_id);
		return (bool) $db->sql_query($sql);
	}

	/**
	* Unprotect the specified revision
	*
	* Note: this does not check authorization
	*
	* @param mixed $revision_id Revision ID or array of revision IDs
	*/
	protected function unprotect($revision_id)
	{
		if (!is_array($revision_id))
		{
			$revision_id = array($revision_id);
		}

		$sql = 'UPDATE ' . POST_REVISIONS_TABLE . '
			SET revision_protected = 0
			WHERE ' . $this->db->sql_in_set('revision_id', $revision_id);
		return (bool) $db->sql_query($sql);
	}

	/**
	* Restore a post to a given revision
	*
	* This controller method is accessed directly from the path:
	* /post/{id}/restore/{to}
	*
	* @param $id
	*/
	public function restore($id, $to)
	{
		$post = new phpbb_revisions_post($id, $this->db);
		$post_data = $post->get_post_data();
		$revisions = $post->get_revisions();

		if (!$this->get_restore_permission($post_data))
		{
			return $this->helper->error($this->user->lang('ERROR_AUTH_RESTORE'), 401);
		}

		if ($this->request->is_set_post('submit'))
		{
			$error = '';
			$code = 500;
			if (!check_form_key('restore', 120))
			{
				$error = 'FORM_INVALID';
			}
			else if (!isset($revisions[$to]))
			{
				$error = 'ERROR_REVISION_NOT_FOUND';
				$code = 404;
			}
			else if ($post_data['post_edit_locked'] && !$auth->acl_get('m_revisions', $post_data['forum_id']))
			{
				$error = 'ERROR_POST_EDIT_LOCKED';
				// 401 is unauthorized
				$code = 401;
			}

			if ($error)
			{
				$this->send_ajax_response(array(
					'success' => false,
					'message' => $user->lang($error),
				));

				return $this->helper->error($this->user->lang($error), $code);
			}

			$revert_result = $post->revert($revert_id);

			if ($revert_result !== phpbb_revisions_post::REVISION_REVERT_SUCCESS)
			{
				switch ($revert_result)
				{
					default:
					case phpbb_revisions_post::REVISION_NOT_FOUND:
						$error = 'ERROR_REVISION_NOT_FOUND';
					break;

					case phpbb_revisions_post::REVISION_INSERT_FAIL:
						$error = 'ERROR_REVISION_INSERT_FAIL';
					break;

					case phpbb_revisions_post::REVISION_POST_UPDATE_FAIL:
						$error = 'ERROR_REVISION_POST_UPDATE_FAIL';
					break;
				}

				$this->send_ajax_response(array(
					'success' => false,
					'message' => $user->lang($error),
				));

				return $this->helper->error($this->user->lang($error), $code);
			}

			// Because we've changed things up, we need to update our arrays
			$post_data = $post->get_post_data(true);
			$revisions = $post->get_revisions(true);

			$template->assign_vars(array(
				'L_REVISIONS_ACTION_SUCCESS'	=> $this->user->lang('POST_REVERTED_SUCCESS'),
			));

			if ($this->request->is_ajax())
			{
				$this->send_ajax_response(array(
					'success' => true,
					'message' => $this->user->lang('POST_REVERTED_SUCCESS'),
				));
			}

			return $this->view($id);
		}
		else
		{
			add_form_key('restore');

			$this->template->assign_vars(array(
				'U_ACTION'			=> $this->url("post/$id/restore/$to"),
				'VIEWING_REVERT'	=> true,
				'S_HIDDEN_FIELDS'	=> build_hidden_fields(array(
					'id'	=> $id,
					'to'	=> $to,
				)),
			));

			return $this->helper->render('revisions_revert_body.html', $this->user->lang('REVISIONS_REVERT_TITLE'));
		}
	}

	/**
	* Determine whether the current user can restore a post to a different
	* revision
	*
	* @param array $post_data Array of post data
	* @return bool
	*/
	protected function get_restore_permission($post_data)
	{
		// If the post is a wiki post, the user can edit wiki posts in this
		// forum, and the post is not edit locked
		$can_restore_wiki = $post_data['post_wiki']
			&& $this->auth->acl_getf('f_wiki_edit', $post_data['forum_id'])
			&& !$post_data['post_edit_locked'];
		// If the user is the original poster, and the user can manage his own
		// posts' revisions in this forum, and the post is not edit locked
		$can_restore_own = $this->user->data['user_id'] == $post_data['poster_id']
			&& $this->auth->acl_getf('f_revisions', $post_data['forum_id'])
			&& !$post_data['post_edit_locked'];
		// If either of the above is true, or if the user has moderator
		// permissions for managing revisions
		return $can_restore_own ||
			$can_restore_wiki ||
			$this->auth->acl_get('m_revisions');
	}

	/**
	* Determine whether the current user can view the post's revisions
	*
	* @param array $post_data Array of post data
	* @return bool
	*/
	protected function get_view_permission($post_data)
	{
		// If the post is a wiki post and the user has wiki edit permission
		// in the current forum
		$can_view_wiki_revisions = $post_data['post_wiki'] && $this->auth->acl_getf('f_wiki_edit', $post_data['forum_id']);
		// If the user is the original poster, and the user can manage his own
		// posts' revisions in this forum
		$can_view_own_revisions = $this->user->data['user_id'] == $post_data['poster_id'] &&
			$this->auth->acl_getf('f_revisions', $post_data['forum_id']);
		// If either of the above is true, or if the user has moderator
		// permissions for managing revisions
		return $this->auth->acl_get('m_revisions') ||
			$can_view_wiki_revisions || 
			$can_view_own_revisions;
	}

	/**
	* Send the JSON response if the request is AJAX
	*
	* @param array $data The array of data to send with the response
	* @return null
	*/
	protected function send_ajax_response($data)
	{
		if ($this->request->is_ajax())
		{
			$json_response = new phpbb_json_response();
			$json_response->send($data);
		}
	}
}
