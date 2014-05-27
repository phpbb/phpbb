<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_log_add_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/delete_log.xml');
	}

	public function test_log_delete()
	{
		global $phpbb_root_path, $phpEx, $db, $phpbb_dispatcher, $auth;

		$db = $this->new_dbal();
		$cache = new phpbb_mock_cache;
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user = $this->getMock('\phpbb\user');
		$user->data['user_id'] = 1;
		$auth = $this->getMock('\phpbb\auth\auth');

		$log = new \phpbb\log\log($db, $user, $auth, $phpbb_dispatcher, $phpbb_root_path, 'adm/', $phpEx, LOG_TABLE);

		// Delete all admin logs
		$this->assertCount(2, $log->get_logs('admin'));
		$log->delete('admin');
		// One entry is added to the admin log when the logs are purged
		$this->assertCount(1, $log->get_logs('admin'));

		// Delete with keyword
		$this->assertCount(1, $log->get_logs('mod', false, 0, 0, 0, 0, 0, 0, 'l.log_time DESC', 'guest'));
		$log->delete('mod', array('keywords' => 'guest'));
		$this->assertEmpty($log->get_logs('mod', false, 0, 0, 0, 0, 0, 0, 'l.log_time DESC', 'guest'));

		// Delete with simples conditions
		$this->assertCount(3, $log->get_logs('mod', false, 0, 0, 12, 0, 1, 0, 'l.log_time DESC'));
		$log->delete('mod', array('forum_id' => 12, 'user_id' => 1));
		$this->assertEmpty($log->get_logs('mod', false, 0, 0, 12, 0, 1, 0, 'l.log_time DESC'));

		// Delete with IN condition
		$this->assertCount(2, $log->get_logs('mod', false, 0, 0, array(13, 14), 0, 0, 0, 'l.log_time DESC'));
		$log->delete('mod', array('forum_id' => array(14, 13)));
		$this->assertEmpty($log->get_logs('mod', false, 0, 0, array(13, 14), 0, 0, 0, 'l.log_time DESC'));

		// Delete with a custom condition (ie: WHERE x >= 10)
		$this->assertCount(3, $log->get_logs('critical', false, 0, 0, 0, 0, 0, 0, 'l.log_time DESC'));
		$log->delete('critical', array('user_id' => array('>', 1)));
		$this->assertCount(1, $log->get_logs('critical', false, 0, 0, 0, 0, 0, 0, 'l.log_time DESC'));
	}
}
