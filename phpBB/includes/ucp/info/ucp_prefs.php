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
class ucp_prefs_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_prefs',
			'title'		=> 'UCP_PREFS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'personal'	=> array('title' => 'UCP_PREFS_PERSONAL', 'auth' => ''),
				'view'		=> array('title' => 'UCP_PREFS_VIEW', 'auth' => ''),
				'post'		=> array('title' => 'UCP_PREFS_POST', 'auth' => ''),
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