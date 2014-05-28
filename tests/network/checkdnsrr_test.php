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

/**
* @group slow
*/
class phpbb_network_checkdnsrr_test extends phpbb_test_case
{
	public function data_provider()
	{
		return array(
			// Existing MX record
			array('phpbb.com', 'MX', true),

			// Non-existing MX record
			array('does-not-exist.phpbb.com', 'MX', false),

			// Existing A record
			array('www.phpbb.com', 'A', true),

			// Non-existing A record
			array('does-not-exist.phpbb.com', 'A', false),

			// Existing AAAA record
			array('www.six.heise.de', 'AAAA', true),

			// Non-existing AAAA record
			array('does-not-exist.phpbb.com', 'AAAA', false),

			// Existing CNAME record
			array('news.cnet.com', 'CNAME', true),

			// Non-existing CNAME record
			array('does-not-exist.phpbb.com', 'CNAME', false),

			// Existing NS record
			array('phpbb.com', 'NS', true),

			// Non-existing NS record
			array('does-not-exist', 'NS', false),

			// Existing TXT record
			array('phpbb.com', 'TXT', true),

			// Non-existing TXT record
			array('does-not-exist', 'TXT', false),
		);
	}

	/**
	* @dataProvider data_provider
	*/
	public function test_checkdnsrr($host, $type, $expected)
	{
		$this->assertEquals($expected, phpbb_checkdnsrr($host, $type));
	}
}
