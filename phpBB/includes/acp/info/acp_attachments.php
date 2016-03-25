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

class acp_attachments_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_attachments',
			'title'		=> 'ACP_ATTACHMENTS',
			'modes'		=> array(
				'attach'		=> array('title' => 'ACP_ATTACHMENT_SETTINGS', 'auth' => 'acl_a_attach', 'cat' => array('ACP_BOARD_CONFIGURATION', 'ACP_ATTACHMENTS')),
				'extensions'	=> array('title' => 'ACP_MANAGE_EXTENSIONS', 'auth' => 'acl_a_attach', 'cat' => array('ACP_ATTACHMENTS')),
				'ext_groups'	=> array('title' => 'ACP_EXTENSION_GROUPS', 'auth' => 'acl_a_attach', 'cat' => array('ACP_ATTACHMENTS')),
				'orphan'		=> array('title' => 'ACP_ORPHAN_ATTACHMENTS', 'auth' => 'acl_a_attach', 'cat' => array('ACP_ATTACHMENTS')),
				'manage'		=> array('title' => 'ACP_MANAGE_ATTACHMENTS', 'auth' => 'acl_a_attach', 'cat' => array('ACP_ATTACHMENTS')),
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
