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

namespace phpbb\ucp\controller;

class auth_link
{
	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\auth\provider_collection */
	protected $provider_collection;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\controller\helper			$helper					Controller helper object
	 * @param \phpbb\language\language			$language				Language object
	 * @param \phpbb\auth\provider_collection	$provider_collection	Auth provider collection
	 * @param \phpbb\request\request			$request				Request object
	 * @param \phpbb\template\template			$template				Template object
	 * @param \phpbb\user						$user					User object
	 */
	public function __construct(
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\auth\provider_collection $provider_collection,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user
	)
	{
		$this->helper				= $helper;
		$this->language				= $language;
		$this->provider_collection	= $provider_collection;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;
	}

	/**
	 * Generates the ucp_auth_link page and handles the auth link process
	 */
	public function main()
	{
		$errors = [];
		$s_hidden_fields = [];

		// confirm that the auth provider supports this page
		$auth_provider = $this->provider_collection->get_provider();
		$provider_data = $auth_provider->get_auth_link_data();

		if ($provider_data === null)
		{
			$errors[] = $this->language->lang('UCP_AUTH_LINK_NOT_SUPPORTED');
		}

		$form_key = 'ucp_auth_link';
		add_form_key($form_key);

		$submit	= $this->request->is_set_post('submit');

		// This path is only for primary actions
		if ($submit && empty($errors))
		{
			if (!check_form_key($form_key))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			if (empty($errors))
			{
				// Any post data could be necessary for auth (un)linking
				$link_data = $this->request->get_super_global(\phpbb\request\request_interface::POST);

				// The current user_id is also necessary
				$link_data['user_id'] = (int) $this->user->data['user_id'];

				// Tell the provider that the method is auth_link not login_link
				$link_data['link_method'] = 'auth_link';

				if ($this->request->variable('link', 0, false, \phpbb\request\request_interface::POST))
				{
					$error = $auth_provider->link_account($link_data);

					if ($error)
					{
						$errors[] = $this->language->lang($error);
					}
				}
				else
				{
					$error = $auth_provider->unlink_account($link_data);

					if ($error)
					{
						$errors[] = $this->language->lang($error);
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
				$errors[] = $this->language->lang($error);
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

		$this->template->assign_vars([
			'ERROR'						=> implode('<br />', $errors),
			'PROVIDER_TEMPLATE_FILE'	=> $provider_data['TEMPLATE_FILE'],

			'S_HIDDEN_FIELDS'			=> build_hidden_fields($s_hidden_fields),
			'S_UCP_ACTION'				=> $this->helper->route('ucp_manage_oauth'),
		]);

		return $this->helper->render('ucp_auth_link.html', $this->language->lang('UCP_MANAGE_OAUTH'));
	}
}
