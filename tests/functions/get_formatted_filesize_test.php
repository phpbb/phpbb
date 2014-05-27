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

			array(1023, '1023 BYTES'),
			array(1025, '1 KIB'),
			array(1048575, '1024 KIB'),

			// String values
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

			array('1023', '1023 BYTES'),
			array('1025', '1 KIB'),
			array('1048575', '1024 KIB'),
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
}
