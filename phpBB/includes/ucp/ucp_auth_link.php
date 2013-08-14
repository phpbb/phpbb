<?php
/**
*
* @package notifications
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

class ucp_auth_link
{
	public $u_action;

	public function main($id, $mode)
	{
		global $config, $request, $template, $phpbb_container, $user;

		$error = array();

		$auth_provider = $phpbb_container->get('auth.provider.' . $config['auth_method']);

		// confirm that the auth provider supports this page
		$provider_data = $auth_provider->get_auth_link_data();
		if ($provider_data === null)
		{
			$error[] = 'UCP_AUTH_LINK_NOT_SUPPORTED';
		}

		$s_hidden_fields = array();
		add_form_key('ucp_auth_link');

		$submit	= $request->variable('submit', false, false, phpbb_request_interface::POST);

		// This path is only for primary actions
		if (!sizeof($error) && $submit)
		{
			if (!check_form_key('ucp_auth_link'))
			{
				$error[] = 'FORM_INVALID';
			}

			if (!sizeof($error))
			{
				// Any post data could be necessary for auth (un)linking
				$link_data = $request->get_super_global(phpbb_request_interface::POST);

				// The current user_id is also necessary
				$link_data['user_id'] = $user->data['user_id'];

				// Tell the provider that the method is auth_link not login_link
				$link_data['link_method'] = 'auth_link';

				if ($request->variable('link', 0, false, phpbb_request_interface::POST))
				{
					$error[] = $auth_provider->link_account($link_data);
				}
				else
				{
					$error[] = $auth_provider->unlink_account($link_data);
				}
			}
		}

		// In some cases, an request to an external server may be required in
		// these cases, the GET parameter 'link' should exist and should be true
		if ($request->variable('link', false))
		{
			// In this case the link data should only be populated with the
			// link_method as the provider dictates how data is returned to it.
			$link_data = array('link_method' => 'auth_link');

			$error[] = $auth_provider->link_account($link_data);
		}

		if (isset($provider_data['VARS']))
		{
			// Handle hidden fields separately
			if (isset($provider_data['VARS']['HIDDEN_FIELDS']))
			{
				$s_hidden_fields = array_merge($s_hidden_fields, $provider_data['VARS']['HIDDEN_FIELDS']);
				unset($provider_data['VARS']['HIDDEN_FIELDS']);
			}

			$template->assign_vars($provider_data['VARS']);
		}

		if (isset($provider_data['BLOCK_VAR_NAME']))
		{
			foreach ($provider_data['BLOCK_VARS'] as $block_vars)
			{
				// See if there are additional hidden fields. This should be an associative array
				if (isset($block_vars['HIDDEN_FIELDS']))
				{
					$block_vars['HIDDEN_FIELDS'] = build_hidden_fields($block_vars['HIDDEN_FIELDS']);
				}

				$template->assign_block_vars($provider_data['BLOCK_VAR_NAME'], $block_vars);
			}
		}

		$s_hidden_fields = build_hidden_fields($s_hidden_fields);

		$template->assign_vars(array(
			'ERROR'	=> $this->build_error_text($error),

			'PROVIDER_TEMPLATE_FILE'	=> $provider_data['TEMPLATE_FILE'],

			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> $this->u_action,
		));

		$this->tpl_name = 'ucp_auth_link';
		$this->page_title = 'UCP_AUTH_LINK';
	}

	private function build_error_text(array $errors)
	{
		global $user;

		// Replace all errors that are language constants
		foreach ($errors as $key => $error)
		{
			if (isset($user->lang[$error]))
			{
				$errors[$key] = $user->lang($error);
			}
		}

		return implode('<br />', $errors);
	}
}
