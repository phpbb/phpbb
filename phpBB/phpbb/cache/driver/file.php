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
* ACM File Based Caching
* @package acm
*/
class file extends \phpbb\cache\driver\base
{
	var $vars = array();
	var $var_expires = array();
	var $is_modified = false;

	var $sql_rowset = array();
	var $sql_row_pointer = array();
	var $cache_dir = '';

	/**
	* Set cache path
	*/
	function __construct($cache_dir = null)
	{
		global $phpbb_root_path;
		$this->cache_dir = !is_null($cache_dir) ? $cache_dir : $phpbb_root_path . 'cache/';
	}

	/**
	* {@inheritDoc}
	*/
	function load()
	{
		return $this->_read('data_global');
	}

	/**
	* {@inheritDoc}
	*/
	function unload()
	{
		$this->save();
		unset($this->vars);
		unset($this->var_expires);
		unset($this->sql_rowset);
		unset($this->sql_row_pointer);

		$this->vars = array();
		$this->var_expires = array();
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

		global $phpEx;

		if (!$this->_write('data_global'))
		{
			if (!function_exists('phpbb_is_writable'))
			{
				global $phpbb_root_path;
				include($phpbb_root_path . 'includes/functions.' . $phpEx);
			}

			// Now, this occurred how often? ... phew, just tell the user then...
			if (!phpbb_is_writable($this->cache_dir))
			{
				// We need to use die() here, because else we may encounter an infinite loop (the message handler calls $cache->unload())
				die('Fatal: ' . $this->cache_dir . ' is NOT writable.');
				exit;
			}

			die('Fatal: Not able to open ' . $this->cache_dir . 'data_global.' . $phpEx);
			exit;
		}

		$this->is_modified = false;
	}

	/**
	* {@inheritDoc}
	*/
	function tidy()
	{
		global $phpEx;

		$dir = @opendir($this->cache_dir);

		if (!$dir)
		{
			return;
		}

		$time = time();

		while (($entry = readdir($dir)) !== false)
		{
			if (!preg_match('/^(sql_|data_(?!global))/', $entry))
			{
				continue;
			}

			if (!($handle = @fopen($this->cache_dir . $entry, 'rb')))
			{
				continue;
			}

			// Skip the PHP header
			fgets($handle);

			// Skip expiration
			$expires = (int) fgets($handle);

			fclose($handle);

			if ($time >= $expires)
			{
				$this->remove_file($this->cache_dir . $entry);
			}
		}
		closedir($dir);

		if (file_exists($this->cache_dir . 'data_global.' . $phpEx))
		{
			if (!sizeof($this->vars))
			{
				$this->load();
			}

			foreach ($this->var_expires as $var_name => $expires)
			{
				if ($time >= $expires)
				{
					$this->destroy($var_name);
				}
			}
		}

		set_config('cache_last_gc', time(), true);
	}

	/**
	* {@inheritDoc}
	*/
	function get($var_name)
	{
		if ($var_name[0] == '_')
		{
			global $phpEx;

			if (!$this->_exists($var_name))
			{
				return false;
			}

			return $this->_read('data' . $var_name);
		}
		else
		{
			return ($this->_exists($var_name)) ? $this->vars[$var_name] : false;
		}
	}

	/**
	* {@inheritDoc}
	*/
	function put($var_name, $var, $ttl = 31536000)
	{
		if ($var_name[0] == '_')
		{
			$this->_write('data' . $var_name, $var, time() + $ttl);
		}
		else
		{
			$this->vars[$var_name] = $var;
			$this->var_expires[$var_name] = time() + $ttl;
			$this->is_modified = true;
		}
	}

	/**
	* {@inheritDoc}
	*/
	function purge()
	{
		// Purge all phpbb cache files
		try
		{
			$iterator = new \DirectoryIterator($this->cache_dir);
		}
		catch (Exception $e)
		{
			return;
		}

		foreach ($iterator as $fileInfo)
		{
			if ($fileInfo->isDot())
			{
				continue;
			}
			$filename = $fileInfo->getFilename();
			if ($fileInfo->isDir())
			{
				$this->remove_dir($fileInfo->getPathname());
			}
			elseif (strpos($filename, 'container_') === 0 ||
				strpos($filename, 'url_matcher') === 0 ||
				strpos($filename, 'sql_') === 0 ||
				strpos($filename, 'data_') === 0)
			{
				$this->remove_file($fileInfo->getPathname());
			}
		}

		unset($this->vars);
		unset($this->var_expires);
		unset($this->sql_rowset);
		unset($this->sql_row_pointer);

		$this->vars = array();
		$this->var_expires = array();
		$this->sql_rowset = array();
		$this->sql_row_pointer = array();

		$this->is_modified = false;
	}

	/**
	* Remove directory
	*
	* @param string $dir Directory to remove
	*
	* @return null
	*/
	protected function remove_dir($dir)
	{
		try
		{
			$iterator = new \DirectoryIterator($dir);
		}
		catch (Exception $e)
		{
			return;
		}

		foreach ($iterator as $fileInfo)
		{
			if ($fileInfo->isDot())
			{
				continue;
			}

			if ($fileInfo->isDir())
			{
				$this->remove_dir($fileInfo->getPathname());
			}
			else
			{
				$this->remove_file($fileInfo->getPathname());
			}
		}

		@rmdir($dir);
	}

	/**
	* {@inheritDoc}
	*/
	function destroy($var_name, $table = '')
	{
		global $phpEx;

		if ($var_name == 'sql' && !empty($table))
		{
			if (!is_array($table))
			{
				$table = array($table);
			}

			$dir = @opendir($this->cache_dir);

			if (!$dir)
			{
				return;
			}

			while (($entry = readdir($dir)) !== false)
			{
				if (strpos($entry, 'sql_') !== 0)
				{
					continue;
				}

				if (!($handle = @fopen($this->cache_dir . $entry, 'rb')))
				{
					continue;
				}

				// Skip the PHP header
				fgets($handle);

				// Skip expiration
				fgets($handle);

				// Grab the query, remove the LF
				$query = substr(fgets($handle), 0, -1);

				fclose($handle);

				foreach ($table as $check_table)
				{
					// Better catch partial table names than no table names. ;)
					if (strpos($query, $check_table) !== false)
					{
						$this->remove_file($this->cache_dir . $entry);
						break;
					}
				}
			}
			closedir($dir);

			return;
		}

		if (!$this->_exists($var_name))
		{
			return;
		}

		if ($var_name[0] == '_')
		{
			$this->remove_file($this->cache_dir . 'data' . $var_name . ".$phpEx", true);
		}
		else if (isset($this->vars[$var_name]))
		{
			$this->is_modified = true;
			unset($this->vars[$var_name]);
			unset($this->var_expires[$var_name]);

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
			global $phpEx;
			return file_exists($this->cache_dir . 'data' . $var_name . ".$phpEx");
		}
		else
		{
			if (!sizeof($this->vars))
			{
				$this->load();
			}

			if (!isset($this->var_expires[$var_name]))
			{
				return false;
			}

			return (time() > $this->var_expires[$var_name]) ? false : isset($this->vars[$var_name]);
		}
	}

	/**
	* {@inheritDoc}
	*/
	function sql_load($query)
	{
		// Remove extra spaces and tabs
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);

		if (($rowset = $this->_read('sql_' . md5($query))) === false)
		{
			return false;
		}

		$query_id = sizeof($this->sql_rowset);
		$this->sql_rowset[$query_id] = $rowset;
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

		$query_id = sizeof($this->sql_rowset);
		$this->sql_rowset[$query_id] = array();
		$this->sql_row_pointer[$query_id] = 0;

		while ($row = $db->sql_fetchrow($query_result))
		{
			$this->sql_rowset[$query_id][] = $row;
		}
		$db->sql_freeresult($query_result);

		if ($this->_write('sql_' . md5($query), $this->sql_rowset[$query_id], $ttl + time(), $query))
		{
			return $query_id;
		}

		return $query_result;
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
	* Read cached data from a specified file
	*
	* @access private
	* @param string $filename Filename to write
	* @return mixed False if an error was encountered, otherwise the data type of the cached data
	*/
	function _read($filename)
	{
		global $phpEx;

		$file = "{$this->cache_dir}$filename.$phpEx";

		$type = substr($filename, 0, strpos($filename, '_'));

		if (!file_exists($file))
		{
			return false;
		}

		if (!($handle = @fopen($file, 'rb')))
		{
			return false;
		}

		// Skip the PHP header
		fgets($handle);

		if ($filename == 'data_global')
		{
			$this->vars = $this->var_expires = array();

			$time = time();

			while (($expires = (int) fgets($handle)) && !feof($handle))
			{
				// Number of bytes of data
				$bytes = substr(fgets($handle), 0, -1);

				if (!is_numeric($bytes) || ($bytes = (int) $bytes) === 0)
				{
					// We cannot process the file without a valid number of bytes
					// so we discard it
					fclose($handle);

					$this->vars = $this->var_expires = array();
					$this->is_modified = false;

					$this->remove_file($file);

					return false;
				}

				if ($time >= $expires)
				{
					fseek($handle, $bytes, SEEK_CUR);

					continue;
				}

				$var_name = substr(fgets($handle), 0, -1);

				// Read the length of bytes that consists of data.
				$data = fread($handle, $bytes - strlen($var_name));
				$data = @unserialize($data);

				// Don't use the data if it was invalid
				if ($data !== false)
				{
					$this->vars[$var_name] = $data;
					$this->var_expires[$var_name] = $expires;
				}

				// Absorb the LF
				fgets($handle);
			}

			fclose($handle);

			$this->is_modified = false;

			return true;
		}
		else
		{
			$data = false;
			$line = 0;

			while (($buffer = fgets($handle)) && !feof($handle))
			{
				$buffer = substr($buffer, 0, -1); // Remove the LF

				// $buffer is only used to read integers
				// if it is non numeric we have an invalid
				// cache file, which we will now remove.
				if (!is_numeric($buffer))
				{
					break;
				}

				if ($line == 0)
				{
					$expires = (int) $buffer;

					if (time() >= $expires)
					{
						break;
					}

					if ($type == 'sql')
					{
						// Skip the query
						fgets($handle);
					}
				}
				else if ($line == 1)
				{
					$bytes = (int) $buffer;

					// Never should have 0 bytes
					if (!$bytes)
					{
						break;
					}

					// Grab the serialized data
					$data = fread($handle, $bytes);

					// Read 1 byte, to trigger EOF
					fread($handle, 1);

					if (!feof($handle))
					{
						// Somebody tampered with our data
						$data = false;
					}
					break;
				}
				else
				{
					// Something went wrong
					break;
				}
				$line++;
			}
			fclose($handle);

			// unserialize if we got some data
			$data = ($data !== false) ? @unserialize($data) : $data;

			if ($data === false)
			{
				$this->remove_file($file);
				return false;
			}

			return $data;
		}
	}

	/**
	* Write cache data to a specified file
	*
	* 'data_global' is a special case and the generated format is different for this file:
	* <code>
	* <?php exit; ?>
	* (expiration)
	* (length of var and serialised data)
	* (var)
	* (serialised data)
	* ... (repeat)
	* </code>
	*
	* The other files have a similar format:
	* <code>
	* <?php exit; ?>
	* (expiration)
	* (query) [SQL files only]
	* (length of serialised data)
	* (serialised data)
	* </code>
	*
	* @access private
	* @param string $filename Filename to write
	* @param mixed $data Data to store
	* @param int $expires Timestamp when the data expires
	* @param string $query Query when caching SQL queries
	* @return bool True if the file was successfully created, otherwise false
	*/
	function _write($filename, $data = null, $expires = 0, $query = '')
	{
		global $phpEx;

		$file = "{$this->cache_dir}$filename.$phpEx";

		$lock = new \phpbb\lock\flock($file);
		$lock->acquire();

		if ($handle = @fopen($file, 'wb'))
		{
			// File header
			fwrite($handle, '<' . '?php exit; ?' . '>');

			if ($filename == 'data_global')
			{
				// Global data is a different format
				foreach ($this->vars as $var => $data)
				{
					if (strpos($var, "\r") !== false || strpos($var, "\n") !== false)
					{
						// CR/LF would cause fgets() to read the cache file incorrectly
						// do not cache test entries, they probably won't be read back
						// the cache keys should really be alphanumeric with a few symbols.
						continue;
					}
					$data = serialize($data);

					// Write out the expiration time
					fwrite($handle, "\n" . $this->var_expires[$var] . "\n");

					// Length of the remaining data for this var (ignoring two LF's)
					fwrite($handle, strlen($data . $var) . "\n");
					fwrite($handle, $var . "\n");
					fwrite($handle, $data);
				}
			}
			else
			{
				fwrite($handle, "\n" . $expires . "\n");

				if (strpos($filename, 'sql_') === 0)
				{
					fwrite($handle, $query . "\n");
				}
				$data = serialize($data);

				fwrite($handle, strlen($data) . "\n");
				fwrite($handle, $data);
			}

			fclose($handle);

			if (!function_exists('phpbb_chmod'))
			{
				global $phpbb_root_path;
				include($phpbb_root_path . 'includes/functions.' . $phpEx);
			}

			phpbb_chmod($file, CHMOD_READ | CHMOD_WRITE);

			$return_value = true;
		}
		else
		{
			$return_value = false;
		}

		$lock->release();

		return $return_value;
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
}
