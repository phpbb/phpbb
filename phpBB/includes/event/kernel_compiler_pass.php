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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class phpbb_event_kernel_compiler_pass implements CompilerPassInterface
{
	/**
	* Modify the container before it is passed to the rest of the code
	*
	* @param ContainerBuilder $container ContainerBuilder object
	* @return null
	*/
	public function process(ContainerBuilder $container)
	{
		$definition = $container->getDefinition('dispatcher');
		$user = $container->get('user');

		foreach ($container->findTaggedServiceIds('kernel.event_listener') as $id => $events)
		{
			foreach ($events as $event)
			{
				$priority = isset($event['priority']) ? $event['priority'] : 0;

				if (!isset($event['event']))
				{
					throw new InvalidArgumentException($user->lang('NO_EVENT_ATTRIBUTE', $id));
				}

				if (!isset($event['method']))
				{
					throw new InvalidArgumentException($user->lang('NO_METHOD_ATTRIBUTE', $id));
				}

				$definition->addMethodCall('addListenerService', array($event['event'], array($id, $event['method']), $priority));
			}
		}

		foreach ($container->findTaggedServiceIds('kernel.event_subscriber') as $id => $attributes)
		{
			// We must assume that the class value has been correctly filled, even if the service is created by a factory
			$class = $container->getDefinition($id)->getClass();

			$refClass = new ReflectionClass($class);
			$interface = 'Symfony\Component\EventDispatcher\EventSubscriberInterface';
			if (!$refClass->implementsInterface($interface))
			{
				throw new InvalidArgumentException($user->lang('SUBSCRIBER_WRONG_TYPE', $id, $interface));
			}

			$definition->addMethodCall('addSubscriberService', array($id, $class));
		}
	}
}
