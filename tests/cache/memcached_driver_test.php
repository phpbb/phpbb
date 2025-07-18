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

require_once __DIR__ . '/common_test_case.php';

class phpbb_cache_memcached_driver_test extends \phpbb_cache_common_test_case
{
	protected static $config;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/config.xml');
	}

	static public function setUpBeforeClass(): void
	{
		if (!extension_loaded('memcached'))
		{
			self::markTestSkipped('memcached extension is not loaded');
		}

		$config = phpbb_test_case_helpers::get_test_config();
		if (isset($config['memcached_host']) || isset($config['memcached_port']))
		{
			$host = isset($config['memcached_host']) ? $config['memcached_host'] : 'localhost';
			$port = isset($config['memcached_port']) ? $config['memcached_port'] : 11211;
			self::$config = array('host' => $host, 'port' => $port);
		}
		else
		{
			self::markTestSkipped('Test memcached host/port is not specified');
		}

		$memcached = new \Memcached();
		$memcached->addServer(self::$config['host'], self::$config['port']);
		if (empty($memcached->getStats()))
		{
			self::markTestSkipped('Test memcached server is not available');
		}

		parent::setUpBeforeClass();
	}

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpbb_container;

		parent::setUp();

		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->setParameter('core.cache_dir', $phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/');
		$this->driver = new \phpbb\cache\driver\memcached(self::$config['host'] . '/' . self::$config['port']);
		$this->driver->purge();
	}
}
