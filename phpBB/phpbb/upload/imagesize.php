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

namespace phpbb\upload;

/**
 * This class handles the retrieval of image dimensions
 */
class imagesize
{
	/** @var string PNG header */
	const PNG_HEADER = "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a";

	/** @var int PNG IHDR offset */
	const PNG_IHDR_OFFSET = 12;

	/** @var int PNG chunk size */
	const PNG_CHUNK_SIZE = 4;

	/** @var string GIF87a header */
	const GIF87A_HEADER = "\x47\x49\x46\x38\x37\x61";

	/** @var string GIF89a header */
	const GIF89A_HEADER = "\x47\x49\x46\x38\x39\x61";

	/** @var int GIF header size */
	const GIF_HEADER_SIZE = 6;

	/** @var int GIF chunk size */
	const GIF_CHUNK_SIZE = 2;

	/**
	 * Get image dimensions of supplied image
	 *
	 * @param string $file Path to image that should be checked
	 * @param string $type Mimetype of image
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	public function get_imagesize($file, $type = '')
	{
		if (!preg_match('/\.([a-z0-9]+)$/i', $file, $match) && empty($type))
		{
			return false;
		}

		$extension = (!empty($match)) ? $match[0] : preg_replace('/.+\/([a-z0-9]+)$/i', '$1', $type);

		switch ($extension)
		{
			case 'png':
				return $this->get_png_size($file);
			break;

			case 'gif':
				return $this->get_gif_size($file);
			break;

			default:
				return false;
		}
	}

	/**
	 * Get dimensions of PNG image
	 *
	 * @param string $filename Filename of image
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	protected function get_png_size($filename)
	{
		// Retrieve image data including the header, the IHDR tag, and the
		// following 2 chunks for the image width and height
		$data = file_get_contents($filename, null, null, 0, self::PNG_IHDR_OFFSET + 3 * self::PNG_CHUNK_SIZE);

		// Check if header fits expected format specified by RFC 2083
		if (substr($data, 0, self::PNG_IHDR_OFFSET - self::PNG_CHUNK_SIZE) !== self::PNG_HEADER || substr($data, self::PNG_IHDR_OFFSET, self::PNG_CHUNK_SIZE) !== 'IHDR')
		{
			return false;
		}

		$size = unpack('Nwidth/Nheight', substr($data, self::PNG_IHDR_OFFSET + self::PNG_CHUNK_SIZE, self::PNG_CHUNK_SIZE * 2));

		return sizeof($size) ? $size : false;
	}

	/**
	 * Get dimensions of GIF image
	 *
	 * @param string $filename Filename of image
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	protected function get_gif_size($filename)
	{
		// Get data needed for reading image dimensions as outlined by GIF87a
		// and GIF87a specifications
		$data = file_get_contents($filename, null, null, 0, self::GIF_HEADER_SIZE + self::GIF_CHUNK_SIZE * 2);

		$type = substr($data, 0, self::GIF_HEADER_SIZE);
		if ($type !== self::GIF87A_HEADER && $type !== self::GIF89A_HEADER)
		{
			return false;
		}

		$size = unpack('vwidth/vheight', substr($data, self::GIF_HEADER_SIZE, self::GIF_CHUNK_SIZE * 2));

		return sizeof($size) ? $size : false;
	}
}
