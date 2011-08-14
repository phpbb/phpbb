<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_transfer.php';

/**
* @group slow
*/
class phpbb_ftp_fsock_test extends phpbb_test_case
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
		// PHP can connect to IPv6 addresses which are IPv6-encapsulated
		// IPv4 addresses on systems that don't have IPv6 connectivity,
		// provided that PHP was built with IPv6 support.
		// If this test fails on such an IPv6-encapsulated IPv4 address,
		// check whether you disabled IPv6 support in your PHP.
		if ($result !== true)
		{
			$this->markTestSkipped("Failed to connect to $hostname: $result");
		}
		$this->assertContains('debian', $o->_ls());
		$o->_close();
	}

	protected function get_object($hostname)
	{
		return new ftp_fsock($hostname, 'anonymous', 'anonymous@localost.tld', '/');
	}
}
