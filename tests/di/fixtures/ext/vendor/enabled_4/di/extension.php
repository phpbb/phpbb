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

namespace vendor\enabled_4\di;

use phpbb\extension\di\extension_base;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
* Container core extension
*/
class extension extends extension_base
{
	protected function load_services(ContainerBuilder $container)
	{
		$filesystem = new \phpbb\filesystem\filesystem();
		$loader = new YamlFileLoader($container, new FileLocator($filesystem->realpath($this->ext_path)));
		$loader->load('environment.yml');
	}
}
