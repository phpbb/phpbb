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

use phpbb\config\config;
use phpbb\di\service_collection;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\search\search_backend_factory;
use phpbb\template\template;
use phpbb\user;
use phpbb\db\driver\driver_interface;

if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_search
{
	public $u_action;
	public $tpl_name;
	public $page_title;

	protected const STATE_SEARCH_TYPE = 0;
	protected const STATE_ACTION = 1;
	protected const STATE_POST_COUNTER = 2;

	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var log */
	protected $log;

	/** @var request */
	protected $request;

	/** @var service_collection */
	protected $search_backend_collection;

	/** @var search_backend_factory */
	protected $search_backend_factory;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var driver_interface DBAL driver */
	protected $db;

	/** @var string */
	protected $phpbb_admin_path;

	/** @var string */
	protected $php_ex;

	public function __construct($p_master)
	{
		global $config, $phpbb_container, $language, $phpbb_log, $request, $template, $user, $db, $phpbb_admin_path, $phpEx;

		$this->config = $config;
		$this->language = $language;
		$this->log = $phpbb_log;
		$this->request = $request;
		$this->search_backend_collection = $phpbb_container->get('search.backend_collection');
		$this->search_backend_factory = $phpbb_container->get('search.backend_factory');
		$this->template = $template;
		$this->user = $user;
		$this->db = $db;
		$this->phpbb_admin_path = $phpbb_admin_path;
		$this->php_ex = $phpEx;
	}

	/**
	 * @param string $id
	 * @param string $mode
	 * @throws Exception
	 * @return void
	 */
	public function main(string $id, string $mode): void
	{
		$this->language->add_lang('acp/search');

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

	/**
	 * Settings page
	 *
	 * @param string $id
	 * @param string $mode
	 */
	public function settings(string $id, string $mode): void
	{
		$submit = $this->request->is_set_post('submit');

		if ($submit && !check_link_hash($this->request->variable('hash', ''), 'acp_search'))
		{
			trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

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

		$search_options = '';

		foreach ($this->search_backend_collection as $search)
		{
			// Only show available search backends
			if ($search->is_available())
			{
				$name = $search->get_name();
				$type = $search->get_type();

				$selected = ($this->config['search_type'] === $type) ? ' selected="selected"' : '';
				$identifier = substr($type, strrpos($type, '\\') + 1);
				$search_options .= "<option value=\"$type\"$selected data-toggle-setting=\"#search_{$identifier}_settings\">$name</option>";

				$vars = $search->get_acp_options();

				if (!$submit)
				{
					$this->template->assign_block_vars('backend', [
						'NAME' => $name,
						'SETTINGS' => $vars['tpl'],
						'IDENTIFIER' => $identifier,
					]);
				}
				else if (is_array($vars['config']))
				{
					$settings = array_merge($settings, $vars['config']);
				}
			}
		}

		$cfg_array = (isset($_REQUEST['config'])) ? $this->request->variable('config', ['' => ''], true) : [];
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
			if ($submit && ($this->config[$config_name] !== $config_value))
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

			if (isset($cfg_array['search_type']) && ($cfg_array['search_type'] !== $this->config['search_type']))
			{
				$search = $this->search_backend_factory->get($cfg_array['search_type']);
				if (confirm_box(true))
				{
					// Initialize search backend, if $error is false means that everything is ok
					if (!($error = $search->init()))
					{
						$this->config->set('search_type', $cfg_array['search_type']);

						if (!$updated)
						{
							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CONFIG_SEARCH');
						}
						$extra_message = '<br>' . $this->language->lang('SWITCHED_SEARCH_BACKEND') . '<br><a href="' . append_sid($this->phpbb_admin_path . "index." . $this->php_ex, 'i=search&amp;mode=index') . '">&raquo; ' . $this->language->lang('GO_TO_SEARCH_INDEX') . '</a>';
					}
					else
					{
						trigger_error($error . adm_back_link($this->u_action), E_USER_WARNING);
					}
				}
				else
				{
					confirm_box(false, $this->language->lang('CONFIRM_SEARCH_BACKEND'), build_hidden_fields([
						'i'			=> $id,
						'mode'		=> $mode,
						'submit'	=> true,
						'updated'	=> $updated,
						'config'	=> ['search_type' => $cfg_array['search_type']],
					]));
				}
			}

			trigger_error($this->language->lang('CONFIG_UPDATED') . $extra_message . adm_back_link($this->u_action));
		}
		unset($cfg_array);

		$this->tpl_name = 'acp_search_settings';
		$this->page_title = 'ACP_SEARCH_SETTINGS';

		$this->template->assign_vars([
			'DEFAULT_SEARCH_RETURN_CHARS'	=> (int) $this->config['default_search_return_chars'],
			'LIMIT_SEARCH_LOAD'				=> (float) $this->config['limit_search_load'],
			'MIN_SEARCH_AUTHOR_CHARS'		=> (int) $this->config['min_search_author_chars'],
			'SEARCH_INTERVAL'				=> (float) $this->config['search_interval'],
			'SEARCH_GUEST_INTERVAL'			=> (float) $this->config['search_anonymous_interval'],
			'SEARCH_STORE_RESULTS'			=> (int) $this->config['search_store_results'],
			'MAX_NUM_SEARCH_KEYWORDS'		=> (int) $this->config['max_num_search_keywords'],

			'S_SEARCH_TYPES'		=> $search_options,
			'S_YES_SEARCH'			=> (bool) $this->config['load_search'],

			'U_ACTION'				=> $this->u_action . '&amp;hash=' . generate_link_hash('acp_search'),
		]);
	}

	/**
	 * Execute action depending on the action and state
	 *
	 * @param string $id
	 * @param string $mode
	 * @throws Exception
	 */
	public function index(string $id, string $mode): void
	{
		$action = $this->request->variable('action', '');
		$state = !empty($this->config['search_indexing_state']) ? explode(',', $this->config['search_indexing_state']) : [];

		if ($action && !$this->request->is_set_post('cancel'))
		{
			switch ($action)
			{
				case 'create':
				case 'delete':
					$this->index_action($id, $mode, $action, $state);
				break;

				default:
					trigger_error('NO_ACTION', E_USER_ERROR);
			}
		}
		else
		{
			// If clicked to cancel the indexing progress (acp_search_index_inprogress form)
			if ($this->request->is_set_post('cancel'))
			{
				$state = [];
				$this->save_state($state);
			}

			if (!empty($state))
			{
				$this->index_inprogress($id, $mode, $state);
			}
			else
			{
				$this->index_overview($id, $mode);
			}
		}
	}

	/**
	 * @param string $id
	 * @param string $mode
	 *
	 * @throws Exception
	 */
	private function index_overview(string $id, string $mode): void
	{
		$this->tpl_name = 'acp_search_index';
		$this->page_title = 'ACP_SEARCH_INDEX';

		foreach ($this->search_backend_collection as $search)
		{
			$this->template->assign_block_vars('backends', [
				'NAME'			=> $search->get_name(),
				'TYPE'				=> $search->get_type(),

				'S_ACTIVE'			=> $search->get_type() === $this->config['search_type'],
				'S_HIDDEN_FIELDS'	=> build_hidden_fields(['search_type' => $search->get_type()]),
				'S_INDEXED'			=> $search->index_created(),
				'S_STATS'			=> $search->index_stats(),
			]);
		}

		$this->template->assign_vars([
			'U_ACTION'				=> $this->u_action . '&amp;hash=' . generate_link_hash('acp_search'),
		]);
	}

	/**
	 * Form to continue or cancel indexing process
	 *
	 * @param string $id
	 * @param string $mode
	 * @param array $state
	 */
	private function index_inprogress(string $id, string $mode, array $state): void
	{
		$this->tpl_name = 'acp_search_index_inprogress';
		$this->page_title = 'ACP_SEARCH_INDEX';

		$action = $state[self::STATE_ACTION];
		$post_counter = (isset($state[self::STATE_POST_COUNTER]) && $state[self::STATE_POST_COUNTER] > 0) ? $state[self::STATE_POST_COUNTER] : 0;

		$this->template->assign_vars([
			'U_ACTION'				=> $this->u_action . '&amp;action=' . $action . '&amp;hash=' . generate_link_hash('acp_search'),
			'L_CONTINUE'			=> ($action === 'create') ? $this->language->lang('CONTINUE_INDEXING') : $this->language->lang('CONTINUE_DELETING_INDEX'),
			'L_CONTINUE_EXPLAIN'	=> ($action === 'create') ? $this->language->lang('CONTINUE_INDEXING_EXPLAIN') : $this->language->lang('CONTINUE_DELETING_INDEX_EXPLAIN'),
			'CONTINUE_PROGRESS'		=> $this->get_post_index_progress($post_counter),
			'S_ACTION'				=> $action,
		]);
	}

	/**
	 * Progress that do the indexing/index removal, updating the page continuously until is finished
	 *
	 * @param string $id
	 * @param string $mode
	 * @param string $action
	 * @param array $state
	 */
	private function index_action(string $id, string $mode, string $action, array $state): void
	{
		// For some this may be of help...
		@ini_set('memory_limit', '128M');

		if (!check_link_hash($this->request->variable('hash', ''), 'acp_search'))
		{
			trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Entering here for the first time
		if (empty($state))
		{
			if ($this->request->is_set_post('search_type', ''))
			{
				$state = [
					self::STATE_SEARCH_TYPE => $this->request->variable('search_type', ''),
					self::STATE_ACTION => $action,
					self::STATE_POST_COUNTER => 0
				];
			}
			else
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->save_state($state); // Create new state in the database
		}

		$type = $state[self::STATE_SEARCH_TYPE];
		$action = $state[self::STATE_ACTION];
		$post_counter = &$state[self::STATE_POST_COUNTER];

		$message_progress = $this->language->lang(($action == 'create') ? 'INDEXING_IN_PROGRESS' : 'DELETING_INDEX_IN_PROGRESS');
		$message_progress_explain = $this->language->lang(($action == 'create') ? 'INDEXING_IN_PROGRESS_EXPLAIN' : 'DELETING_INDEX_IN_PROGRESS_EXPLAIN');

		if ($this->request->is_set_post('submit'))
		{
			$u_action = append_sid($this->phpbb_admin_path . "index." . $this->php_ex, "i=$id&mode=$mode&action=$action&hash=" . generate_link_hash('acp_search'), false);
			meta_refresh(1, $u_action);

			$lang_str_ary = [
				$message_progress,
				$message_progress_explain,
				$this->get_post_index_progress($post_counter)
			];
			trigger_error(implode('<br>', $lang_str_ary));
		}

		// Execute create/delete
		$search = $this->search_backend_factory->get($type);

		try
		{
			$status = ($action == 'create') ? $search->create_index($post_counter) : $search->delete_index($post_counter);
			if ($status) // Status is not null, so action is in progress....
			{
				$this->save_state($state); // update $post_counter in $state in the database

				$u_action = append_sid($this->phpbb_admin_path . "index." . $this->php_ex, "i=$id&mode=$mode&action=$action&hash=" . generate_link_hash('acp_search'), false);
				meta_refresh(1, $u_action);

				$message_redirect = $this->language->lang(($action == 'create') ? 'SEARCH_INDEX_CREATE_REDIRECT' : 'SEARCH_INDEX_DELETE_REDIRECT', (int) $status['row_count'], $status['post_counter']);
				$message_rate = $this->language->lang(($action == 'create') ? 'SEARCH_INDEX_CREATE_REDIRECT_RATE' : 'SEARCH_INDEX_DELETE_REDIRECT_RATE', $status['rows_per_second']);

				$lang_str_ary = [
					$message_progress,
					$message_progress_explain,
					$message_redirect,
					$message_rate,
					$this->get_post_index_progress($status['post_counter'])
				];
				trigger_error(implode('<br>', $lang_str_ary));
			}
		}
		catch (Exception $e)
		{
			$this->save_state([]); // Unexpected error, cancel action
			trigger_error($e->getMessage() . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$search->tidy();

		$this->save_state([]); // finished operation, cancel action

		$log_operation = ($action == 'create') ? 'LOG_SEARCH_INDEX_CREATED' : 'LOG_SEARCH_INDEX_REMOVED';
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $log_operation, false, [$search->get_name()]);

		$message = $this->language->lang(($action == 'create') ? 'SEARCH_INDEX_CREATED' : 'SEARCH_INDEX_REMOVED');
		trigger_error($message . adm_back_link($this->u_action));
	}

	/**
	 * Get progress stats of search index with HTML progress bar.
	 *
	 * @param int		$post_counter	Post ID of last post indexed.
	 * @return string	Returns string with HTML progress bar and stats.
	 */
	function get_post_index_progress(int $post_counter)
	{
		$sql = 'SELECT COUNT(post_id) as done_count
			FROM ' . POSTS_TABLE . '
			WHERE post_id <= ' . (int) $post_counter;
		$result = $this->db->sql_query($sql);
		$done_count = (int) $this->db->sql_fetchfield('done_count');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) as remain_count
			FROM ' . POSTS_TABLE . '
			WHERE post_id > ' . (int) $post_counter;
		$result = $this->db->sql_query($sql);
		$remain_count = (int) $this->db->sql_fetchfield('remain_count');
		$this->db->sql_freeresult($result);

		$total_count = $done_count + $remain_count;
		$percent = ($done_count / $total_count) * 100;

		$progress = sprintf('<progress value="%1$d" max="%2$d" style="height: 2em; width: 20em;"></progress><br> %3$.2f %% <br>', $done_count, $total_count, $percent);
		$progress .= $this->language->lang('SEARCH_INDEX_PROGRESS', $done_count, $remain_count, $total_count);

		return $progress;
	}

	/**
	 * @param array $state
	 */
	private function save_state(array $state = []): void
	{
		ksort($state);

		$this->config->set('search_indexing_state', implode(',', $state), true);
	}
}
