<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

class acp_profile_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_profile',
			'title'		=> 'ACP_CUSTOM_PROFILE_FIELDS',
			'modes'		=> array(
				'profile'	=> array('title' => 'ACP_CUSTOM_PROFILE_FIELDS', 'auth' => 'acl_a_profile', 'cat' => array('ACP_CAT_USERS')),
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
