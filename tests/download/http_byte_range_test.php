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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_download.php';

class phpbb_download_http_byte_range_test extends phpbb_test_case
{
	public function test_find_range_request()
	{
		// Missing 'bytes=' prefix
		$GLOBALS['request'] = new phpbb_mock_request();
		$GLOBALS['request']->set_header('Range', 'bztes=');
		$this->assertEquals(false, phpbb_find_range_request());
		unset($GLOBALS['request']);

		$GLOBALS['request'] = new phpbb_mock_request();
		$_ENV['HTTP_RANGE'] = 'bztes=';
		$this->assertEquals(false, phpbb_find_range_request());
		unset($_ENV['HTTP_RANGE']);

		$GLOBALS['request'] = new phpbb_mock_request();
		$GLOBALS['request']->set_header('Range', 'bytes=0-0,123-125');
		$this->assertEquals(array('0-0', '123-125'), phpbb_find_range_request());
		unset($GLOBALS['request']);
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
            // Valid request
			array(
				array('3-4'),
				10,
				array(
					'byte_pos_start'	=> 3,
					'byte_pos_end'		=> 4,
					'bytes_requested'	=> 2,
					'bytes_total'		=> 10,
				),
			),

            // Get the beginning
			array(
				array('-5'),
				10,
				array(
					'byte_pos_start'	=> 0,
					'byte_pos_end'		=> 5,
					'bytes_requested'	=> 6,
					'bytes_total'		=> 10,
				),
			),

            // Get the end
			array(
				array('5-'),
				10,
				array(
					'byte_pos_start'	=> 5,
					'byte_pos_end'		=> 9,
					'bytes_requested'	=> 5,
					'bytes_total'		=> 10,
				),
			),

            // Overlong request
			array(
				array('3-20'),
				10,
				array(
					'byte_pos_start'	=> 3,
					'byte_pos_end'		=> 9,
					'bytes_requested'	=> 7,
					'bytes_total'		=> 10,
				),
			),

			// Multiple, contiguous range
			array(
				array('10-20', '21-30'),
				125,
				array(
					'byte_pos_start'	=> 10,
					'byte_pos_end'		=> 30,
					'bytes_requested'	=> 21,
					'bytes_total'		=> 125,
				)
			),

			// We don't do multiple, non-contiguous range
			array(
				array('0-0', '120-125'),
				125,
				false,
			),
		);
	}
}
