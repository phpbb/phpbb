<?php
/**
*
* @package controller
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

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
	* @param phpbb_template $template Template object
	* @param string $phpbb_root_path phpBB root path
	* @param string $php_ext PHP extension
	*/
	public function __construct(phpbb_controller_helper $helper, phpbb_user $user, phpbb_db_driver $db, phpbb_config $config, phpbb_auth $auth, phpbb_request $request, phpbb_template $template, $phpbb_root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->user = $user;
		$this->db = $db;
		$this->config = $config;
		$this->auth = $auth;
		$this->request = $request;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->user->add_lang('revisions');

		if (!function_exists('get_user_avatar'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		}
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
	* Compare two revisions
	*
	* Defaults to comparison between oldest available revision & current post
	*
	* This controller method is accessed directly from the routes:
	* /post/{id}/revisions/{from}...{to}
	* /post/{id}/revisions/{to}
	* /post/{id}/revisions
	*
	* @param int $id Post ID
	* @param int $from Starting point in the comparison (a revision ID)
	* @param int $to Ending point in the comparison (a revision ID)
	* @return Response
	*/
	public function compare($id, $from = false, $to = false)
	{
		// Changing the comparison range uses POST instead of changing the URL
		// So if we need to, we overwrite the revision ids
		$from = $this->request->is_set_post('first') ? $this->request->variable('first', 0) : $from;
		$to = $this->request->is_set_post('last') ? $this->request->variable('last', 0) : $to;

		$post = new phpbb_revisions_post($id, $this->db, $this->config, $this->auth);
		$post_data = $post->get_post_data();

		if (!sizeof($post_data))
		{
			return $this->helper->error($this->user->lang('ERROR_NO_POST_REVISIONS'), 404);
		}

		// Ensure that the user can view revisions for this post
		if (!$this->get_view_permission($post_data))
		{
			// 401 is the Unauthorized status code
			return $this->helper->error($this->user->lang('ERROR_AUTH_VIEW'), 401);
		}

		$revisions = $post->get_revisions();
		$current = $post->get_current_revision();

		// This allows an action to be applied to multiple revisions
		// @todo implement on interface and test
		if (isset($_POST['delete']) || isset($_POST['protect']) || isset($_POST['unprotect']))
		{
			$action = isset($_POST['delete']) ? 'delete' : (isset($_POST['protect']) ? 'protect' : 'unprotect');
			$action_permission = 'delete' == $action ? 'm_delete_revisions' : 'm_protect_revisions';
			if (!$this->auth->acl_get($action_permission, $post_data['forum_id']))
			{
				// Get the localised word describing each of the actions
				$action_lang = strtolower($this->user->lang(strtoupper($action)));
				$this->send_ajax_response(array(
					'success'	=> false,
					'message'	=> $this->user->lang($error),
				));

				return $this->helper->error($this->user->lang('ERROR_AUTH_ACTION', $action_lang), 401);
			}

			$action_ids = $this->request->variable('revision_ids', array(0));
			$result = (bool) $this->$action($action_ids);

			// Default to failure; this will be replaced upon success in the
			// following 'if' block
			$l_result = $this->user->lang('REVISION_' . strtoupper($action) . '_FAIL');

			if ($result)
			{
				$post_data = $post->get_post_data(true);
				$revisions = $post->get_revisions(true);

				$l_result = $this->user->lang('REVISION_' . strtoupper($action) . 'ED_SUCCESS');
			}

			$this->send_ajax_response(array(
				'success'	=> $result,
				'message'	=> $l_result,
			));
		}

		// revisions/1...2	= revision 1 compared to revision 2
		// revisions/0...2	= current post compared to revision 2
		// revisions/2...0	= revision 2 compared to current post
		// revisions/2	= earliest available revision compared to revision 2

		// 0 = current revision

		if (!sizeof($revisions) || $from === 0)
		{
			$from_revision = $post->get_current_revision();
		}
		else if ($from === false)
		{
			$from_revision = current($revisions);
		}
		$from_revision = !$from ? $from_revision : $revisions[$from];
		$to_revision = $to ? $revisions[$to] : $post->get_current_revision();

		$comparison = new phpbb_revisions_comparison($from_revision, $to_revision);
		$comparison->output_template_block($post, $this->template, $this->user, $this->auth, $this->request, $this->get_restore_permission($post_data), $this->phpbb_root_path, $this->php_ext);

		$this->template->assign_vars(array(
			'POST_USERNAME'		=> get_username_string('full', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
			'U_PROFILE'			=> get_username_string('profile', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
			
			'RANK_TITLE'		=> $post_data['rank_title'],
			'RANK_IMG'			=> $post_data['rank_image'],

			'AVATAR'			=> get_user_avatar($post_data['user_avatar'], $post_data['user_avatar_type'], $post_data['user_avatar_width'], $post_data['user_avatar_height']),

			'POST_DATE'			=> $this->user->format_date($post_data['post_time']),
			'POST_SUBJECT'		=> $comparison->get_subject_diff_rendered(),
			'CURRENT_SUBJECT' 	=> $current->get_subject(),
			'MESSAGE'			=> $comparison->get_text_diff_rendered(),
			'SIGNATURE'			=> ($post_data['enable_sig']) ? $post_data['user_sig_parsed'] : '',

			'POSTER_JOINED'		=> $this->user->format_date($post_data['user_regdate']),
			'POSTER_POSTS'		=> $post_data['user_posts'],
			'POSTER_LOCATION'	=> $post_data['user_from'],

			'POST_IMG'			=> $this->user->img('icon_post_target', 'POST'),

			'POST_ID'			=> $post_data['post_id'],
			'POSTER_ID'			=> $post_data['poster_id'],

			'U_VIEW_REVISIONS'	=> $this->url("post/{$post_data['post_id']}/revisions"),
			'U_VIEW_POST'		=> append_sid("{$this->phpbb_root_path}viewtopic.{$this->php_ext}", array('f' => $post_data['forum_id'], 't' => $post_data['topic_id'], 'p' => $post_data['post_id'])) . '#p' . $post_data['post_id'],

			'REVISION_COUNT'	=> sizeof($revisions),
		));

		return $this->helper->render('revisions_body.html', $this->user->lang('REVISIONS_COMPARE_TITLE'));
	}

	/**
	* View a given revision ID as if it were the current post on the viewtopic
	* page
	*
	* This controller method is accessed directly from the route:
	* /post/{id}/revision/{revision_id}
	*
	* @param int $id Post ID
	* @param int $revision_id Revision ID
	* @return Response
	*/
	public function view_revision_as_post($id, $revision_id)
	{
		$post = new phpbb_revisions_post($id, $this->db, $this->config, $this->auth);
		$post_data = $post->get_post_data();
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

			'L_VIEWING_POST_REVISION_EXPLAIN'	=> $this->user->lang('VIEWING_POST_REVISION_EXPLAIN',
				$revision->get_avatar(20, 20) . ' ' . $revision->get_username(),
				$this->user->format_date($revision->get_time())
			),

			'U_VIEW_REVISIONS'	=> $this->url("post/{$post_data['post_id']}/revisions"),
			'U_VIEW_POST'		=> append_sid("{$this->phpbb_root_path}viewtopic.{$this->php_ext}", array('f' => $post_data['forum_id'], 't' => $post_data['topic_id'], 'p' => $post_data['post_id'])) . '#p' . $post_data['post_id'],
		));

		return $this->helper->render('revisions_view_body.html', $this->user->lang('REVISIONS_COMPARE_TITLE'));
	}

	/**
	* Display the page on which to set a specific revision to protected or
	* unprotected
	*
	* This controller method is accessed directly from the routes:
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
		// Ensure that $mode is one of 'protect' or 'unprotect' (default)
		$mode = in_array($mode, array('protect', 'unprotect')) ? $mode : 'unprotect';
		$post = new phpbb_revisions_post($id, $this->db, $this->config, $this->auth);
		$post_data = $post->get_post_data();
		$revisions = $post->get_revisions();

		if (!$this->auth->acl_get('m_protect_revisions', $post_data['forum_id']))
		{
			$error = ($mode == 'protect') ? 'NO_AUTH_PROTECT_REVISIONS' : 'NO_AUTH_UNPROTECT_REVISIONS';
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
			return $this->compare($id);
		}

		$result = $this->$mode($revision_id);

		$post_data = $post->get_post_data(true);
		$revisions = $post->get_revisions(true);
		$message = $this->user->lang($mode == 'protect' ? 'REVISION_PROTECTED_SUCCESS' : 'REVISION_UNPROTECTED_SUCCESS');

		$this->send_ajax_response(array(
			'revision_id' => $revision_id,
			'success'	=> true,
			'message'	=> $message,
		));

		return $this->compare($id);
	}

	/**
	* Display the page on which to delete a specific revision
	*
	* This controller method is accessed directly from the path:
	* /post/{id}/revision/{revision_id}/delete
	*
	* @param int $id Post ID
	* @param int $revision_id Revision ID
	* @return Response
	*/
	public function delete_revision($id, $revision_id)
	{
		$post = new phpbb_revisions_post($id, $this->db, $this->config, $this->auth);
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

		$result = $this->delete($revision_id);

		$this->send_ajax_response(array(
			'success'	=> true,
			'message'	=> $this->user->lang('REVISION_DELETED_SUCCESS'),
		));

		return $this->compare($id);
	}

	/**
	* Helper method for deleting one or more post revisions
	*
	* This does NOT check authorization, but should not be called without
	* checking for m_revisions_delete
	*
	* @param mixed $revision_id Revision ID or array of revision IDs
	* @return bool
	*/
	protected function delete($revision_id)
	{
		if (!is_array($revision_id))
		{
			$revision_id = array($revision_id);
		}

		// First we need to get the post IDs for the revisions so we can
		// update the revision count on the posts table
		$sql = 'SELECT post_id
			FROM ' . POST_REVISIONS_TABLE . '
			WHERE ' . $this->db->sql_in_set('revision_id', $revision_id);
		$result = $this->db->sql_query($sql);
		$post_ids = array();
		while($row = $this->db->sql_fetchrow($result))
		{
			// Determine the number of revisions being deleted from the post
			if (!isset($post_ids[$row['post_id']]))
			{
				$post_ids[$row['post_id']] = 1;
			}
			else
			{
				$post_ids[$row['post_id']] += 1;
			}
		}
		$this->db->sql_freeresult($result);

		$sql = 'DELETE FROM ' . POST_REVISIONS_TABLE . '
			WHERE ' . $this->db->sql_in_set('revision_id', $revision_id);
		$return = (bool) $this->db->sql_query($sql);

		if ($return === true)
		{
			foreach ($post_ids as $post_id => $deleted_revisions_count)
			{
				$sql = 'UPDATE ' . POSTS_TABLE . '
					SET post_edit_count = post_edit_count - ' . (int) $deleted_revisions_count . '
					WHERE post_id = ' . (int) $post_id;
				$result = $this->db->sql_query($sql);
			}
		}

		return $return;
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
		return (bool) $this->db->sql_query($sql);
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
		return (bool) $this->db->sql_query($sql);
	}

	/**
	* Display the page on which to restore a post to a given revision
	*
	* This controller method is accessed directly from the path:
	* /post/{id}/restore/{to}
	*
	* @param $id
	*/
	public function restore($id, $to)
	{
		$post = new phpbb_revisions_post($id, $this->db, $this->config, $this->auth);
		$post_data = $post->get_post_data();
		$revisions = $post->get_revisions();

		if (!$this->get_restore_permission($post_data))
		{
			return $this->helper->error($this->user->lang('ERROR_AUTH_RESTORE'), 401);
		}

		if ($this->request->is_set_post('confirm'))
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
			else if ($post_data['post_edit_locked'] && !$this->auth->acl_get('m_revisions', $post_data['forum_id']))
			{
				$error = 'ERROR_POST_EDIT_LOCKED';
				// 401 is unauthorized
				$code = 401;
			}

			if ($error)
			{
				$this->send_ajax_response(array(
					'success' => false,
					'message' => $this->user->lang($error),
				));

				return $this->helper->error($this->user->lang($error), $code);
			}

			$restore_result = $post->restore($to);

			if ($restore_result !== phpbb_revisions_post::REVISION_RESTORE_SUCCESS)
			{
				switch ($restore_result)
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
					'message' => $this->user->lang($error),
				));

				return $this->helper->error($this->user->lang($error), $code);
			}

			// Because we've changed things up, we need to update our arrays
			$post_data = $post->get_post_data(true);
			$revisions = $post->get_revisions(true);

			$this->template->assign_vars(array(
				'L_REVISIONS_ACTION_SUCCESS'	=> $this->user->lang('POST_RESTORED_SUCCESS'),
			));

			if ($this->request->is_ajax())
			{
				$this->send_ajax_response(array(
					'success' => true,
					'message' => $this->user->lang('POST_RESTORED_SUCCESS'),
				));
			}

			return $this->compare($id);
		}
		else
		{
			add_form_key('restore');

			$this->template->assign_vars(array(
				'U_ACTION'			=> $this->url("post/$id/restore/$to"),
				'S_HIDDEN_FIELDS'	=> build_hidden_fields(array(
					'id'	=> $id,
					'to'	=> $to,
				)),
			));

			// Compare current post to the revision to which we are going to
			// restore it
			$comparison = new phpbb_revisions_comparison($post->get_current_revision(), $revisions[$to]);
			$this->template->assign_vars(array(
				'POST_USERNAME'		=> get_username_string('full', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
				'U_PROFILE'			=> get_username_string('profile', $post_data['poster_id'], $post_data['username'], $post_data['user_colour'], $post_data['post_username']),
				
				'RANK_TITLE'		=> $post_data['rank_title'],
				'RANK_IMG'			=> $post_data['rank_image'],

				'AVATAR'			=> get_user_avatar($post_data['user_avatar'], $post_data['user_avatar_type'], $post_data['user_avatar_width'], $post_data['user_avatar_height']),

				'POST_DATE'			=> $this->user->format_date($post_data['post_time']),
				'POST_SUBJECT'		=> $comparison->get_subject_diff_rendered(),
				'MESSAGE'			=> $comparison->get_text_diff_rendered(),
				'SIGNATURE'			=> ($post_data['enable_sig']) ? $post_data['user_sig_parsed'] : '',

				'POSTER_JOINED'		=> $this->user->format_date($post_data['user_regdate']),
				'POSTER_POSTS'		=> $post_data['user_posts'],
				'POSTER_LOCATION'	=> $post_data['user_from'],

				'POST_IMG'			=> $this->user->img('icon_post_target', 'POST'),

				'POST_ID'			=> $post_data['post_id'],
				'POSTER_ID'			=> $post_data['poster_id'],

				'U_VIEW_REVISIONS'	=> $this->url("post/{$post_data['post_id']}/revisions"),
				'U_VIEW_POST'		=> append_sid("{$this->phpbb_root_path}viewtopic.{$this->php_ext}", array('f' => $post_data['forum_id'], 't' => $post_data['topic_id'], 'p' => $post_data['post_id'])) . '#p' . $post_data['post_id'],
			));

			return $this->helper->render('revisions_restore_body.html', $this->user->lang('REVISIONS_RESTORE_TITLE'));
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
		// If the post data does not contain the information we need, we
		// return false
		if (empty($post_data) || !isset($post_data['post_wiki']) || !isset($post_data['post_edit_locked']) || !isset($post_data['forum_id']))
		{
			return false;
		}

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
		// If the post data does not contain the information we need, we
		// return false
		if (empty($post_data) || !isset($post_data['post_wiki']) || !isset($post_data['forum_id']))
		{
			return false;
		}

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
