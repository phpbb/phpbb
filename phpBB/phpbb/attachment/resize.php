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

use phpbb\config\config;
use phpbb\filesystem\filesystem_interface;
use bantu\IniGetWrapper\IniGetWrapper;
use FastImageSize\FastImageSize;

/**
 * Attachment resize class
 */
class resize
{
	/** @var config phpBB config */
	protected $config;

	/** @var filesystem_interface phpBB filesystem */
	protected $filesystem;

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

	/** @var int Source image height */
	protected $new_height;

	/** @var int Source image width */
	protected $new_width;

	/** @var bool Flag whether thumbnail was created */
	private $resize_created = false;

	/**
	 * Resize constructor
	 *
	 * @param config $config phpBB config
	 * @param filesystem_interface $filesystem phpBB filesystem
	 * @param IniGetWrapper $php_ini ini_get() wrapper
	 * @param FastImageSize $image_size FastImageSize library
	 */
	public function __construct(config $config, filesystem_interface $filesystem, IniGetWrapper $php_ini, FastImageSize $image_size)
	{
		$this->config = $config;
		$this->filesystem = $filesystem;
		$this->php_ini = $php_ini;
		$this->image_size = $image_size;
	}

	/**
	 * Create resized image
	 *
	 * @param string $source Source file path
	 * @param string $destination Destination file path
	 * @param string $mime_type File mime type
	 *
	 * @return bool True if thumbnail was created, false if not
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

		if (!$img_filesize || $img_filesize <= (int) $this->config['img_min_thumb_filesize'])
		{
			return false;
		}

		$dimension = $this->image_size->getImageSize($this->source, $mime_type);

		if ($dimension === false || empty($dimension['width'] || empty($dimension['height'])))
		{
			return false;
		}

		$this->width = $dimension['width'];
		$this->height = $dimension['height'];
		$this->type = $dimension['type'];

		list($this->new_width, $this->new_height) = get_img_size_format($this->width, $this->height);

		// Do not create a thumbnail if the resulting width/height is bigger than the original one
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
		// Only use imagemagick if defined and the passthru function not disabled
		if ($this->config['img_imagick'] && function_exists('passthru'))
		{
			if (substr($this->config['img_imagick'], -1) !== '/')
			{
				$this->config['img_imagick'] .= '/';
			}

			@passthru(escapeshellcmd($this->config['img_imagick']) . 'convert' . ((defined('PHP_OS') && preg_match('#^win#i', PHP_OS)) ? '.exe' : '') . ' -quality 85 -geometry ' . $this->new_width . 'x' . $this->new_height . ' "' . str_replace('\\', '/', $this->source) . '" "' . str_replace('\\', '/', $this->destination) . '"');

			if (file_exists($this->destination))
			{
				$this->resize_created = true;
			}
		}
	}

	/**
	 * Get supported image types
	 *
	 * @param bool|int $type Image type constant
	 * @return array An Array containing whether gd is enabled and supported
	 *		image types
	 */
	public function get_supported_image_types($type = false)
	{
		if (@extension_loaded('gd'))
		{
			$format = imagetypes();
			$new_type = 0;

			if ($type !== false)
			{
				// Type is one of the IMAGETYPE constants - it is fetched from getimagesize()
				switch ($type)
				{
					// GIF
					case IMAGETYPE_GIF:
						$new_type = ($format & IMG_GIF) ? IMG_GIF : false;
					break;

					// JPG, JPC, JP2
					case IMAGETYPE_JPEG:
					case IMAGETYPE_JPC:
					case IMAGETYPE_JPEG2000:
					case IMAGETYPE_JP2:
					case IMAGETYPE_JPX:
					case IMAGETYPE_JB2:
						$new_type = ($format & IMG_JPG) ? IMG_JPG : false;
					break;

					// PNG
					case IMAGETYPE_PNG:
						$new_type = ($format & IMG_PNG) ? IMG_PNG : false;
					break;

					// WBMP
					case IMAGETYPE_WBMP:
						$new_type = ($format & IMG_WBMP) ? IMG_WBMP : false;
					break;
				}
			}
			else
			{
				$new_type = array();
				$go_through_types = array(IMG_GIF, IMG_JPG, IMG_PNG, IMG_WBMP);

				foreach ($go_through_types as $check_type)
				{
					if ($format & $check_type)
					{
						$new_type[] = $check_type;
					}
				}
			}

			return array(
				'gd'		=> ($new_type) ? true : false,
				'format'	=> $new_type,
				'version'	=> (function_exists('imagecreatetruecolor')) ? 2 : 1
			);
		}

		return array('gd' => false);
	}

	/**
	 * Create resized image using GD
	 *
	 * @return bool True if thumbnail might have been created, false if not
	 */
	protected function create_gd()
	{
		$type = $this->get_supported_image_types($this->type);

		if ($type['gd'])
		{
			// If the type is not supported, we are not able to create a thumbnail
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

			// If we are in safe mode create the destination file prior to using the gd functions to circumvent a PHP bug
			if ($this->php_ini->getBool('safe_mode'))
			{
				$this->filesystem->touch($this->destination);
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
