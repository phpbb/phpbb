<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class phpbb_event_extension_subscriber_loader
{
	private $dispatcher;
	private $extension_manager;

	public function __construct(EventDispatcherInterface $dispatcher, phpbb_extension_manager $extension_manager)
	{
		$this->dispatcher = $dispatcher;
		$this->extension_manager = $extension_manager;
	}

	public function load()
	{
		$finder = $this->extension_manager->get_finder();
		$subscriber_classes = $finder
			->extension_directory('/event')
			->core_path('event/')
			->get_classes();

		foreach ($subscriber_classes as $class)
		{
			$subscriber = new $class();
			$this->dispatcher->addSubscriber($subscriber);
		}
	}
}
