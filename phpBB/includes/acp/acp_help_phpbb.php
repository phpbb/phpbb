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

class acp_help_phpbb
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $request, $template, $user, $phpbb_dispatcher, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		if (!class_exists('phpbb_questionnaire_data_collector'))
		{
			include($phpbb_root_path . 'includes/questionnaire/questionnaire.' . $phpEx);
		}

		$collect_url = "https://www.phpbb.com/statistics/send";

		$this->tpl_name = 'acp_help_phpbb';
		$this->page_title = 'ACP_HELP_PHPBB';

		$submit = ($request->is_set_post('submit')) ? true : false;

		$form_key = 'acp_help_phpbb';
		add_form_key($form_key);
		$error = array();

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}
		// Do not write values if there is an error
		if (count($error))
		{
			$submit = false;
		}

		// generate a unique id if necessary
		if (!isset($config['questionnaire_unique_id']))
		{
			$install_id = unique_id();
			$config->set('questionnaire_unique_id', $install_id);
		}
		else
		{
			$install_id = $config['questionnaire_unique_id'];
		}

		$collector = new phpbb_questionnaire_data_collector($install_id);

		// Add data provider
		$collector->add_data_provider(new phpbb_questionnaire_php_data_provider());
		$collector->add_data_provider(new phpbb_questionnaire_system_data_provider());
		$collector->add_data_provider(new phpbb_questionnaire_phpbb_data_provider($config));

		/**
		 * Event to modify ACP help phpBB page and/or listen to submit
		 *
		 * @event core.acp_help_phpbb_submit_before
		 * @var	boolean	submit			Do we display the form or process the submission
		 * @since 3.2.0-RC2
		 */
		$vars = array('submit');
		extract($phpbb_dispatcher->trigger_event('core.acp_help_phpbb_submit_before', compact($vars)));

		if ($submit)
		{
			$config->set('help_send_statistics', $request->variable('help_send_statistics', false));
			$response = $request->variable('send_statistics_response', '');

			$config->set('help_send_statistics_time', time());

			if (!empty($response))
			{
				$decoded_response = json_decode(html_entity_decode($response, ENT_COMPAT), true);

				if ($decoded_response && isset($decoded_response['status']) && $decoded_response['status'] == 'ok')
				{
					trigger_error($user->lang('THANKS_SEND_STATISTICS') . adm_back_link($this->u_action));
				}
				else
				{
					trigger_error($user->lang('FAIL_SEND_STATISTICS') . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}

			trigger_error($user->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'U_COLLECT_STATS'		=> $collect_url,
			'S_COLLECT_STATS'		=> (!empty($config['help_send_statistics'])) ? true : false,
			'S_STATS'				=> $collector->get_data_raw(),
			'S_STATS_DATA'			=> json_encode($collector->get_data_raw()),
			'U_ACP_MAIN'			=> append_sid("{$phpbb_admin_path}index.$phpEx"),
			'U_ACTION'				=> $this->u_action,
			// Pass earliest time we should try to send stats again
			'COLLECT_STATS_TIME'	=> intval($config['help_send_statistics_time']) + 86400,
		));

		$raw = $collector->get_data_raw();

		foreach ($raw as $provider => $data)
		{
			if ($provider == 'install_id')
			{
				$data = array($provider => $data);
			}

			$template->assign_block_vars('providers', array(
				'NAME'	=> htmlspecialchars($provider, ENT_COMPAT),
			));

			foreach ($data as $key => $value)
			{
				if (is_array($value))
				{
					$value = utf8_wordwrap(serialize($value), 75, "\n", true);
				}

				$template->assign_block_vars('providers.values', array(
					'KEY'	=> utf8_htmlspecialchars($key),
					'VALUE'	=> utf8_htmlspecialchars($value),
				));
			}
		}
	}
}
