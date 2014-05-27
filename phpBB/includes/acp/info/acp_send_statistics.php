<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
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
