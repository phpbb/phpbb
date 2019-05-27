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

namespace phpbb\cp\menu;

class menu
{
	/** @var \phpbb\cp\helper\auth */
	protected $cp_auth;

	/** @var \phpbb\cp\manager */
	protected $cp_manager;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\symfony_request */
	protected $symfony_request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\di\service_collection */
	protected $collection;

	/** @var array The control panel active menu items */
	protected $actives;

	/** @var array The control panel menu items */
	protected $items;

	/** @var string The control panel type (acp|mcp|ucp) */
	protected $panel;

	/** @var string The ACP extensions category service definition */
	protected $extensions_category = 'acp_cat_extensions';

	/**
	 * Constructor.
	 *
	 * @param \phpbb\cp\helper\auth		$cp_auth				Control panel auth object
	 * @param \phpbb\cp\manager			$cp_manager				Control panel manager object
	 * @param \phpbb\controller\helper	$helper					Controller helper object
	 * @param \phpbb\language\language	$lang					Language object
	 * @param \phpbb\symfony_request	$symfony_request		Symfony request object
	 * @param \phpbb\template\template	$template				Template object
	 */
	public function __construct(
		\phpbb\cp\helper\auth $cp_auth,
		\phpbb\cp\manager $cp_manager,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
		\phpbb\symfony_request $symfony_request,
		\phpbb\template\template $template
	)
	{
		$this->cp_auth			= $cp_auth;
		$this->cp_manager		= $cp_manager;
		$this->helper			= $helper;
		$this->lang				= $lang;
		$this->symfony_request	= $symfony_request;
		$this->template			= $template;
	}

	/**
	 * Build a control panel.
	 *
	 * @param string	$cp			The control panel type (acp|mcp|ucp)
	 * @return void
	 */
	public function build($cp)
	{
		// Reset the menu items
		$this->actives		= [];
		$this->items		= [];

		// Set the control panel type
		$this->panel = $cp;

		// Get the menu items collection
		$this->collection = $this->cp_manager->get_collection($cp);

		// Build a menu, all items indexed per parent
		$this->build_menu();

		// Build all active items, the accessed item and its parents
		$this->build_actives();

		// Build the navigation menu tree
		$menu = $this->build_menu_tree();

		// Handle the initial "empty" extensions category
		$menu = $this->update_extensions_category_route($menu);

		// Assign the menu to the template
		$this->template->assign_block_vars_array("{$cp}_menu", $menu);
	}

	/**
	 * Build the active control panel menu items array.
	 *
	 * The accessed menu item and its parents are added to the array,
	 * where the active control panel category is the last value.
	 *
	 * @return void
	 */
	protected function build_actives()
	{
		$route = $this->symfony_request->get('_route');
		$route = str_replace($this->cp_manager->get_route_pagination(), '', $route);

		if ($this->collection->offsetExists($route))
		{
			$item = $this->collection[$route];

			$this->actives[] = $route;

			while ($item !== null)
			{
				$parent = $item->get_parent();

				if ($this->collection->offsetExists($parent))
				{
					$this->actives[] = $parent;

					$item = $this->collection->offsetGet($parent);
				}
				else
				{
					$item = null;
				}
			}
		}
	}

	/**
	 * Build the control panel menu items.
	 *
	 * Index all items per parent and
	 * check if an item should be inserted before a specific item.
	 *
	 * @return void
	 */
	protected function build_menu()
	{
		foreach ($this->collection as $name => $item)
		{
			if (!empty($this->items[$item->get_parent()][$item->get_before()]))
			{
				$this->insert_item_before($name, $item);
			}
			else
			{
				$this->items[$item->get_parent()][$name] = $item;
			}
		}
	}

	/**
	 * Build the control panel navigation menu.
	 *
	 * Iterate over the items for the specified parent.
	 * The default parent '', is for the top level categories.
	 *
	 * This checks permissions and creates the template variables per item.
	 *
	 * @param string	$parent			The menu item parent
	 * @return array					The menu items belonging the the specified parent
	 */
	protected function build_menu_tree($parent = '')
	{
		$menu = [];

		/** @var item $item */
		foreach ($this->items[$parent] as $name => $item)
		{
			if ($this->cp_auth->check_auth($item->get_auth()))
			{
				$variables = $this->get_item_variables($name, $item);

				if (!empty($this->items[$name]))
				{
					$variables['ITEMS'] = $this->build_menu_tree($name);
				}

				$menu[$name] = $variables;

				if ($parent === '' && $name === end($this->actives))
				{
					$this->template->assign_vars(["{$this->panel}_menu_active" => $variables]);
				}
			}
		}

		return $menu;
	}

	/**
	 * Get the menu item's template variables.
	 *
	 * @param string	$name		The menu item name (service definition)
	 * @param item		$item		The menu item object
	 * @return array
	 */
	protected function get_item_variables($name, $item)
	{
		$s_category = (bool) is_string($item->get_route());
		$s_extension = (bool) ($name === $this->extensions_category);

		return [
			'ICON'		=> $item->get_icon(),
			'TITLE'		=> $this->lang->lang(utf8_strtoupper($name)),
			'S_CAT'		=> $s_category,
			'S_ACTIVE'	=> in_array($name, $this->actives),
			'U_VIEW'	=> !$s_extension ? $this->helper->route($s_category ? $item->get_route() : $name) : '',
		];
	}

	/**
	 * Insert a menu item before a specific item.
	 *
	 * @param string	$name		The menu item name (service definition)
	 * @param item		$item		The menu item object
	 * @return void
	 */
	protected function insert_item_before($name, $item)
	{
		$parent = $item->get_parent();
		$before = $item->get_before();

		$index = array_search($before, array_keys($this->items[$parent]));

		$this->items[$parent] = array_merge(
			array_slice($this->items[$parent], 0, $index),
			[$name => $item],
			array_slice($this->items[$parent], $index)
		);
	}

	/**
	 * Update the initial "empty" extensions category route.
	 *
	 * @param array		$menu		The control panel navigation menu
	 * @return array
	 */
	protected function update_extensions_category_route(array $menu)
	{
		if (!empty($menu[$this->extensions_category]['ITEMS']))
		{
			$items = $menu[$this->extensions_category]['ITEMS'];
			$item = reset($items);

			$menu[$this->extensions_category]['U_VIEW'] = $item['U_VIEW'];
		}

		return $menu;
	}
}
