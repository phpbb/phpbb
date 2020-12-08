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
	*
	* @param ContainerBuilder $container ContainerBuilder object
	* @return null
	*/
	public function process(ContainerBuilder $container)
	{
		foreach ($container->getDefinitions() as $definition)
		{
			if ($definition->isPrivate())
			{
				$definition->setPublic(true);
			}
		}
	}
}
