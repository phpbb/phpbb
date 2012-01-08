<?php
/**
*
* @package phpBB3
* @copyright (c) Fabien Potencier <fabien@symfony.com>
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

/**
 * This file has been taken from Symfony2 and adjusted for
 * phpBB's coding standards.
 */

/**
 * The EventDispatcherInterface is the central point of Symfony's event listener system.
 *
 * Listeners are registered on the manager and events are dispatched through the
 * manager.
 *
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Bernhard Schussek <bschussek@gmail.com>
 * @author  Fabien Potencier <fabien@symfony.com>
 * @author  Jordi Boggiano <j.boggiano@seld.be>
 * @author  Jordan Alliot <jordan.alliot@gmail.com>
 */
class phpbb_event_dispatcher
{
	private $listeners = array();
	private $sorted = array();

	/**
	 * @see EventDispatcherInterface::dispatch
	 *
	 * @api
	 */
	public function dispatch($event_name, phpbb_event $event = null)
	{
		if (!isset($this->listeners[$event_name])) {
			return;
		}

		if (null === $event) {
			$event = new phpbb_event();
		}

		$event->set_dispatcher($this);
		$event->set_name($event_name);

		$this->do_dispatch($this->get_listeners($event_name), $event_name, $event);
	}

	/**
	 * @see EventDispatcherInterface::get_listeners
	 */
	public function get_listeners($event_name = null)
	{
		if (null !== $event_name) {
			if (!isset($this->sorted[$event_name])) {
				$this->sort_listeners($event_name);
			}

			return $this->sorted[$event_name];
		}

		foreach (array_keys($this->listeners) as $event_name) {
			if (!isset($this->sorted[$event_name])) {
				$this->sort_listeners($event_name);
			}
		}

		return $this->sorted;
	}

	/**
	 * @see EventDispatcherInterface::has_listeners
	 */
	public function has_listeners($event_name = null)
	{
		return (Boolean) count($this->get_listeners($event_name));
	}

	/**
	 * @see EventDispatcherInterface::add_listener
	 *
	 * @api
	 */
	public function add_listener($event_name, $listener, $priority = 0)
	{
		$this->listeners[$event_name][$priority][] = $listener;
		unset($this->sorted[$event_name]);
	}

	/**
	 * @see EventDispatcherInterface::remove_listener
	 */
	public function remove_listener($event_name, $listener)
	{
		if (!isset($this->listeners[$event_name])) {
			return;
		}

		foreach ($this->listeners[$event_name] as $priority => $listeners) {
			if (false !== ($key = array_search($listener, $listeners))) {
				unset($this->listeners[$event_name][$priority][$key], $this->sorted[$event_name]);
			}
		}
	}

	/**
	 * @see EventDispatcherInterface::add_subscriber
	 *
	 * @api
	 */
	public function add_subscriber(phpbb_event_subscriber_interface $subscriber)
	{
		foreach ($subscriber->get_subscribed_events() as $event_name => $params) {
			if (is_string($params)) {
				$this->add_listener($event_name, array($subscriber, $params));
			} elseif (is_string($params[0])) {
				$this->add_listener($event_name, array($subscriber, $params[0]), $params[1]);
			} else {
				foreach ($params as $listener) {
					$this->add_listener($event_name, array($subscriber, $listener[0]), isset($listener[1]) ? $listener[1] : 0);
				}
			}
		}
	}

	/**
	 * @see EventDispatcherInterface::remove_subscriber
	 */
	public function remove_subscriber(phpbb_event_subscriber_interface $subscriber)
	{
		foreach ($subscriber->get_subscribed_events() as $event_name => $params) {
			if (is_array($params) && is_array($params[0])) {
				foreach ($params as $listener) {
					$this->remove_listener($event_name, array($subscriber, $listener[0]));
				}
			} else {
				$this->remove_listener($event_name, array($subscriber, is_string($params) ? $params : $params[0]));
			}
		}
	}

	/**
	 * Triggers the listeners of an event.
	 *
	 * This method can be overridden to add functionality that is executed
	 * for each listener.
	 *
	 * @param array[callback] $listeners The event listeners.
	 * @param string $event_name The name of the event to dispatch.
	 * @param Event $event The event object to pass to the event handlers/listeners.
	 */
	protected function do_dispatch($listeners, $event_name, phpbb_event $event)
	{
		foreach ($listeners as $listener) {
			call_user_func($listener, $event);
			if ($event->is_propagation_stopped()) {
				break;
			}
		}
	}

	/**
	 * Sorts the internal list of listeners for the given event by priority.
	 *
	 * @param string $event_name The name of the event.
	 */
	private function sort_listeners($event_name)
	{
		$this->sorted[$event_name] = array();

		if (isset($this->listeners[$event_name])) {
			krsort($this->listeners[$event_name]);
			$this->sorted[$event_name] = call_user_func_array('array_merge', $this->listeners[$event_name]);
		}
	}
}
