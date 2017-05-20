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

namespace phpbb\storage;

class storage
{
	/**
	 * Eliminates useless . and .. components from specified path.
	 *
	 * @param string $path Path to clean
	 *
	 * @return string Cleaned path
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

			if ($part === '..' && !empty($filtered) && $filtered[sizeof($filtered) - 1] !== '.' && $filtered[sizeof($filtered) - 1] !== '..')
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
	 * Checks if a path is absolute or not
	 *
	 * @param string	$path	Path to check
	 *
	 * @return	bool	true if the path is absolute, false otherwise
	 */
	public function is_absolute_path($path)
	{
		return (isset($path[0]) && $path[0] === '/' || preg_match('#^[a-z]:[/\\\]#i', $path)) ? true : false;
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
					$this->working_directory = str_replace(DIRECTORY_SEPARATOR, '/', dirname($call_stack[sizeof($call_stack) - 1]['file']));
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

					//$namespace_part_count = sizeof($namespace_parts);

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
	 * A wrapper for PHP's realpath
	 *
	 * Try to resolve realpath when PHP's realpath is not available, or
	 * known to be buggy.
	 *
	 * @param string	$path	Path to resolve
	 *
	 * @return string	Resolved path
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
	 * Given an existing path, convert it to a path relative to a given starting path
	 *
	 * @param string $end_path		Absolute path of target
	 * @param string $start_path	Absolute path where traversal begins
	 *
	 * @return string Path of target relative to starting path
	 */
	public function make_path_relative($end_path, $start_path)
	{
		$symfony_filesystem = new \Symfony\Component\Filesystem\Filesystem();
		return $symfony_filesystem->makePathRelative($end_path, $start_path);
	}

}
