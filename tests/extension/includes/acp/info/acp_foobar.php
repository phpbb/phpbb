<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @package module_install
*/
class acp_foobar_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_foobar',
			'title'		=> 'ACP Foobar',
			'version'	=> '3.1.0-dev',
			'modes'		=> array(
				'test'		=> array('title' => 'Test', 'auth' => '', 'cat' => array('ACP_GENERAL')),
			),
		);
	}
}
