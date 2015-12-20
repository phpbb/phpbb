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
		$config = $this->config = new \phpbb\config\config(array('rand_seed' => '', 'rand_seed_last_update' => '0'));
		$this->lock = new \phpbb\lock\db('test_lock', $this->config, $this->db);
	}

	public function test_new_lock()
	{
		$this->assertFalse($this->lock->owns_lock());

		$this->assertTrue($this->lock->acquire());
		$this->assertTrue($this->lock->owns_lock());
		$this->assertTrue(isset($this->config['test_lock']), 'Lock was created');

		$lock2 = new \phpbb\lock\db('test_lock', $this->config, $this->db);
		$this->assertFalse($lock2->acquire());
		$this->assertFalse($lock2->owns_lock());

		$this->lock->release();
		$this->assertFalse($this->lock->owns_lock());
		$this->assertEquals('0', $this->config['test_lock'], 'Lock was released');
	}

	public function test_expire_lock()
	{
		$lock = new \phpbb\lock\db('foo_lock', $this->config, $this->db);
		$this->assertTrue($lock->acquire());
	}

	public function test_double_lock()
	{
		$this->assertFalse($this->lock->owns_lock());

		$this->assertTrue($this->lock->acquire());
		$this->assertTrue($this->lock->owns_lock());
		$this->assertTrue(isset($this->config['test_lock']), 'Lock was created');

		$value = $this->config['test_lock'];

		$this->assertFalse($this->lock->acquire());
		$this->assertTrue($this->lock->owns_lock());
		$this->assertEquals($value, $this->config['test_lock'], 'Second lock failed');

		$this->lock->release();
		$this->assertFalse($this->lock->owns_lock());
		$this->assertEquals('0', $this->config['test_lock'], 'Lock was released');
	}

	public function test_double_unlock()
	{
		$this->assertTrue($this->lock->acquire());
		$this->assertTrue($this->lock->owns_lock());
		$this->assertFalse(empty($this->config['test_lock']), 'First lock is acquired');

		$this->lock->release();
		$this->assertFalse($this->lock->owns_lock());
		$this->assertEquals('0', $this->config['test_lock'], 'First lock is released');

		$lock2 = new \phpbb\lock\db('test_lock', $this->config, $this->db);
		$this->assertTrue($lock2->acquire());
		$this->assertTrue($lock2->owns_lock());
		$this->assertFalse(empty($this->config['test_lock']), 'Second lock is acquired');

		$this->lock->release();
		$this->assertTrue($lock2->owns_lock());
		$this->assertFalse(empty($this->config['test_lock']), 'Double release of first lock is ignored');

		$lock2->release();
		$this->assertEquals('0', $this->config['test_lock'], 'Second lock is released');
	}
}
