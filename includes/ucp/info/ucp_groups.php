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

class ucp_groups_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_groups',
			'title'		=> 'UCP_USERGROUPS',
			'modes'		=> array(
				'membership'	=> array('title' => 'UCP_USERGROUPS_MEMBER', 'auth' => '', 'cat' => array('UCP_USERGROUPS')),
				'manage'		=> array('title' => 'UCP_USERGROUPS_MANAGE', 'auth' => '', 'cat' => array('UCP_USERGROUPS')),
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
