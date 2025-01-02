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

class temp
{
	/**
	 * @var filesystem
	 */
	protected $filesystem;

	/**
	 * @var string
	 */
	protected $cache_temp_dir;

	/**
	* @var string	Temporary directory path
	*/
	protected $temp_dir;

	/**
	 * Constructor
	 */
	public function __construct(filesystem $filesystem, string $cache_temp_dir)
	{
		$this->filesystem = $filesystem;
		$this->cache_temp_dir = $cache_temp_dir;
	}

	/**
	 * Get a temporary directory to write files
	 *
	 * @return string	returns the directory
	 */
	public function get_dir()
	{
		if ($this->temp_dir === null)
		{
			$tmp_dir = (function_exists('sys_get_temp_dir')) ? sys_get_temp_dir() : '';

			// Prevent trying to write to system temp dir in case of open_basedir
			// restrictions being in effect
			if (empty($tmp_dir) || !@file_exists($tmp_dir) || !@is_writable($tmp_dir))
			{
				$tmp_dir = $this->cache_temp_dir;

				if (!is_dir($tmp_dir))
				{
					$this->filesystem->mkdir($tmp_dir, 0777);
				}
			}

			$this->temp_dir = helper::realpath($tmp_dir);

			if ($this->temp_dir === false)
			{
				throw new filesystem_exception('FILESYSTEM_CANNOT_CREATE_DIRECTORY', $tmp_dir);
			}
		}

		return $this->temp_dir;
	}
}
