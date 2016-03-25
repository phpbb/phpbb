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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_log_delete_test extends phpbb_database_test_case
{
	protected $log;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/delete_log.xml');
	}

	protected function setUp()
	{
		global $phpbb_root_path, $phpEx, $db, $phpbb_dispatcher, $auth;

		$db = $this->new_dbal();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->data['user_id'] = 1;
		$auth = $this->getMock('\phpbb\auth\auth');

		$this->log = new \phpbb\log\log($db, $user, $auth, $phpbb_dispatcher, $phpbb_root_path, 'adm/', $phpEx, LOG_TABLE);

		parent::setUp();
	}

	public function log_delete_data()
	{
		return array(
			array(
				array(1, 2),
				array(16),
				array(),
				'admin',
				false,
				0,
				0,
				0,
				0,
				0,
				0,
				'l.log_id ASC',
				'',
			),
			array(
				array(11),
				array(),
				array('keywords' => 'guest'),
				'mod',
				false,
				0,
				0,
				0,
				0,
				0,
				0,
				'l.log_id ASC',
				'guest',
			),
			array(
				array(4, 5, 7),
				array(),
				array('forum_id' => 12, 'user_id' => 1),
				'mod',
				false,
				0,
				0,
				12,
				0,
				1,
				0,
				'l.log_id ASC',
				'',
			),
			array(
				array(12, 13),
				array(),
				array('forum_id' => array('IN' => array(14, 13))),
				'mod',
				false,
				0,
				0,
				array(13, 14),
				0,
				0,
				0,
				'l.log_id ASC',
				'',
			),
			array(
				array(3, 14, 15),
				array(3),
				array('user_id' => array('>', 1)),
				'critical',
				false,
				0,
				0,
				0,
				0,
				0,
				0,
				'l.log_id ASC',
				'',
			),
			array(
				array(3, 14, 15),
				array(),
				array('keywords' => ''),
				'critical',
				false,
				0,
				0,
				0,
				0,
				0,
				0,
				'l.log_id ASC',
				'',
			),
		);
	}

	/**
	* @dataProvider log_delete_data
	*/
	public function test_log_delete($expected_before, $expected_after, $delete_conditions, $mode, $count_logs, $limit, $offset, $forum_id, $topic_id, $user_id, $log_time, $sort_by, $keywords)
	{
		$this->assertSame($expected_before, $this->get_ids($this->log->get_logs($mode, $count_logs, $limit, $offset, $forum_id, $topic_id, $user_id, $log_time, $sort_by, $keywords)), 'before');
		$this->log->delete($mode, $delete_conditions);
		$this->assertSame($expected_after, $this->get_ids($this->log->get_logs($mode, $count_logs, $limit, $offset, $forum_id, $topic_id, $user_id, $log_time, $sort_by, $keywords)), 'after');
	}

	public function get_ids($logs)
	{
		$ids = array();
		foreach ($logs as $log_entry)
		{
			$ids[] = (int) $log_entry['id'];
		}
		return $ids;
	}
}
