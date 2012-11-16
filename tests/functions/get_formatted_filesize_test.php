<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_get_formatted_filesize_test extends phpbb_test_case
{
	public function get_formatted_filesize_test_data()
	{
		return array(
			// exact powers of 2
			array(1, '1 BYTES'),
			array(1024, '1 KIB'),
			array(1048576, '1 MIB'),
			array(1073741824, '1 GIB'),
			array(1099511627776, '1 TIB'),

			// exact powers of 10
			array(1000, '1000 BYTES'),
			array(1000000, '976.56 KIB'),
			array(1000000000, '953.67 MIB'),
			array(1000000000000, '931.32 GIB'),
			array(100000000000000, '90.95 TIB'),

			array(0, '0 BYTES'),
			array(2, '2 BYTES'),
			array(-2, '-2 BYTES'),

			array(1023, '1023 BYTES'),
			array(1025, '1 KIB'),
			array(-1023, '-1023 BYTES'),
			array(-1025, '-1025 BYTES'),

			array(1048575, '1024 KIB'),

			// large negatives
			array(-1073741824, '-1073741824 BYTES'),
			array(-1099511627776, '-1099511627776 BYTES'),
		);
	}

	public function get_formatted_filesize_test_data_string()
	{
		return array(
			// exact powers of 2
			array('1', '1 BYTES'),
			array('1024', '1 KIB'),
			array('1048576', '1 MIB'),
			array('1073741824', '1 GIB'),
			array('1099511627776', '1 TIB'),

			// exact powers of 10
			array('1000', '1000 BYTES'),
			array('1000000', '976.56 KIB'),
			array('1000000000', '953.67 MIB'),
			array('1000000000000', '931.32 GIB'),
			array('100000000000000', '90.95 TIB'),

			array('0', '0 BYTES'),
			array('2', '2 BYTES'),
			array('-2', '-2 BYTES'),

			array('1023', '1023 BYTES'),
			array('1025', '1 KIB'),
			array('-1023', '-1023 BYTES'),
			array('-1025', '-1025 BYTES'),

			array('1048575', '1024 KIB'),

			// large negatives
			array('-1073741824', '-1073741824 BYTES'),
			array('-1099511627776', '-1099511627776 BYTES'),
		);
	}

	/**
	* @dataProvider get_formatted_filesize_test_data
	*/
	public function test_get_formatted_filesize($input, $expected)
	{
		$output = get_formatted_filesize($input);

		$this->assertEquals($expected, $output);
	}

	/**
	* @dataProvider get_formatted_filesize_test_data_string
	*/
	public function test_get_formatted_filesize_string($input, $expected)
	{
		$output = get_formatted_filesize($input);

		$this->assertEquals($expected, $output);
	}
}
