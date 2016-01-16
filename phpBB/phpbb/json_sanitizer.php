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

namespace phpbb;

/**
* JSON sanitizer class
*/
class json_sanitizer
{
	/**
	 * Sanitize json data
	 *
	 * @param array $data Data to sanitize
	 *
	 * @return array Sanitized data
	 */
	static public function sanitize($data)
	{
		if (!empty($data))
		{
			$json_sanitizer = function (&$value, $key) {
				$type_cast_helper = new \phpbb\request\type_cast_helper();
				$type_cast_helper->set_var($value, $value, gettype($value), true);
			};
			array_walk_recursive($data, $json_sanitizer);
		}

		return $data;
	}
}
