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

require_once __DIR__ . '/base.php';

class phpbb_notification_report_post_test extends phpbb_tests_notification_base
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/notification.xml');
	}

	public static function report_id_data()
	{
		return array(
			'new report' => array(456, false, 456),
			'another report on the same post' => array(789, false, 789),
			'legacy persisted notification' => array(456, true, 0),
			'caller without report id' => array(null, false, 0),
			'zero report id' => array(0, false, 0),
			'negative report id' => array(-1, false, 0),
		);
	}

	/**
	* @dataProvider report_id_data
	*/
	public function test_persisted_report_urls($report_id, $legacy, $expected_report_id)
	{
		global $phpbb_root_path, $phpEx, $_SID, $phpbb_dispatcher;

		$phpbb_dispatcher = $this->phpbb_dispatcher;

		$_SID = '';
		$this->config['force_server_vars'] = true;
		$this->config['server_protocol'] = 'http://';
		$this->config['server_name'] = 'board.example';
		$this->config['server_port'] = 80;
		$this->user->data['user_options'] = 0;
		$this->config['script_path'] = '';

		$data = array(
			'post_id' => 123,
			'topic_id' => 12,
			'poster_id' => 2,
			'post_username' => '',
			'post_subject' => 'Reported post',
			'topic_title' => 'Reported topic',
			'post_time' => 1234567890,
			'forum_id' => 2,
			'forum_name' => 'Test forum',
			'reason_title' => 'OTHER',
			'reason_description' => 'Other reason',
			'report_text' => 'Report details',
		);
		if ($report_id !== null)
		{
			$data['report_id'] = $report_id;
		}

		$notification = $this->notifications->get_item_type_class('notification.type.report_post');
		$notification->user_id = 2;
		$notification->create_insert_array($data);
		$row = $notification->get_insert_array();

		if ($legacy)
		{
			$stored_data = unserialize($row['notification_data']);
			unset($stored_data['report_id']);
			$row['notification_data'] = serialize($stored_data);
		}

		$this->db->sql_query('INSERT INTO phpbb_notifications ' . $this->db->sql_build_array('INSERT', $row));
		$notification_id = (int) $this->db->sql_nextid();
		$result = $this->db->sql_query('SELECT * FROM phpbb_notifications WHERE notification_id = ' . $notification_id);
		$stored_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$reloaded = $this->notifications->get_item_type_class('notification.type.report_post');
		$reloaded->set_initial_data($stored_row);
		$this->assertEquals(123, $reloaded->item_id);
		$this->assertEquals(12, $reloaded->item_parent_id);

		$report_parameter = $expected_report_id ? '&r=' . $expected_report_id : '';
		$parameters = 'p=123&i=reports&mode=report_details' . $report_parameter . '#reports';
		$this->assertSame($phpbb_root_path . 'mcp.' . $phpEx . '?' . str_replace('&', '&amp;', $parameters), $reloaded->get_url());
		$redirect_url = $expected_report_id ? $reloaded->get_url() : $phpbb_root_path . 'viewtopic.' . $phpEx . '?p=123#p123';
		$this->assertSame($redirect_url, $reloaded->get_redirect_url());
		$email = $reloaded->get_email_template_variables();
		$this->assertSame('http://board.example/mcp.' . $phpEx . '?' . $parameters, $email['U_VIEW_REPORT']);
		$this->assertSame('http://board.example/viewtopic.' . $phpEx . '?p=123#p123', $email['U_VIEW_POST']);
	}
}
