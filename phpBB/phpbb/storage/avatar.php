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

namespace phpbb\storage;

class storage extends abstract_storage
{
	public function __construct(\phpbb\event\dispatcher_interface $dispatcher)
	{
		$adapter = \phpbb\storage\adapter\local;
		$params = array();

		$vars = array('adapter', 'params');
		extract($dispatcher->trigger_event('core.avatar_storage', compact($vars)));

		$this->adapter = $adapter($params);
	}
}
