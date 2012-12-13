<?php
/**
*
* @package acp
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @package module_install
*/
class acp_extensions_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_extensions',
			'title'		=> 'ACP_EXTENSIONS_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'		=> array('title' => 'ACP_EXTENSIONS', 'auth' => 'acl_a_extensions', 'cat' => array('ACP_EXTENSIONS_MANAGEMENT')),
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
