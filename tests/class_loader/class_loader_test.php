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

class phpbb_class_loader_test extends \phpbb_test_case
{
	public function setUp()
	{
		global $phpbb_class_loader;
		$phpbb_class_loader->unregister();

		global $phpbb_class_loader_ext;
		$phpbb_class_loader_ext->unregister();
	}

	public function tearDown()
	{
		global $phpbb_class_loader_ext;
		$phpbb_class_loader_ext->register();

		global $phpbb_class_loader;
		$phpbb_class_loader->register();
	}

	public function test_resolve_path()
	{
		$prefix = dirname(__FILE__) . '/';
		$class_loader = new \phpbb\class_loader('phpbb\\', $prefix . 'phpbb/');

		$prefix .= 'phpbb/';

		$this->assertEquals(
			$prefix . 'class_name.php',
			$class_loader->resolve_path('\\phpbb\\class_name'),
			'Top level class'
		);
		$this->assertEquals(
			$prefix . 'dir/class_name.php',
			$class_loader->resolve_path('\\phpbb\\dir\\class_name'),
			'Class in a directory'
		);
		$this->assertEquals(
			$prefix . 'dir/subdir/class_name.php',
			$class_loader->resolve_path('\\phpbb\\dir\\subdir\\class_name'),
			'Class in a sub-directory'
		);
		$this->assertEquals(
			$prefix . 'dir2/dir2.php',
			$class_loader->resolve_path('\\phpbb\\dir2\\dir2'),
			'Class with name of dir within dir'
		);
	}

	public function test_resolve_cached()
	{
		$cache_map = array(
			'class_loader___phpbb__' => array('\\phpbb\\a\\cached_name' => 'a/cached_name'),
			'class_loader___' => array('\\phpbb\\ext\\foo' => 'foo'),
		);
		$cache = new phpbb_mock_cache($cache_map);

		$prefix = dirname(__FILE__) . '/';
		$class_loader = new \phpbb\class_loader('phpbb\\', $prefix . 'phpbb/', 'php', $cache);
		$class_loader_ext = new \phpbb\class_loader('\\', $prefix . 'phpbb/', 'php', $cache);

		$prefix .= 'phpbb/';

		$this->assertEquals(
			$prefix . 'dir/class_name.php',
			$class_loader->resolve_path('\\phpbb\\dir\\class_name'),
			'Class in a directory'
		);

		$this->assertFalse($class_loader->resolve_path('\\phpbb\\ext\\foo'));
		$this->assertFalse($class_loader_ext->resolve_path('\\phpbb\\a\\cached_name'));

		$this->assertEquals(
			$prefix . 'a/cached_name.php',
			$class_loader->resolve_path('\\phpbb\\a\\cached_name'),
			'Cached class found'
		);

		$this->assertEquals(
			$prefix . 'foo.php',
			$class_loader_ext->resolve_path('\\phpbb\\ext\\foo'),
			'Cached class found in alternative loader'
		);

		$cache_map['class_loader___phpbb__']['\\phpbb\\dir\\class_name'] = 'dir/class_name';
		$cache->check($this, $cache_map);
	}
}
