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
* Container config extension
*/
class config extends Extension
{
	/** @var array */
	protected $config_php;

	public function __construct(\phpbb\config_php_file $config_php)
	{
		$this->config_php = $config_php;
	}

	/**
	* Loads a specific configuration.
	*
	* @param array            $config    An array of configuration values
	* @param ContainerBuilder $container A ContainerBuilder instance
	*
	* @throws \InvalidArgumentException When provided tag is not defined in this extension
	*/
	public function load(array $config, ContainerBuilder $container)
	{
		$container->setParameter('core.adm_relative_path', ($this->config_php->get('phpbb_adm_relative_path') ? $this->config_php->get('phpbb_adm_relative_path') : 'adm/'));
		$container->setParameter('core.table_prefix', $this->config_php->get('table_prefix'));
		$container->setParameter('cache.driver.class', $this->convert_30_acm_type($this->config_php->get('acm_type')));
		$container->setParameter('dbal.driver.class', $this->config_php->convert_30_dbms_to_31($this->config_php->get('dbms')));
		$container->setParameter('dbal.dbhost', $this->config_php->get('dbhost'));
		$container->setParameter('dbal.dbuser', $this->config_php->get('dbuser'));
		$container->setParameter('dbal.dbpasswd', $this->config_php->get('dbpasswd'));
		$container->setParameter('dbal.dbname', $this->config_php->get('dbname'));
		$container->setParameter('dbal.dbport', $this->config_php->get('dbport'));
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
	* @return string cache driver class
	*/
	protected function convert_30_acm_type($acm_type)
	{
		if (preg_match('#^[a-z]+$#', $acm_type))
		{
			return 'phpbb\\cache\\driver\\' . $acm_type;
		}

		return $acm_type;
	}
}
