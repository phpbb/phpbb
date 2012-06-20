<?php
/**
*
* @package acp
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @package module_install
*/
class acp_modules_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_modules',
			'title'		=> 'ACP_MODULE_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'acp'		=> array('title' => 'ACP', 'auth' => 'acl_a_modules', 'cat' => array('ACP_MODULE_MANAGEMENT')),
				'ucp'		=> array('title' => 'UCP', 'auth' => 'acl_a_modules', 'cat' => array('ACP_MODULE_MANAGEMENT')),
				'mcp'		=> array('title' => 'MCP', 'auth' => 'acl_a_modules', 'cat' => array('ACP_MODULE_MANAGEMENT')),
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
