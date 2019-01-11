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

namespace phpbb\forum;

use phpbb\forum\exception\forum_password_needed_exception;
use phpbb\forum\exception\login_required_exception;
use phpbb\forum\exception\permission_denied_exception;

class visibility_helper
{
	protected $auth;

	protected $content_visibility;

	protected $db;

	protected $dispatcher;

	protected $password_manager;

	protected $user;

	protected $forum_access_table;

	protected $sessions_table;

	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\content_visibility $content_visibility,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\passwords\manager $password_manager,
		\phpbb\user $user,
		string $forum_access_table,
		string $sessions_table)
	{
		$this->auth = $auth;
		$this->content_visibility = $content_visibility;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->password_manager = $password_manager;
		$this->user = $user;

		$this->forum_access_table = $forum_access_table;
		$this->sessions_table = $sessions_table;
	}

	/**
	 * Checks whether or not the user has permissions to read the forum.
	 *
	 * @param array $forum_data	Forum data array.
	 * @param array $parameters	Additional parameter array.
	 */
	public function check(array $forum_data, array $parameters)
	{
		$forum_id = (int) $forum_data['forum_id'];

		if (!$this->auth->acl_gets('f_list', 'f_list_topics', 'f_read', $forum_id) ||
			($forum_data['forum_type'] == FORUM_LINK) && $forum_data['forum_link'] && $this->auth->acl_get('f_read', $forum_id))
		{
			if ($this->user->data['user_id'] != ANONYMOUS)
			{
				throw new permission_denied_exception('SORRY_AUTH_READ');
			}
			else
			{
				throw new login_required_exception('LOGIN_VIEWFORUM');
			}
		}

		if ($forum_data['forum_password'])
		{
			$forum_password = (isset($parameters['forum_password'])) ? $parameters['forum_password'] : false;

			if (!$this->check_forum_password($forum_data, $forum_password))
			{
				throw new forum_password_needed_exception();
			}
		}
	}

	/**
	 * Checks the forum password.
	 *
	 * @param array		$forum_data		Forum data.
	 * @param string	$forum_password	The password entered by the user.
	 *
	 * @return bool True if the entered password is correct or the user is already authenticated, false otherwise.
	 */
	public function check_forum_password($forum_data, $forum_password)
	{
		$sql = 'SELECT forum_id
		FROM ' . $this->forum_access_table . '
		WHERE forum_id = ' . (int) $forum_data['forum_id'] . '
			AND user_id = ' . (int) $this->user->data['user_id'] . '
			AND session_id = \'' . $this->db->sql_escape($this->user->session_id) . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row)
		{
			return true;
		}

		if (!$forum_password)
		{
			return false;
		}

		// Remove expired authorised sessions
		$sql = 'SELECT f.session_id
			FROM ' . $this->forum_access_table . ' f
			LEFT JOIN ' . $this->sessions_table . ' s ON (f.session_id = s.session_id)
			WHERE s.session_id IS NULL';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);

		if ($row)
		{
			$sql_in = array();
			do
			{
				$sql_in[] = (string) $row['session_id'];
			}
			while ($row = $this->db->sql_fetchrow($result));

			// Remove expired sessions
			$sql = 'DELETE FROM ' . $this->forum_access_table . '
				WHERE ' . $this->db->sql_in_set('session_id', $sql_in);
			$this->db->sql_query($sql);
		}

		$this->db->sql_freeresult($result);

		if ($this->password_manager->check($forum_password, $forum_data['forum_password']))
		{
			$sql_ary = array(
				'forum_id'		=> (int) $forum_data['forum_id'],
				'user_id'		=> (int) $this->user->data['user_id'],
				'session_id'	=> (string) $this->user->session_id,
			);

			$sql = 'INSERT INTO ' . $this->forum_access_table . ' '
				. $this->db->sql_build_array('INSERT', $sql_ary);
			$this->db->sql_query($sql);

			return true;
		}

		return false;
	}

	/**
	 * Filter out parent forums that the user cannot read.
	 *
	 * @param array $forum_parents Array of parent forums.
	 *
	 * @return array Array of parent forums that the current user can read.
	 */
	public function filter_forum_parents(array $forum_parents)
	{
		$filtered_parents = [];

		foreach ($forum_parents as $forum_id => $forum_data)
		{
			if (!$this->auth->acl_get('f_list', $forum_id))
			{
				continue;
			}

			$filtered_parents[$forum_id] = $forum_data;
		}

		return $filtered_parents;
	}

	/**
	 * Filters out subforums that the user has no permissions for.
	 *
	 * @param array $subforums Array of subforums.
	 *
	 * @return array Filtered subforums.
	 */
	public function filter_subforums(array $subforums)
	{
		foreach ($subforums as &$row)
		{
			$forum_id = (int) $row['forum_id'];

			// Category with no members
			if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
			{
				continue;
			}

			// Skip branch
			if (isset($right_id))
			{
				if ($row['left_id'] < $right_id)
				{
					continue;
				}
				unset($right_id);
			}

			if (!$this->auth->acl_get('f_list', $forum_id))
			{
				// If the user does not have permissions to list this forum, skip everything until next branch.
				$right_id = $row['right_id'];
				continue;
			}

			// Lets check whether there are unapproved topics/posts, so we can display an information to moderators
			$row['forum_id_unapproved_topics'] = ($this->auth->acl_get('m_approve', $forum_id) && $row['forum_topics_unapproved']) ? $forum_id : 0;
			$row['forum_id_unapproved_posts'] = ($this->auth->acl_get('m_approve', $forum_id) && $row['forum_posts_unapproved']) ? $forum_id : 0;
			$row['forum_posts'] = $this->content_visibility->get_count('forum_posts', $row, $forum_id);
			$row['forum_topics'] = $this->content_visibility->get_count('forum_topics', $row, $forum_id);
			$row['user_may_read_forum'] = $this->auth->acl_get('f_read', $forum_id);
			$row['may_display_last_post'] = $this->auth->acl_gets('f_read', 'f_list_topics', $forum_id);
		}

		unset($row);

		return $subforums;
	}
}
