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
	protected $acp_collection;
	protected $mcp_collection;
	protected $ucp_collection;

	protected $acp_constructor;

	protected $prefixes = [
		'acp'	=> '/admin',
		'mcp'	=> '/mod',
		'ucp'	=> '/user',
	];

	public function __construct(
		\phpbb\di\service_collection $acp_collection,
		\phpbb\acp\helper\constructor $acp_constructor
	)
	{
		$this->acp_collection	= $acp_collection;
		$this->acp_constructor	= $acp_constructor;
	}

	public function get_route_pagination()
	{
		return '_pagination';
	}

	public function get_route_prefix($cp)
	{
		return isset($this->prefixes[$cp]) ? (string) $this->prefixes[$cp] : '';
	}

	public function get_collections()
	{
		return [
			'acp'	=> $this->acp_collection,
		#	'mcp'	=> $this->mcp_collection, // @todo
		#	'ucp'	=> $this->ucp_collection, // @todo
		];
	}

	/**
	 * @return \phpbb\di\service_collection
	 */
	public function get_collection($cp)
	{
		return $this->{$cp . '_collection'};
	}

	public function get_constructor($cp)
	{
		return $this->{$cp . '_constructor'};
	}

	public function setup_cp($cp, $route)
	{
		$collection = $this->get_collection($cp);

		$item = $collection->offsetGet($route);

		$s_auth = true;
		$active = [$route];

		do
		{
			$auth = $item->get_auth();

			// Preserve "false"
			$s_auth = !$s_auth ? $s_auth : $auth;

			if ($item->get_parent() && $collection->offsetExists($item->get_parent()))
			{
				$active[] = $item->get_parent();

				$item = $collection->offsetGet($item->get_parent());
			}
			else
			{
				$item = null;
			}
		}
		while ($item !== null);

		if ($s_auth)
		{
			# @todo language from extensions
			$this->get_constructor($cp)->setup();
		}
		else
		{
			throw new \phpbb\exception\http_exception(403, 'NOT_AUTHORISED');
		}
	}
}
