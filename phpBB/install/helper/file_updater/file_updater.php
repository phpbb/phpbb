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

namespace phpbb\install\helper\file_updater;

use phpbb\filesystem\exception\filesystem_exception;
use phpbb\filesystem\filesystem;
use phpbb\install\exception\file_updater_failure_exception;

/**
 * File updater for direct filesystem access
 */
class file_updater implements file_updater_interface
{
	/**
	 * @var filesystem
	 */
	protected $filesystem;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param filesystem	$filesystem
	 * @param string		$phpbb_root_path
	 */
	public function __construct(filesystem $filesystem, $phpbb_root_path)
	{
		$this->filesystem		= $filesystem;
		$this->phpbb_root_path	= $phpbb_root_path;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws file_updater_failure_exception	When the file is not writable
	 * @throws filesystem_exception				When the filesystem class fails
	 */
	public function delete_file($path_to_file)
	{
		$this->filesystem->remove($this->phpbb_root_path . $path_to_file);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws file_updater_failure_exception	When the file is not writable
	 * @throws filesystem_exception				When the filesystem class fails
	 */
	public function create_new_file($path_to_file_to_create, $source, $create_from_content = false)
	{
		$path_to_file_to_create = $this->phpbb_root_path . $path_to_file_to_create;

		$dir = dirname($path_to_file_to_create);
		if (!$this->filesystem->exists($dir))
		{
			$this->make_dir($dir);
		}

		$original_dir_perms = false;

		if (!$this->filesystem->is_writable($dir))
		{
			// Extract last 9 bits we actually need
			$original_dir_perms = @fileperms($dir) & 511;
			$this->filesystem->phpbb_chmod($dir, filesystem::CHMOD_ALL);
		}

		if (!$create_from_content)
		{
			try
			{
				$this->filesystem->copy($source, $path_to_file_to_create);
			}
			catch (filesystem_exception $e)
			{
				$this->write_file($path_to_file_to_create, $source, $create_from_content);
			}
		}
		else
		{
			$this->write_file($path_to_file_to_create, $source, $create_from_content);
		}

		if ($original_dir_perms !== false)
		{
			$this->filesystem->phpbb_chmod($dir, $original_dir_perms);
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws file_updater_failure_exception	When the file is not writable
	 * @throws filesystem_exception				When the filesystem class fails
	 */
	public function update_file($path_to_file_to_update, $source, $create_from_content = false)
	{
		$path_to_file_to_update = $this->phpbb_root_path . $path_to_file_to_update;
		$original_file_perms = false;

		// Maybe necessary for binary files
		$dir = dirname($path_to_file_to_update);
		if (!$this->filesystem->exists($dir))
		{
			$this->make_dir($dir);
		}

		if (!$this->filesystem->is_writable($path_to_file_to_update))
		{
			// Extract last 9 bits we actually need
			$original_file_perms = @fileperms($path_to_file_to_update) & 511;
			$this->filesystem->phpbb_chmod($path_to_file_to_update, filesystem::CHMOD_WRITE);
		}

		if (!$create_from_content)
		{
			try
			{
				$this->filesystem->copy($source, $path_to_file_to_update, true);
			}
			catch (filesystem_exception $e)
			{
				$this->write_file($path_to_file_to_update, $source, $create_from_content);
			}
		}
		else
		{
			$this->write_file($path_to_file_to_update, $source, $create_from_content);
		}

		if ($original_file_perms !== false)
		{
			$this->filesystem->phpbb_chmod($path_to_file_to_update, $original_file_perms);
		}
	}

	/**
	 * Creates directory structure
	 *
	 * @param string	$path	Path to the directory where the file should be placed (and non-existent)
	 */
	private function make_dir($path)
	{
		if (is_dir($path))
		{
			return;
		}

		$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
		$this->filesystem->mkdir($path, 493); // 493 === 0755
	}

	/**
	 * Fallback function for file writing
	 *
	 * @param string		$path_to_file			Path to the file's location
	 * @param string		$source					Path to file to copy or string with the new file's content
	 * @param bool|false	$create_from_content	Whether or not to use $source as the content, false by default
	 *
	 * @throws file_updater_failure_exception	When the file is not writable
	 */
	private function write_file($path_to_file, $source, $create_from_content = false)
	{
		if (!$create_from_content)
		{
			$source = @file_get_contents($source);
		}

		$file_pointer = @fopen($path_to_file, 'w');

		if (!is_resource($file_pointer))
		{
			throw new file_updater_failure_exception();
		}

		@fwrite($file_pointer, $source);
		@fclose($file_pointer);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_method_name()
	{
		return 'direct_file';
	}
}
