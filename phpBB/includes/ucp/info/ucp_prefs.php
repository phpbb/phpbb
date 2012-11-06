<?php
/**
*
* @package ucp
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
				'personal'	=> array('title' => 'UCP_PREFS_PERSONAL', 'auth' => '', 'cat' => array('UCP_PREFS')),
				'post'		=> array('title' => 'UCP_PREFS_POST', 'auth' => '', 'cat' => array('UCP_PREFS')),
				'view'		=> array('title' => 'UCP_PREFS_VIEW', 'auth' => '', 'cat' => array('UCP_PREFS')),
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
