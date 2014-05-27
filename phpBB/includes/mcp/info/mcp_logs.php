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

class mcp_logs_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_logs',
			'title'		=> 'MCP_LOGS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'front'			=> array('title' => 'MCP_LOGS_FRONT', 'auth' => 'acl_m_ || aclf_m_', 'cat' => array('MCP_LOGS')),
				'forum_logs'	=> array('title' => 'MCP_LOGS_FORUM_VIEW', 'auth' => 'acl_m_,$id', 'cat' => array('MCP_LOGS')),
				'topic_logs'	=> array('title' => 'MCP_LOGS_TOPIC_VIEW', 'auth' => 'acl_m_,$id', 'cat' => array('MCP_LOGS')),
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
