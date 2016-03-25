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

require_once dirname(__FILE__) . '/base.php';

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_security_extract_current_page_test extends phpbb_security_test_base
{
	public function security_variables()
	{
		return array(
			array('mark=forums&x="><script>alert(/XSS/);</script>', 'mark=forums&x=%22%3E%3Cscript%3Ealert%28%2FXSS%2F%29%3B%3C%2Fscript%3E'),
			array('mark=forums&x=%22%3E%3Cscript%3Ealert(/XSS/);%3C/script%3E', 'mark=forums&x=%22%3E%3Cscript%3Ealert%28%2FXSS%2F%29%3B%3C%2Fscript%3E'),
			array('mark=forums&x=%22%3E%3Cscript%3Ealert%28%2FXSS%2F%29%3B%3C%2Fscript%3E', 'mark=forums&x=%22%3E%3Cscript%3Ealert%28%2FXSS%2F%29%3B%3C%2Fscript%3E'),
		);
	}

	/**
	* @dataProvider security_variables
	*/
	public function test_query_string_php_self($query_string, $expected)
	{
		global $symfony_request, $request;

		$this->server['REQUEST_URI'] = '';
		$this->server['QUERY_STRING'] = $query_string;

		$request = new phpbb_mock_request(array(), array(), array(), $this->server);
		$symfony_request = new \phpbb\symfony_request($request);

		$result = \phpbb\session::extract_current_page('./');

		$label = 'Running extract_current_page on ' . $query_string . ' with PHP_SELF filled.';
		$this->assertEquals($expected, $result['query_string'], $label);
	}

	/**
	* @dataProvider security_variables
	*/
	public function test_query_string_request_uri($query_string, $expected)
	{
		global $symfony_request, $request;

		$this->server['QUERY_STRING'] = $query_string;

		$request = new phpbb_mock_request(array(), array(), array(), $this->server);
		$symfony_request = new \phpbb\symfony_request($request);

		$result = \phpbb\session::extract_current_page('./');

		$label = 'Running extract_current_page on ' . $query_string . ' with REQUEST_URI filled.';
		$this->assertEquals($expected, $result['query_string'], $label);
	}
}
