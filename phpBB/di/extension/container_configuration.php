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

namespace phpbb\di\extension;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class container_configuration implements ConfigurationInterface
{

	/**
	 * Generates the configuration tree builder.
	 *
	 * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('core');
		$rootNode
			->children()
				->booleanNode('require_dev_dependencies')->defaultValue(false)->end()
				->arrayNode('debug')
					->addDefaultsIfNotSet()
					->children()
						->booleanNode('exceptions')->defaultValue(false)->end()
					->end()
				->end()
				->arrayNode('twig')
					->addDefaultsIfNotSet()
					->children()
						->booleanNode('debug')->defaultValue(null)->end()
						->booleanNode('auto_reload')->defaultValue(null)->end()
						->booleanNode('enable_debug_extension')->defaultValue(false)->end()
					->end()
				->end()
			->end()
		;
		return $treeBuilder;
	}
}
