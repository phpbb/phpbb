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
		global $request;

		$request->merge(phpbb_request_interface::SERVER, array(
			'PHP_SELF'	=> $url,
			'QUERY_STRING'	=> $query_string,
		));

		$result = phpbb_session::extract_current_page('./');

		$label = 'Running extract_current_page on ' . $query_string . ' with PHP_SELF filled.';
		$this->assertEquals($expected, $result['query_string'], $label);
	}

	/**
	* @dataProvider security_variables
	*/
	public function test_query_string_request_uri($url, $query_string, $expected)
	{
		global $request;

		$request->merge(phpbb_request_interface::SERVER, array(
			'PHP_SELF'	=> $url,
			'QUERY_STRING'	=> $query_string,
		));

		$result = phpbb_session::extract_current_page('./');

		$label = 'Running extract_current_page on ' . $query_string . ' with REQUEST_URI filled.';
		$this->assertEquals($expected, $result['query_string'], $label);
	}
}

