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

class phpbb_mock_router extends \phpbb\routing\router
{
	public function get_matcher()
	{
		$this->create_new_url_matcher();
		return parent::get_matcher();
	}

	public function get_generator()
	{
		$this->create_new_url_generator();
		return parent::get_generator();
	}
}
