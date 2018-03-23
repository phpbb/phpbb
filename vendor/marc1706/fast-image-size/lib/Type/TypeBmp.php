<?php

/**
 * fast-image-size image type bmp
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

class TypeBmp extends TypeBase
{
	/** @var int BMP header size needed for retrieving dimensions */
	const BMP_HEADER_SIZE = 26;

	/** @var string BMP signature */
	const BMP_SIGNATURE = "\x42\x4D";

	/** qvar int BMP dimensions offset */
	const BMP_DIMENSIONS_OFFSET = 18;

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		$data = $this->fastImageSize->getImage($filename, 0, self::BMP_HEADER_SIZE);

		// Check if supplied file is a BMP file
		if (substr($data, 0, 2) !== self::BMP_SIGNATURE)
		{
			return;
		}

		$size = unpack('lwidth/lheight', substr($data, self::BMP_DIMENSIONS_OFFSET, 2 * self::LONG_SIZE));

		$this->fastImageSize->setSize($size);
		$this->fastImageSize->setImageType(IMAGETYPE_BMP);
	}
}
