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

class phpbb_get_install_redirect_test extends phpbb_test_case
{
	public function data_redirect(): array
	{
		return [
			[
				['REQUEST_URI'	=> '/foo/bar/'],
				'/foo/bar/install/app.php',
			],
			[
				['REQUEST_URI'	=> '/foo/bar/index.php'],
				'/foo/bar/install/app.php',
			],
			[
				['REQUEST_URI'	=> '/foo/bar'],
				'/foo/install/app.php',
			],
			[
				['REQUEST_URI'	=> '/foo/'],
				'/foo/install/app.php',
			],
			[
				['REQUEST_URI'	=> '/foo/index.php'],
				'/foo/install/app.php',
			],
			[
				[
					'REQUEST_URI'	=> '/foo/bar/',
					'PHP_SELF'		=> '/foo/bar/index.php'
				],
				'/foo/bar/install/app.php',
			],
			[
				[
					'REQUEST_URI'	=> '',
					'PHP_SELF'		=> '/foo/bar/index.php'
				],
				'/foo/bar/install/app.php',
			],
		];
	}

	/**
	 * @backupGlobals enabled
	 * @dataProvider data_redirect
	 */
	public function test_install_redirect($server_vars, $expected)
	{
		$phpbb_root_path = '/';
		$phpEx = 'php';

		$_SERVER = array_merge($_SERVER, $server_vars);
		$this->assertEquals($expected, phpbb_get_install_redirect($phpbb_root_path, $phpEx));
	}
}
