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

class acp_bots_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_bots',
			'title'		=> 'ACP_BOTS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'bots'		=> array('title' => 'ACP_BOTS', 'auth' => 'acl_a_bots', 'cat' => array('ACP_GENERAL_TASKS')),
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
