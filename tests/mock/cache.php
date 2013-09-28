<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

	public function destroy($var_name, $table = '')
	{
		if ($table)
		{
			throw new Exception('Destroying tables is not implemented yet');
		}

		unset($this->data[$var_name]);
	}

	/**
	* Obtain active bots
	*/
	public function obtain_bots()
	{
		return $this->data['_bots'];
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
			unset($cache_data['mssqlodbc_version']);
			unset($cache_data['mssql_version']);
			unset($cache_data['mysql_version']);
			unset($cache_data['mysqli_version']);
			unset($cache_data['pgsql_version']);
			unset($cache_data['sqlite_version']);
		}

		$test->assertEquals($data, $cache_data);
	}
}

