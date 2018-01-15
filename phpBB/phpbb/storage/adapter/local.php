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

use phpbb\storage\stream_interface;
use phpbb\storage\exception\exception;
use phpbb\filesystem\exception\filesystem_exception;
use phpbb\filesystem\filesystem;
use phpbb\filesystem\helper as filesystem_helper;
use phpbb\mimetype\guesser;
use FastImageSize\FastImageSize;

/**
 * @internal Experimental
 */
class local implements adapter_interface, stream_interface
{
	/**
	 * Filesystem component
	 *
	 * @var \phpbb\filesystem\filesystem
	 */
	protected $filesystem;

	/**
	 * FastImageSize
	 *
	 * @var \FastImageSize\FastImageSize
	 */
	protected $imagesize;

	/**
	 * Mimetype Guesser component
	 *
	 * @var \phpbb\mimetype\guesser
	 */
	protected $mimetype_guesser;

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
	public function __construct(filesystem $filesystem, FastImageSize $imagesize, guesser $mimetype_guesser, $phpbb_root_path)
	{
		$this->filesystem = $filesystem;
		$this->imagesize = $imagesize;
		$this->mimetype_guesser = $mimetype_guesser;
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
		$this->ensure_directory_exists($path);

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
		$this->ensure_directory_exists($path_dest);

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
		$this->ensure_directory_exists($path_dest);

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
	 * Creates a directory recursively.
	 *
	 * @param string	$path	The directory path
	 *
	 * @throws \phpbb\storage\exception\exception	On any directory creation failure
	 */
	protected function create_dir($path)
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

	/**
	 * Ensures that the directory of a file exists.
	 *
	 * @param string	$path	The file path
	 */
	protected function ensure_directory_exists($path)
	{
		$path = dirname($this->root_path . $path);
		$path = filesystem_helper::make_path_relative($path, $this->root_path);

		if (!$this->exists($path))
		{
			$this->create_dir($path);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function read_stream($path)
	{
		$stream = @fopen($this->root_path . $path, 'rb');

		if (!$stream)
		{
			throw new exception('STORAGE_CANNOT_OPEN_FILE', $path);
		}

		return $stream;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write_stream($path, $resource)
	{
		if ($this->exists($path))
		{
			throw new exception('STORAGE_FILE_EXISTS', $path);
		}

		$stream = @fopen($this->root_path . $path, 'w+b');

		if (!$stream)
		{
			throw new exception('STORAGE_CANNOT_CREATE_FILE', $path);
		}

		if (stream_copy_to_stream($resource, $stream) === false)
		{
			fclose($stream);
			throw new exception('STORAGE_CANNOT_COPY_RESOURCE');
		}
	}

	/**
	 * Get file size.
	 *
	 * @param string	$path	The file
	 *
	 * @throws \phpbb\storage\exception\exception		When cannot get size
	 *
	 * @return array Properties
	 */
	public function file_size($path)
	{
		$size = filesize($this->root_path . $path);

		if ($size === null)
		{
			throw new exception('STORAGE_CANNOT_GET_FILESIZE');
		}

		return ['size' => $size];
	}

	/**
	 * Get file mimetype.
	 *
	 * @param string	$path	The file
	 *
	 * @return array	Properties
	 */
	public function file_mimetype($path)
	{
		return ['mimetype' => $this->mimetype_guesser->guess($this->root_path . $path)];
	}

	/**
	 * Get image dimensions.
	 *
	 * @param string	$path	The file
	 *
	 * @return array	Properties
	 */
	protected function image_dimensions($path)
	{
		$size = $this->imagesize->getImageSize($this->root_path . $path);

		// For not supported types like swf
		if ($size === false)
		{
			$imsize = getimagesize($this->root_path . $path);
			$size = ['width' => $imsize[0], 'height' => $imsize[1]];
		}

		return ['image_width' => $size['width'], 'image_height' => $size['height']];
	}

	/**
	 * Get image width.
	 *
	 * @param string	$path	The file
	 *
	 * @return array	Properties
	 */
	public function file_image_width($path)
	{
		return $this->image_dimensions($path);
	}

	/**
	 * Get image height.
	 *
	 * @param string	$path	The file
	 *
	 * @return array	Properties
	 */
	public function file_image_height($path)
	{
		return $this->image_dimensions($path);
	}
}
