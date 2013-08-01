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
				'/phpBB/ucp.php?mode=login',
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
				'/phpBB/ucp.php?mode=register',
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
				'/phpBB/ucp.php?mode=register',
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
				'/phpBB/adm/index.php?sid=e7215d958cdd41a6fc13509bebe53e42',
				array(
					'page_name' => 'index.php',
					//'page_dir' => 'adm',
					// ^-- Ignored because .. returns different directory in live vs testing
					'query_string' => '',
					'script_path' => '/phpBB/adm/',
					'root_script_path' => '/phpBB/',
					//'page' => 'adm/index.php',
					'forum' => 0,
				),
			),
		);
	}

	/** @dataProvider extract_current_page_data */
	function test_extract_current_page($root_path, $php_self, $query_string, $request_uri, $expected)
	{
		$output = $this->session_facade->extract_current_page(
			$root_path,
			$php_self,
			$query_string,
			$request_uri
		);

		// This compares the result of the output.
		// Any keys that are not in the expected array are overwritten by the output (aka not checked).
		$this->assert_array_content_equals(array_merge($output, $expected), $output);
	}
}
