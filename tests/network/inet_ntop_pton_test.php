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

class phpbb_network_inet_ntop_pton_test extends phpbb_test_case
{
	public function data_provider()
	{
		return array(
			array('127.0.0.1',						'7f000001'),
			array('192.232.131.223',				'c0e883df'),
			array('13.1.68.3',						'0d014403'),
			array('129.144.52.38',					'81903426'),

			array('2001:280:0:10::5',				'20010280000000100000000000000005'),
			array('fe80::200:4cff:fefe:172f',		'fe8000000000000002004cfffefe172f'),

			array('::',								'00000000000000000000000000000000'),
			array('::1',							'00000000000000000000000000000001'),
			array('1::',							'00010000000000000000000000000000'),

			array('1:1:0:0:1::',					'00010001000000000001000000000000'),

			array('0:2:3:4:5:6:7:8',				'00000002000300040005000600070008'),
			array('1:2:0:4:5:6:7:8',				'00010002000000040005000600070008'),
			array('1:2:3:4:5:6:7:0',				'00010002000300040005000600070000'),

			array('2001:0:0:1::1',					'20010000000000010000000000000001'),
		);
	}

	/**
	* @dataProvider data_provider
	*/
	public function test_inet_ntop($address, $hex)
	{
		$this->assertEquals($address, phpbb_inet_ntop(pack('H*', $hex)));
	}

	/**
	* @dataProvider data_provider
	*/
	public function test_inet_pton($address, $hex)
	{
		$this->assertEquals($hex, bin2hex(phpbb_inet_pton($address)));
	}
}
