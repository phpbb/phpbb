<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_download.php';

class phpbb_download_http_byte_range_test extends phpbb_test_case
{
	public function test_find_range_request()
	{
		// Missing 'bytes=' prefix
		$_SERVER['HTTP_RANGE'] = 'bztes=';
		$this->assertEquals(false, phpbb_find_range_request());
		unset($_SERVER['HTTP_RANGE']);

		$_ENV['HTTP_RANGE'] = 'bztes=';
		$this->assertEquals(false, phpbb_find_range_request());
		unset($_ENV['HTTP_RANGE']);

		$_SERVER['HTTP_RANGE'] = 'bytes=0-0,123-125';
		$this->assertEquals(array('0-0', '123-125'), phpbb_find_range_request());
		unset($_SERVER['HTTP_RANGE']);
	}

	/**
	* @dataProvider parse_range_request_data()
	*/
	public function test_parse_range_request($request_array, $filesize, $expected)
	{
		$this->assertEquals($expected, phpbb_parse_range_request($request_array, $filesize));
	}

	public function parse_range_request_data()
	{
		return array(
			// Does not read until the end of file.
			array(
				array('3-4'),
				10,
				false,
			),

			// Valid request, handle second range.
			array(
				array('0-0', '120-125'),
				125,
				array(
					'byte_pos_start'	=> 120,
					'byte_pos_end'		=> 124,
					'bytes_requested'	=> 5,
					'bytes_total'		=> 125,
				)
			),
		);
	}
}
