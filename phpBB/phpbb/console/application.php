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

use Symfony\Component\DependencyInjection\TaggedContainerInterface;

class application extends \Symfony\Component\Console\Application
{
	function register_container_commands(TaggedContainerInterface $container, $tag = 'console.command')
	{
		foreach($container->findTaggedServiceIds($tag) as $id => $void)
		{
			$this->add($container->get($id));
		}
	}
}
