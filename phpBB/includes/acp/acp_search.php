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

class acp_search
{
	var $u_action;
	var $state;
	var $search;

	protected const STATE_SEARCH_TYPE = 0;
	protected const STATE_ACTION = 1;
	protected const STATE_POST_COUNTER = 2;

	function main($id, $mode)
	{
		global $user;

		$user->add_lang('acp/search');

		// For some this may be of help...
		@ini_set('memory_limit', '128M');

		switch ($mode)
		{
			case 'settings':
				$this->settings($id, $mode);
			break;

			case 'index':
				$this->index($id, $mode);
			break;
		}
	}

	function settings($id, $mode)
	{
		global $user, $template, $phpbb_log, $request;
		global $config, $phpbb_admin_path, $phpEx;
		global $phpbb_container;

		$submit = $request->is_set_post('submit');

		if ($submit && !check_link_hash($request->variable('hash', ''), 'acp_search'))
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$search_types = $phpbb_container->get('search.backend_collection');

		$settings = [
			'search_interval'				=> 'float',
			'search_anonymous_interval'		=> 'float',
			'load_search'					=> 'bool',
			'limit_search_load'				=> 'float',
			'min_search_author_chars'		=> 'integer',
			'max_num_search_keywords'		=> 'integer',
			'default_search_return_chars'	=> 'integer',
			'search_store_results'			=> 'integer',
		];

		$search = null;
		$search_options = '';

		foreach ($search_types as $search)
		{
			// Only show available search backends
			if ($search->is_available())
			{
				$name = $search->get_name();
				$type = get_class($search);

				$selected = ($config['search_type'] == $type) ? ' selected="selected"' : '';
				$identifier = substr($type, strrpos($type, '\\') + 1);
				$search_options .= "<option value=\"$type\"$selected data-toggle-setting=\"#search_{$identifier}_settings\">$name</option>";

				$vars = $search->get_acp_options();

				if (!$submit)
				{
					$template->assign_block_vars('backend', array(
						'NAME' => $name,
						'SETTINGS' => $vars['tpl'],
						'IDENTIFIER' => $identifier,
					));
				}
				else if (is_array($vars['config']))
				{
					$settings = array_merge($settings, $vars['config']);
				}
			}
		}
		unset($search);

		$cfg_array = (isset($_REQUEST['config'])) ? $request->variable('config', array('' => ''), true) : array();
		$updated = $request->variable('updated', false);

		foreach ($settings as $config_name => $var_type)
		{
			if (!isset($cfg_array[$config_name]))
			{
				continue;
			}

			// e.g. integer:4:12 (min 4, max 12)
			$var_type = explode(':', $var_type);

			$config_value = $cfg_array[$config_name];
			settype($config_value, $var_type[0]);

			if (isset($var_type[1]))
			{
				$config_value = max($var_type[1], $config_value);
			}

			if (isset($var_type[2]))
			{
				$config_value = min($var_type[2], $config_value);
			}

			// only change config if anything was actually changed
			if ($submit && ($config[$config_name] != $config_value))
			{
				$config->set($config_name, $config_value);
				$updated = true;
			}
		}

		if ($submit)
		{
			$extra_message = '';
			if ($updated)
			{
				$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_CONFIG_SEARCH');
			}

			if (isset($cfg_array['search_type']) && ($cfg_array['search_type'] != $config['search_type']))
			{
				$search_backend_factory = $phpbb_container->get('search.backend_factory');
				$search = $search_backend_factory->get($cfg_array['search_type']);
				if (confirm_box(true))
				{
					// Initialize search backend, if $error is false means that everything is ok
					if (!($error = $search->init()))
					{
						$config->set('search_type', $cfg_array['search_type']);

						if (!$updated)
						{
							$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_CONFIG_SEARCH');
						}
						$extra_message = '<br />' . $user->lang['SWITCHED_SEARCH_BACKEND'] . '<br /><a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", 'i=search&amp;mode=index') . '">&raquo; ' . $user->lang['GO_TO_SEARCH_INDEX'] . '</a>';
					}
					else
					{
						trigger_error($error . adm_back_link($this->u_action), E_USER_WARNING);
					}
				}
				else
				{
					confirm_box(false, $user->lang['CONFIRM_SEARCH_BACKEND'], build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'submit'	=> true,
						'updated'	=> $updated,
						'config'	=> array('search_type' => $cfg_array['search_type']),
					)));
				}
			}

			if ($updated)
			{
				if (method_exists($search, 'config_updated'))
				{
					if ($search->config_updated())
					{
						trigger_error($error . adm_back_link($this->u_action), E_USER_WARNING);
					}
				}
			}

			trigger_error($user->lang['CONFIG_UPDATED'] . $extra_message . adm_back_link($this->u_action));
		}
		unset($cfg_array);

		$this->tpl_name = 'acp_search';
		$this->page_title = 'ACP_SEARCH_SETTINGS';

		$template->assign_vars([
			'DEFAULT_SEARCH_RETURN_CHARS'	=> (int) $config['default_search_return_chars'],
			'LIMIT_SEARCH_LOAD'				=> (float) $config['limit_search_load'],
			'MIN_SEARCH_AUTHOR_CHARS'		=> (int) $config['min_search_author_chars'],
			'SEARCH_INTERVAL'				=> (float) $config['search_interval'],
			'SEARCH_GUEST_INTERVAL'			=> (float) $config['search_anonymous_interval'],
			'SEARCH_STORE_RESULTS'			=> (int) $config['search_store_results'],
			'MAX_NUM_SEARCH_KEYWORDS'		=> (int) $config['max_num_search_keywords'],

			'S_SEARCH_TYPES'		=> $search_options,
			'S_YES_SEARCH'			=> (bool) $config['load_search'],
			'S_SETTINGS'			=> true,

			'U_ACTION'				=> $this->u_action . '&amp;hash=' . generate_link_hash('acp_search'),
		]);
	}

	function index($id, $mode)
	{
		global $user, $template, $phpbb_log, $request;
		global $config, $phpbb_admin_path, $phpEx, $phpbb_container;

		$action = $request->variable('action', '');
		$this->state = explode(',', $config['search_indexing_state']);

		if ($request->is_set_post('cancel'))
		{
			$action = '';
			$this->state = array();
			$this->save_state();
		}
		$submit = $request->is_set_post('submit');

		if (!check_link_hash($request->variable('hash', ''), 'acp_search') && in_array($action, array('create', 'delete')))
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		if ($action)
		{
			switch ($action)
			{
				case 'progress_bar':
					$type = $request->variable('type', '');
					$this->display_progress_bar($type);
				break;

				case 'delete':
					$this->state[self::STATE_ACTION] = 'delete';
				break;

				case 'create':
					$this->state[self::STATE_ACTION] = 'create';
				break;

				default:
					trigger_error('NO_ACTION', E_USER_ERROR);
			}

			if (empty($this->state[self::STATE_SEARCH_TYPE]))
			{
				$this->state[self::STATE_SEARCH_TYPE] = $request->variable('search_type', '');
			}

			$search_backend_factory = $phpbb_container->get('search.backend_factory');
			$this->search = $search_backend_factory->get($this->state[self::STATE_SEARCH_TYPE]);

			$name = $this->search->get_name();

			$action = &$this->state[1];

			$this->save_state();

			switch ($action)
			{
				case 'delete':
					try
					{
						$this->state[self::STATE_POST_COUNTER] = $this->state[self::STATE_POST_COUNTER] ?? 0;
						if ($status = $this->search->delete_index($this->state[self::STATE_POST_COUNTER])) // Status is not null, so deleting is in progress....
						{
							// save the current state
							$this->save_state();

							$u_action = append_sid("{$phpbb_admin_path}index.$phpEx", "i=$id&mode=$mode&action=delete&hash=" . generate_link_hash('acp_search'), false);
							meta_refresh(1, $u_action);
							trigger_error($user->lang('SEARCH_INDEX_DELETE_REDIRECT', (int) $status['row_count'], $status['post_counter']) . $user->lang('SEARCH_INDEX_DELETE_REDIRECT_RATE', $status['rows_per_second']));
						}
					}
					catch (Exception $e)
					{
						$this->state = [];
						$this->save_state();
						trigger_error($e->getMessage() . adm_back_link($this->u_action) . $this->close_popup_js(), E_USER_WARNING);
					}

					$this->search->tidy();

					$this->state = [];
					$this->save_state();

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_SEARCH_INDEX_REMOVED', false, array($name));
					trigger_error($user->lang['SEARCH_INDEX_REMOVED'] . adm_back_link($this->u_action) . $this->close_popup_js());
				break;

				case 'create':
					try
					{
						$this->state[self::STATE_POST_COUNTER] = $this->state[self::STATE_POST_COUNTER] ?? 0;
						if ($status = $this->search->create_index($this->state[self::STATE_POST_COUNTER])) // Status is not null, so indexing is in progress....
						{
							// save the current state
							$this->save_state();

							$u_action = append_sid("{$phpbb_admin_path}index.$phpEx", "i=$id&mode=$mode&action=create&hash=" . generate_link_hash('acp_search'), false);
							meta_refresh(1, $u_action);
							trigger_error($user->lang('SEARCH_INDEX_CREATE_REDIRECT', (int) $status['row_count'], $status['post_counter']) . $user->lang('SEARCH_INDEX_CREATE_REDIRECT_RATE', $status['rows_per_second']));
						}
					}
					catch (Exception $e)
					{
						// Error executing create_index
						$this->state = [];
						$this->save_state();
						trigger_error($e->getMessage() . adm_back_link($this->u_action) . $this->close_popup_js(), E_USER_WARNING);
					}

					// Indexing have finished

					$this->search->tidy();

					$this->state = [];
					$this->save_state();

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_SEARCH_INDEX_CREATED', false, array($name));
					trigger_error($user->lang['SEARCH_INDEX_CREATED'] . adm_back_link($this->u_action) . $this->close_popup_js());
				break;
			}
		}

		$search_types = $phpbb_container->get('search.backend_collection');

		$search = null;

		foreach ($search_types as $search)
		{
			$type = get_class($search);

			$name = $search->get_name();

			$data = array();
			if (method_exists($search, 'index_stats'))
			{
				$data = $search->index_stats();
			}

			$statistics = array();
			foreach ($data as $statistic => $value)
			{
				$n = count($statistics);
				if ($n && count($statistics[$n - 1]) < 3)
				{
					$statistics[$n - 1] += array('statistic_2' => $statistic, 'value_2' => $value);
				}
				else
				{
					$statistics[] = array('statistic_1' => $statistic, 'value_1' => $value);
				}
			}

			$template->assign_block_vars('backend', array(
				'L_NAME'			=> $name,
				'NAME'				=> $type,

				'S_ACTIVE'			=> ($type == $config['search_type']) ? true : false,
				'S_HIDDEN_FIELDS'	=> build_hidden_fields(array('search_type' => $type)),
				'S_INDEXED'			=> (bool) $search->index_created(),
				'S_STATS'			=> (bool) count($statistics))
			);

			foreach ($statistics as $statistic)
			{
				$template->assign_block_vars('backend.data', array(
					'STATISTIC_1'	=> $statistic['statistic_1'],
					'VALUE_1'		=> $statistic['value_1'],
					'STATISTIC_2'	=> (isset($statistic['statistic_2'])) ? $statistic['statistic_2'] : '',
					'VALUE_2'		=> (isset($statistic['value_2'])) ? $statistic['value_2'] : '')
				);
			}
		}
		unset($search);
		unset($statistics);
		unset($data);

		$this->tpl_name = 'acp_search';
		$this->page_title = 'ACP_SEARCH_INDEX';

		$template->assign_vars(array(
			'S_INDEX'				=> true,
			'U_ACTION'				=> $this->u_action . '&amp;hash=' . generate_link_hash('acp_search'),
			'U_PROGRESS_BAR'		=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=$id&amp;mode=$mode&amp;action=progress_bar"),
			'UA_PROGRESS_BAR'		=> addslashes(append_sid("{$phpbb_admin_path}index.$phpEx", "i=$id&amp;mode=$mode&amp;action=progress_bar")),
		));

		if (isset($this->state[self::STATE_ACTION]))
		{
			$template->assign_vars(array(
				'S_CONTINUE_INDEXING'	=> $this->state[1],
				'U_CONTINUE_INDEXING'	=> $this->u_action . '&amp;action=' . $this->state[self::STATE_ACTION] . '&amp;hash=' . generate_link_hash('acp_search'),
				'L_CONTINUE'			=> ($this->state[self::STATE_ACTION] == 'create') ? $user->lang['CONTINUE_INDEXING'] : $user->lang['CONTINUE_DELETING_INDEX'],
				'L_CONTINUE_EXPLAIN'	=> ($this->state[self::STATE_ACTION] == 'create') ? $user->lang['CONTINUE_INDEXING_EXPLAIN'] : $user->lang['CONTINUE_DELETING_INDEX_EXPLAIN'])
			);
		}
	}

	function display_progress_bar($type)
	{
		global $template, $user;

		$l_type = ($type == 'create') ? 'INDEXING_IN_PROGRESS' : 'DELETING_INDEX_IN_PROGRESS';

		adm_page_header($user->lang[$l_type]);

		$template->set_filenames(array(
			'body'	=> 'progress_bar.html')
		);

		$template->assign_vars(array(
			'L_PROGRESS'			=> $user->lang[$l_type],
			'L_PROGRESS_EXPLAIN'	=> $user->lang[$l_type . '_EXPLAIN'])
		);

		adm_page_footer();
	}

	function close_popup_js()
	{
		return "<script type=\"text/javascript\">\n" .
			"// <![CDATA[\n" .
			"	close_waitscreen = 1;\n" .
			"// ]]>\n" .
			"</script>\n";
	}

	function save_state($state = false)
	{
		global $config;

		if ($state)
		{
			$this->state = $state;
		}

		ksort($this->state);

		$config->set('search_indexing_state', implode(',', $this->state), true);
	}
}
