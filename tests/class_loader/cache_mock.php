<?php
/**
*
* @package testing
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require '../phpBB/includes/cache/driver/interface.php';

class phpbb_cache_mock implements phpbb_cache_driver_interface
{
	private $variables = array();

	function get($var_name)
	{
		if (isset($this->variables[$var_name]))
		{
			return $this->variables[$var_name];
		}

		return false;
	}

	function put($var_name, $value, $ttl = 0)
	{
		$this->variables[$var_name] = $value;
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
}
