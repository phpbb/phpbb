<?php
/** 
*
* @package mcp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package module_install
*/
class mcp_warn_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_warn',
			'title'		=> 'MCP_WARN',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'front'				=> array('title' => 'MCP_WARN_FRONT', 'auth' => ''),
				'list'				=> array('title' => 'MCP_WARN_LIST', 'auth' => ''),
				'warn_user'			=> array('title' => 'MCP_WARN_USER', 'auth' => ''),
				'warn_post'			=> array('title' => 'MCP_WARN_POST', 'auth' => 'acl_m_,$id'),
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

?>