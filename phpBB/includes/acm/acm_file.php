<?php
/***************************************************************************
 *                              acm_file.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

class acm
{
	var $vars = '';
	var $vars_ts = array();
	var $is_modified = FALSE;

	var $sql_rowset = array();

	function acm()
	{
		global $phpbb_root_path;
		$this->cache_dir = $phpbb_root_path . 'cache/';
	}

	function load()
	{
		global $phpEx;
		@include($this->cache_dir . 'data_global.' . $phpEx);
	}

	function unload()
	{
		$this->save();
		unset($this->vars);
		unset($this->vars_ts);
		unset($this->sql_rowset);
	}

	function save() 
	{
		if (!$this->is_modified)
		{
			return;
		}

		global $phpEx;
		$file = '<?php $this->vars=' . $this->format_array($this->vars) . ";\n\$this->vars_ts=" . $this->format_array($this->vars_ts) . ' ?>';

		if ($fp = @fopen($this->cache_dir . 'data_global.' . $phpEx, 'wb'))
		{
			@flock($fp, LOCK_EX);
			fwrite($fp, $file);
			@flock($fp, LOCK_UN);
			fclose($fp);
		}

		$this->is_modified = FALSE;
	}

	function tidy($max_age = 0)
	{
		global $phpEx;

		$dir = opendir($this->cache_dir);
		while ($entry = readdir($dir))
		{
			if (substr($entry, 0, 4) != 'sql_')
			{
				continue;
			}

			if (time() > filemtime($this->cache_dir . $entry) + $max_age)
			{
				unlink($this->cache_dir . $entry);
			}
		}
		@closedir($dir);

		if (file_exists($this->cache_dir . 'data_global.' . $phpEx))
		{
			foreach ($this->vars_ts as $var_name => $timestamp)
			{
				if (time() > $timestamp + $max_age)
				{
					$this->destroy($var_name);
				}
			}
		}
		else
		{
			$this->vars = $this->vars_ts = array();
			$this->is_modified = TRUE;
		}
	}

	function get($var_name, $max_age = 0)
	{
		return ($this->exists($var_name, $max_age)) ? $this->vars[$var_name] : NULL;
	}

	function put($var_name, $var)
	{
		$this->vars[$var_name] = $var;
		$this->vars_ts[$var_name] = time();
		$this->is_modified = TRUE;
	}

	function destroy($var_name, $table = '')
	{
		if ($var_name == 'sql' && !empty($table))
		{
			$regex = '(' . ((is_array($table)) ? implode('|', $table) : $table) . ')';

			$dir = opendir($this->cache_dir);
			while ($entry = readdir($dir))
			{
				if (substr($entry, 0, 4) != 'sql_')
				{
					continue;
				}

				$fp = fopen($this->cache_dir . $entry, 'rb');
				$file = fread($fp, filesize($this->cache_dir . $entry));
				@fclose($fp);

				if (preg_match('#/\*.*?\W' . $regex . '\W.*?\*/#s', $file, $m))
				{
					unlink($this->cache_dir . $entry);
				}
			}
			@closedir($dir);
		}
		elseif (isset($this->vars[$var_name]))
		{
			$this->is_modified = TRUE;
			unset($this->vars[$var_name]);
			unset($this->vars_ts[$var_name]);
		}
	}

	function exists($var_name, $max_age = 0)
	{
		if (!is_array($this->vars))
		{
			$this->load();
		}

		if ($max_age > 0 && isset($this->vars_ts[$var_name]))
		{
			if (time() > $this->vars_ts[$var_name] + $max_age)
			{
				$this->destroy($var_name);
				return FALSE;
			}
		}

		return isset($this->vars[$var_name]);
	}

	function format_array($array)
	{
		$lines = array();
		foreach ($array as $k => $v)
		{
			if (is_array($v))
			{
				$lines[] = "'$k'=>" . $this->format_array($v);
			}
			elseif (is_int($v))
			{
				$lines[] = "'$k'=>$v";
			}
			elseif (is_bool($v))
			{
				$lines[] = "'$k'=>" . (($v) ? 'TRUE' : 'FALSE');
			}
			else
			{
				$lines[] = "'$k'=>'" . str_replace("'", "\'", str_replace('\\', '\\\\', $v)) . "'";
			}
		}
		return 'array(' . implode(',', $lines) . ')';
	}

	function sql_load($query, $max_age)
	{
		global $phpEx;

		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);

		$filemtime = intval(@filemtime($this->cache_dir . 'sql_' . md5($query) . '.' . $phpEx));
		if (time() > $filemtime + $max_age)
		{
			return FALSE;
		}

		include($this->cache_dir . 'sql_' . md5($query) . '.' . $phpEx);

		$query_id = 'Cache id #' . count($this->sql_rowset);
		$this->sql_rowset[$query_id] = $rowset;

		return $query_id;
	}

	function sql_save($query, &$query_result)
	{
		global $db, $phpEx;

		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);

		if ($fp = @fopen($this->cache_dir . 'sql_' . md5($query) . '.' . $phpEx, 'wb'))
		{
			@flock($fp, LOCK_EX);

			$lines = array();
			$query_id = 'Cache id #' . count($this->sql_rowset);
			$this->sql_rowset[$query_id] = array();

			while ($row = $db->sql_fetchrow($query_result))
			{
				$this->sql_rowset[$query_id][] = $row;

				$line = 'array(';
				foreach ($row as $key => $val)
				{
					$line .= "'$key'=>'" . str_replace("'", "\'", str_replace('\\', '\\\\', $val)) . "',";
				}
				$lines[] = substr($line, 0, -1) . ')';
			}

			fwrite($fp, "<?php\n\n/*\n$query\n*/\n\n\$rowset = array(" . implode(',', $lines) . ') ?>');
			@flock($fp, LOCK_UN);
			fclose($fp);

			$query_result = $query_id;
		}
	}

	function sql_exists($query_id)
	{
		return isset($this->sql_rowset[$query_id]);
	}

	function sql_fetchrow($query_id)
	{
		return array_shift($this->sql_rowset[$query_id]);
	}
}
?>