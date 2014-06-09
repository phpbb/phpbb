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

class phpbb_mock_cache implements \phpbb\cache\driver\driver_interface
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

	/**
	 * Obtain list of word censors. We don't need to parse them here,
	 * that is tested elsewhere.
	 */
	public function obtain_word_list()
	{
		return array(
			'match'		=> array(
				'#(?<![\\p{Nd}\\p{L}_-])([\\p{Nd}\\p{L}_-]*?badword1[\\p{Nd}\\p{L}_-]*?)(?![\\p{Nd}\\p{L}_-])#iu',
				'#(?<![\\p{Nd}\\p{L}_-])([\\p{Nd}\\p{L}_-]*?badword2)(?![\\p{Nd}\\p{L}_-])#iu',
				'#(?<![\\p{Nd}\\p{L}_-])(badword3[\\p{Nd}\\p{L}_-]*?)(?![\\p{Nd}\\p{L}_-])#iu',
				'#(?<![\\p{Nd}\\p{L}_-])(badword4)(?![\\p{Nd}\\p{L}_-])#iu',
			),
			'replace'	=> array(
				'replacement1',
				'replacement2',
				'replacement3',
				'replacement4',
			),
		);
	}

	/**
	* Obtain disallowed usernames. Input data via standard put method.
	*/
	public function obtain_disallowed_usernames()
	{
		if (($usernames = $this->get('_disallowed_usernames')) !== false)
		{
			return $usernames;
		}
		else
		{
			return array();
		}
	}

	public function checkVar(PHPUnit_Framework_Assert $test, $var_name, $data)
	{
		$test->assertTrue(isset($this->data[$var_name]));
		$test->assertEquals($data, $this->data[$var_name]);
	}

	public function checkAssociativeVar(PHPUnit_Framework_Assert $test, $var_name, $data, $sort = true)
	{
		$test->assertTrue(isset($this->data[$var_name]));

		if ($sort)
		{
			foreach ($this->data[$var_name] as &$content)
			{
				sort($content);
			}
		}

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

	/**
	* {@inheritDoc}
	*/
	public function sql_save(\phpbb\db\driver\driver_interface $db, $query, $query_result, $ttl)
	{
		return $query_result;
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
