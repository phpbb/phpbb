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

class diff_files_test extends phpbb_test_case
{
	/**
	 * @var \phpbb\install\module\update_filesystem\task\diff_files
	 */
	protected $diff_task;

	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $config;

	/**
	 * @var phpbb_mock_container_builder
	 */
	protected $container;


	/**
	 * @var \phpbb\request\request
	 */
	protected $request;

	private static $helper;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $phpEx;

	protected function setUp(): void
	{
		$this->phpbb_root_path = __DIR__ . '/../../phpBB/';
		$this->phpEx = 'php';

		$language = new \phpbb\language\language(
			new \phpbb\language\language_file_loader($this->phpbb_root_path, $this->phpEx)
		);
		$this->request = new \phpbb\request\request();
		$update_helper = new \phpbb\install\helper\update_helper($this->phpbb_root_path);
		$this->container = new \phpbb\install\helper\container_factory($language, $this->request, $update_helper, $this->phpbb_root_path, $this->phpEx);

		$iohandler = $this->createMock('\phpbb\install\helper\iohandler\iohandler_interface');

		$this->config = new \phpbb\install\helper\config(new \phpbb\filesystem\filesystem(), new \bantu\IniGetWrapper\IniGetWrapper(), '');
		$update_files['update_with_diff'] = [
			'test_files_diff.php',
		];
		$this->config->set('update_files', $update_files);

		$this->diff_task = new \phpbb\install\module\update_filesystem\task\diff_files($this->container, $this->config, $iohandler, $update_helper, $this->phpbb_root_path, $this->phpEx);
	}

	protected function tearDown(): void
	{
		$disable_super_globals = $this->request->super_globals_disabled();

		// This is needed because \phpbb\install\helper\container_factory disables it
		if ($disable_super_globals)
		{
			$this->request->enable_super_globals();
		}
	}

	public static function setUpBeforeClass(): void
	{
		$phpbb_root_path = __DIR__ . '/../../phpBB/';

		parent::setUpBeforeClass();

		self::$helper = new phpbb_test_case_helpers(__CLASS__);
		self::$helper->copy_dir(__DIR__ . '/fixtures/', $phpbb_root_path);
	}

	public static function tearDownAfterClass(): void
	{
		$phpbb_root_path = __DIR__ . '/../../phpBB/';

		parent::tearDownAfterClass();

		self::$helper->empty_dir($phpbb_root_path . 'install/update');
		rmdir($phpbb_root_path . 'install/update');
		unlink($phpbb_root_path . 'test_files_diff.php');
	}

	public function test_diff_files()
	{
		$this->diff_task->run();

		$this->assertEmpty($this->config->get('update_files'));
	}
}
