<?php
/**
*
* @package mcp
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @package module_install
*/
class mcp_queue_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_queue',
			'title'		=> 'MCP_QUEUE',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'unapproved_topics'	=> array('title' => 'MCP_QUEUE_UNAPPROVED_TOPICS', 'auth' => 'aclf_m_approve', 'cat' => array('MCP_QUEUE')),
				'unapproved_posts'	=> array('title' => 'MCP_QUEUE_UNAPPROVED_POSTS', 'auth' => 'aclf_m_approve', 'cat' => array('MCP_QUEUE')),
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
