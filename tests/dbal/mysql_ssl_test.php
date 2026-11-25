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

use phpbb\db\doctrine\connection_parameter_factory;
use phpbb\db\mysql_ssl;

class phpbb_dbal_mysql_ssl_test extends phpbb_test_case
{
	public static function mode_data()
	{
		return [
			[null, mysql_ssl::DISABLED],
			['', mysql_ssl::DISABLED],
			['disabled', mysql_ssl::DISABLED],
			['preferred', mysql_ssl::PREFERRED],
			['REQUIRED', mysql_ssl::REQUIRED],
			[' verify_identity ', mysql_ssl::VERIFY_IDENTITY],
		];
	}

	/**
	* @dataProvider mode_data
	*/
	public function test_get_mode($setting, $expected)
	{
		$this->assertSame($expected, mysql_ssl::get_mode($setting));
	}

	public function test_get_mode_rejects_unknown_modes()
	{
		// MySQL's verify_ca is not offered: mysqlnd cannot check the CA without also checking the host name
		$this->expectException(InvalidArgumentException::class);
		mysql_ssl::get_mode('verify_ca');
	}

	public function test_get_ca_locations_uses_the_configured_file()
	{
		$ca = __DIR__ . '/fixtures/mysql_ssl_untrusted_ca.pem';
		$this->assertSame([$ca, null], mysql_ssl::get_ca_locations($ca));
	}

	public function test_get_ca_locations_rejects_an_unreadable_file()
	{
		$this->expectException(InvalidArgumentException::class);
		mysql_ssl::get_ca_locations(__DIR__ . '/fixtures/does_not_exist.pem');
	}

	public function test_get_ca_locations_falls_back_to_the_system_cas()
	{
		if (!function_exists('openssl_get_cert_locations'))
		{
			$this->markTestSkipped('The openssl extension is not loaded.');
		}

		[$file, $path] = mysql_ssl::get_ca_locations('');
		$this->assertTrue($file !== null || $path !== null);
	}

	public static function unavailable_data()
	{
		return [
			[2006, 'MySQL server has gone away', true],
			[2002, 'Cannot connect to MySQL using SSL', true],
			[2002, 'SQLSTATE[HY000] [2002] Cannot connect to MySQL using SSL', true],
			[2002, 'php_network_getaddresses: getaddrinfo for nowhere failed', false],
			[1045, 'Access denied for user', false],
		];
	}

	/**
	* @dataProvider unavailable_data
	*/
	public function test_is_unavailable($code, $message, $expected)
	{
		$this->assertSame($expected, mysql_ssl::is_unavailable($code, $message));
	}

	public static function doctrine_options_data()
	{
		return [
			['disabled', null],
			['preferred', false],
			['required', false],
			['verify_identity', true],
		];
	}

	/**
	* @dataProvider doctrine_options_data
	*/
	public function test_doctrine_options($mode, $verify)
	{
		if (!extension_loaded('pdo_mysql'))
		{
			$this->markTestSkipped('The pdo_mysql extension is not loaded.');
		}

		$ca = __DIR__ . '/fixtures/mysql_ssl_untrusted_ca.pem';
		$params = connection_parameter_factory::get_configuration('pdo_mysql', 'localhost', 'user', 'pass', 'phpbb', null, $mode, $ca);

		if ($verify === null)
		{
			$this->assertArrayNotHasKey('driverOptions', $params);
			return;
		}

		$this->assertSame($ca, $params['driverOptions'][mysql_ssl::get_pdo_attribute('SSL_CA')]);
		$this->assertSame($verify, $params['driverOptions'][mysql_ssl::get_pdo_attribute('SSL_VERIFY_SERVER_CERT')]);
	}

	public function test_doctrine_options_only_apply_to_mysql()
	{
		$params = connection_parameter_factory::get_configuration('pdo_pgsql', 'localhost', 'user', 'pass', 'phpbb', null, 'verify_identity');
		$this->assertArrayNotHasKey('driverOptions', $params);
	}
}
