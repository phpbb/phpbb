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

class mcp_ban_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_ban',
			'title'		=> 'MCP_BAN',
			'modes'		=> array(
				'user'		=> array('title' => 'MCP_BAN_USERNAMES', 'auth' => 'acl_m_ban', 'cat' => array('MCP_BAN')),
				'ip'		=> array('title' => 'MCP_BAN_IPS', 'auth' => 'acl_m_ban', 'cat' => array('MCP_BAN')),
				'email'		=> array('title' => 'MCP_BAN_EMAILS', 'auth' => 'acl_m_ban', 'cat' => array('MCP_BAN')),
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
