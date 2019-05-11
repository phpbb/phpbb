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

namespace phpbb\mcp\controller;

class auth_link
{
	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\auth\provider_collection */
	protected $provider_collection;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\language\language			$lang					Language object
	 * @param \phpbb\auth\provider_collection	$provider_collection	Auth provider collection
	 * @param \phpbb\request\request			$request				Request object
	 * @param \phpbb\template\template			$template				Template object
	 * @param \phpbb\user						$user					User object
	 */
	public function __construct(
		\phpbb\language\language $lang,
		\phpbb\auth\provider_collection $provider_collection,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user
	)
	{
		$this->lang					= $lang;
		$this->provider_collection	= $provider_collection;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;
	}

	/**
	* Generates the ucp_auth_link page and handles the auth link process
	*
	* @param	int		$id
	* @param	string	$mode
	*/
	public function main($id, $mode)
	{
		$errors = [];

		$auth_provider = $this->provider_collection->get_provider();
		$provider_data = $auth_provider->get_auth_link_data();

		// confirm that the auth provider supports this page
		if ($provider_data === null)
		{
			$errors[] = $this->lang->lang('UCP_AUTH_LINK_NOT_SUPPORTED');
		}

		$s_hidden_fields = [];
		add_form_key('ucp_auth_link');

		$submit	= $this->request->variable('submit', false, false, \phpbb\request\request_interface::POST);

		// This path is only for primary actions
		if (empty($errors) && $submit)
		{
			if (!check_form_key('ucp_auth_link'))
			{
				$errors[] = $this->lang->lang('FORM_INVALID');
			}

			if (empty($errors))
			{
				// Any post data could be necessary for auth (un)linking
				$link_data = $this->request->get_super_global(\phpbb\request\request_interface::POST);

				// The current user_id is also necessary
				$link_data['user_id'] = $this->user->data['user_id'];

				// Tell the provider that the method is auth_link not login_link
				$link_data['link_method'] = 'auth_link';

				if ($this->request->variable('link', 0, false, \phpbb\request\request_interface::POST))
				{
					$error = $auth_provider->link_account($link_data);

					if ($error)
					{
						$errors[] = $this->lang->lang($error);
					}
				}
				else
				{
					$error = $auth_provider->unlink_account($link_data);

					if ($error)
					{
						$errors[] = $this->lang->lang($error);
					}
				}

				// Template data may have changed, get new data
				$provider_data = $auth_provider->get_auth_link_data();
			}
		}

		// In some cases, a request to an external server may be required. In
		// these cases, the GET parameter 'link' should exist and should be true
		if ($this->request->variable('link', false))
		{
			// In this case the link data should only be populated with the
			// link_method as the provider dictates how data is returned to it.
			$link_data = ['link_method' => 'auth_link'];

			$error = $auth_provider->link_account($link_data);

			if ($error)
			{
				$errors[] = $this->lang->lang($error);
			}

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

			$this->template->assign_vars($provider_data['VARS']);
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

				$this->template->assign_block_vars($provider_data['BLOCK_VAR_NAME'], $block_vars);
			}
		}

		$s_hidden_fields = build_hidden_fields($s_hidden_fields);

		$this->template->assign_vars([
			'ERROR'						=> implode('<br />', $errors),

			'PROVIDER_TEMPLATE_FILE'	=> $provider_data['TEMPLATE_FILE'],

			'S_HIDDEN_FIELDS'			=> $s_hidden_fields,
			'S_UCP_ACTION'				=> $this->u_action,
		]);

		$this->tpl_name = 'ucp_auth_link';
		$this->page_title = 'UCP_AUTH_LINK';
	}
}
