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

class acp_modules_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_modules',
			'title'		=> 'ACP_MODULE_MANAGEMENT',
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
