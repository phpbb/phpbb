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
use phpbb\filesystem\filesystem;
use FastImageSize\FastImageSize;

/**
 * Attachment thumbnail class
 */
class thumbnail
{
	/** @var config phpBB config */
	protected $config;

	/** @var filesystem phpBB filesystem */
	protected $filesystem;

	/** @var FastImageSize */
	protected $image_size;

	/**
	 * Thumbnail constructor
	 *
	 * @param config $config phpBB config
	 * @param filesystem $filesystem phpBB filesystem
	 * @param FastImageSize $image_size FastImageSize library
	 */
	public function __construct(config $config, filesystem $filesystem, FastImageSize $image_size)
	{
		$this->config = $config;
		$this->filesystem = $filesystem;
		$this->imagesize = $image_size;
	}

	/**
	 * Create Thumbnail
	 *
	 * @param string $source Source file path
	 * @param string $destination Destination file path
	 * @param string $mime_type File mime type
	 *
	 * @return bool True if thumbnail was created, false if not
	 */
	function create($source, $destination, $mime_type)
	{
		$img_filesize = (file_exists($source)) ? @filesize($source) : false;

		if (!$img_filesize || $img_filesize <= (int) $this->config['img_min_thumb_filesize'])
		{
			return false;
		}

		$dimension = $this->image_size->getImageSize($source, $mime_type);

		if ($dimension === false)
		{
			return false;
		}

		list($width, $height, $type, ) = $dimension;

		if (empty($width) || empty($height))
		{
			return false;
		}

		list($new_width, $new_height) = get_img_size_format($width, $height);

		// Do not create a thumbnail if the resulting width/height is bigger than the original one
		if ($new_width >= $width && $new_height >= $height)
		{
			return false;
		}

		$used_imagick = false;

		// Only use imagemagick if defined and the passthru function not disabled
		if ($this->config['img_imagick'] && function_exists('passthru'))
		{
			if (substr($this->config['img_imagick'], -1) !== '/')
			{
				$this->config['img_imagick'] .= '/';
			}

			@passthru(escapeshellcmd($this->config['img_imagick']) . 'convert' . ((defined('PHP_OS') && preg_match('#^win#i', PHP_OS)) ? '.exe' : '') . ' -quality 85 -geometry ' . $new_width . 'x' . $new_height . ' "' . str_replace('\\', '/', $source) . '" "' . str_replace('\\', '/', $destination) . '"');

			if (file_exists($destination))
			{
				$used_imagick = true;
			}
		}

		if (!$used_imagick)
		{
			$type = get_supported_image_types($type);

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
						$image = @imagecreatefromgif($source);
						break;

					case IMG_JPG:
						@ini_set('gd.jpeg_ignore_warning', 1);
						$image = @imagecreatefromjpeg($source);
						break;

					case IMG_PNG:
						$image = @imagecreatefrompng($source);
						break;

					case IMG_WBMP:
						$image = @imagecreatefromwbmp($source);
						break;
				}

				if (empty($image))
				{
					return false;
				}

				if ($type['version'] == 1)
				{
					$new_image = imagecreate($new_width, $new_height);

					if ($new_image === false)
					{
						return false;
					}

					imagecopyresized($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				}
				else
				{
					$new_image = imagecreatetruecolor($new_width, $new_height);

					if ($new_image === false)
					{
						return false;
					}

					// Preserve alpha transparency (png for example)
					@imagealphablending($new_image, false);
					@imagesavealpha($new_image, true);

					imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				}

				// If we are in safe mode create the destination file prior to using the gd functions to circumvent a PHP bug
				if (@ini_get('safe_mode') || @strtolower(ini_get('safe_mode')) == 'on')
				{
					@touch($destination);
				}

				switch ($type['format'])
				{
					case IMG_GIF:
						imagegif($new_image, $destination);
						break;

					case IMG_JPG:
						imagejpeg($new_image, $destination, 90);
						break;

					case IMG_PNG:
						imagepng($new_image, $destination);
						break;

					case IMG_WBMP:
						imagewbmp($new_image, $destination);
						break;
				}

				imagedestroy($new_image);
			}
			else
			{
				return false;
			}
		}

		if (!file_exists($destination))
		{
			return false;
		}

		try
		{
			$this->filesystem->phpbb_chmod($destination, CHMOD_READ | CHMOD_WRITE);
		}
		catch (\phpbb\filesystem\exception\filesystem_exception $e)
		{
			// Do nothing
		}

		return true;
	}
}
