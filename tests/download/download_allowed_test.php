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

use phpbb\db\driver\driver_interface;
use phpbb\request\request_interface;
use phpbb\user;

require_once __DIR__ . '/../../phpBB/includes/functions_download.php';

class phpbb_download_download_allowed_test extends phpbb_test_case
{
	/** @var driver_interface */
	private $db;

	/** @var request_interface */
	private $request;

	/** @var user */
	private $user;

	protected function setUp(): void
	{
		$this->request = new phpbb_mock_request();

		$this->user = new phpbb_mock_user();

		$this->db = $this->getMockBuilder('phpbb\db\driver\driver')
			->disableOriginalConstructor()
			->getMock();
		$this->db->method('sql_query')->willReturn(null);
		$this->db->method('sql_freeresult')->willReturn(null);
	}

	public static function download_allowed_data(): array
	{
		$base_config = [
			'secure_downloads' => 1,
			'secure_allow_empty_referer' => 0,
			'secure_allow_deny' => 1,
			'force_server_vars' => 0,
			'server_name' => 'example.com',
		];

		return [
			'secure_disabled' => [
				array_merge($base_config, ['secure_downloads' => 0]),
				'https://other_domain.com/',
				'example.com',
				[],
				true,
				'secure_downloads disabled must always return true',
			],
			'empty_referer_allow_empty_on' => [
				array_merge($base_config, ['secure_allow_empty_referer' => 1]),
				'',
				'example.com',
				[],
				true,
				'empty referer with secure_allow_empty_referer=1 must return true',
			],
			'empty_referer_allow_empty_off' => [
				array_merge($base_config, ['secure_allow_empty_referer' => 0]),
				'',
				'example.com',
				[],
				false,
				'empty referer with secure_allow_empty_referer=0 must return false',
			],
			'invalid_referer_allow_empty_on' => [
				array_merge($base_config, ['secure_allow_empty_referer' => 1]),
				'not_a_proper_referer',
				'example.com',
				[],
				true,
				'empty referer with secure_allow_empty_referer=1 must return true',
			],
			'invalid_referer_allow_empty_off' => [
				array_merge($base_config, ['secure_allow_empty_referer' => 0]),
				'not_a_proper_referer',
				'example.com',
				[],
				false,
				'empty referer with secure_allow_empty_referer=0 must return false',
			],
			'same_hostname' => [
				$base_config,
				'https://example.com/path',
				'example.com',
				[],
				true,
				'referer hostname = server_name must be allowed',
			],
			'subdomain_of_hostname' => [
				$base_config,
				'https://sub.example.com/path',
				'example.com',
				[],
				true,
				'referer direct subdomain of server_name must be allowed',
			],
			'deep_subdomain_of_hostname' => [
				$base_config,
				'https://a.b.example.com/',
				'example.com',
				[],
				true,
				'deep subdomain of server_name must be allowed',
			],
			'partial_hostname_match' => [
				$base_config,
				'https://otherhostexample.com/path',
				'example.com',
				[],
				false,
				'otherhostexample.com must NOT be allowed when server_name=example.com (only partial match)',
			],
			'partial_subdomain_match' => [
				$base_config,
				'https://example.com.otherhostexample.com/path',
				'example.com',
				[],
				false,
				'example.com.otherhostexample.com must NOT be allowed when server_name=example.com (only partial match)',
			],
			'forced_server_vars_hostname' => [
				array_merge($base_config, [
					'force_server_vars' => 1,
					'server_name' => 'forced.example.com',
				]),
				'https://forced.example.com/page',
				'ignored-host.com',
				[],
				true,
				'force_server_vars must use config server_name and allow exact match',
			],
			'forced_server_vars_hostname_subdomain' => [
				array_merge($base_config, [
					'force_server_vars' => 1,
					'server_name' => 'forced.example.com',
				]),
				'https://sub.forced.example.com/page',
				'ignored-host.com',
				[],
				true,
				'force_server_vars: subdomain of forced server_name must be allowed',
			],
			'forced_server_vars_hostname_partial_subdomain' => [
				array_merge($base_config, [
					'force_server_vars' => 1,
					'server_name' => 'forced.example.com',
				]),
				'https://otherforced.example.com/page',
				'ignored-host.com',
				[],
				false,
				'force_server_vars: hostname that is not an exact/subdomain match must be denied',
			],
			'case_insensitive' => [
				$base_config,
				'https://EXAMPLE.COM/path',
				'example.com',
				[],
				true,
				'server_name comparison must be case-insensitive',
			],
			'sitelist_allowlist_default_denied' => [
				$base_config,
				'https://unknown-site.com/',
				'example.com',
				[],
				false,
				'allowlist mode with no matching sitelist entries must return false (default deny)',
			],
			'sitelist_denylist_default_allowed' => [
				array_merge($base_config, ['secure_allow_deny' => 0]),
				'https://unknown-site.com/',
				'example.com',
				[],
				true,
				'denylist mode with no matching sitelist entries must return true (default allow)',
			],
			'sitelist_hostname_in_allowlist' => [
				$base_config,
				'https://trusted-site.com/',
				'example.com',
				[['site_ip' => '', 'site_hostname' => 'trusted-site.com', 'ip_exclude' => 0]],
				true,
				'allowlist: hostname match with ip_exclude=0 must return true',
			],
			'sitelist_hostname_in_denylist' => [
				array_merge($base_config, ['secure_allow_deny' => 0]),
				'https://untrusted-site.com/',
				'example.com',
				[['site_ip' => '', 'site_hostname' => 'untrusted-site.com', 'ip_exclude' => 0]],
				false,
				'denylist: hostname match with ip_exclude=0 must return false',
			],
			'sitelist_hostname_not_in_allowlist' => [
				$base_config,
				'https://unknown-site.com/',
				'example.com',
				[['site_ip' => '', 'site_hostname' => 'trusted-site.com', 'ip_exclude' => 0]],
				false,
				'allowlist: non-matching hostname must return false',
			],
			'sitelist_hostname_not_in_denylist' => [
				array_merge($base_config, ['secure_allow_deny' => 0]),
				'https://unknown-site.com/',
				'example.com',
				[['site_ip' => '', 'site_hostname' => 'trusted-site.com', 'ip_exclude' => 0]],
				true,
				'denylist: non-matching hostname must return true',
			],
			'sitelist_hostname_excluded_from_allowlist' => [
				$base_config,
				'https://excluded-site.com/',
				'example.com',
				[['site_ip' => '', 'site_hostname' => 'excluded-site.com', 'ip_exclude' => 1]],
				false,
				'allowlist: hostname match with ip_exclude=1 must return false and stop processing',
			],
			'sitelist_hostname_excluded_from_denylist' => [
				array_merge($base_config, ['secure_allow_deny' => 0]),
				'https://excluded-site.com/',
				'example.com',
				[['site_ip' => '', 'site_hostname' => 'excluded-site.com', 'ip_exclude' => 1]],
				true,
				'denylist: hostname match with ip_exclude=1 must return true and stop processing',
			],
			'sitelist_hostname_wildcard_in_allowlist' => [
				$base_config,
				'https://sub.trusted-domain.com/',
				'example.com',
				[['site_ip' => '', 'site_hostname' => '*.trusted-domain.com', 'ip_exclude' => 0]],
				true,
				'allowlist: wildcard hostname pattern must match subdomains',
			],
			'sitelist_hostname_wildcard_in_denylist' => [
				array_merge($base_config, ['secure_allow_deny' => 0]),
				'https://sub.trusted-domain.com/',
				'example.com',
				[['site_ip' => '', 'site_hostname' => '*.trusted-domain.com', 'ip_exclude' => 0]],
				false,
				'denylist: wildcard hostname pattern must match subdomains and deny',
			],
			'sitelist_hostname_wildcard_no_match' => [
				$base_config,
				'https://other-domain.com/',
				'example.com',
				[['site_ip' => '', 'site_hostname' => '*.trusted-domain.com', 'ip_exclude' => 0]],
				false,
				'allowlist: wildcard hostname pattern must not match an unrelated domain',
			],
			'sitelist_hostname_wildcard_deny_no_match' => [
				array_merge($base_config, ['secure_allow_deny' => 0]),
				'https://other-domain.com/',
				'example.com',
				[['site_ip' => '', 'site_hostname' => '*.trusted-domain.com', 'ip_exclude' => 0]],
				true,
				'denylist: wildcard hostname pattern must not match an unrelated domain',
			],
			'sitelist_hostname_allowed_then_excluded' => [
				$base_config,
				'https://overridden-site.com/',
				'example.com',
				[
					['site_ip' => '', 'site_hostname' => 'overridden-site.com', 'ip_exclude' => 0],
					['site_ip' => '', 'site_hostname' => 'overridden-site.com', 'ip_exclude' => 1],
				],
				false,
				'allowlist: a hostname allowed by one row must be denied when a subsequent row excludes it',
			],
			'sitelist_hostname_denied_then_excluded' => [
				array_merge($base_config, ['secure_allow_deny' => 0]),
				'https://overridden-site.com/',
				'example.com',
				[
					['site_ip' => '', 'site_hostname' => 'overridden-site.com', 'ip_exclude' => 0],
					['site_ip' => '', 'site_hostname' => 'overridden-site.com', 'ip_exclude' => 1],
				],
				true,
				'denylist: a hostname denied by one row must be allowed when a subsequent row excludes it',
			],
			'sitelist_ip_in_allowlist' => [
				$base_config,
				'https://localhost/',
				'example.com',
				[['site_ip' => '127.0.0.1', 'site_hostname' => '', 'ip_exclude' => 0]],
				true,
				'allowlist: exact IP match with ip_exclude=0 must return true',
			],
			'sitelist_ip_in_denylist' => [
				array_merge($base_config, ['secure_allow_deny' => 0]),
				'https://localhost/',
				'example.com',
				[['site_ip' => '127.0.0.1', 'site_hostname' => '', 'ip_exclude' => 0]],
				false,
				'denylist: exact IP match with ip_exclude=0 must return false',
			],
			'sitelist_ip_not_in_allowlist' => [
				$base_config,
				'https://localhost/',
				'example.com',
				[['site_ip' => '10.0.0.1', 'site_hostname' => '', 'ip_exclude' => 0]],
				false,
				'allowlist: non-matching IP must return false',
			],
			'sitelist_ip_not_in_denylist' => [
				array_merge($base_config, ['secure_allow_deny' => 0]),
				'https://localhost/',
				'example.com',
				[['site_ip' => '10.0.0.1', 'site_hostname' => '', 'ip_exclude' => 0]],
				true,
				'denylist: non-matching IP must return true',
			],
			'sitelist_ip_excluded_from_allowlist' => [
				$base_config,
				'https://localhost/',
				'example.com',
				[['site_ip' => '127.0.0.1', 'site_hostname' => '', 'ip_exclude' => 1]],
				false,
				'allowlist: IP match with ip_exclude=1 must return false (break 2)',
			],
			'sitelist_ip_excluded_from_denylist' => [
				array_merge($base_config, ['secure_allow_deny' => 0]),
				'https://localhost/',
				'example.com',
				[['site_ip' => '127.0.0.1', 'site_hostname' => '', 'ip_exclude' => 1]],
				true,
				'denylist: IP match with ip_exclude=1 must return true (break 2)',
			],
			'sitelist_ip_wildcard_in_allowlist' => [
				$base_config,
				'https://localhost/',
				'example.com',
				[['site_ip' => '127.0.*', 'site_hostname' => '', 'ip_exclude' => 0]],
				true,
				'allowlist: wildcard IP pattern must match localhost address',
			],
			'sitelist_ip_wildcard_in_denylist' => [
				array_merge($base_config, ['secure_allow_deny' => 0]),
				'https://localhost/',
				'example.com',
				[['site_ip' => '127.0.*', 'site_hostname' => '', 'ip_exclude' => 0]],
				false,
				'denylist: wildcard IP pattern must match localhost address',
			],
			'sitelist_ip_allowed_then_excluded' => [
				$base_config,
				'https://localhost/',
				'example.com',
				[
					['site_ip' => '127.0.0.1', 'site_hostname' => '', 'ip_exclude' => 0],
					['site_ip' => '127.0.0.1', 'site_hostname' => '', 'ip_exclude' => 1],
				],
				false,
				'allowlist: an IP allowed by one row must be denied when a subsequent row excludes it (break 2)',
			],
			'sitelist_ip_denied_then_excluded' => [
				$base_config,
				'https://localhost/',
				'example.com',
				[
					['site_ip' => '127.0.0.1', 'site_hostname' => '', 'ip_exclude' => 0],
					['site_ip' => '127.0.0.1', 'site_hostname' => '', 'ip_exclude' => 1],
				],
				false,
				'denylist: an IP denied by one row must be allowed when a subsequent row excludes it (break 2)',
			],
		];
	}

	/**
	 * @dataProvider download_allowed_data
	 */
	public function test_download_allowed(array $config_vars, $referer, $host, array $db_rows, $expected, $message)
	{
		global $config, $db, $request, $user;

		// Build the sequence of sql_fetchrow return values: each row followed
		// by false to terminate the while loop.
		$fetchrow_sequence = array_merge($db_rows, [false]);

		if (count($fetchrow_sequence) === 1)
		{
			// Only the terminating false – no rows at all.
			$this->db->method('sql_fetchrow')->willReturn(false);
		}
		else
		{
			$this->db->method('sql_fetchrow')
				->will($this->onConsecutiveCalls(...$fetchrow_sequence));
		}

		$this->request->set_header('Referer', $referer);
		$this->user->host = $host;

		// Set globals
		$config = $config_vars;
		$db = $this->db;
		$request = $this->request;
		$user = $this->user;

		$result = download_allowed();
		$this->assertEquals($expected, $result, $message);
	}
}
