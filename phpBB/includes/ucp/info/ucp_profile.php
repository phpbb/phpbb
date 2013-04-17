<?php
/**
*
* @package ucp
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @package module_install
*/
class ucp_profile_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_profile',
			'title'		=> 'UCP_PROFILE',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'profile_info'	=> array('title' => 'UCP_PROFILE_PROFILE_INFO', 'auth' => 'acl_u_chgprofileinfo', 'cat' => array('UCP_PROFILE')),
				'signature'		=> array('title' => 'UCP_PROFILE_SIGNATURE', 'auth' => 'acl_u_sig', 'cat' => array('UCP_PROFILE')),
				'avatar'		=> array('title' => 'UCP_PROFILE_AVATAR', 'auth' => 'cfg_allow_avatar', 'cat' => array('UCP_PROFILE')),
				'reg_details'	=> array('title' => 'UCP_PROFILE_REG_DETAILS', 'auth' => '', 'cat' => array('UCP_PROFILE')),
				'autologin_keys'=> array('title' => 'UCP_PROFILE_AUTOLOGIN_KEYS', 'auth' => '', 'cat' => array('UCP_PROFILE')),
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
