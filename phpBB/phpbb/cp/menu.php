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

namespace phpbb\cp;

class menu
{
	/** @var helper\auth */
	protected $cp_auth;

	/** @var helper\identifiers */
	protected $cp_ids;

	/** @var manager */
	protected $cp_manager;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\request\request */
	protected $request;

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

	/**
	 * Constructor.
	 *
	 * @param \phpbb\cp\helper\auth				$cp_auth			Control panel auth object
	 * @param \phpbb\cp\manager					$cp_manager			Control panel manager object
	 * @param \phpbb\cp\helper\identifiers		$cp_ids				Control panel identifiers object
	 * @param \phpbb\controller\helper			$helper				Controller helper object
	 * @param \phpbb\language\language			$lang				Language object
	 * @param \phpbb\request\request			$request			Request object
	 * @param \phpbb\symfony_request			$symfony_request	Symfony request object
	 * @param \phpbb\template\template			$template			Template object
	 */
	public function __construct(
		helper\auth $cp_auth,
		helper\identifiers $cp_ids,
		manager $cp_manager,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
		\phpbb\request\request $request,
		\phpbb\symfony_request $symfony_request,
		\phpbb\template\template $template
	)
	{
		$this->cp_auth			= $cp_auth;
		$this->cp_ids			= $cp_ids;
		$this->cp_manager		= $cp_manager;
		$this->helper			= $helper;
		$this->lang				= $lang;
		$this->request			= $request;
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

		// Get identifiers for item authentication
		$this->cp_ids->get_identifiers($cp);

		// Build a menu, all items indexed per parent
		$this->build_menu();

		// Build all active items, the accessed item and its parents
		$this->build_actives();

		// Build the navigation menu tree
		$menu = $this->build_menu_tree();

		// Assign the menu to the template
		$this->template->assign_block_vars_array("{$cp}_menu", $menu);

		if (!empty($this->actives))
		{
			// Get the active category
			$category = end($this->actives);

			if (isset($menu[$category]))
			{
				// Assign the active category to the template
				$this->template->assign_vars(["{$cp}_menu_active" => $menu[$category]]);
			}
		}
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

		if (array_key_exists($route, $this->collection))
		{
			$item = $this->collection[$route];

			$this->actives[] = $route;

			while ($item !== null)
			{
				$parent = $item['parent'];

				if ($parent && array_key_exists($parent, $this->collection))
				{
					$this->actives[] = $parent;

					$item = $this->collection[$parent];
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
			$before = $item['before'];
			$parent = $item['parent'];

			if (!empty($this->items[$parent][$before]))
			{
				$this->insert_item_before($name, $item);
			}
			else
			{
				$this->items[$parent][$name] = $item;
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

		foreach ($this->items[$parent] as $name => $item)
		{
			// If the authorisation requirements are met
			if ($this->cp_auth->check_auth(
				$item['auth'],
				$this->cp_ids->get_forum_id(),
				$this->cp_ids->get_topic_id(),
				$this->cp_ids->get_post_id()
			))
			{
				$route = $item['route'];

				// Is this a category?
				$s_category = (bool) is_string($route);

				// Set up this item's template variables
				$variables = $this->get_item_variables($name, $item);

				// Does this item have any children?
				if (!empty($this->items[$name]))
				{
					// Iterate over all the children
					$variables['ITEMS'] = $this->build_menu_tree($name);

					// If this is a category and the categories pre-defined route is not available
					if ($s_category && empty($variables['ITEMS'][$route]))
					{
						// Get the first child of the category
						$first = reset($variables['ITEMS']);

						// Set the first child's route as the category's route
						$variables['U_VIEW'] = $first['U_VIEW'];
					}
				}

				// If it's not a category or the category has children
				// And the display is set to true, or if it's in the active items
				if ((!$s_category || !empty($variables['ITEMS'])) &&
					($item['display'] || in_array($name, $this->actives)))
				{
					// Add it the to menu
					$menu[$name] = $variables;
				}
			}
		}

		return $menu;
	}

	/**
	 * Get the menu item's template variables.
	 *
	 * @param string	$name		The menu item name
	 * @param array		$item		The menu item
	 * @return array
	 */
	protected function get_item_variables($name, array $item)
	{
		$route = $item['route'];

		$s_category = (bool) is_string($route);

		$u_view = '';

		if ($route !== '')
		{
			$route = $s_category ? $route : $name;

			$u_view = $this->helper->route($route, $this->cp_ids->get_params($this->panel));
		}

		return [
			'TITLE'		=> $this->lang->lang(utf8_strtoupper($name)),
			'S_ACTIVE'	=> in_array($name, $this->actives),
			'S_CAT'		=> $s_category,
			'S_PM_VIEW'	=> $name === 'ucp_pm_view',
			'U_VIEW'	=> $u_view,
		];
	}

	/**
	 * Insert a menu item before a specific item.
	 *
	 * @param string	$name		The menu item name
	 * @param array		$item		The menu item
	 * @return void
	 */
	protected function insert_item_before($name, array $item)
	{
		$parent = $item['parent'];
		$before = $item['before'];

		$index = array_search($before, array_keys($this->items[$parent]));

		$this->items[$parent] = array_merge(
			array_slice($this->items[$parent], 0, $index),
			[$name => $item],
			array_slice($this->items[$parent], $index)
		);
	}
}
