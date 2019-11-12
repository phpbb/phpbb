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

namespace phpbb\acp\controller;

class help_phpbb
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var string phpBB admin path */
	protected $admin_path;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var string phpBB collection url */
	protected $collect_url = "https://www.phpbb.com/statistics/send";

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config			$config			Config object
	 * @param \phpbb\event\dispatcher		$dispatcher		Event dispatcher object
	 * @param \phpbb\acp\helper\controller	$helper			ACP Controller helper object
	 * @param \phpbb\language\language		$language		Language object
	 * @param \phpbb\request\request		$request		Request object
	 * @param \phpbb\template\template		$template		Template object
	 * @param string						$admin_path		phpBB admin path
	 * @param string						$root_path		phpBB root path
	 * @param string						$php_ext		php File extension
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		$admin_path,
		$root_path,
		$php_ext
	)
	{
		$this->config		= $config;
		$this->dispatcher	= $dispatcher;
		$this->helper		= $helper;
		$this->language		= $language;
		$this->request		= $request;
		$this->template		= $template;

		$this->admin_path	= $admin_path;
		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
	}

	public function main()
	{
		if (!class_exists('phpbb_questionnaire_data_collector'))
		{
			include($this->root_path . 'includes/questionnaire/questionnaire.' . $this->php_ext);
		}

		$submit = $this->request->is_set_post('submit');
		$errors = [];

		$form_key = 'acp_help_phpbb';
		add_form_key($form_key);

		if ($submit && !check_form_key($form_key))
		{
			$errors[] = $this->language->lang('FORM_INVALID');
		}

		// Do not write values if there is an error
		if (!empty($errors))
		{
			$submit = false;
		}

		// Generate a unique id if necessary
		if (!isset($this->config['questionnaire_unique_id']))
		{
			$install_id = unique_id();
			$this->config->set('questionnaire_unique_id', $install_id);
		}
		else
		{
			$install_id = $this->config['questionnaire_unique_id'];
		}

		$collector = new \phpbb_questionnaire_data_collector($install_id);

		// Add data provider
		$collector->add_data_provider(new \phpbb_questionnaire_php_data_provider());
		$collector->add_data_provider(new \phpbb_questionnaire_system_data_provider());
		$collector->add_data_provider(new \phpbb_questionnaire_phpbb_data_provider($this->config));

		/**
		 * Event to modify ACP help phpBB page and/or listen to submit
		 *
		 * @event core.acp_help_phpbb_submit_before
		 * @var bool	submit		Do we display the form or process the submission
		 * @since 3.2.0-RC2
		 */
		$vars = ['submit'];
		extract($this->dispatcher->trigger_event('core.acp_help_phpbb_submit_before', compact($vars)));

		if ($submit)
		{
			$this->config->set('help_send_statistics', $this->request->variable('help_send_statistics', false));
			$response = $this->request->variable('send_statistics_response', '');

			$this->config->set('help_send_statistics_time', time());

			if (!empty($response))
			{
				$decoded_response = json_decode(htmlspecialchars_decode($response), true);

				if ($decoded_response && isset($decoded_response['status']) && $decoded_response['status'] == 'ok')
				{
					return $this->helper->message_back('THANKS_SEND_STATISTICS', 'acp_help_phpbb');
				}
				else
				{
					return trigger_error($this->language->lang('FAIL_SEND_STATISTICS') . $this->helper->adm_back_route('acp_help_phpbb'), E_USER_WARNING);
				}
			}

			return $this->helper->message_back('CONFIG_UPDATED', 'acp_help_phpbb');
		}

		$this->template->assign_vars([
			// Pass earliest time we should try to send stats again
			'COLLECT_STATS_TIME'	=> intval($this->config['help_send_statistics_time']) + 86400,

			'S_STATS'				=> $collector->get_data_raw(),
			'S_STATS_DATA'			=> json_encode($collector->get_data_raw()),
			'S_COLLECT_STATS'		=> !empty($this->config['help_send_statistics']),
			'U_COLLECT_STATS'		=> $this->collect_url,
			'U_ACP_MAIN'			=> $this->helper->route('acp_index'),
			'U_ACTION'				=> $this->helper->route('acp_help_phpbb'),
		]);

		$raw = $collector->get_data_raw();

		foreach ($raw as $provider => $data)
		{
			if ($provider === 'install_id')
			{
				$data = [$provider => $data];
			}

			$this->template->assign_block_vars('providers', ['NAME' => htmlspecialchars($provider)]);

			foreach ($data as $key => $value)
			{
				if (is_array($value))
				{
					$value = utf8_wordwrap(serialize($value), 75, "\n", true);
				}

				$this->template->assign_block_vars('providers.values', [
					'KEY'	=> utf8_htmlspecialchars($key),
					'VALUE'	=> utf8_htmlspecialchars($value),
				]);
			}
		}

		return $this->helper->render('acp_help_phpbb.html', $this->language->lang('ACP_HELP_PHPBB'));
	}
}
