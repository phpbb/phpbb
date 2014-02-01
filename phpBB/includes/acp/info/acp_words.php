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
class acp_words_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_words',
			'title'		=> 'ACP_WORDS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'words'		=> array('title' => 'ACP_WORDS', 'auth' => 'acl_a_words', 'cat' => array('ACP_MESSAGES')),
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
