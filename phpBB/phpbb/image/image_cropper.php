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

namespace phpbb\image;

use phpbb\files\filespec_storage;

/**
 * Image cropper for locally uploaded files.
 *
 * Static class.
 * Requires the "ext-gd" PHP extension.
 */
class image_cropper
{
	/**
	 * Check if any image cropping can be done.
	 *
	 * @return bool						Whether image cropping is available
	 * @static
	 */
	public static function is_available(): bool
	{
		return extension_loaded('gd');
	}

	/**
	 * Check if the file extension is supported.
	 *
	 * This checks whether the create and save functions for a file extension exists.
	 * For example the 'imagecreatefrompng' and 'imagepng' functions for a PNG extension.
	 * Please note that the JPG extension should be in the JPEG form.
	 * Use @see image_cropper::get_file_extension() to ensure the correct form.
	 *
	 * @param string	$extension		The file extension
	 * @return bool						Whether the file extension is supported or not
	 * @static
	 */
	public static function is_extension_supported(string $extension): bool
	{
		return function_exists(self::get_create_function($extension))
			&& function_exists(self::get_save_function($extension));
	}

	/**
	 * Check if the file is supported.
	 *
	 * Retrieves the file extension from the file,
	 * and checks if the file extension is supported.
	 *
	 * @param filespec_storage	$file	The locally uploaded file
	 * @return bool						Whether the file is supported or not
	 * @static
	 */
	public static function is_file_supported(filespec_storage $file): bool
	{
		return self::is_extension_supported(self::get_file_extension($file));
	}

	/**
	 * Get the file extension.
	 *
	 * This ensures that the JPG extension is in the JPEG form.
	 * Because this form is needed for the create and save functions.
	 *
	 * @param filespec_storage	$file	The locally uploaded file
	 * @return string					The file extension
	 * @static
	 */
	public static function get_file_extension(filespec_storage $file): string
	{
		if ('jpg' === ($extension = strtolower($file->get('extension'))))
		{
			return 'jpeg';
		}

		return $extension;
	}

	/**
	 * Get the image create function.
	 *
	 * @param string	$extension		The file extension
	 * @return callable					The callable create function
	 * @static
	 */
	public static function get_create_function(string $extension): callable
	{
		return 'imagecreatefrom' . strtolower($extension);
	}

	/**
	 * Get the image save function.
	 *
	 * @param string	$extension		The file extension
	 * @return callable					The callable save function
	 * @static
	 */
	public static function get_save_function(string $extension): callable
	{
		return 'image' . strtolower($extension);
	}

	/**
	 * Crop the file by specified (cropper) data.
	 *
	 * $data = [
	 * 		'x'			=> (int)	Image X offset		Required
	 * 		'y'			=> (int)	Image Y offset		Required
	 * 		'width'		=> (int)	New image width		Required
	 * 		'height'	=> (int)	New image height	Required
	 * 		'rotate'	=> (float)	-360.00 to 360.00	Optional
	 * 		'scaleX'	=> (int)	-1					Optional
	 * 		'scaleY'	=> (int)	-1					Optional
	 * ];
	 *
	 * @param filespec_storage	$file	The locally uploaded file
	 * @param array				$data	The (cropper) data
	 * @return bool						Whether the image was successfully saved or not
	 * @static
	 */
	public static function crop_file_by_data(filespec_storage $file, array $data): bool
	{
		$image = self::create_image($file->get('filename'), self::get_file_extension($file));

		$rotate = isset($data['rotate']) && (float) $data['rotate'] !== 0;
		$flip_x = isset($data['scaleX']) && (int) $data['scaleX'] === -1;
		$flip_y = isset($data['scaleY']) && (int) $data['scaleY'] === -1;

		if ($rotate)
		{
			$image = self::rotate_image($image, (float) $data['rotate']);
		}

		if ($flip_x || $flip_y)
		{
			$image = self::flip_image($image, $flip_x, $flip_y);
		}

		$image = self::crop_image_by_array($image, $data);

		return self::save_image($file->get('filename'), self::get_file_extension($file), $image);
	}

	/**
	 * Create an image.
	 *
	 * @param string	$file			The file path
	 * @param string	$extension		The file extension
	 * @return resource					The image resource
	 * @static
	 */
	public static function create_image(string $file, string $extension)
	{
		return self::get_create_function($extension)($file);
	}

	/**
	 * Save an image.
	 *
	 * @param string	$file			The file path
	 * @param string	$extension		The file extension
	 * @param resource	$image			The image resource
	 * @return bool						Whether the image was successfully saved or not
	 * @static
	 */
	public static function save_image(string $file, string $extension, $image): bool
	{
		return self::get_save_function($extension)($image, $file);
	}

	/**
	 * Rotate an image.
	 *
	 * @param resource	$image			The image resource
	 * @param float		$degrees		The amount of degrees to rotate (-360.00 to 360.00)
	 * @param int		$bg_color		The background colour for any part that is left empty
	 * @return resource					The new image resource
	 * @static
	 */
	public static function rotate_image($image, float $degrees, int $bg_color = 0)
	{
		if (false !== ($new_image = imagerotate($image, $degrees, $bg_color)))
		{
			return $new_image;
		}

		return $image;
	}

	/**
	 * Flip an image.
	 *
	 * @param resource	$image				The image resource
	 * @param bool		$flip_horizontally	Whether the image should be flipped horizontally or not
	 * @param bool		$flip_vertically	Whether the image should be flipped vertically or not
	 * @return resource						The new image resource
	 * @static
	 */
	public static function flip_image($image, bool $flip_horizontally, bool $flip_vertically = false)
	{
		$flip_mode = 0;
		$flip_mode |= $flip_horizontally ? IMG_FLIP_HORIZONTAL : 0;
		$flip_mode |= $flip_vertically ? IMG_FLIP_VERTICAL : 0;

		if (0 !== $flip_mode)
		{
			imageflip($image, $flip_mode);
		}

		return $image;
	}

	/**
	 * Crop an image by a data array.
	 *
	 * This ensures the correct types (integer) are set.
	 *
	 * @param resource	$image		The image resource
	 * @param array		$data		The data array
	 * @return resource				The new image resource
	 * @static
	 */
	public static function crop_image_by_array($image, array $data)
	{
		return self::crop_image($image, (int) $data['x'], (int) $data['y'], (int) $data['width'], (int) $data['height']);
	}

	/**
	 * Crop an image.
	 *
	 * @param resource	$image		The image resource
	 * @param int		$x			The new image's X offset
	 * @param int		$y			The new image's Y offset
	 * @param int		$width		The new image's width
	 * @param int		$height		The new image's height
	 * @return resource				The new image resource
	 * @static
	 */
	public static function crop_image($image, int $x, int $y, int $width, int $height)
	{
		return false !== ($new_image = imagecrop($image, [
			'x'			=> $x,
			'y'			=> $y,
			'width'		=> $width,
			'height'	=> $height,
		])) ? $new_image : $image;
	}
}
