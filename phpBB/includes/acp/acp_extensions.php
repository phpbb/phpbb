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

use phpbb\exception\exception_interface;
use phpbb\exception\runtime_exception;
use phpbb\exception\version_check_exception;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_extensions
{
	private const DEFAULT_COMPOSER_REPOSITORIES = [
		'https://satis.phpbb.com/',
		'https://www.phpbb.com/customise/db/composer/40/',
	];

	public $u_action;
	public $tpl_name;
	public $page_title;

	private $db;

	/** @var  phpbb\config\config */
	private $config;

	/** @var \phpbb\template\twig\twig */
	private $template;
	private $user;
	private $log;

	/** @var \phpbb\request\request */
	private $request;
	private $phpbb_dispatcher;

	/** @var \phpbb\extension\manager */
	private $ext_manager;

	private $phpbb_container;
	private $php_ini;
	private $u_catalog_action;
	private $u_manager_action;

	/** @var string */
	private $phpbb_root_path;

	function main($id, $mode)
	{
		// Start the page
		global $config, $user, $template, $request, $phpbb_extension_manager, $db, $phpbb_log, $phpbb_dispatcher, $phpbb_container, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$this->db       = $db;
		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
		$this->request = $request;
		$this->log = $phpbb_log;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->ext_manager = $phpbb_extension_manager;
		$this->phpbb_container = $phpbb_container;
		$this->php_ini = $this->phpbb_container->get('php_ini');
		$this->phpbb_root_path = $phpbb_root_path;
		$is_catalog_available = $phpbb_container->hasParameter('extensions.enable_catalog') && $phpbb_container->getParameter('extensions.enable_catalog') === true;
		$this->u_catalog_action = append_sid("{$phpbb_admin_path}index.$phpEx", "i=$id&amp;mode=catalog");
		$this->u_manager_action = append_sid("{$phpbb_admin_path}index.$phpEx", "i=$id&amp;mode=main");

		$this->user->add_lang(['install', 'acp/extensions', 'acp/modules', 'migrator']);

		if ($mode === 'catalog' && $is_catalog_available)
		{
			$this->catalog_mode($id, $mode);
		}
		else
		{
			$this->main_mode($id, $mode);
		}
	}

	public function main_mode($id, $mode)
	{
		global $phpbb_extension_manager, $phpbb_container;

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

		if (in_array($action, array('enable', 'disable', 'delete_data')) && !check_link_hash($this->request->variable('hash', ''), $action . '.' . $ext_name))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
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
		$vars = array('action', 'u_action', 'ext_name', 'safe_time_limit', 'start_time', 'tpl_name');
		extract($this->phpbb_dispatcher->trigger_event('core.acp_extensions_run_action_before', compact($vars)));

		// In case they have been updated by the event
		$this->u_action = $u_action;
		$this->tpl_name = $tpl_name;

		// If they've specified an extension, let's load the metadata manager and validate it.
		if ($ext_name)
		{
			$md_manager = $this->ext_manager->create_extension_metadata_manager($ext_name);

			try
			{
				$md_manager->get_metadata('all');
			}
			catch (exception_interface $e)
			{
				$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
				trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

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
					$s_hidden_fields = build_hidden_fields(array(
						'force_unstable'	=> $force_unstable,
					));

					confirm_box(false, $this->user->lang('EXTENSION_FORCE_UNSTABLE_CONFIRM'), $s_hidden_fields);
				}
				else
				{
					$this->config->set('extension_force_unstable', false);
					trigger_error($this->user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
				}
			break;

			case 'list':
			default:
				if (confirm_box(true))
				{
					$this->config->set('extension_force_unstable', true);
					trigger_error($this->user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
				}

				/** @var \phpbb\composer\manager $composer_manager */
				$composer_manager = $phpbb_container->get('ext.composer.manager');

				$managed_packages = [];
				$available_updates = [];
				if ($composer_manager->check_requirements())
				{
					$managed_packages = $composer_manager->get_managed_packages();
					$available_updates = $composer_manager->get_available_updates();
				}

				$this->list_enabled_exts($phpbb_extension_manager, $managed_packages, $available_updates);
				$this->list_disabled_exts($phpbb_extension_manager, $managed_packages, $available_updates);
				$this->list_available_exts($managed_packages);

				$this->tpl_name = 'acp_ext_list';

				$this->template->assign_vars(array(
					'U_VERSIONCHECK_FORCE' 	=> $this->u_action . '&amp;action=list&amp;versioncheck_force=1',
					'FORCE_UNSTABLE'		=> $this->config['extension_force_unstable'],
					'U_ACTION' 				=> $this->u_action,
					'MANAGED_EXTENSIONS'	=> $managed_packages,
					'U_CATALOG_ACTION' 		=> $this->u_catalog_action,
				));

				$this->request->disable_super_globals();
			break;

			case 'enable_pre':
				try
				{
					$md_manager->validate_enable();
				}
				catch (exception_interface $e)
				{
					$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
					trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$extension = $this->ext_manager->get_extension($ext_name);

				$this->check_is_enableable($extension);

				if ($this->ext_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}

				$this->tpl_name = 'acp_ext_enable';

				$this->template->assign_vars([
					'S_PRE_STEP'		=> true,
					'CONFIRM_MESSAGE'	=> $this->user->lang('EXTENSION_ENABLE_CONFIRM', $md_manager->get_metadata('display-name')),
					'U_ENABLE'			=> $this->u_action . '&amp;action=enable&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('enable.' . $ext_name),
				]);
			break;

			case 'enable':
				try
				{
					$md_manager->validate_enable();
				}
				catch (exception_interface $e)
				{
					$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
					trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$extension = $this->ext_manager->get_extension($ext_name);

				$this->check_is_enableable($extension);

				try
				{
					while ($this->ext_manager->enable_step($ext_name))
					{
						// Are we approaching the time limit? If so we want to pause the update and continue after refreshing
						if ((time() - $start_time) >= $safe_time_limit)
						{
							$this->template->assign_var('S_NEXT_STEP', true);

							meta_refresh(0, $this->u_action . '&amp;action=enable&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('enable.' . $ext_name));
						}
					}

					// Update custom style for admin area
					$this->template->set_custom_style(array(
						array(
							'name' 		=> 'adm',
							'ext_path' 	=> 'adm/style/',
						),
					), $phpbb_admin_path . 'style');

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_EXT_ENABLE', time(), array($ext_name));
				}
				catch (\phpbb\db\migration\exception $e)
				{
					$this->template->assign_var('MIGRATOR_ERROR', $e->getLocalisedMessage($this->user));
				}
				catch (\Exception $e)
				{
					$this->template->assign_var('MIGRATOR_ERROR', $e->getMessage());
					$this->template->assign_var('MIGRATOR_ERROR_STACK_TRACE', phpbb_filter_root_path($e->getTraceAsString()));
				}

				$this->tpl_name = 'acp_ext_enable';

				$this->template->assign_vars([
					'U_RETURN'		=> $this->u_action . '&amp;action=list',
				]);
			break;

			case 'disable_pre':
				if (!$this->ext_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}

				$this->tpl_name = 'acp_ext_disable';

				$this->template->assign_vars([
					'S_PRE_STEP'		=> true,
					'CONFIRM_MESSAGE'	=> $this->user->lang('EXTENSION_DISABLE_CONFIRM', $md_manager->get_metadata('display-name')),
					'U_DISABLE'			=> $this->u_action . '&amp;action=disable&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('disable.' . $ext_name),
				]);
			break;

			case 'disable':
				if (!$this->ext_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}

				while ($this->ext_manager->disable_step($ext_name))
				{
					// Are we approaching the time limit? If so we want to pause the update and continue after refreshing
					if ((time() - $start_time) >= $safe_time_limit)
					{
						$this->template->assign_var('S_NEXT_STEP', true);

						meta_refresh(0, $this->u_action . '&amp;action=disable&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('disable.' . $ext_name));
					}
				}
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_EXT_DISABLE', time(), array($ext_name));

				$this->tpl_name = 'acp_ext_disable';

				$this->template->assign_vars([
					'U_RETURN'	=> $this->u_action . '&amp;action=list',
				]);
			break;

			case 'delete_data_pre':
				if ($this->ext_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}

				$this->tpl_name = 'acp_ext_delete_data';

				$this->template->assign_vars([
					'S_PRE_STEP'		=> true,
					'CONFIRM_MESSAGE'	=> $this->user->lang('EXTENSION_DELETE_DATA_CONFIRM', $md_manager->get_metadata('display-name')),
					'U_PURGE'			=> $this->u_action . '&amp;action=delete_data&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('delete_data.' . $ext_name),
				]);
			break;

			case 'delete_data':
				if ($this->ext_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}

				try
				{
					while ($this->ext_manager->purge_step($ext_name))
					{
						// Are we approaching the time limit? If so we want to pause the update and continue after refreshing
						if ((time() - $start_time) >= $safe_time_limit)
						{
							$this->template->assign_var('S_NEXT_STEP', true);

							meta_refresh(0, $this->u_action . '&amp;action=delete_data&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('delete_data.' . $ext_name));
						}
					}
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_EXT_PURGE', time(), array($ext_name));
				}
				catch (\phpbb\db\migration\exception $e)
				{
					$this->template->assign_var('MIGRATOR_ERROR', $e->getLocalisedMessage($this->user));
				}
				catch (\Exception $e)
				{
					$this->template->assign_var('MIGRATOR_ERROR', $e->getMessage());
					$this->template->assign_var('MIGRATOR_ERROR_STACK_TRACE', phpbb_filter_root_path($e->getTraceAsString()));
				}

				$this->tpl_name = 'acp_ext_delete_data';

				$this->template->assign_vars([
					'U_RETURN'	=> $this->u_action . '&amp;action=list',
				]);
			break;

			case 'details':
				// Output it to the template
				$meta = $md_manager->get_metadata('all');
				$this->output_metadata_to_template($meta);

				if (isset($meta['extra']['version-check']))
				{
					try
					{
						$updates_available = $this->ext_manager->version_check($md_manager, $this->request->variable('versioncheck_force', false), false, $this->config['extension_force_unstable'] ? 'unstable' : null);

						$this->template->assign_vars(array(
							'S_UP_TO_DATE' => empty($updates_available),
							'UP_TO_DATE_MSG' => $this->user->lang(empty($updates_available) ? 'UP_TO_DATE' : 'NOT_UP_TO_DATE', $md_manager->get_metadata('display-name')),
						));

						$this->template->assign_block_vars('updates_available', $updates_available);
					}
					catch (runtime_exception $e)
					{
						$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));

						$this->template->assign_vars(array(
							'S_VERSIONCHECK_FAIL' => true,
							'VERSIONCHECK_FAIL_REASON' => ($e->getMessage() !== 'VERSIONCHECK_FAIL') ? $message : '',
						));
					}
					$this->template->assign_var('S_VERSIONCHECK', true);
				}
				else
				{
					$this->template->assign_var('S_VERSIONCHECK', false);
				}

				$this->template->assign_vars(array(
					'U_BACK'				=> $this->u_action . '&amp;action=list',
					'U_VERSIONCHECK_FORCE'	=> $this->u_action . '&amp;action=details&amp;versioncheck_force=1&amp;ext_name=' . urlencode($md_manager->get_metadata('name')),
				));

				$this->tpl_name = 'acp_ext_details';
			break;
		}

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
		$u_action = $this->u_action;
		$tpl_name = $this->tpl_name;
		$vars = array('action', 'u_action', 'ext_name', 'safe_time_limit', 'start_time', 'tpl_name');
		extract($this->phpbb_dispatcher->trigger_event('core.acp_extensions_run_action_after', compact($vars)));

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
		global $phpbb_container;

		$action = $this->request->variable('action', 'list');
		$origin = $this->get_composer_action_origin();

		/** @var \phpbb\language\language $language */
		$language = $phpbb_container->get('language');

		/** @var \phpbb\composer\manager $composer_manager */
		$composer_manager = $phpbb_container->get('ext.composer.manager');

		/** @var \phpbb\extension\manager $extensions_manager */
		$extensions_manager = $phpbb_container->get('ext.manager');

		$composer_actions = ['install', 'remove', 'update', 'manage'];
		if (in_array($action, $composer_actions, true))
		{
			$extension = $this->request->variable('extension', '');

			if ($this->request->is_set_post('cancel'))
			{
				redirect($this->get_composer_return_url($origin));
			}

			if (!$this->is_valid_composer_package_name($extension))
			{
				trigger_error($language->lang('EXTENSIONS_INVALID_PACKAGE') . $this->get_composer_back_links($origin), E_USER_WARNING);
			}

			if (!confirm_box(true))
			{
				confirm_box(false, $language->lang('EXTENSIONS_' . strtoupper($action) . '_CONFIRM', $extension), build_hidden_fields([
					'action' => $action,
					'extension' => $extension,
					'origin' => $origin,
				]));
			}

			$this->validate_composer_action($action, $extension, $composer_manager, $extensions_manager, $language, $origin);
		}

		if (!$composer_manager->check_requirements())
		{
			$this->page_title = 'ACP_EXTENSIONS_CATALOG';
			$this->tpl_name = 'message_body';

			$this->template->assign_vars([
				'MESSAGE_TITLE'	=> $language->lang('EXTENSIONS_CATALOG_NOT_AVAILABLE'),
				'MESSAGE_TEXT'	=> $language->lang('EXTENSIONS_COMPOSER_NOT_WRITABLE') . $this->get_composer_back_links($origin),
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
					redirect($this->get_composer_return_url($origin));
				}

				$formatter = new \phpbb\composer\io\html_output_formatter([
					'warning' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('black', 'yellow')
				]);

				$composer_io = new \phpbb\composer\io\web_io($language, '', $phpbb_container->getParameter('extensions.composer.output'), $formatter);

				try
				{
					$composer_manager->install((array) $extension, $composer_io);
				}
				catch (\phpbb\exception\runtime_exception $e)
				{
					$this->display_composer_exception($language, $e, $composer_io, $origin);
					return;
				}
				$this->tpl_name = 'detailed_message_body';

				$this->template->assign_vars(array(
						'MESSAGE_TITLE'			=> $language->lang('ACP_EXTENSIONS_INSTALL'),
						'MESSAGE_TEXT'			=> $language->lang('EXTENSIONS_INSTALLED') . $this->get_composer_back_links($origin),
						'MESSAGE_DETAIL'		=> $composer_io->getOutput(),
						'MESSAGE_DETAIL_LEGEND'	=> $language->lang('COMPOSER_OUTPUT'),
						'S_USER_NOTICE'			=> true,
					)
				);

			break;
			case 'remove':
				$this->page_title = 'ACP_EXTENSIONS_REMOVE';

				$extension = $this->request->variable('extension', '');

				if (empty($extension))
				{
					redirect($this->get_composer_return_url($origin));
				}

				$formatter = new \phpbb\composer\io\html_output_formatter([
					'warning' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('black', 'yellow')
				]);

				$composer_io = new \phpbb\composer\io\web_io($language, '', $phpbb_container->getParameter('extensions.composer.output'), $formatter);

				try
				{
					$composer_manager->remove((array) $extension, $composer_io);
				}
				catch (\phpbb\exception\runtime_exception $e)
				{
					$this->display_composer_exception($language, $e, $composer_io, $origin);
					return;
				}
				$this->tpl_name = 'detailed_message_body';

				$this->template->assign_vars(array(
						'MESSAGE_TITLE'			=> $language->lang('ACP_EXTENSIONS_REMOVE'),
						'MESSAGE_TEXT'			=> $language->lang('EXTENSIONS_REMOVED') . $this->get_composer_back_links($origin),
						'MESSAGE_DETAIL'		=> $composer_io->getOutput(),
						'MESSAGE_DETAIL_LEGEND'	=> $language->lang('COMPOSER_OUTPUT'),
						'S_USER_NOTICE'			=> true,
					)
				);

			break;
			case 'update':
				$this->page_title = 'ACP_EXTENSIONS_UPDATE';

				$extension = $this->request->variable('extension', '');

				if (empty($extension))
				{
					redirect($this->get_composer_return_url($origin));
				}

				$formatter = new \phpbb\composer\io\html_output_formatter([
					'warning' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('black', 'yellow')
				]);

				$composer_io = new \phpbb\composer\io\web_io($language, '', $phpbb_container->getParameter('extensions.composer.output'), $formatter);

				try
				{
					$composer_manager->update((array) $extension, $composer_io);
				}
				catch (\phpbb\exception\runtime_exception $e)
				{
					$this->display_composer_exception($language, $e, $composer_io, $origin);
					return;
				}
				$this->tpl_name = 'detailed_message_body';

				$this->template->assign_vars(array(
						'MESSAGE_TITLE'			=> $language->lang('ACP_EXTENSIONS_UPDATE'),
						'MESSAGE_TEXT'			=> $language->lang('EXTENSIONS_UPDATED') . $this->get_composer_back_links($origin),
						'MESSAGE_DETAIL'		=> $composer_io->getOutput(),
						'MESSAGE_DETAIL_LEGEND'	=> $language->lang('COMPOSER_OUTPUT'),
						'S_USER_NOTICE'			=> true,
					)
				);

			break;
			case 'manage':
				$this->page_title = 'ACP_EXTENSIONS_MANAGE';

				$extension = $this->request->variable('extension', '');

				if (empty($extension))
				{
					redirect($this->get_composer_return_url($origin));
				}

				$formatter = new \phpbb\composer\io\html_output_formatter([
					'warning' => new \Symfony\Component\Console\Formatter\OutputFormatterStyle('black', 'yellow')
				]);

				$composer_io = new \phpbb\composer\io\web_io($language, '', $phpbb_container->getParameter('extensions.composer.output'), $formatter);

				try
				{
					$composer_manager->start_managing($extension, $composer_io);
				}
				catch (\phpbb\exception\runtime_exception $e)
				{
					$this->display_composer_exception($language, $e, $composer_io, $origin);
					return;
				}
				$this->tpl_name = 'detailed_message_body';

				$this->template->assign_vars(array(
						'MESSAGE_TITLE'			=> $language->lang('ACP_EXTENSIONS_MANAGE'),
						'MESSAGE_TEXT'			=> $language->lang('EXTENSION_MANAGED_SUCCESS', $extension) . $this->get_composer_back_links($origin),
						'MESSAGE_DETAIL'		=> $composer_io->getOutput(),
						'MESSAGE_DETAIL_LEGEND'	=> $language->lang('COMPOSER_OUTPUT'),
						'S_USER_NOTICE'			=> true,
					)
				);

			break;
			case 'list':
			default:
				if (!$this->config['exts_composer_packagist'] && $this->request->is_set('enable_packagist') && confirm_box(true))
				{
					$this->config->set('exts_composer_packagist', true);
					$composer_manager->reset_cache();

					trigger_error($language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
				}

				$submit = $this->request->is_set('update');
				if ($submit)
				{
					if (!check_form_key('catalog_settings'))
					{
						trigger_error($language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$enable_packagist = $this->request->variable('enable_packagist', false);
					$enable_on_install = $this->request->variable('enable_on_install', false);
					$purge_on_remove = $this->request->variable('purge_on_remove', false);
					$minimum_stability = $this->request->variable('minimum_stability', 'stable');
					$repositories = array_values(array_unique(
						array_filter(
							array_map(
								'trim',
								explode("\n", $this->request->variable('repositories', ''))
							)
						)
					));

					if (!array_key_exists($minimum_stability, \Composer\Package\BasePackage::$stabilities))
					{
						trigger_error($language->lang('EXTENSIONS_INVALID_STABILITY') . adm_back_link($this->u_action), E_USER_WARNING);
					}

					foreach ($repositories as $repository)
					{
						$parts = parse_url($repository);
						if ($parts === false || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']))
						{
							trigger_error($language->lang('EXTENSIONS_INVALID_REPOSITORY', $repository) . adm_back_link($this->u_action), E_USER_WARNING);
						}
					}

					$previous_minimum_stability = $this->config['exts_composer_minimum_stability'];
					$previous_repositories = array_values(json_decode($this->config['exts_composer_repositories'], true) ?: []);
					$previous_enable_packagist = $this->config['exts_composer_packagist'];

					$this->config->set('exts_composer_enable_on_install', $enable_on_install);
					$this->config->set('exts_composer_purge_on_remove', $purge_on_remove);
					$this->config->set('exts_composer_minimum_stability', $minimum_stability);
					$this->config->set('exts_composer_repositories', json_encode($repositories, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

					if ($minimum_stability != $previous_minimum_stability
						|| $repositories !== $previous_repositories
						|| $enable_packagist != $previous_enable_packagist)
					{
						$composer_manager->reset_cache();
					}

					if (!$this->config['exts_composer_packagist'] && $enable_packagist)
					{
						$s_hidden_fields = build_hidden_fields(array(
							'enable_packagist'	=> $enable_packagist
						));

						confirm_box(false, $language->lang('ENABLE_PACKAGIST_CONFIRM'), $s_hidden_fields);
					}
					else
					{
						$this->config->set('exts_composer_packagist', $enable_packagist);
						trigger_error($language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
					}
				}

				/** @var \phpbb\composer\extension_manager $manager */
				$manager = $phpbb_container->get('ext.composer.manager');

				/** @var \phpbb\pagination $pagination */
				$pagination = $phpbb_container->get('pagination');

				$start = $this->request->variable('start', 0);
				$base_url = $this->u_action;

				$available_extensions = $manager->get_available_packages();
				$managed_packages = $manager->get_managed_packages();

				$extensions = array_slice($available_extensions, $start, 20);

				$pagination->generate_template_pagination($base_url, 'pagination', 'start', count($available_extensions), 20, $start);

				$this->page_title = 'ACP_EXTENSIONS_CATALOG';
				$this->tpl_name = 'acp_ext_catalog';

				$this->template->assign_vars([
					'extensions'			=> $extensions,
					'managed_extensions'	=> array_keys($managed_packages),
					'installed_extensions'	=> array_keys($extensions_manager->all_available()),
					'U_ACTION'				=> $this->u_action,
					'settings' => [
						'enable_packagist'	=> $this->config['exts_composer_packagist'],
						'enable_on_install'	=> $this->config['exts_composer_enable_on_install'],
						'purge_on_remove'	=> $this->config['exts_composer_purge_on_remove'],
						'minimum_stability'	=> $this->config['exts_composer_minimum_stability'],
						'stabilities'		=> array_keys(\Composer\Package\BasePackage::$stabilities),
						'repositories'		=> json_decode($this->config['exts_composer_repositories'], true),
						'default_repositories'	=> self::DEFAULT_COMPOSER_REPOSITORIES,
					],
				]);

				add_form_key('catalog_settings');

			break;
		}
	}

	/**
	 * Display an exception raised by the composer manager
	 *
	 * @param \phpbb\language\language           $language
	 * @param \phpbb\exception\runtime_exception $e
	 * @param \phpbb\composer\io\web_io          $composer_io
	 * @param string                               $origin
	 */
	private function display_composer_exception(\phpbb\language\language $language, \phpbb\exception\runtime_exception $e, \phpbb\composer\io\web_io $composer_io, string $origin)
	{
		$this->tpl_name = 'detailed_message_body';

		if ($e->getPrevious())
		{
			$message_title = $language->lang_array($e->getMessage(), $e->get_parameters());

			if ($e->getPrevious() instanceof \phpbb\exception\exception_interface)
			{
				$message_text  = $language->lang_array($e->getPrevious()->getMessage(), $e->getPrevious()->get_parameters());
			}
			else
			{
				$message_text = $e->getPrevious()->getMessage();
			}
		}
		else
		{
			$message_title = $language->lang('INFORMATION');
			$message_text  = $language->lang_array($e->getMessage(), $e->get_parameters());
		}

		$message_text .= $this->get_composer_back_links($origin);

		$this->template->assign_vars(array(
				'MESSAGE_TITLE'			=> $message_title,
				'MESSAGE_TEXT'			=> $message_text,
				'MESSAGE_DETAIL'		=> $composer_io->getOutput(),
				'MESSAGE_DETAIL_LEGEND'	=> $language->lang('COMPOSER_OUTPUT'),
				'S_USER_ERROR'			=> true,
			)
		);
	}

	/**
	 * Validate a Composer package name before passing it to Composer.
	 */
	private function is_valid_composer_package_name(string $package): bool
	{
		return \Composer\Package\Loader\ValidatingArrayLoader::hasPackageNamingError($package, true) === null;
	}

	/**
	 * Ensure ACP actions can only target packages exposed by the catalog or already managed by phpBB.
	 */
	private function validate_composer_action($action, $extension, \phpbb\composer\manager_interface $composer_manager, \phpbb\extension\manager $extensions_manager, \phpbb\language\language $language, string $origin)
	{
		$allowed = false;

		if ($action === 'install' || $action === 'manage')
		{
			foreach ($composer_manager->get_available_packages() as $package)
			{
				if (isset($package['composer_name']) && $package['composer_name'] === $extension)
				{
					$allowed = $action === 'install' || $extensions_manager->is_available($extension);
					break;
				}
			}
		}
		else
		{
			$allowed = array_key_exists($extension, $composer_manager->get_managed_packages());
		}

		if (!$allowed)
		{
			trigger_error($language->lang('EXTENSIONS_ACTION_NOT_ALLOWED') . $this->get_composer_back_links($origin), E_USER_WARNING);
		}
	}

	/**
	 * Return the trusted page from which a Composer action originated.
	 */
	private function get_composer_action_origin(): string
	{
		$origin = $this->request->variable('origin', 'catalog');

		return $origin === 'manager' ? 'manager' : 'catalog';
	}

	/**
	 * Return the ACP URL matching a trusted Composer action origin.
	 */
	private function get_composer_return_url(string $origin): string
	{
		return $origin === 'manager' ? $this->u_manager_action : $this->u_catalog_action;
	}

	/**
	 * Build explicit links to both extension management pages, with the origin first.
	 */
	private function get_composer_back_links(string $origin): string
	{
		$links = [
			'manager' => '<a href="' . $this->u_manager_action . '">&laquo; ' . $this->user->lang('RETURN_TO_EXTENSION_MANAGER') . '</a>',
			'catalog' => '<a href="' . $this->u_catalog_action . '">&laquo; ' . $this->user->lang('RETURN_TO_EXTENSION_CATALOG') . '</a>',
		];

		if ($origin === 'catalog')
		{
			$links = array_reverse($links, true);
		}

		return '<br /><br />' . implode('<br />', $links);
	}

	/**
	 * Lists all the enabled extensions and dumps to the template
	 *
	 * @param \phpbb\extension\manager  $phpbb_extension_manager     An instance of the extension manager
	 * @param array                     $managed_packages            List of managed packages
	 * @param array                     $available_updates           List of managed packages with updates
	 *
	 * @return void
	 */
	public function list_enabled_exts(\phpbb\extension\manager $phpbb_extension_manager, array $managed_packages, array $available_updates)
	{
		$enabled_extension_meta_data = array();

		foreach ($this->ext_manager->all_enabled() as $name => $location)
		{
			$md_manager = $this->ext_manager->create_extension_metadata_manager($name);

			try
			{
				$meta = $md_manager->get_metadata('all');
				$enabled_extension_meta_data[$name] = array(
					'META_DISPLAY_NAME' => $md_manager->get_metadata('display-name'),
					'META_VERSION' => $meta['version'],
					'META_NAME' => $md_manager->get_metadata('name'),
				);

				if (isset($meta['extra']['version-check']))
				{
					try
					{
						$force_update = $this->request->variable('versioncheck_force', false);
						$updates = $this->ext_manager->version_check($md_manager, $force_update, !$force_update);

						$enabled_extension_meta_data[$name]['S_UP_TO_DATE'] = empty($updates);
						$enabled_extension_meta_data[$name]['S_VERSIONCHECK'] = true;
						$enabled_extension_meta_data[$name]['U_VERSIONCHECK_FORCE'] = $this->u_action . '&amp;action=details&amp;versioncheck_force=1&amp;ext_name=' . urlencode($md_manager->get_metadata('name'));
					}
					catch (runtime_exception $e)
					{
						// Ignore exceptions due to the version check
					}
				}
				else
				{
					$enabled_extension_meta_data[$name]['S_VERSIONCHECK'] = false;
				}
			}
			catch (runtime_exception $e)
			{
				$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
				$this->template->assign_block_vars('disabled', array(
					'META_DISPLAY_NAME'		=> $this->user->lang('EXTENSION_INVALID_LIST', $name, $message),
					'S_VERSIONCHECK'		=> false,
				));
			}
			catch (\RuntimeException $e)
			{
				$enabled_extension_meta_data[$name]['S_VERSIONCHECK'] = false;
			}
		}

		uasort($enabled_extension_meta_data, array($this, 'sort_extension_meta_data_table'));

		foreach ($enabled_extension_meta_data as $name => $block_vars)
		{
			$block_vars['NAME'] = $name;
			$block_vars['U_DETAILS'] = $this->u_action . '&amp;action=details&amp;ext_name=' . urlencode($name);

			$this->template->assign_block_vars('enabled', $block_vars);

			$this->output_actions('enabled', array(
				'DISABLE'		=> $this->u_action . '&amp;action=disable_pre&amp;ext_name=' . urlencode($name),
			));

			if (isset($managed_packages[$block_vars['META_NAME']]))
			{
				$actions = [
					'REMOVE' => $this->u_catalog_action . '&amp;action=remove&amp;origin=manager&amp;extension=' . urlencode($block_vars['META_NAME']),
				];

				if (isset($available_updates[$block_vars['META_NAME']]))
				{
					$actions = [
						'UPDATE' => $this->u_catalog_action . '&amp;action=update&amp;origin=manager&amp;extension=' . urlencode($block_vars['META_NAME']),
					] + $actions;
				}

				$this->output_actions('enabled', $actions);
			}
		}
	}

	/**
	 * Lists all the disabled extensions and dumps to the template
	 *
	 * @param \phpbb\extension\manager  $phpbb_extension_manager     An instance of the extension manager
	 * @param array                     $managed_packages            List of managed packages
	 * @param array                     $available_updates           List of managed packages with updates
	 *
	 * @return void
	 */
	public function list_disabled_exts(\phpbb\extension\manager $phpbb_extension_manager, array $managed_packages, array $available_updates)
	{
		$disabled_extension_meta_data = array();

		foreach ($this->ext_manager->all_disabled() as $name => $location)
		{
			$md_manager = $this->ext_manager->create_extension_metadata_manager($name);

			try
			{
				$meta = $md_manager->get_metadata('all');
				$disabled_extension_meta_data[$name] = array(
					'META_DISPLAY_NAME' => $md_manager->get_metadata('display-name'),
					'META_VERSION' => $meta['version'],
					'META_NAME' => $md_manager->get_metadata('name'),
				);

				if (isset($meta['extra']['version-check']))
				{
					$force_update = $this->request->variable('versioncheck_force', false);
					$updates = $this->ext_manager->version_check($md_manager, $force_update, !$force_update);

					$disabled_extension_meta_data[$name]['S_UP_TO_DATE'] = empty($updates);
					$disabled_extension_meta_data[$name]['S_VERSIONCHECK'] = true;
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
			catch (runtime_exception $e)
			{
				$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
				$this->template->assign_block_vars('disabled', array(
					'META_DISPLAY_NAME'		=> $this->user->lang('EXTENSION_INVALID_LIST', $name, $message),
					'S_VERSIONCHECK'		=> false,
				));
			}
			catch (\RuntimeException $e)
			{
				$disabled_extension_meta_data[$name]['S_VERSIONCHECK'] = false;
			}
		}

		uasort($disabled_extension_meta_data, array($this, 'sort_extension_meta_data_table'));

		foreach ($disabled_extension_meta_data as $name => $block_vars)
		{
			$block_vars['NAME'] = $name;
			$block_vars['U_DETAILS'] = $this->u_action . '&amp;action=details&amp;ext_name=' . urlencode($name);

			$this->template->assign_block_vars('disabled', $block_vars);

			$this->output_actions('disabled', array(
				'ENABLE'		=> $this->u_action . '&amp;action=enable_pre&amp;ext_name=' . urlencode($name),
				'DELETE_DATA'	=> $this->u_action . '&amp;action=delete_data_pre&amp;ext_name=' . urlencode($name),
			));

			if (isset($managed_packages[$block_vars['META_NAME']]))
			{
				$actions = [
					'REMOVE' => $this->u_catalog_action . '&amp;action=remove&amp;origin=manager&amp;extension=' . urlencode($block_vars['META_NAME']),
				];

				if (isset($available_updates[$block_vars['META_NAME']]))
				{
					$actions = [
						'UPDATE' => $this->u_catalog_action . '&amp;action=update&amp;origin=manager&amp;extension=' . urlencode($block_vars['META_NAME']),
					] + $actions;
				}

				$this->output_actions('disabled', $actions);
			}
		}
	}

	/**
	 * Lists all the available extensions and dumps to the template
	 *
	 * @param array                     $managed_packages            List of managed packages
	 *
	 * @return void
	 */
	public function list_available_exts(array $managed_packages)
	{
		$uninstalled = array_diff_key($this->ext_manager->all_available(), $this->ext_manager->all_configured());

		$available_extension_meta_data = array();

		foreach ($uninstalled as $name => $location)
		{
			$md_manager = $this->ext_manager->create_extension_metadata_manager($name);

			try
			{
				$meta = $md_manager->get_metadata('all');
				$available_extension_meta_data[$name] = array(
					'META_DISPLAY_NAME' => $md_manager->get_metadata('display-name'),
					'META_VERSION' => $meta['version'],
					'META_NAME' => $md_manager->get_metadata('name'),
				);

				if (isset($meta['extra']['version-check']))
				{
					$force_update = $this->request->variable('versioncheck_force', false);
					$updates = $this->ext_manager->version_check($md_manager, $force_update, !$force_update);

					$available_extension_meta_data[$name]['S_UP_TO_DATE'] = empty($updates);
					$available_extension_meta_data[$name]['S_VERSIONCHECK'] = true;
					$available_extension_meta_data[$name]['U_VERSIONCHECK_FORCE'] = $this->u_action . '&amp;action=details&amp;versioncheck_force=1&amp;ext_name=' . urlencode($md_manager->get_metadata('name'));
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
			catch (runtime_exception $e)
			{
				$message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
				$this->template->assign_block_vars('not_installed', array(
					'META_DISPLAY_NAME'		=> $this->user->lang('EXTENSION_INVALID_LIST', $name, $message),
					'S_VERSIONCHECK'		=> false,
				));
			}
		}

		uasort($available_extension_meta_data, array($this, 'sort_extension_meta_data_table'));

		foreach ($available_extension_meta_data as $name => $block_vars)
		{
			$block_vars['NAME'] = $name;
			$block_vars['U_DETAILS'] = $this->u_action . '&amp;action=details&amp;ext_name=' . urlencode($name);

			$this->template->assign_block_vars('not_installed', $block_vars);

			$this->output_actions('not_installed', array(
				'ENABLE'		=> $this->u_action . '&amp;action=enable_pre&amp;ext_name=' . urlencode($name),
				'REMOVE' => $this->u_catalog_action . '&amp;action=remove&amp;origin=manager&amp;extension=' . urlencode($block_vars['META_NAME']),
			));
		}
	}

	/**
	* Output actions to a block
	*
	* @param string $block
	* @param array $actions
	*/
	private function output_actions($block, $actions)
	{
		foreach ($actions as $action => $url)
		{
			$this->template->assign_block_vars($block . '.actions', [
				'ACTION'			=> $action,
				'L_ACTION'			=> $this->user->lang('EXTENSION_' . $action),
				'L_ACTION_EXPLAIN'	=> (isset($this->user->lang['EXTENSION_' . $action . '_EXPLAIN'])) ? $this->user->lang('EXTENSION_' . $action . '_EXPLAIN') : '',
				'U_ACTION'			=> $url,
			]);
		}
	}

	/**
	* Sort helper for the table containing the metadata about the extensions.
	*/
	protected function sort_extension_meta_data_table($val1, $val2)
	{
		return strnatcasecmp($val1['META_DISPLAY_NAME'], $val2['META_DISPLAY_NAME']);
	}

	/**
	* Outputs extension metadata into the template
	*
	* @param array $metadata Array with all metadata for the extension
	* @return void
	*/
	public function output_metadata_to_template($metadata)
	{
		$this->template->assign_vars(array(
			'META_NAME'			=> $metadata['name'],
			'META_TYPE'			=> $metadata['type'],
			'META_DESCRIPTION'	=> (isset($metadata['description'])) ? $metadata['description'] : '',
			'META_HOMEPAGE'		=> (isset($metadata['homepage'])) ? $metadata['homepage'] : '',
			'META_VERSION'		=> $metadata['version'],
			'META_TIME'			=> (isset($metadata['time'])) ? $metadata['time'] : '',
			'META_LICENSE'		=> $metadata['license'],

			'META_REQUIRE_PHP'		=> (isset($metadata['require']['php'])) ? $metadata['require']['php'] : '',
			'META_REQUIRE_PHP_FAIL'	=> (isset($metadata['require']['php'])) ? false : true,

			'META_REQUIRE_PHPBB'		=> (isset($metadata['extra']['soft-require']['phpbb/phpbb'])) ? $metadata['extra']['soft-require']['phpbb/phpbb'] : '',
			'META_REQUIRE_PHPBB_FAIL'	=> (isset($metadata['extra']['soft-require']['phpbb/phpbb'])) ? false : true,

			'META_DISPLAY_NAME'	=> (isset($metadata['extra']['display-name'])) ? $metadata['extra']['display-name'] : '',
		));

		foreach ($metadata['authors'] as $author)
		{
			$this->template->assign_block_vars('meta_authors', array(
				'AUTHOR_NAME'		=> $author['name'],
				'AUTHOR_EMAIL'		=> (isset($author['email'])) ? $author['email'] : '',
				'AUTHOR_HOMEPAGE'	=> (isset($author['homepage'])) ? $author['homepage'] : '',
				'AUTHOR_ROLE'		=> (isset($author['role'])) ? $author['role'] : '',
			));
		}
	}

	/**
	* Checks whether the extension can be enabled. Triggers error if not.
	* Error message can be set by the extension.
	*
	* @param \phpbb\extension\extension_interface $extension Extension to check
	*/
	protected function check_is_enableable(\phpbb\extension\extension_interface $extension)
	{
		$message = $extension->is_enableable();
		if ($message !== true)
		{
			if (empty($message))
			{
				$message = $this->user->lang('EXTENSION_NOT_ENABLEABLE');
			}
			else if (is_array($message))
			{
				$message = implode('<br>', $message);
			}

			trigger_error($message . adm_back_link($this->u_action), E_USER_WARNING);
		}
	}
}
