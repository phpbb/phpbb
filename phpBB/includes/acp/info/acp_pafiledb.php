<?php
/**
*
* @package acp
* @version $Id: acp_pafiledb.php,v 1.2 2008/10/26 08:50:23 orynider Exp $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
*/
class acp_pafiledb_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_pafiledb',
			'title'		=> 'ACP_PAFILEDB_MANAGEMENT',
			'version'	=> '2.9.0',
			'modes'		=> array(
				'settings'			=> array('title' => 'ACP_PA_SETTINGS', 'auth' 	=> 'acl_a_pafiledb', 'cat' => array('ACP_MANAGE_PAFILEDB')),
				'cat_manage'		=> array('title' => 'ACP_PA_CAT', 'auth' 		=> 'acl_a_pafiledb', 'cat' => array('ACP_MANAGE_PAFILEDB')),
				'catauth_manage'	=> array('title' => 'ACP_PA_CAT_AUTH', 'auth' 	=> 'acl_a_pafiledb', 'cat' => array('ACP_MANAGE_PAFILEDB')),
				'ug_auth_manage'	=> array('title' => 'ACP_PA_UG_AUTH', 'auth' 	=> 'acl_a_pafiledb', 'cat' => array('ACP_MANAGE_PAFILEDB')),
				'license_manage'	=> array('title' => 'ACP_PA_LICENCE', 'auth' 	=> 'acl_a_pafiledb', 'cat' => array('ACP_MANAGE_PAFILEDB')),
				'custom_manage'		=> array('title' => 'ACP_PA_CUSTOM', 'auth' 	=> 'acl_a_pafiledb', 'cat' => array('ACP_MANAGE_PAFILEDB')),
				'fchecker_manage'	=> array('title' => 'ACP_PA_FCHECKER', 'auth' 	=> 'acl_a_pafiledb', 'cat' => array('ACP_MANAGE_PAFILEDB')),				
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