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

/**
* Appends an add method call to the definition of each collection service for
* the services tagged with the appropriate name defined in the collection's
* service_collection tag.
*/
class collection_pass implements CompilerPassInterface
{
	/**
	* Modify the container before it is passed to the rest of the code
	*
	* @param ContainerBuilder $container ContainerBuilder object
	* @return null
	*/
	public function process(ContainerBuilder $container)
	{
		foreach ($container->findTaggedServiceIds('service_collection') as $id => $data)
		{
			$definition = $container->getDefinition($id);
			$is_ordered_collection = (substr($definition->getClass(), -strlen('ordered_service_collection')) === 'ordered_service_collection');
			$is_class_name_aware = (isset($data[0]['class_name_aware']) && $data[0]['class_name_aware']);

			foreach ($container->findTaggedServiceIds($data[0]['tag']) as $service_id => $service_data)
			{
				if ($is_ordered_collection)
				{
					$arguments = array($service_id, $service_data[0]['order']);
				}
				else
				{
					$arguments = array($service_id);
				}

				if ($is_class_name_aware)
				{
					$service_definition = $container->getDefinition($service_id);
					$definition->addMethodCall('add_service_class', array(
						$service_id,
						$service_definition->getClass()
					));
				}

				$definition->addMethodCall('add', $arguments);
			}
		}
	}
}
