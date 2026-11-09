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

class phpbb_di_markpublic_pass_test extends phpbb_test_case
{
	public function test_marks_alias_chain_public(): void
	{
		$container = new Symfony\Component\DependencyInjection\ContainerBuilder();
		$container->setDefinition('service', new Symfony\Component\DependencyInjection\Definition());
		$container->setAlias('service.alias', 'service');
		$container->setAlias('service.chained_alias', 'service.alias');

		$pass = new phpbb\di\pass\markpublic_pass();
		$pass->process($container);

		self::assertTrue($container->getDefinition('service')->isPublic());
		self::assertTrue($container->getAlias('service.alias')->isPublic());
		self::assertTrue($container->getAlias('service.chained_alias')->isPublic());
	}

	public function test_keeps_alias_to_explicitly_private_definition_private(): void
	{
		$container = new Symfony\Component\DependencyInjection\ContainerBuilder();
		$definition = new Symfony\Component\DependencyInjection\Definition();
		$definition->setPublic(false);
		$container->setDefinition('service.private', $definition);
		$container->setAlias('service.private_alias', 'service.private');
		$container->setAlias('service.private_chained_alias', 'service.private_alias');

		$pass = new phpbb\di\pass\markpublic_pass();
		$pass->process($container);

		self::assertFalse($container->getDefinition('service.private')->isPublic());
		self::assertTrue($container->getAlias('service.private_alias')->isPrivate());
		self::assertTrue($container->getAlias('service.private_chained_alias')->isPrivate());
	}
}
