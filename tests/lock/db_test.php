<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once __DIR__ . '/../../phpBB/includes/functions.php';

class phpbb_lock_db_test extends phpbb_database_test_case
{
	private $db;
	private $config;
	private $lock;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/config.xml');
	}

	public function setUp()
	{
		global $db, $config;

		$db = $this->db = $this->new_dbal();
		$config = $this->config = array('rand_seed' => '', 'rand_seed_last_update' => '0');
		$this->lock = new phpbb_lock_db('test_lock', $this->config, $this->db);
	}

	public function test_new_lock()
	{
		global $config;

		$this->assertTrue($this->lock->lock());
		$this->assertTrue(isset($config['test_lock']), 'Lock was created');

		$lock2 = new phpbb_lock_db('test_lock', $config, $this->db);
		$this->assertFalse($lock2->lock());

		$this->lock->unlock();
		$this->assertEquals('0', $config['test_lock'], 'Lock was released');
	}

	public function test_expire_lock()
	{
		$lock = new phpbb_lock_db('foo_lock', $this->config, $this->db);
		$this->assertTrue($lock->lock());
	}

	public function test_double_lock()
	{
		global $config;

		$this->assertTrue($this->lock->lock());
		$this->assertTrue(isset($config['test_lock']), 'Lock was created');

		$value = $config['test_lock'];

		$this->assertTrue($this->lock->lock());
		$this->assertEquals($value, $config['test_lock'], 'Second lock was ignored');

		$this->lock->unlock();
		$this->assertEquals('0', $config['test_lock'], 'Lock was released');
	}
}
