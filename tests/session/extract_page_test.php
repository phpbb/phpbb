<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/../test_framework/phpbb_session_test_case.php';

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
					//'root_script_path' => '/phpBB/',
					//'page' => 'adm/index.php',
					'forum' => 0,
				),
			),
			array(
				'./',
				'/phpBB/adm/app.php',
				'page=1&test=2',
				'/phpBB/',
				'/foo/bar',
				array(
					'page_name' => 'app.php/foo/bar',
					'page_dir' => '',
					'query_string' => 'page=1&test=2',
					'script_path' => '/phpBB/',
					'root_script_path' => '/phpBB/',
					'page' => 'app.php/foo/bar?page=1&test=2',
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
		global $symfony_request;

		$symfony_request = $this->getMock("\phpbb\symfony_request", array(), array(
			new phpbb_mock_request(),
		));
		$symfony_request->expects($this->any())
			->method('getScriptName')
			->will($this->returnValue($getScriptName));
		$symfony_request->expects($this->any())
			->method('getQueryString')
			->will($this->returnValue($getQueryString));
		$symfony_request->expects($this->any())
			->method('getBasePath')
			->will($this->returnValue($getBasePath));
		$symfony_request->expects($this->any())
			->method('getPathInfo')
			->will($this->returnValue($getPathInfo));

		$output = \phpbb\session::extract_current_page($root_path);

		// This compares the result of the output.
		// Any keys that are not in the expected array are overwritten by the output (aka not checked).
		$this->assert_array_content_equals(array_merge($output, $expected), $output);
	}
}
