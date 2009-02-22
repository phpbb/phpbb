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
* ACM Memcache Based Caching
* @package acm
*/
class acm
{
	var $vars = array();
	var $is_modified = false;

	var $sql_rowset = array();
	var $sql_row_pointer = array();
	var $cache_dir = '';

	/**
	* Set cache path
	*/
	function acm()
	{
		$this->cache_dir = $phpbb_root_path . 'cache/';
	}

	/**
	* Load global cache
	*/
	function load()
	{
		// grab the global cache
		$temp = eaccelerator_get('global');

		if ($temp !== null)
		{
			$this->vars = $temp;
		}
		else
		{
			return false;
		}

		return true;
	}

	/**
	* Unload cache object
	*/
	function unload()
	{
		$this->save();
		unset($this->vars);
		unset($this->sql_rowset);
		unset($this->sql_row_pointer);
	}

	/**
	* Save modified objects
	*/
	function save()
	{
		if (!$this->is_modified)
		{
			return;
		}

		eaccelerator_put('global', $this->vars, 31536000);

		$this->is_modified = false;
	}

	/**
	* Tidy cache
	*/
	function tidy()
	{
		eaccelerator_gc();

		set_config('cache_last_gc', time(), true);
	}

	/**
	* Get saved cache object
	*/
	function get($var_name)
	{
		if ($var_name[0] == '_')
		{
			$temp = eaccelerator_get($var_name);

			if ($temp !== null)
			{
				return $temp;
			}
			else
			{
				return false;
			}
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
	function put($var_name, $var, $ttl = 31536000)
	{
		if ($var_name[0] == '_')
		{
			eaccelerator_put($var_name, $var, $ttl);
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
	function purge()
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

		foreach (eaccelerator_list_keys() as $var)
		{
			eaccelerator_rm(substr($var['name'], 1));
		}

		unset($this->vars);
		unset($this->sql_rowset);
		unset($this->sql_row_pointer);

		$this->is_modified = false;
	}

	/**
	* Destroy cache data
	*/
	function destroy($var_name, $table = '')
	{
		if ($var_name == 'sql' && !empty($table))
		{
			if (!is_array($table))
			{
				$table = array($table);
			}

			foreach ($table as $table_name)
			{
				// gives us the md5s that we want
				eaccelerator_lock('sql_' . $table_name);
				$temp = eaccelerator_get('sql_' . $table_name);
				if ($temp === null)
				{
					continue;
				}

				// delete each query ref
				foreach ($temp as $md5_id => $void)
				{
					eaccelerator_lock('sql_' . $md5_id);
					eaccelerator_rm('sql_' . $md5_id);
					eaccelerator_unlock('sql_' . $md5_id);
				}

				// delete the table ref
				eaccelerator_rm('sql_' . $table_name);
				eaccelerator_unlock('sql_' . $table_name);
			}

			return;
		}

		if ($var_name[0] == '_')
		{
			eaccelerator_rm($var_name);
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
	function sql_load($query)
	{
		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		$query_id = sizeof($this->sql_rowset);

		$temp = eaccelerator_get('sql_' . md5($query));

		if ($temp === null)
		{
			return false;
		}

		$this->sql_rowset[$query_id] = $temp;

		$this->sql_row_pointer[$query_id] = 0;

		return $query_id;
	}

	/**
	* Save sql query
	*/
	function sql_save($query, &$query_result, $ttl)
	{
		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);

		// determine which tables this query belongs to
		preg_match('/FROM \\(?(\\w+(?: \\w+)?(?:, ?\\w+(?: \\w+)?)*)\\)?/', $query, $regs);
		$tables = array_map('trim', explode(',', $regs[1]));

		foreach ($tables as $table_name)
		{
			if (($pos = strpos($table_name, ' ')) !== false)
			{
				$table_name = substr($table_name, 0, $pos);
			}

			$temp = eaccelerator_get('sql_' . $table_name);
			if ($temp === null)
			{
				$temp = array();
			}
			$temp[md5($query)] = true;
			eaccelerator_put('sql_' . $table_name, $temp, $ttl);
		}

		// store them in the right place
		$query_id = sizeof($this->sql_rowset);
		$this->sql_rowset[$query_id] = array();
		$this->sql_row_pointer[$query_id] = 0;

		while ($row = $db->sql_fetchrow($query_result))
		{
			$this->sql_rowset[$query_id][] = $row;
		}
		$db->sql_freeresult($query_result);

		eaccelerator_put('sql_' . md5($query), $this->sql_rowset[$query_id], $ttl);

		$query_result = $query_id;
	}

	/**
	* Ceck if a given sql query exist in cache
	*/
	function sql_exists($query_id)
	{
		return isset($this->sql_rowset[$query_id]);
	}

	/**
	* Fetch row from cache (database)
	*/
	function sql_fetchrow($query_id)
	{
		if ($this->sql_row_pointer[$query_id] < sizeof($this->sql_rowset[$query_id]))
		{
			return $this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]++];
		}

		return false;
	}

	/**
	* Fetch a field from the current row of a cached database result (database)
	*/
	function sql_fetchfield($query_id, $field)
	{
		if ($this->sql_row_pointer[$query_id] < sizeof($this->sql_rowset[$query_id]))
		{
			return (isset($this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]][$field])) ? $this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]][$field] : false;
		}

		return false;
	}

	/**
	* Seek a specific row in an a cached database result (database)
	*/
	function sql_rowseek($rownum, $query_id)
	{
		if ($rownum >= sizeof($this->sql_rowset[$query_id]))
		{
			return false;
		}

		$this->sql_row_pointer[$query_id] = $rownum;
		return true;
	}

	/**
	* Free memory used for a cached database result (database)
	*/
	function sql_freeresult($query_id)
	{
		if (!isset($this->sql_rowset[$query_id]))
		{
			return false;
		}

		unset($this->sql_rowset[$query_id]);
		unset($this->sql_row_pointer[$query_id]);

		return true;
	}
}

?>