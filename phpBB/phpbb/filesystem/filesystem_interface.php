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

/**
 * Interface for phpBB's filesystem service
 */
interface filesystem_interface
{
	/**
	 * chmod all permissions flag
	 *
	 * @var int
	 */
	const CHMOD_ALL = 7;

	/**
	 * chmod read permissions flag
	 *
	 * @var int
	 */
	const CHMOD_READ = 4;

	/**
	 * chmod write permissions flag
	 *
	 * @var int
	 */
	const CHMOD_WRITE = 2;

	/**
	 * chmod execute permissions flag
	 *
	 * @var int
	 */
	const CHMOD_EXECUTE = 1;

	/**
	 * Change owner group of files/directories
	 *
	 * @param string|array|\Traversable	$files		The file(s)/directorie(s) to change group
	 * @param string					$group		The group that should own the files/directories
	 * @param bool 						$recursive	If the group should be changed recursively
	 * @throws \phpbb\filesystem\exception\filesystem_exception	the filename which triggered the error can be
	 * 															retrieved by filesystem_exception::get_filename()
	 */
	public function chgrp($files, $group, $recursive = false);

	/**
	 * Global function for chmodding directories and files for internal use
	 *
	 * The function accepts filesystem_interface::CHMOD_ flags in the permission argument
	 * or the user can specify octal values (or any integer if it makes sense). All directories will have
	 * an execution bit appended, if the user group (owner, group or other) has any bit specified.
	 *
	 * @param string|array|\Traversable	$files				The file/directory to be chmodded
	 * @param int						$perms				Permissions to set
	 * @param bool						$recursive			If the permissions should be changed recursively
	 * @param bool						$force_chmod_link	Try to apply permissions to symlinks as well
	 *
	 * @throws \phpbb\filesystem\exception\filesystem_exception	the filename which triggered the error can be
	 * 															retrieved by filesystem_exception::get_filename()
	 */
	public function chmod($files, $perms = null, $recursive = false, $force_chmod_link = false);

	/**
	 * Change owner group of files/directories
	 *
	 * @param string|array|\Traversable	$files		The file(s)/directorie(s) to change group
	 * @param string					$user		The owner user name
	 * @param bool 						$recursive	Whether change the owner recursively or not
	 *
	 * @throws \phpbb\filesystem\exception\filesystem_exception	the filename which triggered the error can be
	 * 															retrieved by filesystem_exception::get_filename()
	 */
	public function chown($files, $user, $recursive = false);

	/**
	 * Eliminates useless . and .. components from specified path.
	 *
	 * @param string $path Path to clean
	 *
	 * @return string Cleaned path
	 */
	public function clean_path($path);

	/**
	 * Copies a file.
	 *
	 * This method only copies the file if the origin file is newer than the target file.
	 *
	 * By default, if the target already exists, it is not overridden.
	 *
	 * @param string	$origin_file	The original filename
	 * @param string	$target_file	The target filename
	 * @param bool		$override		Whether to override an existing file or not
	 *
	 * @throws \phpbb\filesystem\exception\filesystem_exception When the file cannot be copied
	 */
	public function copy($origin_file, $target_file, $override = false);

	/**
	 * Atomically dumps content into a file.
	 *
	 * @param string	$filename	The file to be written to.
	 * @param string	$content	The data to write into the file.
	 *
	 * @throws \phpbb\filesystem\exception\filesystem_exception When the file cannot be written
	 */
	public function dump_file($filename, $content);

	/**
	 * Checks the existence of files or directories.
	 *
	 * @param string|array|\Traversable	$files	files/directories to check
	 *
	 * @return bool	Returns true if all files/directories exist, false otherwise
	 */
	public function exists($files);

	/**
	 * Checks if a path is absolute or not
	 *
	 * @param string	$path	Path to check
	 *
	 * @return	bool	true if the path is absolute, false otherwise
	 */
	public function is_absolute_path($path);

	/**
	 * Checks if files/directories are readable
	 *
	 * @param string|array|\Traversable	$files		files/directories to check
	 * @param bool						$recursive	Whether or not directories should be checked recursively
	 *
	 * @return bool True when the files/directories are readable, otherwise false.
	 */
	public function is_readable($files, $recursive = false);

	/**
	 * Test if a file/directory is writable
	 *
	 * @param string|array|\Traversable	$files		files/directories to perform write test on
	 * @param bool						$recursive	Whether or not directories should be checked recursively
	 *
	 * @return bool True when the files/directories are writable, otherwise false.
	 */
	public function is_writable($files, $recursive = false);

	/**
	 * Given an existing path, convert it to a path relative to a given starting path
	 *
	 * @param string $end_path		Absolute path of target
	 * @param string $start_path	Absolute path where traversal begins
	 *
	 * @return string Path of target relative to starting path
	 */
	public function make_path_relative($end_path, $start_path);

	/**
	 * Mirrors a directory to another.
	 *
	 * @param string		$origin_dir	The origin directory
	 * @param string		$target_dir	The target directory
	 * @param \Traversable	$iterator	A Traversable instance
	 * @param array			$options	An array of boolean options
	 *									Valid options are:
	 *										- $options['override'] Whether to override an existing file on copy or not (see copy())
	 *										- $options['copy_on_windows'] Whether to copy files instead of links on Windows (see symlink())
	 *										- $options['delete'] Whether to delete files that are not in the source directory (defaults to false)
	 *
	 * @throws \phpbb\filesystem\exception\filesystem_exception When the file cannot be copied.
	 * 															The filename which triggered the error can be
	 * 															retrieved by filesystem_exception::get_filename()
	 */
	public function mirror($origin_dir, $target_dir, \Traversable $iterator = null, $options = array());

	/**
	 * Creates a directory recursively.
	 *
	 * @param string|array|\Traversable	$dirs	The directory path
	 * @param int						$mode	The directory mode
	 *
	 * @throws \phpbb\filesystem\exception\filesystem_exception On any directory creation failure
	 * 															The filename which triggered the error can be
	 * 															retrieved by filesystem_exception::get_filename()
	 */
	public function mkdir($dirs, $mode = 0777);

	/**
	 * Global function for chmodding directories and files for internal use
	 *
	 * This function determines owner and group whom the file belongs to and user and group of PHP and then set safest possible file permissions.
	 * The function determines owner and group from common.php file and sets the same to the provided file.
	 * The function uses bit fields to build the permissions.
	 * The function sets the appropiate execute bit on directories.
	 *
	 * Supported constants representing bit fields are:
	 *
	 * filesystem_interface::CHMOD_ALL - all permissions (7)
	 * filesystem_interface::CHMOD_READ - read permission (4)
	 * filesystem_interface::CHMOD_WRITE - write permission (2)
	 * filesystem_interface::CHMOD_EXECUTE - execute permission (1)
	 *
	 * NOTE: The function uses POSIX extension and fileowner()/filegroup() functions. If any of them is disabled, this function tries to build proper permissions, by calling is_readable() and is_writable() functions.
	 *
	 * @param string|array|\Traversable	$file				The file/directory to be chmodded
	 * @param int						$perms				Permissions to set
	 * @param bool						$recursive			If the permissions should be changed recursively
	 * @param bool						$force_chmod_link	Try to apply permissions to symlinks as well
	 *
	 * @throws \phpbb\filesystem\exception\filesystem_exception	the filename which triggered the error can be
	 * 															retrieved by filesystem_exception::get_filename()
	 */
	public function phpbb_chmod($file, $perms = null, $recursive = false, $force_chmod_link = false);

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
	public function realpath($path);

	/**
	 * Removes files or directories.
	 *
	 * @param string|array|\Traversable	$files	A filename, an array of files, or a \Traversable instance to remove
	 *
	 * @throws \phpbb\filesystem\exception\filesystem_exception When removal fails.
	 * 															The filename which triggered the error can be
	 * 															retrieved by filesystem_exception::get_filename()
	 */
	public function remove($files);

	/**
	 * Renames a file or a directory.
	 *
	 * @param string	$origin		The origin filename or directory
	 * @param string	$target		The new filename or directory
	 * @param bool		$overwrite	Whether to overwrite the target if it already exists
	 *
	 * @throws \phpbb\filesystem\exception\filesystem_exception	When target file or directory already exists,
	 * 															or origin cannot be renamed.
	 */
	public function rename($origin, $target, $overwrite = false);

	/**
	 * Creates a symbolic link or copy a directory.
	 *
	 * @param string	$origin_dir			The origin directory path
	 * @param string	$target_dir			The symbolic link name
	 * @param bool		$copy_on_windows	Whether to copy files if on Windows
	 *
	 * @throws \phpbb\filesystem\exception\filesystem_exception When symlink fails
	 */
	public function symlink($origin_dir, $target_dir, $copy_on_windows = false);

	/**
	 * Sets access and modification time of file.
	 *
	 * @param string|array|\Traversable	$files			A filename, an array of files, or a \Traversable instance to create
	 * @param int						$time			The touch time as a Unix timestamp
	 * @param int						$access_time	The access time as a Unix timestamp
	 *
	 * @throws \phpbb\filesystem\exception\filesystem_exception	When touch fails
	 */
	public function touch($files, $time = null, $access_time = null);
}
