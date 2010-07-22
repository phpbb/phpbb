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

class phpbb_network_inet_ntop_pton_test extends phpbb_test_case
{
	public function data_provider()
	{
		return array(
			array('127.0.0.1',			'7f000001'),
			array('192.232.131.223',	'c0e883df'),

			array('::1',						'00000000000000000000000000000001'),
			array('2001:280:0:10::5',			'20010280000000100000000000000005'),
			array('fe80::200:4cff:fefe:172f',	'fe8000000000000002004cfffefe172f'),
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
