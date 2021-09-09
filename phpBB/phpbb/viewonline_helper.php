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

use phpbb\filesystem\helper as filesystem_helper;

/**
* Class to handle viewonline related tasks
*/
class viewonline_helper
{
	/**
	*
	*/
	public function __construct()
	{
	}

	/**
	* Get user page
	*
	* @param string $session_page User's session page
	* @return array Match array filled by preg_match()
	*/
	public function get_user_page($session_page)
	{
		$session_page = filesystem_helper::clean_path($session_page);
		if (strpos($session_page, './') === 0)
		{
			$session_page = substr($session_page, 2);
		}

		preg_match('#^((\.\./)*([a-z0-9/_-]+))#i', $session_page, $on_page);
		if (empty($on_page))
		{
			$on_page[1] = '';
		}

		return $on_page;
	}
}
