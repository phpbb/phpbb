<?php
/**
*
* @package acp
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @package module_install
*/
class acp_attachments_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_attachments',
			'title'		=> 'ACP_ATTACHMENTS',
			'version'	=> '1.0.0',
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
