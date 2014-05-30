<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
*     $vars = array('page_title');
*     extract($phpbb_dispatcher->trigger_event('core.index', compact($vars)));
*
*/
interface dispatcher_interface extends \Symfony\Component\EventDispatcher\EventDispatcherInterface
{
	public function trigger_event($eventName, $data = array());
}
