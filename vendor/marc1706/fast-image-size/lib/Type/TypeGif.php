<?php

/**
 * fast-image-size image type gif
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

class TypeGif extends TypeBase
{
	/** @var string GIF87a header */
	const GIF87A_HEADER = "\x47\x49\x46\x38\x37\x61";

	/** @var string GIF89a header */
	const GIF89A_HEADER = "\x47\x49\x46\x38\x39\x61";

	/** @var int GIF header size */
	const GIF_HEADER_SIZE = 6;

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		// Get data needed for reading image dimensions as outlined by GIF87a
		// and GIF89a specifications
		$data = $this->fastImageSize->getImage($filename, 0, self::GIF_HEADER_SIZE + self::SHORT_SIZE * 2);

		$type = substr($data, 0, self::GIF_HEADER_SIZE);
		if ($type !== self::GIF87A_HEADER && $type !== self::GIF89A_HEADER)
		{
			return;
		}

		$size = unpack('vwidth/vheight', substr($data, self::GIF_HEADER_SIZE, self::SHORT_SIZE * 2));

		$this->fastImageSize->setSize($size);
		$this->fastImageSize->setImageType(IMAGETYPE_GIF);
	}
}
