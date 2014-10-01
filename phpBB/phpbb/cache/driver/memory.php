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
* ACM Abstract Memory Class
*/
abstract class memory extends \phpbb\cache\driver\base
{
	var $key_prefix;

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
	function sql_save(\phpbb\db\driver\driver_interface $db, $query, $query_result, $ttl)
	{
		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		$hash = md5($query);

		// determine which tables this query belongs to
		// Some queries use backticks, namely the get_database_size() query
		// don't check for conformity, the SQL would error and not reach here.
		if (!preg_match_all('/(?:FROM \\(?(`?\\w+`?(?: \\w+)?(?:, ?`?\\w+`?(?: \\w+)?)*)\\)?)|(?:JOIN (`?\\w+`?(?: \\w+)?))/', $query, $regs, PREG_SET_ORDER))
		{
			// Bail out if the match fails.
			return $query_result;
		}

		$tables = array();
		foreach ($regs as $match)
		{
			if ($match[0][0] == 'F')
			{
				$tables = array_merge($tables, array_map('trim', explode(',', $match[1])));
			}
			else
			{
				$tables[] = $match[2];
			}
		}

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
