<?php
/**
*
* @package acm
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\cache\driver;

/**
* ACM Abstract Memory Class
* @package acm
*/
abstract class memory extends \phpbb\cache\driver\base
{
	var $key_prefix;

	var $vars = array();
	var $is_modified = false;

	var $sql_rowset = array();
	var $sql_row_pointer = array();
	var $cache_dir = '';

	/**
	* Set cache path
	*/
	function __construct()
	{
		global $phpbb_root_path, $dbname, $table_prefix;

		$this->cache_dir	= $phpbb_root_path . 'cache/';
		$this->key_prefix	= substr(md5($dbname . $table_prefix), 0, 8) . '_';

		if (!isset($this->extension) || !extension_loaded($this->extension))
		{
			global $acm_type;

			trigger_error("Could not find required extension [{$this->extension}] for the ACM module $acm_type.", E_USER_ERROR);
		}

		if (isset($this->function) && !function_exists($this->function))
		{
			global $acm_type;

			trigger_error("The required function [{$this->function}] is not available for the ACM module $acm_type.", E_USER_ERROR);
		}
	}

	/**
	* {@inheritDoc}
	*/
	function load()
	{
		// grab the global cache
		$this->vars = $this->_read('global');

		if ($this->vars !== false)
		{
			return true;
		}

		return false;
	}

	/**
	* {@inheritDoc}
	*/
	function unload()
	{
		$this->save();
		unset($this->vars);
		unset($this->sql_rowset);
		unset($this->sql_row_pointer);

		$this->vars = array();
		$this->sql_rowset = array();
		$this->sql_row_pointer = array();
	}

	/**
	* {@inheritDoc}
	*/
	function save()
	{
		if (!$this->is_modified)
		{
			return;
		}

		$this->_write('global', $this->vars, 2592000);

		$this->is_modified = false;
	}

	/**
	* {@inheritDoc}
	*/
	function tidy()
	{
		// cache has auto GC, no need to have any code here :)

		set_config('cache_last_gc', time(), true);
	}

	/**
	* {@inheritDoc}
	*/
	function get($var_name)
	{
		if ($var_name[0] == '_')
		{
			if (!$this->_exists($var_name))
			{
				return false;
			}

			return $this->_read($var_name);
		}
		else
		{
			return ($this->_exists($var_name)) ? $this->vars[$var_name] : false;
		}
	}

	/**
	* {@inheritDoc}
	*/
	function put($var_name, $var, $ttl = 2592000)
	{
		if ($var_name[0] == '_')
		{
			$this->_write($var_name, $var, $ttl);
		}
		else
		{
			$this->vars[$var_name] = $var;
			$this->is_modified = true;
		}
	}

	/**
	* {@inheritDoc}
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
			if (strpos($entry, 'container_') !== 0 &&
				strpos($entry, 'url_matcher') !== 0 &&
				strpos($entry, 'sql_') !== 0 &&
				strpos($entry, 'data_') !== 0 &&
				strpos($entry, 'ctpl_') !== 0 &&
				strpos($entry, 'tpl_') !== 0)
			{
				continue;
			}

			$this->remove_file($this->cache_dir . $entry);
		}
		closedir($dir);

		unset($this->vars);
		unset($this->sql_rowset);
		unset($this->sql_row_pointer);

		$this->vars = array();
		$this->sql_rowset = array();
		$this->sql_row_pointer = array();

		$this->is_modified = false;
	}


	/**
	* {@inheritDoc}
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
				$temp = $this->_read('sql_' . $table_name);

				if ($temp === false)
				{
					continue;
				}

				// delete each query ref
				foreach ($temp as $md5_id => $void)
				{
					$this->_delete('sql_' . $md5_id);
				}

				// delete the table ref
				$this->_delete('sql_' . $table_name);
			}

			return;
		}

		if (!$this->_exists($var_name))
		{
			return;
		}

		if ($var_name[0] == '_')
		{
			$this->_delete($var_name);
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
	* {@inheritDoc}
	*/
	function _exists($var_name)
	{
		if ($var_name[0] == '_')
		{
			return $this->_isset($var_name);
		}
		else
		{
			if (!sizeof($this->vars))
			{
				$this->load();
			}

			return isset($this->vars[$var_name]);
		}
	}

	/**
	* {@inheritDoc}
	*/
	function sql_load($query)
	{
		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		$query_id = sizeof($this->sql_rowset);

		if (($result = $this->_read('sql_' . md5($query))) === false)
		{
			return false;
		}

		$this->sql_rowset[$query_id] = $result;
		$this->sql_row_pointer[$query_id] = 0;

		return $query_id;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_save(\phpbb\db\driver\driver_interface $db, $query, $query_result, $ttl)
	{
		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		$hash = md5($query);

		// determine which tables this query belongs to
		// Some queries use backticks, namely the get_database_size() query
		// don't check for conformity, the SQL would error and not reach here.
		if (!preg_match('/FROM \\(?(`?\\w+`?(?: \\w+)?(?:, ?`?\\w+`?(?: \\w+)?)*)\\)?/', $query, $regs))
		{
			// Bail out if the match fails.
			return $query_result;
		}
		$tables = array_map('trim', explode(',', $regs[1]));

		foreach ($tables as $table_name)
		{
			// Remove backticks
			$table_name = ($table_name[0] == '`') ? substr($table_name, 1, -1) : $table_name;

			if (($pos = strpos($table_name, ' ')) !== false)
			{
				$table_name = substr($table_name, 0, $pos);
			}

			$temp = $this->_read('sql_' . $table_name);

			if ($temp === false)
			{
				$temp = array();
			}

			$temp[$hash] = true;

			// This must never expire
			$this->_write('sql_' . $table_name, $temp, 0);
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

		$this->_write('sql_' . $hash, $this->sql_rowset[$query_id], $ttl);

		return $query_id;
	}

	/**
	* {@inheritDoc}
	*/
	function sql_exists($query_id)
	{
		return isset($this->sql_rowset[$query_id]);
	}

	/**
	* {@inheritDoc}
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
	* {@inheritDoc}
	*/
	function sql_fetchfield($query_id, $field)
	{
		if ($this->sql_row_pointer[$query_id] < sizeof($this->sql_rowset[$query_id]))
		{
			return (isset($this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]][$field])) ? $this->sql_rowset[$query_id][$this->sql_row_pointer[$query_id]++][$field] : false;
		}

		return false;
	}

	/**
	* {@inheritDoc}
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
	* {@inheritDoc}
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

	/**
	* Removes/unlinks file
	*
	* @param string $filename Filename to remove
	* @param bool $check Check file permissions
	* @return bool True if the file was successfully removed, otherwise false
	*/
	function remove_file($filename, $check = false)
	{
		if (!function_exists('phpbb_is_writable'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions.' . $phpEx);
		}

		if ($check && !phpbb_is_writable($this->cache_dir))
		{
			// E_USER_ERROR - not using language entry - intended.
			trigger_error('Unable to remove files within ' . $this->cache_dir . '. Please check directory permissions.', E_USER_ERROR);
		}

		return @unlink($filename);
	}

	/**
	* Check if a cache var exists
	*
	* @access protected
	* @param string $var Cache key
	* @return bool True if it exists, otherwise false
	*/
	function _isset($var)
	{
		// Most caches don't need to check
		return true;
	}
}
