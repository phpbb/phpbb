<?php
/**
*
* @package testing
* @copyright (c) 2020 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_user.php';

class phpbb_functions_user_whois_test extends phpbb_test_case
{
	protected function setUp()
	{
		global $config, $phpbb_dispatcher, $user, $request, $symfony_request;

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$config = new \phpbb\config\config([]);
		$request = new phpbb_mock_request();
		$symfony_request = new \phpbb\symfony_request($request);
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
		$this->assertNotContains('Query terms are ambiguous', $ip_whois);
		$this->assertNotContains('no entries found', $ip_whois);
		$this->assertNotContains('ERROR', $ip_whois);
	}
}
