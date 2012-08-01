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
class acp_revisions_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_revisions',
			'title'		=> 'ACP_REVISIONS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'settings'		=> array('title' => 'ACP_REVISION_SETTINGS', 'auth' => 'acl_a_revisions', 'cat' => array('ACP_REVISIONS')),
				'purge'			=> array('title' => 'ACP_REVISIONS_PURGE', 'auth' => 'acl_a_revisions', 'cat' => array('ACP_REVISIONS')),
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
