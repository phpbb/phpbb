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
use phpbb\db\driver\driver_interface;
use phpbb\di\service_collection;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\search\backend\search_backend_interface;
use phpbb\search\search_backend_factory;
use phpbb\search\state_helper;
use phpbb\template\template;
use phpbb\user;

if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_search
{
	public $u_action;
	public $tpl_name;
	public $page_title;

	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

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

	/** @var state_helper  */
	protected $search_state_helper;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var string */
	protected $phpbb_admin_path;

	/** @var string */
	protected $php_ex;

	public function __construct($p_master)
	{
		global $config, $db, $phpbb_container, $language, $phpbb_log, $request, $template, $user, $phpbb_admin_path, $phpEx;

		$this->config = $config;
		$this->db = $db;
		$this->language = $language;
		$this->log = $phpbb_log;
		$this->request = $request;
		$this->search_backend_collection = $phpbb_container->get('search.backend_collection');
		$this->search_backend_factory = $phpbb_container->get('search.backend_factory');
		$this->search_state_helper = $phpbb_container->get('search.state_helper');
		$this->template = $template;
		$this->user = $user;
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

		if ($action && !$this->request->is_set_post('cancel'))
		{
			switch ($action)
			{
				case 'create':
				case 'delete':
					$this->index_action($id, $mode, $action);
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
				$this->search_state_helper->clear_state();
			}

			if ($this->search_state_helper->is_action_in_progress())
			{
				$this->index_inprogress($id, $mode);
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

		/** @var search_backend_interface $search */
		foreach ($this->search_backend_collection as $search)
		{
			$this->template->assign_block_vars('backends', [
				'NAME'	=> $search->get_name(),
				'TYPE'	=> $search->get_type(),

				'S_ACTIVE'			=> $search->get_type() === $this->config['search_type'],
				'S_HIDDEN_FIELDS'	=> build_hidden_fields(['search_type' => $search->get_type()]),
				'S_INDEXED'			=> $search->index_created(),
				'S_STATS'			=> $search->index_stats(),
			]);
		}

		$this->template->assign_vars([
			'U_ACTION'			=> $this->u_action . '&amp;hash=' . generate_link_hash('acp_search'),
			'UA_PROGRESS_BAR'	=> addslashes($this->u_action . '&amp;action=progress_bar'),
		]);
	}

	/**
	 * Form to continue or cancel indexing process
	 *
	 * @param string $id
	 * @param string $mode
	 */
	private function index_inprogress(string $id, string $mode): void
	{
		$this->tpl_name = 'acp_search_index_inprogress';
		$this->page_title = 'ACP_SEARCH_INDEX';

		$action = $this->search_state_helper->action();
		$post_counter = $this->search_state_helper->counter();

		$this->template->assign_vars([
			'U_ACTION'				=> $this->u_action . '&amp;action=' . $action . '&amp;hash=' . generate_link_hash('acp_search'),
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
	 */
	private function index_action(string $id, string $mode, string $action): void
	{
		// For some this may be of help...
		@ini_set('memory_limit', '128M');

		if (!check_link_hash($this->request->variable('hash', ''), 'acp_search'))
		{
			trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Entering here for the first time
		if (!$this->search_state_helper->is_action_in_progress())
		{
			if ($this->request->is_set_post('search_type', ''))
			{
				$this->search_state_helper->init($this->request->variable('search_type', ''), $action);
			}
			else
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		// Start displaying progress on first submit
		if ($this->request->is_set_post('submit'))
		{
			$this->display_progress_bar($id, $mode);
			return;
		}

		// Execute create/delete
		$type = $this->search_state_helper->type();
		$action = $this->search_state_helper->action();
		$post_counter = $this->search_state_helper->counter();

		$search = $this->search_backend_factory->get($type);

		try
		{
			$status = ($action == 'create') ? $search->create_index($post_counter) : $search->delete_index($post_counter);
			if ($status) // Status is not null, so action is in progress....
			{
				$this->search_state_helper->update_counter($status['post_counter']);

				$u_action = append_sid($this->phpbb_admin_path . "index." . $this->php_ex, "i=$id&mode=$mode&action=$action&hash=" . generate_link_hash('acp_search'), false);
				meta_refresh(1, $u_action);

				$message_progress = $this->language->lang(($action === 'create') ? 'INDEXING_IN_PROGRESS' : 'DELETING_INDEX_IN_PROGRESS');
				$message_progress_explain = $this->language->lang(($action == 'create') ? 'INDEXING_IN_PROGRESS_EXPLAIN' : 'DELETING_INDEX_IN_PROGRESS_EXPLAIN');
				$message_redirect = $this->language->lang(
					($action === 'create') ? 'SEARCH_INDEX_CREATE_REDIRECT' : 'SEARCH_INDEX_DELETE_REDIRECT',
					(int) $status['row_count'],
					$status['post_counter']
				);
				$message_redirect_rate = $this->language->lang(
					($action === 'create') ? 'SEARCH_INDEX_CREATE_REDIRECT_RATE' : 'SEARCH_INDEX_DELETE_REDIRECT_RATE',
					$status['rows_per_second']
				);

				$this->template->assign_vars([
					'INDEXING_TITLE'		=> $message_progress,
					'INDEXING_EXPLAIN'		=> $message_progress_explain,
					'INDEXING_PROGRESS'		=> $message_redirect,
					'INDEXING_RATE'			=> $message_redirect_rate,
					'INDEXING_PROGRESS_BAR'	=> $this->get_post_index_progress($post_counter),
				]);

				$this->tpl_name = 'acp_search_index_progress';
				$this->page_title = 'ACP_SEARCH_INDEX';

				return;
			}
		}
		catch (Exception $e)
		{
			$this->search_state_helper->clear_state(); // Unexpected error, cancel action
			trigger_error($e->getMessage() . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$search->tidy();

		$this->search_state_helper->clear_state(); // finished operation, cancel action

		$log_operation = ($action == 'create') ? 'LOG_SEARCH_INDEX_CREATED' : 'LOG_SEARCH_INDEX_REMOVED';
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $log_operation, false, [$search->get_name()]);

		$message = $this->language->lang(($action == 'create') ? 'SEARCH_INDEX_CREATED' : 'SEARCH_INDEX_REMOVED');
		trigger_error($message . adm_back_link($this->u_action));
	}

	/**
	 * Display progress bar for search after first submit
	 *
	 * @param string $id ACP module id
	 * @param string $mode ACP module mode
	 */
	private function display_progress_bar(string $id, string $mode): void
	{
		$action = $this->search_state_helper->action();
		$post_counter = $this->search_state_helper->counter();

		$message_progress = $this->language->lang(($action === 'create') ? 'INDEXING_IN_PROGRESS' : 'DELETING_INDEX_IN_PROGRESS');
		$message_progress_explain = $this->language->lang(($action == 'create') ? 'INDEXING_IN_PROGRESS_EXPLAIN' : 'DELETING_INDEX_IN_PROGRESS_EXPLAIN');

		$u_action = append_sid($this->phpbb_admin_path . "index." . $this->php_ex, "i=$id&mode=$mode&action=$action&hash=" . generate_link_hash('acp_search'), false);
		meta_refresh(1, $u_action);

		adm_page_header($this->language->lang($message_progress));

		$this->template->set_filenames([
			'body'	=> 'acp_search_index_progress.html'
		]);

		$this->template->assign_vars([
			'INDEXING_TITLE'		=> $message_progress,
			'INDEXING_EXPLAIN'		=> $message_progress_explain,
			'INDEXING_PROGRESS_BAR'	=> $this->get_post_index_progress($post_counter),
		]);

		adm_page_footer();
	}

	/**
	 * Get progress stats of search index with HTML progress bar.
	 *
	 * @param int		$post_counter	Post ID of last post indexed.
	 * @return array	Returns array with progress bar data.
	 */
	protected function get_post_index_progress(int $post_counter): array
	{
		$sql = 'SELECT COUNT(post_id) as done_count
			FROM ' . POSTS_TABLE . '
			WHERE post_id <= ' . $post_counter;
		$result = $this->db->sql_query($sql);
		$done_count = (int) $this->db->sql_fetchfield('done_count');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(post_id) as remain_count
			FROM ' . POSTS_TABLE . '
			WHERE post_id > ' . $post_counter;
		$result = $this->db->sql_query($sql);
		$remain_count = (int) $this->db->sql_fetchfield('remain_count');
		$this->db->sql_freeresult($result);

		$total_count = $done_count + $remain_count;
		$percent = $total_count > 0 ? ($done_count / $total_count) * 100 : 100;

		return [
			'VALUE'			=> $done_count,
			'TOTAL'			=> $total_count,
			'PERCENTAGE'	=> $percent,
			'REMAINING'		=> $remain_count,
		];
	}
}
