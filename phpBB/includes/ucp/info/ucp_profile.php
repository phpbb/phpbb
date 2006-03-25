<?php
/** 
*
* @package ucp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
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
				'reg_details'	=> array('title' => 'UCP_PROFILE_REG_DETAILS', 'auth' => ''),
				'profile_info'	=> array('title' => 'UCP_PROFILE_PROFILE_INFO', 'auth' => ''),
				'signature'		=> array('title' => 'UCP_PROFILE_SIGNATURE', 'auth' => ''),
				'avatar'		=> array('title' => 'UCP_PROFILE_AVATAR', 'auth' => ''),
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