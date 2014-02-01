<?php
/**
*
* @package acp
* @copyright (c) 2006 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @package module_install
*/
class acp_inactive_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_inactive',
			'title'		=> 'ACP_INACTIVE_USERS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'list'		=> array('title' => 'ACP_INACTIVE_USERS', 'auth' => 'acl_a_user', 'cat' => array('ACP_CAT_USERS')),
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
