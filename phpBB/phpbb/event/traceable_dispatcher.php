<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/
namespace phpbb\event;

use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;

/**
* Extension of the Symfony2 TraceableEventDispatcher
*
* It collects some data about event listeners.
*
* This event dispatcher delegates the dispatching to another one.
*/
class traceable_dispatcher extends TraceableEventDispatcher implements dispatcher_interface
{
	public function trigger_event($eventName, $data = array())
	{
		$event = new \phpbb\event\data($data);
		$this->dispatch($eventName, $event);
		return $event->get_data_filtered(array_keys($data));
	}
}
