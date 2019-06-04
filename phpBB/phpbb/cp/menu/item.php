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

class item implements item_interface
{
	/** @var string Item's auth */
	protected $auth;

	/** @var string Item's icon */
	protected $icon;

	/** @var string|array Item's route */
	protected $route;

	/** @var string Item's parent */
	protected $parent;

	/** @var string Item's "before" sibling */
	protected $before;

	/** @var string Item's pagination "page" variable */
	protected $page;

	/** @var bool Item's display property */
	protected $display;

	/**
	 * Constructor.
	 *
	 * @param string		$auth		Item's auth
	 * @param string		$icon		Item's icon
	 * @param string|array	$route		Item's route
	 * @param string		$parent		Item's parent
	 * @param string		$before		Item's before sibling
	 * @param string		$page		Item's pagination variable
	 */
	public function __construct($auth = '', $icon = '', $route = '', $parent = '', $before = '', $page = '', $display = true)
	{
		$this->auth 	= $auth;
		$this->icon		= $icon;
		$this->route	= $route;
		$this->parent	= $parent;
		$this->before	= $before;
		$this->page		= $page;
		$this->display	= $display;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_auth()
	{
		return $this->auth;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_icon()
	{
		return $this->icon;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_parent()
	{
		return $this->parent;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_before()
	{
		return $this->before;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_route()
	{
		return $this->route;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page()
	{
		return $this->page;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_display()
	{
		return $this->display;
	}
}
