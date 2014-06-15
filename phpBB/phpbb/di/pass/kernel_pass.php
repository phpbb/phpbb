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

namespace phpbb\di\pass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class kernel_pass implements CompilerPassInterface
{
	/**
	* Modify the container before it is passed to the rest of the code
	*
	* @param ContainerBuilder $container ContainerBuilder object
	* @return null
	* @throws \InvalidArgumentException
	*/
	public function process(ContainerBuilder $container)
	{
		$definition = $container->getDefinition('dispatcher');

		foreach ($container->findTaggedServiceIds('kernel.event_listener') as $id => $events)
		{
			foreach ($events as $event)
			{
				$priority = isset($event['priority']) ? $event['priority'] : 0;

				if (!isset($event['event']))
				{
					throw new \InvalidArgumentException(sprintf('Service "%1$s" must define the "event" attribute on "kernel.event_listener" tags.', $id));
				}

				if (!isset($event['method']))
				{
					throw new \InvalidArgumentException(sprintf('Service "%1$s" must define the "method" attribute on "kernel.event_listener" tags.', $id));
				}

				$definition->addMethodCall('addListenerService', array($event['event'], array($id, $event['method']), $priority));
			}
		}

		foreach ($container->findTaggedServiceIds('kernel.event_subscriber') as $id => $attributes)
		{
			// We must assume that the class value has been correctly filled, even if the service is created by a factory
			$class = $container->getDefinition($id)->getClass();

			$refClass = new \ReflectionClass($class);
			$interface = 'Symfony\Component\EventDispatcher\EventSubscriberInterface';
			if (!$refClass->implementsInterface($interface))
			{
				throw new \InvalidArgumentException(sprintf('Service "%1$s" must implement interface "%2$s".', $id, $interface));
			}

			$definition->addMethodCall('addSubscriberService', array($id, $class));
		}
	}
}
