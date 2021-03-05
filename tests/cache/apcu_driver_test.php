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

// Important: apc.enable_cli=1 must be in php.ini.
// http://forums.devshed.com/php-development-5/apc-problem-561290.html
// http://php.net/manual/en/apc.configuration.php

require_once __DIR__ . '/common_test_case.php';

class phpbb_cache_apcu_driver_test extends phpbb_cache_common_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/config.xml');
	}

	static public function setUpBeforeClass(): void
	{
		if (!extension_loaded('apcu'))
		{
			self::markTestSkipped('APCu extension is not loaded');
		}

		$php_ini = new \bantu\IniGetWrapper\IniGetWrapper;

		if (!$php_ini->getBool('apc.enabled'))
		{
			self::markTestSkipped('APCu is not enabled. Make sure apc.enabled=1 in php.ini');
		}

		if (PHP_SAPI == 'cli' && !$php_ini->getBool('apc.enable_cli'))
		{
			self::markTestSkipped('APCu is not enabled for CLI. Set apc.enable_cli=1 in php.ini');
		}

		parent::setUpBeforeClass();
	}

	protected function setUp(): void
	{
		global $phpbb_container, $phpbb_root_path;

		parent::setUp();

		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->setParameter('core.cache_dir', $phpbb_root_path . 'cache/' . PHPBB_ENVIRONMENT . '/');

		$this->driver = new \phpbb\cache\driver\apcu;

		$this->driver->purge();
	}

	public function test_purge()
	{
		/* add a cache entry which does not match our key */
		$foreign_key = 'test_' . $this->driver->key_prefix . 'test';
		$this->assertSame(true, apcu_store($foreign_key, 0, 600));
		$this->assertSame(true, apcu_exists($foreign_key));

		parent::test_purge();

		$this->assertSame(true, apcu_exists($foreign_key));
	}
}
