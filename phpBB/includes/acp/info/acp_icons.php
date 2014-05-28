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

class acp_icons_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_icons',
			'title'		=> 'ACP_ICONS_SMILIES',
			'modes'		=> array(
				'icons'		=> array('title' => 'ACP_ICONS', 'auth' => 'acl_a_icons', 'cat' => array('ACP_MESSAGES')),
				'smilies'	=> array('title' => 'ACP_SMILIES', 'auth' => 'acl_a_icons', 'cat' => array('ACP_MESSAGES')),
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
