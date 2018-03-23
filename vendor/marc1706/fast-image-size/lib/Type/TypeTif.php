<?php

/**
 * fast-image-size image type tif
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

class TypeTif extends TypeBase
{
	/** @var int TIF header size. The header might be larger but the dimensions
	 *			should be in the first 51200 bytes */
	const TIF_HEADER_SIZE = 51200;

	/** @var int TIF tag for image height */
	const TIF_TAG_IMAGE_HEIGHT = 257;

	/** @var int TIF tag for image width */
	const TIF_TAG_IMAGE_WIDTH = 256;

	/** @var int TIF tag for exif IFD offset */
	const TIF_TAG_EXIF_OFFSET = 34665;

	/** @var int TIF tag for Image X resolution in pixels */
	const TIF_TAG_EXIF_IMAGE_WIDTH = 0xA002;

	/** @var int TIF tag for Image Y resolution in pixels */
	const TIF_TAG_EXIF_IMAGE_HEIGHT = 0xA003;

	/** @var int TIF tag type for short */
	const TIF_TAG_TYPE_SHORT = 3;

	/** @var int TIF IFD entry size */
	const TIF_IFD_ENTRY_SIZE = 12;

	/** @var string TIF signature of intel type */
	const TIF_SIGNATURE_INTEL = 'II';

	/** @var string TIF signature of motorola type */
	const TIF_SIGNATURE_MOTOROLA = 'MM';

	/** @var array Size info array */
	protected $size;

	/** @var string Bit type of long field */
	public $typeLong;

	/** @var string Bit type of short field */
	public $typeShort;

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		// Do not force length of header
		$data = $this->fastImageSize->getImage($filename, 0, self::TIF_HEADER_SIZE, false);

		$this->size = array();

		$signature = substr($data, 0, self::SHORT_SIZE);

		if (!in_array($signature, array(self::TIF_SIGNATURE_INTEL, self::TIF_SIGNATURE_MOTOROLA)))
		{
			return;
		}

		// Set byte type
		$this->setByteType($signature);

		// Get offset of IFD
		list(, $offset) = unpack($this->typeLong, substr($data, self::LONG_SIZE, self::LONG_SIZE));

		// Get size of IFD
		list(, $sizeIfd) = unpack($this->typeShort, substr($data, $offset, self::SHORT_SIZE));

		// Skip 2 bytes that define the IFD size
		$offset += self::SHORT_SIZE;

		// Ensure size can't exceed data length
		$sizeIfd = min($sizeIfd, floor((strlen($data) - $offset) / self::TIF_IFD_ENTRY_SIZE));

		// Filter through IFD
		for ($i = 0; $i < $sizeIfd; $i++)
		{
			// Get IFD tag
			$type = unpack($this->typeShort, substr($data, $offset, self::SHORT_SIZE));

			// Get field type of tag
			$fieldType = unpack($this->typeShort . 'type', substr($data, $offset + self::SHORT_SIZE, self::SHORT_SIZE));

			// Get IFD entry
			$ifdValue = substr($data, $offset + 2 * self::LONG_SIZE, self::LONG_SIZE);

			// Set size of field
			$this->setSizeInfo($type[1], $fieldType['type'], $ifdValue);

			$offset += self::TIF_IFD_ENTRY_SIZE;
		}

		$this->fastImageSize->setSize($this->size);
	}

	/**
	 * Set byte type based on signature in header
	 *
	 * @param string $signature Header signature
	 */
	public function setByteType($signature)
	{
		if ($signature === self::TIF_SIGNATURE_INTEL)
		{
			$this->typeLong = 'V';
			$this->typeShort = 'v';
			$this->size['type'] = IMAGETYPE_TIFF_II;
		}
		else
		{
			$this->typeLong = 'N';
			$this->typeShort = 'n';
			$this->size['type'] = IMAGETYPE_TIFF_MM;
		}
	}

	/**
	 * Set size info
	 *
	 * @param int $dimensionType Type of dimension. Either width or height
	 * @param int $fieldLength Length of field. Either short or long
	 * @param string $ifdValue String value of IFD field
	 */
	protected function setSizeInfo($dimensionType, $fieldLength, $ifdValue)
	{
		// Set size of field
		$fieldSize = $fieldLength === self::TIF_TAG_TYPE_SHORT ? $this->typeShort : $this->typeLong;

		// Get actual dimensions from IFD
		if ($dimensionType === self::TIF_TAG_IMAGE_HEIGHT)
		{
			$this->size = array_merge($this->size, unpack($fieldSize . 'height', $ifdValue));
		}
		else if ($dimensionType === self::TIF_TAG_IMAGE_WIDTH)
		{
			$this->size = array_merge($this->size, unpack($fieldSize . 'width', $ifdValue));
		}
	}
}
