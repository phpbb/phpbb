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
class acp_bots_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_bots',
			'title'		=> 'ACP_BOTS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'bots'		=> array('title' => 'ACP_BOTS', 'auth' => 'acl_a_bots', 'cat' => array('ACP_GENERAL_TASKS')),
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
