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

class acp_foobar_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_foobar',
			'title'		=> 'ACP Foobar',
			'modes'		=> array(
				'test'		=> array('title' => 'Test', 'auth' => '', 'cat' => array('ACP_GENERAL')),
			),
		);
	}
}
