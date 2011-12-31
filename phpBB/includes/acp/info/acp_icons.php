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
class acp_icons_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_icons',
			'title'		=> 'ACP_ICONS_SMILIES',
			'version'	=> '1.0.0',
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
