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

require_once __DIR__ . '/../test_framework/phpbb_session_test_case.php';

class phpbb_session_extract_hostname_test extends phpbb_session_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/sessions_empty.xml');
	}

	static public function extract_current_hostname_data(): array
	{
		return [
			// [Input] $host, $server_name_config, $cookie_domain_config, [Expected] $output
			// If host is ip use that
			//    ipv4
			'ipv4' => ['127.0.0.1', 'skipped.org', 'skipped.org', 'skipped.org'],
			//    ipv6
			'ipv6' => ['::1', 'skipped.org', 'skipped.org', 'skipped.org'],
			'ipv6_2' => ['2002::3235:51f9', 'skipped.org', 'skipped.org', 'skipped.org'],
			// If no host but server name matches cookie_domain use that
			'no_host_both_configs' => ['', 'example.org', 'example.org', 'example.org'],
			// If there is a host uri use that
			'host_no_configs' => ['example.org', false, false, 'example.org'],
			// 'best approach' guessing
			'no_host_only_server_name' => ['', 'example.org', false, 'example.org'],
			'no_host_only_cookie_domain' => ['', false, '127.0.0.1', '127.0.0.1'],
			'no_host_no_config' => ['', false, false, php_uname('n')],
			'host_is_server_config' => ['example.org', 'example.org', '.example.org', 'example.org'],
			'host_is_cookie_domain' => ['example.org', 'foobar.org', 'example.org', 'foobar.org'],
		];
	}

	/** @dataProvider extract_current_hostname_data */
	function test_extract_current_hostname($host, $server_name_config, $cookie_domain_config, $expected)
	{
		$output = $this->session_facade->extract_current_hostname(
			$host,
			$server_name_config,
			$cookie_domain_config
		);

		$this->assertEquals($expected, $output);
	}
}
