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

namespace phpbb\install;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class updater_configuration implements ConfigurationInterface
{

	/**
	 * Generates the configuration tree builder.
	 *
	 * @return TreeBuilder The tree builder
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('updater');
		$rootNode
			->addDefaultsIfNotSet()
			->children()
				->enumNode('type')->values(['all','db_only'])->defaultValue('all')->end()
				->arrayNode('extensions')
					->prototype('scalar')->end()
					->defaultValue([])
				->end()
			->end()
		;

		return $treeBuilder;
	}
}
