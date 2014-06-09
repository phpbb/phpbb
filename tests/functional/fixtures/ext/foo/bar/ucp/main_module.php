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

namespace foo\bar\ucp;

class main_module
{
	var $u_action;

	function main($id, $mode)
	{
		$this->tpl_name = 'foobar';
		$this->page_title = 'Bertie';
	}
}
