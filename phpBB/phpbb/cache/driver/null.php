<?php
/**
*
* @package acm
* @copyright (c) 2005, 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\cache\driver;

/**
* ACM Null Caching
* @package acm
*/
class null extends \phpbb\cache\driver\base
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
		// This cache always has a tidy room.
		set_config('cache_last_gc', time(), true);
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
