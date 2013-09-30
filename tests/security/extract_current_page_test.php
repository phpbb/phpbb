<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/base.php';

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_security_extract_current_page_test extends phpbb_security_test_base
{
	public function security_variables()
	{
		return array(
			array('http://localhost/phpBB/index.php', 'mark=forums&x="><script>alert(/XSS/);</script>', 'mark=forums&x=%22%3E%3Cscript%3Ealert(/XSS/);%3C/script%3E'),
			array('http://localhost/phpBB/index.php', 'mark=forums&x=%22%3E%3Cscript%3Ealert(/XSS/);%3C/script%3E', 'mark=forums&x=%22%3E%3Cscript%3Ealert(/XSS/);%3C/script%3E'),
		);
	}

	/**
	* @dataProvider security_variables
	*/
	public function test_query_string_php_self($url, $query_string, $expected)
	{
		global $symfony_request, $request;

		$symfony_request = $this->getMock("phpbb_symfony_request", array(), array(
			$request,
		));
		$symfony_request->expects($this->any())
			->method('getScriptName')
			->will($this->returnValue($url));
		$symfony_request->expects($this->any())
			->method('getQueryString')
			->will($this->returnValue($query_string));
		$symfony_request->expects($this->any())
			->method('getBasePath')
			->will($this->returnValue($server['REQUEST_URI']));
		$symfony_request->expects($this->any())
			->method('getPathInfo')
			->will($this->returnValue('/'));
		$result = phpbb_session::extract_current_page('./');

		$label = 'Running extract_current_page on ' . $query_string . ' with PHP_SELF filled.';
		$this->assertEquals($expected, $result['query_string'], $label);
	}

	/**
	* @dataProvider security_variables
	*/
	public function test_query_string_request_uri($url, $query_string, $expected)
	{
		global $symfony_request, $request;

		$symfony_request = $this->getMock("phpbb_symfony_request", array(), array(
			$request,
		));
		$symfony_request->expects($this->any())
			->method('getScriptName')
			->will($this->returnValue($url));
		$symfony_request->expects($this->any())
			->method('getQueryString')
			->will($this->returnValue($query_string));
		$symfony_request->expects($this->any())
			->method('getBasePath')
			->will($this->returnValue($server['REQUEST_URI']));
		$symfony_request->expects($this->any())
			->method('getPathInfo')
			->will($this->returnValue('/'));

		$result = \phpbb\session::extract_current_page('./');

		$label = 'Running extract_current_page on ' . $query_string . ' with REQUEST_URI filled.';
		$this->assertEquals($expected, $result['query_string'], $label);
	}
}
