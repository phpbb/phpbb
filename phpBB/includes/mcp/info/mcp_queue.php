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

class mcp_queue_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_queue',
			'title'		=> 'MCP_QUEUE',
			'modes'		=> array(
				'unapproved_topics'	=> array('title' => 'MCP_QUEUE_UNAPPROVED_TOPICS', 'auth' => 'aclf_m_approve', 'cat' => array('MCP_QUEUE')),
				'unapproved_posts'	=> array('title' => 'MCP_QUEUE_UNAPPROVED_POSTS', 'auth' => 'aclf_m_approve', 'cat' => array('MCP_QUEUE')),
				'deleted_topics'	=> array('title' => 'MCP_QUEUE_DELETED_TOPICS', 'auth' => 'aclf_m_approve', 'cat' => array('MCP_QUEUE')),
				'deleted_posts'		=> array('title' => 'MCP_QUEUE_DELETED_POSTS', 'auth' => 'aclf_m_approve', 'cat' => array('MCP_QUEUE')),
				'approve_details'	=> array('title' => 'MCP_QUEUE_APPROVE_DETAILS', 'auth' => 'acl_m_approve,$id || (!$id && aclf_m_approve)', 'cat' => array('MCP_QUEUE')),
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
