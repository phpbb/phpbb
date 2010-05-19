<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once 'test_framework/framework.php';
require_once '../phpBB/includes/functions.php';

class phpbb_regex_url_test extends phpbb_test_case
{
	public function url_test_data()
	{
		return array(
			array('http://www.phpbb.com/community/', 1),
			array('http://www.phpbb.com/path/file.ext#section', 1),
			array('ftp://ftp.phpbb.com/', 1),
			array('sip://bantu@phpbb.com', 1),

			array('www.phpbb.com/community/', 0),
		);
	}

	/**
	* @dataProvider url_test_data
	*/
	public function test_url($url, $expected)
	{
		$this->assertEquals($expected, preg_match('#^' . get_preg_expression('url') . '$#i', $url));
	}
}
