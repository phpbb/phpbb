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

namespace phpbb\storage\adapter;

use phpbb\storage\exception\exception;
use phpbb\filesystem\filesystem_exception;

class local implements adapter_interface
{
	/**
	 * Filesystem component
	 *
	 * @var \phpbb\filesystem\filesystem
	 */
	protected $filesystem;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 */
	public function __construct($filesystem, $phpbb_root_path)
	{
		$this->filesystem = $filesystem;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * {@inheritdoc}
	 */
	public function put_contents($path, $content)
	{
		if ($this->exists($this->phpbb_root_path.$path))
		{
			throw new exception('', $path); // FILE_EXISTS
		}

		try
		{
			$this->filesystem->dump_file($this->phpbb_root_path.$path, $content);
		}
		catch (filesystem_exception $e)
		{
			throw new exception('', $path, array(), $e); // CANNOT_DUMP_FILE
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_contents($path)
	{
		if (!$this->exists($this->phpbb_root_path.$path))
		{
			throw new exception('', $path); // FILE_DONT_EXIST
		}

		if (($content = @file_get_contents($this->phpbb_root_path.$path)) === false)
		{
			throw new exception('', $path); // CANNOT READ FILE
		}

		return $content;
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists($path)
	{
		return $this->filesystem->exists($this->phpbb_root_path.$path);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete($path)
	{
		try
		{
			$this->filesystem->remove($this->phpbb_root_path.$path);
		}
		catch (filesystem_exception $e)
		{
			throw new exception('', $path, array(), $e); // CANNOT DELETE
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function rename($path_orig, $path_dest)
	{
		try
		{
			$this->filesystem->rename($this->phpbb_root_path.$path_orig, $this->phpbb_root_path.$path_dest, false);
		}
		catch (filesystem_exception $e)
		{
			throw new exception('', $path_orig, array(), $e); // CANNOT_RENAME
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function copy($path_orig, $path_dest)
	{
		try
		{
			$this->filesystem->copy($this->phpbb_root_path.$path_orig, $this->phpbb_root_path.$path_dest, false);
		}
		catch (filesystem_exception $e)
		{
			throw new exception('', '', array(), $e); // CANNOT_COPY_FILES
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function create_dir($path)
	{
		try
		{
			$this->filesystem->mkdir($this->phpbb_root_path.$path);
		}
		catch (filesystem_exception $e)
		{
			throw new exception('', $path, array(), $e); // CANNOT_CREATE_DIRECTORY
		}
	}

}
