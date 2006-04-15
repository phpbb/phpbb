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
				'settings'		=> array('title' => 'ACP_BOARD_SETTINGS', 'auth' => 'acl_a_board'),
				'features'		=> array('title' => 'ACP_BOARD_FEATURES', 'auth' => 'acl_a_board'),
				'avatar'		=> array('title' => 'ACP_AVATAR_SETTINGS', 'auth' => 'acl_a_board'),
				'message'		=> array('title' => 'ACP_MESSAGE_SETTINGS', 'auth' => 'acl_a_board'),
				'post'			=> array('title' => 'ACP_POST_SETTINGS', 'auth' => 'acl_a_board'),
				'signature'		=> array('title' => 'ACP_SIGNATURE_SETTINGS', 'auth' => 'acl_a_board'),
				'registration'	=> array('title' => 'ACP_REGISTER_SETTINGS', 'auth' => 'acl_a_board'),
				'visual'		=> array('title' => 'ACP_VC_SETTINGS', 'auth' => 'acl_a_board'),

				'auth'		=> array('title' => 'ACP_AUTH_SETTINGS', 'auth' => 'acl_a_server'),
				'email'		=> array('title' => 'ACP_EMAIL_SETTINGS', 'auth' => 'acl_a_server'),

				'cookie'	=> array('title' => 'ACP_COOKIE_SETTINGS', 'auth' => 'acl_a_server'),
				'server'	=> array('title' => 'ACP_SERVER_SETTINGS', 'auth' => 'acl_a_server'),
				'security'	=> array('title' => 'ACP_SECURITY_SETTINGS', 'auth' => 'acl_a_server'),
				'load'		=> array('title' => 'ACP_LOAD_SETTINGS', 'auth' => 'acl_a_server'),
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

// a_cookies, a_defaults weg
?>