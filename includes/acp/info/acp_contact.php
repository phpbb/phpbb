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

/**
* @package module_install
*/
class acp_contact_info
{
	public function module()
	{
		return array(
			'filename'	=> 'acp_contact',
			'title'		=> 'ACP_CONTACT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'contact'	=> array('title' => 'ACP_CONTACT_SETTINGS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
			),
		);
	}
}
