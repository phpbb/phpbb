<?php

/**
 * fast-image-size image type ico
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

class TypeIco extends TypeBase
{
	/** @var string ICO reserved field */
	const ICO_RESERVED = 0;

	/** @var int ICO type field */
	const ICO_TYPE = 1;

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		// Retrieve image data for ICO header and header of first entry.
		// We assume the first entry to have the same size as the other ones.
		$data = $this->fastImageSize->getImage($filename, 0, 2 * self::LONG_SIZE);

		if ($data === false)
		{
			return;
		}

		// Check if header fits expected format
		if (!$this->isValidIco($data))
		{
			return;
		}

		$size = unpack('Cwidth/Cheight', substr($data, self::LONG_SIZE + self::SHORT_SIZE, self::SHORT_SIZE));

		$this->fastImageSize->setSize($size);
		$this->fastImageSize->setImageType(IMAGETYPE_ICO);
	}

	/**
	 * Return whether image is a valid ICO file
	 *
	 * @param string $data Image data string
	 *
	 * @return bool True if file is a valid ICO file, false if not
	 */
	protected function isValidIco($data)
	{
		// Get header
		$header = unpack('vreserved/vtype/vimages', $data);

		return $header['reserved'] === self::ICO_RESERVED && $header['type'] === self::ICO_TYPE && $header['images'] > 0 && $header['images'] <= 255;
	}
}
