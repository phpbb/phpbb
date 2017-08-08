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

	/** @var string */
	public $page_title;

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container, $phpbb_dispatcher;

		$this->config = $phpbb_container->get('config');
		$this->lang = $phpbb_container->get('language');
		$this->request = $phpbb_container->get('request');
		$this->template = $phpbb_container->get('template');
		$this->user = $phpbb_container->get('user');
		$this->provider_collection = $phpbb_container->get('storage.provider_collection');
		$this->storage_collection = $phpbb_container->get('storage.storage_collection');

		// Add necesary language files
		$this->lang->add_lang(array('common'));
		$this->lang->add_lang(array('acp/storage'));

		/**
		 * Add language strings
		 *
		 * @event core.acp_storage_load
		 * @var array
		 * @since 3.3.0-a1
		 */
		$vars = array();
		extract($phpbb_dispatcher->trigger_event('core.acp_storage_load', compact($vars)));

		$this->overview($id, $mode);
	}

	public function overview($id, $mode)
	{
		$form_name = 'acp_storage';
		add_form_key($form_name);

		// Template from adm/style
		$this->tpl_name = 'acp_storage';

		// Set page title
		$this->page_title = 'STORAGE_TITLE';

		if ($this->request->is_set_post('submit'))
		{
			$modified_storages = [];
			$messages = [];

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
					foreach (array_keys($options) as $def)
					{
						if ($this->get_new_def($storage_name, $def) != $this->get_current_def($storage_name, $def))
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

			if (count($modified_storages))
			{
				if (!count($messages))
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

			// If there is no errors
			trigger_error($this->lang->lang('STORAGE_NO_CHANGES') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$this->template->assign_vars(array(
			'STORAGES' => $this->storage_collection,
			'PROVIDERS' => $this->provider_collection,
		));
	}

	protected function get_current_provider($storage_name)
	{
		return $this->config['storage\\' . $storage_name . '\\provider'];
	}

	protected function get_new_provider($storage_name)
	{
		return $this->request->variable([$storage_name, 'provider'], '');
	}

	protected function get_provider_options($provider)
	{
		return $this->provider_collection->get_by_class($provider)->get_options();
	}

	protected function get_current_def($storage_name, $def)
	{
		return $this->config['storage\\' . $storage_name . '\\config\\' . $def];
	}

	protected function get_new_def($storage_name, $def)
	{
		return $this->request->variable([$storage_name, $def], '');
	}

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

		foreach ($new_options as $def_k => $def_v)
		{
			$provider = $this->provider_collection->get_by_class($this->get_new_provider($storage_name));
			$def_title = $this->lang->lang('STORAGE_ADAPTER_' . strtoupper($provider->get_name()) . '_OPTION_' . strtoupper($def_k));

			$value = $this->get_new_def($storage_name, $def_k);

			switch ($def_v['type'])
			{
				case 'email':
					if (!filter_var($value, FILTER_VALIDATE_EMAIL))
					{
						$messages[] = $this->lang->lang('STORAGE_FORM_TYPE_EMAIL_INCORRECT_FORMAT', $def_title, $storage_title);
					}
				case 'text':
				case 'password':
					$maxlength = isset($def_v['maxlength']) ? $def_v['maxlength'] : 255;
					if (strlen($value) > $maxlength)
					{
						$messages[] = $this->lang->lang('STORAGE_FORM_TYPE_TEXT_TOO_LONG', $def_title, $storage_title);
					}
					break;
				case 'radio':
				case 'select':
					if (!in_array($value, array_values($def_v['options'])))
					{
						$messages[] = $this->lang->lang('STORAGE_FORM_TYPE_SELECT_NOT_AVAILABLE', $def_title, $storage_title);
					}
					break;
			}
		}
	}

	protected function update_storage_config($storage_name)
	{
		$current_options = $this->get_provider_options($this->get_current_provider($storage_name));

		// Remove old storage config
		foreach (array_keys($current_options) as $def)
		{
			$this->config->delete('storage\\' . $storage_name . '\\config\\' . $def);
		}

		// Update provider
		$this->config->set('storage\\' . $storage_name . '\\provider', $this->get_new_provider($storage_name));

		// Set new storage config
		$new_options = $this->get_provider_options($this->get_new_provider($storage_name));

		foreach (array_keys($new_options) as $def)
		{
			$this->config->set('storage\\' . $storage_name . '\\config\\' . $def, $this->get_new_def($storage_name, $def));
		}
	}
}
