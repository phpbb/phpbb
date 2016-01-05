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

/**
 * Attachment image helper class
 */
class image_helper
{
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
	 * Calculate the needed size for Thumbnail
	 *
	 * @param int $width Image width
	 * @param int $height Image height
	 * @param int $max_target_width Maximum image width. Defaults to 400px
	 * @param int $max_target_height Maximum image height. Defaults to 400px
	 *
	 * @return array An array with target width and height
	 */
	public function get_img_size_format($width, $height, $max_target_width = 400, $max_target_height = 400)
	{
		$target_ratio = ($max_target_height != 0) ? $max_target_width / $max_target_height : 1;
		$image_ratio = ($height != 0) ? $width / $height : 1;

		// Check if size is limited by width or height
		if ($image_ratio >= $target_ratio)
		{
			return array(
				round($width * ($max_target_width / $width)),
				round($height * ($max_target_width / $width))
			);
		}
		else
		{
			return array(
				round($width * ($max_target_height / $height)),
				round($height * ($max_target_height / $height))
			);
		}
	}
}
