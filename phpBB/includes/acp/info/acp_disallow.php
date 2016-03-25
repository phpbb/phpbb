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

class acp_disallow_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_disallow',
			'title'		=> 'ACP_DISALLOW',
			'modes'		=> array(
				'usernames'		=> array('title' => 'ACP_DISALLOW_USERNAMES', 'auth' => 'acl_a_names', 'cat' => array('ACP_USER_SECURITY')),
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
