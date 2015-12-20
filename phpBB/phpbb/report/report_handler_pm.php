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

use phpbb\report\exception\empty_report_exception;
use phpbb\report\exception\already_reported_exception;
use phpbb\report\exception\pm_reporting_disabled_exception;
use phpbb\report\exception\entity_not_found_exception;

class report_handler_pm extends report_handler
{
	/**
	 * {@inheritdoc}
	 * @throws \phpbb\report\exception\pm_reporting_disabled_exception when PM reporting is disabled on the board
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
			'post_id'							=> 0,
			'pm_id'								=> $id,
			'user_notify'						=> $user_notify,
			'report_text'						=> $report_text,
			'reported_post_text'				=> $this->report_data['message_text'],
			'reported_post_uid'					=> $this->report_data['bbcode_bitfield'],
			'reported_post_bitfield'			=> $this->report_data['bbcode_uid'],
			'reported_post_enable_bbcode'		=> $this->report_data['enable_bbcode'],
			'reported_post_enable_smilies'		=> $this->report_data['enable_smilies'],
			'reported_post_enable_magic_url'	=> $this->report_data['enable_magic_url'],
		);

		$report_id = $this->create_report($report_data);

		$sql = 'UPDATE ' . PRIVMSGS_TABLE . '
			SET message_reported = 1
			WHERE msg_id = ' . $id;
		$this->db->sql_query($sql);

		$sql_ary = array(
			'msg_id'		=> $id,
			'user_id'		=> ANONYMOUS,
			'author_id'		=> (int) $this->report_data['author_id'],
			'pm_deleted'	=> 0,
			'pm_new'		=> 0,
			'pm_unread'		=> 0,
			'pm_replied'	=> 0,
			'pm_marked'		=> 0,
			'pm_forwarded'	=> 0,
			'folder_id'		=> PRIVMSGS_INBOX,
		);

		$sql = 'INSERT INTO ' . PRIVMSGS_TO_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		$this->notifications->add_notifications('notification.type.report_pm', array_merge($this->report_data, $row, array(
			'report_text'	=> $report_text,
			'from_user_id'	=> $this->report_data['author_id'],
			'report_id'		=> $report_id,
		)));
	}

	/**
	 * {@inheritdoc}
	 * @throws \phpbb\report\exception\pm_reporting_disabled_exception when PM reporting is disabled on the board
	 */
	public function validate_report_request($id)
	{
		$id = (int) $id;

		// Check if reporting PMs is enabled
		if (!$this->config['allow_pm_report'])
		{
			throw new pm_reporting_disabled_exception();
		}
		else if ($id <= 0)
		{
			throw new entity_not_found_exception('NO_POST_SELECTED');
		}

		// Grab all relevant data
		$sql = 'SELECT p.*, pt.*
			FROM ' . PRIVMSGS_TABLE . ' p, ' . PRIVMSGS_TO_TABLE . " pt
			WHERE p.msg_id = $id
				AND p.msg_id = pt.msg_id
				AND (p.author_id = " . $this->user->data['user_id'] . "
					OR pt.user_id = " . $this->user->data['user_id'] . ")";
		$result = $this->db->sql_query($sql);
		$report_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// Check if message exists
		if (!$report_data)
		{
			$this->user->add_lang('ucp');
			throw new entity_not_found_exception('NO_MESSAGE');
		}

		// Check if message is already reported
		if ($report_data['message_reported'])
		{
			throw new already_reported_exception();
		}

		$this->report_data = $report_data;
	}
}
