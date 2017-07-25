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

		// Add necesary language files
		$this->user->add_lang(array('acp/storage'));

		switch($mode)
		{
			case 'settings':
				$this->overview($id, $mode);
				break;
		}
	}

	public function overview($id, $mode)
	{
		$form_name = 'acp_storage';
		add_form_key($form_name);

		global $phpbb_container;
		$storage_collection = $phpbb_container->get('storage.storage_collection');
		$adapter_provider_collection = $phpbb_container->get('storage.provider_collection');

		$storages = array();

		foreach($storage_collection->getIterator() as $storage)
		{
			$this->template->assign_block_vars('storage', array(
				'LEGEND' => $storage->get_name(),
				'TITLE' => $storage->get_name(),
				'TITLE_EXPLAIN' => $storage->get_description(),
				'OPTIONS' => $this->generate_adapter_options(),
			));

			foreach($adapter_provider_collection as $provider)
			{
				if(!$provider->is_available())
				{
					continue;
				}

				$this->template->assign_block_vars('storage.adapter', array(
					'NAME' => get_class($provider),
					'SETTINGS' => print_r($provider->get_options(), 1),
				));
			}
		}

		// Template from adm/style
		$this->tpl_name = 'acp_storage';

		// Set page title
		$this->page_title = 'STORAGE_TITLE';

		$this->template->assign_vars(array(
		));
	}

	protected function generate_adapter_options()
	{
		global $phpbb_container;
		$adapter_provider_collection = $phpbb_container->get('storage.provider_collection');

		$options = '';

		foreach($adapter_provider_collection as $provider)
		{
			$class = get_class($provider);
			$options .= "<option value=\"$class\" data-toggle-setting=\"\">$class</option>";
		}

		return $options;
	}
}
