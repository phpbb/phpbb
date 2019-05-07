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

namespace phpbb\acp;

use phpbb\exception\exception_interface;
use phpbb\exception\version_check_exception;

class extensions
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\composer\extension_manager */
	protected $composer_manager;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\extension\manager */
	protected $extension_manager;

	protected $lang;
	protected $log;
	protected $pagination;
	/** @var \bantu\IniGetWrapper\IniGetWrapper */
	protected $php_ini;
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;
	protected $user;

	/** @var string extensions.composer.output */
	protected $composer_output;

	protected $admin_path;

	/** @var string */
	protected $root_path;

	protected $php_ext;

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;
	protected $u_catalog_action;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\composer\extension_manager $composer_manager,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\extension\manager $extension_manager,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\pagination $pagination,
		\bantu\IniGetWrapper\IniGetWrapper $php_ini,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$composer_output,
		$admin_path,
		$root_path,
		$php_ext
	)
	{
		$this->config				= $config;
		$this->composer_manager		= $composer_manager;
		$this->dispatcher			= $dispatcher;
		$this->extension_manager	= $extension_manager;
		$this->lang					= $lang;
		$this->log					= $log;
		$this->pagination			= $pagination;
		$this->php_ini				= $php_ini;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->composer_output		= $composer_output;
		$this->admin_path			= $admin_path;
		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
	}

	function main($id, $mode)
	{
		$this->lang->add_lang(['install', 'acp/extensions', 'migrator']);

		switch ($mode)
		{
			case 'catalog':
				$this->catalog_mode($id, $mode);
			break;

			default:
				$this->main_mode($id, $mode);
			break;
		}
	}

	public function main_mode($id, $mode)
	{
		$this->page_title = 'ACP_EXTENSIONS';

		$action = $this->request->variable('action', 'list');
		$ext_name = $this->request->variable('ext_name', '');

		// What is a safe limit of execution time? Half the max execution time should be safe.
		$safe_time_limit = ($this->php_ini->getNumeric('max_execution_time') / 2);
		$start_time = time();

		// Cancel action
		if ($this->request->is_set_post('cancel'))
		{
			$action = 'list';
			$ext_name = '';
		}

		if (in_array($action, ['enable', 'disable', 'delete_data']) && !check_link_hash($this->request->variable('hash', ''), $action . '.' . $ext_name))
		{
			trigger_error($this->lang->lang('FORM_INVALID'), E_USER_WARNING);
		}

		/**
		 * Event to run a specific action on extension
		 *
		 * @event core.acp_extensions_run_action_before
		 * @var	string	action			Action to run; if the event completes execution of the action, should be set to 'none'
		 * @var	string	u_action		Url we are at
		 * @var	string	ext_name		Extension name from request
		 * @var	int		safe_time_limit	Safe limit of execution time
		 * @var	int		start_time		Start time
		 * @var	string	tpl_name		Template file to load
		 * @since 3.1.11-RC1
		 * @changed 3.2.1-RC1			Renamed to core.acp_extensions_run_action_before, added tpl_name, added action 'none'
		 */
		$u_action = $this->u_action;
		$tpl_name = '';
		$vars = ['action', 'u_action', 'ext_name', 'safe_time_limit', 'start_time', 'tpl_name'];
		extract($this->dispatcher->trigger_event('core.acp_extensions_run_action_before', compact($vars)));

		// In case they have been updated by the event
		$this->u_action = $u_action;
		$this->tpl_name = $tpl_name;

		$md_manager = null;

		// If they've specified an extension, let's load the metadata manager and validate it.
		if ($ext_name)
		{
			$md_manager = $this->extension_manager->create_extension_metadata_manager($ext_name);

			try
			{
				$md_manager->get_metadata('all');
			}
			catch (exception_interface $e)
			{
				$message = call_user_func_array([$this->lang, 'lang'], array_merge([$e->getMessage()], $e->get_parameters()));
				trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		$this->u_catalog_action = append_sid("{$this->admin_path}index.$this->php_ext", "i=$id&amp;mode=catalog");

		// What are we doing?
		switch ($action)
		{
			case 'none':
				// Intentionally empty, used by extensions that execute additional actions in the prior event
			break;

			case 'set_config_version_check_force_unstable':
				$force_unstable = $this->request->variable('force_unstable', false);

				if ($force_unstable)
				{
					$s_hidden_fields = build_hidden_fields([
						'force_unstable'	=> $force_unstable,
					]);

					confirm_box(false, $this->lang->lang('EXTENSION_FORCE_UNSTABLE_CONFIRM'), $s_hidden_fields);
				}
				else
				{
					$this->config->set('extension_force_unstable', false);

					trigger_error($this->lang->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
				}
			break;

			case 'list':
			default:
				if (confirm_box(true))
				{
					$this->config->set('extension_force_unstable', true);

					trigger_error($this->lang->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
				}

				$managed_packages = [];
				if ($this->composer_manager->check_requirements())
				{
					$managed_packages = $this->composer_manager->get_managed_packages();
				}

				$this->list_enabled_exts($managed_packages);
				$this->list_disabled_exts($managed_packages);
				$this->list_available_exts($managed_packages);

				$this->template->assign_vars([
					'FORCE_UNSTABLE'		=> $this->config['extension_force_unstable'],
					'MANAGED_EXTENSIONS'	=> $managed_packages,

					'U_ACTION' 				=> $this->u_action,
					'U_CATALOG_ACTION' 		=> $this->u_catalog_action,
					'U_VERSIONCHECK_FORCE' 	=> $this->u_action . '&amp;action=list&amp;versioncheck_force=1',
				]);

				$this->request->disable_super_globals();

				$this->tpl_name = 'acp_ext_list';
			break;

			case 'enable_pre':
				try
				{
					$md_manager->validate_enable();
				}
				catch (exception_interface $e)
				{
					$message = call_user_func_array([$this->lang, 'lang'], array_merge([$e->getMessage()], $e->get_parameters()));
					trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$extension = $this->extension_manager->get_extension($ext_name);
				if (!$extension->is_enableable())
				{
					trigger_error($this->lang->lang('EXTENSION_NOT_ENABLEABLE') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if ($this->extension_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}

				if (confirm_box(true))
				{
					redirect($this->u_action . '&amp;action=enable&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('enable.' . $ext_name));
				}
				else
				{
					confirm_box(false, $this->lang->lang('EXTENSION_ENABLE_CONFIRM', $md_manager->get_metadata('display-name')), build_hidden_fields([
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> 'enable_pre',
						'ext_name'	=> $ext_name,
					]));
				}
			break;

			case 'enable':
				try
				{
					$md_manager->validate_enable();
				}
				catch (exception_interface $e)
				{
					$message = call_user_func_array([$this->lang, 'lang'], array_merge([$e->getMessage()], $e->get_parameters()));
					trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$extension = $this->extension_manager->get_extension($ext_name);
				if (!$extension->is_enableable())
				{
					trigger_error($this->lang->lang('EXTENSION_NOT_ENABLEABLE') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				try
				{
					while ($this->extension_manager->enable_step($ext_name))
					{
						// Are we approaching the time limit? If so we want to pause the update and continue after refreshing
						if ((time() - $start_time) >= $safe_time_limit)
						{
							meta_refresh(0, $this->u_action . '&amp;action=enable&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('enable.' . $ext_name));
							trigger_error($this->lang->lang('EXTENSION_ENABLE_IN_PROGRESS'), E_USER_NOTICE);
						}
					}

					// Update custom style for admin area
					$this->template->set_custom_style([[
						'name' 		=> 'adm',
						'ext_path' 	=> 'adm/style/',
					]], [$this->root_path . 'adm/style']);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_EXT_ENABLE', time(), [$ext_name]);
				}
				catch (\phpbb\db\migration\exception $e)
				{
					trigger_error($this->lang->lang('MIGRATION_EXCEPTION_ERROR', $e->getLocalisedMessage($this->user)), E_USER_WARNING);
				}

				if ($this->request->is_ajax())
				{
					$actions = $this->output_actions('enabled', [
						'DISABLE'	=> $this->u_action . '&amp;action=disable_pre&amp;ext_name=' . urlencode($ext_name),
					]);

					$json_response = new \phpbb\json_response;
					$json_response->send([
						'EXT_ENABLE_SUCCESS'	=> true,
						'ACTIONS'				=> $actions,
					]);
				}

				trigger_error($this->lang->lang('EXTENSION_ENABLE_SUCCESS') . adm_back_link($this->u_action), E_USER_NOTICE);
			break;

			case 'disable_pre':
				if (!$this->extension_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}

				if (confirm_box(true))
				{
					redirect($this->u_action . '&amp;action=disable&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('disable.' . $ext_name));
				}
				else
				{
					confirm_box(false, $this->lang->lang('EXTENSION_DISABLE_CONFIRM', $md_manager->get_metadata('display-name')), build_hidden_fields([
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> 'disable_pre',
						'ext_name'	=> $ext_name,
					]));
				}
			break;

			case 'disable':
				if (!$this->extension_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}

				while ($this->extension_manager->disable_step($ext_name))
				{
					// Are we approaching the time limit? If so we want to pause the update and continue after refreshing
					if ((time() - $start_time) >= $safe_time_limit)
					{
						$this->template->assign_var('S_NEXT_STEP', true);

						meta_refresh(0, $this->u_action . '&amp;action=disable&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('disable.' . $ext_name));

						trigger_error($this->lang->lang('EXTENSION_DISABLE_IN_PROGRESS'), E_USER_NOTICE);
					}
				}
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_EXT_DISABLE', time(), [$ext_name]);

				if ($this->request->is_ajax())
				{
					$actions = $this->output_actions('disabled', [
						'ENABLE'		=> $this->u_action . '&amp;action=enable_pre&amp;ext_name=' . urlencode($ext_name),
						'DELETE_DATA'	=> $this->u_action . '&amp;action=delete_data_pre&amp;ext_name=' . urlencode($ext_name),
					]);

					$json_response = new \phpbb\json_response;
					$json_response->send([
						'EXT_DISABLE_SUCCESS'	=> true,
						'ACTIONS'				=> $actions,
					]);
				}
				trigger_error($this->lang->lang('EXTENSION_DISABLE_SUCCESS') . adm_back_link($this->u_action), E_USER_NOTICE);
			break;

			case 'delete_data_pre':
				if ($this->extension_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}

				if (confirm_box(true))
				{
					redirect($this->u_action . '&amp;action=delete_data&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('delete_data.' . $ext_name));
				}
				else
				{
					confirm_box(false, $this->lang->lang('EXTENSION_DELETE_DATA_CONFIRM', $md_manager->get_metadata('display-name')), build_hidden_fields([
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> 'delete_data_pre',
						'ext_name'	=> $ext_name,
					]));
				}
			break;

			case 'delete_data':
				if ($this->extension_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}

				try
				{
					while ($this->extension_manager->purge_step($ext_name))
					{
						// Are we approaching the time limit? If so we want to pause the update and continue after refreshing
						if ((time() - $start_time) >= $safe_time_limit)
						{
							$this->template->assign_var('S_NEXT_STEP', true);

							meta_refresh(0, $this->u_action . '&amp;action=delete_data&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('delete_data.' . $ext_name));

							trigger_error($this->lang->lang('EXTENSION_DELETE_DATA_IN_PROGRESS'), E_USER_NOTICE);
						}
					}
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_EXT_PURGE', time(), [$ext_name]);
				}
				catch (\phpbb\db\migration\exception $e)
				{
					trigger_error($this->lang->lang('MIGRATION_EXCEPTION_ERROR', $e->getLocalisedMessage($this->user)), E_USER_WARNING);
				}

				if ($this->request->is_ajax())
				{
					$actions = $this->output_actions('disabled', [
						'ENABLE'		=> $this->u_action . '&amp;action=enable_pre&amp;ext_name=' . urlencode($ext_name),
					]);

					$json_response = new \phpbb\json_response;
					$json_response->send([
						'EXT_DELETE_DATA_SUCCESS'	=> true,
						'ACTIONS'					=> $actions,
					]);
				}
				trigger_error($this->lang->lang('EXTENSION_DELETE_DATA_SUCCESS') . adm_back_link($this->u_action), E_USER_NOTICE);
			break;

			case 'details':
				// Output it to the template
				$meta = $md_manager->get_metadata('all');

				$this->output_metadata_to_template($meta);

				if (isset($meta['extra']['version-check']))
				{
					try
					{
						$updates_available = $this->extension_manager->version_check($md_manager, $this->request->variable('versioncheck_force', false), false, $this->config['extension_force_unstable'] ? 'unstable' : null);

						$this->template->assign_vars([
							'S_UP_TO_DATE'		=> empty($updates_available),
							'UP_TO_DATE_MSG'	=> $this->lang->lang(empty($updates_available) ? 'UP_TO_DATE' : 'NOT_UP_TO_DATE', $md_manager->get_metadata('display-name')),
						]);

						$this->template->assign_block_vars('updates_available', $updates_available);
					}
					catch (exception_interface $e)
					{
						$message = call_user_func_array([$this->lang, 'lang'], array_merge([$e->getMessage()], $e->get_parameters()));

						$this->template->assign_vars([
							'S_VERSIONCHECK_FAIL'		=> true,
							'VERSIONCHECK_FAIL_REASON'	=> ($e->getMessage() !== 'VERSIONCHECK_FAIL') ? $message : '',
						]);
					}

					$this->template->assign_var('S_VERSIONCHECK', true);
				}
				else
				{
					$this->template->assign_var('S_VERSIONCHECK', false);
				}

				$this->template->assign_vars([
					'U_BACK'				=> $this->u_action . '&amp;action=list',
					'U_VERSIONCHECK_FORCE'	=> $this->u_action . '&amp;action=details&amp;versioncheck_force=1&amp;ext_name=' . urlencode((string) $md_manager->get_metadata('name')),
				]);

				$this->tpl_name = 'acp_ext_details';
			break;
		}

		$u_action = $this->u_action;
		$tpl_name = $this->tpl_name;

		/**
		 * Event to run after a specific action on extension has completed
		 *
		 * @event core.acp_extensions_run_action_after
		 * @var	string	action			Action that has run
		 * @var	string	u_action		Url we are at
		 * @var	string	ext_name		Extension name from request
		 * @var	int		safe_time_limit	Safe limit of execution time
		 * @var	int		start_time		Start time
		 * @var	string	tpl_name		Template file to load
		 * @since 3.1.11-RC1
		 */
		$vars = ['action', 'u_action', 'ext_name', 'safe_time_limit', 'start_time', 'tpl_name'];
		extract($this->dispatcher->trigger_event('core.acp_extensions_run_action_after', compact($vars)));

		// In case they have been updated by the event
		$this->u_action = $u_action;
		$this->tpl_name = $tpl_name;
	}

	/**
	 * Handles the catalog mode of the extensions list
	 *
	 * @param string $id
	 * @param string $mode
	 */
	public function catalog_mode($id, $mode)
	{
		$action = $this->request->variable('action', 'list');

		if (!$this->composer_manager->check_requirements())
		{
			$this->page_title = 'ACP_EXTENSIONS_CATALOG';
			$this->tpl_name = 'message_body';

			$this->template->assign_vars([
				'MESSAGE_TITLE'	=> $this->lang->lang('EXTENSIONS_CATALOG_NOT_AVAILABLE'),
				'MESSAGE_TEXT'	=> $this->lang->lang('EXTENSIONS_COMPOSER_NOT_WRITABLE'),
			]);

			return;
		}

		switch ($action)
		{
			case 'install':
				$this->page_title = 'ACP_EXTENSIONS_INSTALL';

				$extension = $this->request->variable('extension', '');

				if (empty($extension))
				{
					redirect($this->u_action);
				}

				$formatter = new \phpbb\composer\io\html_output_formatter([
					'warning' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('black', 'yellow')
				]);

				$composer_io = new \phpbb\composer\io\web_io($this->lang, '', $this->composer_output, $formatter);

				try
				{
					$this->composer_manager->install((array) $extension, $composer_io);
				}
				catch (\phpbb\exception\runtime_exception $e)
				{
					$this->display_composer_exception($e, $composer_io);
					return;
				}

				$this->tpl_name = 'detailed_message_body';

				$this->template->assign_vars([
					'MESSAGE_TITLE'			=> $this->lang->lang('ACP_EXTENSIONS_INSTALL'),
					'MESSAGE_TEXT'			=> $this->lang->lang('EXTENSIONS_INSTALLED') . adm_back_link($this->u_action),
					'MESSAGE_DETAIL'		=> $composer_io->getOutput(),
					'MESSAGE_DETAIL_LEGEND'	=> $this->lang->lang('COMPOSER_OUTPUT'),
					'S_USER_NOTICE'			=> true,
				]);
			break;

			case 'remove':
				$this->page_title = 'ACP_EXTENSIONS_REMOVE';

				$extension = $this->request->variable('extension', '');

				if (empty($extension))
				{
					redirect($this->u_action);
				}

				$formatter = new \phpbb\composer\io\html_output_formatter([
					'warning' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('black', 'yellow')
				]);

				$composer_io = new \phpbb\composer\io\web_io($this->lang, '', $this->composer_output, $formatter);

				try
				{
					$this->composer_manager->remove((array) $extension, $composer_io);
				}
				catch (\phpbb\exception\runtime_exception $e)
				{
					$this->display_composer_exception($e, $composer_io);
					return;
				}

				$this->tpl_name = 'detailed_message_body';

				$this->template->assign_vars([
					'MESSAGE_TITLE'			=> $this->lang->lang('ACP_EXTENSIONS_REMOVE'),
					'MESSAGE_TEXT'			=> $this->lang->lang('EXTENSIONS_REMOVED') . adm_back_link($this->u_action),
					'MESSAGE_DETAIL'		=> $composer_io->getOutput(),
					'MESSAGE_DETAIL_LEGEND'	=> $this->lang->lang('COMPOSER_OUTPUT'),
					'S_USER_NOTICE'			=> true,
				]);
			break;

			case 'update':
				$this->page_title = 'ACP_EXTENSIONS_UPDATE';

				$extension = $this->request->variable('extension', '');

				if (empty($extension))
				{
					redirect($this->u_action);
				}

				$formatter = new \phpbb\composer\io\html_output_formatter([
					'warning' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('black', 'yellow')
				]);

				$composer_io = new \phpbb\composer\io\web_io($this->lang, '', $this->composer_output, $formatter);

				try
				{
					$this->composer_manager->update((array) $extension, $composer_io);
				}
				catch (\phpbb\exception\runtime_exception $e)
				{
					$this->display_composer_exception($e, $composer_io);
					return;
				}
				$this->tpl_name = 'detailed_message_body';

				$this->template->assign_vars([
					'MESSAGE_TITLE'			=> $this->lang->lang('ACP_EXTENSIONS_UPDATE'),
					'MESSAGE_TEXT'			=> $this->lang->lang('EXTENSIONS_UPDATED') . adm_back_link($this->u_action),
					'MESSAGE_DETAIL'		=> $composer_io->getOutput(),
					'MESSAGE_DETAIL_LEGEND'	=> $this->lang->lang('COMPOSER_OUTPUT'),
					'S_USER_NOTICE'			=> true,
				]);
			break;

			case 'manage':
				$this->page_title = 'ACP_EXTENSIONS_MANAGE';

				$extension = $this->request->variable('extension', '');

				if (empty($extension))
				{
					redirect($this->u_action);
				}

				$formatter = new \phpbb\composer\io\html_output_formatter([
					'warning' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('black', 'yellow')
				]);

				$composer_io = new \phpbb\composer\io\web_io($this->lang, '', $this->composer_output, $formatter);

				try
				{
					$this->composer_manager->start_managing($extension, $composer_io);
				}
				catch (\phpbb\exception\runtime_exception $e)
				{
					$this->display_composer_exception($e, $composer_io);
					return;
				}
				$this->tpl_name = 'detailed_message_body';

				$this->template->assign_vars([
					'MESSAGE_TITLE'			=> $this->lang->lang('ACP_EXTENSIONS_MANAGE'),
					'MESSAGE_TEXT'			=> $this->lang->lang('EXTENSION_MANAGED_SUCCESS', $extension) . adm_back_link($this->u_action),
					'MESSAGE_DETAIL'		=> $composer_io->getOutput(),
					'MESSAGE_DETAIL_LEGEND'	=> $this->lang->lang('COMPOSER_OUTPUT'),
					'S_USER_NOTICE'			=> true,
				]);

			break;

			case 'list':
			default:
				if (!$this->config['exts_composer_packagist'] && $this->request->is_set('enable_packagist') && confirm_box(true))
				{
					$this->config->set('exts_composer_packagist', true);
					$this->composer_manager->reset_cache();

					trigger_error($this->lang->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
				}

				$submit = $this->request->is_set('update');

				if ($submit)
				{
					if (!check_form_key('catalog_settings'))
					{
						trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$enable_packagist	= $this->request->variable('enable_packagist', false);
					$enable_on_install	= $this->request->variable('enable_on_install', false);
					$minimum_stability	= $this->request->variable('minimum_stability', 'stable');
					$purge_on_remove	= $this->request->variable('purge_on_remove', false);

					$repositories = array_unique(
						array_filter(
							array_map(
								'trim',
								explode("\n", $this->request->variable('repositories', ''))
							)
						)
					);

					$previous_minimum_stability	= $this->config['exts_composer_minimum_stability'];
					$previous_repositories		= $this->config['exts_composer_repositories'];
					$previous_enable_packagist	= $this->config['exts_composer_packagist'];

					$this->config->set('exts_composer_enable_on_install', $enable_on_install);
					$this->config->set('exts_composer_purge_on_remove', $purge_on_remove);
					$this->config->set('exts_composer_minimum_stability', $minimum_stability);
					$this->config->set('exts_composer_repositories', json_encode($repositories, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

					if ($minimum_stability != $previous_minimum_stability
						|| $repositories != $previous_repositories
						|| $enable_packagist != $previous_enable_packagist)
					{
						$this->composer_manager->reset_cache();
					}

					if (!$this->config['exts_composer_packagist'] && $enable_packagist)
					{
						$s_hidden_fields = build_hidden_fields([
							'enable_packagist'	=> $enable_packagist
						]);

						confirm_box(false, $this->lang->lang('ENABLE_PACKAGIST_CONFIRM'), $s_hidden_fields);
					}
					else
					{
						$this->config->set('exts_composer_packagist', $enable_packagist);
						trigger_error($this->lang->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
					}
				}

				$start = $this->request->variable('start', 0);
				$base_url = $this->u_action;

				$available_extensions = $this->composer_manager->get_available_packages();
				$managed_packages = $this->composer_manager->get_managed_packages();

				$extensions = array_slice($available_extensions, $start, 20);

				$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', count($available_extensions), 20, $start);

				$this->page_title = 'ACP_EXTENSIONS_CATALOG';
				$this->tpl_name = 'acp_ext_catalog';

				$this->template->assign_vars([
					'extensions'			=> $extensions,
					'managed_extensions'	=> array_keys($managed_packages),
					'installed_extensions'	=> array_keys($this->extension_manager->all_available()),
					'settings' => [
						'enable_packagist'	=> $this->config['exts_composer_packagist'],
						'enable_on_install'	=> $this->config['exts_composer_enable_on_install'],
						'purge_on_remove'	=> $this->config['exts_composer_purge_on_remove'],
						'minimum_stability'	=> $this->config['exts_composer_minimum_stability'],
						'stabilities'		=> array_keys(\Composer\Package\BasePackage::$stabilities),
						'repositories'		=> json_decode($this->config['exts_composer_repositories'], true),
					],

					'U_ACTION'				=> $this->u_action,
				]);

				add_form_key('catalog_settings');
			break;
		}
	}

	/**
	 * Display an exception raised by the composer manager
	 *
	 * @param \phpbb\exception\runtime_exception $e
	 * @param \phpbb\composer\io\web_io          $composer_io
	 */
	protected function display_composer_exception(\phpbb\exception\runtime_exception $e, \phpbb\composer\io\web_io $composer_io)
	{
		$this->tpl_name = 'detailed_message_body';

		if ($e->getPrevious())
		{
			$message_title = $this->lang->lang_array($e->getMessage(), $e->get_parameters());

			if ($e->getPrevious() instanceof exception_interface)
			{
				$message_text = $this->lang->lang_array($e->getPrevious()->getMessage(), $e->getPrevious()->get_parameters()) . adm_back_link($this->u_action);
			}
			else
			{
				$message_text = $e->getPrevious()->getMessage() . adm_back_link($this->u_action);
			}
		}
		else
		{
			$message_title = $this->lang->lang('INFORMATION');
			$message_text  = $this->lang->lang_array($e->getMessage(), $e->get_parameters()) . adm_back_link($this->u_action);
		}

		$this->template->assign_vars([
			'MESSAGE_TITLE'			=> $message_title,
			'MESSAGE_TEXT'			=> $message_text,
			'MESSAGE_DETAIL'		=> $composer_io->getOutput(),
			'MESSAGE_DETAIL_LEGEND'	=> $this->lang->lang('COMPOSER_OUTPUT'),
			'S_USER_ERROR'			=> true,
		]);
	}

	/**
	 * Lists all the enabled extensions and dumps to the template
	 *
	 * @param array                     $managed_packages            List of managed packages
	 */
	public function list_enabled_exts(array $managed_packages)
	{
		$enabled_extension_meta_data = [];

		foreach ($this->extension_manager->all_enabled() as $name => $location)
		{
			$md_manager = $this->extension_manager->create_extension_metadata_manager($name);

			try
			{
				$meta = $md_manager->get_metadata('all');
				$enabled_extension_meta_data[$name] = [
					'META_DISPLAY_NAME'	=> $md_manager->get_metadata('display-name'),
					'META_VERSION'		=> $meta['version'],
					'META_NAME'			=> $md_manager->get_metadata('name'),
				];

				if (isset($meta['extra']['version-check']))
				{
					try
					{
						$force_update = $this->request->variable('versioncheck_force', false);
						$updates = $this->extension_manager->version_check($md_manager, $force_update, !$force_update);

						$enabled_extension_meta_data[$name]['S_UP_TO_DATE']			= empty($updates);
						$enabled_extension_meta_data[$name]['S_VERSIONCHECK']		= true;
						$enabled_extension_meta_data[$name]['U_VERSIONCHECK_FORCE']	= $this->u_action . '&amp;action=details&amp;versioncheck_force=1&amp;ext_name=' . urlencode($md_manager->get_metadata('name'));
					}
					catch (exception_interface $e)
					{
						// Ignore exceptions due to the version check
					}
				}
				else
				{
					$enabled_extension_meta_data[$name]['S_VERSIONCHECK'] = false;
				}
			}
			catch (exception_interface $e)
			{
				$message = call_user_func_array([$this->lang, 'lang'], array_merge([$e->getMessage()], $e->get_parameters()));
				$this->template->assign_block_vars('disabled', [
					'META_DISPLAY_NAME'		=> $this->lang->lang('EXTENSION_INVALID_LIST', $name, $message),
					'S_VERSIONCHECK'		=> false,
				]);
			}
			catch (\RuntimeException $e)
			{
				$enabled_extension_meta_data[$name]['S_VERSIONCHECK'] = false;
			}
		}

		uasort($enabled_extension_meta_data, [$this, 'sort_extension_meta_data_table']);

		foreach ($enabled_extension_meta_data as $name => $block_vars)
		{
			$block_vars['NAME'] = $name;
			$block_vars['U_DETAILS'] = $this->u_action . '&amp;action=details&amp;ext_name=' . urlencode($name);

			$this->template->assign_block_vars('enabled', $block_vars);

			$this->output_actions('enabled', [
				'DISABLE'		=> $this->u_action . '&amp;action=disable_pre&amp;ext_name=' . urlencode($name),
			]);

			if (isset($managed_packages[$block_vars['META_NAME']]))
			{
				$this->output_actions('enabled', [
					'UPDATE' => $this->u_catalog_action . '&amp;action=update&amp;extension=' . urlencode($block_vars['META_NAME']),
					'REMOVE' => [
						'url' => $this->u_catalog_action . '&amp;action=remove&amp;extension=' . urlencode($block_vars['META_NAME']),
						'color' => '#BC2A4D;',
					]
				]);
			}
		}
	}

	/**
	 * Lists all the disabled extensions and dumps to the template
	 *
	 * @param array                     $managed_packages            List of managed packages
	 */
	public function list_disabled_exts(array $managed_packages)
	{
		$disabled_extension_meta_data = [];

		foreach ($this->extension_manager->all_disabled() as $name => $location)
		{
			$md_manager = $this->extension_manager->create_extension_metadata_manager($name);

			try
			{
				$meta = $md_manager->get_metadata('all');
				$disabled_extension_meta_data[$name] = [
					'META_DISPLAY_NAME'	=> $md_manager->get_metadata('display-name'),
					'META_VERSION'		=> $meta['version'],
					'META_NAME'			=> $md_manager->get_metadata('name'),
				];

				if (isset($meta['extra']['version-check']))
				{
					$force_update = $this->request->variable('versioncheck_force', false);
					$updates = $this->extension_manager->version_check($md_manager, $force_update, !$force_update);

					$disabled_extension_meta_data[$name]['S_UP_TO_DATE']		= empty($updates);
					$disabled_extension_meta_data[$name]['S_VERSIONCHECK']		= true;
					$disabled_extension_meta_data[$name]['U_VERSIONCHECK_FORCE'] = $this->u_action . '&amp;action=details&amp;versioncheck_force=1&amp;ext_name=' . urlencode($md_manager->get_metadata('name'));
				}
				else
				{
					$disabled_extension_meta_data[$name]['S_VERSIONCHECK'] = false;
				}
			}
			catch (version_check_exception $e)
			{
				$disabled_extension_meta_data[$name]['S_VERSIONCHECK'] = false;
			}
			catch (exception_interface $e)
			{
				$message = call_user_func_array([$this->lang, 'lang'], array_merge([$e->getMessage()], $e->get_parameters()));
				$this->template->assign_block_vars('disabled', [
					'META_DISPLAY_NAME'		=> $this->lang->lang('EXTENSION_INVALID_LIST', $name, $message),
					'S_VERSIONCHECK'		=> false,
				]);
			}
			catch (\RuntimeException $e)
			{
				$disabled_extension_meta_data[$name]['S_VERSIONCHECK'] = false;
			}
		}

		uasort($disabled_extension_meta_data, [$this, 'sort_extension_meta_data_table']);

		foreach ($disabled_extension_meta_data as $name => $block_vars)
		{
			$block_vars['NAME'] = $name;
			$block_vars['U_DETAILS'] = $this->u_action . '&amp;action=details&amp;ext_name=' . urlencode($name);

			$this->template->assign_block_vars('disabled', $block_vars);

			$this->output_actions('disabled', [
				'ENABLE'		=> $this->u_action . '&amp;action=enable_pre&amp;ext_name=' . urlencode($name),
				'DELETE_DATA'	=> $this->u_action . '&amp;action=delete_data_pre&amp;ext_name=' . urlencode($name),
			]);

			if (isset($managed_packages[$block_vars['META_NAME']]))
			{
				$this->output_actions('disabled', [
					'UPDATE' => $this->u_catalog_action . '&amp;action=update&amp;extension=' . urlencode($block_vars['META_NAME']),
					'REMOVE' => [
						'url' => $this->u_catalog_action . '&amp;action=remove&amp;extension=' . urlencode($block_vars['META_NAME']),
						'color' => '#BC2A4D;',
					]
				]);
			}
		}
	}

	/**
	 * Lists all the available extensions and dumps to the template
	 *
	 * @param array                     $managed_packages            List of managed packages
	 */
	public function list_available_exts(array $managed_packages)
	{
		$uninstalled = array_diff_key($this->extension_manager->all_available(), $this->extension_manager->all_configured());

		$available_extension_meta_data = [];

		foreach ($uninstalled as $name => $location)
		{
			$md_manager = $this->extension_manager->create_extension_metadata_manager($name);

			try
			{
				$meta = (array) $md_manager->get_metadata('all');
				$meta_name = (string) $md_manager->get_metadata('name');

				$available_extension_meta_data[$name] = [
					'META_DISPLAY_NAME'	=> $md_manager->get_metadata('display-name'),
					'META_VERSION'		=> $meta['version'],
					'META_NAME'			=> $meta_name,
				];

				if (isset($meta['extra']['version-check']))
				{
					$force_update = $this->request->variable('versioncheck_force', false);
					$updates = $this->extension_manager->version_check($md_manager, $force_update, !$force_update);

					$available_extension_meta_data[$name]['S_UP_TO_DATE']		= empty($updates);
					$available_extension_meta_data[$name]['S_VERSIONCHECK']		= true;
					$available_extension_meta_data[$name]['U_VERSIONCHECK_FORCE'] = $this->u_action . '&amp;action=details&amp;versioncheck_force=1&amp;ext_name=' . urlencode($meta_name);
				}
				else
				{
					$available_extension_meta_data[$name]['S_VERSIONCHECK'] = false;
				}
			}
			catch (version_check_exception $e)
			{
				$available_extension_meta_data[$name]['S_VERSIONCHECK'] = false;
			}
			catch (exception_interface $e)
			{
				$message = call_user_func_array([$this->lang, 'lang'], array_merge([$e->getMessage()], $e->get_parameters()));
				$this->template->assign_block_vars('disabled', [
					'META_DISPLAY_NAME'		=> $this->lang->lang('EXTENSION_INVALID_LIST', $name, $message),
					'S_VERSIONCHECK'		=> false,
				]);
			}
		}

		uasort($available_extension_meta_data, [$this, 'sort_extension_meta_data_table']);

		foreach ($available_extension_meta_data as $name => $block_vars)
		{
			$block_vars['NAME'] = $name;
			$block_vars['U_DETAILS'] = $this->u_action . '&amp;action=details&amp;ext_name=' . urlencode($name);

			$this->template->assign_block_vars('disabled', $block_vars);

			$this->output_actions('disabled', [
				'ENABLE'		=> $this->u_action . '&amp;action=enable_pre&amp;ext_name=' . urlencode($name),
			]);
		}
	}

	/**
	 * Output actions to a block
	 *
	 * @param string	$block
	 * @param array		$actions
	 * @return array 				List of actions to be performed on the extension
	 */
	protected function output_actions($block, array $actions)
	{
		$vars_ary = [];

		foreach ($actions as $lang => $options)
		{
			$url = is_array($options) ? $options['url'] : $options;

			$vars = [
				'L_ACTION'			=> $this->lang->lang('EXTENSION_' . $lang),
				'L_ACTION_EXPLAIN'	=> $this->lang->is_set('EXTENSION_' . $lang . '_EXPLAIN') ? $this->lang->lang('EXTENSION_' . $lang . '_EXPLAIN') : '',
				'U_ACTION'			=> $url,
				'ACTION_AJAX'		=> 'ext_' . strtolower($lang),
			];

			if (isset($options['color']))
			{
				$vars['COLOR'] = $options['color'];
			}

			$this->template->assign_block_vars($block . '.actions', $vars);

			$vars_ary[] = $vars;
		}

		return $vars_ary;
	}

	/**
	 * Outputs extension metadata into the template
	 *
	 * @param array		$metadata	Array with all metadata for the extension
	 * @return void
	 */
	public function output_metadata_to_template(array $metadata)
	{
		$this->template->assign_vars([
			'META_NAME'			=> $metadata['name'],
			'META_TYPE'			=> $metadata['type'],
			'META_DESCRIPTION'	=> isset($metadata['description']) ? $metadata['description'] : '',
			'META_HOMEPAGE'		=> isset($metadata['homepage']) ? $metadata['homepage'] : '',
			'META_VERSION'		=> $metadata['version'],
			'META_TIME'			=> isset($metadata['time']) ? $metadata['time'] : '',
			'META_LICENSE'		=> $metadata['license'],

			'META_REQUIRE_PHP'		=> isset($metadata['require']['php']) ? $metadata['require']['php'] : '',
			'META_REQUIRE_PHP_FAIL'	=> isset($metadata['require']['php']) ? false : true,

			'META_REQUIRE_PHPBB'		=> isset($metadata['extra']['soft-require']['phpbb/phpbb']) ? $metadata['extra']['soft-require']['phpbb/phpbb'] : '',
			'META_REQUIRE_PHPBB_FAIL'	=> isset($metadata['extra']['soft-require']['phpbb/phpbb']) ? false : true,

			'META_DISPLAY_NAME'	=> isset($metadata['extra']['display-name']) ? $metadata['extra']['display-name'] : '',
		]);

		foreach ($metadata['authors'] as $author)
		{
			$this->template->assign_block_vars('meta_authors', [
				'AUTHOR_NAME'		=> $author['name'],
				'AUTHOR_EMAIL'		=> isset($author['email']) ? $author['email'] : '',
				'AUTHOR_HOMEPAGE'	=> isset($author['homepage']) ? $author['homepage'] : '',
				'AUTHOR_ROLE'		=> isset($author['role']) ? $author['role'] : '',
			]);
		}
	}

	/**
	 * Sort helper for the table containing the metadata about the extensions.
	 *
	 * @param array		$val1
	 * @param array		$val2
	 * @return bool
	 */
	protected function sort_extension_meta_data_table(array $val1, array $val2)
	{
		return strnatcasecmp($val1['META_DISPLAY_NAME'], $val2['META_DISPLAY_NAME']);
	}
}
