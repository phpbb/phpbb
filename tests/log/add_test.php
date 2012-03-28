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
		$this->assertTrue($log->is_enabled(), 'Initialise failed');

		$log->disable();
		$this->assertFalse($log->is_enabled(), 'Disable all failed');

		$log->enable();
		$this->assertTrue($log->is_enabled(), 'Enable all failed');

		$log->disable('admin');
		$this->assertFalse($log->is_enabled('admin'), 'Disable admin failed');
		$this->assertTrue($log->is_enabled('user'), 'User should be enabled, is disabled');
		$this->assertTrue($log->is_enabled(), 'Disable admin disabled all');

		$log->enable('admin');
		$this->assertTrue($log->is_enabled('admin'), 'Enable admin failed');
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

		// Disable logging for all types
		$log->disable();
		$this->assertFalse($log->add($mode, $user_id, $log_ip, $log_operation, $log_time), 'Disable for all types failed');
		$log->enable();

		// Disable logging for same type
		$log->disable('critical');
		$this->assertFalse($log->add($mode, $user_id, $log_ip, $log_operation, $log_time), 'Disable for same type failed');
		$log->enable();

		// Disable logging for different type
		$log->disable('admin');
		$this->assertEquals(2, $log->add($mode, $user_id, $log_ip, $log_operation, $log_time), 'Disable for different types failed');
		$log->enable();

		// Invalid mode specified
		$this->assertFalse($log->add('mode_does_not_exist', $user_id, $log_ip, $log_operation, $log_time));
	}
}
