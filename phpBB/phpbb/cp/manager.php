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
	/** @var array */
	protected $acp_collection;

	/** @var array */
	protected $mcp_collection;

	/** @var array */
	protected $ucp_collection;

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
	 * @param array		$acp_collection		ACP Menu item collection
	 * @param array		$mcp_collection		MCP Menu item collection
	 * @param array		$ucp_collection		UCP Menu item collection
	 */
	public function __construct(array $acp_collection, array $mcp_collection, array $ucp_collection)
	{
		$this->acp_collection	= $acp_collection;
		$this->mcp_collection	= $mcp_collection;
		$this->ucp_collection	= $ucp_collection;
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
}
