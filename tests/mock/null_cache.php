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

	public function sql_exists($query_id)
	{
		return false;
	}
}
