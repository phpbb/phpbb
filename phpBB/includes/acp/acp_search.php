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
	var $max_post_id;
	var $batch_size = 100;

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

		$submit = $request->is_set_post('submit');

		if ($submit && !check_link_hash($request->variable('hash', ''), 'acp_search'))
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$search_types = $this->get_search_types();

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
		$error = false;
		$search_options = '';
		foreach ($search_types as $type)
		{
			if ($this->init_search($type, $search, $error))
			{
				continue;
			}

			$name = $search->get_name();

			$selected = ($config['search_type'] == $type) ? ' selected="selected"' : '';
			$identifier = substr($type, strrpos($type, '\\') + 1);
			$search_options .= "<option value=\"$type\"$selected data-toggle-setting=\"#search_{$identifier}_settings\">$name</option>";

			if (method_exists($search, 'acp'))
			{
				$vars = $search->acp();

				if (!$submit)
				{
					$template->assign_block_vars('backend', array(
						'NAME'			=> $name,
						'SETTINGS'		=> $vars['tpl'],
						'IDENTIFIER'	=> $identifier,
					));
				}
				else if (is_array($vars['config']))
				{
					$settings = array_merge($settings, $vars['config']);
				}
			}
		}
		unset($search);
		unset($error);

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

			if (isset($cfg_array['search_type']) && in_array($cfg_array['search_type'], $search_types, true) && ($cfg_array['search_type'] != $config['search_type']))
			{
				$search = null;
				$error = false;

				if (!$this->init_search($cfg_array['search_type'], $search, $error))
				{
					if (confirm_box(true))
					{
						if (!method_exists($search, 'init') || !($error = $search->init()))
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
				else
				{
					trigger_error($error . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}

			$search = null;
			$error = false;
			if (!$this->init_search($config['search_type'], $search, $error))
			{
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
			}
			else
			{
				trigger_error($error . adm_back_link($this->u_action), E_USER_WARNING);
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
		global $db, $language, $user, $template, $phpbb_log, $request;
		global $config, $phpbb_admin_path, $phpEx;

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
				case 'delete':
					$this->state[1] = 'delete';
				break;

				case 'create':
					$this->state[1] = 'create';
				break;

				default:
					trigger_error('NO_ACTION', E_USER_ERROR);
				break;
			}

			if (empty($this->state[0]))
			{
				$this->state[0] = $request->variable('search_type', '');
			}

			$this->search = null;
			$error = false;
			if ($this->init_search($this->state[0], $this->search, $error))
			{
				trigger_error($error . adm_back_link($this->u_action), E_USER_WARNING);
			}
			$name = $this->search->get_name();

			$action = &$this->state[1];

			$this->max_post_id = $this->get_max_post_id();

			$post_counter = (isset($this->state[2])) ? $this->state[2] : 0;
			$this->state[2] = &$post_counter;
			$this->save_state();

			switch ($action)
			{
				case 'delete':
					if (method_exists($this->search, 'delete_index'))
					{
						// pass a reference to myself so the $search object can make use of save_state() and attributes
						if ($error = $this->search->delete_index($this, append_sid("{$phpbb_admin_path}index.$phpEx", "i=$id&mode=$mode&action=delete&hash=" . generate_link_hash('acp_search'), false)))
						{
							$this->state = array('');
							$this->save_state();
							trigger_error($error . adm_back_link($this->u_action), E_USER_WARNING);
						}
					}
					else if ($submit)
					{
						meta_refresh(1, append_sid($this->u_action . '&amp;action=delete&amp;skip_rows=' . $post_counter . '&amp;hash=' . generate_link_hash('acp_search')));
						$template->assign_vars([
							'S_INDEX_PROGRESS'		=> true,
							'INDEXING_TITLE'		=> $language->lang('DELETING_INDEX_IN_PROGRESS'),
							'INDEXING_EXPLAIN'		=> $language->lang('DELETING_INDEX_IN_PROGRESS_EXPLAIN'),
							'INDEXING_PROGRESS_BAR'	=> $this->get_post_index_progress($post_counter),
						]);

						$this->tpl_name = 'acp_search';
						$this->page_title = 'ACP_SEARCH_INDEX';

						return;
					}
					else
					{
						$starttime = microtime(true);
						$row_count = 0;
						while (still_on_time() && $post_counter < $this->max_post_id)
						{
							$sql = 'SELECT post_id, poster_id, forum_id
								FROM ' . POSTS_TABLE . '
								WHERE post_id > ' . (int) $post_counter . '
								ORDER BY post_id ASC';
							$result = $db->sql_query_limit($sql, $this->batch_size);

							$ids = $posters = $forum_ids = array();
							while ($row = $db->sql_fetchrow($result))
							{
								$ids[] = $row['post_id'];
								$posters[] = $row['poster_id'];
								$forum_ids[] = $row['forum_id'];
							}
							$db->sql_freeresult($result);
							$row_count += count($ids);

							if (count($ids))
							{
								$this->search->index_remove($ids, $posters, $forum_ids);
								$post_counter = $ids[count($ids) - 1];
							}
						}
						// save the current state
						$this->save_state();

						if ($post_counter < $this->max_post_id)
						{
							$totaltime = microtime(true) - $starttime;
							$rows_per_second = $row_count / $totaltime;
							meta_refresh(1, append_sid($this->u_action . '&amp;action=delete&amp;skip_rows=' . $post_counter . '&amp;hash=' . generate_link_hash('acp_search')));

							$template->assign_vars([
								'S_INDEX_PROGRESS'		=> true,
								'INDEXING_TITLE'		=> $language->lang('DELETING_INDEX_IN_PROGRESS'),
								'INDEXING_EXPLAIN'		=> $language->lang('DELETING_INDEX_IN_PROGRESS_EXPLAIN'),
								'INDEXING_PROGRESS'		=> $language->lang('SEARCH_INDEX_DELETE_REDIRECT', $row_count, $post_counter),
								'INDEXING_RATE'			=> $language->lang('SEARCH_INDEX_DELETE_REDIRECT_RATE', $rows_per_second),
								'INDEXING_PROGRESS_BAR'	=> $this->get_post_index_progress($post_counter),
							]);

							$this->tpl_name = 'acp_search';
							$this->page_title = 'ACP_SEARCH_INDEX';

							return;
						}
					}

					$this->search->tidy();

					$this->state = array('');
					$this->save_state();

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_SEARCH_INDEX_REMOVED', false, array($name));
					trigger_error($user->lang['SEARCH_INDEX_REMOVED'] . adm_back_link($this->u_action));
				break;

				case 'create':
					if (method_exists($this->search, 'create_index'))
					{
						// pass a reference to acp_search so the $search object can make use of save_state() and attributes
						if ($error = $this->search->create_index($this, append_sid("{$phpbb_admin_path}index.$phpEx", "i=$id&mode=$mode&action=create", false)))
						{
							$this->state = array('');
							$this->save_state();
							trigger_error($error . adm_back_link($this->u_action), E_USER_WARNING);
						}
					}
					else if ($submit)
					{
						meta_refresh(1, append_sid($this->u_action . '&amp;action=create&amp;skip_rows=' . $post_counter . '&amp;hash=' . generate_link_hash('acp_search')));

						$template->assign_vars([
							'S_INDEX_PROGRESS'		=> true,
							'INDEXING_TITLE'		=> $language->lang('INDEXING_IN_PROGRESS'),
							'INDEXING_EXPLAIN'		=> $language->lang('INDEXING_IN_PROGRESS_EXPLAIN'),
							'INDEXING_PROGRESS_BAR'	=> $this->get_post_index_progress($post_counter),
						]);

						$this->tpl_name = 'acp_search';
						$this->page_title = 'ACP_SEARCH_INDEX';

						return;
					}
					else
					{
						$sql = 'SELECT forum_id, enable_indexing
							FROM ' . FORUMS_TABLE;
						$result = $db->sql_query($sql, 3600);

						while ($row = $db->sql_fetchrow($result))
						{
							$forums[$row['forum_id']] = (bool) $row['enable_indexing'];
						}
						$db->sql_freeresult($result);

						$starttime = microtime(true);
						$row_count = 0;
						while (still_on_time() && $post_counter < $this->max_post_id)
						{
							$sql = 'SELECT post_id, post_subject, post_text, poster_id, forum_id
								FROM ' . POSTS_TABLE . '
								WHERE post_id > ' . (int) $post_counter . '
								ORDER BY post_id ASC';
							$result = $db->sql_query_limit($sql, $this->batch_size);

							$buffer = $db->sql_buffer_nested_transactions();

							if ($buffer)
							{
								$rows = $db->sql_fetchrowset($result);
								$rows[] = false; // indicate end of array for while loop below

								$db->sql_freeresult($result);
							}

							$i = 0;
							while ($row = ($buffer ? $rows[$i++] : $db->sql_fetchrow($result)))
							{
								// Indexing enabled for this forum
								if (isset($forums[$row['forum_id']]) && $forums[$row['forum_id']])
								{
									$this->search->index('post', $row['post_id'], $row['post_text'], $row['post_subject'], $row['poster_id'], $row['forum_id']);
								}
								$row_count++;
								$post_counter = $row['post_id'];
							}
							if (!$buffer)
							{
								$db->sql_freeresult($result);
							}
						}
						// save the current state
						$this->save_state();

						// pretend the number of posts was as big as the number of ids we indexed so far
						// just an estimation as it includes deleted posts
						$num_posts = $config['num_posts'];
						$config['num_posts'] = min($config['num_posts'], $post_counter);
						$this->search->tidy();
						$config['num_posts'] = $num_posts;

						if ($post_counter < $this->max_post_id)
						{
							$totaltime = microtime(true) - $starttime;
							$rows_per_second = $row_count / $totaltime;
							meta_refresh(1, append_sid($this->u_action . '&amp;action=create&amp;skip_rows=' . $post_counter . '&amp;hash=' . generate_link_hash('acp_search')));
							$template->assign_vars([
								'S_INDEX_PROGRESS'		=> true,
								'INDEXING_TITLE'		=> $language->lang('INDEXING_IN_PROGRESS'),
								'INDEXING_EXPLAIN'		=> $language->lang('INDEXING_IN_PROGRESS_EXPLAIN'),
								'INDEXING_PROGRESS'		=> $language->lang('SEARCH_INDEX_CREATE_REDIRECT', $row_count, $post_counter),
								'INDEXING_RATE'			=> $language->lang('SEARCH_INDEX_CREATE_REDIRECT_RATE', $rows_per_second),
								'INDEXING_PROGRESS_BAR'	=> $this->get_post_index_progress($post_counter),
							]);

							$this->tpl_name = 'acp_search';
							$this->page_title = 'ACP_SEARCH_INDEX';

							return;
						}
					}

					$this->search->tidy();

					$this->state = array('');
					$this->save_state();

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_SEARCH_INDEX_CREATED', false, array($name));
					trigger_error($user->lang['SEARCH_INDEX_CREATED'] . adm_back_link($this->u_action));
				break;
			}
		}

		$search_types = $this->get_search_types();

		$search = null;
		$error = false;
		foreach ($search_types as $type)
		{
			if ($this->init_search($type, $search, $error) || !method_exists($search, 'index_created'))
			{
				continue;
			}

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
		unset($error);
		unset($statistics);
		unset($data);

		$this->tpl_name = 'acp_search';
		$this->page_title = 'ACP_SEARCH_INDEX';

		$template->assign_vars(array(
			'S_INDEX'				=> true,
			'U_ACTION'				=> $this->u_action . '&amp;hash=' . generate_link_hash('acp_search'),
		));

		if (isset($this->state[1]))
		{
			$template->assign_vars(array(
				'S_CONTINUE_INDEXING'	=> $this->state[1],
				'U_CONTINUE_INDEXING'	=> $this->u_action . '&amp;action=' . $this->state[1] . '&amp;hash=' . generate_link_hash('acp_search'),
				'CONTINUE_PROGRESS'	=> (isset($this->state[2]) && $this->state[2] > 0) ? $this->get_post_index_progress($this->state[2]) : $this->get_post_index_progress(0)
			));
		}
	}

	function get_search_types()
	{
		global $phpbb_extension_manager;

		$finder = $phpbb_extension_manager->get_finder();

		return $finder
			->extension_suffix('_backend')
			->extension_directory('/search')
			->core_path('phpbb/search/')
			->get_classes();
	}

	function get_max_post_id()
	{
		global $db;

		$sql = 'SELECT MAX(post_id) as max_post_id
			FROM '. POSTS_TABLE;
		$result = $db->sql_query($sql);
		$max_post_id = (int) $db->sql_fetchfield('max_post_id');
		$db->sql_freeresult($result);

		return $max_post_id;
	}

	/**
	 * Get progress stats of search index with HTML progress bar.
	 *
	 * @param int		$post_counter	Post ID of last post indexed.
	 * @return array	Returns array with progress bar data.
	 */
	function get_post_index_progress(int $post_counter)
	{
		global $db, $language;

		$sql = 'SELECT COUNT(post_id) as done_count
			FROM ' . POSTS_TABLE . '
			WHERE post_id <= ' . (int) $post_counter;
		$result = $db->sql_query($sql);
		$done_count = (int) $db->sql_fetchfield('done_count');
		$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) as remain_count
			FROM ' . POSTS_TABLE . '
			WHERE post_id > ' . (int) $post_counter;
		$result = $db->sql_query($sql);
		$remain_count = (int) $db->sql_fetchfield('remain_count');
		$db->sql_freeresult($result);

		$total_count = $done_count + $remain_count;
		$percent = ($done_count / $total_count) * 100;

		return [
			'VALUE'			=> $done_count,
			'TOTAL'			=> $total_count,
			'PERCENTAGE'	=> $percent,
			'REMAINING'		=> $remain_count,
		];
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

	/**
	* Initialises a search backend object
	*
	* @return false if no error occurred else an error message
	*/
	function init_search($type, &$search, &$error)
	{
		global $phpbb_root_path, $phpEx, $user, $auth, $config, $db, $phpbb_dispatcher;

		if (!class_exists($type) || !method_exists($type, 'keyword_search'))
		{
			$error = $user->lang['NO_SUCH_SEARCH_MODULE'];
			return $error;
		}

		$error = false;
		$search = new $type($error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher);

		return $error;
	}
}
