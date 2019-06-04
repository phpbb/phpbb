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

	/** @var \phpbb\mcp\helper\constructor */
	protected $mcp_constructor;

	/** @var \phpbb\ucp\helper\constructor */
	protected $ucp_constructor;

	/** @var \phpbb\cp\helper\auth */
	protected $cp_auth;

	/** @var \phpbb\cp\helper\identifiers */
	protected $cp_ids;

	/** @var \phpbb\cp\helper\language */
	protected $cp_lang;

	/** @var array Control panel route prefixes */
	protected $route_prefixes = [
		'acp' => '/admin',
		'mcp' => '/mod',
		'ucp' => '/user',
	];

	/** @var string Control panel pagination route suffix */
	protected $route_pagination = '_pagination';

	/**
	 * Constructor.
	 *
	 * @param \phpbb\di\service_collection		$acp_collection		ACP Menu item collection
	 * @param \phpbb\di\service_collection		$mcp_collection		MCP Menu item collection
	 * @param \phpbb\di\service_collection		$ucp_collection		UCP Menu item collection
	 * @param \phpbb\acp\helper\constructor		$acp_constructor	ACP Construct object
	 * @param \phpbb\mcp\helper\constructor		$mcp_constructor	MCP Construct object
	 * @param \phpbb\ucp\helper\constructor		$ucp_constructor	UCP Construct object
	 * @param \phpbb\cp\helper\auth				$cp_auth			Control panel auth object
	 * @param \phpbb\cp\helper\identifiers		$cp_ids				Control panel identifiers object
	 * @param \phpbb\cp\helper\language			$cp_lang			Control panel language object
	 */
	public function __construct(
		\phpbb\di\service_collection $acp_collection,
		\phpbb\di\service_collection $mcp_collection,
		\phpbb\di\service_collection $ucp_collection,
		\phpbb\acp\helper\constructor $acp_constructor,
		\phpbb\mcp\helper\constructor $mcp_constructor,
		\phpbb\ucp\helper\constructor $ucp_constructor,
		helper\auth $cp_auth,
		helper\identifiers $cp_ids,
		helper\language $cp_lang
	)
	{
		$this->acp_collection	= $acp_collection;
		$this->mcp_collection	= $mcp_collection;
		$this->ucp_collection	= $ucp_collection;
		$this->acp_constructor	= $acp_constructor;
		$this->mcp_constructor	= $mcp_constructor;
		$this->ucp_constructor	= $ucp_constructor;

		$this->cp_auth			= $cp_auth;
		$this->cp_ids			= $cp_ids;
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
			'acp' => $this->acp_collection,
			'mcp' => $this->mcp_collection,
			'ucp' => $this->ucp_collection,
		];
	}

	/**
	 * Get a control panel's menu items collection.
	 *
	 * @param string	$cp			The control panel (acp|mcp|ucp)
	 * @return \phpbb\di\service_collection
	 */
	public function get_collection($cp)
	{
		return $this->{$cp . '_collection'};
	}

	/**
	 * Get a control panel's constructor.
	 *
	 * @param string	$cp			The control panel (acp|mcp|ucp)
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

		$this->cp_ids->get_identifiers($cp);

		do
		{
			$s_auth = $this->cp_auth->check_auth(
				$item->get_auth(),
				$this->cp_ids->get_forum_id(),
				$this->cp_ids->get_topic_id(),
				$this->cp_ids->get_post_id()
			);

			if ($s_auth === false)
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
