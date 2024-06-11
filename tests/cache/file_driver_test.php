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

class phpbb_cache_file_driver_test extends phpbb_cache_common_test_case
{
	private $cache_dir;

	/** @var \phpbb\cache\driver\file */
	private  $cache_file;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/config.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->cache_dir = __DIR__ . '/../tmp/cache/';

		if (file_exists($this->cache_dir))
		{
			// cache directory possibly left after aborted
			// or failed run earlier
			$this->remove_cache_dir();
		}
		$this->create_cache_dir();

		$this->cache_file = new \phpbb\cache\driver\file($this->cache_dir);
		$this->driver = $this->cache_file;
	}

	protected function tearDown(): void
	{
		if (file_exists($this->cache_dir))
		{
			$this->remove_cache_dir();
		}

		parent::tearDown();
	}

	public function test_read_not_readable()
	{
		if (strtolower(substr(PHP_OS, 0, 3)) === 'win')
		{
			$this->markTestSkipped('Unable to test unreadable files on Windows');
		}

		global $phpEx;

		// Create file that is not readable
		$this->assertTrue($this->cache_file->_write('unreadable', 'foo', time() + 86400));

		$filename = "{$this->cache_dir}unreadable.$phpEx";
		@chmod($filename, 0000);
		$readReflection = new \ReflectionMethod($this->cache_file, '_read');
		$readReflection->setAccessible(true);
		$this->assertFalse($readReflection->invoke($this->cache_file, 'unreadable'));
		@chmod($filename, 0600);
		$this->assertNotFalse($readReflection->invoke($this->cache_file, 'unreadable'));
	}

	public function test_read_data_global_invalid()
	{
		global $phpEx;

		$reflectionCacheVars = new \ReflectionProperty($this->cache_file, 'vars');
		$reflectionCacheVars->setAccessible(true);
		$reflectionCacheVars->setValue($this->cache_file, ['foo' => 'bar']);

		$reflectionCacheVarExpires = new \ReflectionProperty($this->cache_file, 'var_expires');
		$reflectionCacheVarExpires->setAccessible(true);
		$reflectionCacheVarExpires->setValue($this->cache_file, ['foo' => time() + 86400]);

		// Create file in invalid format
		$this->assertTrue($this->cache_file->_write('data_global'));
		$filename = "{$this->cache_dir}data_global.$phpEx";
		$cache_data = file_get_contents($filename);
		// Force negative read when retrieving data_global
		$cache_data = str_replace("\n13\n", "\n1\n", $cache_data);
		file_put_contents($filename, $cache_data);

		$readReflection = new \ReflectionMethod($this->cache_file, '_read');
		$readReflection->setAccessible(true);
		$this->assertFalse($readReflection->invoke($this->cache_file, 'data_global'));
	}

	public function test_read_data_global_zero_bytes()
	{
		global $phpEx;

		$reflectionCacheVars = new \ReflectionProperty($this->cache_file, 'vars');
		$reflectionCacheVars->setAccessible(true);
		$reflectionCacheVars->setValue($this->cache_file, ['foo' => 'bar']);

		$reflectionCacheVarExpires = new \ReflectionProperty($this->cache_file, 'var_expires');
		$reflectionCacheVarExpires->setAccessible(true);
		$reflectionCacheVarExpires->setValue($this->cache_file, ['foo' => time() + 86400]);

		// Create file in invalid format
		$this->assertTrue($this->cache_file->_write('data_global'));
		$filename = "{$this->cache_dir}data_global.$phpEx";
		$cache_data = file_get_contents($filename);
		// Force negative read when retrieving data_global
		$cache_data = str_replace("\n13\n", "\n0\n", $cache_data);
		file_put_contents($filename, $cache_data);

		$readReflection = new \ReflectionMethod($this->cache_file, '_read');
		$readReflection->setAccessible(true);
		$this->assertFalse($readReflection->invoke($this->cache_file, 'data_global'));
	}

	public function test_read_data_global_hex_bytes()
	{
		global $phpEx;

		$reflectionCacheVars = new \ReflectionProperty($this->cache_file, 'vars');
		$reflectionCacheVars->setAccessible(true);
		$reflectionCacheVars->setValue($this->cache_file, ['foo' => 'bar']);

		$reflectionCacheVarExpires = new \ReflectionProperty($this->cache_file, 'var_expires');
		$reflectionCacheVarExpires->setAccessible(true);
		$reflectionCacheVarExpires->setValue($this->cache_file, ['foo' => time() + 86400]);

		// Create file in invalid format
		$this->assertTrue($this->cache_file->_write('data_global'));
		$filename = "{$this->cache_dir}data_global.$phpEx";
		$cache_data = file_get_contents($filename);
		// Force negative read when retrieving data_global
		$cache_data = str_replace("\n13\n", "\nA\n", $cache_data);
		file_put_contents($filename, $cache_data);

		$readReflection = new \ReflectionMethod($this->cache_file, '_read');
		$readReflection->setAccessible(true);
		$this->assertFalse($readReflection->invoke($this->cache_file, 'data_global'));
	}

	public function test_read_data_global_expired()
	{
		$reflectionCacheVars = new \ReflectionProperty($this->cache_file, 'vars');
		$reflectionCacheVars->setAccessible(true);
		$reflectionCacheVars->setValue($this->cache_file, ['foo' => 'bar']);

		$reflectionCacheVarExpires = new \ReflectionProperty($this->cache_file, 'var_expires');
		$reflectionCacheVarExpires->setAccessible(true);
		$reflectionCacheVarExpires->setValue($this->cache_file, ['foo' => time() - 86400]);

		// Create file in invalid format
		$this->assertTrue($this->cache_file->_write('data_global'));

		// Clear data
		$reflectionCacheVars->setValue($this->cache_file, []);
		$reflectionCacheVarExpires->setValue($this->cache_file, []);

		$readReflection = new \ReflectionMethod($this->cache_file, '_read');
		$readReflection->setAccessible(true);
		$this->assertTrue($readReflection->invoke($this->cache_file, 'data_global'));

		// Check data, should be empty
		$this->assertEquals([], $reflectionCacheVars->getValue($this->cache_file));
	}

	public function test_read_data_global()
	{
		$reflectionCacheVars = new \ReflectionProperty($this->cache_file, 'vars');
		$reflectionCacheVars->setAccessible(true);
		$expectedVars = ['foo' => 'bar'];
		$reflectionCacheVars->setValue($this->cache_file, $expectedVars);

		$reflectionCacheVarExpires = new \ReflectionProperty($this->cache_file, 'var_expires');
		$reflectionCacheVarExpires->setAccessible(true);
		$expectedVarExpires = ['foo' => time() + 86400];
		$reflectionCacheVarExpires->setValue($this->cache_file, $expectedVarExpires);

		// Create file in invalid format
		$this->assertTrue($this->cache_file->_write('data_global'));

		// Clear data
		$reflectionCacheVars->setValue($this->cache_file, []);
		$reflectionCacheVarExpires->setValue($this->cache_file, []);
		$this->assertEquals([], $reflectionCacheVars->getValue($this->cache_file));
		$this->assertEquals([], $reflectionCacheVarExpires->getValue($this->cache_file));

		$readReflection = new \ReflectionMethod($this->cache_file, '_read');
		$readReflection->setAccessible(true);
		$this->assertTrue($readReflection->invoke($this->cache_file, 'data_global'));

		// Check data, should be empty
		$this->assertEquals($expectedVars, $reflectionCacheVars->getValue($this->cache_file));
		$this->assertEquals($expectedVarExpires, $reflectionCacheVarExpires->getValue($this->cache_file));
	}

	private function create_cache_dir()
	{
		$this->get_test_case_helpers()->makedirs($this->cache_dir);
	}

	private function remove_cache_dir()
	{
		$iterator = new DirectoryIterator($this->cache_dir);
		foreach ($iterator as $file)
		{
			if ($file != '.' && $file != '..')
			{
				unlink($this->cache_dir . '/' . $file);
			}
		}
		rmdir($this->cache_dir);
	}
}
