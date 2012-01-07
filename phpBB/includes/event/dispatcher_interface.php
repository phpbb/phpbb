<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
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
 * The EventDispatcherInterface is the central point of Symfony's event listener system.
 * Listeners are registered on the manager and events are dispatched through the
 * manager.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface phpbb_event_dispatcher_interface
{
	/**
	 * Dispatches an event to all registered listeners.
	 *
	 * @param string $event_name The name of the event to dispatch. The name of
	 *                          the event is the name of the method that is
	 *                          invoked on listeners.
	 * @param Event $event The event to pass to the event handlers/listeners.
	 *                     If not supplied, an empty Event instance is created.
	 *
	 * @api
	 */
	function dispatch($event_name, phpbb_event $event = null);

	/**
	 * Adds an event listener that listens on the specified events.
	 *
	 * @param string   $event_name The event to listen on
	 * @param callable $listener  The listener
	 * @param integer  $priority  The higher this value, the earlier an event
	 *                            listener will be triggered in the chain (defaults to 0)
	 *
	 * @api
	 */
	function add_listener($event_name, $listener, $priority = 0);

	/**
	 * Adds an event subscriber. The subscriber is asked for all the events he is
	 * interested in and added as a listener for these events.
	 *
	 * @param EventSubscriberInterface $subscriber The subscriber.
	 *
	 * @api
	 */
	function add_subscriber(phpbb_event_subscriber_interface $subscriber);

	/**
	 * Removes an event listener from the specified events.
	 *
	 * @param string|array $event_name The event(s) to remove a listener from.
	 * @param object $listener The listener object to remove.
	 */
	function remove_listener($event_name, $listener);

	/**
	 * Removes an event subscriber.
	 *
	 * @param EventSubscriberInterface $subscriber The subscriber.
	 */
	function remove_subscriber(phpbb_event_subscriber_interface $subscriber);

	/**
	 * Gets the listeners of a specific event or all listeners.
	 *
	 * @param string $event_name The name of the event.
	 *
	 * @return array The event listeners for the specified event, or all event
	 *               listeners by event name.
	 */
	function get_listeners($event_name = null);

	/**
	 * Checks whether an event has any registered listeners.
	 *
	 * @param string $event_name The name of the event.
	 *
	 * @return Boolean TRUE if the specified event has any listeners, FALSE
	 *                 otherwise.
	 */
	function has_listeners($event_name = null);
}
