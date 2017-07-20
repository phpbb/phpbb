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
use phpbb\filesystem\exception\filesystem_exception;
use phpbb\filesystem\filesystem;

/**
 * @internal Experimental
 */
class local implements adapter_interface
{
	/**
	 * Filesystem component
	 *
	 * @var \phpbb\filesystem\filesystem
	 */
	protected $filesystem;

	/**
	 * @var string path
	 */
	protected $phpbb_root_path;

	/**
	 * @var string path
	 */
	protected $root_path;

	/**
	 * Constructor
	 */
	public function __construct(filesystem $filesystem, $phpbb_root_path)
	{
		$this->filesystem = $filesystem;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * {@inheritdoc}
	 */
	public function configure($options)
	{
		$this->root_path = $this->phpbb_root_path . $options['path'];

		if (substr($this->root_path, -1, 1) !== DIRECTORY_SEPARATOR)
		{
			$this->root_path = $this->root_path . DIRECTORY_SEPARATOR;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function put_contents($path, $content)
	{
		if ($this->exists($path))
		{
			throw new exception('STORAGE_FILE_EXISTS', $path);
		}

		try
		{
			$this->filesystem->dump_file($this->root_path . $path, $content);
		}
		catch (filesystem_exception $e)
		{
			throw new exception('STORAGE_CANNOT_WRITE_FILE', $path, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_contents($path)
	{
		if (!$this->exists($path))
		{
			throw new exception('STORAGE_FILE_NO_EXIST', $path);
		}

		$content = @file_get_contents($this->root_path . $path);

		if ($content === false)
		{
			throw new exception('STORAGE_CANNOT_READ_FILE', $path);
		}

		return $content;
	}

	/**
	 * {@inheritdoc}
	 */
	public function exists($path)
	{
		return $this->filesystem->exists($this->root_path . $path);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete($path)
	{
		try
		{
			$this->filesystem->remove($this->root_path . $path);
		}
		catch (filesystem_exception $e)
		{
			throw new exception('STORAGE_CANNOT_DELETE', $path, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function rename($path_orig, $path_dest)
	{
		try
		{
			$this->filesystem->rename($this->root_path . $path_orig, $this->root_path . $path_dest, false);
		}
		catch (filesystem_exception $e)
		{
			throw new exception('STORAGE_CANNOT_RENAME', $path_orig, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function copy($path_orig, $path_dest)
	{
		try
		{
			$this->filesystem->copy($this->root_path . $path_orig, $this->root_path . $path_dest, false);
		}
		catch (filesystem_exception $e)
		{
			throw new exception('STORAGE_CANNOT_COPY', $path_orig, array(), $e);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function create_dir($path)
	{
		try
		{
			$this->filesystem->mkdir($this->root_path . $path);
		}
		catch (filesystem_exception $e)
		{
			throw new exception('STORAGE_CANNOT_CREATE_DIR', $path, array(), $e);
		}
	}

}
