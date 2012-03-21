<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
* Wrapper around a Symfony2 EventDispatcherInterface
*
* It provides an additional `trigger_event` method, which
* gives some syntactic sugar for dispatching events. Instead
* of creating the event object, the method will do that for
* you.
*
* Example:
*
*     $vars = array('page_title');
*     extract($phpbb_dispatcher->trigger_event('core.index', compact($vars)));
*
* Apart from that it implements the EventDispatcherInterface
* and proxies all method calls to the member dispatcher.
*/
class phpbb_event_dispatcher_wrapper implements EventDispatcherInterface
{
	private $dispatcher;

	public function __construct(EventDispatcherInterface $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	public function dispatch($eventName, Event $event = null)
	{
		$this->dispatcher->dispatch($eventName, $event);
	}

	public function addListener($eventName, $listener, $priority = 0)
	{
		$this->dispatcher->addListener($eventName, $listener, $priority);
	}

	public function addSubscriber(EventSubscriberInterface $subscriber)
	{
		$this->dispatcher->addSubscriber($subscriber);
	}

	public function removeListener($eventName, $listener)
	{
		$this->dispatcher->removeListener($eventName, $listener);
	}

	public function removeSubscriber(EventSubscriberInterface $subscriber)
	{
		$this->dispatcher->removeSubscriber($subscriber);
	}

	public function getListeners($eventName = null)
	{
		return $this->dispatcher->getListeners($eventName);
	}

	public function hasListeners($eventName = null)
	{
		return $this->dispatcher->hasListeners($eventName);
	}

	public function trigger_event($eventName, $data = array())
	{
		$event = new phpbb_event_data($data);
		$this->dispatch($eventName, $event);
		return $event->get_data_filtered(array_keys($data));
	}
}
