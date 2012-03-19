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
class acp_disallow_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_disallow',
			'title'		=> 'ACP_DISALLOW',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'usernames'		=> array('title' => 'ACP_DISALLOW_USERNAMES', 'auth' => 'acl_a_names', 'cat' => array('ACP_USER_SECURITY')),
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
