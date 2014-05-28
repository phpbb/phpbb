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

class ucp_auth_link_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_auth_link',
			'title'		=> 'UCP_AUTH_LINK',
			'modes'		=> array(
				'auth_link'	=> array('title' => 'UCP_AUTH_LINK_MANAGE', 'auth' => 'authmethod_oauth', 'cat' => array('UCP_PROFILE')),
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
