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

class acp_ban_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_ban',
			'title'		=> 'ACP_BAN',
			'modes'		=> array(
				'email'		=> array('title' => 'ACP_BAN_EMAILS', 'auth' => 'acl_a_ban', 'cat' => array('ACP_USER_SECURITY')),
				'ip'		=> array('title' => 'ACP_BAN_IPS', 'auth' => 'acl_a_ban', 'cat' => array('ACP_USER_SECURITY')),
				'user'		=> array('title' => 'ACP_BAN_USERNAMES', 'auth' => 'acl_a_ban', 'cat' => array('ACP_USER_SECURITY')),
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
