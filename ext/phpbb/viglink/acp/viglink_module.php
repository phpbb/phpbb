<?php
/**
 *
 * VigLink extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\viglink\acp;

use phpbb\request\type_cast_helper;

/**
 * VigLink ACP module
 */
class viglink_module
{
	/** @var string $page_title The page title */
	public $page_title;

	/** @var string $tpl_name The page template name */
	public $tpl_name;

	/** @var string $u_action Custom form action */
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \phpbb\config\config $config Config object */
		$config = $phpbb_container->get('config');

		/** @var \phpbb\language\language $language Language object */
		$language = $phpbb_container->get('language');

		/** @var \phpbb\request\request $request Request object */
		$request  = $phpbb_container->get('request');

		/** @var \phpbb\template\template $template Template object */
		$template = $phpbb_container->get('template');

		$language->add_lang('viglink_module_acp', 'phpbb/viglink');

		$this->tpl_name = 'acp_viglink';
		$this->page_title = $language->lang('ACP_VIGLINK_SETTINGS');

		$submit = $request->is_set_post('submit');

		if ($mode !== 'settings')
		{
			return;
		}

		$form_key = 'acp_viglink';
		add_form_key($form_key);

		$error = array();

		// Get stored config/default values
		$cfg_array = array(
			'viglink_enabled' => isset($config['viglink_enabled']) ? $config['viglink_enabled'] : 0,
		);

		// Error if the form is invalid
		if ($submit && !check_form_key($form_key))
		{
			$error[] = $language->lang('FORM_INVALID');
		}

		// Do not process form if invalid
		if (count($error))
		{
			$submit = false;
		}

		if ($submit)
		{
			// Get the VigLink form field values
			$cfg_array['viglink_enabled'] = $request->variable('viglink_enabled', 0);

			// If no errors, set the config values
			if (!count($error))
			{
				foreach ($cfg_array as $cfg => $value)
				{
					$config->set($cfg, $value);
				}

				trigger_error($language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
			}
		}

		if (!isset($config['questionnaire_unique_id']))
		{
			$config->set('questionnaire_unique_id', unique_id());
		}

		// Set a general error message if VigLink has been disabled by phpBB
		if (!$config['allow_viglink_phpbb'])
		{
			$error[] = $language->lang('ACP_VIGLINK_DISABLED_PHPBB');
		}

		// Try to get convert account key from .com
		$sub_id = md5($config['viglink_api_siteid'] . $config['questionnaire_unique_id']);
		$convert_account_link = $config->offsetGet('viglink_convert_account_url');

		if (empty($convert_account_link) || strpos($config['viglink_convert_account_url'], 'subId=' . $sub_id) === false)
		{
			$convert_account_link = @file_get_contents('https://www.phpbb.com/viglink/convert?domain=' . urlencode($config['server_name']) . '&siteid=' . $config['viglink_api_siteid'] . '&uuid=' . $config['questionnaire_unique_id'] . '&key=' . $config['phpbb_viglink_api_key']);
			if (!empty($convert_account_link) && strpos($convert_account_link, 'https://www.viglink.com/users/convertAccount') === 0)
			{
				$type_caster = new type_cast_helper();
				$type_caster->set_var($convert_account_link, $convert_account_link, 'string', false, false);
				$config->set('viglink_convert_account_url', $convert_account_link);
			}
			else
			{
				$error[] = $language->lang('ACP_VIGLINK_NO_CONVERT_LINK');
				$convert_account_link = '';
			}
		}

		$template->assign_vars(array(
			'S_ERROR'				=> (bool) count($error),
			'ERROR_MSG'				=> implode('<br />', $error),

			'VIGLINK_ENABLED'		=> $cfg_array['viglink_enabled'],

			'U_VIGLINK_CONVERT'		=> $convert_account_link,
			'U_ACTION'				=> $this->u_action,
		));
	}
}
