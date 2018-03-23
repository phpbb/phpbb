<?php

/**
 * fast-image-size image type jpeg 2000
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

class TypeJp2 extends TypeBase
{
	/** @var string JPEG 2000 signature */
	const JPEG_2000_SIGNATURE = "\x00\x00\x00\x0C\x6A\x50\x20\x20\x0D\x0A\x87\x0A";

	/** @var string JPEG 2000 SOC marker */
	const JPEG_2000_SOC_MARKER = "\xFF\x4F";

	/** @var string JPEG 2000 SIZ marker */
	const JPEG_2000_SIZ_MARKER = "\xFF\x51";

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		$data = $this->fastImageSize->getImage($filename, 0, TypeJpeg::JPEG_MAX_HEADER_SIZE, false);

		// Check if file is jpeg 2000
		if (substr($data, 0, strlen(self::JPEG_2000_SIGNATURE)) !== self::JPEG_2000_SIGNATURE)
		{
			return;
		}

		// Get SOC position before starting to search for SIZ.
		// Make sure we do not get SIZ before SOC by cutting at SOC.
		$data = substr($data, strpos($data, self::JPEG_2000_SOC_MARKER));

		// Remove SIZ and everything before
		$data = substr($data, strpos($data, self::JPEG_2000_SIZ_MARKER) + self::SHORT_SIZE);

		// Acquire size info from data
		$size = unpack('Nwidth/Nheight', substr($data, self::LONG_SIZE, self::LONG_SIZE * 2));

		$this->fastImageSize->setSize($size);
		$this->fastImageSize->setImageType(IMAGETYPE_JPEG2000);
	}
}
