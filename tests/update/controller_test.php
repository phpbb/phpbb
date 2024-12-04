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

use phpbb\update\controller;
use phpbb\update\get_updates;
use phpbb\filesystem\filesystem;
use phpbb\language\language;

class phpbb_update_controller_test extends \phpbb_test_case
{
	private $filesystem;
	private $filesystem_mock;
	private $updater_mock;
	private $language_mock;
	private $phpbb_root_path;

	protected function setUp(): void
	{
		global $phpbb_root_path;

		$this->filesystem = new filesystem();
		$this->filesystem_mock = $this->createMock(filesystem::class);
		$this->updater_mock = $this->createMock(get_updates::class);
		$this->language_mock = $this->createMock(language::class);
		$this->phpbb_root_path = $phpbb_root_path;
	}

	protected function tearDown(): void
	{
		$this->filesystem->remove([
			$this->phpbb_root_path . 'store/update.zip',
			$this->phpbb_root_path . 'store/update.zip.sig',
			$this->phpbb_root_path . 'store/update',
		]);
	}

	public function test_download_fails(): void
	{
		$this->updater_mock->expects($this->once())
			->method('download')
			->willReturn(false);

		$this->language_mock->expects($this->once())
			->method('lang')
			->with('UPDATE_PACKAGE_DOWNLOAD_FAILURE')
			->willReturnArgument(0);

		$controller = new controller(
			$this->filesystem_mock,
			$this->updater_mock,
			$this->language_mock,
			$this->phpbb_root_path
		);

		$response = $controller->handle('https://example.com/update.zip');
		$this->assertEquals(['status' => 'error', 'error' => 'UPDATE_PACKAGE_DOWNLOAD_FAILURE'], $response);
	}

	public function test_download_success(): void
	{
		$this->updater_mock->expects($this->once())
			->method('download')
			->willReturn(true);

		$controller = new controller(
			$this->filesystem_mock,
			$this->updater_mock,
			$this->language_mock,
			$this->phpbb_root_path
		);

		$response = $controller->handle('https://example.com/update.zip');
		$this->assertEquals(['status' => 'continue'], $response);
	}

	public function test_download_signature_fails(): void
	{
		$update_path = $this->phpbb_root_path . 'store/update.zip';

		$this->filesystem_mock->expects($this->any())
			->method('exists')
			->willReturnMap([
				[$update_path, true],
				[$update_path . '.sig', false],
				[$this->phpbb_root_path . 'store/update', false],
				[$this->phpbb_root_path . 'install', false],
			]);

		$this->updater_mock->expects($this->once())
			->method('download')
			->with('https://example.com/update.zip.sig', $update_path . '.sig')
			->willReturn(false);

		$this->language_mock->expects($this->once())
			->method('lang')
			->with('UPDATE_SIGNATURE_DOWNLOAD_FAILURE')
			->willReturnArgument(0);

		$controller = new controller(
			$this->filesystem_mock,
			$this->updater_mock,
			$this->language_mock,
			$this->phpbb_root_path
		);

		$response = $controller->handle('https://example.com/update.zip');
		$this->assertEquals(['status' => 'error', 'error' => 'UPDATE_SIGNATURE_DOWNLOAD_FAILURE'], $response);
	}

	public function test_download_signature_success(): void
	{
		$update_path = $this->phpbb_root_path . 'store/update.zip';

		$this->filesystem_mock->expects($this->any())
			->method('exists')
			->willReturnMap([
				[$update_path, true],
				[$update_path . '.sig', false],
				[$this->phpbb_root_path . 'store/update', false],
				[$this->phpbb_root_path . 'install', false],
			]);

		$this->updater_mock->expects($this->once())
			->method('download')
			->with('https://example.com/update.zip.sig', $update_path . '.sig')
			->willReturn(true);

		$controller = new controller(
			$this->filesystem_mock,
			$this->updater_mock,
			$this->language_mock,
			$this->phpbb_root_path
		);

		$response = $controller->handle('https://example.com/update.zip');
		$this->assertEquals(['status' => 'continue'], $response);
	}

	public function test_signature_validation_fails(): void
	{
		$update_path = $this->phpbb_root_path . 'store/update.zip';

		$this->filesystem_mock->expects($this->any())
			->method('exists')
			->willReturnMap([
				[$update_path, true],
				[$update_path . '.sig', true],
				[$this->phpbb_root_path . 'store/update', false],
				[$this->phpbb_root_path . 'install', false],
			]);

		$this->updater_mock->expects($this->once())
			->method('validate')
			->willReturn(false);

		$this->language_mock->expects($this->once())
			->method('lang')
			->with('UPDATE_SIGNATURE_INVALID')
			->willReturnArgument(0);

		$controller = new controller(
			$this->filesystem_mock,
			$this->updater_mock,
			$this->language_mock,
			$this->phpbb_root_path
		);

		$response = $controller->handle('https://example.com/update.zip');
		$this->assertEquals(['status' => 'error', 'error' => 'UPDATE_SIGNATURE_INVALID'], $response);
	}

	public function test_extract_fails(): void
	{
		$update_path = $this->phpbb_root_path . 'store/update.zip';

		$this->filesystem_mock->expects($this->any())
			->method('exists')
			->willReturnMap([
				[$update_path, true],
				[$update_path . '.sig', true],
				[$this->phpbb_root_path . 'store/update', false],
				[$this->phpbb_root_path . 'install', false],
			]);

		$this->updater_mock->expects($this->once())
			->method('validate')
			->willReturn(true);

		$this->updater_mock->expects($this->once())
			->method('extract')
			->willReturn(false);

		$this->language_mock->expects($this->once())
			->method('lang')
			->with('UPDATE_PACKAGE_EXTRACT_FAILURE')
			->willReturnArgument(0);

		$controller = new controller(
			$this->filesystem_mock,
			$this->updater_mock,
			$this->language_mock,
			$this->phpbb_root_path
		);

		$response = $controller->handle('https://example.com/update.zip');
		$this->assertEquals(['status' => 'error', 'error' => 'UPDATE_PACKAGE_EXTRACT_FAILURE'], $response);
	}

	public function test_extract_success(): void
	{
		$update_path = $this->phpbb_root_path . 'store/update.zip';

		$this->filesystem_mock->expects($this->any())
			->method('exists')
			->willReturnMap([
				[$update_path, true],
				[$update_path . '.sig', true],
				[$this->phpbb_root_path . 'store/update', false],
				[$this->phpbb_root_path . 'install', false],
			]);

		$this->updater_mock->expects($this->once())
			->method('validate')
			->willReturn(true);

		$this->updater_mock->expects($this->once())
			->method('extract')
			->willReturn(true);

		$controller = new controller(
			$this->filesystem_mock,
			$this->updater_mock,
			$this->language_mock,
			$this->phpbb_root_path
		);

		$response = $controller->handle('https://example.com/update.zip');
		$this->assertEquals(['status' => 'continue'], $response);
	}

	public function test_copy_fails(): void
	{
		$update_path = $this->phpbb_root_path . 'store/update.zip';
		$this->filesystem->touch($update_path); // Simulate existing update file
		$this->filesystem->touch($update_path . '.sig'); // Simulate existing signature file
		$this->filesystem->mkdir($this->phpbb_root_path . 'store/update');

		$this->filesystem_mock->expects($this->any())
			->method('exists')
			->willReturnMap([
				[$update_path, true],
				[$update_path . '.sig', true],
				[$this->phpbb_root_path . 'store/update', true],
				[$this->phpbb_root_path . 'install', false],
			]);

		$this->updater_mock->expects($this->once())
			->method('copy')
			->willReturn(false);

		$this->language_mock->expects($this->once())
			->method('lang')
			->with('UPDATE_FILES_COPY_FAILURE')
			->willReturnArgument(0);

		$controller = new controller(
			$this->filesystem_mock,
			$this->updater_mock,
			$this->language_mock,
			$this->phpbb_root_path
		);

		$response = $controller->handle('https://example.com/update.zip');
		$this->assertEquals(['status' => 'error', 'error' => 'UPDATE_FILES_COPY_FAILURE'], $response);
	}

	public function test_copy_success(): void
	{
		$update_path = $this->phpbb_root_path . 'store/update.zip';
		$this->filesystem->touch($update_path); // Simulate existing update file
		$this->filesystem->touch($update_path . '.sig'); // Simulate existing signature file
		$this->filesystem->mkdir($this->phpbb_root_path . 'store/update');

		$this->filesystem_mock->expects($this->any())
			->method('exists')
			->willReturnMap([
				[$update_path, true],
				[$update_path . '.sig', true],
				[$this->phpbb_root_path . 'store/update', true],
				[$this->phpbb_root_path . 'install', false],
			]);

		$this->updater_mock->expects($this->once())
			->method('copy')
			->willReturn(true);

		$controller = new controller(
			$this->filesystem_mock,
			$this->updater_mock,
			$this->language_mock,
			$this->phpbb_root_path
		);

		$response = $controller->handle('https://example.com/update.zip');
		$this->assertEquals(['status' => 'continue'], $response);
	}

	public function test_successful_update_process(): void
	{
		$update_path = $this->phpbb_root_path . 'store/update.zip';
		$signature_path = $update_path . '.sig';
		$update_dir = $this->phpbb_root_path . 'store/update';

		$this->filesystem->touch($update_path);
		$this->filesystem->touch($signature_path);
		$this->filesystem->mkdir($update_dir);

		$this->filesystem_mock->expects($this->any())
			->method('exists')
			->willReturnMap([
				[$update_path, true],
				[$update_path . '.sig', true],
				[$this->phpbb_root_path . 'store/update', true],
				[$this->phpbb_root_path . 'install', true],
			]);

		$this->filesystem_mock->expects($this->once())
			->method('remove')
			->with([$update_dir, $update_path, $signature_path]);

		$controller = new controller(
			$this->filesystem_mock,
			$this->updater_mock,
			$this->language_mock,
			$this->phpbb_root_path
		);

		$response = $controller->handle('https://example.com/update.zip');
		$this->assertEquals(['status' => 'done'], $response);
	}
}