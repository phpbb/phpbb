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
	$other_config_path = $phpbb_root_path . 'install/update/new/config/';
	$config_path = file_exists($other_config_path . 'services.yml') ? $other_config_path : $phpbb_root_path . 'config';

	$core = new phpbb_di_extension_core($config_path);
	$container = phpbb_create_container(array($core), $phpbb_root_path, $php_ext);

	$container->setParameter('core.root_path', $phpbb_root_path);
	$container->setParameter('core.adm_relative_path', $phpbb_adm_relative_path);
	$container->setParameter('core.php_ext', $php_ext);
	$container->setParameter('core.table_prefix', '');

	$container->register('dbal.conn')->setSynthetic(true);

	$container->setAlias('cache.driver', 'cache.driver.install');

	$container->compile();

	return $container;
}

/**
* Create updater container
*
* @param string $phpbb_root_path Root path
* @param string $php_ext PHP Extension
* @param array $config_path Path to config directory
* @return ContainerBuilder object (compiled)
*/
function phpbb_create_update_container($phpbb_root_path, $php_ext, $config_path)
{
	return phpbb_create_compiled_container(
		array(
			new phpbb_di_extension_config($phpbb_root_path . 'config.' . $php_ext),
			new phpbb_di_extension_core($config_path),
		),
		array(
			new phpbb_di_pass_collection_pass(),
			new phpbb_di_pass_kernel_pass(),
		),
		$phpbb_root_path,
		$php_ext
	);
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
	// Create a temporary container for access to the ext.manager service
	$tmp_container = phpbb_create_container($extensions, $phpbb_root_path, $php_ext);
	$tmp_container->compile();

	// XXX stop writing to global $cache when
	// http://tracker.phpbb.com/browse/PHPBB3-11203 is fixed
	$GLOBALS['cache'] = $tmp_container->get('cache');
	$installed_exts = $tmp_container->get('ext.manager')->all_enabled();

	// Now pass the enabled extension paths into the ext compiler extension
	$extensions[] = new phpbb_di_extension_ext($installed_exts);

	// Create the final container to be compiled and cached
	$container = phpbb_create_container($extensions, $phpbb_root_path, $php_ext);

	// Compile the container
	foreach ($passes as $pass)
	{
		$container->addCompilerPass($pass);
	}
	$container->compile();

	return $container;
}

/**
* Create a compiled and dumped ContainerBuilder object
*
* @param array $extensions Array of Container extension objects
* @param array $passes Array of Compiler Pass objects
* @param string $phpbb_root_path Root path
* @param string $php_ext PHP Extension
* @return ContainerBuilder object (compiled)
*/
function phpbb_create_dumped_container(array $extensions, array $passes, $phpbb_root_path, $php_ext)
{
	// Check for our cached container; if it exists, use it
	$container_filename = phpbb_container_filename($phpbb_root_path, $php_ext);
	if (file_exists($container_filename))
	{
		require($container_filename);
		return new phpbb_cache_container();
	}

	$container = phpbb_create_compiled_container($extensions, $passes, $phpbb_root_path, $php_ext);

	// Lastly, we create our cached container class
	$dumper = new PhpDumper($container);
	$cached_container_dump = $dumper->dump(array(
		'class'         => 'phpbb_cache_container',
		'base_class'    => 'Symfony\\Component\\DependencyInjection\\ContainerBuilder',
	));

	file_put_contents($container_filename, $cached_container_dump);

	return $container;
}

/**
* Create an environment-specific ContainerBuilder object
*
* If debug is enabled, the container is re-compiled every time.
* This ensures that the latest changes will always be reflected
* during development.
*
* Otherwise it will get the existing dumped container and use
* that one instead.
*
* @param array $extensions Array of Container extension objects
* @param array $passes Array of Compiler Pass objects
* @param string $phpbb_root_path Root path
* @param string $php_ext PHP Extension
* @return ContainerBuilder object (compiled)
*/
function phpbb_create_dumped_container_unless_debug(array $extensions, array $passes, $phpbb_root_path, $php_ext)
{
	$container_factory = defined('DEBUG') ? 'phpbb_create_compiled_container' : 'phpbb_create_dumped_container';
	return $container_factory($extensions, $passes, $phpbb_root_path, $php_ext);
}

/**
* Create a default ContainerBuilder object
*
* Contains the default configuration of the phpBB container.
*
* @param array $extensions Array of Container extension objects
* @param array $passes Array of Compiler Pass objects
* @return ContainerBuilder object (compiled)
*/
function phpbb_create_default_container($phpbb_root_path, $php_ext)
{
	return phpbb_create_dumped_container_unless_debug(
		array(
			new phpbb_di_extension_config($phpbb_root_path . 'config.' . $php_ext),
			new phpbb_di_extension_core($phpbb_root_path . 'config'),
		),
		array(
			new phpbb_di_pass_collection_pass(),
			new phpbb_di_pass_kernel_pass(),
		),
		$phpbb_root_path,
		$php_ext
	);
}

/**
* Get the filename under which the dumped container will be stored.
*
* @param string $phpbb_root_path Root path
* @param string $php_ext PHP Extension
* @return Path for dumped container
*/
function phpbb_container_filename($phpbb_root_path, $php_ext)
{
	$filename = str_replace(array('/', '.'), array('slash', 'dot'), $phpbb_root_path);
	return $phpbb_root_path . 'cache/container_' . $filename . '.' . $php_ext;
}
