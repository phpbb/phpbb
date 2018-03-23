<?php

/**
 * fast-image-size image type webp
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

use \FastImageSize\FastImageSize;

class TypeWebp extends TypeBase
{
	/** @var string RIFF header */
	const WEBP_RIFF_HEADER = "RIFF";

	/** @var string Webp header */
	const WEBP_HEADER = "WEBP";

	/** @var string VP8 chunk header */
	const VP8_HEADER = "VP8";

	/** @var string Simple(lossy) webp format */
	const WEBP_FORMAT_SIMPLE = ' ';

	/** @var string Lossless webp format */
	const WEBP_FORMAT_LOSSLESS = 'L';

	/** @var string Extended webp format */
	const WEBP_FORMAT_EXTENDED = 'X';

	/** @var int WEBP header size needed for retrieving image size */
	const WEBP_HEADER_SIZE = 30;

	/** @var array Size info array */
	protected $size;

	/**
	 * Constructor for webp image type. Adds missing constant if necessary.
	 *
	 * @param FastImageSize $fastImageSize
	 */
	public function __construct(FastImageSize $fastImageSize)
	{
		parent::__construct($fastImageSize);

		if (!defined('IMAGETYPE_WEBP'))
		{
			define('IMAGETYPE_WEBP', 18);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		// Do not force length of header
		$data = $this->fastImageSize->getImage($filename, 0, self::WEBP_HEADER_SIZE);

		$this->size = array();

		$webpFormat = substr($data, 15, 1);

		if (!$this->hasWebpHeader($data) || !$this->isValidFormat($webpFormat))
		{
			return;
		}

		$data = substr($data, 16, 14);

		$this->getWebpSize($data, $webpFormat);

		$this->fastImageSize->setSize($this->size);
		$this->fastImageSize->setImageType(IMAGETYPE_WEBP);
	}

	/**
	 * Check if $data has valid WebP header
	 *
	 * @param string $data Image data
	 *
	 * @return bool True if $data has valid WebP header, false if not
	 */
	protected function hasWebpHeader($data)
	{
		$riffSignature = substr($data, 0, self::LONG_SIZE);
		$webpSignature = substr($data, 8, self::LONG_SIZE);
		$vp8Signature = substr($data, 12, self::SHORT_SIZE + 1);

		return !empty($data) && $riffSignature === self::WEBP_RIFF_HEADER &&
			$webpSignature === self::WEBP_HEADER && $vp8Signature === self::VP8_HEADER;
	}

	/**
	 * Check if $format is a valid WebP format
	 *
	 * @param string $format Format string
	 * @return bool True if format is valid WebP format, false if not
	 */
	protected function isValidFormat($format)
	{
		return in_array($format, array(self::WEBP_FORMAT_SIMPLE, self::WEBP_FORMAT_LOSSLESS, self::WEBP_FORMAT_EXTENDED));
	}

	/**
	 * Get webp size info depending on format type and set size array values
	 *
	 * @param string $data Data string
	 * @param string $format Format string
	 */
	protected function getWebpSize($data, $format)
	{
		switch ($format)
		{
			case self::WEBP_FORMAT_SIMPLE:
				$this->size = unpack('vwidth/vheight', substr($data, 10, 4));
			break;

			case self::WEBP_FORMAT_LOSSLESS:
				// Lossless uses 14-bit values so we'll have to use bitwise shifting
				$this->size = array(
					'width'		=> ord($data[5]) + ((ord($data[6]) & 0x3F) << 8) + 1,
					'height'	=> (ord($data[6]) >> 6) + (ord($data[7]) << 2) + ((ord($data[8]) & 0xF) << 10) + 1,
				);
			break;

			case self::WEBP_FORMAT_EXTENDED:
				// Extended uses 24-bit values cause 14-bit for lossless wasn't weird enough
				$this->size = array(
					'width'		=> ord($data[8]) + (ord($data[9]) << 8) + (ord($data[10]) << 16) + 1,
					'height'	=> ord($data[11]) + (ord($data[12]) << 8) + (ord($data[13]) << 16) + 1,
				);
			break;
		}
	}
}
