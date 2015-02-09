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

	/** @var int JPG max header size. Headers can be bigger, but we'll abort
	 *			going throught he header after this */
	const JPG_MAX_HEADER_SIZE = 24576;

	/** @var int JPEG chunk size */
	const JPG_CHUNK_SIZE = 2;

	/** @var string PSD signature */
	const PSD_SIGNATURE = "8BPS";

	/** @var int PSD header size */
	const PSD_HEADER_SIZE = 22;

	/** @var int PSD dimensions info offset */
	const PSD_DIMENSIONS_OFFSET = 14;

	/** @var int PSD signature and dimensions size*/
	const PSD_CHUNK_SIZE = 4;

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

		$extension = (isset($match[1])) ? $match[1] : preg_replace('/.+\/([a-z0-9-]+)$/i', '$1', $type);

		switch ($extension)
		{
			case 'png':
				return $this->get_png_size($file);
				break;

			case 'gif':
				return $this->get_gif_size($file);
			break;

			case 'jpeg':
			case 'jpg':
			case 'jpe':
			case 'jif':
			case 'jfif':
			case 'jfi':
				return $this->get_jpeg_size($file);
			break;

			case 'psd':
			case 'photoshop':
				return $this->get_psd_size($file);
			break;

			default:
				return false;
		}
	}

	/**
	 * Get dimensions of PNG image
	 *
	 * @param string $filename Filename of image
	 *
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
	 *
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

	/**
	 * Get dimensions of JPG image
	 *
	 * @param string $filename Filename of image
	 *
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	protected function get_jpeg_size($filename)
	{
		$data = file_get_contents($filename, null, null, 0, self::JPG_MAX_HEADER_SIZE);

		// Check if file is jpeg
		if ($data[0] !== "\xFF" || $data[1] !== "\xD8")
		{
			return false;
		}

		$size = array();

		// Look through file for SOF marker
		for ($i = 2 * self::JPG_CHUNK_SIZE; $i < strlen($data); $i = $i + self::JPG_CHUNK_SIZE)
		{
			if ($data[$i] === "\xFF" && in_array($data[$i+1], array("\xC0", "\xC1", "\xC2", "\xC3", "\xC4", "\xC5", "\xC6", "\xC7", "\xC8", "\xC9", "\xCA", "\xCB", "\xCC", "\xCD", "\xCE", "\xCF")))
			{
				// Extract size info from SOF marker
				$size_data = unpack("H*", substr($data, $i + self::JPG_CHUNK_SIZE, 7));

				$unpacked = array_pop($size_data);

				// Get width and height from unpacked size info
				$size = array(
					'width'		=> hexdec(substr($unpacked, 10, 4)),
					'height'	=> hexdec(substr($unpacked, 6, 4)),
				);

				break;
			}
		}

		return sizeof($size) ? $size : false;
	}

	/**
	 * Get dimensions of PSD image
	 *
	 * @param string $filename Filename of image
	 *
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	protected function get_psd_size($filename)
	{
		$data = file_get_contents($filename, null, null, 0, self::PSD_HEADER_SIZE);

		// Offset for version info is length of header but version is only a
		// 16-bit unsigned value
		$version = unpack('n', substr($data, self::PSD_CHUNK_SIZE, 2));

		if (substr($data, 0, self::PSD_CHUNK_SIZE) !== self::PSD_SIGNATURE || $version[1] !== 1)
		{
			return false;
		}

		$size = unpack('Nheight/Nwidth', substr($data, self::PSD_DIMENSIONS_OFFSET, 2 * self::PSD_CHUNK_SIZE));

		return sizeof($size) ? $size : false;
	}
}
