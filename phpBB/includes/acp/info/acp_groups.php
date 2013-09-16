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
class acp_groups_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_groups',
			'title'		=> 'ACP_GROUPS_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'manage'		=> array('title' => 'ACP_GROUPS_MANAGE', 'auth' => 'acl_a_group', 'cat' => array('ACP_GROUPS', 'ACP_QUICK_ACCESS')),
				'position'		=> array('title' => 'ACP_GROUPS_POSITION', 'auth' => 'acl_a_group', 'cat' => array('ACP_GROUPS')),
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
