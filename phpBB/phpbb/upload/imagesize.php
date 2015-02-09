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
	/** @var int 4-byte long size */
	const LONG_SIZE = 4;

	/** @var int 2-byte short size */
	const SHORT_SIZE = 2;

	/** @var string PNG header */
	const PNG_HEADER = "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a";

	/** @var int PNG IHDR offset */
	const PNG_IHDR_OFFSET = 12;

	/** @var string GIF87a header */
	const GIF87A_HEADER = "\x47\x49\x46\x38\x37\x61";

	/** @var string GIF89a header */
	const GIF89A_HEADER = "\x47\x49\x46\x38\x39\x61";

	/** @var int GIF header size */
	const GIF_HEADER_SIZE = 6;

	/** @var int JPG max header size. Headers can be bigger, but we'll abort
	 *			going throught he header after this */
	const JPG_MAX_HEADER_SIZE = 24576;

	/** @var string PSD signature */
	const PSD_SIGNATURE = "8BPS";

	/** @var int PSD header size */
	const PSD_HEADER_SIZE = 22;

	/** @var int PSD dimensions info offset */
	const PSD_DIMENSIONS_OFFSET = 14;

	/** @var int BMP header size needed for retrieving dimensions */
	const BMP_HEADER_SIZE = 26;

	/** @var string BMP signature */
	const BMP_SIGNATURE = "\x42\x4D";

	/** qvar int BMP dimensions offset */
	const BMP_DIMENSIONS_OFFSET = 18;

	/** @var int TIF header size. The header might be larger but the dimensions
	 *			should be in the first 512 bytes */
	const TIF_HEADER_SIZE = 512;

	/** @var int TIF tag for image height */
	const TIF_TAG_IMAGE_HEIGHT = 257;

	/** @var int TIF tag for image width */
	const TIF_TAG_IMAGE_WIDTH = 256;

	/** @var int TIF tag type for short */
	const TIF_TAG_TYPE_SHORT = 3;

	/** @var int TIF IFD entry size */
	const TIF_IFD_ENTRY_SIZE = 12;

	/**
	 * Get image dimensions of supplied image
	 *
	 * @param string $file Path to image that should be checked
	 * @param string $type Mimetype of image
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	public function get_imagesize($file, $type = '')
	{
		// Do not process file further if type is unknown
		if (!preg_match('/\.([a-z0-9]+)$/i', $file, $match) && empty($type))
		{
			return false;
		}

		// Stop if file can't be accessed
		if (!file_exists($file))
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

			case 'bmp':
				return $this->get_bmp_size($file);
			break;

			case 'tif':
			case 'tiff':
				return $this->get_tif_size($file);
			break;

			case 'wbm':
			case 'wbmp':
			case 'vnd.wap.wbmp':
				return $this->get_wbmp_size($file);
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
		$data = file_get_contents($filename, null, null, 0, self::PNG_IHDR_OFFSET + 3 * self::LONG_SIZE);

		// Check if header fits expected format specified by RFC 2083
		if (substr($data, 0, self::PNG_IHDR_OFFSET - self::LONG_SIZE) !== self::PNG_HEADER || substr($data, self::PNG_IHDR_OFFSET, self::LONG_SIZE) !== 'IHDR')
		{
			return false;
		}

		$size = unpack('Nwidth/Nheight', substr($data, self::PNG_IHDR_OFFSET + self::LONG_SIZE, self::LONG_SIZE * 2));

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
		$data = file_get_contents($filename, null, null, 0, self::GIF_HEADER_SIZE + self::SHORT_SIZE * 2);

		$type = substr($data, 0, self::GIF_HEADER_SIZE);
		if ($type !== self::GIF87A_HEADER && $type !== self::GIF89A_HEADER)
		{
			return false;
		}

		$size = unpack('vwidth/vheight', substr($data, self::GIF_HEADER_SIZE, self::SHORT_SIZE * 2));

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
		for ($i = 2 * self::SHORT_SIZE; $i < strlen($data); $i = $i + self::SHORT_SIZE)
		{
			if ($data[$i] === "\xFF" && in_array($data[$i+1], array("\xC0", "\xC1", "\xC2", "\xC3", "\xC4", "\xC5", "\xC6", "\xC7", "\xC8", "\xC9", "\xCA", "\xCB", "\xCC", "\xCD", "\xCE", "\xCF")))
			{
				// Extract size info from SOF marker
				$size_data = unpack("H*", substr($data, $i + self::SHORT_SIZE, 7));

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
		$version = unpack('n', substr($data, self::LONG_SIZE, 2));

		// Check if supplied file is a PSD file
		if (substr($data, 0, self::LONG_SIZE) !== self::PSD_SIGNATURE || $version[1] !== 1)
		{
			return false;
		}

		$size = unpack('Nheight/Nwidth', substr($data, self::PSD_DIMENSIONS_OFFSET, 2 * self::LONG_SIZE));

		return sizeof($size) ? $size : false;
	}

	/**
	 * Get dimensions of BMP image
	 *
	 * @param string $filename Filename of image
	 *
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	protected function get_bmp_size($filename)
	{
		$data = file_get_contents($filename, null, null, 0, self::BMP_HEADER_SIZE);

		// Check if supplied file is a BMP file
		if (substr($data, 0, 2) !== self::BMP_SIGNATURE)
		{
			return false;
		}

		$size = unpack('lwidth/lheight', substr($data, self::BMP_DIMENSIONS_OFFSET, 2 * self::LONG_SIZE));

		return sizeof($size) ? $size : false;
	}

	/**
	 * Get dimensions of TIF/TIFF image
	 *
	 * @param string $filename Filename of image
	 *
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	protected function get_tif_size($filename)
	{
		$data = file_get_contents($filename, null, null, 0, self::TIF_HEADER_SIZE);

		$signature = substr($data, 0, self::SHORT_SIZE);

		if ($signature !== "II" && $signature !== "MM")
		{
			return false;
		}

		$size = array();

		if ($signature === "II")
		{
			$type_long = 'V';
			$type_short = 'v';
		}
		else
		{
			$type_long = 'N';
			$type_short = 'n';
		}

		// Get offset of IFD
		$offset = unpack($type_long . 'offset', substr($data, self::LONG_SIZE, self::LONG_SIZE));
		$offset = array_pop($offset);

		// Get size of IFD
		$size_ifd = unpack($type_short, substr($data, $offset, self::SHORT_SIZE));
		$size_ifd = array_pop($size_ifd);

		// Skip 2 bytes that define the IFD size
		$offset += self::SHORT_SIZE;

		// Filter through IFD
		for ($i = 0; $i < $size_ifd; $i++)
		{
			// Get IFD tag
			$type = unpack($type_short, substr($data, $offset, self::SHORT_SIZE));

			// Get field type of tag
			$field_type = unpack($type_short . 'type', substr($data, $offset + self::SHORT_SIZE, self::SHORT_SIZE));

			// Get IFD entry
			$ifd_value = substr($data, $offset + 2 * self::LONG_SIZE, self::LONG_SIZE);

			// Get actual dimensions from IFD
			if ($type[1] === self::TIF_TAG_IMAGE_HEIGHT)
			{
				$size = array_merge($size, ($field_type['type'] === self::TIF_TAG_TYPE_SHORT) ? unpack($type_short . 'height', $ifd_value) : unpack($type_long . 'height', $ifd_value));
			}
			else if ($type[1] === self::TIF_TAG_IMAGE_WIDTH)
			{
				$size = array_merge($size, ($field_type['type'] === self::TIF_TAG_TYPE_SHORT) ? unpack($type_short .'width', $ifd_value) : unpack($type_long . 'width', $ifd_value));
			}

			$offset += self::TIF_IFD_ENTRY_SIZE;
		}

		return sizeof($size) ? $size : false;
	}

	/**
	 * Get dimensions of WBMP image
	 *
	 * @param string $filename Filename of image
	 *
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	protected function get_wbmp_size($filename)
	{
		$data = file_get_contents($filename, null, null, 0, self::LONG_SIZE);

		// Check if image is WBMP
		if (ord($data[0]) !== 0 || ord($data[1]) !== 0)
		{
			return false;
		}

		$size = unpack('Cwidth/Cheight', substr($data, self::SHORT_SIZE, self::SHORT_SIZE));

		return sizeof($size) ? $size : false;
	}
}
