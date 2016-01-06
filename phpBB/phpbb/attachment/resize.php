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

namespace phpbb\attachment;

use phpbb\filesystem\filesystem_interface;
use bantu\IniGetWrapper\IniGetWrapper;
use FastImageSize\FastImageSize;

/**
 * Attachment resize class
 */
class resize
{
	/** @var filesystem_interface phpBB filesystem */
	protected $filesystem;

	/** @var \phpbb\attachment\image_helper Image helper class */
	protected $image_helper;

	/** @var IniGetWrapper */
	protected $php_ini;

	/** @var FastImageSize */
	protected $image_size;

	/** @var string Source file path */
	protected $source;

	/** @var string Destination file path */
	protected $destination;

	/** @var int Source image height */
	protected $height;

	/** @var int Source image width */
	protected $width;

	/** @var string Source image type */
	protected $type;

	/** @var int New image height */
	protected $new_height;

	/** @var int New image width */
	protected $new_width;

	/** @var int Target width */
	protected $target_width;

	/** @var int Target height */
	protected $target_height;

	/** @var int Minimum target file size */
	protected $target_min_size;

	/** @var bool Flag whether image was resized */
	private $resize_created = false;

	/** @var string Imagemagick path */
	private $imagick_path = '';

	/**
	 * Resize constructor
	 *
	 * @param filesystem_interface $filesystem phpBB filesystem
	 * @param image_helper $image_helper Image helper class
	 * @param IniGetWrapper $php_ini ini_get() wrapper
	 * @param FastImageSize $image_size FastImageSize library
	 */
	public function __construct(filesystem_interface $filesystem, image_helper $image_helper, IniGetWrapper $php_ini, FastImageSize $image_size)
	{
		$this->filesystem = $filesystem;
		$this->image_helper = $image_helper;
		$this->php_ini = $php_ini;
		$this->image_size = $image_size;
	}

	/**
	 * Set target size limits
	 *
	 * @param int $target_width
	 * @param int $target_height
	 *
	 * @return resize Returns self for allowing chained calls
	 */
	public function set_target_size($target_width, $target_height)
	{
		$this->target_width = $target_width;
		$this->target_height = $target_height;

		return $this;
	}

	/**
	 * Set target minimum file size in bytes
	 *
	 * @param int $min_size
	 *
	 * @return resize Returns self for allowing chained calls
	 */
	public function set_min_file_size($min_size)
	{
		$this->target_min_size = $min_size;

		return $this;
	}

	/**
	 * Enable imagemagick support
	 *
	 * @param string $path Imagemagick path
	 *
	 * @return resize Returns self for allowing chained calls
	 */
	public function set_imagick_path($path)
	{
		$this->imagick_path = $path;

		return $this;
	}

	/**
	 * Create resized image
	 *
	 * @param string $source Source file path
	 * @param string $destination Destination file path
	 * @param string $mime_type File mime type
	 *
	 * @return bool True if image was resized, false if not
	 */
	public function create($source, $destination, $mime_type)
	{
		$this->set_paths($source, $destination);
		$this->resize_created = false;

		if (!$this->set_size($mime_type))
		{
			return false;
		}

		$this->create_imagick();

		if (!$this->resize_created && !$this->create_gd())
		{
			return false;
		}

		if (!file_exists($this->destination))
		{
			return false;
		}

		try
		{
			$this->filesystem->phpbb_chmod($this->destination, CHMOD_READ | CHMOD_WRITE);
		}
		catch (\phpbb\filesystem\exception\filesystem_exception $e)
		{
			// Do nothing
		}

		return true;
	}

	/**
	 * Set file paths
	 *
	 * @param string $source Source file path
	 * @param string $destination Destination file path
	 */
	protected function set_paths($source, $destination)
	{
		$this->source = $source;
		$this->destination = $destination;
	}

	/**
	 * Set image size info
	 *
	 * @param string $mime_type Image mime type
	 *
	 * @return bool True if valid size could be set, false if not
	 */
	protected function set_size($mime_type)
	{
		$img_filesize = (file_exists($this->source)) ? @filesize($this->source) : false;

		if (!$img_filesize || $img_filesize <= (int) $this->target_min_size)
		{
			return false;
		}

		$dimension = $this->image_size->getImageSize($this->source, $mime_type);

		if ($dimension === false || empty($dimension['width']) || empty($dimension['height']))
		{
			return false;
		}

		$this->width = $dimension['width'];
		$this->height = $dimension['height'];
		$this->type = $dimension['type'];

		list($this->new_width, $this->new_height) = $this->image_helper->get_img_size_format(
			$this->width,
			$this->height,
			$this->target_width,
			$this->target_height
		);

		// Do not resize image if the resulting width/height is bigger than the original one
		if ($this->new_width >= $this->width && $this->new_height >= $this->height)
		{
			return false;
		}

		return true;
	}

	/**
	 * Create resized image using imagemagick
	 */
	protected function create_imagick()
	{
		if ($this->imagick_path && function_exists('passthru'))
		{
			if (substr($this->imagick_path, -1) !== '/')
			{
				$this->imagick_path .= '/';
			}

			// Make sure we only use existing paths here
			if (!is_dir($this->imagick_path))
			{
				return;
			}

			@passthru(escapeshellcmd($this->imagick_path) . 'convert' . ((defined('PHP_OS') && preg_match('#^win#i', PHP_OS)) ? '.exe' : '') . ' -quality 85 -geometry ' . $this->new_width . 'x' . $this->new_height . ' "' . str_replace('\\', '/', $this->source) . '" "' . str_replace('\\', '/', $this->destination) . '"');

			if (file_exists($this->destination))
			{
				$this->resize_created = true;
			}
		}
	}

	/**
	 * Create resized image using GD
	 *
	 * @return bool True if image was resized, false if not
	 */
	protected function create_gd()
	{
		$type = $this->image_helper->get_supported_image_types($this->type);

		if ($type['gd'])
		{
			// If the type is not supported, we are not able to resize an image
			if ($type['format'] === false)
			{
				return false;
			}

			switch ($type['format'])
			{
				case IMG_GIF:
					$image = @imagecreatefromgif($this->source);
				break;

				case IMG_JPG:
					@ini_set('gd.jpeg_ignore_warning', 1);
					$image = @imagecreatefromjpeg($this->source);
				break;

				case IMG_PNG:
					$image = @imagecreatefrompng($this->source);
				break;

				case IMG_WBMP:
					$image = @imagecreatefromwbmp($this->source);
				break;
			}

			if (empty($image))
			{
				return false;
			}

			if ($type['version'] == 1)
			{
				$new_image = imagecreate($this->new_width, $this->new_height);

				if ($new_image === false)
				{
					return false;
				}

				imagecopyresized($new_image, $image, 0, 0, 0, 0, $this->new_width, $this->new_height, $this->width, $this->height);
			}
			else
			{
				$new_image = imagecreatetruecolor($this->new_width, $this->new_height);

				if ($new_image === false)
				{
					return false;
				}

				// Preserve alpha transparency (png for example)
				@imagealphablending($new_image, false);
				@imagesavealpha($new_image, true);

				imagecopyresampled($new_image, $image, 0, 0, 0, 0, $this->new_width, $this->new_height, $this->width, $this->height);
			}

			switch ($type['format'])
			{
				case IMG_GIF:
					imagegif($new_image, $this->destination);
				break;

				case IMG_JPG:
					imagejpeg($new_image, $this->destination, 90);
				break;

				case IMG_PNG:
					imagepng($new_image, $this->destination);
				break;

				case IMG_WBMP:
					imagewbmp($new_image, $this->destination);
				break;
			}

			imagedestroy($new_image);
		}
		else
		{
			return false;
		}

		return true;
	}
}
