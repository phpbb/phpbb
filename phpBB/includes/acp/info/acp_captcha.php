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

class acp_captcha_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_captcha',
			'title'		=> 'ACP_CAPTCHA',
			'modes'		=> array(
				'visual'		=> array('title' => 'ACP_VC_SETTINGS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
				'img'			=> array('title' => 'ACP_VC_CAPTCHA_DISPLAY', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION'), 'display' => false)
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
