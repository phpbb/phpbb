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

use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

/**
* {@inherit}
*/
class dispatcher_impl extends ContainerAwareEventDispatcher implements dispatcher
{
	public function trigger_event($eventName, $data = array())
	{
		$event = new \phpbb\event\data($data);
		$this->dispatch($eventName, $event);
		return $event->get_data_filtered(array_keys($data));
	}
}
