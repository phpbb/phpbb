<?php

/**
 * fast-image-size image type png
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

class TypePng extends TypeBase
{
	/** @var string PNG header */
	const PNG_HEADER = "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a";

	/** @var int PNG IHDR offset */
	const PNG_IHDR_OFFSET = 12;

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		// Retrieve image data including the header, the IHDR tag, and the
		// following 2 chunks for the image width and height
		$data = $this->fastImageSize->getImage($filename, 0, self::PNG_IHDR_OFFSET + 3 * self::LONG_SIZE);

		// Check if header fits expected format specified by RFC 2083
		if (substr($data, 0, self::PNG_IHDR_OFFSET - self::LONG_SIZE) !== self::PNG_HEADER || substr($data, self::PNG_IHDR_OFFSET, self::LONG_SIZE) !== 'IHDR')
		{
			return;
		}

		$size = unpack('Nwidth/Nheight', substr($data, self::PNG_IHDR_OFFSET + self::LONG_SIZE, self::LONG_SIZE * 2));

		$this->fastImageSize->setSize($size);
		$this->fastImageSize->setImageType(IMAGETYPE_PNG);
	}
}
