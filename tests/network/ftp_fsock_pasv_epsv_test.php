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
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_transfer.php';

/**
* @group slow
*/
class phpbb_network_ftp_fsock_pasv_epsv_test extends phpbb_test_case
{
	static protected $ipv4;

	static public function setUpBeforeClass()
	{
		$hostname = 'ftp.debian.org.';
		self::$ipv4 = gethostbyname($hostname);

		if (self::$ipv4 == $hostname)
		{
			self::markTestSkipped("Got no A record back from DNS query for $hostname");
		}
	}

	public function test_pasv()
	{
		// PASV
		$this->assert_ls_contains_debian(self::$ipv4);
	}

	public function test_epsv()
	{
		$ipv4 = self::$ipv4;
		// EPSV
		$this->assert_ls_contains_debian("[::ffff:$ipv4]");
	}

	protected function assert_ls_contains_debian($hostname)
	{
		$o = $this->get_object($hostname);
		$result = $o->_init();
		// This test may fail on IPv6 addresses if IPv6 support is
		// not available. PHP must be compiled with IPv6 support enabled,
		// and your operating system must be configured for IPv6 as well.
		if ($result !== true)
		{
			$this->markTestSkipped("Failed to connect to $hostname: $result");
		}
		$this->assertContains('debian', $o->_ls());
		$o->_close();
	}

	protected function get_object($hostname)
	{
		return new ftp_fsock($hostname, 'anonymous', 'anonymous@localhost.tld', '/');
	}
}
