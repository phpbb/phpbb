<?php
/** 
*
* @package acm
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* ACM APC Based Caching
* @package acm
*/
class acm
{
	private $vars = array();
	private $is_modified = false;

	public $sql_rowset = array();
	public $cache_dir = '';

	/**
	* Set cache path
	*/
	function __construct()
	{
		global $phpbb_root_path;
		$this->cache_dir = $phpbb_root_path . 'cache/';
	}

	/**
	* Load global cache
	*/
	private function load()
	{
		global $phpEx;

		// grab the global cache
		if ($this->vars = apc_fetch('global'))
		{
			return true;
		}

		return false;
	}

	/**
	* Unload cache object
	*/
	public function unload()
	{
		$this->save();
		unset($this->vars);
		unset($this->sql_rowset);

		$this->vars = array();
		$this->sql_rowset = array();
	}

	/**
	* Save modified objects
	*/
	private function save()
	{
		if (!$this->is_modified)
		{
			return;
		}

		apc_store('global', $this->vars, 31536000);

		$this->is_modified = false;
	}

	/**
	* Tidy cache
	*/
	public function tidy()
	{
		global $phpEx;

		// cache has auto GC, no need to have any code here :)

		set_config('cache_last_gc', time(), true);
	}

	/**
	* Get saved cache object
	*/
	public function get($var_name)
	{
		if ($var_name[0] === '_')
		{
			global $phpEx;

			return apc_fetch($var_name);
		}
		else
		{
			if (!sizeof($this->vars))
			{
				$this->load();
			}
			return (isset($this->vars[$var_name])) ? $this->vars[$var_name] : false;
		}
	}

	/**
	* Put data into cache
	*/
	public function put($var_name, $var, $ttl = 31536000)
	{
		if ($var_name[0] === '_')
		{
			apc_store($var_name, $var, $ttl);
		}
		else
		{
			$this->vars[$var_name] = $var;
			$this->is_modified = true;
		}
	}

	/**
	* Purge cache data
	*/
	public function purge()
	{
		// Purge all phpbb cache files
		$dir = @opendir($this->cache_dir);

		if (!$dir)
		{
			return;
		}

		while (($entry = readdir($dir)) !== false)
		{
			if (strpos($entry, 'sql_') !== 0 && strpos($entry, 'data_') !== 0 && strpos($entry, 'ctpl_') !== 0 && strpos($entry, 'tpl_') !== 0)
			{
				continue;
			}

			@unlink($this->cache_dir . $entry);
		}
		closedir($dir);

		apc_clear_cache('user');

		unset($this->vars);
		unset($this->sql_rowset);

		$this->vars = array();
		$this->var_expires = array();
		$this->sql_rowset = array();

		$this->is_modified = false;
	}

	/**
	* Destroy cache data
	*/
	public function destroy($var_name, $table = '')
	{
		global $phpEx;

		if ($var_name === 'sql' && !empty($table))
		{
			if (!is_array($table))
			{
				$table = array($table);
			}

			foreach ($table as $table_name)
			{
				// gives us the md5s that we want
				$temp = apc_fetch('sql_' . $table_name);
				if ($temp === false)
				{
					continue;
				}

				// delete each query ref
				foreach ($temp as $md5_id => $void)
				{
					apc_delete('sql_' . $md5_id);
				}

				// delete the table ref
				apc_delete('sql_' . $table_name);
			}

			return;
		}

		if ($var_name[0] === '_')
		{
			apc_delete($var_name);
		}
		else if (isset($this->vars[$var_name]))
		{
			$this->is_modified = true;
			unset($this->vars[$var_name]);

			// We save here to let the following cache hits succeed
			$this->save();
		}
	}

	/**
	* Load cached sql query
	*/
	public function sql_load($query)
	{
		global $phpEx;

		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		$query_id = sizeof($this->sql_rowset);

		$temp = apc_fetch('sql_' . md5($query));

		if ($temp === false)
		{
			return false;
		}

		$this->sql_rowset[$query_id] = $temp;

		return $query_id;
	}

	/**
	* Save sql query
	*/
	public function sql_save($query, &$query_result, $ttl)
	{
		global $db, $phpEx;

		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);

		// determine which tables this query belongs to:

		// grab all the FROM tables, avoid getting a LEFT JOIN
		preg_match('/FROM \(?(\w+(?: (?!LEFT JOIN)\w+)?(?:, ?\w+(?: (?!LEFT JOIN)\w+)?)*)\)?/', $query, $regs);
		$tables = array_map('trim', explode(',', $regs[1]));

		// now get the LEFT JOIN
		preg_match_all('/LEFT JOIN\s+(\w+)(?: \w+)?/', $query, $result, PREG_PATTERN_ORDER);
		$tables = array_merge($tables, $result[1]);

		$query_hash = md5($query);

		foreach ($tables as $table_name)
		{
			if (($pos = strpos($table_name, ' ')) !== false)
			{
				$table_name = substr($table_name, 0, $pos);
			}

			$temp = apc_fetch('sql_' . $table_name);
			if ($temp === false)
			{
				$temp = array();
			}
			$temp[$query_hash] = true;
			apc_store('sql_' . $table_name, $temp, $ttl);
		}

		// store them in the right place
		$query_id = sizeof($this->sql_rowset);
		$this->sql_rowset[$query_id] = array();

		while ($row = $db->sql_fetchrow($query_result))
		{
			$this->sql_rowset[$query_id][] = $row;
		}
		$db->sql_freeresult($query_result);

		apc_store('sql_' . $query_hash, $this->sql_rowset[$query_id], $ttl);

		$query_result = $query_id;
	}

	/**
	* Fetch row from cache (database)
	*/
	public function sql_fetchrow($query_id)
	{
		list(, $row) = each($this->sql_rowset[$query_id]);

		return ($row !== NULL) ? $row : false;
	}

	/**
	* Fetch a field from the current row of a cached database result (database)
	*/
	public function sql_fetchfield($query_id, $field)
	{
		$row = current($this->sql_rowset[$query_id]);

		return ($row !== false && isset($row[$field])) ? $row[$field] : false;
	}

	/**
	* Free memory used for a cached database result (database)
	*/
	public function sql_freeresult($query_id)
	{
		if (!isset($this->sql_rowset[$query_id]))
		{
			return false;
		}

		unset($this->sql_rowset[$query_id]);

		return true;
	}
}

?>