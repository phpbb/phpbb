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
class ucp_groups_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_groups',
			'title'		=> 'UCP_USERGROUPS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'membership'	=> array('title' => 'UCP_USERGROUPS_MEMBER', 'auth' => '', 'cat' => array('UCP_USERGROUPS')),
				'manage'		=> array('title' => 'UCP_USERGROUPS_MANAGE', 'auth' => '', 'cat' => array('UCP_USERGROUPS')),
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
