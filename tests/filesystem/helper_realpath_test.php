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

	static public function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		self::$filesystem_helper_phpbb_own_realpath = new ReflectionMethod('\phpbb\filesystem\helper', 'phpbb_own_realpath');
		self::$filesystem_helper_phpbb_own_realpath->setAccessible(true);
	}

	protected function setUp(): void
	{
		parent::setUp();
	}

	public function realpath_resolve_absolute_without_symlinks_data()
	{
		// Constant data
		yield [__DIR__, __DIR__];
		yield [__DIR__ . '/../filesystem/../filesystem', __DIR__];
		yield [__DIR__ . '/././', __DIR__];
		yield [__DIR__ . '/non_existent', false];

		yield [__FILE__, __FILE__];
		yield [__FILE__ . '../', false];
	}

	public function realpath_resolve_relative_without_symlinks_data()
	{
		if (!function_exists('getcwd'))
		{
			yield [];
		}
		else
		{
			$relative_path = filesystem_helper::make_path_relative(__DIR__, getcwd());

			yield [$relative_path, __DIR__];
			yield [$relative_path . '../filesystem/../filesystem', __DIR__];
			yield [$relative_path . '././', __DIR__];

			yield [$relative_path . 'helper_realpath_test.php', __FILE__];
		}
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
