<?php
/** 
*
* @package ucp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package module_install
*/
class ucp_main_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_main',
			'title'		=> 'UCP_MAIN',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'front'			=> array('title' => 'UCP_MAIN_FRONT', 'auth' => ''),
				'subscribed'	=> array('title' => 'UCP_MAIN_SUBSCRIBED', 'auth' => ''),
				'bookmarks'		=> array('title' => 'UCP_MAIN_BOOKMARKS', 'auth' => 'cfg_allow_bookmarks'),
				'drafts'		=> array('title' => 'UCP_MAIN_DRAFTS', 'auth' => ''),
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

?>