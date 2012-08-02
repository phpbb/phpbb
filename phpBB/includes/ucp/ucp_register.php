<?php
/**
*
* @package ucp
* @copyright (c) 2005 phpBB Group
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
* ucp_register
* Board registration
* @package ucp
*/
class ucp_register
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx;
		global $request, $phpbb_auth_manager;

		//
		if ($config['require_activation'] == USER_ACTIVATION_DISABLE)
		{
			trigger_error('UCP_REGISTER_DISABLE');
		}

		include($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);

		$coppa			= $request->is_set('coppa') ? (int) $request->variable('coppa', false) : false;
		$agreed			= (int) $request->variable('agreed', false);
		$submit			= $request->is_set_post('submit');
		$change_lang	= request_var('change_lang', '');
		$user_lang		= request_var('lang', $user->lang_name);
		$auth_provider = $request->variable('auth_provider', '');
		$auth_action = $request->variable('auth_action', '');

		if (!$submit && $auth_action && $auth_provider != 'native')
		{
			$submit = true;
		}
		$phpbb_auth_manager->set_user($user);

		if ($agreed)
		{
			add_form_key('ucp_register');
		}
		else
		{
			add_form_key('ucp_register_terms');
		}

		if ($change_lang || $user_lang != $config['default_lang'])
		{
			$use_lang = ($change_lang) ? basename($change_lang) : basename($user_lang);

			if (!validate_language_iso_name($use_lang))
			{
				if ($change_lang)
				{
					$submit = false;

					// Setting back agreed to let the user view the agreement in his/her language
					$agreed = ($request->variable('change_lang', false)) ? 0 : $agreed;
				}

				$user->lang_name = $user_lang = $use_lang;
				$user->lang = array();
				$user->data['user_lang'] = $user->lang_name;
				$user->add_lang(array('common', 'ucp'));
			}
			else
			{
				$change_lang = '';
				$user_lang = $user->lang_name;
			}
		}

		if (!$agreed || ($coppa === false && $config['coppa_enable']) || ($coppa && !$config['coppa_enable']))
		{
			$add_lang = ($change_lang) ? '&amp;change_lang=' . urlencode($change_lang) : '';
			$add_coppa = ($coppa !== false) ? '&amp;coppa=' . $coppa : '';

			$s_hidden_fields = array(
				'change_lang'	=> $change_lang,
			);

			// If we change the language, we want to pass on some more possible parameter.
			if ($change_lang && $auth_provider = 'native')
			{
				// We do not include the password
				$s_hidden_fields = array_merge($s_hidden_fields, array(
					'username'			=> utf8_normalize_nfc(request_var('username', '', true)),
					'email'				=> strtolower(request_var('email', '')),
					'lang'				=> $user->lang_name,
					'tz'				=> request_var('tz', $config['board_timezone']),
				));

			}

			// Checking amount of available languages
			$sql = 'SELECT lang_id
				FROM ' . LANG_TABLE;
			$result = $db->sql_query($sql);

			$lang_row = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$lang_row[] = $row;
			}
			$db->sql_freeresult($result);

			if ($coppa === false && $config['coppa_enable'])
			{
				$now = getdate();
				$coppa_birthday = $user->create_datetime()
					->setDate($now['year'] - 13, $now['mon'], $now['mday'] - 1)
					->setTime(0, 0, 0)
					->format($user->lang['DATE_FORMAT'], true);
				unset($now);

				$template->assign_vars(array(
					'S_LANG_OPTIONS'	=> (sizeof($lang_row) > 1) ? language_select($user_lang) : '',
					'L_COPPA_NO'		=> sprintf($user->lang['UCP_COPPA_BEFORE'], $coppa_birthday),
					'L_COPPA_YES'		=> sprintf($user->lang['UCP_COPPA_ON_AFTER'], $coppa_birthday),

					'U_COPPA_NO'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register&amp;coppa=0' . $add_lang),
					'U_COPPA_YES'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register&amp;coppa=1' . $add_lang),

					'S_SHOW_COPPA'		=> true,
					'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
					'S_UCP_ACTION'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register' . $add_lang),
				));
			}
			else
			{
				$template->assign_vars(array(
					'S_LANG_OPTIONS'	=> (sizeof($lang_row) > 1) ? language_select($user_lang) : '',
					'L_TERMS_OF_USE'	=> sprintf($user->lang['TERMS_OF_USE_CONTENT'], $config['sitename'], generate_board_url()),

					'S_SHOW_COPPA'		=> false,
					'S_REGISTRATION'	=> true,
					'S_HIDDEN_FIELDS'	=> build_hidden_fields($s_hidden_fields),
					'S_UCP_ACTION'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register' . $add_lang . $add_coppa),
					)
				);
			}
			unset($lang_row);

			$this->tpl_name = 'ucp_agreement';
			return;
		}

		$error = array();

		if ($auth_action & $auth_provider)
		{
			$auth_step = $request->variable('auth_step', 'process');
			$provider = $phpbb_auth_manager->get_provider($auth_provider);
			if (!method_exists($provider, $auth_step))
			{
				$error['NO_AUTH_STEP'] = 'NO_AUTH_STEP';
			}

			try
			{
				$return = $provider->$auth_step();
				$coppa	= $request->is_set('coppa') ? (int) $request->variable('coppa', false) : false;
			}
			catch (phpbb_auth_exception $e)
			{
				$return = null;
				$error[] = $e->getMessage();
			}

			if ($return instanceof phpbb_auth_data_request)
			{
				if (isset($return->USERNAME))
				{
					$error[] = $return->USERNAME_ERROR;
					$template->assign_vars(array(
						'USERNAME'				=> true,
						'L_USERNAME_EXPLAIN'	=> $user->lang($config['allow_name_chars'] . '_EXPLAIN', $user->lang('CHARACTERS', (int) $config['min_name_chars']), $user->lang('CHARACTERS', (int) $config['max_name_chars'])),
					));
				}

				if (isset($return->EMAIL))
				{
					$error[] = $return->EMAIL_ERROR;
					$template->assign_vars(array(
						'EMAIL'	=> true,
					));
				}

				$post_vars = $request->variable_names(phpbb_request_interface::POST);
				$s_hidden_fields = array();
				foreach ($post_vars as $var)
				{
					if ($var == 'auth_step')
					{
						$s_hidden_fields[$var] = $request->variable('auth_action', '') . '_req_data';
					}
					else
					{
						$s_hidden_fields[$var] = $request->variable($var, '');
					}
				}
				$s_hidden_fields = build_hidden_fields($s_hidden_fields);

				$s_ucp_action = append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=register');
				$get_vars = $request->variable_names(phpbb_request_interface::GET);
				$query_vars = array();
				foreach ($get_vars as $var)
				{
					if ($var == 'mode')
					{
						continue;
					}
					else if ($var == 'auth_step')
					{
						$query_vars[$var] = $request->variable('auth_action', '') . '_req_data';
					}
					else
					{
						$query_vars[$var] = $request->variable($var, '');
					}
				}
				$s_ucp_action .= '&' . http_build_query($query_vars);

				$template->assign_vars(array(
					'ERROR'				=> implode('<br />', $error),
					'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
					'S_UCP_ACTION'		=> $s_ucp_action,
				));

				$this->tpl_name = 'ucp_register_request_data';
				$this->page_title = 'UCP_REGISTRATION';
				return;
			}

			if (!sizeof($error))
			{
				if ($coppa && $config['email_enable'])
				{
					$message = $user->lang['ACCOUNT_COPPA'];
				}
				else if ($config['require_activation'] == USER_ACTIVATION_SELF && $config['email_enable'])
				{
					$message = $user->lang['ACCOUNT_INACTIVE'];
				}
				else if ($config['require_activation'] == USER_ACTIVATION_ADMIN && $config['email_enable'])
				{
					$message = $user->lang['ACCOUNT_INACTIVE_ADMIN'];
				}
				else
				{
					$message = $user->lang['ACCOUNT_ADDED'];
				}
				$message = $message . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
				trigger_error($message);
			}
		}

		$providers = $phpbb_auth_manager->get_enabled_providers();
		$rendered_template = false;
		foreach ($providers as $provider)
		{
			if (!($provider instanceof phpbb_auth_interface_provider_registration))
			{
				continue;
			}

			$tpl = $provider->generate_registration($template);

			if ($tpl)
			{
				$template->assign_block_vars('providers_loop', array(
					'TPL'	=> $tpl,
				));
				$rendered_template = true;
			}
		}

		if (!$rendered_template)
		{
			trigger_error('NO_PROVIDERS');
		}

		$template->assign_vars(array(
			'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',
		));

		//
		$this->tpl_name = 'ucp_register';
		$this->page_title = 'UCP_REGISTRATION';
	}
}
