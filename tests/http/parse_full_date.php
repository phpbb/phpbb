<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_test_parse_http_date extends phpbb_test_case
{
	/**
	* @dataProvider parse_http_date_data()
	*/
	public function test_parse_http_date($expected, $date)
	{
		$this->assertEquals($expected, phpbb_parse_http_date($date));
	}

	public function parse_http_date_data()
	{
		return array(
			array(784111777,	'Sun, 06 Nov 1994 08:49:37 GMT'),
			array(784111777,	'Sunday, 06-Nov-94 08:49:37 GMT'),
			array(false,		'blarg'),
		);
	}
}
