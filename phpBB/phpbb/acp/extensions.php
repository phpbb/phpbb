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

use phpbb\composer\io\html_output_formatter;
use phpbb\composer\io\web_io;
use phpbb\exception\runtime_exception;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

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

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \bantu\IniGetWrapper\IniGetWrapper */
	protected $php_ini;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string extensions.composer.output */
	protected $composer_output;

	/** @var string phpBB admin path*/
	protected $admin_path;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;
	protected $u_catalog_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config					$config				Config object
	 * @param \phpbb\composer\extension_manager		$composer_manager	Composer manager object
	 * @param \phpbb\event\dispatcher				$dispatcher			Event dispatcher object
	 * @param \phpbb\extension\manager				$extension_manager	Extension manager object
	 * @param \phpbb\language\language				$lang				Language object
	 * @param \phpbb\log\log						$log				Log object
	 * @param \phpbb\pagination						$pagination			Pagination object
	 * @param \bantu\IniGetWrapper\IniGetWrapper	$php_ini			php_ini Wrapper object
	 * @param \phpbb\request\request				$request			Request object
	 * @param \phpbb\template\template				$template			Template object
	 * @param \phpbb\user							$user				User object
	 * @param string								$composer_output	Composer output
	 * @param string								$admin_path			phpBB admin path
	 * @param string								$root_path			phpBB root path
	 * @param string								$php_ext			php File extension
	 */
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

		$u_action = $this->u_action;
		$tpl_name = '';

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
			catch (runtime_exception $e)
			{
				trigger_error($this->get_exception_message($e) . adm_back_link($this->u_action), E_USER_WARNING);
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

				$managed_packages = $this->composer_manager->check_requirements() ? $this->composer_manager->get_managed_packages() : [];

				$this->list_extensions($managed_packages);

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
				catch (runtime_exception $e)
				{
					trigger_error($this->get_exception_message($e) . adm_back_link($this->u_action), E_USER_WARNING);
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
				catch (runtime_exception $e)
				{
					trigger_error($this->get_exception_message($e) . adm_back_link($this->u_action), E_USER_WARNING);
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
					catch (runtime_exception $e)
					{
						$this->template->assign_vars([
							'S_VERSIONCHECK_FAIL'		=> true,
							'VERSIONCHECK_FAIL_REASON'	=> ($e->getMessage() !== 'VERSIONCHECK_FAIL') ? $this->get_exception_message($e) : '',
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

		$action = $this->request->variable('action', 'list');

		switch ($action)
		{
			case 'install':
			case 'remove':
			case 'update':
			case 'manage':
				$extension = $this->request->variable('extension', '');

				if (empty($extension))
				{
					redirect($this->u_action);
				}

				$action_upper = strtoupper($action);
				$action_title = 'ACP_EXTENSIONS_' . $action_upper;
				$action_text = 'EXTENSIONS_' . $action_upper . ($action === 'install' ? 'ED' : 'D');
				$action_text = $action === 'manage' ? $this->lang->lang('EXTENSION_MANAGED_SUCCESS', $extension) : $this->lang->lang($action_text);
				$action_func = $action === 'manage' ? 'start_managing' : $action;

				$this->page_title = $action_title;
				$this->tpl_name = 'detailed_message_body';

				$formatter = new html_output_formatter(['warning' => new OutputFormatterStyle('black', 'yellow')]);
				$composer_io = new web_io($this->lang, '', $this->composer_output, $formatter);

				try
				{
					$this->composer_manager->$action_func((array) $extension, $composer_io);
				}
				catch (runtime_exception $e)
				{
					$this->display_composer_exception($e, $composer_io);
					return;
				}

				$this->template->assign_vars([
					'MESSAGE_TITLE'			=> $this->lang->lang($action_title),
					'MESSAGE_TEXT'			=> $action_text . adm_back_link($this->u_action),
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

				$form_key = 'catalog_settings';
				add_form_key($form_key);

				if ($submit)
				{
					if (!check_form_key($form_key))
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
							'enable_packagist'	=> $enable_packagist,
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
			break;
		}
	}

	/**
	 * Display an exception raised by the composer manager
	 *
	 * @param runtime_exception $e
	 * @param web_io			$composer_io
	 */
	protected function display_composer_exception(runtime_exception $e, web_io $composer_io)
	{
		$this->tpl_name = 'detailed_message_body';

		/** @var runtime_exception $previous */
		if ($previous = $e->getPrevious())
		{
			$message_title = $this->lang->lang_array($e->getMessage(), $e->get_parameters());

			if ($previous instanceof runtime_exception)
			{
				$message_text = $this->lang->lang_array($previous->getMessage(), $previous->get_parameters()) . adm_back_link($this->u_action);
			}
			else
			{
				$message_text = $previous->getMessage() . adm_back_link($this->u_action);
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
	 * Lists all the extensions per type and dumps to the template
	 *
	 * @param array		$managed_packages		List of managed packages
	 * @return void
	 */
	protected function list_extensions(array $managed_packages)
	{
		$types	= [
			'enabled'	=> [
				'block'		=> 'enabled',
				'actions'	=> ['disable'],
			],
			'disabled'	=> [
				'block'		=> 'disabled',
				'actions'	=> ['enable', 'delete_data'],
			],
			'available'	=> [
				'block'		=> 'disabled',
				'actions'	=> ['enable'],
			],
		];

		foreach (array_keys($types) as $type)
		{
			$extensions_md = [];

			switch ($type)
			{
				case 'available':
					$extensions = array_diff_key(
						$this->extension_manager->all_available(),
						$this->extension_manager->all_configured()
					);
				break;

				default:
					$extensions = $this->extension_manager->{'all_' . $type}();
				break;
			}

			foreach (array_keys($extensions) as $ext_name)
			{
				$md_manager = $this->extension_manager->create_extension_metadata_manager($ext_name);

				try
				{
					$meta = $md_manager->get_metadata('al');

					$extensions_md[$ext_name] = [
						'META_NAME'			=> $md_manager->get_metadata('name'),
						'META_VERSION'		=> $meta['version'],
						'META_DISPLAY_NAME'	=> $md_manager->get_metadata('display-name'),
					];

					if (isset($meta['extra']['version-check']))
					{
						try
						{
							$update_force = $this->request->variable('versioncheck_force', false);
							$update_avail = $this->extension_manager->version_check($md_manager, $update_force, !$update_force);

							$extensions_md[$ext_name] += [
								'S_UP_TO_DATE'			=> empty($update_avail),
								'S_VERSIONCHECK'		=> true,
								'U_VERSIONCHECK_FORCE'	=> $this->u_action . '&amp;action=details&amp;versioncheck_force=1&amp;ext_name=' . urlencode((string) $md_manager->get_metadata('name')),
							];
						}
						catch (runtime_exception $e)
						{
							// Ignore exceptions due to the version check
						}
					}
					else
					{
						$extensions_md[$ext_name]['S_VERSIONCHECK'] = false;
					}
				}
				catch (runtime_exception $e)
				{
					$this->template->assign_block_vars('disabled', [
						'META_DISPLAY_NAME'	=> $this->lang->lang('EXTENSION_INVALID_LIST', $ext_name, $this->get_exception_message($e)),
						'S_VERSIONCHECK'	=> false,
					]);
				}
				catch (\RuntimeException $e)
				{
					$extensions_md[$ext_name]['S_VERSIONCHECK'] = false;
				}
			}

			uasort($extensions_md, [$this, 'sort_Extension_meta_data_table']);

			foreach ($extensions_md as $ext_name => $block_vars)
			{
				$block_vars += [
					'NAME'		=> $ext_name,
					'U_DETAILS'	=> $this->u_action . '&amp;action=details&amp;ext_name=' . $ext_name,
				];

				$this->template->assign_block_vars($types[$type]['block'], $block_vars);

				$actions = [];

				foreach ($types[$type]['actions'] as $action)
				{
					$actions[strtoupper($action)] = $this->u_action . "&amp;action={$action}_pre&amp;ext_nam" . urlencode($ext_name);
				}

				$this->output_actions($types[$type]['block'], $actions);

				if ($type !== 'available' && isset($managed_packages[$block_vars['META_NAME']]))
				{
					$this->output_actions($types[$type]['block'], [
						'UPDATE'	=> $this->u_catalog_action . '&amp;action=update&amp;extension=' . urlencode($block_vars['META_NAME']),
						'REMOVE'	=> [
							'url'	=> $this->u_catalog_action . '&amp;action=remove&amp;extension=' . urlencode($block_vars['META_NAME']),
							'color'	=> '#bc2a4d',
						],
					]);
				}
			}
		}
	}

	/**
	 * Output actions to a block
	 *
	 * @param string	$block		The template block name
	 * @param array		$actions	The extension action
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
	 * Helper function for displaying an exception message.
	 *
	 * @param runtime_exception	$e		The exception
	 * @return string					The localised exception message
	 */
	protected function get_exception_message($e)
	{
		return call_user_func_array([$this->lang, 'lang'], array_merge([$e->getMessage()], $e->get_parameters()));
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
