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
	 * @return TreeBuilder The tree builder
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('core');
		$rootNode
			->children()
				->booleanNode('require_dev_dependencies')->defaultValue(false)->end()
				->booleanNode('allow_install_dir')->defaultValue(false)->end()
				->arrayNode('debug')
					->addDefaultsIfNotSet()
					->children()
						->booleanNode('exceptions')->defaultValue(false)->end()
						->booleanNode('load_time')->defaultValue(false)->end()
						->booleanNode('sql_explain')->defaultValue(false)->end()
						->booleanNode('memory')->defaultValue(false)->end()
						->booleanNode('show_errors')->defaultValue(false)->end()
						->booleanNode('error_handler')->defaultValue(false)->end()
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
				->arrayNode('session')
					->addDefaultsIfNotSet()
					->children()
						->booleanNode('log_errors')->defaultValue(false)->end()
					->end()
				->end()
			->end()
		;
		return $treeBuilder;
	}
}
