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
* Marks all services public
*/
class markpublic_pass implements CompilerPassInterface
{
	/**
	* Modify the container before it is passed to the rest of the code
	* Mark services as public by default unless they were explicitly marked as private
	*
	* @param ContainerBuilder $container ContainerBuilder object
	* @return void
	*/
	public function process(ContainerBuilder $container)
	{
		$service_definitions = $container->getDefinitions();
		foreach ($service_definitions as $definition)
		{
			$changes = $definition->getChanges();

			/* Check if service definition contains explicit 'public' key (changed default state)
			 * If it does and the service is private, then service was explicitly marked as private
			 * Don't mark it as public then
			 */
			$definition_override_public = isset($changes['public']) && $changes['public'];
			if (!$definition_override_public && $definition->isPrivate())
			{
				$definition->setPublic(true);
			}
		}

		foreach ($container->getAliases() as $alias)
		{
			// Only mark alias as public if original service is public too
			if ($service_definitions[(string) $alias]->isPublic() && $alias->isPrivate())
			{
				$alias->setPublic(true);
			}
		}
	}
}
