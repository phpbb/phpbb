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
class ucp_attachments_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_attachments',
			'title'		=> 'UCP_ATTACHMENTS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'attachments'	=> array('title' => 'UCP_MAIN_ATTACHMENTS', 'auth' => 'acl_u_attach', 'cat' => array('UCP_MAIN')),
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
