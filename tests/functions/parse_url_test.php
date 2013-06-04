<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_parse_url_test extends phpbb_test_case
{
	public function parse_url_test_data()
	{
		return array(
			array(
				'http://1.2.3.4/foo/bar.php?query1#foobar',
				array(
					'scheme' => 'http',
					'host' => '1.2.3.4',
					'path' => '/foo/bar.php',
					'query' => 'query1',
					'fragment' => 'foobar',
				),
			),
			array(
				'//1.2.3.4:80/foo/bar.php',
				false,
			),
			array(
				'//phpbb@1.2.3.4/foo/bar.php',
				array(
					'host' => '1.2.3.4',
					'user' => 'phpbb',
					'path' => '/foo/bar.php',
				),
			),
			array(
				'styles/test/foo.js?v=1#bar',
				array(
					'path' => 'styles/test/foo.js',
					'query' => 'v=1',
					'fragment' => 'bar',
				),
			),
		);
	}

	public function join_url_test_data()
	{
		return array(
			array('styles/test/foo.js?v=1#bar', false, 'styles/test/foo.js?v=1#bar'),
			array('/styles/test/foo.js?v=1#bar', true, '/styles/test/foo.js?v=1#bar'),
			array('styles/test 1/foo.js?v=1#bar', true, 'styles/test%201/foo.js?v=1#bar'),
			array('styles/test\'1/foo.js?v=1#bar', true, 'styles/test%271/foo.js?v=1#bar'),
			array('http://1.2.3.4/foo/bar.php?query1#foobar', false, 'http://1.2.3.4/foo/bar.php?query1#foobar'),
			array('//1.2.3.4/foo/bar.php?query1#foobar', false, '//1.2.3.4/foo/bar.php?query1#foobar'),
		);
	}

	/**
	* @dataProvider parse_url_test_data
	*/
	public function test_parse_url($url, $expected)
	{
		$parts = phpbb_parse_url($url);
		$this->assertEquals($expected, $parts);
	}

	/**
	* @dataProvider join_url_test_data
	*/
	public function test_join_url($url, $encode, $expected)
	{
		$parts = phpbb_parse_url($url);
		$joined = phpbb_join_url($parts, $encode);
		$this->assertEquals($expected, $joined);
	}
}
