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

namespace phpbb\di;

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\LazyProxy\Instantiator\InstantiatorInterface;

/**
 * Runtime lazy loading proxy generator extended for allowing use while using
 * open_basedir restrictions
 *
 * Original author: Marco Pivetta <ocramius@gmail.com>
 */
class proxy_instantiator implements InstantiatorInterface
{
	/**
	 * @var LazyLoadingValueHolderFactory
	 */
	private $factory;

	/**
	 * proxy_instantiator constructor
	 * @param string $cache_dir Cache dir for fall back when using open_basedir
	 */
	public function __construct($cache_dir)
	{
		$config = new Configuration();

		// Prevent trying to write to system temp dir in case of open_basedir
		// restrictions being in effect
		$tmp_dir = (function_exists('sys_get_temp_dir')) ? sys_get_temp_dir() : '';
		if (empty($tmp_dir) || !@file_exists($tmp_dir) || !@is_writable($tmp_dir))
		{
			$config->setProxiesTargetDir($cache_dir);
		}
		$config->setGeneratorStrategy(new EvaluatingGeneratorStrategy());

		$this->factory = new LazyLoadingValueHolderFactory($config);
	}

	/**
	 * {@inheritdoc}
	 */
	public function instantiateProxy(ContainerInterface $container, Definition $definition, $id, $realInstantiator)
	{
		return $this->factory->createProxy(
			$definition->getClass(),
			function (&$wrappedInstance, \ProxyManager\Proxy\LazyLoadingInterface $proxy) use ($realInstantiator) {
				$wrappedInstance = call_user_func($realInstantiator);

				$proxy->setProxyInitializer(null);

				return true;
			}
		);
	}
}
