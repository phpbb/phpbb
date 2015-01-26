<?php
/**
 *
 * @package testing
 * @copyright (c) 2011 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/../../phpBB/includes/startup.php';

class phpbb_security_trailing_path_test extends phpbb_test_case
{
	public function data_has_trailing_path()
	{
		return array(
			array(false, '', '', ''),
			array(true, '/', '', ''),
			array(true, '/foo', '', ''),
			array(true, '', '/foo', ''),
			array(true, '/foo', '/foo', ''),
			array(false, '', '', '/'),
			array(false, '', '', '/?/x.php/'),
			array(false, '', '', '/index.php'),
			array(false, '', '', '/dir.phpisfunny/foo.php'),
			array(true, '', '', '/index.php/foo.php'),
			array(false, '', '', '/phpBB/viewtopic.php?f=3&amp;t=5'),
			array(false, '', '', '/phpBB/viewtopic.php?f=3&amp;t=5/'),
			array(false, '', '', '/phpBB/viewtopic.php?f=3&amp;t=5/foo'),
			array(true, '/foo', '/foo', '/phpBB/viewtopic.php?f=3&amp;t=5/foo'),
			array(false, '', '', '/projects/php.bb/phpBB/viewtopic.php?f=3&amp;t=5/'),
			array(false, '', '', '/projects/php.bb/phpBB/viewtopic.php?f=3&amp;t=5'),
			array(false, '', '', '/projects/php.bb/phpBB/viewtopic.php?f=3&amp;t=5/foo.php/'),
			array(false, '', '', '/projects/php.bb/phpBB/index.php'),
			array(true, '', '', '/projects/php.bb/phpBB/index.php/'),
			array(true, '', '', '/phpBB/index.php/?foo/a'),
			array(true, '', '', '/projects/php.bb/phpBB/index.php/?a=5'),
			array(false, '', '', '/projects/php.bb/phpBB/index.php?/a=5'),
		);
	}

	/**
	 * @dataProvider data_has_trailing_path
	 */
	public function test_has_trailing_path($expected, $path_info, $orig_path_info, $request_uri)
	{
		global $phpEx;

		$_SERVER['PATH_INFO'] = $path_info;
		$_SERVER['ORIG_PATH_INFO'] = $orig_path_info;
		$_SERVER['REQUEST_URI'] = $request_uri;

		$this->assertSame($expected, phpbb_has_trailing_path($phpEx));
	}
}
