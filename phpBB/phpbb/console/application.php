<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
