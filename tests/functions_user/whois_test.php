<?php
/**
*
* @package testing
* @copyright (c) 2020 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_user.php';

class phpbb_functions_user_whois_test extends phpbb_functional_test_case
{
	public function ips_data()
	{
		return [
			['2001:4860:4860::8888'], // Google public DNS
			['64.233.161.139'], // google.com
		];
	}

	/**
	* @dataProvider ips_data
	*/
	public function test_ip_whois($ip)
	{
		$ip_whois = user_ipwhois($ip);
		$this->assertNotContains('Query terms are ambiguous', $ip_whois);
		$this->assertNotContains('no entries found', $ip_whois);
		$this->assertNotContains('ERROR', $ip_whois);
	}
}
