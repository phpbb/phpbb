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

class phpbb_cache_redis_driver_test extends \phpbb_cache_common_test_case
{
	protected static $config;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/config.xml');
	}

	static public function setUpBeforeClass(): void
	{
		if (!extension_loaded('redis'))
		{
			self::markTestSkipped('redis extension is not loaded');
		}

		$config = phpbb_test_case_helpers::get_test_config();
		if (isset($config['redis_host']) || isset($config['redis_port']))
		{
			$host = isset($config['redis_host']) ? $config['redis_host'] : 'localhost';
			$port = isset($config['redis_port']) ? $config['redis_port'] : 6379;
			self::$config = array('host' => $host, 'port' => $port);
		}
		else
		{
			self::markTestSkipped('Test redis host/port is not specified');
		}

		parent::setUpBeforeClass();
	}

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpbb_container;

		parent::setUp();

		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->setParameter('core.cache_dir', $phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/');
		$this->driver = new \phpbb\cache\driver\redis(self::$config['host'], self::$config['port']);
		$this->driver->purge();
	}
}
