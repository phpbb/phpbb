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

abstract class base implements \phpbb\cache\driver\driver_interface
{
	var $vars = array();
	var $is_modified = false;

	var $cache_dir = '';

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
		catch (\Exception $e)
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
			else if (strpos($filename, 'container_') === 0 ||
				strpos($filename, 'autoload_') === 0 ||
				strpos($filename, 'url_matcher') === 0 ||
				strpos($filename, 'url_generator') === 0 ||
				strpos($filename, 'sql_') === 0 ||
				strpos($filename, 'data_') === 0)
			{
				$this->remove_file($fileInfo->getPathname());
			}
		}

		unset($this->vars);

		if (function_exists('opcache_reset'))
		{
			@opcache_reset();
		}

		$this->vars = array();

		$this->is_modified = false;
	}

	/**
	* {@inheritDoc}
	*/
	function unload()
	{
		$this->save();
		unset($this->vars);

		$this->vars = array();
	}

	/**
	* {@inheritDoc}
	*/
	public function get_cache_id_from_sql_query($query)
	{
		$query = preg_replace('/[\n\r\s\t]+/', ' ', $query);
		return md5($query);
	}

	/**
	* {@inheritDoc}
	*/
	function sql_load($query)
	{
		// Remove extra spaces and tabs
		$query_id = $this->get_cache_id_from_sql_query($query);

		if (($result = $this->_read('sql_' . $query_id)) === false)
		{
			return false;
		}

		return $result;
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
		global $phpbb_filesystem;

		if ($check && !$phpbb_filesystem->is_writable($this->cache_dir))
		{
			// E_USER_ERROR - not using language entry - intended.
			trigger_error('Unable to remove files within ' . $this->cache_dir . '. Please check directory permissions.', E_USER_ERROR);
		}

		return @unlink($filename);
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
		catch (\Exception $e)
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
}
