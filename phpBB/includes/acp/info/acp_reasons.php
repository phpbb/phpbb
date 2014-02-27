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
class acp_reasons_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_reasons',
			'title'		=> 'ACP_REASONS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'		=> array('title' => 'ACP_MANAGE_REASONS', 'auth' => 'acl_a_reasons', 'cat' => array('ACP_GENERAL_TASKS')),
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
