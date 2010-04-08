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

class phpbb_ipv6_test extends phpbb_test_case
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

			// No leading zeroes in the group
			array('2001:db8:85a3:0:0:8a2e:370:1337'),
			array('2001:db8:85a3:c:d:8a2e:370:1337'),

			// Consecutive all-zero groups
			array('2001:db8:85a3::8a2e:370:1337'),

			// Last 32bit in dotted quad notation
			array('2001:db8:0:1::192.168.0.2'),

			// Mapped IPv4
			array('::ffff:c000:280'),
			array('::ffff:192.0.2.128'),

			// More tests
			array('::'),
			array('0:0:0:0:0:0:0:0'),

			array('::1'),
			array('0:0::0:0:1'),
			array('0:0:0:0:0:0:0:1'),
		);
	}

	public function negative_match_data()
	{
		return array(
			// IPv4 address
			array('192.168.0.2'),

			// Out of hex scope
			array('abcd:efgh:0000::0'),

			// Double ::
			array('2001::23de::2002'),

			// Too many blocks
			array('2001:0db8:85a3:08d3:1319:8a2e:0370:1337:4430'),
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

