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

class mcp_main_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_main',
			'title'		=> 'MCP_MAIN',
			'modes'		=> array(
				'front'			=> array('title' => 'MCP_MAIN_FRONT', 'auth' => '', 'cat' => array('MCP_MAIN')),
				'forum_view'	=> array('title' => 'MCP_MAIN_FORUM_VIEW', 'auth' => 'acl_m_,$id', 'cat' => array('MCP_MAIN')),
				'topic_view'	=> array('title' => 'MCP_MAIN_TOPIC_VIEW', 'auth' => 'acl_m_,$id', 'cat' => array('MCP_MAIN')),
				'post_details'	=> array('title' => 'MCP_MAIN_POST_DETAILS', 'auth' => 'acl_m_,$id || (!$id && aclf_m_)', 'cat' => array('MCP_MAIN')),
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
