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

class phpbb_cache_dummy_driver_test extends phpbb_database_test_case
{
	protected $driver;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		$this->driver = new \phpbb\cache\driver\dummy;
	}

	public function test_get_put()
	{
		$this->assertSame(false, $this->driver->get('key'));

		$this->driver->put('key', 'value');

		// null driver does not cache
		$this->assertSame(false, $this->driver->get('key'));
	}

	public function test_purge()
	{
		$this->assertNull($this->driver->purge());
	}

	public function test_destroy()
	{
		$this->assertNull($this->driver->destroy('foo'));
	}

	public function test_cache_sql()
	{
		global $db, $cache, $phpbb_root_path, $phpEx;
		$config = new phpbb\config\config(array());
		$db = $this->new_dbal();
		$cache = new \phpbb\cache\service($this->driver, $config, $db, $phpbb_root_path, $phpEx);

		$sql = "SELECT * FROM phpbb_config
			WHERE config_name = 'foo'";
		$result = $db->sql_query($sql, 300);
		$first_result = $db->sql_fetchrow($result);
		$expected = array('config_name' => 'foo', 'config_value' => '23', 'is_dynamic' => 0);
		$this->assertEquals($expected, $first_result);

		$sql = 'DELETE FROM phpbb_config';
		$result = $db->sql_query($sql);

		// As null cache driver does not actually cache,
		// this should return no results
		$sql = "SELECT * FROM phpbb_config
			WHERE config_name = 'foo'";
		$result = $db->sql_query($sql, 300);

		$this->assertSame(false, $db->sql_fetchrow($result));

		$db->sql_close();
	}
}
