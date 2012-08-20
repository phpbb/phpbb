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
class acp_auth
{
	var $u_action;
	var $new_config;

	function main($id, $mode)
	{
		global $user, $template, $request, $phpbb_auth_manager, $config, $cache;

		$user->add_lang('acp/auth');

		$submit = ($request->is_set_post('submit') || $request->is_set_post('allow_quick_reply_enable')) ? true : false;

		if ($submit)
		{
			$cache->destroy('auth_providers_enabled');
		}
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
		$cfg_array = ($request->is_set('config')) ? utf8_normalize_nfc($request->variable('config', array('' => ''), true)) : $this->new_config;
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

		// Some providers may need access to phpbb_user during init.
		$phpbb_auth_manager->set_user($user);
		$providers = $phpbb_auth_manager->get_registered_providers();

		foreach($providers as $provider)
		{
			$provider_configuration = $provider->get_configuration();

			$provider_name = $user->lang[strtoupper($provider->name)];

			$err = false;

			validate_config_vars($provider_configuration['OPTIONS'], $cfg_array, $error);
			if (sizeof($error))
			{
				$err = true;
			}

			$custom_tpl = false;
			if ($provider instanceof phpbb_auth_interface_provider_custom_acp_auth)
			{
				$custom_tpl = $provider->generate_acp_options($template, $this->new_config, $submit, $err);
			}

			$template->assign_block_vars('providers_loop', array(
				'CUSTOM_TPL'=> $custom_tpl,
				'PROVIDER'	=> $provider_name,
			));

			$updated_auth_settings = false;
			$old_auth_config = array();
			$init = false;
			foreach ($provider_configuration['OPTIONS'] as $config_key_orig => $vars)
			{
				$config_key = 'auth_provider_' . $provider->name . '_' . $config_key_orig;
				if ($config_key_orig === 'enabled' && $cfg_array[$config_key] == true && $provider instanceof phpbb_auth_interface_provider_acp_init)
				{
					$init = true;
				}

				if (!isset($config[$config_key]))
				{
					set_config($config_key, '');
				}

				if ($submit && !$err)
				{
					$updated_auth_settings = true;
					$old_auth_config[$config_key] = $config[$config_key];
					$this->new_config[$config_key] = $config_value = $cfg_array[$config_key];
					set_config($config_key, $config_value);
				}

				if (!$custom_tpl)
				{
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

			if ($init)
			{
				try
				{
					$provider->init();
				}
				catch (Exception $e)
				{
					$err = true;
					$error[] = $e->getMessage();
					if ($updated_auth_settings)
					{
						foreach ($old_auth_config as $config_key => $config_value)
						{
							set_config($config_key, $config_value);
						}
					}
				}
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
