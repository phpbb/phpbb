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

namespace phpbb\tests\composer;

use phpbb\cache\driver\driver_interface;
use phpbb\composer\installer;
use phpbb\composer\manager;

class manager_test extends \phpbb_test_case
{
	public static function get_available_updates_data(): array
	{
		return [
			'newer stable version' => ['1.0.0.0', ['version' => '1.1.0'], true],
			'current stable version' => ['1.0.0.0', ['version' => '1.0.0'], false],
			'older catalog version' => ['1.1.0.0', ['version' => '1.0.0'], false],
			'normalized catalog version' => ['1.0.0.0', ['version' => 'invalid', 'normalized_version' => '1.1.0.0'], true],
			'invalid catalog version' => ['1.0.0.0', ['version' => 'invalid'], false],
		];
	}

	/**
	 * @dataProvider get_available_updates_data
	 */
	public function test_get_available_updates(string $installed_version, array $catalog_version, bool $expected): void
	{
		$package = array_merge([
			'composer_name' => 'vendor/extension',
		], $catalog_version);

		$installer = $this->getMockBuilder(installer::class)
			->disableOriginalConstructor()
			->onlyMethods(['get_installed_package_versions'])
			->getMock();
		$installer->expects($this->once())
			->method('get_installed_package_versions')
			->with('phpbb-extension')
			->willReturn(['vendor/extension' => $installed_version]);

		$cache = $this->createMock(driver_interface::class);
		$cache->expects($this->once())
			->method('get')
			->with('_composer_phpbb-extension_available')
			->willReturn([$package]);

		$manager = new manager($installer, $cache, 'phpbb-extension', 'EXTENSION');
		$updates = $manager->get_available_updates();

		$this->assertSame($expected, isset($updates['vendor/extension']));
	}

	public function test_get_available_updates_does_not_fetch_missing_catalog(): void
	{
		$installer = $this->getMockBuilder(installer::class)
			->disableOriginalConstructor()
			->onlyMethods(['get_installed_package_versions'])
			->getMock();
		$installer->expects($this->never())
			->method('get_installed_package_versions');

		$cache = $this->createMock(driver_interface::class);
		$cache->expects($this->once())
			->method('get')
			->with('_composer_phpbb-extension_available')
			->willReturn(false);

		$manager = new manager($installer, $cache, 'phpbb-extension', 'EXTENSION');

		$this->assertSame([], $manager->get_available_updates());
	}
}
