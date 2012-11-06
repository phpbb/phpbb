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
class ucp_zebra_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_zebra',
			'title'		=> 'UCP_ZEBRA',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'friends'		=> array('title' => 'UCP_ZEBRA_FRIENDS', 'auth' => '', 'cat' => array('UCP_ZEBRA')),
				'foes'			=> array('title' => 'UCP_ZEBRA_FOES', 'auth' => '', 'cat' => array('UCP_ZEBRA')),
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
