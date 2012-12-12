<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
* Container config extension
*/
class phpbb_di_extension_config extends Extension
{
	public function __construct($config_file)
	{
		$this->config_file = $config_file;
	}

	/**
	* Loads a specific configuration.
	*
	* @param array            $config    An array of configuration values
	* @param ContainerBuilder $container A ContainerBuilder instance
	*
	* @throws InvalidArgumentException When provided tag is not defined in this extension
	*/
	public function load(array $config, ContainerBuilder $container)
	{
		require($this->config_file);

		$container->setParameter('core.table_prefix', $table_prefix);
		$container->setParameter('cache.driver.class', $this->fix_acm_type($acm_type));
		$container->setParameter('cache.driver.directory', (isset($acm_directory) ? $acm_directory : 'cache/'));
		$container->setParameter('dbal.driver.class', 'dbal_'.$dbms);
		$container->setParameter('dbal.dbhost', $dbhost);
		$container->setParameter('dbal.dbuser', $dbuser);
		$container->setParameter('dbal.dbpasswd', $dbpasswd);
		$container->setParameter('dbal.dbname', $dbname);
		$container->setParameter('dbal.dbport', $dbport);
		$container->setParameter('dbal.new_link', defined('PHPBB_DB_NEW_LINK') && PHPBB_DB_NEW_LINK);

		// ACM SQL Type defaults to the normal ACM type if not declared
		$acm_sql_type = $this->fix_acm_type((isset($acm_sql_type) ? $acm_sql_type : $acm_type));
		$sql_cache_reflection = new ReflectionClass($acm_sql_type);
		if (in_array('phpbb_cache_driver_sql_interface', $sql_cache_reflection->getInterfaceNames()))
		{
			// If the ACM SQL type is a "proper" SQL cache system, it will implement phpbb_cache_driver_sql_interface
			$container->setParameter('cache.driver.sql_container', $acm_sql_type);

			// Set the sql driver to null as it shouldn't be used (but is required to setup the parent because of the config)
			$container->setParameter('cache.driver.sql_driver', 'phpbb_cache_driver_null');
		}
		else
		{
			// If the ACM SQL type is a standard cache driver, it will use the generic driver, which runs on top of a standard cache driver
			$container->setParameter('cache.driver.sql_container', 'phpbb_cache_driver_sql_generic');
			$container->setParameter('cache.driver.sql_driver', $acm_sql_type);
		}
		unset($sql_cache_reflection);
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
		return 'config';
	}

	/**
	* Convert old (3.0) values to 3.1 class names
	*
	* @param style $acm_type ACM type
	* @return ACM type class
	*/
	protected function fix_acm_type($acm_type)
	{
		if (preg_match('#^[a-z]+$#', $acm_type))
		{
			return 'phpbb_cache_driver_'.$acm_type;
		}

		return $acm_type;
	}
}
