<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../mock/cache.php';

class phpbb_class_loader_test extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		global $class_loader;
		$class_loader->unregister();
	}

	public function tearDown()
	{
		global $class_loader;
		$class_loader->register();
	}

	public function test_resolve_path()
	{
		$prefix = dirname(__FILE__) . '/';
		$class_loader = new phpbb_class_loader($prefix);

		$prefix .= 'includes/';

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
		$cacheMap = array('class_loader' => array('phpbb_a_cached_name' => 'a/cached_name'));
		$cache = new phpbb_mock_cache($cacheMap);

		$prefix = dirname(__FILE__) . '/';
		$class_loader = new phpbb_class_loader($prefix, '.php', $cache);

		$prefix .= 'includes/';

		$this->assertEquals(
			$prefix . 'dir/class_name.php',
			$class_loader->resolve_path('phpbb_dir_class_name'),
			'Class in a directory'
		);

		$this->assertEquals(
			$prefix . 'a/cached_name.php',
			$class_loader->resolve_path('phpbb_a_cached_name'),
			'Class in a directory'
		);

		$cacheMap['class_loader']['phpbb_dir_class_name'] = 'dir/class_name';
		$cache->check($this, $cacheMap);
	}
}
