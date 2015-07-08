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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_compatibility.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

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
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/thumbnail.xml');
	}

	public function setUp()
	{
		global $config, $phpbb_root_path, $phpEx, $phpbb_filesystem;

		parent::setUp();

		$config = $this->config = new \phpbb\config\config(array(
			'img_min_thumb_filesize' => 2,
			'img_max_thumb_width' => 2,
			'img_imagick' => '',
		));

		$this->db = $this->db = $this->new_dbal();
		$this->user = $this->getMock('\phpbb\user', array(), array(
				new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
				'\phpbb\datetime')
		);
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;

		$this->cache = $this->getMock('\phpbb\cache\service', array(), array(new phpbb_mock_cache(), $this->config, $this->db, $this->phpbb_root_path, $this->phpEx));
		$this->cache->expects(self::any())->method('obtain_attach_extensions')->will(self::returnValue(array(
			'png' => array('display_cat' => ATTACHMENT_CATEGORY_IMAGE),
			'txt' => array('display_cat' => ATTACHMENT_CATEGORY_NONE),
		)));

		$this->application = new Application();
		$this->application->add(new generate($this->user, $this->db, $this->cache, $this->phpbb_root_path, $this->phpEx));
		$this->application->add(new delete($this->user, $this->db, $this->phpbb_root_path));
		$this->application->add(new recreate($this->user));

		$phpbb_filesystem = new \phpbb\filesystem\filesystem();

		copy(dirname(__FILE__) . '/fixtures/png.png', $this->phpbb_root_path . 'files/test_png_1');
		copy(dirname(__FILE__) . '/fixtures/png.png', $this->phpbb_root_path . 'files/test_png_2');
		copy(dirname(__FILE__) . '/fixtures/png.png', $this->phpbb_root_path . 'files/thumb_test_png_2');
		copy(dirname(__FILE__) . '/fixtures/txt.txt', $this->phpbb_root_path . 'files/test_txt');
	}

	protected function tearDown()
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
		$exit_status = $command_tester->execute(array('command' => 'thumbnail:generate'));

		self::assertSame(true, file_exists($this->phpbb_root_path . 'files/thumb_test_png_1'));
		self::assertSame(true, file_exists($this->phpbb_root_path . 'files/thumb_test_png_2'));
		self::assertSame(false, file_exists($this->phpbb_root_path . 'files/thumb_test_txt'));
		self::assertSame(0, $exit_status);

		$command_tester = $this->get_command_tester('thumbnail:delete');
		$exit_status = $command_tester->execute(array('command' => 'thumbnail:delete'));

		self::assertSame(false, file_exists($this->phpbb_root_path . 'files/thumb_test_png_1'));
		self::assertSame(false, file_exists($this->phpbb_root_path . 'files/thumb_test_png_2'));
		self::assertSame(false, file_exists($this->phpbb_root_path . 'files/thumb_test_txt'));
		self::assertSame(0, $exit_status);

		$command_tester = $this->get_command_tester('thumbnail:recreate');
		$exit_status = $command_tester->execute(array('command' => 'thumbnail:recreate'));

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
