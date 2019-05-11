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

class storage
{
	/** @var \phpbb\config\config $config */
	protected $config;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\language\language $lang */
	protected $lang;

	/** @var \phpbb\di\service_collection */
	protected $provider_collection;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\di\service_collection */
	protected $storage_collection;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config			$config					Config object
	 * @param \phpbb\event\dispatcher		$dispatcher				Event dispatcher object
	 * @param \phpbb\language\language		$lang					Language object
	 * @param \phpbb\di\service_collection	$provider_collection	Provider collection object
	 * @param \phpbb\request\request		$request				Request object
	 * @param \phpbb\di\service_collection	$storage_collection		Storage collection object
	 * @param \phpbb\template\template		$template				Template object
	 * @param \phpbb\user					$user					User object
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\language\language $lang,
		\phpbb\di\service_collection $provider_collection,
		\phpbb\request\request $request,
		\phpbb\di\service_collection $storage_collection,
		\phpbb\template\template $template,
		\phpbb\user $user
	)
	{
		$this->config				= $config;
		$this->dispatcher			= $dispatcher;
		$this->lang					= $lang;
		$this->provider_collection	= $provider_collection;
		$this->request				= $request;
		$this->storage_collection	= $storage_collection;
		$this->template				= $template;
		$this->user					= $user;
	}

	public function main($id, $mode)
	{
		// Add necessary language files
		$this->lang->add_lang(['acp/storage']);

		/**
		 * Add language strings
		 *
		 * @event core.acp_storage_load
		 * @since 3.3.0-a1
		 */
		$this->dispatcher->dispatch('core.acp_storage_load');

		$this->overview($id, $mode);
	}

	public function overview($id, $mode)
	{
		$form_key = 'acp_storage';
		add_form_key($form_key);

		// Template from adm/style
		$this->tpl_name = 'acp_storage';

		// Set page title
		$this->page_title = 'STORAGE_TITLE';

		if ($this->request->is_set_post('submit'))
		{
			$modified_storages = [];
			$errors = [];

			if (!check_form_key($form_key))
			{
				$errors[] = $this->lang->lang('FORM_INVALID');
			}

			foreach ($this->storage_collection as $storage)
			{
				$storage_name = $storage->get_name();

				$options = $this->get_provider_options($this->get_current_provider($storage_name));

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
					$this->validate_data($storage_name, $errors);
				}
			}

			if (!empty($modified_storages))
			{
				if (empty($errors))
				{
					foreach ($modified_storages as $storage_name)
					{
						$this->update_storage_config($storage_name);
					}

					trigger_error($this->lang->lang('STORAGE_UPDATE_SUCCESSFUL') . adm_back_link($this->u_action), E_USER_NOTICE);
				}
				else
				{
					trigger_error(implode('<br />', $errors) . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}

			// If there is no changes
			trigger_error($this->lang->lang('STORAGE_NO_CHANGES') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$storage_stats = [];
		foreach ($this->storage_collection as $storage)
		{
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
		]);
	}

	/**
	 * Get the current provider from config.
	 *
	 * @param string	storage_name	Storage name
	 * @return string					The current provider
	 */
	protected function get_current_provider($storage_name)
	{
		return $this->config['storage\\' . $storage_name . '\\provider'];
	}

	/**
	 * Get the new provider from the request.
	 *
	 * @param string	storage_name	Storage name
	 * @return string					The new provider
	 */
	protected function get_new_provider($storage_name)
	{
		return $this->request->variable([$storage_name, 'provider'], '');
	}

	/**
	 * Get adapter definitions from a provider.
	 *
	 * @param string	$provider	Provider class
	 * @return array				Adapter definitions
	 */
	protected function get_provider_options($provider)
	{
		return $this->provider_collection->get_by_class($provider)->get_options();
	}

	/**
	 * Get the current value of the definition of a storage from config
	 *
	 * @param string	$storage_name	Storage name
	 * @param string	$definition		Definition
	 * @return string					Definition value
	 */
	protected function get_current_definition($storage_name, $definition)
	{
		return $this->config['storage\\' . $storage_name . '\\config\\' . $definition];
	}

	/**
	 * Get the new value of the definition of a storage from the request
	 *
	 * @param string	$storage_name	Storage name
	 * @param string	$definition		Definition
	 * @return string					Definition value
	 */
	protected function get_new_definition($storage_name, $definition)
	{
		return $this->request->variable([$storage_name, $definition], '');
	}

	/**
	 * Validates data
	 *
	 * @param string	$storage_name	Storage name
	 * @param array		$errors			Reference to messages array
	 */
	protected function validate_data($storage_name, array &$errors)
	{
		$storage_title = $this->lang->lang('STORAGE_' . strtoupper($storage_name) . '_TITLE');

		// Check if provider exists
		try
		{
			$new_provider = $this->provider_collection->get_by_class($this->get_new_provider($storage_name));
		}
		catch (\Exception $e)
		{
			$errors[] = $this->lang->lang('STORAGE_PROVIDER_NOT_EXISTS', $storage_title);
			return;
		}

		// Check if provider is available
		if (!$new_provider->is_available())
		{
			$errors[] = $this->lang->lang('STORAGE_PROVIDER_NOT_AVAILABLE', $storage_title);
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
				/** @noinspection PhpMissingBreakStatementInspection */
				case 'email':
					if (!filter_var($value, FILTER_VALIDATE_EMAIL))
					{
						$errors[] = $this->lang->lang('STORAGE_FORM_TYPE_EMAIL_INCORRECT_FORMAT', $definition_title, $storage_title);
					}
				// no break;
				case 'text':
				case 'password':
					$maxlength = isset($definition_value['maxlength']) ? $definition_value['maxlength'] : 255;
					if (strlen($value) > $maxlength)
					{
						$errors[] = $this->lang->lang('STORAGE_FORM_TYPE_TEXT_TOO_LONG', $definition_title, $storage_title);
					}
				break;
				case 'radio':
				case 'select':
					if (!in_array($value, array_values($definition_value['options'])))
					{
						$errors[] = $this->lang->lang('STORAGE_FORM_TYPE_SELECT_NOT_AVAILABLE', $definition_title, $storage_title);
					}
				break;
			}
		}
	}

	/**
	 * Updates an storage with the info provided in the form
	 *
	 * @param string	$storage_name	Storage name
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
}
