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
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

define('IN_PORTAL', true);
define('MXBB_MODULE', false);

/**
* @package acp
*/
class acp_pafiledb
{
	var $u_action;
	var $pafiledb_config;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache, $table_prefix;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
		global $pafiledb_functions, $pafiledb_cache;
		global $mx_request_vars, $pafiledb;
		
		// Main paths
		$phpbb_root_path = $phpbb_root_path = $phpbb_root_path;
		
		include($phpbb_root_path . 'pafiledb/includes/functions_mxp.' . $phpEx);		
		
		$u_action = $this->u_action;

		// instatiate the mx_request_vars class
		$mx_request_vars = new mx_request_vars();
		
		$mode = $mx_request_vars->get('mode', MX_TYPE_NO_TAGS, $mode);		
		$mode = $mx_request_vars->post('mode', MX_TYPE_NO_TAGS, $mode);
			
		// Get action variable other wise set it to the main		

		/*
		$action	= $mx_request_vars->request('action', MX_TYPE_NO_TAGS, '');
		$action = $action ? $action : $mx_request_vars->get('action', MX_TYPE_NO_TAGS, '');
		
		$submit = (isset($_POST['submit'])) ? true : false;
		
		$user_id = $mx_request_vars->request('user_id', MX_TYPE_INT, '');
		$group_id = $mx_request_vars->request('group_id', MX_TYPE_INT, '');
		*/
		
		include($phpbb_root_path . 'pafiledb/pafiledb_common.' . $phpEx);		

		$this->pafiledb_config = $pafiledb_config;
		
		$user->add_lang('mods/pafiledb_admin');
		
		//
		// an array of all expected actions
		//
		$actions = array(
			'settings' => 'settings',
			'cat_manage' => 'cat_manage',
			'catauth_manage' => 'catauth_manage',
			'ug_auth_manage' => 'ug_auth_manage',
			'license_manage' => 'license_manage',
			'custom_manage' => 'custom_manage',
			'fchecker_manage' => 'fchecker_manage' 
		);
		

		//Lets Build the page
		$pafiledb->adminmodule($actions[$mode]);
		$pafiledb->modules[$actions[$mode]]->main($mode);
		
		$this->tpl_name = $pafiledb->modules[$actions[$mode]]->tpl_name;
		$this->page_title = $pafiledb->modules[$actions[$mode]]->page_title;
		$form_key = 'acp_pafiledb';
		add_form_key($form_key);

		$pafiledb->modules[$actions[$mode]]->_pafiledb();
		
	}	
}
?>