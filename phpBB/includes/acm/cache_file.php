<?php
/***************************************************************************
 *                              cache_file.php
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
	var $modified = FALSE;

	var $sql_rowset = array();
	var $sql_rowset_index = array();

	function acm()
	{
		//$this->load_cache();
		$this->cache_dir = $phpbb_root_path . 'cache/';
	}

	function tidy($expire_time = 0)
	{
		global $phpEx;

		$dir = opendir($this->cache_dir);
		while ($entry = readdir($dir))
		{
			if ($entry{0} == '.')
			{
				continue;
			}
			if (!$expire_time || time() - $expire_time >= filemtime($this->cache_dir . $entry))
			{
				unlink($this->cache_dir . $entry);
			}
		}
		if (file_exists($this->cache_dir . 'global.' . $phpEx))
		{
			foreach ($this->vars_ts as $varname => $timestamp)
			{
				if (time() - $expire_time >= $timestamp)
				{
					$this->destroy($varname);
				}
			}
		}
		else
		{
			$this->vars = $this->vars_ts = array();
			$this->modified = TRUE;
		}
	}

	function save($varname, $var)
	{
		$this->vars[$varname] = $var;
		$this->vars_ts[$varname] = time();
		$this->modified = TRUE;
	}

	function destroy($varname)
	{
		if (isset($this->vars[$varname]))
		{
			$this->modified = TRUE;
			unset($this->vars[$varname]);
			unset($this->vars_ts[$varname]);
		}
	}

	function load($varname, $expire_time = 0)
	{
		return ($this->exists($varname, $expire_time)) ? $this->vars[$varname] : null;
	}

	function exists($varname, $expire_time = 0)
	{
		if (!is_array($this->vars))
		{
			$this->load_cache();
		}

		if ($expire_time > 0)
		{
			if (!isset($this->vars[$varname]))
			{
				return FALSE;
			}
			if ($this->vars_ts[$varname] <= time() - $expire_time)
			{
				$this->destroy($varname);
				return FALSE;
			}
		}

		return isset($this->vars[$varname]);
	}

	function load_cache()
	{
		global $phpEx;

		$this->vars = array();
		@include($this->cache_dir . 'global.' . $phpEx);
	}

	function save_cache()
	{
		if (!$this->modified)
		{
			return;
		}

		global $phpEx;
		$file = '<?php $this->vars=' . $this->format_array($this->vars) . ";\n\$this->vars_ts=" . $this->format_array($this->vars_ts) . ' ?>';

		$fp = fopen($this->cache_dir . 'global.' . $phpEx, 'wb');
		fwrite($fp, $file);
		fclose($fp);
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
				$lines[] = "'$k'=>'" . str_replace('\\\\', '\\\\\\\\', str_replace("'", "\'", $v)) . "'";
			}
		}
		return 'array(' . implode(',', $lines) . ')';
	}

	function sql_load($query)
	{
		global $db, $phpEx;
		if (!file_exists($this->cache_dir . md5($query) . '.' . $phpEx))
		{
			return false;
		}

		include($this->cache_dir . md5($query) . '.' . $phpEx);

		$query_id = 'Cache id #' . count($this->sql_rowset);
		$this->sql_rowset[$query_id] = $rowset;
		$this->sql_rowset_index[$query_id] = 0;
		$db->query_result = $query_id;

		return true;
	}

	function sql_save($query, $result)
	{
		global $db, $phpEx;
		$fp = fopen($this->cache_dir . md5($query) . '.' . $phpEx, 'wb');

		$lines = array();
		$query_id = 'Cache id #' . count($this->sql_rowset);
		$this->sql_rowset[$query_id] = array();
		$this->sql_rowset_index[$query_id] = 0;
		$db->query_result = $query_id;

		while ($row = $db->sql_fetchrow($result))
		{
			$this->sql_rowset[$query_id][] = $row;

			$line = 'array(';
			foreach ($row as $key => $val)
			{
				$line .= "'$key'=>'" . str_replace('\\\\', '\\\\\\\\', str_replace("'", "\'", $val)) . "',";
			}
			$lines[] = substr($line, 0, -1) . ')';
		}

		fwrite($fp, "<?php\n\n/*\n$query\n*/\n\n\$rowset = array(" . implode(',', $lines) . ') ?>');
		fclose($fp);
	}

	function sql_exists($query_id)
	{
		return isset($this->sql_rowset[$query_id]);
	}

	function sql_fetchrow($query_id)
	{
		if (!isset($this->sql_rowset[$query_id][$this->sql_rowset_index[$query_id]]))
		{
			return false;
		}

		$row = $this->sql_rowset[$query_id][$this->sql_rowset_index[$query_id]];
		++$this->sql_rowset_index[$query_id];

		return $row;
	}
}
?>