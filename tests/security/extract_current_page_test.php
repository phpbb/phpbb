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
require_once dirname(__FILE__) . '/../../phpBB/includes/session.php';

class phpbb_security_extract_current_page_test extends phpbb_security_test_base
{
	static public function security_variables()
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
		$_SERVER['PHP_SELF'] = $url;
		$_SERVER['QUERY_STRING'] = $query_string;

		$result = session::extract_current_page('./');

		$label = 'Running extract_current_page on ' . $query_string . ' with PHP_SELF filled.';
		$this->assertEquals($expected, $result['query_string'], $label);
	}

	/**
	* @dataProvider security_variables
	*/
	public function test_query_string_request_uri($url, $query_string, $expected)
	{
		$_SERVER['REQUEST_URI'] = $url . '?' . $query_string;
		$_SERVER['QUERY_STRING'] = $query_string;

		$result = session::extract_current_page('./');

		$label = 'Running extract_current_page on ' . $query_string . ' with REQUEST_URI filled.';
		$this->assertEquals($expected, $result['query_string'], $label);
	}
}

