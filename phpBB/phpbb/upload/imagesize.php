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

	/** @var int IFF header size. Grab more than what should be needed to make
	 * sure we have the necessary data */
	const IFF_HEADER_SIZE = 32;

	/** @var string JPEG 2000 signature */
	const JPEG_2000_SIGNATURE = "\x00\x00\x00\x0C\x6A\x50\x20\x20\x0D\x0A\x87\x0A";

	/** @var array Size info that is returned */
	protected $size = array();

	/** @var string Data retrieved from remote */
	protected $data = '';

	/**
	 * Get image dimensions of supplied image
	 *
	 * @param string $file Path to image that should be checked
	 * @param string $type Mimetype of image
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	public function get_imagesize($file, $type = '')
	{
		// Reset values
		$this->reset_values();

		// Treat image type as unknown if extension or mime type is unknown
		if (!preg_match('/\.([a-z0-9]+)$/i', $file, $match) && empty($type))
		{
			$this->get_imagesize_unknown_type($file);
		}
		else
		{
			$extension = (isset($match[1])) ? $match[1] : preg_replace('/.+\/([a-z0-9-.]+)$/i', '$1', $type);

			// Reset size info
			$this->size = array();

			switch ($extension)
			{
				case 'png':
					$this->get_png_size($file);
				break;

				case 'gif':
					$this->get_gif_size($file);
				break;

				case 'jpeg':
				case 'jpg':
				case 'jpe':
				case 'jif':
				case 'jfif':
				case 'jfi':
					$this->get_jpeg_size($file);
				break;

				case 'jp2':
				case 'j2k':
				case 'jpf':
				case 'jpg2':
				case 'jpx':
				case 'jpm':
					$this->get_jp2_size($file);
				break;

				case 'psd':
				case 'photoshop':
					$this->get_psd_size($file);
				break;

				case 'bmp':
					$this->get_bmp_size($file);
				break;

				case 'tif':
				case 'tiff':
					// get_tif_size() sets mime type
					$this->get_tif_size($file);
				break;

				case 'wbm':
				case 'wbmp':
				case 'vnd.wap.wbmp':
					$this->get_wbmp_size($file);
				break;

				case 'iff':
				case 'x-iff':
					$this->get_iff_size($file);
				break;

				default:
					return false;
			}
		}

		return sizeof($this->size) > 1 ? $this->size : false;
	}

	/**
	 * Get dimensions of image if type is unknown
	 *
	 * @param string $filename Path to file
	 */
	protected function get_imagesize_unknown_type($filename)
	{
		// Grab the maximum amount of bytes we might need
		$data = $this->get_image($filename, 0, self::JPG_MAX_HEADER_SIZE, false);

		if ($data !== false)
		{
			$class_methods = preg_grep('/get_([a-z0-9]+)_size/i', get_class_methods($this));

			foreach ($class_methods as $method)
			{
				call_user_func_array(array($this, $method), array($filename));

				if (sizeof($this->size) > 1)
				{
					break;
				}
			}
		}
	}

	/**
	 * Reset values to default
	 */
	protected function reset_values()
	{
		$this->size = array();
		$this->data = '';
	}

	/**
	 * Set mime type based on supplied image
	 *
	 * @param int $type Type of image
	 */
	protected function set_image_type($type)
	{
		$this->size['type'] = $type;
	}

	/**
	 * Get image from specified path/source
	 *
	 * @param string $filename Path to image
	 * @param int $offset Offset at which reading of the image should start
	 * @param int $length Maximum length that should be read
	 * @param bool $force_length True if the length needs to be the specified
	 *			length, false if not. Default: true
	 *
	 * @return bool|string Image data or false if result was empty
	 */
	protected function get_image($filename, $offset, $length, $force_length = true)
	{
		if (empty($this->data))
		{
			$this->data = @file_get_contents($filename, null, null, $offset, $length);
		}

		// Force length to expected one. Return false if data length
		// is smaller than expected length
		if ($force_length === true)
		{
			return (strlen($this->data) < $length) ? false : substr($this->data, $offset, $length) ;
		}

		return empty($this->data) ? false : $this->data;
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
		$data = $this->get_image($filename, 0, self::PNG_IHDR_OFFSET + 3 * self::LONG_SIZE);

		// Check if header fits expected format specified by RFC 2083
		if (substr($data, 0, self::PNG_IHDR_OFFSET - self::LONG_SIZE) !== self::PNG_HEADER || substr($data, self::PNG_IHDR_OFFSET, self::LONG_SIZE) !== 'IHDR')
		{
			return;
		}

		$this->size = unpack('Nwidth/Nheight', substr($data, self::PNG_IHDR_OFFSET + self::LONG_SIZE, self::LONG_SIZE * 2));

		$this->set_image_type(IMAGETYPE_PNG);
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
		// and GIF89a specifications
		$data = $this->get_image($filename, 0, self::GIF_HEADER_SIZE + self::SHORT_SIZE * 2);

		$type = substr($data, 0, self::GIF_HEADER_SIZE);
		if ($type !== self::GIF87A_HEADER && $type !== self::GIF89A_HEADER)
		{
			return;
		}

		$this->size = unpack('vwidth/vheight', substr($data, self::GIF_HEADER_SIZE, self::SHORT_SIZE * 2));

		$this->set_image_type(IMAGETYPE_GIF);
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
		// Do not force the data length
		$data = $this->get_image($filename, 0, self::JPG_MAX_HEADER_SIZE, false);

		// Check if file is jpeg
		if ($data[0] !== "\xFF" || $data[1] !== "\xD8")
		{
			return;
		}

		// Look through file for SOF marker
		for ($i = 2 * self::SHORT_SIZE; $i < strlen($data); $i++)
		{
			if ($data[$i] === "\xFF" && in_array($data[$i+1], array("\xC0", "\xC1", "\xC2", "\xC3", "\xC5", "\xC6", "\xC7", "\xC8", "\xC9", "\xCA", "\xCB", "\xCD", "\xCE", "\xCF")))
			{
				// Extract size info from SOF marker
				list(, $unpacked) = unpack("H*", substr($data, $i + self::SHORT_SIZE, 7));

				// Get width and height from unpacked size info
				$this->size = array(
					'width'		=> hexdec(substr($unpacked, 10, 4)),
					'height'	=> hexdec(substr($unpacked, 6, 4)),
				);

				break;
			}
		}

		$this->set_image_type(IMAGETYPE_JPEG);
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
		$data = $this->get_image($filename, 0, self::PSD_HEADER_SIZE);

		if ($data === false)
		{
			return;
		}

		// Offset for version info is length of header but version is only a
		// 16-bit unsigned value
		$version = unpack('n', substr($data, self::LONG_SIZE, 2));

		// Check if supplied file is a PSD file
		if (substr($data, 0, self::LONG_SIZE) !== self::PSD_SIGNATURE || $version[1] !== 1)
		{
			return;
		}

		$this->size = unpack('Nheight/Nwidth', substr($data, self::PSD_DIMENSIONS_OFFSET, 2 * self::LONG_SIZE));

		$this->set_image_type(IMAGETYPE_PSD);
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
		$data = $this->get_image($filename, 0, self::BMP_HEADER_SIZE);

		// Check if supplied file is a BMP file
		if (substr($data, 0, 2) !== self::BMP_SIGNATURE)
		{
			return;
		}

		$this->size = unpack('lwidth/lheight', substr($data, self::BMP_DIMENSIONS_OFFSET, 2 * self::LONG_SIZE));

		$this->set_image_type(IMAGETYPE_BMP);
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
		// Do not force length of header
		$data = $this->get_image($filename, 0, self::TIF_HEADER_SIZE, false);

		$signature = substr($data, 0, self::SHORT_SIZE);

		if ($signature !== "II" && $signature !== "MM")
		{
			return;
		}

		if ($signature === "II")
		{
			$type_long = 'V';
			$type_short = 'v';
			$this->set_image_type(IMAGETYPE_TIFF_II);
		}
		else
		{
			$type_long = 'N';
			$type_short = 'n';
			$this->set_image_type(IMAGETYPE_TIFF_MM);
		}

		// Get offset of IFD
		list(, $offset) = unpack($type_long, substr($data, self::LONG_SIZE, self::LONG_SIZE));

		// Get size of IFD
		list(, $size_ifd) = unpack($type_short, substr($data, $offset, self::SHORT_SIZE));

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
				$this->size = array_merge($this->size, ($field_type['type'] === self::TIF_TAG_TYPE_SHORT) ? unpack($type_short . 'height', $ifd_value) : unpack($type_long . 'height', $ifd_value));
			}
			else if ($type[1] === self::TIF_TAG_IMAGE_WIDTH)
			{
				$this->size = array_merge($this->size, ($field_type['type'] === self::TIF_TAG_TYPE_SHORT) ? unpack($type_short .'width', $ifd_value) : unpack($type_long . 'width', $ifd_value));
			}

			$offset += self::TIF_IFD_ENTRY_SIZE;
		}
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
		$data = $this->get_image($filename, 0, self::LONG_SIZE);

		// Check if image is WBMP
		if (ord($data[0]) !== 0 || ord($data[1]) !== 0 || $data === substr(self::JPEG_2000_SIGNATURE, 0, 4))
		{
			return;
		}

		$this->size = unpack('Cwidth/Cheight', substr($data, self::SHORT_SIZE, self::SHORT_SIZE));

		$this->set_image_type(IMAGETYPE_WBMP);
	}

	/**
	 * Get dimensions of IFF image
	 *
	 * @param string $filename Filename of image
	 *
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	protected function get_iff_size($filename)
	{
		$data = $this->get_image($filename, 0, self::IFF_HEADER_SIZE);

		$signature = substr($data, 0, self::LONG_SIZE );

		// Check if image is IFF
		if ($signature !== 'FORM' && $signature !== 'FOR4')
		{
			return;
		}

		// Amiga version of IFF
		if ($signature === 'FORM')
		{
			$btmhd_position = strpos($data, 'BMHD');
			$this->size = unpack('nwidth/nheight', substr($data, $btmhd_position + 2 * self::LONG_SIZE, self::LONG_SIZE));
		}
		// Maya version
		else
		{
			$btmhd_position = strpos($data, 'BHD');
			$this->size = unpack('Nwidth/Nheight', substr($data, $btmhd_position + 2 * self::LONG_SIZE - 1, self::LONG_SIZE * 2));
		}

		$this->set_image_type(IMAGETYPE_IFF);
	}

	/**
	 * Get dimensions of JPEG 2000 image
	 *
	 * @param string $filename Filename of image
	 *
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	protected function get_jp2_size($filename)
	{
		$data = $this->get_image($filename, 0, self::JPG_MAX_HEADER_SIZE, false);

		// Check if file is jpeg 2000
		if (substr($data, 0, strlen(self::JPEG_2000_SIGNATURE)) !== self::JPEG_2000_SIGNATURE)
		{
			return;
		}

		// Get SOC position before starting to search for SIZ
		$soc_position = strpos($data, "\xFF\x4F");

		// Make sure we do not get SIZ before SOC
		$data = substr($data, $soc_position);

		$siz_position = strpos($data, "\xFF\x51");

		// Remove SIZ and everything before
		$data = substr($data, $siz_position + self::SHORT_SIZE);

		// Acquire size info from data
		$this->size = unpack('Nwidth/Nheight', substr($data, self::LONG_SIZE, self::LONG_SIZE * 2));

		$this->set_image_type(IMAGETYPE_JPEG2000);
	}
}
