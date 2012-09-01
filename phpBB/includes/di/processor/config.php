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

/**
* Configure the container for phpBB's services though
* user-defined parameters defined in the config.php file.
*/
class phpbb_di_processor_config implements phpbb_di_processor_interface
{
	private $config_file;
	private $phpbb_root_path;
	private $php_ext;

	/**
	* Constructor.
	*
	* @param string $config_file The config file
	* @param string $phpbb_root_path The root path
	* @param string $php_ext The PHP extension
	*/
	public function __construct($config_file, $phpbb_root_path, $php_ext)
	{
		$this->config_file = $config_file;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* @inheritdoc
	*/
	public function process(ContainerBuilder $container)
	{
		require $this->config_file;

		$container->setParameter('core.root_path', $this->phpbb_root_path);
		$container->setParameter('core.php_ext', $this->php_ext);

		$container->setParameter('core.table_prefix', $table_prefix);
		$container->setParameter('cache.driver.class', $this->fix_acm_type($acm_type));
		$container->setParameter('dbal.driver.class', 'dbal_'.$dbms);
		$container->setParameter('dbal.dbhost', $dbhost);
		$container->setParameter('dbal.dbuser', $dbuser);
		$container->setParameter('dbal.dbpasswd', $dbpasswd);
		$container->setParameter('dbal.dbname', $dbname);
		$container->setParameter('dbal.dbport', $dbport);
		$container->setParameter('dbal.new_link', defined('PHPBB_DB_NEW_LINK') && PHPBB_DB_NEW_LINK);

		$container->set('container', $container);
	}

	protected function fix_acm_type($acm_type)
	{
		if (preg_match('#^[a-z]+$#', $acm_type))
		{
			return 'phpbb_cache_driver_'.$acm_type;
		}

		return $acm_type;
	}
}
