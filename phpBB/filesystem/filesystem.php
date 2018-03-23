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

namespace phpbb\filesystem;

use phpbb\filesystem\exception\filesystem_exception;

/**
 * A class with various functions that are related to paths, files and the filesystem
 */
class filesystem implements filesystem_interface
{
	/**
	 * Store some information about file ownership for phpBB's chmod function
	 *
	 * @var array
	 */
	protected $chmod_info;

	/**
	 * Stores current working directory
	 *
	 * @var string|bool		current working directory or false if it cannot be recovered
	 */
	protected $working_directory;

	/**
	 * Symfony's Filesystem component
	 *
	 * @var \Symfony\Component\Filesystem\Filesystem
	 */
	protected $symfony_filesystem;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->chmod_info			= array();
		$this->symfony_filesystem	= new \Symfony\Component\Filesystem\Filesystem();
		$this->working_directory	= null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function chgrp($files, $group, $recursive = false)
	{
		try
		{
			$this->symfony_filesystem->chgrp($files, $group, $recursive);
		}
		catch (\Symfony\Component\Filesystem\Exception\IOException $e)
		{
			// Try to recover filename
			// By the time this is written that is at the end of the message
			$error = trim($e->getMessage());
			$file = substr($error, strrpos($error, ' '));

			throw new filesystem_exception('CANNOT_CHANGE_FILE_GROUP', $file, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function chmod($files, $perms = null, $recursive = false, $force_chmod_link = false)
	{
		if (is_null($perms))
		{
			// Default to read permission for compatibility reasons
			$perms = self::CHMOD_READ;
		}

		// Check if we got a permission flag
		if ($perms > self::CHMOD_ALL)
		{
			$file_perm = $perms;

			// Extract permissions
			//$owner = ($file_perm >> 6) & 7; // This will be ignored
			$group = ($file_perm >> 3) & 7;
			$other = ($file_perm >> 0) & 7;

			// Does any permissions provided? if so we add execute bit for directories
			$group = ($group !== 0) ? ($group | self::CHMOD_EXECUTE) : $group;
			$other = ($other !== 0) ? ($other | self::CHMOD_EXECUTE) : $other;

			// Compute directory permissions
			$dir_perm = (self::CHMOD_ALL << 6) + ($group << 3) + ($other << 3);
		}
		else
		{
			// Add execute bit to owner if execute bit is among perms
			$owner_perm	= (self::CHMOD_READ | self::CHMOD_WRITE) | ($perms & self::CHMOD_EXECUTE);
			$file_perm	= ($owner_perm << 6) + ($perms << 3) + ($perms << 0);

			// Compute directory permissions
			$perm = ($perms !== 0) ? ($perms | self::CHMOD_EXECUTE) : $perms;
			$dir_perm = (($owner_perm | self::CHMOD_EXECUTE) << 6) + ($perm << 3) + ($perm << 0);
		}

		// Symfony's filesystem component does not support extra execution flags on directories
		// so we need to implement it again
		foreach ($this->to_iterator($files) as $file)
		{
			if ($recursive && is_dir($file) && !is_link($file))
			{
				$this->chmod(new \FilesystemIterator($file), $perms, true);
			}

			// Don't chmod links as mostly those require 0777 and that cannot be changed
			if (is_dir($file) || (is_link($file) && $force_chmod_link))
			{
				if (true !== @chmod($file, $dir_perm))
				{
					throw new filesystem_exception('CANNOT_CHANGE_FILE_PERMISSIONS', $file,  array());
				}
			}
			else if (is_file($file))
			{
				if (true !== @chmod($file, $file_perm))
				{
					throw new filesystem_exception('CANNOT_CHANGE_FILE_PERMISSIONS', $file,  array());
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function chown($files, $user, $recursive = false)
	{
		try
		{
			$this->symfony_filesystem->chown($files, $user, $recursive);
		}
		catch (\Symfony\Component\Filesystem\Exception\IOException $e)
		{
			// Try to recover filename
			// By the time this is written that is at the end of the message
			$error = trim($e->getMessage());
			$file = substr($error, strrpos($error, ' '));

			throw new filesystem_exception('CANNOT_CHANGE_FILE_GROUP', $file, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function clean_path($path)
	{
		$exploded = explode('/', $path);
		$filtered = array();
		foreach ($exploded as $part)
		{
			if ($part === '.' && !empty($filtered))
			{
				continue;
			}

			if ($part === '..' && !empty($filtered) && $filtered[count($filtered) - 1] !== '.' && $filtered[count($filtered) - 1] !== '..')
			{
				array_pop($filtered);
			}
			else
			{
				$filtered[] = $part;
			}
		}
		$path = implode('/', $filtered);
		return $path;
	}

	/**
	 * {@inheritdoc}
	 */
	public function copy($origin_file, $target_file, $override = false)
	{
		try
		{
			$this->symfony_filesystem->copy($origin_file, $target_file, $override);
		}
		catch (\Symfony\Component\Filesystem\Exception\IOException $e)
		{
			throw new filesystem_exception('CANNOT_COPY_FILES', '', array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function dump_file($filename, $content)
	{
		try
		{
			$this->symfony_filesystem->dumpFile($filename, $content);
		}
		catch (\Symfony\Component\Filesystem\Exception\IOException $e)
		{
			throw new filesystem_exception('CANNOT_DUMP_FILE', $filename, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists($files)
	{
		return $this->symfony_filesystem->exists($files);
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_absolute_path($path)
	{
		return (isset($path[0]) && $path[0] === '/' || preg_match('#^[a-z]:[/\\\]#i', $path)) ? true : false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_readable($files, $recursive = false)
	{
		foreach ($this->to_iterator($files) as $file)
		{
			if ($recursive && is_dir($file) && !is_link($file))
			{
				if (!$this->is_readable(new \FilesystemIterator($file), true))
				{
					return false;
				}
			}

			if (!is_readable($file))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_writable($files, $recursive = false)
	{
		if (defined('PHP_WINDOWS_VERSION_MAJOR') || !function_exists('is_writable'))
		{
			foreach ($this->to_iterator($files) as $file)
			{
				if ($recursive && is_dir($file) && !is_link($file))
				{
					if (!$this->is_writable(new \FilesystemIterator($file), true))
					{
						return false;
					}
				}

				if (!$this->phpbb_is_writable($file))
				{
					return false;
				}
			}
		}
		else
		{
			// use built in is_writable
			foreach ($this->to_iterator($files) as $file)
			{
				if ($recursive && is_dir($file) && !is_link($file))
				{
					if (!$this->is_writable(new \FilesystemIterator($file), true))
					{
						return false;
					}
				}

				if (!is_writable($file))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function make_path_relative($end_path, $start_path)
	{
		return $this->symfony_filesystem->makePathRelative($end_path, $start_path);
	}

	/**
	 * {@inheritdoc}
	 */
	public function mirror($origin_dir, $target_dir, \Traversable $iterator = null, $options = array())
	{
		try
		{
			$this->symfony_filesystem->mirror($origin_dir, $target_dir, $iterator, $options);
		}
		catch (\Symfony\Component\Filesystem\Exception\IOException $e)
		{
			$msg = $e->getMessage();
			$filename = substr($msg, strpos($msg, '"'), strrpos($msg, '"'));

			throw new filesystem_exception('CANNOT_MIRROR_DIRECTORY', $filename, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function mkdir($dirs, $mode = 0777)
	{
		try
		{
			$this->symfony_filesystem->mkdir($dirs, $mode);
		}
		catch (\Symfony\Component\Filesystem\Exception\IOException $e)
		{
			$msg = $e->getMessage();
			$filename = substr($msg, strpos($msg, '"'), strrpos($msg, '"'));

			throw new filesystem_exception('CANNOT_CREATE_DIRECTORY', $filename, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function phpbb_chmod($files, $perms = null, $recursive = false, $force_chmod_link = false)
	{
		if (is_null($perms))
		{
			// Default to read permission for compatibility reasons
			$perms = self::CHMOD_READ;
		}

		if (empty($this->chmod_info))
		{
			if (!function_exists('fileowner') || !function_exists('filegroup'))
			{
				$this->chmod_info['process'] = false;
			}
			else
			{
				$common_php_owner	= @fileowner(__FILE__);
				$common_php_group	= @filegroup(__FILE__);

				// And the owner and the groups PHP is running under.
				$php_uid	= (function_exists('posic_getuid')) ? @posix_getuid() : false;
				$php_gids	= (function_exists('posix_getgroups')) ? @posix_getgroups() : false;

				// If we are unable to get owner/group, then do not try to set them by guessing
				if (!$php_uid || empty($php_gids) || !$common_php_owner || !$common_php_group)
				{
					$this->chmod_info['process'] = false;
				}
				else
				{
					$this->chmod_info = array(
						'process'		=> true,
						'common_owner'	=> $common_php_owner,
						'common_group'	=> $common_php_group,
						'php_uid'		=> $php_uid,
						'php_gids'		=> $php_gids,
					);
				}
			}
		}

		if ($this->chmod_info['process'])
		{
			try
			{
				foreach ($this->to_iterator($files) as $file)
				{
					$file_uid = @fileowner($file);
					$file_gid = @filegroup($file);

					// Change owner
					if ($file_uid !== $this->chmod_info['common_owner'])
					{
						$this->chown($file, $this->chmod_info['common_owner'], $recursive);
					}

					// Change group
					if ($file_gid !== $this->chmod_info['common_group'])
					{
						$this->chgrp($file, $this->chmod_info['common_group'], $recursive);
					}

					clearstatcache();
					$file_uid = @fileowner($file);
					$file_gid = @filegroup($file);
				}
			}
			catch (filesystem_exception $e)
			{
				$this->chmod_info['process'] = false;
			}
		}

		// Still able to process?
		if ($this->chmod_info['process'])
		{
			if ($file_uid === $this->chmod_info['php_uid'])
			{
				$php = 'owner';
			}
			else if (in_array($file_gid, $this->chmod_info['php_gids']))
			{
				$php = 'group';
			}
			else
			{
				// Since we are setting the everyone bit anyway, no need to do expensive operations
				$this->chmod_info['process'] = false;
			}
		}

		// We are not able to determine or change something
		if (!$this->chmod_info['process'])
		{
			$php = 'other';
		}

		switch ($php)
		{
			case 'owner':
				try
				{
					$this->chmod($files, $perms, $recursive, $force_chmod_link);
					clearstatcache();
					if ($this->is_readable($files) && $this->is_writable($files))
					{
						break;
					}
				}
				catch (filesystem_exception $e)
				{
					// Do nothing
				}
			case 'group':
				try
				{
					$this->chmod($files, $perms, $recursive, $force_chmod_link);
					clearstatcache();
					if ((!($perms & self::CHMOD_READ) || $this->is_readable($files, $recursive)) && (!($perms & self::CHMOD_WRITE) || $this->is_writable($files, $recursive)))
					{
						break;
					}
				}
				catch (filesystem_exception $e)
				{
					// Do nothing
				}
			case 'other':
			default:
				$this->chmod($files, $perms, $recursive, $force_chmod_link);
			break;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function realpath($path)
	{
		if (!function_exists('realpath'))
		{
			return $this->phpbb_own_realpath($path);
		}

		$realpath = realpath($path);

		// Strangely there are provider not disabling realpath but returning strange values. :o
		// We at least try to cope with them.
		if ((!$this->is_absolute_path($path) && $realpath === $path) || $realpath === false)
		{
			return $this->phpbb_own_realpath($path);
		}

		// Check for DIRECTORY_SEPARATOR at the end (and remove it!)
		if (substr($realpath, -1) === DIRECTORY_SEPARATOR)
		{
			$realpath = substr($realpath, 0, -1);
		}

		return $realpath;
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($files)
	{
		try
		{
			$this->symfony_filesystem->remove($files);
		}
		catch (\Symfony\Component\Filesystem\Exception\IOException $e)
		{
			// Try to recover filename
			// By the time this is written that is at the end of the message
			$error = trim($e->getMessage());
			$file = substr($error, strrpos($error, ' '));

			throw new filesystem_exception('CANNOT_DELETE_FILES', $file, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function rename($origin, $target, $overwrite = false)
	{
		try
		{
			$this->symfony_filesystem->rename($origin, $target, $overwrite);
		}
		catch (\Symfony\Component\Filesystem\Exception\IOException $e)
		{
			$msg = $e->getMessage();
			$filename = substr($msg, strpos($msg, '"'), strrpos($msg, '"'));

			throw new filesystem_exception('CANNOT_RENAME_FILE', $filename, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function symlink($origin_dir, $target_dir, $copy_on_windows = false)
	{
		try
		{
			$this->symfony_filesystem->symlink($origin_dir, $target_dir, $copy_on_windows);
		}
		catch (\Symfony\Component\Filesystem\Exception\IOException $e)
		{
			throw new filesystem_exception('CANNOT_CREATE_SYMLINK', $origin_dir, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function touch($files, $time = null, $access_time = null)
	{
		try
		{
			$this->symfony_filesystem->touch($files, $time, $access_time);
		}
		catch (\Symfony\Component\Filesystem\Exception\IOException $e)
		{
			// Try to recover filename
			// By the time this is written that is at the end of the message
			$error = trim($e->getMessage());
			$file = substr($error, strrpos($error, ' '));

			throw new filesystem_exception('CANNOT_TOUCH_FILES', $file, array(), $e);
		}
	}

	/**
	 * phpBB's implementation of is_writable
	 *
	 * @todo Investigate if is_writable is still buggy
	 *
	 * @param string	$file	file/directory to check if writable
	 *
	 * @return bool	true if the given path is writable
	 */
	protected function phpbb_is_writable($file)
	{
		if (file_exists($file))
		{
			// Canonicalise path to absolute path
			$file = $this->realpath($file);

			if (is_dir($file))
			{
				// Test directory by creating a file inside the directory
				$result = @tempnam($file, 'i_w');

				if (is_string($result) && file_exists($result))
				{
					unlink($result);

					// Ensure the file is actually in the directory (returned realpathed)
					return (strpos($result, $file) === 0) ? true : false;
				}
			}
			else
			{
				$handle = @fopen($file, 'c');

				if (is_resource($handle))
				{
					fclose($handle);
					return true;
				}
			}
		}
		else
		{
			// file does not exist test if we can write to the directory
			$dir = dirname($file);

			if (file_exists($dir) && is_dir($dir) && $this->phpbb_is_writable($dir))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Try to resolve real path when PHP's realpath failes to do so
	 *
	 * @param string	$path
	 * @return bool|string
	 */
	protected function phpbb_own_realpath($path)
	{
		// Replace all directory separators with '/'
		$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

		$is_absolute_path = false;
		$path_prefix = '';

		if ($this->is_absolute_path($path))
		{
			$is_absolute_path = true;
		}
		else
		{
			// Resolve working directory and store it
			if (is_null($this->working_directory))
			{
				if (function_exists('getcwd'))
				{
					$this->working_directory = str_replace(DIRECTORY_SEPARATOR, '/', getcwd());
				}

				//
				// From this point on we really just guessing
				// If chdir were called we screwed
				//
				else if (function_exists('debug_backtrace'))
				{
					$call_stack = debug_backtrace(0);
					$this->working_directory = str_replace(DIRECTORY_SEPARATOR, '/', dirname($call_stack[count($call_stack) - 1]['file']));
				}
				else
				{
					//
					// Assuming that the working directory is phpBB root
					// we could use this as a fallback, when phpBB will use controllers
					// everywhere this will be a safe assumption
					//
					//$dir_parts = explode(DIRECTORY_SEPARATOR, __DIR__);
					//$namespace_parts = explode('\\', trim(__NAMESPACE__, '\\'));

					//$namespace_part_count = count($namespace_parts);

					// Check if we still loading from root
					//if (array_slice($dir_parts, -$namespace_part_count) === $namespace_parts)
					//{
					//	$this->working_directory = implode('/', array_slice($dir_parts, 0, -$namespace_part_count));
					//}
					//else
					//{
					//	$this->working_directory = false;
					//}

					$this->working_directory = false;
				}
			}

			if ($this->working_directory !== false)
			{
				$is_absolute_path = true;
				$path = $this->working_directory . '/' . $path;
			}
		}

		if ($is_absolute_path)
		{
			if (defined('PHP_WINDOWS_VERSION_MAJOR'))
			{
				$path_prefix = $path[0] . ':';
				$path = substr($path, 2);
			}
			else
			{
				$path_prefix = '';
			}
		}

		$resolved_path = $this->resolve_path($path, $path_prefix, $is_absolute_path);
		if ($resolved_path === false)
		{
			return false;
		}

		if (!@file_exists($resolved_path) || (!@is_dir($resolved_path . '/') && !is_file($resolved_path)))
		{
			return false;
		}

		// Return OS specific directory separators
		$resolved = str_replace('/', DIRECTORY_SEPARATOR, $resolved_path);

		// Check for DIRECTORY_SEPARATOR at the end (and remove it!)
		if (substr($resolved, -1) === DIRECTORY_SEPARATOR)
		{
			return substr($resolved, 0, -1);
		}

		return $resolved;
	}

	/**
	 * Convert file(s) to \Traversable object
	 *
	 * This is the same function as Symfony's toIterator, but that is private
	 * so we cannot use it.
	 *
	 * @param string|array|\Traversable	$files	filename/list of filenames
	 * @return \Traversable
	 */
	protected function to_iterator($files)
	{
		if (!$files instanceof \Traversable)
		{
			$files = new \ArrayObject(is_array($files) ? $files : array($files));
		}

		return $files;
	}

	/**
	 * Try to resolve symlinks in path
	 *
	 * @param string	$path			The path to resolve
	 * @param string	$prefix			The path prefix (on windows the drive letter)
	 * @param bool 		$absolute		Whether or not the path is absolute
	 * @param bool		$return_array	Whether or not to return path parts
	 *
	 * @return string|array|bool	returns the resolved path or an array of parts of the path if $return_array is true
	 * 								or false if path cannot be resolved
	 */
	protected function resolve_path($path, $prefix = '', $absolute = false, $return_array = false)
	{
		if ($return_array)
		{
			$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
		}

		trim ($path, '/');
		$path_parts = explode('/', $path);
		$resolved = array();
		$resolved_path = $prefix;
		$file_found = false;

		foreach ($path_parts as $path_part)
		{
			if ($file_found)
			{
				return false;
			}

			if (empty($path_part) || ($path_part === '.' && ($absolute || !empty($resolved))))
			{
				continue;
			}
			else if ($absolute && $path_part === '..')
			{
				if (empty($resolved))
				{
					// No directories above root
					return false;
				}

				array_pop($resolved);
				$resolved_path = false;
			}
			else if ($path_part === '..' && !empty($resolved) && !in_array($resolved[count($resolved) - 1], array('.', '..')))
			{
				array_pop($resolved);
				$resolved_path = false;
			}
			else
			{
				if ($resolved_path === false)
				{
					if (empty($resolved))
					{
						$resolved_path = ($absolute) ? $prefix . '/' . $path_part : $path_part;
					}
					else
					{
						$tmp_array = $resolved;
						if ($absolute)
						{
							array_unshift($tmp_array, $prefix);
						}

						$resolved_path = implode('/', $tmp_array);
					}
				}

				$current_path = $resolved_path . '/' . $path_part;

				// Resolve symlinks
				if (is_link($current_path))
				{
					if (!function_exists('readlink'))
					{
						return false;
					}

					$link = readlink($current_path);

					// Is link has an absolute path in it?
					if ($this->is_absolute_path($link))
					{
						if (defined('PHP_WINDOWS_VERSION_MAJOR'))
						{
							$prefix = $link[0] . ':';
							$link = substr($link, 2);
						}
						else
						{
							$prefix = '';
						}

						$resolved = $this->resolve_path($link, $prefix, true, true);
						$absolute = true;
					}
					else
					{
						$resolved = $this->resolve_path($resolved_path . '/' . $link, $prefix, $absolute, true);
					}

					if (!$resolved)
					{
						return false;
					}

					$resolved_path = false;
				}
				else if (is_dir($current_path . '/'))
				{
					$resolved[] = $path_part;
					$resolved_path = $current_path;
				}
				else if (is_file($current_path))
				{
					$resolved[] = $path_part;
					$resolved_path = $current_path;
					$file_found = true;
				}
				else
				{
					return false;
				}
			}
		}

		// If at the end of the path there were a .. or .
		// we need to build the path again.
		// Only doing this when a string is expected in return
		if ($resolved_path === false && $return_array === false)
		{
			if (empty($resolved))
			{
				$resolved_path = ($absolute) ? $prefix . '/' : './';
			}
			else
			{
				$tmp_array = $resolved;
				if ($absolute)
				{
					array_unshift($tmp_array, $prefix);
				}

				$resolved_path = implode('/', $tmp_array);
			}
		}

		return ($return_array) ? $resolved : $resolved_path;
	}
}
