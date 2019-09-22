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

use Symfony\Component\Filesystem\Filesystem as symfony_filesystem;

class helper
{
	/**
	* @var \Symfony\Component\Filesystem\Filesystem
	*/
	protected static $symfony_filesystem;

	/**
	 * Eliminates useless . and .. components from specified path.
	 *
	 * @param string $path Path to clean
	 *
	 * @return string Cleaned path
	 */
	public static function clean_path($path)
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
	 * Checks if a path is absolute or not
	 *
	 * @param string	$path	Path to check
	 *
	 * @return	bool	true if the path is absolute, false otherwise
	 */
	public static function is_absolute_path($path)
	{
		return (isset($path[0]) && $path[0] === '/' || preg_match('#^[a-z]:[/\\\]#i', $path)) ? true : false;
	}

	/**
	 * Try to resolve real path when PHP's realpath failes to do so
	 *
	 * @param string	$path
	 * @return bool|string
	 */
	protected static function phpbb_own_realpath($path)
	{
		// Replace all directory separators with '/'
		$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

		$is_absolute_path = false;
		$path_prefix = '';

		if (self::is_absolute_path($path))
		{
			$is_absolute_path = true;
		}
		else
		{
			if (function_exists('getcwd'))
			{
				$working_directory = str_replace(DIRECTORY_SEPARATOR, '/', getcwd());
			}

			//
			// From this point on we really just guessing
			// If chdir were called we screwed
			//
			else if (function_exists('debug_backtrace'))
			{
				$call_stack = debug_backtrace(0);
				$working_directory = str_replace(DIRECTORY_SEPARATOR, '/', dirname($call_stack[count($call_stack) - 1]['file']));
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
				//	$working_directory = implode('/', array_slice($dir_parts, 0, -$namespace_part_count));
				//}
				//else
				//{
				//	$working_directory = false;
				//}

				$working_directory = false;
			}

			if ($working_directory !== false)
			{
				$is_absolute_path = true;
				$path = $working_directory . '/' . $path;
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

		$resolved_path = self::resolve_path($path, $path_prefix, $is_absolute_path);
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
	public static function realpath($path)
	{
		if (!function_exists('realpath'))
		{
			return self::phpbb_own_realpath($path);
		}

		$realpath = realpath($path);

		// Strangely there are provider not disabling realpath but returning strange values. :o
		// We at least try to cope with them.
		if ((!self::is_absolute_path($path) && $realpath === $path) || $realpath === false)
		{
			return self::phpbb_own_realpath($path);
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
	public static function make_path_relative($end_path, $start_path)
	{
		return self::get_symfony_filesystem()->makePathRelative($end_path, $start_path);
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
	public static function resolve_path($path, $prefix = '', $absolute = false, $return_array = false)
	{
		if ($return_array)
		{
			$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
		}

		trim($path, '/');
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
				if (@is_link($current_path))
				{
					if (!function_exists('readlink'))
					{
						return false;
					}

					$link = readlink($current_path);

					// Is link has an absolute path in it?
					if (self::is_absolute_path($link))
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

						$resolved = self::resolve_path($link, $prefix, true, true);
						$absolute = true;
					}
					else
					{
						$resolved = self::resolve_path($resolved_path . '/' . $link, $prefix, $absolute, true);
					}

					if (!$resolved)
					{
						return false;
					}

					$resolved_path = false;
				}
				else if (@is_dir($current_path . '/'))
				{
					$resolved[] = $path_part;
					$resolved_path = $current_path;
				}
				else if (@is_file($current_path))
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

		return $return_array ? $resolved : $resolved_path;
	}

	/**
	 * Get an instance of symfony's filesystem object.
	 *
	 * @return \Symfony\Component\Filesystem\Filesystem	Symfony filesystem
	 */
	protected static function get_symfony_filesystem()
	{
		if (self::$symfony_filesystem === null)
		{
			self::$symfony_filesystem = new symfony_filesystem();
		}

		return self::$symfony_filesystem;
	}
}
