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
		global $config, $request, $template, $phpbb_container;

		$auth_provider = $phpbb_container->get('auth.provider.' . $config['auth_method']);

		// confirm that the auth provider supports this page
		$provider_data = $auth_provider->get_auth_link_data();
		if ($provider_data === null)
		{
			// does not support this page, throw error?
			throw new Exception('TEMPORARY EXCEPTION');
		}

		$error = array();
		$s_hidden_fields = array();
		add_form_key('ucp_auth_link');

		$submit	= $request->variable('submit', false, false, phpbb_request_interface::POST);

		if ($submit)
		{
			if (!check_form_key('ucp_reg_details'))
			{
				$error[] = 'FORM_INVALID';
			}

			if (!sizeof($error))
			{

			}
		}

		$s_hidden_fields = build_hidden_fields($s_hidden_fields);

		$template->assign_vars(array(
			'PROVIDER_TEMPLATE_FILE'	=> $provider_data['TEMPLATE_FILE'],

			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'S_UCP_ACTION'		=> $this->u_action,
		));

		$this->tpl_name = 'ucp_auth_link';
		$this->page_title = 'UCP_AUTH_LINK';
	}
}
