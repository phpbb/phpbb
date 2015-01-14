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

/**
* Extension of the Symfony2 EventDispatcher
*
* It provides an additional `trigger_event` method, which
* gives some syntactic sugar for dispatching events. Instead
* of creating the event object, the method will do that for
* you.
*
* Example:
*
* $vars = array('page_title');
* extract($phpbb_dispatcher->trigger_event('core.index', compact($vars)));
*
*/
interface dispatcher_interface extends \Symfony\Component\EventDispatcher\EventDispatcherInterface
{
	/**
	* Construct and dispatch an event
	*
	* @param string $eventName	The event name
	* @param array $data		An array containing the variables sending with the event
	* @return mixed
	*/
	public function trigger_event($eventName, $data = array());

	/**
	 * Disable the event dispatcher.
	 */
	public function disable();

	/**
	 * Enable the event dispatcher.
	 */
	public function enable();
}
