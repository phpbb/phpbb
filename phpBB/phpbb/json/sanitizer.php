<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

declare(strict_types=1);

namespace phpbb\json;

use phpbb\request\type_cast_helper;

/**
 * JSON sanitizer class
 */
class sanitizer
{
	/**
	 * Sanitize json data
	 *
	 * @param array $data Data to sanitize
	 *
	 * @return array Sanitized data
	 */
	public static function sanitize(array $data) : array
	{
		if (!empty($data))
		{
			$json_sanitizer = function (&$value)
			{
				$type_cast_helper = new type_cast_helper();
				$type_cast_helper->set_var($value, $value, gettype($value), true);
			};
			array_walk_recursive($data, $json_sanitizer);
		}

		return $data;
	}

	/**
	 * Decode and sanitize json data
	 *
	 * @param string $json JSON data string
	 *
	 * @return array Data array
	 */
	public static function decode(string $json) : array
	{
		$data = json_decode($json, true);
		return !empty($data) ? self::sanitize($data) : [];
	}
}
