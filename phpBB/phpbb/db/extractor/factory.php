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

namespace phpbb\db\extractor;

/**
* A factory which serves the suitable extractor instance for the given dbal
*/
class factory
{
	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	protected $container;

	/**
	* Extractor factory constructor
	*
	* @param \phpbb\db\driver\driver_interface							$db
	* @param \Symfony\Component\DependencyInjection\ContainerInterface	$container
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \Symfony\Component\DependencyInjection\ContainerInterface $container)
	{
		$this->db			= $db;
		$this->container	= $container;
	}

	/**
	* DB extractor factory getter
	*
	* @return \phpbb\db\extractor\extractor_interface an appropriate instance of the database extractor for the used database driver
	* @throws \InvalidArgumentException when the database driver is unknown
	*/
	public function get()
	{
		// Return the appropriate DB extractor
		if ($this->db instanceof \phpbb\db\driver\mssql_base)
		{
			return $this->container->get('dbal.extractor.extractors.mssql_extractor');
		}
		else if ($this->db instanceof \phpbb\db\driver\mysql_base)
		{
			return $this->container->get('dbal.extractor.extractors.mysql_extractor');
		}
		else if ($this->db instanceof \phpbb\db\driver\oracle)
		{
			return $this->container->get('dbal.extractor.extractors.oracle_extractor');
		}
		else if ($this->db instanceof \phpbb\db\driver\postgres)
		{
			return $this->container->get('dbal.extractor.extractors.postgres_extractor');
		}
		else if ($this->db instanceof \phpbb\db\driver\sqlite3)
		{
			return $this->container->get('dbal.extractor.extractors.sqlite3_extractor');
		}

		throw new \InvalidArgumentException('Invalid database driver given');
	}
}
