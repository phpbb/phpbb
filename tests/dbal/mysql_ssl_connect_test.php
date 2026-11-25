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

use phpbb\db\doctrine\connection_factory;

/**
* Connects to the test server with each ssl mode, through both phpBB's mysqli driver and the Doctrine connection.
* Works whether or not the server offers TLS: some MariaDB versions in CI don't.
*/
class phpbb_dbal_mysql_ssl_connect_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/../fixtures/empty.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		if ($this->get_database_config()['dbms'] !== 'phpbb\db\driver\mysqli')
		{
			$this->markTestSkipped('The ssl modes only apply to MySQL.');
		}
	}

	public static function connector_data()
	{
		return [
			['connect_driver'],
			['connect_doctrine'],
		];
	}

	/**
	* @dataProvider connector_data
	*/
	public function test_disabled_is_unencrypted($connector)
	{
		$this->assertSame('', $this->$connector('disabled'));
	}

	/**
	* @dataProvider connector_data
	*/
	public function test_preferred_uses_tls_when_offered($connector)
	{
		$cipher = $this->$connector('preferred');

		if ($this->server_offers_tls())
		{
			$this->assertNotEmpty($cipher);
		}
		else
		{
			$this->assertSame('', $cipher);
		}
	}

	/**
	* @dataProvider connector_data
	*/
	public function test_required_refuses_unencrypted($connector)
	{
		$cipher = $this->$connector('required');

		if ($this->server_offers_tls())
		{
			$this->assertNotEmpty($cipher);
		}
		else
		{
			$this->assertNull($cipher);
		}
	}

	/**
	* @dataProvider connector_data
	*/
	public function test_verify_identity_refuses_an_untrusted_certificate($connector)
	{
		$this->assertNull($this->$connector('verify_identity', __DIR__ . '/fixtures/mysql_ssl_untrusted_ca.pem'));
	}

	public function test_driver_refuses_an_unknown_mode()
	{
		$this->assertNull($this->connect_driver('verify_ca'));
	}

	public function test_doctrine_refuses_an_unknown_mode()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->connect_doctrine('verify_ca');
	}

	/**
	* @return string|null The session's TLS cipher, '' when unencrypted, or null when the connection was refused
	*/
	protected function connect_driver(string $mode, string|null $ca = null): string|null
	{
		$config = $this->get_database_config();
		$db = new \phpbb\db\driver\mysqli();
		$db->set_ssl_options($mode, $ca);
		$db->sql_return_on_error(true);

		try
		{
			$result = $db->sql_connect($config['dbhost'], $config['dbuser'], $config['dbpasswd'], $config['dbname'], $config['dbport'], false, true);
		}
		catch (mysqli_sql_exception $e)
		{
			return null;
		}

		if (is_array($result))
		{
			return null;
		}

		$result = $db->sql_query("SHOW SESSION STATUS LIKE 'Ssl_cipher'");
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		$db->sql_close();

		return (string) $row['Value'];
	}

	/**
	* @return string|null The session's TLS cipher, '' when unencrypted, or null when the connection was refused
	*/
	protected function connect_doctrine(string $mode, string|null $ca = null): string|null
	{
		$config = $this->get_database_config();
		$connection = connection_factory::get_connection_from_params(
			$config['dbms'],
			$config['dbhost'],
			$config['dbuser'],
			$config['dbpasswd'],
			$config['dbname'],
			(string) $config['dbport'],
			$mode,
			$ca
		);

		try
		{
			$cipher = $connection->executeQuery("SHOW SESSION STATUS LIKE 'Ssl_cipher'")->fetchNumeric()[1];
		}
		catch (\Doctrine\DBAL\Exception $e)
		{
			return null;
		}
		finally
		{
			$connection->close();
		}

		return (string) $cipher;
	}

	protected function server_offers_tls(): bool
	{
		$config = $this->get_database_config();
		$link = mysqli_init();

		try
		{
			return @mysqli_real_connect($link, $config['dbhost'], $config['dbuser'], $config['dbpasswd'], $config['dbname'], $config['dbport'] ? (int) $config['dbport'] : null, null, MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);
		}
		catch (mysqli_sql_exception $e)
		{
			return false;
		}
	}
}
