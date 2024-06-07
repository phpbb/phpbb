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

use phpbb\db\driver\driver_interface;
use phpbb\di\service_collection;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\request\request;
use phpbb\storage\exception\storage_exception;
use phpbb\storage\helper;
use phpbb\storage\state_helper;
use phpbb\storage\update_type;
use phpbb\template\template;
use phpbb\user;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_storage
{
	/** @var driver_interface */
	protected $db;

	/** @var language */
	protected $lang;

	/** @var log_interface */
	protected $log;

	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var service_collection */
	protected $provider_collection;

	/** @var service_collection */
	protected $storage_collection;

	/** @var \phpbb\filesystem\filesystem */
	protected $filesystem;

	/** @var string */
	public $page_title;

	/** @var string */
	public $phpbb_root_path;

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $u_action;

	/** @var state_helper */
	private $state_helper;

	/** @var helper */
	private $storage_helper;

	/** @var string */
	private $storage_table;

	/**
	 * @param string $id
	 * @param string $mode
	 */
	public function main(string $id, string $mode): void
	{
		global $phpbb_container, $phpbb_dispatcher, $phpbb_root_path;

		$this->db = $phpbb_container->get('dbal.conn');
		$this->lang = $phpbb_container->get('language');
		$this->log = $phpbb_container->get('log');
		$this->request = $phpbb_container->get('request');
		$this->template = $phpbb_container->get('template');
		$this->user = $phpbb_container->get('user');
		$this->provider_collection = $phpbb_container->get('storage.provider_collection');
		$this->storage_collection = $phpbb_container->get('storage.storage_collection');
		$this->filesystem = $phpbb_container->get('filesystem');
		$this->phpbb_root_path = $phpbb_root_path;
		$this->state_helper = $phpbb_container->get('storage.state_helper');
		$this->storage_helper = $phpbb_container->get('storage.helper');
		$this->storage_table = $phpbb_container->getParameter('tables.storage');

		// Add necessary language files
		$this->lang->add_lang(['acp/storage']);

		/**
		 * Add language strings
		 *
		 * @event core.acp_storage_load
		 * @since 4.0.0-a1
		 */
		$phpbb_dispatcher->trigger_event('core.acp_storage_load');

		switch ($mode)
		{
			case 'settings':
				$this->settings($id, $mode);
			break;
		}
	}

	/**
	 * Method to route the request to the correct page
	 *
	 * @param string $id
	 * @param string $mode
	 */
	private function settings(string $id, string $mode): void
	{
		$action = $this->request->variable('action', '');
		if ($action && !$this->request->is_set_post('cancel'))
		{
			switch ($action)
			{
				case 'update':
					$this->update_action();
				break;

				default:
					trigger_error('NO_ACTION', E_USER_ERROR);
			}
		}
		else
		{
			// If clicked to cancel (acp_storage_update_progress form)
			if ($this->request->is_set_post('cancel'))
			{
				$this->state_helper->clear_state();
			}

			// There is an updating in progress, show the form to continue or cancel
			if ($this->state_helper->is_action_in_progress())
			{
				$this->update_inprogress();
			}
			else
			{
				$this->settings_form();
			}
		}
	}

	/**
	 * Page to update storage settings and move files
	 *
	 * @return void
	 */
	private function update_action(): void
	{
		if (!check_link_hash($this->request->variable('hash', ''), 'acp_storage'))
		{
			trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// If update_type is copy or move, copy files from the old to the new storage
		if (in_array($this->state_helper->update_type(), [update_type::COPY, update_type::MOVE], true))
		{
			$i = 0;
			foreach ($this->state_helper->storages() as $storage_name)
			{
				// Skip storages that have already copied files
				if ($this->state_helper->storage_index() > $i++)
				{
					continue;
				}

				$sql = 'SELECT file_id, file_path
						FROM ' . $this->storage_table . "
						WHERE  storage = '" . $this->db->sql_escape($storage_name) . "'
							AND file_id > " . $this->state_helper->file_index();
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					if (!still_on_time())
					{
						$this->db->sql_freeresult($result);
						$this->display_progress_page();
						return;
					}

					// Copy file from old adapter to the new one
					$this->storage_helper->copy_file_to_new_adapter($storage_name, $row['file_path']);

					$this->state_helper->set_file_index($row['file_id']); // update last file index copied
				}

				$this->db->sql_freeresult($result);

				// Copied all files of a storage, increase storage index and reset file index
				$this->state_helper->set_storage_index($this->state_helper->storage_index()+1);
				$this->state_helper->set_file_index(0);
			}

			// If update_type is move files, remove the old files
			if ($this->state_helper->update_type() === update_type::MOVE)
			{
				$i = 0;
				foreach ($this->state_helper->storages() as $storage_name)
				{
					// Skip storages that have already moved files
					if ($this->state_helper->remove_storage_index() > $i++)
					{
						continue;
					}

					$sql = 'SELECT file_id, file_path
							FROM ' . $this->storage_table . "
							WHERE  storage = '" . $this->db->sql_escape($storage_name) . "'
								AND file_id > " . $this->state_helper->file_index();
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						if (!still_on_time())
						{
							$this->db->sql_freeresult($result);
							$this->display_progress_page();
							return;
						}

						// remove file from old (current) adapter
						$current_adapter = $this->storage_helper->get_current_adapter($storage_name);
						$current_adapter->delete($row['file_path']);

						$this->state_helper->set_file_index($row['file_id']);
					}

					$this->db->sql_freeresult($result);

					// Remove all files of a storage, increase storage index and reset file index
					$this->state_helper->set_remove_storage_index($this->state_helper->remove_storage_index() + 1);
					$this->state_helper->set_file_index(0);
				}
			}
		}

		// Here all files have been copied/moved, so save new configuration
		foreach ($this->state_helper->storages() as $storage_name)
		{
			$this->storage_helper->update_storage_config($storage_name);
		}

		$storages = $this->state_helper->storages();
		$this->state_helper->clear_state();
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_STORAGE_UPDATE', false, [implode(', ', $storages)]);
		trigger_error($this->lang->lang('STORAGE_UPDATE_SUCCESSFUL') . adm_back_link($this->u_action));
	}

	/**
	 * Page that show a form with the progress bar, and a button to continue or cancel
	 *
	 * @return void
	 */
	private function update_inprogress(): void
	{
		// Template from adm/style
		$this->tpl_name = 'acp_storage_update_inprogress';

		// Set page title
		$this->page_title = 'STORAGE_TITLE';

		$this->template->assign_vars([
			'U_ACTION'	=> $this->u_action . '&amp;action=update&amp;hash=' . generate_link_hash('acp_storage'),
			'CONTINUE_PROGRESS' => $this->get_storage_update_progress(),
		]);
	}

	/**
	 * Main settings page, shows a form with all the storages and their configuration options
	 *
	 * @return void
	 */
	private function settings_form(): void
	{
		$form_key = 'acp_storage';
		add_form_key($form_key);

		// Process form and create a "state" for the update,
		// then show a confirm form
		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key($form_key) || !check_link_hash($this->request->variable('hash', ''), 'acp_storage'))
			{
				trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$modified_storages = $this->get_modified_storages();

			// validate submited paths if they are local
			$messages = [];
			foreach ($modified_storages as $storage_name)
			{
				$this->validate_data($storage_name, $messages);
			}
			if (!empty($messages))
			{
				trigger_error(implode('<br>', $messages) . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// Start process and show progress
			if (!empty($modified_storages))
			{
				// Create state
				$this->state_helper->init(update_type::from((int) $this->request->variable('update_type', update_type::CONFIG->value)), $modified_storages, $this->request);

				// Start displaying progress on first submit
				$this->display_progress_page();
				return;
			}

			// If there is no changes
			trigger_error($this->lang->lang('STORAGE_NO_CHANGES') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Template from adm/style
		$this->tpl_name = 'acp_storage';

		// Set page title
		$this->page_title = 'STORAGE_TITLE';

		$this->storage_stats(); // Show table with storage stats

		// Validate local paths to check if everything is fine
		$messages = [];
		foreach ($this->storage_collection as $storage)
		{
			$this->validate_path($storage->get_name(), $messages);
		}

		$this->template->assign_vars([
			'STORAGES'						=> $this->storage_collection,
			'PROVIDERS' 					=> $this->provider_collection,

			'ERROR_MESSAGES'				=> $messages,

			'U_ACTION'						=> $this->u_action . '&amp;hash=' . generate_link_hash('acp_storage'),

			'STORAGE_UPDATE_TYPE_CONFIG'	=> update_type::CONFIG->value,
			'STORAGE_UPDATE_TYPE_COPY'		=> update_type::COPY->value,
			'STORAGE_UPDATE_TYPE_MOVE'		=> update_type::MOVE->value,
		]);
	}

	/**
	 * When submit the settings form, check which storages have been modified
	 * to update only those.
	 *
	 * @return array
	 */
	private function get_modified_storages(): array
	{
		$modified_storages = [];

		foreach ($this->storage_collection as $storage)
		{
			$storage_name = $storage->get_name();
			$options = $this->storage_helper->get_provider_options($this->storage_helper->get_current_provider($storage_name));

			$modified = false;

			// Check if provider have been modified
			if ($this->request->variable([$storage_name, 'provider'], '') != $this->storage_helper->get_current_provider($storage_name))
			{
				$modified = true;
			}
			else
			{
				// Check if options have been modified
				foreach (array_keys($options) as $definition)
				{
					if ($this->request->variable([$storage_name, $definition], '') != $this->storage_helper->get_current_definition($storage_name, $definition))
					{
						$modified = true;
						break;
					}
				}
			}

			if ($modified)
			{
				$modified_storages[] = $storage_name;
			}
		}

		return $modified_storages;
	}

	/**
	 * Fill template variables to show storage stats in settings page
	 *
	 * @return void
	 */
	protected function storage_stats(): void
	{
		// Top table with stats of each storage
		$storage_stats = [];
		foreach ($this->storage_collection as $storage)
		{
			try
			{
				$free_space = get_formatted_filesize($storage->free_space());
			}
			catch (storage_exception $e)
			{
				$free_space = $this->lang->lang('STORAGE_UNKNOWN');
			}

			$storage_stats[] = [
				'name' => $this->lang->lang('STORAGE_' . strtoupper($storage->get_name()) . '_TITLE'),
				'files' => $storage->get_num_files(),
				'size' => get_formatted_filesize($storage->get_size()),
				'free_space' => $free_space,
			];
		}

		$this->template->assign_vars([
			'STORAGE_STATS' => $storage_stats,
		]);
	}

	/**
	 * Display progress page
	 */
	protected function display_progress_page() : void
	{
		$u_action = append_sid($this->u_action . '&amp;action=update&amp;hash=' . generate_link_hash('acp_storage'));
		meta_refresh(1, $u_action);

		adm_page_header($this->lang->lang('STORAGE_UPDATE_IN_PROGRESS'));
		$this->template->set_filenames([
				'body'	=> 'acp_storage_update_progress.html'
		]);

		$this->template->assign_vars([
				'INDEXING_TITLE'		=> $this->lang->lang('STORAGE_UPDATE_IN_PROGRESS'),
				'INDEXING_EXPLAIN'		=> $this->lang->lang('STORAGE_UPDATE_IN_PROGRESS_EXPLAIN'),
				'INDEXING_PROGRESS_BAR'	=> $this->get_storage_update_progress(),
		]);
		adm_page_footer();
	}

	/**
	 * Get storage update progress to show progress bar
	 *
	 * @return array
	 */
	protected function get_storage_update_progress(): array
	{
		$file_index = $this->state_helper->file_index();
		$stage_is_copy = $this->state_helper->storage_index() < count($this->state_helper->storages());
		$storage_name = $this->state_helper->storages()[$stage_is_copy ? $this->state_helper->storage_index() : $this->state_helper->remove_storage_index()];

		$sql = 'SELECT COUNT(file_id) as done_count
			FROM ' . $this->storage_table . '
			WHERE file_id <= ' . $file_index . "
				AND storage = '" . $this->db->sql_escape($storage_name) . "'";
		$result = $this->db->sql_query($sql);
		$done_count = (int) $this->db->sql_fetchfield('done_count');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT COUNT(file_id) as remain_count
			FROM ' . $this->storage_table . "
			WHERE file_id > ' . $file_index . '
				AND storage = '" . $this->db->sql_escape($storage_name) . "'";
		$result = $this->db->sql_query($sql);
		$remain_count = (int) $this->db->sql_fetchfield('remain_count');
		$this->db->sql_freeresult($result);

		$total_count = $done_count + $remain_count;
		$percent = $total_count > 0 ? $done_count / $total_count : 0;

		$steps = $this->state_helper->storage_index() + $this->state_helper->remove_storage_index() + $percent;
		$multiplier = $this->state_helper->update_type() === update_type::MOVE ? 2 : 1;
		$steps_total = count($this->state_helper->storages()) * $multiplier;

		return [
			'VALUE'			=> $steps,
			'TOTAL'			=> $steps_total,
			'PERCENTAGE'	=> $steps / $steps_total * 100,
		];
	}

	/**
	 * Validates data
	 *
	 * @param string $storage_name Storage name
	 * @param array $messages Reference to messages array
	 */
	protected function validate_data(string $storage_name, array &$messages): void
	{
		$storage_title = $this->lang->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE');

		// Check if provider exists
		try
		{
			$new_provider = $this->provider_collection->get_by_class($this->request->variable([$storage_name, 'provider'], ''));
		}
		catch (\Exception $e)
		{
			$messages[] = $this->lang->lang('STORAGE_PROVIDER_NOT_EXISTS', $storage_title);
			return;
		}

		// Check if provider is available
		if (!$new_provider->is_available())
		{
			$messages[] = $this->lang->lang('STORAGE_PROVIDER_NOT_AVAILABLE', $storage_title);
			return;
		}

		$this->validate_path($storage_name, $messages);

		// Check options
		$new_options = $this->storage_helper->get_provider_options($this->request->variable([$storage_name, 'provider'], ''));

		foreach ($new_options as $definition_key => $definition_value)
		{
			$provider = $this->provider_collection->get_by_class($this->request->variable([$storage_name, 'provider'], ''));
			$definition_title = $this->lang->lang('STORAGE_ADAPTER_' . strtoupper($provider->get_name()) . '_OPTION_' . strtoupper($definition_key));

			$value = $this->request->variable([$storage_name, $definition_key], '');

			switch ($definition_value['tag'])
			{
				case 'text':
					if ($definition_value['type'] == 'email' && filter_var($value, FILTER_VALIDATE_EMAIL))
					{
						$messages[] = $this->lang->lang('STORAGE_FORM_TYPE_EMAIL_INCORRECT_FORMAT', $definition_title, $storage_title);
					}

					$maxlength = $definition_value['max'] ?? 255;
					if (strlen($value) > $maxlength)
					{
						$messages[] = $this->lang->lang('STORAGE_FORM_TYPE_TEXT_TOO_LONG', $definition_title, $storage_title);
					}

					if ($provider->get_name() == 'local' && $definition_key == 'path')
					{
						$path = $value;

						if (empty($path))
						{
							$messages[] = $this->lang->lang('STORAGE_PATH_NOT_SET', $this->lang->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE'));
						}
						else if (!$this->filesystem->exists($this->phpbb_root_path . $path) || !$this->filesystem->is_writable($this->phpbb_root_path . $path))
						{
							$messages[] = $this->lang->lang('STORAGE_PATH_NOT_EXISTS', $this->lang->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE'));
						}
					}
				break;

				case 'radio':
					$found = false;
					foreach ($definition_value['buttons'] as $button)
					{
						if ($button['value'] == $value)
						{
							$found = true;
							break;
						}
					}

					if (!$found)
					{
						$messages[] = $this->lang->lang('STORAGE_FORM_TYPE_SELECT_NOT_AVAILABLE', $definition_title, $storage_title);
					}
				break;

				case 'select':
					$found = false;
					foreach ($definition_value['options'] as $option)
					{
						if ($option['value'] == $value)
						{
							$found = true;
							break;
						}
					}

					if (!$found)
					{
						$messages[] = $this->lang->lang('STORAGE_FORM_TYPE_SELECT_NOT_AVAILABLE', $definition_title, $storage_title);
					}
				break;
			}
		}
	}

	/**
	 * Validates path when the filesystem is local
	 *
	 * @param string $storage_name Storage name
	 * @param array $messages Error messages array
	 * @return void
	 */
	protected function validate_path(string $storage_name, array &$messages) : void
	{
		$current_provider = $this->storage_helper->get_current_provider($storage_name);
		$options = $this->storage_helper->get_provider_options($current_provider);

		if ($this->provider_collection->get_by_class($current_provider)->get_name() == 'local' && isset($options['path']))
		{
			$path = $this->request->is_set_post('submit') ? $this->request->variable([$storage_name, 'path'], '') : $this->storage_helper->get_current_definition($storage_name, 'path');

			if (empty($path))
			{
				$messages[] = $this->lang->lang('STORAGE_PATH_NOT_SET', $this->lang->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE'));
			}
			else if (!$this->filesystem->exists($this->phpbb_root_path . $path) || !$this->filesystem->is_writable($this->phpbb_root_path . $path))
			{
				$messages[] = $this->lang->lang('STORAGE_PATH_NOT_EXISTS', $this->lang->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE'));
			}
		}
	}

}
