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
class acp_language_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_language',
			'title'		=> 'ACP_LANGUAGE',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'lang_packs'		=> array('title' => 'ACP_LANGUAGE_PACKS', 'auth' => 'acl_a_language', 'cat' => array('ACP_LANGUAGE')),
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
