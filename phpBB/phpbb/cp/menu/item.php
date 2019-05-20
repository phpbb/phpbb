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

class item
{
	protected $auth;
	protected $icon;
	protected $route;
	protected $parent;
	protected $before;
	protected $page;

	public function __construct($auth = '', $icon = '', $route = '', $parent = '', $before = '', $page = '')
	{
		$this->auth 	= $auth;
		$this->icon		= $icon;
		$this->route	= $route;
		$this->parent	= $parent;
		$this->before	= $before;
		$this->page		= $page;
	}

	public function get_auth()
	{
		return $this->auth;
	}

	public function get_icon()
	{
		return $this->icon;
	}

	public function get_parent()
	{
		return $this->parent;
	}

	public function get_before()
	{
		return $this->before;
	}

	public function get_route()
	{
		return $this->route;
	}

	public function get_page()
	{
		return $this->page;
	}
}
