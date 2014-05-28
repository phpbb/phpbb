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

require_once dirname(__FILE__) . '/../test_framework/phpbb_session_test_case.php';

class phpbb_session_extract_hostname_test extends phpbb_session_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/sessions_empty.xml');
	}

	static public function extract_current_hostname_data()
	{
		return array (
			// [Input] $host, $server_name_config, $cookie_domain_config, [Expected] $output
			// If host is ip use that
			//    ipv4
			array('127.0.0.1', 'skipped.org', 'skipped.org', '127.0.0.1'),
			//    ipv6
			array('::1', 'skipped.org', 'skipped.org', ':'),
			array('2002::3235:51f9', 'skipped.org', 'skipped.org', '2002::3235'),
			// If no host but server name matches cookie_domain use that
			array('', 'example.org', 'example.org', 'example.org'),
			// If there is a host uri use that
			array('example.org', false, false, 'example.org'),
			// 'best approach' guessing
			array('', 'example.org', false, 'example.org'),
			array('', false, '127.0.0.1', '127.0.0.1'),
			array('', false, false, php_uname('n')),
		);
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
