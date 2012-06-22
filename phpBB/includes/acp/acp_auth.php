<?php
/**
*
* @package acp
* @copyright (c) 2012 phpBB Group
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
* @package acp
*/
class acp_auth {
	var $u_action;
	var $new_config;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $request;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('acp/auth');

		$submit = ($request->is_set_post('submit') || $request->is_set_post('allow_quick_reply_enable')) ? true : false;

		$form_key = 'acp_auth';
		add_form_key($form_key);

		switch($mode)
		{
			case 'index':
			$this->page_title = 'ACP_AUTH';
			$this->tpl_name = 'acp_auth';
			$display_vars = array(
					'title'	=> 'ACP_AUTH_SETTINGS',
					'vars'	=> array(
					)
				);
			break;
		}

		$this->new_config = $config;
		$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
		$error = array();

		// We validate the complete config if wished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}

		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}

		$auth_manager = new phpbb_auth_manager($request, $db, $config, $user);
		$providers = $auth_manager->get_registered_providers(); // TODO: Make options non-static

		foreach($providers as $provider)
		{
			$provider_configuration = $provider->get_configuration();

			$provider_name = explode('_', $provider_configuration['NAME']);
			foreach($provider_name as &$name_part)
			{
				$name_part = ucfirst($name_part);
			}
			$provider_name = implode(' ', $provider_name);

			$template->assign_block_vars('providers_loop', array(
				'PROVIDER'	=> $provider_name,
			));

			foreach ($provider_configuration['OPTIONS'] as $config_key => $vars)
			{
				$config_key = $provider_configuration['NAME'] . '_' . $config_key;

				$this->new_config[$config_key] = $config_value = $cfg_array[$config_key];

				if ($submit)
				{
					set_config($config_key, $config_value);
				}

				$type = explode(':', $vars['type']);

				$l_explain = '';
				if ($vars['explain'] && isset($vars['lang_explain']))
				{
					$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
				}
				else if ($vars['explain'])
				{
					$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
				}

				$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

				if (empty($content))
				{
					continue;
				}

				$template->assign_block_vars('providers_loop.options', array(
					'KEY'			=> $config_key,
					'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
					'S_EXPLAIN'		=> $vars['explain'],
					'TITLE_EXPLAIN'	=> $l_explain,
					'CONTENT'		=> $content,
					)
				);
			}
		}

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action,
		));
	}
}
