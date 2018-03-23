<?php

/**
 * fast-image-size image type iff
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

class TypeIff extends TypeBase
{
	/** @var int IFF header size. Grab more than what should be needed to make
	 * sure we have the necessary data */
	const IFF_HEADER_SIZE = 32;

	/** @var string IFF header for Amiga type */
	const IFF_HEADER_AMIGA = 'FORM';

	/** @var string IFF header for Maya type */
	const IFF_HEADER_MAYA = 'FOR4';

	/** @var string IFF BTMHD for Amiga type */
	const IFF_AMIGA_BTMHD = 'BMHD';

	/** @var string IFF BTMHD for Maya type */
	const IFF_MAYA_BTMHD = 'BHD';

	/** @var string PHP pack format for unsigned short */
	const PACK_UNSIGNED_SHORT = 'n';

	/** @var string PHP pack format for unsigned long */
	const PACK_UNSIGNED_LONG = 'N';

	/** @var string BTMHD of current image */
	protected $btmhd;

	/** @var int Size of current BTMHD */
	protected $btmhdSize;

	/** @var string Current byte type */
	protected $byteType;

	/**
	 * {@inheritdoc}
	 */
	public function getSize($filename)
	{
		$data = $this->fastImageSize->getImage($filename, 0, self::IFF_HEADER_SIZE);

		$signature = $this->getIffSignature($data);

		// Check if image is IFF
		if ($signature === false)
		{
			return;
		}

		// Set type constraints
		$this->setTypeConstraints($signature);

		// Get size from data
		$btmhdPosition = strpos($data, $this->btmhd);
		$size = unpack("{$this->byteType}width/{$this->byteType}height", substr($data, $btmhdPosition + self::LONG_SIZE + strlen($this->btmhd), $this->btmhdSize));

		$this->fastImageSize->setSize($size);
		$this->fastImageSize->setImageType(IMAGETYPE_IFF);
	}

	/**
	 * Get IFF signature from data string
	 *
	 * @param string|bool $data Image data string
	 *
	 * @return false|string Signature if file is a valid IFF file, false if not
	 */
	protected function getIffSignature($data)
	{
		$signature = substr($data, 0, self::LONG_SIZE);

		// Check if image is IFF
		if ($signature !== self::IFF_HEADER_AMIGA && $signature !== self::IFF_HEADER_MAYA)
		{
			return false;
		}
		else
		{
			return $signature;
		}
	}

	/**
	 * Set type constraints for current image
	 *
	 * @param string $signature IFF signature of image
	 */
	protected function setTypeConstraints($signature)
	{
		// Amiga version of IFF
		if ($signature === 'FORM')
		{
			$this->btmhd = self::IFF_AMIGA_BTMHD;
			$this->btmhdSize = self::LONG_SIZE;
			$this->byteType = self::PACK_UNSIGNED_SHORT;
		}
		// Maya version
		else
		{
			$this->btmhd = self::IFF_MAYA_BTMHD;
			$this->btmhdSize = self::LONG_SIZE * 2;
			$this->byteType = self::PACK_UNSIGNED_LONG;
		}
	}
}
