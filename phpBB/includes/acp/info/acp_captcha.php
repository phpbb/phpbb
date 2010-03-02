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
class acp_captcha_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_captcha',
			'title'		=> 'ACP_CAPTCHA',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'visual'		=> array('title' => 'ACP_VC_SETTINGS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
				'img'			=> array('title' => 'ACP_VC_CAPTCHA_DISPLAY', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION'), 'display' => false)
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