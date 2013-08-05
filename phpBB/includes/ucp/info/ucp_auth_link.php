<?php
/**
*
* @package ucp
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @package module_install
*/
class ucp_auth_link_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_auth_link',
			'title'		=> 'UCP_AUTH_LINK',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'auth_link'	=> array('title' => 'UCP_AUTH_LINK_MANAGE', 'auth' => '', 'cat' => array('UCP_MAIN')),
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
