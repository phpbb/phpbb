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

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
* Extension of the Symfony EventDispatcher
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
*/
class dispatcher extends EventDispatcher implements dispatcher_interface
{
	/**
	 * @var bool
	 */
	protected $disabled = false;

	/**
	* {@inheritdoc}
	*/
	public function trigger_event($eventName, $data = [])
	{
		$event = new \phpbb\event\data($data);
		foreach ((array) $eventName as $name)
		{
			$this->dispatch($event, $name);
		}
		return $event->get_data_filtered(array_keys($data));
	}

	/**
	 * {@inheritdoc}
	 */
	public function dispatch(object $event, string|null $eventName = null) : object
	{
		if ($this->disabled)
		{
			return $event;
		}

		return parent::dispatch($event, $eventName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function disable()
	{
		$this->disabled = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function enable()
	{
		$this->disabled = false;
	}
}
