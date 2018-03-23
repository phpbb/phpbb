<?php

/**
 * fast-image-size image type jpeg
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

class TypeJpeg extends TypeBase
{
	/** @var int JPEG max header size. Headers can be bigger, but we'll abort
	 *			going through the header after this */
	const JPEG_MAX_HEADER_SIZE = 124576;

	/** @var string JPEG header */
	const JPEG_HEADER = "\xFF\xD8";

	/** @var string Start of frame marker */
	const SOF_START_MARKER = "\xFF";

	/** @var string End of image (EOI) marker */
	const JPEG_EOI_MARKER = "\xD9";

	/** @var array JPEG SOF markers */
	protected $sofMarkers = array(
		"\xC0",
		"\xC1",
		"\xC2",
		"\xC3",
		"\xC5",
		"\xC6",
		"\xC7",
		"\xC9",
		"\xCA",
		"\xCB",
		"\xCD",
		"\xCE",
		"\xCF"
	);

	/** @var array JPEG APP markers */
	protected $appMarkers = array(
		"\xE0",
		"\xE1",
		"\xE2",
		"\xE3",
		"\xEC",
		"\xED",
		"\xEE",
	);

	/** @var string|bool JPEG data stream */
	protected $data = '';

	/** @var int Data length */
	protected $dataLength = 0;

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		// Do not force the data length
		$this->data = $this->fastImageSize->getImage($filename, 0, self::JPEG_MAX_HEADER_SIZE, false);

		// Check if file is jpeg
		if ($this->data === false || substr($this->data, 0, self::SHORT_SIZE) !== self::JPEG_HEADER)
		{
			return;
		}

		// Look through file for SOF marker
		$size = $this->getSizeInfo();

		$this->fastImageSize->setSize($size);
		$this->fastImageSize->setImageType(IMAGETYPE_JPEG);
	}

	/**
	 * Get size info from image data
	 *
	 * @return array An array with the image's size info or an empty array if
	 *		size info couldn't be found
	 */
	protected function getSizeInfo()
	{
		$size = array();
		// since we check $i + 1 we need to stop one step earlier
		$this->dataLength = strlen($this->data) - 1;

		$sofStartRead = true;

		// Look through file for SOF marker
		for ($i = 2; $i < $this->dataLength; $i++)
		{
			$marker = $this->getNextMarker($i, $sofStartRead);

			if (in_array($marker, $this->sofMarkers))
			{
				// Extract size info from SOF marker
				return $this->extractSizeInfo($i);
			}
			else
			{
				// Extract length only
				$markerLength = $this->extractMarkerLength($i);

				if ($markerLength < 2)
				{
					return $size;
				}

				$i += $markerLength - 1;
				continue;
			}
		}

		return $size;
	}

	/**
	 * Extract marker length from data
	 *
	 * @param int $i Current index
	 * @return int Length of current marker
	 */
	protected function extractMarkerLength($i)
	{
		// Extract length only
		list(, $unpacked) = unpack("H*", substr($this->data, $i, self::LONG_SIZE));

		// Get width and height from unpacked size info
		$markerLength = hexdec(substr($unpacked, 0, 4));

		return $markerLength;
	}

	/**
	 * Extract size info from data
	 *
	 * @param int $i Current index
	 * @return array Size info of current marker
	 */
	protected function extractSizeInfo($i)
	{
		// Extract size info from SOF marker
		list(, $unpacked) = unpack("H*", substr($this->data, $i - 1 + self::LONG_SIZE, self::LONG_SIZE));

		// Get width and height from unpacked size info
		$size = array(
			'width'		=> hexdec(substr($unpacked, 4, 4)),
			'height'	=> hexdec(substr($unpacked, 0, 4)),
		);

		return $size;
	}

	/**
	 * Get next JPEG marker in file
	 *
	 * @param int $i Current index
	 * @param bool $sofStartRead Flag whether SOF start padding was already read
	 *
	 * @return string Next JPEG marker in file
	 */
	protected function getNextMarker(&$i, &$sofStartRead)
	{
		$this->skipStartPadding($i, $sofStartRead);

		do {
			if ($i >= $this->dataLength)
			{
				return self::JPEG_EOI_MARKER;
			}
			$marker = $this->data[$i];
			$i++;
		} while ($marker == self::SOF_START_MARKER);

		return $marker;
	}

	/**
	 * Skip over any possible padding until we reach a byte without SOF start
	 * marker. Extraneous bytes might need to require proper treating.
	 *
	 * @param int $i Current index
	 * @param bool $sofStartRead Flag whether SOF start padding was already read
	 */
	protected function skipStartPadding(&$i, &$sofStartRead)
	{
		if (!$sofStartRead)
		{
			while ($this->data[$i] !== self::SOF_START_MARKER)
			{
				$i++;
			}
		}
	}
}
