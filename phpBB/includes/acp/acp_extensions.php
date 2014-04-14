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

	function main()
	{
		// Start the page
		global $config, $user, $template, $request, $phpbb_extension_manager, $db, $phpbb_root_path, $phpEx, $phpbb_log, $cache;

		$this->db = $db;
		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
		$this->cache = $cache;
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
			$md_manager = new \phpbb\extension\metadata_manager($ext_name, $config, $phpbb_extension_manager, $template, $phpbb_root_path);

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
			case 'list':
			default:
				$this->list_enabled_exts($phpbb_extension_manager);
				$this->list_disabled_exts($phpbb_extension_manager);
				$this->list_available_exts($phpbb_extension_manager);

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

				if ($phpbb_extension_manager->enabled($ext_name))
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

				if ($phpbb_extension_manager->enabled($ext_name))
				{
					redirect($this->u_action);
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
				if (!$phpbb_extension_manager->enabled($ext_name))
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
				if (!$phpbb_extension_manager->enabled($ext_name))
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
				if ($phpbb_extension_manager->enabled($ext_name))
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
				if ($phpbb_extension_manager->enabled($ext_name))
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
					$infos = array();
					$this->version_check($md_manager, $infos, $request->variable('versioncheck_force', false));
					$template->assign_vars($infos);
				}
				catch (\RuntimeException $e)
				{
					$template->assign_vars(array(
						'S_VERSIONCHECK_FAIL'		=> true,
						'VERSIONCHECK_FAIL_REASON'	=> ($e->getMessage() !== $user->lang('VERSIONCHECK_FAIL')) ? $e->getMessage() : '',
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

				$this->version_check($md_manager, $enabled_extension_meta_data[$name]);
			}
			catch(\phpbb\extension\exception $e)
			{
				$this->template->assign_block_vars('disabled', array(
					'META_DISPLAY_NAME'		=> $this->user->lang('EXTENSION_INVALID_LIST', $name, $e),
					'S_VERSIONCHECK'		=> false,
				));
			}
		}

		uasort($enabled_extension_meta_data, array('self', 'sort_extension_meta_data_table'));

		foreach ($enabled_extension_meta_data as $name => $infos)
		{
			$values = $infos;
			$values['U_DETAILS'] = $this->u_action . '&amp;action=details&amp;ext_name=' . urlencode($name);
			
			$this->template->assign_block_vars('enabled', $values); 

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

				$this->version_check($md_manager, $disabled_extension_meta_data[$name]);
			}
			catch(\phpbb\extension\exception $e)
			{
				$this->template->assign_block_vars('disabled', array(
					'META_DISPLAY_NAME'		=> $this->user->lang('EXTENSION_INVALID_LIST', $name, $e),
					'S_VERSIONCHECK'		=> false,
				));
			}
		}

		uasort($disabled_extension_meta_data, array('self', 'sort_extension_meta_data_table'));

		foreach ($disabled_extension_meta_data as $name => $infos)
		{
			$values = $infos;
			$values['U_DETAILS'] = $this->u_action . '&amp;action=details&amp;ext_name=' . urlencode($name);
			
			$this->template->assign_block_vars('disabled', $values); 

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

				$this->version_check($md_manager, $available_extension_meta_data[$name]);
			}
			catch(\phpbb\extension\exception $e)
			{
				$this->template->assign_block_vars('disabled', array(
					'META_DISPLAY_NAME'		=> $this->user->lang('EXTENSION_INVALID_LIST', $name, $e),
					'S_VERSIONCHECK'		=> false,
				));
			}
		}

		uasort($available_extension_meta_data, array('self', 'sort_extension_meta_data_table'));

		foreach ($available_extension_meta_data as $name => $infos)
		{
			$values = $infos;
			$values['U_DETAILS'] = $this->u_action . '&amp;action=details&amp;ext_name=' . urlencode($name);
			
			$this->template->assign_block_vars('disabled', $values); 

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
	 * Check the version and dump to the template
	 *
	 * @param \phpbb\extension\metadata_manager $md_manager The metadata manager for the version to check.
	 * @param array $array_dest The array to bind to the template.
	 * @param bool $force Ignores cached data. Default to false.
	 */
	private function version_check(\phpbb\extension\metadata_manager $md_manager, &$array_dest, $force = false) 
	{
		$meta = $md_manager->get_metadata('all');

		if (! isset($meta['extra']['version-check']))
		{
			throw new \RuntimeException($this->user->lang('NO_VERSIONCHECK'));
		}
		
		$version_helper = new \phpbb\extension\version_helper($this->cache, $this->user);
		$version_helper->set_metadata($meta);

		try
		{
			$version_helper->get_version($force);
			$version_compare = $version_helper->is_uptodate();

			$array_dest = array_merge($array_dest, array(
				'S_VERSIONCHECK'		=> true,
				'S_UP_TO_DATE'			=> $version_compare,
				'LATEST_VERSION'		=> $version_helper->get_latest_version(),
				'LATEST_DOWNLOAD'		=> $version_helper->get_latest_download_link(),
				'LATEST_ANNOUNCEMENT'	=> $version_helper->get_latest_announcement_link(),
				'UP_TO_DATE_MSG'		=> $this->user->lang($version_compare ? 'UP_TO_DATE' : 'NOT_UP_TO_DATE', $md_manager->get_metadata('display-name')),
				'U_VERSIONCHECK_FORCE' => $this->u_action . '&amp;action=details&amp;versioncheck_force=1&amp;ext_name=' . urlencode($md_manager->get_metadata('name')),
			));
		}
		catch(\RuntimeException $e)
		{
			$array_dest['S_VERSIONCHECK'] = false;
		}
	}

	/**
	 * Sort helper for the table containing the metadata about the extensions.
	 */
	static private function sort_extension_meta_data_table($val1, $val2)
	{
		return strnatcasecmp($val1['META_DISPLAY_NAME'], $val2['META_DISPLAY_NAME']);
	}
}
