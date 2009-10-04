<?php
/**
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
*/
class acp_send_statistics_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_send_statistics',
			'title'		=> 'ACP_SEND_STATISTICS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'send_statistics'		=> array('title' => 'ACP_SEND_STATISTICS', 'auth' => 'acl_a_server', 'cat' => array('ACP_SERVER_CONFIGURATION')),
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