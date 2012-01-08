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
 * An EventSubscriber knows himself what events he is interested in.
 * If an EventSubscriber is added to an EventDispatcherInterface, the manager invokes
 * {@link get_subscribed_events} and registers the subscriber as a listener for all
 * returned events.
 *
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Bernhard Schussek <bschussek@gmail.com>
 */
interface phpbb_event_subscriber_interface
{
	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 *  * The method name to call (priority defaults to 0)
	 *  * An array composed of the method name to call and the priority
	 *  * An array of arrays composed of the method names to call and respective
	 *    priorities, or 0 if unset
	 *
	 * For instance:
	 *
	 *  * array('event_name' => 'method_name')
	 *  * array('event_name' => array('method_name', $priority))
	 *  * array('event_name' => array(array('method_name1', $priority), array('method_name2'))
	 *
	 * @return array The event names to listen to
	 */
	static function get_subscribed_events();
}
