<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_cache_null_driver_test extends phpbb_database_test_case
{
	protected $driver;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		$this->driver = new \phpbb\cache\driver\null;
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
		// does nothing
		$this->driver->purge();
	}

	public function test_destroy()
	{
		// does nothing
		$this->driver->destroy('foo');
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
