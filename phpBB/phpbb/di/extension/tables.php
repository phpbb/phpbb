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
	 * {@inheritDoc}
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		// Tables is a reserved parameter and will be overwritten at all times
		$tables = [];

		// Add access via 'tables' parameter to acquire array with all tables
		$parameterBag = $container->getParameterBag();
		$parameters = $parameterBag->all();
		foreach ($parameters as $parameter_name => $parameter_value)
		{
			if (!preg_match('/tables\.(.+)/', $parameter_name, $matches))
			{
				continue;
			}

			$tables[$matches[1]] = $parameter_value;
		}

		$container->setParameter('tables', $tables);
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
