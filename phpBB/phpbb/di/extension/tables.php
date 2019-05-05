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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Container tables extension
 */
class tables extends Extension
{
	/**
	 * Loads a specific configuration.
	 *
	 * @param array            $configs   An array of configuration values
	 * @param ContainerBuilder $container A ContainerBuilder instance
	 *
	 * @throws \InvalidArgumentException When provided tag is not defined in this extension
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		if (!$container->hasParameter('tables'))
		{
			return;
		}

		$tables = $container->getParameter('tables');

		foreach ($tables as $table_name => $table_value)
		{
			$container->setParameter('tables.' . $table_name, $table_value);
		}
	}

	/**
	 * Returns the recommended alias to use in XML.
	 *
	 * This alias is also the mandatory prefix to use when using YAML.
	 *
	 * @return string The alias
	 */
	public function getAlias()
	{
		return 'tables';
	}
}
