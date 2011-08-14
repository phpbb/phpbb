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
	public function test_pasv_epsv()
	{
		$hostname = 'ftp.debian.org.';
		$ipv4 = gethostbyname($hostname);

		if ($ipv4 == $hostname)
		{
			$this->markTestSkipped("Got no A record back from DNS query for $hostname");
		}

		// PASV
		$this->assert_ls_contains_debian($ipv4);

		// EPSV
		$this->assert_ls_contains_debian("[::ffff:$ipv4]");
	}

	protected function assert_ls_contains_debian($hostname)
	{
		$o = $this->get_object($hostname);
		$o->_init();
		$this->assertContains('debian', $o->_ls());
		$o->_close();
	}

	protected function get_object($hostname)
	{
		return new ftp_fsock($hostname, 'anonymous', 'anonymous@localost.tld', '/');
	}
}
