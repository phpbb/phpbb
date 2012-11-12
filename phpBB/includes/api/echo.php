<?php
/**
 *
 * @package phpBB3
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * This is a temporary file until some real stuff is written for the API.
 */

class phpbb_api_echo extends phpbb_api
{
	public function echo_get($value)
	{
		return array(
			'value'     => $value,
			'md5'       => md5($value)
		);
	}
}