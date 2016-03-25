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

require_once dirname(__FILE__) . '/../test_framework/phpbb_session_test_case.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_session_extract_page_test extends phpbb_session_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/sessions_empty.xml');
	}

	static public function extract_current_page_data()
	{
		return array(
			array(
				'./',
				'/phpBB/index.php',
				'',
				'/phpBB/',
				'/',
				array(
					'page_name' => 'index.php',
					'page_dir' => '',
					'query_string' => '',
					'script_path' => '/phpBB/',
					'root_script_path' => '/phpBB/',
					'page' => 'index.php',
					'forum' => 0,
				),
			),
			array(
				'./',
				'/phpBB/ucp.php',
				'mode=login',
				'/phpBB/',
				'/',
				array(
					'page_name' => 'ucp.php',
					'page_dir' => '',
					'query_string' => 'mode=login',
					'script_path' => '/phpBB/',
					'root_script_path' => '/phpBB/',
					'page' => 'ucp.php?mode=login',
					'forum' => 0,
				),
			),
			array(
				'./',
				'/phpBB/ucp.php',
				'mode=register',
				'/phpBB/',
				'/',
				array(
					'page_name' => 'ucp.php',
					'page_dir' => '',
					'query_string' => 'mode=register',
					'script_path' => '/phpBB/',
					'root_script_path' => '/phpBB/',
					'page' => 'ucp.php?mode=register',
					'forum' => 0,
				),
			),
			array(
				'./',
				'/phpBB/ucp.php',
				'mode=register',
				'/phpBB/',
				'/',
				array(
					'page_name' => 'ucp.php',
					'page_dir' => '',
					'query_string' => 'mode=register',
					'script_path' => '/phpBB/',
					'root_script_path' => '/phpBB/',
					'page' => 'ucp.php?mode=register',
					'forum' => 0,
				),
			),
			array(
				'./../',
				'/phpBB/adm/index.php',
				'sid=e7215d958cdd41a6fc13509bebe53e42',
				'/phpBB/adm/',
				'/',
				array(
					'page_name' => 'index.php',
					//'page_dir' => 'adm',
					// ^-- Ignored because .. returns different directory in live vs testing
					'query_string' => '',
					'script_path' => '/phpBB/adm/',
					//'root_script_path' => '/phpBB/adm/',
					//'page' => 'adm/index.php',
					'forum' => 0,
				),
			),
			array(
				'./',
				'/phpBB/adm/app.php',
				'page=1&test=2',
				'/phpBB/adm/',
				'/foo/bar',
				array(
					'page_name' => 'app.php/foo/bar',
					//'page_dir' => '',
					'query_string' => 'page=1&test=2',
					'script_path' => '/phpBB/adm/',
					//'root_script_path' => '/phpBB/adm/',
					//'page' => 'app.php/foo/bar?page=1&test=2',
					'forum' => 0,
				),
			),
			array(
				'./../phpBB/',
				'/test/test.php',
				'page=1&test=2',
				'/test/',
				'',
				array(
					'page_name' => 'test.php',
					//'page_dir' => '',
					'query_string' => 'page=1&test=2',
					'script_path' => '/test/',
					//'root_script_path' => '../phpBB/',
					//'page' => '../test/test.php/foo/bar?page=1&test=2',
					'forum' => 0,
				),
			),
		);
	}

	/** @dataProvider extract_current_page_data */
	function test_extract_current_page($root_path, $getScriptName, $getQueryString, $getBasePath, $getPathInfo, $expected)
	{
		global $symfony_request, $request, $phpbb_filesystem;

		$phpbb_filesystem = new \phpbb\filesystem\filesystem();

		$server['HTTP_HOST']			= 'localhost';
		$server['SERVER_NAME']			= 'localhost';
		$server['SERVER_ADDR']			= '127.0.0.1';
		$server['SERVER_PORT']			= 80;
		$server['REMOTE_ADDR']			= '127.0.0.1';
		$server['QUERY_STRING']			= $getQueryString;
		$server['REQUEST_URI']			= $getScriptName . $getPathInfo . ($getQueryString === '' ? '' : '?' . $getQueryString);
		$server['SCRIPT_NAME']			= $getScriptName;
		$server['SCRIPT_FILENAME']		= '/var/www/' . $getScriptName;
		$server['PHP_SELF']				= $getScriptName;
		$server['HTTP_USER_AGENT']		= 'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.8.1.14) Gecko/20080404 Firefox/2.0.0.14';
		$server['HTTP_ACCEPT_LANGUAGE']	= 'de-de,de;q=0.8,en-us;q=0.5,en;q=0.3';

		$request = new phpbb_mock_request(array(), array(), array(), $server);
		$symfony_request = new \phpbb\symfony_request($request);

		$output = \phpbb\session::extract_current_page($root_path);

		// This compares the result of the output.
		// Any keys that are not in the expected array are overwritten by the output (aka not checked).
		$this->assert_array_content_equals(array_merge($output, $expected), $output);
	}
}
