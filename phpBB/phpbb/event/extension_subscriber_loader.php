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

namespace phpbb\event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class extension_subscriber_loader
{
	private $dispatcher;
	private $listener_collection;

	public function __construct(EventDispatcherInterface $dispatcher, \phpbb\di\service_collection $listener_collection)
	{
		$this->dispatcher = $dispatcher;
		$this->listener_collection = $listener_collection;
	}

	public function load()
	{
		if (!empty($this->listener_collection))
		{
			foreach ($this->listener_collection as $listener)
			{
				$this->dispatcher->addSubscriber($listener);
			}
		}
	}
}
