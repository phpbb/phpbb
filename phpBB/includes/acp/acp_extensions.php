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

class acp_extensions
{
	var $u_action;

	private $db;
	private $config;
	private $template;
	private $user;
	private $cache;
	private $log;
	private $request;

	function main()
	{
		// Start the page
		global $config, $user, $template, $request, $phpbb_extension_manager, $db, $phpbb_root_path, $phpEx, $phpbb_log, $cache;

		$this->db = $db;
		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
		$this->cache = $cache;
		$this->request = $request;
		$this->log = $phpbb_log;

		$user->add_lang(array('install', 'acp/extensions', 'migrator'));

		$this->page_title = 'ACP_EXTENSIONS';

		$action = $request->variable('action', 'list');
		$ext_name = $request->variable('ext_name', '');

		// What is a safe limit of execution time? Half the max execution time should be safe.
		$safe_time_limit = (ini_get('max_execution_time') / 2);
		$start_time = time();

		// Cancel action
		if ($request->is_set_post('cancel'))
		{
			$action = 'list';
			$ext_name = '';
		}

		if (in_array($action, array('enable', 'disable', 'delete_data')) && !check_link_hash($request->variable('hash', ''), $action . '.' . $ext_name))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
		}

		// If they've specified an extension, let's load the metadata manager and validate it.
		if ($ext_name)
		{
			$md_manager = new \phpbb\extension\metadata_manager($ext_name, $config, $phpbb_extension_manager, $template, $user, $phpbb_root_path);

			try
			{
				$md_manager->get_metadata('all');
			}
			catch(\phpbb\extension\exception $e)
			{
				trigger_error($e, E_USER_WARNING);
			}
		}

		// What are we doing?
		switch ($action)
		{
			case 'set_config_version_check_force_unstable':
				$force_unstable = $this->request->variable('force_unstable', false);

				if ($force_unstable)
				{
					$s_hidden_fields = build_hidden_fields(array(
						'force_unstable'	=> $force_unstable,
					));

					confirm_box(false, $user->lang('EXTENSION_FORCE_UNSTABLE_CONFIRM'), $s_hidden_fields);
				}
				else
				{
					$config->set('extension_force_unstable', false);
					trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
				}
				break;

			case 'list':
			default:
				if (confirm_box(true))
				{
					$config->set('extension_force_unstable', true);
					trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
				}

				$this->list_enabled_exts($phpbb_extension_manager);
				$this->list_disabled_exts($phpbb_extension_manager);
				$this->list_available_exts($phpbb_extension_manager);

				$this->template->assign_vars(array(
					'U_VERSIONCHECK_FORCE' 	=> $this->u_action . '&amp;action=list&amp;versioncheck_force=1',
					'FORCE_UNSTABLE'		=> $config['extension_force_unstable'],
					'U_ACTION' 				=> $this->u_action,
				));

				add_form_key('version_check_settings');

				$this->tpl_name = 'acp_ext_list';
			break;

			case 'enable_pre':
				if (!$md_manager->validate_dir())
				{
					trigger_error($user->lang['EXTENSION_DIR_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$md_manager->validate_enable())
				{
					trigger_error($user->lang['EXTENSION_NOT_AVAILABLE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$extension = $phpbb_extension_manager->get_extension($ext_name);
				if (!$extension->is_enableable())
				{
					trigger_error($user->lang['EXTENSION_NOT_ENABLEABLE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if ($phpbb_extension_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}

				$this->tpl_name = 'acp_ext_enable';

				$template->assign_vars(array(
					'PRE'				=> true,
					'L_CONFIRM_MESSAGE'	=> $this->user->lang('EXTENSION_ENABLE_CONFIRM', $md_manager->get_metadata('display-name')),
					'U_ENABLE'			=> $this->u_action . '&amp;action=enable&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('enable.' . $ext_name),
				));
			break;

			case 'enable':
				if (!$md_manager->validate_dir())
				{
					trigger_error($user->lang['EXTENSION_DIR_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$md_manager->validate_enable())
				{
					trigger_error($user->lang['EXTENSION_NOT_AVAILABLE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$extension = $phpbb_extension_manager->get_extension($ext_name);
				if (!$extension->is_enableable())
				{
					trigger_error($user->lang['EXTENSION_NOT_ENABLEABLE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				try
				{
					while ($phpbb_extension_manager->enable_step($ext_name))
					{
						// Are we approaching the time limit? If so we want to pause the update and continue after refreshing
						if ((time() - $start_time) >= $safe_time_limit)
						{
							$template->assign_var('S_NEXT_STEP', true);

							meta_refresh(0, $this->u_action . '&amp;action=enable&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('enable.' . $ext_name));
						}
					}
					$this->log->add('admin', $user->data['user_id'], $user->ip, 'LOG_EXT_ENABLE', time(), array($ext_name));
				}
				catch (\phpbb\db\migration\exception $e)
				{
					$template->assign_var('MIGRATOR_ERROR', $e->getLocalisedMessage($user));
				}

				$this->tpl_name = 'acp_ext_enable';

				$template->assign_vars(array(
					'U_RETURN'		=> $this->u_action . '&amp;action=list',
				));
			break;

			case 'disable_pre':
				if (!$phpbb_extension_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}

				$this->tpl_name = 'acp_ext_disable';

				$template->assign_vars(array(
					'PRE'				=> true,
					'L_CONFIRM_MESSAGE'	=> $this->user->lang('EXTENSION_DISABLE_CONFIRM', $md_manager->get_metadata('display-name')),
					'U_DISABLE'			=> $this->u_action . '&amp;action=disable&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('disable.' . $ext_name),
				));
			break;

			case 'disable':
				if (!$phpbb_extension_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}

				while ($phpbb_extension_manager->disable_step($ext_name))
				{
					// Are we approaching the time limit? If so we want to pause the update and continue after refreshing
					if ((time() - $start_time) >= $safe_time_limit)
					{
						$template->assign_var('S_NEXT_STEP', true);

						meta_refresh(0, $this->u_action . '&amp;action=disable&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('disable.' . $ext_name));
					}
				}
				$this->log->add('admin', $user->data['user_id'], $user->ip, 'LOG_EXT_DISABLE', time(), array($ext_name));

				$this->tpl_name = 'acp_ext_disable';

				$template->assign_vars(array(
					'U_RETURN'	=> $this->u_action . '&amp;action=list',
				));
			break;

			case 'delete_data_pre':
				if ($phpbb_extension_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}
				$this->tpl_name = 'acp_ext_delete_data';

				$template->assign_vars(array(
					'PRE'				=> true,
					'L_CONFIRM_MESSAGE'	=> $this->user->lang('EXTENSION_DELETE_DATA_CONFIRM', $md_manager->get_metadata('display-name')),
					'U_PURGE'			=> $this->u_action . '&amp;action=delete_data&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('delete_data.' . $ext_name),
				));
			break;

			case 'delete_data':
				if ($phpbb_extension_manager->is_enabled($ext_name))
				{
					redirect($this->u_action);
				}

				try
				{
					while ($phpbb_extension_manager->purge_step($ext_name))
					{
						// Are we approaching the time limit? If so we want to pause the update and continue after refreshing
						if ((time() - $start_time) >= $safe_time_limit)
						{
							$template->assign_var('S_NEXT_STEP', true);

							meta_refresh(0, $this->u_action . '&amp;action=delete_data&amp;ext_name=' . urlencode($ext_name) . '&amp;hash=' . generate_link_hash('delete_data.' . $ext_name));
						}
					}
					$this->log->add('admin', $user->data['user_id'], $user->ip, 'LOG_EXT_PURGE', time(), array($ext_name));
				}
				catch (\phpbb\db\migration\exception $e)
				{
					$template->assign_var('MIGRATOR_ERROR', $e->getLocalisedMessage($user));
				}

				$this->tpl_name = 'acp_ext_delete_data';

				$template->assign_vars(array(
					'U_RETURN'	=> $this->u_action . '&amp;action=list',
				));
			break;

			case 'details':
				// Output it to the template
				$md_manager->output_template_data();

				try
				{
					$updates_available = $this->version_check($md_manager, $request->variable('versioncheck_force', false));

					$template->assign_vars(array(
						'S_UP_TO_DATE'		=> empty($updates_available),
						'S_VERSIONCHECK'	=> true,
						'UP_TO_DATE_MSG'	=> $this->user->lang(empty($updates_available) ? 'UP_TO_DATE' : 'NOT_UP_TO_DATE', $md_manager->get_metadata('display-name')),
					));

					foreach ($updates_available as $branch => $version_data)
					{
						$template->assign_block_vars('updates_available', $version_data);
					}
				}
				catch (\RuntimeException $e)
				{
					$template->assign_vars(array(
						'S_VERSIONCHECK_STATUS'			=> $e->getCode(),
						'VERSIONCHECK_FAIL_REASON'		=> ($e->getMessage() !== $user->lang('VERSIONCHECK_FAIL')) ? $e->getMessage() : '',
					));
				}

				$template->assign_vars(array(
					'U_BACK'				=> $this->u_action . '&amp;action=list',
					'U_VERSIONCHECK_FORCE'	=> $this->u_action . '&amp;action=details&amp;versioncheck_force=1&amp;ext_name=' . urlencode($md_manager->get_metadata('name')),
				));

				$this->tpl_name = 'acp_ext_details';
			break;
		}
	}

	/**
	* Lists all the enabled extensions and dumps to the template
	*
	* @param  $phpbb_extension_manager     An instance of the extension manager
	* @return null
	*/
	public function list_enabled_exts(\phpbb\extension\manager $phpbb_extension_manager)
	{
		$enabled_extension_meta_data = array();

		foreach ($phpbb_extension_manager->all_enabled() as $name => $location)
		{
			$md_manager = $phpbb_extension_manager->create_extension_metadata_manager($name, $this->template);

			try
			{
				$meta = $md_manager->get_metadata('all');
				$enabled_extension_meta_data[$name] = array(
					'META_DISPLAY_NAME' => $md_manager->get_metadata('display-name'),
					'META_VERSION' => $meta['version'],
				);

				$force_update = $this->request->variable('versioncheck_force', false);
				$updates = $this->version_check($md_manager, $force_update, !$force_update);

				$enabled_extension_meta_data[$name]['S_UP_TO_DATE'] = empty($updates);
				$enabled_extension_meta_data[$name]['S_VERSIONCHECK'] = true;
				$enabled_extension_meta_data[$name]['U_VERSIONCHECK_FORCE'] = $this->u_action . '&amp;action=details&amp;versioncheck_force=1&amp;ext_name=' . urlencode($md_manager->get_metadata('name'));
			}
			catch(\phpbb\extension\exception $e)
			{
				$this->template->assign_block_vars('disabled', array(
					'META_DISPLAY_NAME'		=> $this->user->lang('EXTENSION_INVALID_LIST', $name, $e),
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
			$block_vars['U_DETAILS'] = $this->u_action . '&amp;action=details&amp;ext_name=' . urlencode($name);

			$this->template->assign_block_vars('enabled', $block_vars);

			$this->output_actions('enabled', array(
				'DISABLE'		=> $this->u_action . '&amp;action=disable_pre&amp;ext_name=' . urlencode($name),
			));
		}
	}

	/**
	* Lists all the disabled extensions and dumps to the template
	*
	* @param  $phpbb_extension_manager     An instance of the extension manager
	* @return null
	*/
	public function list_disabled_exts(\phpbb\extension\manager $phpbb_extension_manager)
	{
		$disabled_extension_meta_data = array();

		foreach ($phpbb_extension_manager->all_disabled() as $name => $location)
		{
			$md_manager = $phpbb_extension_manager->create_extension_metadata_manager($name, $this->template);

			try
			{
				$meta = $md_manager->get_metadata('all');
				$disabled_extension_meta_data[$name] = array(
					'META_DISPLAY_NAME' => $md_manager->get_metadata('display-name'),
					'META_VERSION' => $meta['version'],
				);

				$force_update = $this->request->variable('versioncheck_force', false);
				$updates = $this->version_check($md_manager, $force_update, !$force_update);

				$disabled_extension_meta_data[$name]['S_UP_TO_DATE'] = empty($updates);
				$disabled_extension_meta_data[$name]['S_VERSIONCHECK'] = true;
				$disabled_extension_meta_data[$name]['U_VERSIONCHECK_FORCE'] = $this->u_action . '&amp;action=details&amp;versioncheck_force=1&amp;ext_name=' . urlencode($md_manager->get_metadata('name'));
			}
			catch(\phpbb\extension\exception $e)
			{
				$this->template->assign_block_vars('disabled', array(
					'META_DISPLAY_NAME'		=> $this->user->lang('EXTENSION_INVALID_LIST', $name, $e),
					'S_VERSIONCHECK'		=> false,
				));
			}
			catch (\RuntimeException $e)
			{
				$disabeld_extension_meta_data[$name]['S_VERSIONCHECK'] = false;
			}
		}

		uasort($disabled_extension_meta_data, array($this, 'sort_extension_meta_data_table'));

		foreach ($disabled_extension_meta_data as $name => $block_vars)
		{
			$block_vars['U_DETAILS'] = $this->u_action . '&amp;action=details&amp;ext_name=' . urlencode($name);

			$this->template->assign_block_vars('disabled', $block_vars);

			$this->output_actions('disabled', array(
				'ENABLE'		=> $this->u_action . '&amp;action=enable_pre&amp;ext_name=' . urlencode($name),
				'DELETE_DATA'	=> $this->u_action . '&amp;action=delete_data_pre&amp;ext_name=' . urlencode($name),
			));
		}
	}

	/**
	* Lists all the available extensions and dumps to the template
	*
	* @param  $phpbb_extension_manager     An instance of the extension manager
	* @return null
	*/
	public function list_available_exts(\phpbb\extension\manager $phpbb_extension_manager)
	{
		$uninstalled = array_diff_key($phpbb_extension_manager->all_available(), $phpbb_extension_manager->all_configured());

		$available_extension_meta_data = array();

		foreach ($uninstalled as $name => $location)
		{
			$md_manager = $phpbb_extension_manager->create_extension_metadata_manager($name, $this->template);

			try
			{
				$meta = $md_manager->get_metadata('all');
				$available_extension_meta_data[$name] = array(
					'META_DISPLAY_NAME' => $md_manager->get_metadata('display-name'),
					'META_VERSION' => $meta['version'],
				);

				$force_update = $this->request->variable('versioncheck_force', false);
				$updates = $this->version_check($md_manager, $force_update, !$force_update);

				$available_extension_meta_data[$name]['S_UP_TO_DATE'] = empty($updates);
				$available_extension_meta_data[$name]['S_VERSIONCHECK'] = true;
				$available_extension_meta_data[$name]['U_VERSIONCHECK_FORCE'] = $this->u_action . '&amp;action=details&amp;versioncheck_force=1&amp;ext_name=' . urlencode($md_manager->get_metadata('name'));
			}
			catch(\phpbb\extension\exception $e)
			{
				$this->template->assign_block_vars('disabled', array(
					'META_DISPLAY_NAME'		=> $this->user->lang('EXTENSION_INVALID_LIST', $name, $e),
					'S_VERSIONCHECK'		=> false,
				));
			}
			catch (\RuntimeException $e)
			{
				$available_extension_meta_data[$name]['S_VERSIONCHECK'] = false;
			}
		}

		uasort($available_extension_meta_data, array($this, 'sort_extension_meta_data_table'));

		foreach ($available_extension_meta_data as $name => $block_vars)
		{
			$block_vars['U_DETAILS'] = $this->u_action . '&amp;action=details&amp;ext_name=' . urlencode($name);

			$this->template->assign_block_vars('disabled', $block_vars);

			$this->output_actions('disabled', array(
				'ENABLE'		=> $this->u_action . '&amp;action=enable_pre&amp;ext_name=' . urlencode($name),
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
		foreach ($actions as $lang => $url)
		{
			$this->template->assign_block_vars($block . '.actions', array(
				'L_ACTION'			=> $this->user->lang('EXTENSION_' . $lang),
				'L_ACTION_EXPLAIN'	=> (isset($this->user->lang['EXTENSION_' . $lang . '_EXPLAIN'])) ? $this->user->lang('EXTENSION_' . $lang . '_EXPLAIN') : '',
				'U_ACTION'			=> $url,
			));
		}
	}

	/**
	* Check the version and return the available updates.
	*
	* @param \phpbb\extension\metadata_manager $md_manager The metadata manager for the version to check.
	* @param bool $force_update Ignores cached data. Defaults to false.
	* @param bool $force_cache Force the use of the cache. Override $force_update.
	* @return string
	* @throws RuntimeException
	*/
	protected function version_check(\phpbb\extension\metadata_manager $md_manager, $force_update = false, $force_cache = false)
	{
		$meta = $md_manager->get_metadata('all');

		if (!isset($meta['extra']['version-check']))
		{
			throw new \RuntimeException($this->user->lang('NO_VERSIONCHECK'), 1);
		}

		$version_check = $meta['extra']['version-check'];

		$version_helper = new \phpbb\version_helper($this->cache, $this->config, new \phpbb\file_downloader(), $this->user);
		$version_helper->set_current_version($meta['version']);
		$version_helper->set_file_location($version_check['host'], $version_check['directory'], $version_check['filename']);
		$version_helper->force_stability($this->config['extension_force_unstable'] ? 'unstable' : null);

		return $updates = $version_helper->get_suggested_updates($force_update, $force_cache);
	}

	/**
	* Sort helper for the table containing the metadata about the extensions.
	*/
	protected function sort_extension_meta_data_table($val1, $val2)
	{
		return strnatcasecmp($val1['META_DISPLAY_NAME'], $val2['META_DISPLAY_NAME']);
	}
}
