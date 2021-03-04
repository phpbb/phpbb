<?php
/**
*
* @package testing
* @copyright (c) 2020 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once __DIR__ . '/../../phpBB/includes/functions_user.php';

class phpbb_functions_user_whois_test extends phpbb_test_case
{
	public function setUp(): void
	{
		global $config, $phpbb_dispatcher, $user, $request, $symfony_request, $phpbb_root_path, $phpEx;

		$user = $this->getMockBuilder('\phpbb\user')
			->setConstructorArgs([
				new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
				 '\phpbb\datetime',
			])
			->getMock();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$config = new \phpbb\config\config([]);
		$request = $this->getMockBuilder('\phpbb\request\request')
			->getMock();
		$symfony_request = $this->getMockBuilder('\phpbb\symfony_request')
			->disableOriginalConstructor()
			->getMock();
	}

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
		$this->assertStringNotContainsString('Query terms are ambiguous', $ip_whois);
		$this->assertStringNotContainsString('no entries found', $ip_whois);
		$this->assertStringNotContainsString('ERROR', $ip_whois);
	}
}
