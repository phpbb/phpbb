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

class acp_storage
{
	/** @var \phpbb\config $config */
	protected $config;

	/** @var \phpbb\language\language $lang */
	protected $lang;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\di\service_collection */
	protected $provider_collection;

	/** @var \phpbb\di\service_collection */
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

	/**
	 *  @param string $id
	 * @param string $mode
	 */
	public function main($id, $mode)
	{
		global $phpbb_container, $phpbb_dispatcher, $phpbb_root_path;

		$this->config = $phpbb_container->get('config');
		$this->filesystem = $phpbb_container->get('filesystem');
		$this->lang = $phpbb_container->get('language');
		$this->request = $phpbb_container->get('request');
		$this->template = $phpbb_container->get('template');
		$this->user = $phpbb_container->get('user');
		$this->provider_collection = $phpbb_container->get('storage.provider_collection');
		$this->storage_collection = $phpbb_container->get('storage.storage_collection');
		$this->phpbb_root_path = $phpbb_root_path;

		// Add necesary language files
		$this->lang->add_lang(['acp/storage']);

		/**
		 * Add language strings
		 *
		 * @event core.acp_storage_load
		 * @since 3.3.0-a1
		 */
		$phpbb_dispatcher->trigger_event('core.acp_storage_load');

		$this->overview($id, $mode);
	}

	/**
	 * @param string $id
	 * @param string $mode
	 */
	public function overview($id, $mode)
	{
		$form_key = 'acp_storage';
		add_form_key($form_key);

		// Template from adm/style
		$this->tpl_name = 'acp_storage';

		// Set page title
		$this->page_title = 'STORAGE_TITLE';

		$messages = [];
		if ($this->request->is_set_post('submit'))
		{
			$modified_storages = [];

			if (!check_form_key($form_key))
			{
				$messages[] = $this->lang->lang('FORM_INVALID');
			}

			foreach ($this->storage_collection as $storage)
			{
				$storage_name = $storage->get_name();

				$options = $this->get_provider_options($this->get_current_provider($storage_name));

				$this->validate_path($storage_name, $options, $messages);

				$modified = false;

				// Check if provider have been modified
				if ($this->get_new_provider($storage_name) != $this->get_current_provider($storage_name))
				{
					$modified = true;
				}

				// Check if options have been modified
				if (!$modified)
				{
					foreach (array_keys($options) as $definition)
					{
						if ($this->get_new_definition($storage_name, $definition) != $this->get_current_definition($storage_name, $definition))
						{
							$modified = true;
							break;
						}
					}
				}

				// If the storage have been modified, validate options
				if ($modified)
				{
					$modified_storages[] = $storage_name;
					$this->validate_data($storage_name, $messages);
				}
			}

			if (!empty($modified_storages))
			{
				if (empty($messages))
				{
					foreach ($modified_storages as $storage_name)
					{
						$this->update_storage_config($storage_name);
					}

					trigger_error($this->lang->lang('STORAGE_UPDATE_SUCCESSFUL') . adm_back_link($this->u_action), E_USER_NOTICE);
				}
				else
				{
					trigger_error(implode('<br />', $messages) . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}

			// If there is no changes
			trigger_error($this->lang->lang('STORAGE_NO_CHANGES') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$storage_stats = [];
		foreach ($this->storage_collection as $storage)
		{
			$storage_name = $storage->get_name();
			$options = $this->get_provider_options($this->get_current_provider($storage_name));

			$this->validate_path($storage_name, $options, $messages);

			try
			{
				$free_space = get_formatted_filesize($storage->free_space());
			}
			catch (\phpbb\storage\exception\exception $e)
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
			'STORAGES' => $this->storage_collection,
			'STORAGE_STATS' => $storage_stats,
			'PROVIDERS' => $this->provider_collection,

			'ERROR_MSG'	=> implode('<br />', $messages),
			'S_ERROR'	=> !empty($messages),
		]);
	}

	/**
	 * Get the current provider from config
	 *
	 * @param string $storage_name Storage name
	 * @return string The current provider
	 */
	protected function get_current_provider($storage_name)
	{
		return $this->config['storage\\' . $storage_name . '\\provider'];
	}

	/**
	 * Get the new provider from the request
	 *
	 * @param string $storage_name Storage name
	 * @return string The new provider
	 */
	protected function get_new_provider($storage_name)
	{
		return $this->request->variable([$storage_name, 'provider'], '');
	}

	/**
	 * Get adapter definitions from a provider
	 *
	 * @param string $provider Provider class
	 * @return array Adapter definitions
	 */
	protected function get_provider_options($provider)
	{
		return $this->provider_collection->get_by_class($provider)->get_options();
	}

	/**
	 * Get the current value of the definition of a storage from config
	 *
	 * @param string $storage_name Storage name
	 * @param string $definition Definition
	 * @return string Definition value
	 */
	protected function get_current_definition($storage_name, $definition)
	{
		return $this->config['storage\\' . $storage_name . '\\config\\' . $definition];
	}

	/**
	 * Get the new value of the definition of a storage from the request
	 *
	 * @param string $storage_name Storage name
	 * @param string $definition Definition
	 * @return string Definition value
	 */
	protected function get_new_definition($storage_name, $definition)
	{
		return $this->request->variable([$storage_name, $definition], '');
	}

	/**
	 * Validates data
	 *
	 * @param string $storage_name Storage name
	 * @param array $messages Reference to messages array
	 */
	protected function validate_data($storage_name, &$messages)
	{
		$storage_title = $this->lang->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE');

		// Check if provider exists
		try
		{
			$new_provider = $this->provider_collection->get_by_class($this->get_new_provider($storage_name));
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

		// Check options
		$new_options = $this->get_provider_options($this->get_new_provider($storage_name));

		foreach ($new_options as $definition_key => $definition_value)
		{
			$provider = $this->provider_collection->get_by_class($this->get_new_provider($storage_name));
			$definition_title = $this->lang->lang('STORAGE_ADAPTER_' . strtoupper($provider->get_name()) . '_OPTION_' . strtoupper($definition_key));

			$value = $this->get_new_definition($storage_name, $definition_key);

			switch ($definition_value['type'])
			{
				case 'email':
					if (!filter_var($value, FILTER_VALIDATE_EMAIL))
					{
						$messages[] = $this->lang->lang('STORAGE_FORM_TYPE_EMAIL_INCORRECT_FORMAT', $definition_title, $storage_title);
					}
				case 'text':
				case 'password':
					$maxlength = isset($definition_value['maxlength']) ? $definition_value['maxlength'] : 255;
					if (strlen($value) > $maxlength)
					{
						$messages[] = $this->lang->lang('STORAGE_FORM_TYPE_TEXT_TOO_LONG', $definition_title, $storage_title);
					}
					break;
				case 'radio':
				case 'select':
					if (!in_array($value, array_values($definition_value['options'])))
					{
						$messages[] = $this->lang->lang('STORAGE_FORM_TYPE_SELECT_NOT_AVAILABLE', $definition_title, $storage_title);
					}
					break;
			}
		}
	}

	/**
	 * Updates an storage with the info provided in the form
	 *
	 * @param string $storage_name Storage name
	 */
	protected function update_storage_config($storage_name)
	{
		$current_options = $this->get_provider_options($this->get_current_provider($storage_name));

		// Remove old storage config
		foreach (array_keys($current_options) as $definition)
		{
			$this->config->delete('storage\\' . $storage_name . '\\config\\' . $definition);
		}

		// Update provider
		$this->config->set('storage\\' . $storage_name . '\\provider', $this->get_new_provider($storage_name));

		// Set new storage config
		$new_options = $this->get_provider_options($this->get_new_provider($storage_name));

		foreach (array_keys($new_options) as $definition)
		{
			$this->config->set('storage\\' . $storage_name . '\\config\\' . $definition, $this->get_new_definition($storage_name, $definition));
		}
	}

	/**
	 * Validates path
	 *
	 * @param string $storage_name Storage name
	 * @param array $options Storage provider configuration keys
	 * @param array $messages Error messages array
	 * @return array $messages Reference to messages array
	 */
	protected function validate_path($storage_name, $options, &$messages)
	{
		if ($this->provider_collection->get_by_class($this->get_current_provider($storage_name))->get_name() == 'local' && isset($options['path']))
		{
			$path = $this->request->is_set_post('submit') ? $this->get_new_definition($storage_name, 'path') : $this->get_current_definition($storage_name, 'path');

			if (empty($path))
			{
				$messages[] = $this->lang->lang('STORAGE_PATH_NOT_SET', $this->lang->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE'));
			}
			else if (!$this->filesystem->is_writable($this->phpbb_root_path . $path) || !$this->filesystem->exists($this->phpbb_root_path . $path))
			{
				$messages[] = $this->lang->lang('STORAGE_PATH_NOT_EXISTS', $this->lang->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE'));
			}
		}
	}
}
