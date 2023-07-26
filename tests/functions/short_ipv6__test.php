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

class short_ipv6__test extends phpbb_test_case
{
	public function data_short_ipv6(): array
	{
		return [
			['::1', 0, ''],
			['::1', 1, '0000:0000'],
			['::1', 2, '0000:0000:0000'],
			['::1', 3, '0000:0000:0000:0000'],
			['::1', 4, '0000:0000:0000:0000:0000:0000:0000:1'],
			['2001:db8:3333:4444:5555:6666:7777:8888', 0, ''],
			['2001:db8:3333:4444:5555:6666:7777:8888', 1, '2001:db8'],
			['2001:db8:3333:4444:5555:6666:7777:8888', 2, '2001:db8:3333'],
			['2001:db8:3333:4444:5555:6666:7777:8888', 3, '2001:db8:3333:4444'],
			['2001:db8:3333:4444:5555:6666:7777:8888', 4, '2001:db8:3333:4444:5555:6666:7777:8888'],
			['::ffff:192.168.1.1', 0, ''],
			['::ffff:192.168.1.1', 1, '0000:0000'],
			['::ffff:192.168.1.1', 2, '0000:0000:0000'],
			['::ffff:192.168.1.1', 3, '0000:0000:0000:0000'],
			['::ffff:192.168.1.1', 4, '0000:0000:0000:0000:0000:0000:ffff:192.168.1.1'],
			['FADE:BAD::192.168.0.1', 0, ''],
			['FADE:BAD::192.168.0.1', 1, 'fade:bad'],
			['FADE:BAD::192.168.0.1', 2, 'fade:bad:0000'],
			['FADE:BAD::192.168.0.1', 3, 'fade:bad:0000:0000'],
			['FADE:BAD::192.168.0.1', 4, 'fade:bad:0000:0000:0000:0000:c0a8:1'],
		];
	}

	/**
	* @dataProvider data_short_ipv6
	*/
	public function test_short_ipv6($ip, $length, $expected)
	{
		$this->assertEquals($expected, short_ipv6($ip, $length));
	}
}
