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
		require($config_file);

		$this->data = array(
			'dbms'				=> $dbms,
			'table_prefix'		=> $table_prefix,
			'dbhost'			=> $dbhost,
			'dbport'			=> $dbport,
			'dbname'			=> $dbname,
			'dbuser'			=> $dbuser,
			'dbpasswd'			=> $dbpasswd,
			'table_prefix'		=> $table_prefix,
			'acm_type'			=> $acm_type,
			'load_extensions'	=> $load_extensions,
		);
	}

	/**
	* Loads a specific configuration.
	*
	* @param array            $config    An array of configuration values
	* @param ContainerBuilder $container A ContainerBuilder instance
	*
	* @throws InvalidArgumentException When provided tag is not defined in this extension
	*
	* @api
	*/
	public function load(array $config, ContainerBuilder $container)
	{
		$container->setParameter('core.table_prefix', $this->data['table_prefix']);
		$container->setParameter('cache.driver.class', $this->fix_acm_type($this->data['acm_type']));
		$container->setParameter('dbal.driver.class', 'dbal_'.$this->data['dbms']);
		$container->setParameter('dbal.dbhost', $this->data['dbhost']);
		$container->setParameter('dbal.dbuser', $this->data['dbuser']);
		$container->setParameter('dbal.dbpasswd', $this->data['dbpasswd']);
		$container->setParameter('dbal.dbname', $this->data['dbname']);
		$container->setParameter('dbal.dbport', $this->data['dbport']);
		$container->setParameter('dbal.new_link', defined('PHPBB_DB_NEW_LINK') && PHPBB_DB_NEW_LINK);
	}

	/**
	* Returns the recommended alias to use in XML.
	*
	* This alias is also the mandatory prefix to use when using YAML.
	*
	* @return string The alias
	*
	* @api
	*/
	public function getAlias()
	{
		return 'config';
	}

	/**
	* Returns the namespace to be used for this extension (XML namespace).
	*
	* @return string The XML namespace
	*/
	public function getNamespace()
	{
		return false;
	}

	/**
	* Fix ACM type
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
