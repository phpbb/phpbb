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

namespace phpbb\report;

use phpbb\report\exception\invalid_report_exception;
use phpbb\report\exception\empty_report_exception;
use phpbb\report\exception\already_reported_exception;
use phpbb\report\exception\entity_not_found_exception;
use phpbb\report\exception\report_permission_denied_exception;

class report_handler_post extends report_handler
{
	/**
	 * @var array
	 */
	protected $forum_data;

	/**
	 * {@inheritdoc}
	 * @throws \phpbb\report\exception\report_permission_denied_exception when the user does not have permission to report the post
	 */
	public function add_report($id, $reason_id, $report_text, $user_notify)
	{
		// Cast the input variables
		$id				= (int) $id;
		$reason_id		= (int) $reason_id;
		$report_text	= (string) $report_text;
		$user_notify	= (int) $user_notify;

		$this->validate_report_request($id);

		$sql = 'SELECT *
			FROM ' . REPORTS_REASONS_TABLE . "
			WHERE reason_id = $reason_id";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row || (empty($report_text) && strtolower($row['reason_title']) === 'other'))
		{
			throw new empty_report_exception();
		}

		$report_data = array(
			'reason_id'							=> $reason_id,
			'post_id'							=> $id,
			'pm_id'								=> 0,
			'user_notify'						=> $user_notify,
			'report_text'						=> $report_text,
			'reported_post_text'				=> $this->report_data['post_text'],
			'reported_post_uid'					=> $this->report_data['bbcode_uid'],
			'reported_post_bitfield'			=> $this->report_data['bbcode_bitfield'],
			'reported_post_enable_bbcode'		=> $this->report_data['enable_bbcode'],
			'reported_post_enable_smilies'		=> $this->report_data['enable_smilies'],
			'reported_post_enable_magic_url'	=> $this->report_data['enable_magic_url'],
		);

		$this->create_report($report_data);

		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET post_reported = 1
			WHERE post_id = ' . $id;
		$this->db->sql_query($sql);

		if (!$this->report_data['topic_reported'])
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_reported = 1
				WHERE topic_id = ' . $this->report_data['topic_id'] . '
					OR topic_moved_id = ' . $this->report_data['topic_id'];
			$this->db->sql_query($sql);
		}

		$this->notifications->add_notifications('notification.type.report_post', array_merge($this->report_data, $row, $this->forum_data, array(
			'report_text'	=> $report_text,
		)));
	}

	/**
	 * {@inheritdoc}
	 * @throws \phpbb\report\exception\report_permission_denied_exception when the user does not have permission to report the post
	 */
	public function validate_report_request($id)
	{
		$id = (int) $id;

		// Check if id is valid
		if ($id <= 0)
		{
			throw new entity_not_found_exception('NO_POST_SELECTED');
		}

		// Grab all relevant data
		$sql = 'SELECT t.*, p.*
			FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . " t
			WHERE p.post_id = $id
				AND p.topic_id = t.topic_id";
		$result = $this->db->sql_query($sql);
		$report_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$report_data)
		{
			throw new entity_not_found_exception('POST_NOT_EXIST');
		}

		$forum_id = (int) $report_data['forum_id'];

		$sql = 'SELECT *
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . $forum_id;
		$result = $this->db->sql_query($sql);
		$forum_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$forum_data)
		{
			throw new invalid_report_exception('FORUM_NOT_EXIST');
		}

		$acl_check_ary = array(
			'f_list' => 'POST_NOT_EXIST',
			'f_read' => 'USER_CANNOT_READ',
			'f_report' => 'USER_CANNOT_REPORT'
		);

		/**
		 * This event allows you to do extra auth checks and verify if the user
		 * has the required permissions
		 *
		 * @event core.report_post_auth
		 * @var	array	forum_data		All data available from the forums table on this post's forum
		 * @var	array	report_data		All data available from the topics and the posts tables on this post (and its topic)
		 * @var	array	acl_check_ary	An array with the ACL to be tested. The evaluation is made in the same order as the array is sorted
		 *								The key is the ACL name and the value is the language key for the error message.
		 * @since 3.1.3-RC1
		 */
		$vars = array(
			'forum_data',
			'report_data',
			'acl_check_ary',
		);
		extract($this->dispatcher->trigger_event('core.report_post_auth', compact($vars)));

		$this->auth->acl($this->user->data);

		foreach ($acl_check_ary as $acl => $error)
		{
			if (!$this->auth->acl_get($acl, $forum_id))
			{
				throw new report_permission_denied_exception($error);
			}
		}
		unset($acl_check_ary);

		if ($report_data['post_reported'])
		{
			throw new already_reported_exception();
		}

		$this->report_data	= $report_data;
		$this->forum_data	= $forum_data;
	}
}
