<?php
/**
 *
 * @package testing
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/testable_facade.php';

class phpbb_session_extract_hostname_test extends phpbb_database_test_case
{
	public $session_factory;
	public $db;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/sessions_empty.xml');
	}

	public function setUp()
	{
		$this->session_factory = new phpbb_session_testable_factory;
		$this->db = $this->new_dbal();
	}

	static public function extract_current_hostname_data()
	{
		return array (
			// [Input] $host, $server_name_config, $cookie_domain_config, [Expected] $output
			// If host is ip use that 	ipv4
			array('127.0.0.1', 'skipped.org', 'skipped.org', '127.0.0.1'),
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
		$output = phpbb_session_testable_facade::extract_current_hostname(
			$this->db,
			$this->session_factory,
			$host,
			$server_name_config,
			$cookie_domain_config
		);

		$this->assertEquals($expected, $output);
	}
}
