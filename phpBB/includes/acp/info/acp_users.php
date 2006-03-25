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
class acp_users_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_users',
			'title'		=> 'ACP_USER_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'overview'		=> array('title' => 'ACP_MANAGE_USERS', 'auth' => 'acl_a_user'),
				'feedback'		=> array('title' => 'ACP_USER_FEEDBACK', 'auth' => 'acl_a_user', 'display' => false),
				'profile'		=> array('title' => 'ACP_USER_PROFILE', 'auth' => 'acl_a_user', 'display' => false),
				'prefs'			=> array('title' => 'ACP_USER_PREFS', 'auth' => 'acl_a_user', 'display' => false),
				'avatar'		=> array('title' => 'ACP_USER_AVATAR', 'auth' => 'acl_a_user', 'display' => false),
				'rank'			=> array('title' => 'ACP_USER_RANK', 'auth' => 'acl_a_user', 'display' => false),
				'sig'			=> array('title' => 'ACP_USER_SIG', 'auth' => 'acl_a_user', 'display' => false),
				'groups'		=> array('title' => 'ACP_USER_GROUPS', 'auth' => 'acl_a_user && acl_a_group', 'display' => false),
				'perm'			=> array('title' => 'ACP_USER_PERM', 'auth' => 'acl_a_user && acl_a_viewauth', 'display' => false),
				'attach'		=> array('title' => 'ACP_USER_ATTACH', 'auth' => 'acl_a_user', 'display' => false),
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