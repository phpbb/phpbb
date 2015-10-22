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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

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
		$this->assertEquals($expected, preg_match('#^' . get_preg_expression('url') . '$#iu', $url));
	}
}
