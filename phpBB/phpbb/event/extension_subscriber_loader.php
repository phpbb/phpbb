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

use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

class extension_subscriber_loader
{
	/**
	* @var \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
	*/
	protected $dispatcher;

	/**
	* @var \phpbb\di\service_collection
	*/
	protected $listener_collection;

	/**
	* @var \phpbb\cache\service
	*/
	protected $cache;

	/**
	* Constructor
	*
	* @param ContainerAwareEventDispatcher $dispatcher
	* @param \phpbb\di\service_collection  $listener_collection
	* @param \phpbb\cache\service          $cache
	*/
	public function __construct(ContainerAwareEventDispatcher $dispatcher, \phpbb\di\service_collection $listener_collection, \phpbb\cache\service $cache)
	{
		$this->dispatcher = $dispatcher;
		$this->listener_collection = $listener_collection;
		$this->cache = $cache;
	}

	/**
	* Registers the listeners into the event dispatcher.
	*/
	public function load()
	{
		if (!empty($this->listener_collection))
		{
			$subscribers = $this->cache->get('events_subscribers_data');
			if ($subscribers === false)
			{
				$subscribers = array();
				foreach ($this->listener_collection as $subscriber_id => $subscriber)
				{
					foreach ($subscriber->getSubscribedEvents() as $event_name => $params)
					{
						if (is_string($params))
						{
							$subscribers[$subscriber_id][$event_name] = array($params => 0);
						}
						elseif (is_string($params[0]))
						{
							$subscribers[$subscriber_id][$event_name] = array($params[0] => isset($params[1]) ? $params[1] : 0);
						}
						else
						{
							$subscribers[$subscriber_id][$event_name] = array();
							foreach ($params as $listener)
							{
								$subscribers[$subscriber_id][$event_name][$listener[0]] = isset($listener[1]) ? $listener[1] : 0;
							}
						}
					}
				}

				$this->cache->put('events_subscribers_data', $subscribers);
			}

			foreach ($subscribers as $subscriber_id => $events)
			{
				foreach ($events as $event_name => $listeners)
				{
					foreach ($listeners as $method => $priority)
					{
						$this->dispatcher->addListenerService($event_name, array($subscriber_id, $method), $priority);
					}
				}
			}
		}
	}
}
