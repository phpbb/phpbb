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

class installer_configuration implements ConfigurationInterface
{

	/**
	 * Generates the configuration tree builder.
	 *
	 * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('installer');
		$rootNode
			->children()
				->arrayNode('admin')
					->children()
						->scalarNode('name')->defaultValue('admin')->cannotBeEmpty()->end()
						->scalarNode('password')->defaultValue('adminadmin')->cannotBeEmpty()->end()
						->scalarNode('email')->defaultValue('admin@example.org')->cannotBeEmpty()->end()
					->end()
				->end()
				->arrayNode('board')
					->children()
						->scalarNode('lang')
							->defaultValue('en')
							->cannotBeEmpty()
							->end()
						->scalarNode('name')
							->defaultValue('My Board')
							->cannotBeEmpty()
							->end()
						->scalarNode('description')
						->defaultValue('My amazing new phpBB board')
						->cannotBeEmpty()
						->end()
					->end()
				->end()
				->arrayNode('database')
					->children()
						->scalarNode('dbms')
							->defaultValue('sqlite3')
							->cannotBeEmpty()
							->isRequired()
							->end()
						->scalarNode('dbhost')
							->defaultValue(null)
							->end()
						->scalarNode('dbport')
							->defaultValue(null)
							->end()
						->scalarNode('dbuser')
							->defaultValue(null)
							->end()
						->scalarNode('dbpasswd')
							->defaultValue(null)
							->end()
						->scalarNode('dbname')
							->defaultValue(null)
							->end()
						->scalarNode('table_prefix')
							->defaultValue('phpbb_')
							->cannotBeEmpty()
							->isRequired()
							->end()
					->end()
				->end()
				->arrayNode('email')
					->canBeEnabled()
					->addDefaultsIfNotSet()
					->children()
						->booleanNode('smtp_delivery')
							->defaultValue(false)
							->treatNullLike(false)
							->end()
						->scalarNode('smtp_host')
							->defaultValue(null)
							->end()
						->scalarNode('smtp_port')
							->defaultValue(null)
							->end()
						->scalarNode('smtp_auth')
							->defaultValue(null)
							->end()
						->scalarNode('smtp_user')
							->defaultValue(null)
							->end()
						->scalarNode('smtp_pass')
							->defaultValue(null)
							->end()
					->end()
				->end()
				->arrayNode('server')
					->children()
						->booleanNode('cookie_secure')
							->defaultValue(false)
							->treatNullLike(false)
							->end()
						->scalarNode('server_protocol')
							->defaultValue('http://')
							->cannotBeEmpty()
							->end()
						->booleanNode('force_server_vars')
							->defaultValue(false)
							->treatNullLike(false)
							->end()
						->scalarNode('server_name')
							->defaultValue('localhost')
							->cannotBeEmpty()
							->end()
						->integerNode('server_port')
							->defaultValue(80)
							->min(1)
							->cannotBeEmpty()
							->end()
						->scalarNode('script_path')
							->defaultValue('/')
							->cannotBeEmpty()
							 ->end()
					->end()
				->end()
				->arrayNode('extensions')
					->prototype('scalar')->end()
					->defaultValue([])
				->end()
			->end()
		;
		return $treeBuilder;
	}
}
