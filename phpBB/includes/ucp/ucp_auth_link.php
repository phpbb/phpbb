<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
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
	/**
	* @var string
	*/
	public $u_action;

	/**
	* Generates the ucp_auth_link page and handles the auth link process
	*
	* @param	int		$id
	* @param	string	$mode
	*/
	public function main($id, $mode)
	{
		global $request, $template, $phpbb_container, $user;

		$error = array();

		/* @var $provider_collection \phpbb\auth\provider_collection */
		$provider_collection = $phpbb_container->get('auth.provider_collection');
		$auth_provider = $provider_collection->get_provider();

		// confirm that the auth provider supports this page
		$provider_data = $auth_provider->get_auth_link_data();
		if ($provider_data === null)
		{
			$error[] = 'UCP_AUTH_LINK_NOT_SUPPORTED';
		}

		$s_hidden_fields = array();
		add_form_key('ucp_auth_link');

		$submit	= $request->variable('submit', false, false, \phpbb\request\request_interface::POST);

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
				$link_data = $request->get_super_global(\phpbb\request\request_interface::POST);

				// The current user_id is also necessary
				$link_data['user_id'] = $user->data['user_id'];

				// Tell the provider that the method is auth_link not login_link
				$link_data['link_method'] = 'auth_link';

				if ($request->variable('link', 0, false, \phpbb\request\request_interface::POST))
				{
					$error[] = $auth_provider->link_account($link_data);
				}
				else
				{
					$error[] = $auth_provider->unlink_account($link_data);
				}

				// Template data may have changed, get new data
				$provider_data = $auth_provider->get_auth_link_data();
			}
		}

		// In some cases, a request to an external server may be required. In
		// these cases, the GET parameter 'link' should exist and should be true
		if ($request->variable('link', false))
		{
			// In this case the link data should only be populated with the
			// link_method as the provider dictates how data is returned to it.
			$link_data = array('link_method' => 'auth_link');

			$error[] = $auth_provider->link_account($link_data);

			// Template data may have changed, get new data
			$provider_data = $auth_provider->get_auth_link_data();
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

		// Replace "error" strings with their real, localised form
		$error = array_map(array($user, 'lang'), $error);
		$error = implode('<br />', $error);

		$template->assign_vars(array(
			'ERROR'	=> $error,

			'PROVIDER_TEMPLATE_FILE'	=> $provider_data['TEMPLATE_FILE'],

			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> $this->u_action,
		));

		$this->tpl_name = 'ucp_auth_link';
		$this->page_title = 'UCP_AUTH_LINK';
	}
}
