<?php

namespace phpbb\cp;

class manager
{
	protected $acp_collection;

	protected $cache;

	protected $config;

	protected $dispatcher;

	protected $extension_manager;

	protected $language;

	protected $panel_admin;

	protected $template;

	protected $user;

	protected $class;

	/** @var \phpbb\cp\panel\admin */
	protected $panel;

	public function __construct(
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\config\config $config,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\extension\manager $extension_manager,
		\phpbb\language\language $language,
		panel\admin $panel_admin,
		\phpbb\template\template $template,
		\phpbb\user $user
	)
	{
		$this->cache				= $cache;
		$this->config				= $config;
		$this->dispatcher			= $dispatcher;
		$this->extension_manager	= $extension_manager;
		$this->language				= $language;
		$this->panel_admin			= $panel_admin;
		$this->template				= $template;
		$this->user					= $user;
	}

	public function build($class, $active)
	{
		switch ($class)
		{
			case 'acp':
				$this->panel = $this->panel_admin;
			break;

			case 'mcp':

			break;

			case 'ucp':

			break;

			default:
				throw new \phpbb\exception\http_exception(404, 'CP_NOT_EXIST');
			break;
		}

		$this->class = (string) $class;

		# Panel language
		$this->include_language();

		# Panel build
		$this->panel->build();

		# Panel menu
		$authorised = $this->build_menu($active);

		if (!$authorised)
		{
			throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED');
		}
	}

	protected function build_menu($active)
	{
		$class = $this->class;

		if (!($menu = $this->cache->get('_' . $class . '_menu')))
		{
			$menu = $this->panel->get_menu();

			/**
			 * Event to add or modify CP menu items
			 *
			 * @event core.cp_menu_modify
			 * @var string	class	The control panel class (acp|mcp|ucp)
			 * @var	array	menu	The control panel menu array
			 * @since 4.0.0 @todo
			 */
			$vars = array('class', 'menu');
			extract($this->dispatcher->trigger_event('core.cp_menu_modify', compact($vars)));
		}

		$collection = $this->panel->get_collection();

		$s_auth = false;

		foreach ($menu as $category_service => $subcategories)
		{
			/** @var \phpbb\cp\info\base $category */
			$category = $collection[$category_service];

			$category_auth = $category->get_auth();

			if ($category_service === $active)
			{
				$s_auth = $category_auth;
			}

			if (!$category_auth)
			{
				continue;
			}

			$category_children = [];
			$category_selected = false;

			foreach ($subcategories as $subcategory_service => $items)
			{
				/** @var \phpbb\cp\info\base $subcategory */
				$subcategory = $collection[$subcategory_service];

				$subcategory_auth = $subcategory->get_auth();

				if ($subcategory_service === $active)
				{
					$s_auth = $subcategory_auth;
				}

				if (!$subcategory_auth)
				{
					continue;
				}

				$subcategory_children = [];
				$subcategory_selected = false;

				if ($items !== null)
				{
					foreach ($items as $item_service)
					{
						/** @var \phpbb\cp\info\base $item */
						$item = $collection[$item_service];

						$item_auth = $item->get_auth();

						if ($item_service === $active)
						{
							$s_auth = $item_auth;
						}

						if (!$item_auth)
						{
							continue;
						}

						$item_selected = $item_service === $active;
						$subcategory_selected = $subcategory_selected ? $subcategory_selected : $item_selected;

						$subcategory_children[] = $this->get_template_variables($item, $item_selected);
					}
				}

				$subcategory_selected = $subcategory_service === $active || $subcategory_selected;
				$category_selected = $category_selected ? $category_selected : $subcategory_selected;

				$subcategory_vars = $this->get_template_variables($subcategory, $subcategory_selected);

				if ($subcategory_children)
				{
					$subcategory_vars['CHILDREN'] = $subcategory_children;
				}

				$category_children[] = $subcategory_vars;
			}

			$category_selected = $category_service === $active || $category_selected;
			$category_vars = $this->get_template_variables($category, $category_selected);

			if ($category_children)
			{
				$category_vars['CHILDREN'] = $category_children;

				if ($category_selected)
				{
					$this->template->assign_block_vars_array($this->class . '_menu_items', $category_children);
				}
			}

			$this->template->assign_block_vars($this->class . '_menu', $category_vars);
		}

		return $s_auth;
	}

	/**
	 * X
	 *
	 * @param \phpbb\cp\info\base	$item
	 * @param bool					$selected
	 * @return array
	 * @access protected
	 */
	protected function get_template_variables($item, $selected)
	{
		return [
			'TITLE'			=> $item->get_title(),
			'S_SELECTED'	=> $selected,
			'U_VIEW'		=> $item->get_route(),
		];
	}

	protected function include_language()
	{
		switch ($this->class)
		{
			case 'acp':
				$this->language->add_lang('acp/common');
			break;

			case 'mcp':
			case 'ucp':
				$this->language->add_lang($this->class);
			break;
		}

		$files = [];

		$finder = $this->extension_manager->get_finder();

		$language_array = array_unique([
			$this->user->data['user_lang'],
			$this->config['default_lang'],
			\phpbb\language\language::FALLBACK_LANGUAGE,
		]);

		foreach ($language_array as $language_iso)
		{
			$files += $finder
				->prefix('info_' . $this->class . '_')
				->suffix('.php')
				->extension_directory('language/' . $language_iso . '/')
				->find();
		}

		foreach ($files as $language_file => $extension_name)
		{
			$this->language->add_lang($language_file, $extension_name);
		}
	}
}
