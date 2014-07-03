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

class acp_inactive_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_inactive',
			'title'		=> 'ACP_INACTIVE_USERS',
			'modes'		=> array(
				'list'		=> array('title' => 'ACP_INACTIVE_USERS', 'auth' => 'acl_a_user', 'cat' => array('ACP_CAT_USERS')),
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
