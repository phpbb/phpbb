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

namespace phpbb\cache\driver;

/**
* ACM dummy Caching
*/
class dummy extends \phpbb\cache\driver\base
{
	/**
	* Set cache path
	*/
	function __construct()
	{
	}

	/**
	* {@inheritDoc}
	*/
	function load()
	{
		return true;
	}

	/**
	* {@inheritDoc}
	*/
	function unload()
	{
	}

	/**
	* {@inheritDoc}
	*/
	function save()
	{
	}

	/**
	* {@inheritDoc}
	*/
	function tidy()
	{
		global $config;

		// This cache always has a tidy room.
		$config->set('cache_last_gc', time(), false);
	}

	/**
	* {@inheritDoc}
	*/
	function get($var_name)
	{
		return false;
	}

	/**
	* {@inheritDoc}
	*/
	function put($var_name, $var, $ttl = 0)
	{
	}

	/**
	* {@inheritDoc}
	*/
	function purge()
	{
	}

	/**
	* {@inheritDoc}
	*/
	function destroy($var_name, $table = '')
	{
	}

	/**
	* {@inheritDoc}
	*/
	function _exists($var_name)
	{
		return false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_load($query)
	{
		return false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_save(\phpbb\db\driver\driver_interface $db, $query, $query_result, $ttl)
	{
		return $query_result;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_exists($query_id)
	{
		return false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_fetchrow($query_id)
	{
		return false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_fetchfield($query_id, $field)
	{
		return false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_rowseek($rownum, $query_id)
	{
		return false;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_freeresult($query_id)
	{
		return false;
	}
}
