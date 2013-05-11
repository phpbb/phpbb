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
class acp_search_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_search',
			'title'		=> 'ACP_SEARCH',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'settings'	=> array('title' => 'ACP_SEARCH_SETTINGS', 'auth' => 'acl_a_search', 'cat' => array('ACP_SERVER_CONFIGURATION')),
				'index'		=> array('title' => 'ACP_SEARCH_INDEX', 'auth' => 'acl_a_search', 'cat' => array('ACP_CAT_DATABASE')),
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
