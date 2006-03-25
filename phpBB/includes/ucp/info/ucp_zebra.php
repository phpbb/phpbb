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
class ucp_zebra_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_zebra',
			'title'		=> 'UCP_ZEBRA',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'friends'		=> array('title' => 'UCP_ZEBRA_FRIENDS', 'auth' => ''),
				'foes'			=> array('title' => 'UCP_ZEBRA_FOES', 'auth' => ''),
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