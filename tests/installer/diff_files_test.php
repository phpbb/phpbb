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

use phpbb\install\helper\config;
use phpbb\install\helper\update_helper;
use phpbb\install\module\update_filesystem\task\diff_files;

class phpbb_installer_diff_files_test extends phpbb_test_case
{
	/** @var config */
	protected $installer_config;

	/** @var phpbb_mock_cache */
	protected $cache;

	/** @var diff_files */
	protected $task;

	protected function setUp(): void
	{
		global $phpbb_root_path;

		parent::setUp();

		include_once($phpbb_root_path . 'includes/diff/diff.php');
		include_once($phpbb_root_path . 'includes/diff/engine.php');

		$board_path = __DIR__ . '/fixtures/diff_files/';

		$filesystem = $this->createMock('\phpbb\filesystem\filesystem');
		$php_ini = $this->getMockBuilder('\bantu\IniGetWrapper\IniGetWrapper')
			->setMethods(['getInt', 'getBytes'])
			->getMock();
		$php_ini->method('getInt')
			->willReturn(-1);
		$php_ini->method('getBytes')
			->willReturn(-1);
		$this->installer_config = new config($filesystem, $php_ini, $board_path);

		$this->cache = new phpbb_mock_cache();
		$container = $this->createMock('\phpbb\install\helper\container_factory');
		$container->method('get')
			->with('cache.driver')
			->willReturn($this->cache);

		$iohandler = $this->createMock('\phpbb\install\helper\iohandler\iohandler_interface');

		$this->task = new diff_files($container, $this->installer_config, $iohandler, new update_helper($board_path), $board_path, 'php');
	}

	public function test_conflicting_file_is_reported_and_kept()
	{
		$this->installer_config->set('update_files', ['update_with_diff' => ['conflict.txt']]);

		$this->task->run();

		$this->assertSame(['conflict.txt'], $this->installer_config->get('merge_conflict_list'));
		$this->assertContains('conflict.txt', $this->installer_config->get('update_files')['update_with_diff']);
		$this->assertStringContainsString('<<<<<<<', base64_decode($this->cache->get('_file_' . md5('conflict.txt'))));
	}

	public function test_conflicting_file_with_other_changes_is_reported_and_kept()
	{
		$this->installer_config->set('update_files', ['update_with_diff' => ['mixed.txt']]);

		$this->task->run();

		$this->assertSame(['mixed.txt'], $this->installer_config->get('merge_conflict_list'));
		$this->assertContains('mixed.txt', $this->installer_config->get('update_files')['update_with_diff']);
	}

	public function test_conflict_kept_by_user_on_a_later_run_counts_as_merged()
	{
		$this->installer_config->set('update_files', ['update_with_diff' => ['conflict.txt']]);
		$this->installer_config->set('merge_conflict_list', ['conflict.txt']);

		$this->task->run();

		$this->assertSame(['conflict.txt'], $this->installer_config->get('merge_conflict_list'));
		$this->assertArrayNotHasKey('update_with_diff', $this->installer_config->get('update_files'));
	}

	public function test_mergeable_file_is_merged()
	{
		$this->installer_config->set('update_files', ['update_with_diff' => ['clean.txt']]);

		$this->task->run();

		$this->assertSame([], $this->installer_config->get('merge_conflict_list'));
		$this->assertContains('clean.txt', $this->installer_config->get('update_files')['update_with_diff']);
		$this->assertSame(
			"line 1\nline 2 changed by the board owner\nline 3\nline 4\nline 5 changed by phpBB\n",
			base64_decode($this->cache->get('_file_' . md5('clean.txt')))
		);
	}

	public function test_file_already_equal_to_new_version_is_skipped()
	{
		$this->installer_config->set('update_files', ['update_with_diff' => ['merged.txt']]);

		$this->task->run();

		$this->assertSame([], $this->installer_config->get('merge_conflict_list'));
		$this->assertArrayNotHasKey('update_with_diff', $this->installer_config->get('update_files'));
	}
}
