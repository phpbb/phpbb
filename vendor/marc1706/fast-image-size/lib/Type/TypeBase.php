<?php

/**
 * fast-image-size image type base
 * @package fast-image-size
 * @copyright (c) Marc Alexander <admin@m-a-styles.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FastImageSize\Type;

use \FastImageSize\FastImageSize;

abstract class TypeBase implements TypeInterface
{
	/** @var FastImageSize */
	protected $fastImageSize;

	/**
	 * Base constructor for image types
	 *
	 * @param FastImageSize $fastImageSize
	 */
	public function __construct(FastImageSize $fastImageSize)
	{
		$this->fastImageSize = $fastImageSize;
	}
}
