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
use phpbb\composer\exception\runtime_exception;
use phpbb\composer\extension_manager;
use phpbb\composer\installer;
use phpbb\composer\io\null_io;
use phpbb\extension\manager as phpbb_extension_manager;
use phpbb\filesystem\filesystem;

class extension_manager_test extends \phpbb_test_case
{
	public function test_remove_without_purge_disables_extension_and_preserves_files(): void
	{
		$phpbb_extension_manager = $this->createMock(phpbb_extension_manager::class);
		$phpbb_extension_manager->expects($this->once())
			->method('is_enabled')
			->with('vendor/extension')
			->willReturn(true);
		$phpbb_extension_manager->expects($this->once())
			->method('disable')
			->with('vendor/extension');
		$phpbb_extension_manager->expects($this->once())
			->method('is_configured')
			->with('vendor/extension')
			->willReturn(true);
		$phpbb_extension_manager->expects($this->never())
			->method('purge');

		$manager = $this->get_manager($phpbb_extension_manager);
		$manager->set_purge_on_remove(false);

		try
		{
			$manager->pre_remove(['vendor/extension' => '*'], new null_io());
			$this->fail('Removing a configured extension without purging should fail.');
		}
		catch (runtime_exception $e)
		{
			$this->assertSame('EXTENSIONS_REMOVE_REQUIRES_PURGE', $e->getMessage());
			$this->assertSame(['vendor/extension'], $e->get_parameters());
		}
	}

	public function test_remove_without_purge_allows_unconfigured_extension(): void
	{
		$phpbb_extension_manager = $this->createMock(phpbb_extension_manager::class);
		$phpbb_extension_manager->expects($this->once())
			->method('is_enabled')
			->with('vendor/extension')
			->willReturn(false);
		$phpbb_extension_manager->expects($this->once())
			->method('is_configured')
			->with('vendor/extension')
			->willReturn(false);

		$manager = $this->get_manager($phpbb_extension_manager);
		$manager->set_purge_on_remove(false);
		$manager->pre_remove(['vendor/extension' => '*'], new null_io());

		$this->addToAssertionCount(1);
	}

	public function test_remove_with_purge_deletes_extension_data(): void
	{
		$phpbb_extension_manager = $this->createMock(phpbb_extension_manager::class);
		$phpbb_extension_manager->expects($this->once())
			->method('is_configured')
			->with('vendor/extension')
			->willReturn(true);
		$phpbb_extension_manager->expects($this->once())
			->method('purge')
			->with('vendor/extension');
		$phpbb_extension_manager->expects($this->never())
			->method('disable');

		$manager = $this->get_manager($phpbb_extension_manager);
		$manager->set_purge_on_remove(true);
		$manager->pre_remove(['vendor/extension' => '*'], new null_io());

		$this->addToAssertionCount(1);
	}

	private function get_manager(phpbb_extension_manager $phpbb_extension_manager): extension_manager
	{
		$installer = $this->getMockBuilder(installer::class)
			->disableOriginalConstructor()
			->getMock();

		return new extension_manager(
			$installer,
			$this->createMock(driver_interface::class),
			$phpbb_extension_manager,
			$this->createMock(filesystem::class),
			'phpbb-extension',
			'EXTENSIONS_',
			'./'
		);
	}
}
