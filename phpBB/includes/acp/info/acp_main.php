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

class acp_main_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_main',
			'title'		=> 'ACP_INDEX',
			'modes'		=> array(
				'main'		=> array('title' => 'ACP_INDEX', 'auth' => '', 'cat' => array('ACP_CAT_GENERAL')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}
