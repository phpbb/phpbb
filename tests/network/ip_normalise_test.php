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

class phpbb_network_ip_normalise_test extends phpbb_test_case
{
	public function data_provider()
	{
		return array(
			// From: A Recommendation for IPv6 Address Text Representation
			// http://tools.ietf.org/html/draft-ietf-6man-text-addr-representation-07

			// Section 4: A Recommendation for IPv6 Text Representation
			// Section 4.1: Handling Leading Zeros in a 16 Bit Field
			array('2001:0db8::0001', '2001:db8::1'),

			// Section 4.2: "::" Usage
			// Section 4.2.1: Shorten As Much As Possible
			array('2001:db8::0:1', '2001:db8::1'),

			// Section 4.2.2: Handling One 16 Bit 0 Field
			array('2001:db8::1:1:1:1:1', '2001:db8:0:1:1:1:1:1'),

			// Section 4.2.3: Choice in Placement of "::"
			array('2001:db8:0:0:1:0:0:1', '2001:db8::1:0:0:1'),

			// Section 4.3: Lower Case
			array('2001:DB8::1', '2001:db8::1'),

			// Section 5: Text Representation of Special Addresses
			// We want to show IPv4-mapped addresses as plain IPv4 addresses, though.
			array('::ffff:192.168.0.1',			'192.168.0.1'),
			array('0000::0000:ffff:c000:0280',	'192.0.2.128'),

			// IPv6 addresses with the last 32-bit written in dotted-quad notation
			// should be converted to hex-only IPv6 addresses.
			array('2001:db8::192.0.2.128', '2001:db8::c000:280'),

			// Any string not passing the IPv4 or IPv6 regular expression
			// is supposed to result in false being returned.
			// Valid and invalid IP addresses are tested in 
			// tests/regex/ipv4.php and tests/regex/ipv6.php.
			array('', false),
			array('192.168.1.256', false),
			array('::ffff:192.168.255.256', false),
			array('::1111:2222:3333:4444:5555:6666::', false),
		);
	}

	/**
	* @dataProvider data_provider
	*/
	public function test_ip_normalise($ip_address, $expected)
	{
		$this->assertEquals($expected, phpbb_ip_normalise($ip_address));
	}
}
