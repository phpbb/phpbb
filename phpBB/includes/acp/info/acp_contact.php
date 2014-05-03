<?php
/**
*
* @package acp
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @package module_install
*/
class acp_contact_info
{
	public function module()
	{
		return array(
			'filename'	=> 'acp_contact',
			'title'		=> 'ACP_CONTACT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'contact'	=> array('title' => 'ACP_CONTACT_SETTINGS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
			),
		);
	}
}
