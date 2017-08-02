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
* @todo add cron intervals to server settings? (database_gc, queue_interval, session_gc, search_gc, cache_gc, warnings_gc)
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
		global $phpbb_container;

		$this->config = $phpbb_container->get('config');
		$this->lang = $phpbb_container->get('language');
		$this->request = $phpbb_container->get('request');
		$this->template = $phpbb_container->get('template');
		$this->user = $phpbb_container->get('user');
		$this->provider_collection = $phpbb_container->get('storage.provider_collection');
		$this->storage_collection = $phpbb_container->get('storage.storage_collection');

		// Add necesary language files
		$this->lang->add_lang(array('acp/storage'));

		switch($mode)
		{
			case 'settings':
				$this->overview($id, $mode);
				break;
		}
	}

	// TODO: Validate data
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
			foreach ($this->storage_collection as $storage)
			{
				$modified = false;
				$provider = $this->provider_collection->get_by_class($this->config['storage\\' . $storage->get_name() . '\\provider']);

				// Check if provider have been modified
				if ($this->request->variable([$storage->get_name(), 'provider'], '') != $this->config['storage\\' . $storage->get_name() . '\\provider'])
				{
					$modified = true;
				}

				// Check if options have been modified
				if(!$modified)
				{
					foreach($provider->get_options() as $option => $params)
					{
						if ($this->request->variable([$storage->get_name(), $option], '') != $this->config['storage\\' . $storage->get_name() . '\\provider'])
						{
							$modified = true;
							break;
						}
					}
				}

				// Update storage
				if($modified)
				{
					// TODO: Allow to move data to the new storage automatically

					// TODO: Validate data

					// Remove old straoge config
					foreach (array_keys($provider->get_options()) as $def)
					{
						$this->config->delete('storage\\' . $storage->get_name() . '\\config\\' . $def);
					}

					// Update provider
					$this->config->set('storage\\' . $storage->get_name() . '\\provider', $this->request->variable([$storage->get_name(), 'provider'], ''));

					// Set new storage config
					$new_provider = $this->provider_collection->get_by_class($this->config['storage\\' . $storage->get_name() . '\\provider']);

					foreach (array_keys($new_provider->get_options()) as $def)
					{
						$this->config->set('storage\\' . $storage->get_name() . '\\config\\' . $def, $this->request->variable([$storage->get_name(), $def], ''));
					}
				}
			}

			// Updated succesfuly
		}

		$this->template->assign_vars(array(
			'STORAGES' => $this->storage_collection,
			'PROVIDERS' => $this->provider_collection,
			'CONFIG' => $this->config // Maybe this should be added to \phpbb\templat\twig\extension
		));
	}
}
