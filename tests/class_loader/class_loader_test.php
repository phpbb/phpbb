<?php
/**
*
* @package testing
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once 'test_framework/framework.php';
require_once 'class_loader/cache_mock.php';

require_once '../phpBB/includes/class_loader.php';


class phpbb_class_loader_test extends PHPUnit_Framework_TestCase
{
	public function test_resolve_path()
	{
		$prefix = 'class_loader/';
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
		$cache = new phpbb_cache_mock;
		$cache->put('class_loader', array('phpbb_a_cached_name' => 'a/cached_name'));

		$prefix = 'class_loader/';
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
	}
}
