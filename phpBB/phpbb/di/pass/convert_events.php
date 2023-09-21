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
 * Converts event types to Symfony ones
 */
class convert_events implements CompilerPassInterface
{
	/** @var string[] Map for conversions of types */
	private static array $conversions = [
		'event.listener_listener'	=> 'kernel.event_listener',
		'event.listener'			=> 'kernel.event_subscriber',
	];

	/**
	 * Modify the container before it is passed to the rest of the code
	 * Add Symfony event tags to previously used phpBB ones
	 *
	 * @param ContainerBuilder $container ContainerBuilder object
	 * @return void
	 */
	public function process(ContainerBuilder $container): void
	{
		// Add alias for event dispatcher
		$container->addAliases(['dispatcher' => 'event_dispatcher']);

		foreach (self::$conversions as $from => $to)
		{
			foreach ($container->findTaggedServiceIds($from, true) as $id => $tags)
			{
				$definition = $container->getDefinition($id);
				$definition->addTag($to);
			}
		}
	}
}
