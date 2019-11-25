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

require_once dirname(__FILE__) . '/common_test_case.php';

class phpbb_cache_memcached_driver_test extends \phpbb_cache_common_test_case
{
	protected static $config;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	static public function setUpBeforeClass()
	{
		if (!extension_loaded('memcached'))
		{
			self::markTestSkipped('memcached extension is not loaded');
		}

		parent::setUpBeforeClass();
	}

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpbb_container;

		parent::setUp();

		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->setParameter('core.cache_dir', $phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/');
		$this->driver = new \phpbb\cache\driver\memcached();
		$this->driver->purge();
	}
}
