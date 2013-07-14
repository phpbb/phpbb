<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_class_loader_test extends PHPUnit_Framework_TestCase
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
		$class_loader = new phpbb_class_loader('phpbb_', $prefix . 'phpbb/');

		$prefix .= 'phpbb/';

		$this->assertEquals(
			'',
			$class_loader->resolve_path('phpbb_dir'),
			'Class with same name as a directory is unloadable'
		);

		$this->assertEquals(
			$prefix . 'class_name.php',
			$class_loader->resolve_path('phpbb_class_name'),
			'Top level class'
		);
		$this->assertEquals(
			$prefix . 'dir/class_name.php',
			$class_loader->resolve_path('phpbb_dir_class_name'),
			'Class in a directory'
		);
		$this->assertEquals(
			$prefix . 'dir/subdir/class_name.php',
			$class_loader->resolve_path('phpbb_dir_subdir_class_name'),
			'Class in a sub-directory'
		);
		$this->assertEquals(
			$prefix . 'dir2/dir2.php',
			$class_loader->resolve_path('phpbb_dir2'),
			'Class with name of dir within dir (short class name)'
		);
	}

	public function test_resolve_cached()
	{
		$cache_map = array(
			'class_loader_phpbb_' => array('phpbb_a_cached_name' => 'a/cached_name'),
			'class_loader_phpbb_ext_' => array('phpbb_ext_foo' => 'foo'),
		);
		$cache = new phpbb_mock_cache($cache_map);

		$prefix = dirname(__FILE__) . '/';
		$class_loader = new phpbb_class_loader('phpbb_', $prefix . 'phpbb/', 'php', $cache);
		$class_loader_ext = new phpbb_class_loader('phpbb_ext_', $prefix . 'phpbb/', 'php', $cache);

		$prefix .= 'phpbb/';

		$this->assertEquals(
			$prefix . 'dir/class_name.php',
			$class_loader->resolve_path('phpbb_dir_class_name'),
			'Class in a directory'
		);

		$this->assertFalse($class_loader->resolve_path('phpbb_ext_foo'));
		$this->assertFalse($class_loader_ext->resolve_path('phpbb_a_cached_name'));

		$this->assertEquals(
			$prefix . 'a/cached_name.php',
			$class_loader->resolve_path('phpbb_a_cached_name'),
			'Cached class found'
		);

		$this->assertEquals(
			$prefix . 'foo.php',
			$class_loader_ext->resolve_path('phpbb_ext_foo'),
			'Cached class found in alternative loader'
		);

		$cache_map['class_loader_phpbb_']['phpbb_dir_class_name'] = 'dir/class_name';
		$cache->check($this, $cache_map);
	}
}
