<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
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
		foreach (dns_get_record('ftp.debian.org', DNS_A | DNS_AAAA) as $row)
		{
			if (isset($row['ip']))
			{
				$ipv4 = $row['ip'];
			}
			else if (isset($row['ipv6']))
			{
				$ipv6 = $row['ipv6'];
			}
		}

		$this->assert_ls_contains_debian($ipv4);
		$this->assert_ls_contains_debian("[$ipv6]");
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
