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

class acp_ranks_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_ranks',
			'title'		=> 'ACP_RANKS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'ranks'		=> array('title' => 'ACP_MANAGE_RANKS', 'auth' => 'acl_a_ranks', 'cat' => array('ACP_CAT_USERS')),
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
