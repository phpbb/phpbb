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
* Get DB connection from config.php.
*
* Used to bootstrap the container.
*
* @param string $config_file
* @return \phpbb\db\driver\driver_interface
*/
function phpbb_bootstrap_db_connection(array $config_file_data)
{
	extract($config_file_data);
	$dbal_driver_class = phpbb_convert_30_dbms_to_31($dbms);

	$db = new $dbal_driver_class();
	$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, defined('PHPBB_DB_NEW_LINK'));

	return $db;
}

/**
* Get enabled extensions.
*
* Used to bootstrap the container.
*
* @param string $config_file
* @param string $phpbb_root_path
* @return array enabled extensions
*/
function phpbb_bootstrap_enabled_exts($config_file, $phpbb_root_path, array $config_file_data)
{
	$db = phpbb_bootstrap_db_connection($config_file_data);

	$sql = 'SELECT *
			FROM ' . $config_file_data['table_prefix'] . 'ext
			WHERE ext_active = 1';

	$result = $db->sql_query($sql);
	$rows = $db->sql_fetchrowset($result);
	$db->sql_freeresult($result);

	$exts = array();
	foreach ($rows as $row)
	{
		$exts[$row['ext_name']] = $phpbb_root_path . 'ext/' . $row['ext_name'] . '/';
	}

	return $exts;
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
	$config_path = file_exists($other_config_path . 'services.yml') ? $other_config_path : $phpbb_root_path . 'config/';

	$core = new \phpbb\di\extension\core($config_path);
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
	$config_file = $phpbb_root_path . 'config.' . $php_ext;
	return phpbb_create_compiled_container(
		array(
			new phpbb\di\extension\config($config_file),
			new phpbb\di\extension\core($config_path),
		),
		array(
			new phpbb\di\pass\collection_pass(),
			new phpbb\di\pass\kernel_pass(),
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
	$container_factory = defined('DEBUG_CONTAINER') ? 'phpbb_create_compiled_container' : 'phpbb_create_dumped_container';
	return $container_factory($extensions, $passes, $phpbb_root_path, $php_ext);
}

/**
* Create a default ContainerBuilder object
*
* Contains the default configuration of the phpBB container.
*
* @param array $extensions Array of Container extension objects
* @param array $passes Array of Compiler Pass objects
* @param array $config_file_data Array containing data from config file.
* @return ContainerBuilder object (compiled)
*/
function phpbb_create_default_container($phpbb_root_path, $php_ext, array $config_file_data)
{
	$installed_exts = phpbb_bootstrap_enabled_exts($config_file_data, $phpbb_root_path, $config_file_data);

	return phpbb_create_dumped_container_unless_debug(
		array(
			new \phpbb\di\extension\config($config_file_data),
			new \phpbb\di\extension\core($phpbb_root_path . 'config'),
			new \phpbb\di\extension\ext($installed_exts),
		),
		array(
			new \phpbb\di\pass\collection_pass(),
			new \phpbb\di\pass\kernel_pass(),
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
