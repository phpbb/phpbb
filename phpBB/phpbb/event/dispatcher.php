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
use Symfony\Component\EventDispatcher\Event;

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
*     $vars = array('page_title');
*     extract($phpbb_dispatcher->trigger_event('core.index', compact($vars)));
*
*/
class dispatcher extends ContainerAwareEventDispatcher implements dispatcher_interface
{
	/**
	 * @var bool
	 */
	protected $disabled = false;

	/**
	* {@inheritdoc}
	*/
	public function trigger_event($eventName, $data = array())
	{
		$event = new \phpbb\event\data($data);
		$this->dispatch($eventName, $event);
		return $event->get_data_filtered(array_keys($data));
	}

	/**
	 * {@inheritdoc}
	 */
	public function dispatch($eventName, Event $event = null)
	{
		if ($this->disabled)
		{
			return $event;
		}

		return parent::dispatch($eventName, $event);
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
