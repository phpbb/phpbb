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

require_once dirname(__FILE__) . '/common_test_case.php';

class phpbb_cache_apc_driver_test extends phpbb_cache_common_test_case
{
	protected static $config;
	protected $driver;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	static public function setUpBeforeClass()
	{
		if (!extension_loaded('apc'))
		{
			self::markTestSkipped('APC extension is not loaded');
		}

		$php_ini = new \phpbb\php\ini;

		if (!$php_ini->get_bool('apc.enabled'))
		{
			self::markTestSkipped('APC is not enabled. Make sure apc.enabled=1 in php.ini');
		}

		if (PHP_SAPI == 'cli' && !$php_ini->get_bool('apc.enable_cli'))
		{
			self::markTestSkipped('APC is not enabled for CLI. Set apc.enable_cli=1 in php.ini');
		}
	}

	protected function setUp()
	{
		parent::setUp();

		$this->driver = new \phpbb\cache\driver\apc;
		$this->driver->purge();
	}
}
