<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_mock_cache implements phpbb_cache_driver_interface
{
	protected $data;

	public function __construct($data = array())
	{
		$this->data = $data;
	}

	public function get($var_name)
	{
		if (isset($this->data[$var_name]))
		{
			return $this->data[$var_name];
		}

		return false;
	}

	public function put($var_name, $var, $ttl = 0)
	{
		$this->data[$var_name] = $var;
	}

	public function checkVar(PHPUnit_Framework_Assert $test, $var_name, $data)
	{
		$test->assertTrue(isset($this->data[$var_name]));
		$test->assertEquals($data, $this->data[$var_name]);
	}

	public function checkVarUnset(PHPUnit_Framework_Assert $test, $var_name)
	{
		$test->assertFalse(isset($this->data[$var_name]));
	}

	public function check(PHPUnit_Framework_Assert $test, $data, $ignore_db_info = true)
	{
		$cache_data = $this->data;

		if ($ignore_db_info)
		{
			unset($cache_data['mssqlodbc_version']);
			unset($cache_data['mssql_version']);
			unset($cache_data['mysql_version']);
			unset($cache_data['mysqli_version']);
			unset($cache_data['pgsql_version']);
			unset($cache_data['sqlite_version']);
		}

		$test->assertEquals($data, $cache_data);
	}

	function load()
	{
	}
	function unload()
	{
	}
	function save()
	{
	}
	function tidy()
	{
	}
	function purge()
	{
	}
	function destroy($var_name, $table = '')
	{
		unset($this->data[$var_name]);
	}
	public function _exists($var_name)
	{
	}
	public function sql_load($query)
	{
	}
	public function sql_save($query, &$query_result, $ttl)
	{
	}
	public function sql_exists($query_id)
	{
	}
	public function sql_fetchrow($query_id)
	{
	}
	public function sql_fetchfield($query_id, $field)
	{
	}
	public function sql_rowseek($rownum, $query_id)
	{
	}
	public function sql_freeresult($query_id)
	{
	}

	public function obtain_bots()
	{
		return isset($this->data['_bots']) ? $this->data['_bots'] : array();
	}
}
