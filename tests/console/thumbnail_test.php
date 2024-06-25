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

use phpbb\attachment\attachment_category;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use phpbb\console\command\thumbnail\generate;
use phpbb\console\command\thumbnail\delete;
use phpbb\console\command\thumbnail\recreate;

class phpbb_console_command_thumbnail_test extends phpbb_database_test_case
{
	protected $db;
	protected $config;
	protected $cache;
	protected $user;
	protected $phpEx;
	protected $phpbb_root_path;
	protected $application;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/thumbnail.xml');
	}

	protected function setUp(): void
	{
		global $config, $phpbb_root_path, $phpEx, $phpbb_filesystem;

		if (!@extension_loaded('gd'))
		{
			$this->markTestSkipped('Thumbnail tests require gd extension.');
		}

		parent::setUp();

		$config = $this->config = new \phpbb\config\config(array(
			'img_min_thumb_filesize' => 2,
			'img_max_thumb_width' => 2,
			'upload_path' => 'files',
		));

		$this->db = $this->db = $this->new_dbal();
		$this->language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$this->user = new \phpbb\user($this->language, '\phpbb\datetime');
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;

		$this->cache = $this->createMock('\phpbb\cache\service');
		$this->cache->expects(self::any())->method('obtain_attach_extensions')->will(self::returnValue(array(
			'png' => array('display_cat' => attachment_category::IMAGE),
			'txt' => array('display_cat' => attachment_category::NONE),
		)));

		$this->application = new Application();
		$this->application->add(new generate($config, $this->user, $this->db, $this->cache, $this->phpbb_root_path, $this->phpEx));
		$this->application->add(new delete($config, $this->user, $this->db, $this->phpbb_root_path));
		$this->application->add(new recreate($this->user));

		$phpbb_filesystem = new \phpbb\filesystem\filesystem();

		copy(__DIR__ . '/fixtures/png.png', $this->phpbb_root_path . 'files/test_png_1');
		copy(__DIR__ . '/fixtures/png.png', $this->phpbb_root_path . 'files/test_png_2');
		copy(__DIR__ . '/fixtures/png.png', $this->phpbb_root_path . 'files/thumb_test_png_2');
		copy(__DIR__ . '/fixtures/txt.txt', $this->phpbb_root_path . 'files/test_txt');
	}

	protected function tearDown(): void
	{
		parent::tearDown();

		unlink($this->phpbb_root_path . 'files/test_png_1');
		unlink($this->phpbb_root_path . 'files/test_png_2');
		unlink($this->phpbb_root_path . 'files/test_txt');
		unlink($this->phpbb_root_path . 'files/thumb_test_png_1');
		unlink($this->phpbb_root_path . 'files/thumb_test_png_2');
	}

	public function test_thumbnails()
	{
		$command_tester = $this->get_command_tester('thumbnail:generate');
		$exit_status = $command_tester->execute([]);

		self::assertSame(true, file_exists($this->phpbb_root_path . 'files/thumb_test_png_1'));
		self::assertSame(true, file_exists($this->phpbb_root_path . 'files/thumb_test_png_2'));
		self::assertSame(false, file_exists($this->phpbb_root_path . 'files/thumb_test_txt'));
		self::assertSame(0, $exit_status);

		$command_tester = $this->get_command_tester('thumbnail:delete');
		$exit_status = $command_tester->execute([]);

		self::assertSame(false, file_exists($this->phpbb_root_path . 'files/thumb_test_png_1'));
		self::assertSame(false, file_exists($this->phpbb_root_path . 'files/thumb_test_png_2'));
		self::assertSame(false, file_exists($this->phpbb_root_path . 'files/thumb_test_txt'));
		self::assertSame(0, $exit_status);

		$command_tester = $this->get_command_tester('thumbnail:recreate');
		$exit_status = $command_tester->execute([]);

		self::assertSame(true, file_exists($this->phpbb_root_path . 'files/thumb_test_png_1'));
		self::assertSame(true, file_exists($this->phpbb_root_path . 'files/thumb_test_png_2'));
		self::assertSame(false, file_exists($this->phpbb_root_path . 'files/thumb_test_txt'));
		self::assertSame(0, $exit_status);
	}

	public function get_command_tester($command_name)
	{
		$command = $this->application->find($command_name);
		return new CommandTester($command);
	}
}
