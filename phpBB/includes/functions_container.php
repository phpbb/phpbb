<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Create the ContainerBuilder object
*
* @param array $extensions Array of Container extension objects
* @param string $phpbb_root_path Root path
* @param string $php_ext PHP Extension
* @return ContainerBuilder object
*/
function phpbb_create_container(array $extensions, $phpbb_root_path, $php_ext)
{
	$container = new ContainerBuilder();

	foreach ($extensions as $extension)
	{
		$container->registerExtension($extension);
		$container->loadFromExtension($extension->getAlias());
	}

	$container->setParameter('core.root_path', $phpbb_root_path);
	$container->setParameter('core.php_ext', $php_ext);

	return $container;
}

/**
* Create installer container
*
* @param string $phpbb_root_path Root path
* @param string $php_ext PHP Extension
* @return ContainerBuilder object
*/
function phpbb_create_install_container($phpbb_root_path, $php_ext)
{
	// We have to do it like this instead of with extensions
	$container = new ContainerBuilder();
	$loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../config'));
	$loader->load('services.yml');

	$container->setParameter('core.root_path', $phpbb_root_path);
	$container->setParameter('core.php_ext', $php_ext);

	$container->setAlias('cache.driver', 'cache.driver.install');

	return $container;
}

/**
* Create a compiled ContainerBuilder object
*
* @param array $extensions Array of Container extension objects
* @param array $passes Array of Compiler Pass objects
* @param string $phpbb_root_path Root path
* @param string $php_ext PHP Extension
* @return ContainerBuilder object (compiled)
*/
function phpbb_create_compiled_container(array $extensions, array $passes, $phpbb_root_path, $php_ext)
{
	// Check for our cached container; if it exists, use it
	$container_filename = phpbb_container_filename($phpbb_root_path, $php_ext);
	if (file_exists($container_filename))
	{
		require($container_filename);
		return new phpbb_cache_container();
	}

	// Create a temporary container for access to the ext.manager service
	$tmp_container = phpbb_create_container($extensions, $phpbb_root_path, $php_ext);
	$tmp_container->compile();

	// Now pass the enabled extension paths into the ext compiler extension
	$extensions[] = new phpbb_di_extension_ext($tmp_container->get('ext.manager')->all_enabled());

	// Create the final container to be compiled and cached
	$container = phpbb_create_container($extensions, $phpbb_root_path, $php_ext);

	// Compile the container
	foreach ($passes as $pass)
	{
		$container->addCompilerPass($pass);
	}
	$container->compile();

	// Lastly, we create our cached container class
	$dumper = new PhpDumper($container);
	$cached_container_dump = $dumper->dump(array(
		'class'         => 'phpbb_cache_container',
		'base_class'    => 'Symfony\\Component\\DependencyInjection\\ContainerBuilder',
	));

	file_put_contents($container_filename, $cached_container_dump);

	return $container;
}

function phpbb_container_filename($phpbb_root_path, $php_ext)
{
	$filename = str_replace(array('/', '.'), array('slash', 'dot'), $phpbb_root_path);
	return $phpbb_root_path . 'cache/' . $filename . '_container.' . $php_ext;
}
