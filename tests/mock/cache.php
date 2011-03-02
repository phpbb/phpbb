<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_mock_cache
{
	public function __construct($data = array())
	{
		$this->data = $data;

		if (!isset($this->data['_bots']))
		{
			$this->data['_bots'] = array();
		}
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

	/**
	* Obtain active bots
	*/
	public function obtain_bots()
	{
		return $this->data['_bots'];
	}

	public function set_bots($bots)
	{
		$this->data['_bots'] = $bots;
	}

	public function checkVar(PHPUnit_Framework_Assert $test, $var_name, $data)
	{
		$test->assertTrue(isset($this->data[$var_name]));
		$test->assertEquals($data, $this->data[$var_name]);
	}

	public function check(PHPUnit_Framework_Assert $test, $data, $ignore_db_info = true)
	{
		$cache_data = $this->data;

		if ($ignore_db_info)
		{
			unset($cache_data['mysqli_version']);
		}

		$test->assertEquals($data, $cache_data);
	}
}

