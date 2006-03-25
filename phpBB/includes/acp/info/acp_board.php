<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package module_install
*/
class acp_board_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_board',
			'title'		=> 'ACP_BOARD_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'auth'		=> array('title' => 'ACP_AUTH_SETTINGS', 'auth' => 'acl_a_server'),
				'avatar'	=> array('title' => 'ACP_AVATAR_SETTINGS', 'auth' => 'acl_a_board'),
				'default'	=> array('title' => 'ACP_BOARD_DEFAULTS', 'auth' => 'acl_a_defaults'),
				'settings'	=> array('title' => 'ACP_BOARD_SETTINGS', 'auth' => 'acl_a_board'),
				'cookie'	=> array('title' => 'ACP_COOKIE_SETTINGS', 'auth' => 'acl_a_cookies'),
				'email'		=> array('title' => 'ACP_EMAIL_SETTINGS', 'auth' => 'acl_a_server'),
				'load'		=> array('title' => 'ACP_LOAD_SETTINGS', 'auth' => 'acl_a_server'),
				'server'	=> array('title' => 'ACP_SERVER_SETTINGS', 'auth' => 'acl_a_server'),
				'message'	=> array('title' => 'ACP_MESSAGE_SETTINGS', 'auth' => 'acl_a_defaults'),
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