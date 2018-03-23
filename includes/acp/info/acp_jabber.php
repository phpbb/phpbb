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

class acp_jabber_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_jabber',
			'title'		=> 'ACP_JABBER_SETTINGS',
			'modes'		=> array(
				'settings'		=> array('title' => 'ACP_JABBER_SETTINGS', 'auth' => 'acl_a_jabber', 'cat' => array('ACP_CLIENT_COMMUNICATION')),
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
