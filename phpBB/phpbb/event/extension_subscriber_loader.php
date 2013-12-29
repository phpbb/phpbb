<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
