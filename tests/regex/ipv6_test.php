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

class phpbb_regex_ipv6_test extends phpbb_test_case
{
	protected $regex;

	public function setUp()
	{
		$this->regex = get_preg_expression('ipv6');
	}

	public function positive_match_data()
	{
		return array(
			// Full length IPv6 address
			array('2001:0db8:85a3:0000:0000:8a2e:0370:1337'),
			array('0000:0000:0000:0000:0000:0000:0000:0001'),
			array('3FFE:0b00:0000:0000:0001:0000:0000:000a'),
			array('3ffe:0b00:0000:0000:0001:0000:0000:000a'),
			array('2002:0db8:0000:0000:0000:dead:1337:d00d'),

			// No leading zeroes in the group
			array('2001:db8:85a3:0:0:8a2e:370:1337'),
			array('2001:db8:85a3:c:d:8a2e:370:1337'),

			// Consecutive all-zero groups
			array('2001:db8:85a3::8a2e:370:1337'),
			array('1::2:3:4:5:6:7'),
			array('1::2:3:4:5:6'),
			array('1::2:3:4:5'),
			array('1::2:3:4'),
			array('1::2:3'),
			array('1::2'),

			// Last 32bit in dotted quad notation
			array('2001:db8:0:1::192.168.0.2'),

			// IPv4-compatible IPv6 address
			array('::13.1.68.3'),
			array('0:0:0:0:0:0:13.1.68.3'),

			// IPv4-mapped IPv6 address
			array('::ffff:c000:280'),
			array('::ffff:c000:0280'),
			array('::ffff:192.0.2.128'),
			array('0:0:0:0:0:ffff:c000:280'),
			array('0:0:0:0:0:ffff:c000:0280'),
			array('0:0:0:0:0:ffff:192.0.2.128'),
			array('0000:0000:0000:0000:0000:ffff:c000:280'),
			array('0000:0000:0000:0000:0000:ffff:c000:0280'),
			array('0000:0000:0000:0000:0000:ffff:192.0.2.128'),

			// No trailing zeroes
			array('fe80::'),
			array('2002::'),
			array('2001:db8::'),
			array('2001:0db8:1234::'),
			array('1:2:3:4:5:6::'),
			array('1:2:3:4:5::'),
			array('1:2:3:4::'),
			array('1:2:3::'),
			array('1:2::'),

			// No leading zeroes
			array('::2:3:4:5:6:7:8'),
			array('::2:3:4:5:6:7'),
			array('::2:3:4:5:6'),
			array('::2:3:4:5'),
			array('::2:3:4'),
			array('::2:3'),
			array('::1'),
			array('::8'),
			array('::c'),
			array('::abcd'),

			// All zeroes
			array('::'),
			array('0:0:0:0:0:0:0:0'),
			array('0000:0000:0000:0000:0000:0000:0000:0000'),

			// More tests
			array('2::10'),
			array('0:0::0:0:1'),
			array('0:0:0:0:0:0:0:1'),
			array('::ffff:0:0'),
		);
	}

	public function negative_match_data()
	{
		return array(
			// Empty address
			array(''),

			// IPv4 address
			array('192.168.0.2'),

			// Out of scope
			array('abcd:efgh:0000::0'),
			array('::ffff:192.168.255.256'),

			// Double ::
			array('2001::23de::2002'),
			array('3ffe:b00::1::b'),
			array('::1111:2222:3333:4444:5555:6666::'),

			// Too many blocks
			array('2001:0db8:85a3:08d3:1319:8a2e:0370:1337:4430'),

			// More tests
			array('02001:0000:1234:0000:0000:C1C0:ABCD:9876'),
			array('2001:0000:1234: 0000:0000:C1C0:ABCD:9876'),
			array('::ffff:192x168.255.255'),
		);
	}

	/**
	* @dataProvider positive_match_data
	*/
	public function test_positive_match($address)
	{
		$this->assertEquals(1, preg_match($this->regex, $address));
	}

	/**
	* @dataProvider negative_match_data
	*/
	public function test_negative_match($address)
	{
		$this->assertEquals(0, preg_match($this->regex, $address));
	}
}

