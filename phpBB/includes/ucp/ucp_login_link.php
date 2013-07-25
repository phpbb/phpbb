<?php
/**
*
* @package ucp
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* ucp_login_link
* Allows users of external accounts link those accounts to their phpBB accounts
* during an attempted login.
* @package ucp
*/
class ucp_login_link
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $phpbb_container, $request, $template;

		$auth_provider = 'auth.provider.' . $request->variable('auth_provider', $config['auth_method']);
		$auth_provider = $phpbb_container->get($auth_provider);

		// Process POST and GET data
		$login_error = false;
		$login_username = '';

		// Common template elements
		$template->assign_vars(array(
			'PASSWORD_CREDENTIAL'	=> 'password',
			'USERNAME_CREDENTIAL'	=> 'username',
		));

		// Registration template
		$register_link = 'ucp.php?mode=register';

		$template->assign_vars(array(
			'REGISTER_LINK'	=>	redirect($register_link, true),
		));

		// Link to existing account template
		$template->assign_vars(array(
			'LOGIN_ERROR'		=> $login_error,
			'LOGIN_USERNAME'	=> $login_username,
		));

		$this->tpl_name = 'ucp_login_link';
		$this->page_title = 'UCP_LOGIN_LINK';
	}
}
