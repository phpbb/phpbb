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

 use phpbb\filesystem\helper as filesystem_helper;

class phpbb_filesystem_helper_realpath_test extends phpbb_test_case
{
	protected static $filesystem_helper_phpbb_own_realpath;

	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		self::$filesystem_helper_phpbb_own_realpath = new ReflectionMethod('filesystem_helper', 'phpbb_own_realpath');
		self::$filesystem_helper_phpbb_own_realpath->setAccessible(true);
	}

	public function setUp()
	{
		parent::setUp();
	}

	public function realpath_resolve_absolute_without_symlinks_data()
	{
		return array(
			// Constant data
			array(__DIR__, __DIR__),
			array(__DIR__ . '/../filesystem/../filesystem', __DIR__),
			array(__DIR__ . '/././', __DIR__),
			array(__DIR__ . '/non_existent', false),

			array(__FILE__, __FILE__),
			array(__FILE__ . '../', false),
		);
	}

	public function realpath_resolve_relative_without_symlinks_data()
	{
		if (!function_exists('getcwd'))
		{
			return array();
		}

		$relative_path = filesystem_helper::make_path_relative(__DIR__, getcwd());

		return array(
			array($relative_path, __DIR__),
			array($relative_path . '../filesystem/../filesystem', __DIR__),
			array($relative_path . '././', __DIR__),

			array($relative_path . 'helper_realpath_test.php', __FILE__),
		);
	}

	/**
	 * @dataProvider realpath_resolve_absolute_without_symlinks_data
	 */
	public function test_realpath_absolute_without_links($path, $expected)
	{
		$this->assertEquals($expected, self::$filesystem_helper_phpbb_own_realpath->invoke(null, $path));
	}

	/**
	 * @dataProvider realpath_resolve_relative_without_symlinks_data
	 */
	public function test_realpath_relative_without_links($path, $expected)
	{
		if (!function_exists('getcwd'))
		{
			$this->markTestSkipped('phpbb_own_realpath() cannot be tested with relative paths: getcwd is not available.');
		}

		$this->assertEquals($expected, self::$filesystem_helper_phpbb_own_realpath->invoke(null, $path));
	}
}
