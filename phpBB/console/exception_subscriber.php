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

namespace phpbb\console;

use phpbb\exception\exception_interface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class exception_subscriber implements EventSubscriberInterface
{
	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * Construct method
	 *
	 * @param \phpbb\language\language $language Language object
	 */
	public function __construct(\phpbb\language\language $language)
	{
		$this->language = $language;
	}

	/**
	 * This listener is run when the ConsoleEvents::EXCEPTION event is triggered.
	 * It translate the exception message. If din debug mode the original exception is embedded.
	 *
	 * @param ConsoleExceptionEvent $event
	 */
	public function on_exception(ConsoleExceptionEvent $event)
	{
		$original_exception = $event->getException();

		if ($original_exception instanceof exception_interface)
		{
			$parameters = array_merge(array($original_exception->getMessage()), $original_exception->get_parameters());
			$message = call_user_func_array(array($this->language, 'lang'), $parameters);

			$exception = new \RuntimeException($message , $original_exception->getCode(), $original_exception);

			$event->setException($exception);
		}
	}

	static public function getSubscribedEvents()
	{
		return array(
			ConsoleEvents::EXCEPTION => 'on_exception',
		);
	}
}
