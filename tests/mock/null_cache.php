<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_mock_null_cache
{
	public function __construct()
	{
	}

	public function get($var_name)
	{
		return false;
	}

	public function put($var_name, $var, $ttl = 0)
	{
	}

	public function destroy($var_name, $table = '')
	{
	}

	public function obtain_bots()
	{
		return array();
	}

	public function obtain_word_list()
	{
		return array();
	}

	public function set_bots($bots)
	{
	}
}
