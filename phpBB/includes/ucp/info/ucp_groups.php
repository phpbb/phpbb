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
class ucp_groups_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_groups',
			'title'		=> 'UCP_USERGROUPS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'membership'	=> array('title' => 'UCP_USERGROUPS_MEMBER', 'auth' => ''),
				'manage'		=> array('title' => 'UCP_USERGROUPS_MANAGE', 'auth' => ''),
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