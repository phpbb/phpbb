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

class search
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB admin path */
	protected $admin_path;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/** @var \phpbb\search\fulltext_mysql @todo Search interface? */
	protected $search;

	/** @var array */
	protected $state;

	/** @var int */
	protected $max_post_id;

	/** @var int */
	protected $batch_size = 100;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth			Auth object
	 * @param \phpbb\config\config				$config			Config object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\event\dispatcher			$dispatcher		Event dispatcher object
	 * @param \phpbb\extension\manager			$ext_manager	Extension manager object
	 * @param \phpbb\acp\helper\controller		$helper			ACP Controller helper object
	 * @param \phpbb\language\language			$language		Language object
	 * @param \phpbb\log\log					$log			Log object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$admin_path		phpBB admin path
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\extension\manager $ext_manager,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$admin_path,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->ext_manager	= $ext_manager;
		$this->helper		= $helper;
		$this->language		= $language;
		$this->log			= $log;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->admin_path	= $admin_path;
		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	public function main($mode)
	{
		$this->language->add_lang('acp/search');

		// For some this may be of help...
		@ini_set('memory_limit', '128M');

		return $this->$mode();
	}

	protected function settings()
	{
		$submit = $this->request->is_set_post('submit');

		if ($submit && !check_link_hash($this->request->variable('hash', ''), 'acp_search'))
		{
			return trigger_error($this->language->lang('FORM_INVALID') . $this->helper->adm_back_route('acp_settings_search'), E_USER_WARNING);
		}

		$search_types = $this->get_search_types();

		$settings = [
			'search_interval'			=> 'float',
			'search_anonymous_interval'	=> 'float',
			'load_search'				=> 'bool',
			'limit_search_load'			=> 'float',
			'min_search_author_chars'	=> 'integer',
			'max_num_search_keywords'	=> 'integer',
			'search_store_results'		=> 'integer',
		];

		/** @var \phpbb\search\fulltext_mysql $search	@todo Search interface? */
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

			$selected = $this->config['search_type'] == $type ? ' selected="selected"' : '';
			$identifier = substr($type, strrpos($type, '\\') + 1);
			$search_options .= "<option value=\"$type\"$selected data-toggle-setting=\"#search_{$identifier}_settings\">$name</option>";

			if (method_exists($search, 'acp'))
			{
				$vars = $search->acp();

				if (!$submit)
				{
					$this->template->assign_block_vars('backend', [
						'NAME'			=> $name,
						'SETTINGS'		=> $vars['tpl'],
						'IDENTIFIER'	=> $identifier,
					]);
				}
				else if (is_array($vars['config']))
				{
					$settings = array_merge($settings, $vars['config']);
				}
			}
		}
		unset($search);
		unset($error);

		$cfg_array = $this->request->is_set('config') ? $this->request->variable('config', ['' => ''], true) : [];
		$updated = $this->request->variable('updated', false);

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
			if ($submit && ($this->config[$config_name] != $config_value))
			{
				$this->config->set($config_name, $config_value);
				$updated = true;
			}
		}

		if ($submit)
		{
			$extra_message = '';

			if ($updated)
			{
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_SEARCH');
			}

			if (isset($cfg_array['search_type']) && in_array($cfg_array['search_type'], $search_types, true) && ($cfg_array['search_type'] != $this->config['search_type']))
			{
				$search = null;
				$error = false;

				if (!$this->init_search($cfg_array['search_type'], $search, $error))
				{
					if (confirm_box(true))
					{
						if (!method_exists($search, 'init') || !($error = $search->init()))
						{
							$this->config->set('search_type', $cfg_array['search_type']);

							if (!$updated)
							{
								$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_SEARCH');
							}

							$extra_message = '<br />' . $this->language->lang('SWITCHED_SEARCH_BACKEND') . '<br /><a href="' . append_sid("{$this->admin_path}index.$this->php_ext", 'i=search&amp;mode=index') . '">&raquo; ' . $this->language->lang('GO_TO_SEARCH_INDEX') . '</a>';
						}
						else
						{
							return trigger_error($error . $this->helper->adm_back_route('acp_settings_search'), E_USER_WARNING);
						}
					}
					else
					{
						confirm_box(false, $this->language->lang('CONFIRM_SEARCH_BACKEND'), build_hidden_fields([
							'submit'	=> true,
							'updated'	=> $updated,
							'config'	=> ['search_type' => $cfg_array['search_type']],
						]));

						return redirect($this->helper->route('acp_settings_search'));
					}
				}
				else
				{
					return trigger_error($error . $this->helper->adm_back_route('acp_settings_search'), E_USER_WARNING);
				}
			}

			$search = null;
			$error = false;
			if (!$this->init_search($this->config['search_type'], $search, $error))
			{
				if ($updated)
				{
					if (method_exists($search, 'config_updated'))
					{
						if ($search->config_updated())
						{
							return trigger_error($error . $this->helper->adm_back_route('acp_settings_search'), E_USER_WARNING);
						}
					}
				}
			}
			else
			{
				return trigger_error($error . $this->helper->adm_back_route('acp_settings_search'), E_USER_WARNING);
			}

			return $this->helper->message_back($this->language->lang('CONFIG_UPDATED') . $extra_message, 'acp_settings_search');
		}
		unset($cfg_array);

		$this->template->assign_vars([
			'LIMIT_SEARCH_LOAD'			=> (float) $this->config['limit_search_load'],
			'SEARCH_INTERVAL'			=> (float) $this->config['search_interval'],
			'SEARCH_GUEST_INTERVAL'		=> (float) $this->config['search_anonymous_interval'],
			'SEARCH_STORE_RESULTS'		=> (int) $this->config['search_store_results'],
			'MAX_NUM_SEARCH_KEYWORDS'	=> (int) $this->config['max_num_search_keywords'],
			'MIN_SEARCH_AUTHOR_CHARS'	=> (int) $this->config['min_search_author_chars'],

			'S_SETTINGS'			=> true,
			'S_SEARCH_TYPES'		=> $search_options,
			'S_YES_SEARCH'			=> (bool) $this->config['load_search'],

			'U_ACTION'				=> $this->helper->route('acp_settings_search', ['hash' => generate_link_hash('acp_search')]),
		]);

		return $this->helper->render('acp_search.html', $this->language->lang('ACP_SETTINGS_SEARCH'));
	}

	function index()
	{
		$action = $this->request->variable('action', '');

		$this->state = explode(',', $this->config['search_indexing_state']);

		if ($this->request->is_set_post('cancel'))
		{
			$action = '';
			$this->state = [];
			$this->save_state();
		}

		if (!check_link_hash($this->request->variable('hash', ''), 'acp_search') && in_array($action, ['create', 'delete']))
		{
			return trigger_error($this->language->lang('FORM_INVALID') . $this->helper->adm_back_route('acp_search_index'), E_USER_WARNING);
		}

		if ($action)
		{
			switch ($action)
			{
				case 'progress_bar':
					return $this->display_progress_bar();
				break;

				case 'delete':
					$this->state[1] = 'delete';
				break;

				case 'create':
					$this->state[1] = 'create';
				break;

				default:
					return trigger_error($this->language->lang('NO_ACTION') . $this->helper->adm_back_route('acp_search_index'), E_USER_ERROR);
				break;
			}

			if (empty($this->state[0]))
			{
				$this->state[0] = $this->request->variable('search_type', '');
			}

			$this->search = null;
			$error = false;
			if ($this->init_search($this->state[0], $this->search, $error))
			{
				return trigger_error($error . $this->helper->adm_back_route('acp_search_index'), E_USER_WARNING);
			}
			$name = $this->search->get_name();

			$action = &$this->state[1];

			$this->max_post_id = $this->get_max_post_id();

			$post_counter = isset($this->state[2]) ? $this->state[2] : 0;
			$this->state[2] = &$post_counter;
			$this->save_state();

			switch ($action)
			{
				case 'delete':
					if (method_exists($this->search, 'delete_index'))
					{
						// pass a reference to myself so the $search object can make use of save_state() and attributes
						$error = $this->search->delete_index($this, $this->helper->route('acp_search_index', ['action' => 'delete', 'hash' => generate_link_hash('acp_search')]));

						if ($error)
						{
							$this->state = [''];
							$this->save_state();

							return trigger_error($error . $this->helper->adm_back_route('acp_search_index') . $this->close_popup_js(), E_USER_WARNING);
						}
					}
					else
					{
						$starttime = microtime(true);
						$row_count = 0;

						while (still_on_time() && $post_counter <= $this->max_post_id)
						{
							$ids = $posters = $forum_ids = [];

							$sql = 'SELECT post_id, poster_id, forum_id
								FROM ' . $this->tables['posts'] . '
								WHERE post_id >= ' . (int) ($post_counter + 1) . '
									AND post_id <= ' . (int) ($post_counter + $this->batch_size);
							$result = $this->db->sql_query($sql);
							while ($row = $this->db->sql_fetchrow($result))
							{
								$ids[] = $row['post_id'];
								$posters[] = $row['poster_id'];
								$forum_ids[] = $row['forum_id'];
							}
							$this->db->sql_freeresult($result);
							$row_count += count($ids);

							if (!empty($ids))
							{
								$this->search->index_remove($ids, $posters, $forum_ids);
							}

							$post_counter += $this->batch_size;
						}
						// save the current state
						$this->save_state();

						if ($post_counter <= $this->max_post_id)
						{
							$totaltime = microtime(true) - $starttime;
							$rows_per_second = $row_count / $totaltime;

							meta_refresh(1, $this->helper->route('acp_search_index', ['action' => 'delete', 'skip_rows' => $post_counter, 'hash' => generate_link_hash('acp_search')]));

							return $this->helper->message($this->language->lang('SEARCH_INDEX_DELETE_REDIRECT', (int) $row_count, $post_counter) . $this->language->lang('SEARCH_INDEX_DELETE_REDIRECT_RATE', $rows_per_second));
						}
					}

					$this->search->tidy();

					$this->state = [''];
					$this->save_state();

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_SEARCH_INDEX_REMOVED', false, [$name]);

					return $this->helper->message_back($this->language->lang('SEARCH_INDEX_REMOVED') . $this->close_popup_js(), 'acp_search_index');
				break;

				case 'create':
					if (method_exists($this->search, 'create_index'))
					{
						// pass a reference to acp_search so the $search object can make use of save_state() and attributes
						$error = $this->search->create_index($this, $this->helper->route('acp_search_index', ['action' => 'create', 'hash' => generate_link_hash('acp_search')]));

						if ($error)
						{
							$this->state = [''];
							$this->save_state();

							return trigger_error($error . $this->helper->adm_back_route('acp_search_index') . $this->close_popup_js(), E_USER_WARNING);
						}
					}
					else
					{
						$forums = [];

						$sql = 'SELECT forum_id, enable_indexing
							FROM ' . $this->tables['forums'];
						$result = $this->db->sql_query($sql, 3600);
						while ($row = $this->db->sql_fetchrow($result))
						{
							$forums[$row['forum_id']] = (bool) $row['enable_indexing'];
						}
						$this->db->sql_freeresult($result);

						$starttime = microtime(true);
						$row_count = 0;
						$rows = [];

						while (still_on_time() && $post_counter <= $this->max_post_id)
						{
							$sql = 'SELECT post_id, post_subject, post_text, poster_id, forum_id
								FROM ' . $this->tables['posts'] . '
								WHERE post_id >= ' . (int) ($post_counter + 1) . '
									AND post_id <= ' . (int) ($post_counter + $this->batch_size);
							$result = $this->db->sql_query($sql);

							$buffer = $this->db->sql_buffer_nested_transactions();

							if ($buffer)
							{
								$rows = $this->db->sql_fetchrowset($result);
								$rows[] = false; // indicate end of array for while loop below

								$this->db->sql_freeresult($result);
							}

							$i = 0;
							while ($row = ($buffer ? $rows[$i++] : $this->db->sql_fetchrow($result)))
							{
								// Indexing enabled for this forum
								if (isset($forums[$row['forum_id']]) && $forums[$row['forum_id']])
								{
									$this->search->index('post', $row['post_id'], $row['post_text'], $row['post_subject'], $row['poster_id'], $row['forum_id']);
								}
								$row_count++;
							}
							if (!$buffer)
							{
								$this->db->sql_freeresult($result);
							}

							$post_counter += $this->batch_size;
						}
						// save the current state
						$this->save_state();

						// pretend the number of posts was as big as the number of ids we indexed so far
						// just an estimation as it includes deleted posts
						$num_posts = $this->config['num_posts'];
						$this->config['num_posts'] = min($this->config['num_posts'], $post_counter);
						$this->search->tidy();
						$this->config['num_posts'] = $num_posts;

						if ($post_counter <= $this->max_post_id)
						{
							$totaltime = microtime(true) - $starttime;
							$rows_per_second = $row_count / $totaltime;

							meta_refresh(1, $this->helper->route('acp_search_index', ['action' => 'create', 'skip_rows' => $post_counter, 'hash' => generate_link_hash('acp_search')]));

							return $this->helper->message($this->language->lang('SEARCH_INDEX_CREATE_REDIRECT', (int) $row_count, $post_counter) . $this->language->lang('SEARCH_INDEX_CREATE_REDIRECT_RATE', $rows_per_second));
						}
					}

					$this->search->tidy();

					$this->state = [''];
					$this->save_state();

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_SEARCH_INDEX_CREATED', false, [$name]);

					return $this->helper->message_back($this->language->lang('SEARCH_INDEX_CREATED') . $this->close_popup_js(), 'acp_search_index');
				break;
			}
		}

		$search_types = $this->get_search_types();

		/** @var \phpbb\search\fulltext_mysql $search	@todo Search interface? */
		$search = null;
		$error = false;

		foreach ($search_types as $type)
		{
			if ($this->init_search($type, $search, $error) || !method_exists($search, 'index_created'))
			{
				continue;
			}

			$name = $search->get_name();
			$data = [];
			$statistics = [];

			if (method_exists($search, 'index_stats'))
			{
				$data = $search->index_stats();
			}

			foreach ($data as $statistic => $value)
			{
				$n = count($statistics);
				if ($n && count($statistics[$n - 1]) < 3)
				{
					$statistics[$n - 1] += ['statistic_2' => $statistic, 'value_2' => $value];
				}
				else
				{
					$statistics[] = ['statistic_1' => $statistic, 'value_1' => $value];
				}
			}

			$this->template->assign_block_vars('backend', [
				'L_NAME'			=> $name,
				'NAME'				=> $type,

				'S_ACTIVE'			=> $type == $this->config['search_type'],
				'S_HIDDEN_FIELDS'	=> build_hidden_fields(['search_type' => $type]),
				'S_INDEXED'			=> (bool) $search->index_created(),
				'S_STATS'			=> (bool) count($statistics),
			]);

			foreach ($statistics as $statistic)
			{
				$this->template->assign_block_vars('backend.data', [
					'STATISTIC_1'	=> $statistic['statistic_1'],
					'VALUE_1'		=> $statistic['value_1'],
					'STATISTIC_2'	=> isset($statistic['statistic_2']) ? $statistic['statistic_2'] : '',
					'VALUE_2'		=> isset($statistic['value_2']) ? $statistic['value_2'] : '',
				]);
			}
		}
		unset($search);
		unset($error);
		unset($statistics);
		unset($data);

		$this->template->assign_vars([
			'S_INDEX'				=> true,
			'U_ACTION'				=> $this->helper->route('acp_search_index', ['hash' => generate_link_hash('acp_search')]),
			'U_PROGRESS_BAR'		=> $this->helper->route('acp_search_index', ['action' => 'progress_bar']),
			'UA_PROGRESS_BAR'		=> addslashes($this->helper->route('acp_search_index', ['action' => 'progress_bar'])),
		]);

		if (isset($this->state[1]))
		{
			$this->template->assign_vars([
				'S_CONTINUE_INDEXING'	=> $this->state[1],
				'U_CONTINUE_INDEXING'	=> $this->helper->route('acp_search_index', ['action' => $this->state[1], 'hash' => generate_link_hash('acp_search')]),
				'L_CONTINUE'			=> $this->state[1] === 'create' ? $this->language->lang('CONTINUE_INDEXING') : $this->language->lang('CONTINUE_DELETING_INDEX'),
				'L_CONTINUE_EXPLAIN'	=> $this->state[1] === 'create' ? $this->language->lang('CONTINUE_INDEXING_EXPLAIN') : $this->language->lang('CONTINUE_DELETING_INDEX_EXPLAIN'),
			]);
		}

		return $this->helper->render('acp_search.html', $this->language->lang('ACP_SEARCH_INDEX'));
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function display_progress_bar()
	{
		$type = $this->request->variable('type', '');

		$l_type = $type === 'create' ? 'INDEXING_IN_PROGRESS' : 'DELETING_INDEX_IN_PROGRESS';

		$this->template->assign_vars([
			'L_PROGRESS'			=> $this->language->lang($l_type),
			'L_PROGRESS_EXPLAIN'	=> $this->language->lang($l_type . '_EXPLAIN'),
		]);

		return $this->helper->render('progress_bar.html', $l_type);
	}

	/**
	 * @return string
	 */
	protected function close_popup_js()
	{
		return "<script type=\"text/javascript\">\n" .
			"// <![CDATA[\n" .
			"	close_waitscreen = 1;\n" .
			"// ]]>\n" .
			"</script>\n";
	}

	/**
	 * @return array
	 */
	protected function get_search_types()
	{
		$finder = $this->ext_manager->get_finder();

		return $finder
			->extension_suffix('_backend')
			->extension_directory('/search')
			->core_path('phpbb/search/')
			->get_classes();
	}

	/**
	 * @return int
	 */
	protected function get_max_post_id()
	{
		$sql = 'SELECT MAX(post_id) as max_post_id
			FROM '. $this->tables['posts'];
		$result = $this->db->sql_query($sql);
		$max_post_id = $this->db->sql_fetchfield('max_post_id');
		$this->db->sql_freeresult($result);

		return (int) $max_post_id;
	}

	/**
	 * @param array|false	$state
	 * @return void
	 */
	protected function save_state($state = false)
	{
		if ($state)
		{
			$this->state = $state;
		}

		ksort($this->state);

		$this->config->set('search_indexing_state', implode(',', $this->state), true);
	}

	/**
	 * Initialises a search backend object.
	 *
	 * @param string						$type		The search time
	 * @param \phpbb\search\fulltext_mysql	$search		The search object (null at first) @todo Search interface?
	 * @param string|false					$error		Error message
	 * @return string|false								false if no error occurred else an error message
	 */
	protected function init_search($type, &$search, &$error)
	{
		if (!class_exists($type) || !method_exists($type, 'keyword_search'))
		{
			$error = $this->language->lang('NO_SUCH_SEARCH_MODULE');
			return $error;
		}

		$error = false;
		$search	= new $type($error, $this->root_path, $this->php_ext, $this->auth, $this->config, $this->db, $this->user, $this->dispatcher);

		return $error;
	}
}
