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

class manager
{
	/** @var \phpbb\di\service_collection */
	protected $acp_collection;

	/** @var \phpbb\di\service_collection */
	protected $mcp_collection;

	/** @var \phpbb\di\service_collection */
	protected $ucp_collection;

	/** @var \phpbb\acp\helper\constructor */
	protected $acp_constructor;

	/** @var \phpbb\cp\helper\auth */
	protected $cp_auth;

	/** @var \phpbb\cp\helper\language */
	protected $cp_lang;

	/** @var array Control panel route prefixes */
	protected $route_prefixes = [
		'acp'	=> '/admin',
		'mcp'	=> '/mod',
		'ucp'	=> '/user',
	];

	/** @var string Control panel pagination route suffix */
	protected $route_pagination = '_pagination';

	public function __construct(
		\phpbb\di\service_collection $acp_collection,
		\phpbb\acp\helper\constructor $acp_constructor,
		helper\auth $cp_auth,
		helper\language $cp_lang
	)
	{
		$this->acp_collection	= $acp_collection;
		$this->acp_constructor	= $acp_constructor;

		$this->cp_auth			= $cp_auth;
		$this->cp_lang			= $cp_lang;
	}

	/**
	 * Get the control panel routes' pagination suffix.
	 *
	 * @return string				The pagination route suffix
	 */
	public function get_route_pagination()
	{
		return $this->route_pagination;
	}

	/**
	 * Get the control panel's route prefix.
	 *
	 * @param string	$cp			The control panel's route prefix
	 * @return string
	 */
	public function get_route_prefix($cp)
	{
		return isset($this->route_prefixes[$cp]) ? (string) $this->route_prefixes[$cp] : '';
	}

	/**
	 * Get the control panels' menu items collections.
	 *
	 * @return array				The control panels item collections.
	 */
	public function get_collections()
	{
		return [
			'acp'	=> $this->acp_collection,
		#	'mcp'	=> $this->mcp_collection, // @todo
		#	'ucp'	=> $this->ucp_collection, // @todo
		];
	}

	/**
	 * Get a control panel's menu items collection.
	 *
	 * @return \phpbb\di\service_collection
	 */
	public function get_collection($cp)
	{
		return $this->{$cp . '_collection'};
	}

	/**
	 * Get a control panel's constructor.
	 *
	 * @return constructor_interface
	 */
	public function get_constructor($cp)
	{
		return $this->{$cp . '_constructor'};
	}

	/**
	 * Set up a control panel.
	 *
	 * Check authentication for accessed item and its parents.
	 *
	 * @param string	$cp			The control panel (acp|mcp|ucp)
	 * @param string	$route		The accessed control panel route
	 * @return void
	 */
	public function setup_cp($cp, $route)
	{
		$collection = $this->get_collection($cp);

		$item = $collection->offsetGet($route);

		do
		{
			if ($this->cp_auth->check_auth($item->get_auth()) === false)
			{
				throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED');
			}

			if ($item->get_parent() && $collection->offsetExists($item->get_parent()))
			{
				$item = $collection->offsetGet($item->get_parent());
			}
			else
			{
				$item = null;
			}
		}
		while ($item !== null);

		$this->cp_lang->load_cp_language_files($cp);

		$this->get_constructor($cp)->setup();
	}
}
