<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_log_add_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/empty_log.xml');
	}

	public function test_log_enabled()
	{
		$log = new phpbb_log(LOG_TABLE);
		$this->assertTrue($log->is_enabled());

		$log->disable();
		$this->assertFalse($log->is_enabled());

		$log->enable();
		$this->assertTrue($log->is_enabled());
	}

	public function test_log_add()
	{
		global $db;

		$db = $this->new_dbal();

		$mode = 'critical';
		$user_id = ANONYMOUS;
		$log_ip = 'user_ip';
		$log_time = time();
		$log_operation = 'LOG_OPERATION';
		$additional_data = array();

		// Add an entry successful
		$log = new phpbb_log(LOG_TABLE);
		$this->assertEquals(1, $log->add($mode, $user_id, $log_ip, $log_operation, $log_time));

		// Disable logging
		$log->disable();
		$this->assertFalse($log->add($mode, $user_id, $log_ip, $log_operation, $log_time));
		$log->enable();

		// Invalid mode specified
		$this->assertFalse($log->add('mode_does_not_exist', $user_id, $log_ip, $log_operation, $log_time));
	}
}
