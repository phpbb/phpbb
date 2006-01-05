<?php
/**
*
* @package acm_db
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package acm_db
* ACM Database Caching
*/
class acm
{
	// Contains all loaded variables
	var $vars = '';

	// Contains the names of the variables that are ready to be used
	// (iow, variables that have been unserialized)
	var $var_ready = array();

	// Contains variables that have been updated or destroyed this session
	var $var_expires = array();

	// Contains variables that have already been requested
	// If a variable has been requested but not loaded, it simply means it
	// wasn't found in the db
	var $var_requested = array();

	function load($var_names = '')
	{
		global $db;
		$this->vars = array();

		if (is_array($var_names))
		{
			$var_requested = $var_names;
			$sql_condition = "var_name IN ('" . implode("', '", $var_names) . "')";
		}
		else
		{
//			$sql_condition = "var_name NOT LIKE '\_%'";
			$sql_condition = "LEFT(var_name, 1) <> '_'";
		}

		$sql = 'SELECT var_name, var_data
			FROM ' . CACHE_TABLE . '
			WHERE var_expires > ' . time() . "
				AND $sql_condition";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$this->vars[$row['var_name']] = $row['var_data'];

			if (!$var_names)
			{
				$var_requested[] = $row['var_name'];
			}
		}
	}

	function unload()
	{
		$this->save();
		unset($this->vars);
	}

	function save()
	{
		global $db;

		$delete = $insert = array();
		foreach ($this->var_expires as $var_name => $expires)
		{
			if ($expires == 'now')
			{
				$delete[] = $var_name;
			}
			else
			{
				$delete[] = $var_name;
				$insert[] = "'$var_name', $expires, '" . $db->sql_escape(serialize($this->vars[$var_name])) . "'";
			}
		}
		$this->var_expires = array();

		if (sizeof($delete))
		{
			$sql = 'DELETE FROM ' . CACHE_TABLE . "
				WHERE var_name IN ('" . implode("', '", $delete) . "')";
			$db->sql_query($sql);
		}

		if (sizeof($insert))
		{
			switch (SQL_LAYER)
			{
				case 'mysql':
				case 'mysql4':
				case 'mysqli':
					$sql = 'INSERT INTO ' . CACHE_TABLE . ' (var_name, var_expires, var_data)
						VALUES (' . implode('), (', $insert) . ')';
					$db->sql_query($sql);
				break;

				default:
					foreach ($insert as $values)
					{
						$sql = 'INSERT INTO ' . CACHE_TABLE . " (var_name, var_expires, var_data)
							VALUES ($values)";
						$db->sql_query($sql);
					}
			}
		}
	}

	function tidy()
	{
		global $db;

		$sql = 'DELETE FROM ' . CACHE_TABLE . '
			WHERE var_expires < ' . time();
		$db->sql_query($sql);

		set_config('cache_last_gc', time(), true);
	}

	function get($var_name)
	{
		if (!is_array($this->vars))
		{
			$this->load();
		}

		if ($var_name{0} == '_')
		{
			if (!in_array($this->var_requested, $var_name))
			{
				$this->var_requested[] = $var_name;

				global $db;
				$sql = 'SELECT var_data
					FROM ' . CACHE_TABLE . "
					WHERE var_name = '$var_name'
						AND var_expires > " . time();
				$result = $db->sql_query($sql);
				if ($row = $db->sql_fetchrow($result))
				{
					$this->vars[$var_name] = $row['var_data'];
				}
			}
		}

		if ($this->_exists($var_name))
		{
			if (empty($this->var_ready[$var_name]))
			{
				$this->vars[$var_name] = unserialize($this->vars[$var_name]);
				$this->var_ready[$var_name] = true;
			}

			return $this->vars[$var_name];
		}
		else
		{
			return false;
		}
	}

	function put($var_name, $var_data, $ttl = 31536000)
	{
		$this->vars[$var_name] = $var_data;

		if ($var_name{0} == '_')
		{
			global $db;

			switch (SQL_LAYER)
			{
				case 'mysql':
				case 'mysql4':
				case 'mysqli':
					$INSERT = 'REPLACE';
				break;
			
				default:
					$sql = 'DELETE FROM ' . CACHE_TABLE . "
						WHERE var_name = '$var_name'";
					$db->sql_query($sql);

					$INSERT = 'INSERT';
			}

			$sql = "$INSERT INTO " . CACHE_TABLE . " (var_name, var_expires, var_data)
				VALUES ('$var_name', " . (time() + $ttl) . ", '" . $db->sql_escape(serialize($var_data)) . "')";
			$db->sql_query($sql);
		}
		else
		{
			$this->var_expires[$var_name] = time() + $ttl;
		}
	}

	function destroy($var_name, $void = NULL)
	{
		if (isset($this->vars[$var_name]))
		{
			$this->var_expires[$var_name] = 'now';
			unset($this->vars[$var_name]);
		}
	}

	function _exists($var_name)
	{
		if (!is_array($this->vars))
		{
			$this->load();
		}

		return isset($this->vars[$var_name]);
	}
}

?>