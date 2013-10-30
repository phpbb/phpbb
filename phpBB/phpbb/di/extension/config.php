<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\di\extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
* Container config extension
*/
class config extends Extension
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

		$container->setParameter('core.adm_relative_path', (isset($phpbb_adm_relative_path) ? $phpbb_adm_relative_path : 'adm/'));
		$container->setParameter('core.table_prefix', $table_prefix);
		$container->setParameter('cache.driver.class', $this->convert_30_acm_type($acm_type));
		$container->setParameter('dbal.driver.class', phpbb_convert_30_dbms_to_31($dbms));
		$container->setParameter('dbal.dbhost', $dbhost);
		$container->setParameter('dbal.dbuser', $dbuser);
		$container->setParameter('dbal.dbpasswd', $dbpasswd);
		$container->setParameter('dbal.dbname', $dbname);
		$container->setParameter('dbal.dbport', $dbport);
		$container->setParameter('dbal.new_link', defined('PHPBB_DB_NEW_LINK') && PHPBB_DB_NEW_LINK);
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
	* Convert 3.0 ACM type to 3.1 cache driver class name
	*
	* @param string $acm_type ACM type
	* @return cache driver class
	*/
	protected function convert_30_acm_type($acm_type)
	{
		if (preg_match('#^[a-z]+$#', $acm_type))
		{
			return '\\phpbb\cache\driver\\'.$acm_type;
		}

		return $acm_type;
	}
}
