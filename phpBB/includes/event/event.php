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
 * Event is the base class for classes containing event data.
 *
 * This class contains no event data. It is used by events that do not pass
 * state information to an event handler when an event is raised.
 *
 * You can call the method stop_propagation() to abort the execution of
 * further listeners in your event listener.
 *
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Bernhard Schussek <bschussek@gmail.com>
 */
class phpbb_event
{
	/**
	 * @var Boolean Whether no further event listeners should be triggered
	 */
	private $propagation_stopped = false;

	/**
	 * @var EventDispatcher Dispatcher that dispatched this event
	 */
	private $dispatcher;

	/**
	 * @var string This event's name
	 */
	private $name;

	/**
	 * Returns whether further event listeners should be triggered.
	 *
	 * @see Event::stop_propagation
	 * @return Boolean Whether propagation was already stopped for this event.
	 *
	 * @api
	 */
	public function is_propagation_stopped()
	{
		return $this->propagation_stopped;
	}

	/**
	 * Stops the propagation of the event to further event listeners.
	 *
	 * If multiple event listeners are connected to the same event, no
	 * further event listener will be triggered once any trigger calls
	 * stop_propagation().
	 *
	 * @api
	 */
	public function stop_propagation()
	{
		$this->propagation_stopped = true;
	}

	/**
	 * Stores the EventDispatcher that dispatches this Event
	 *
	 * @param EventDispatcher $dispatcher
	 *
	 * @api
	 */
	public function set_dispatcher(phpbb_event_dispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Returns the EventDispatcher that dispatches this Event
	 *
	 * @return EventDispatcher
	 *
	 * @api
	 */
	public function get_dispatcher()
	{
		return $this->dispatcher;
	}

	/**
	 * Gets the event's name.
	 *
	 * @return string
	 *
	 * @api
	 */
	public function get_name()
	{
		return $this->name;
	}

	/**
	 * Sets the event's name property.
	 *
	 * @param string $name The event name.
	 *
	 * @api
	 */
	public function set_name($name)
	{
		$this->name = $name;
	}
}
