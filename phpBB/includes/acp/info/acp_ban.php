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
class acp_ban_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_ban',
			'title'		=> 'ACP_BAN',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'email'		=> array('title' => 'ACP_BAN_EMAILS', 'auth' => 'acl_a_ban', 'cat' => array('ACP_USER_SECURITY')),
				'ip'		=> array('title' => 'ACP_BAN_IPS', 'auth' => 'acl_a_ban', 'cat' => array('ACP_USER_SECURITY')),
				'user'		=> array('title' => 'ACP_BAN_USERNAMES', 'auth' => 'acl_a_ban', 'cat' => array('ACP_USER_SECURITY')),
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
