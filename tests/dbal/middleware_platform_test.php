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

use Doctrine\DBAL\Driver;

class phpbb_dbal_middleware_platform_test extends phpbb_test_case
{
	public static function platform_provider(): array
	{
		return [
			['\\phpbb\\db\\middleware\\mysql\\driver', '\\phpbb\\db\\middleware\\mysql\\platform'],
			['\\phpbb\\db\\middleware\\oracle\\driver', '\\phpbb\\db\\middleware\\oracle\\platform'],
			['\\phpbb\\db\\middleware\\postgresql\\driver', '\\phpbb\\db\\middleware\\postgresql\\platform'],
			['\\phpbb\\db\\middleware\\sqlsrv\\driver', '\\phpbb\\db\\middleware\\sqlsrv\\platform'],
		];
	}

	/**
	 * @dataProvider platform_provider
	 */
	public function test_driver_uses_phpbb_platform(string $driver_class, string $platform_class)
	{
		$wrapped_driver = $this->createMock(Driver::class);
		$driver = new $driver_class($wrapped_driver);

		$this->assertInstanceOf($platform_class, $driver->createDatabasePlatformForVersion('0'));
	}

	public function test_oracle_autoincrement_sql_uses_phpbb_constraint_name()
	{
		$platform = new \phpbb\db\middleware\oracle\platform();
		$sql = implode("\n", $platform->getCreateAutoincrementSql('id', 'test_table'));

		$this->assertStringContainsString('CONSTRAINT T_test_table PRIMARY KEY (ID)', $sql);
	}

	public function test_postgresql_drops_primary_key_as_constraint()
	{
		$platform = new \phpbb\db\middleware\postgresql\platform();

		$this->assertSame(
			'ALTER TABLE test_table DROP CONSTRAINT test_table_pkey',
			$platform->getDropIndexSQL('test_table_pkey', 'test_table')
		);
	}
}
