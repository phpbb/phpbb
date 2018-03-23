<?php

/**
 * fast-image-size image type psd
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

class TypePsd extends TypeBase
{
	/** @var string PSD signature */
	const PSD_SIGNATURE = "8BPS";

	/** @var int PSD header size */
	const PSD_HEADER_SIZE = 22;

	/** @var int PSD dimensions info offset */
	const PSD_DIMENSIONS_OFFSET = 14;

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		$data = $this->fastImageSize->getImage($filename, 0, self::PSD_HEADER_SIZE);

		if ($data === false)
		{
			return;
		}

		// Offset for version info is length of header but version is only a
		// 16-bit unsigned value
		$version = unpack('n', substr($data, self::LONG_SIZE, 2));

		// Check if supplied file is a PSD file
		if (!$this->validPsd($data, $version))
		{
			return;
		}

		$size = unpack('Nheight/Nwidth', substr($data, self::PSD_DIMENSIONS_OFFSET, 2 * self::LONG_SIZE));

		$this->fastImageSize->setSize($size);
		$this->fastImageSize->setImageType(IMAGETYPE_PSD);
	}

	/**
	 * Return whether file is a valid PSD file
	 *
	 * @param string $data Image data string
	 * @param array $version Version array
	 *
	 * @return bool True if image is a valid PSD file, false if not
	 */
	protected function validPsd($data, $version)
	{
		return substr($data, 0, self::LONG_SIZE) === self::PSD_SIGNATURE && $version[1] === 1;
	}
}
