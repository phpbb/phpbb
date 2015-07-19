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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use phpbb\console\command\cache\purge;

require_once dirname(__FILE__) . '/../../../phpBB/includes/functions_admin.php';

class phpbb_console_command_cache_purge_test extends phpbb_test_case
{
	protected $cache_dir;
	protected $cache;
	protected $command_name;
	protected $db;
	protected $config;

	public function __construct()
	{
		$this->cache_dir = dirname(__FILE__) . '/tmp/cache/';
	}

	protected function setUp()
	{
		global $phpbb_root_path, $phpEx;

		if (file_exists($this->cache_dir))
		{
			// cache directory possibly left after aborted
			// or failed run earlier
			$this->remove_cache_dir();
		}
		$this->create_cache_dir();

		$this->cache = new \phpbb\cache\driver\file($this->cache_dir);

		$this->db = $this->getMock('\phpbb\db\driver\driver_interface');

		$this->config = new \phpbb\config\config(array('assets_version' => 1));
		$this->user = $this->getMock('\phpbb\user', array(), array(
			new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
			'\phpbb\datetime')
		);
	}

	public function test_purge()
	{
		$this->cache->put('test_key', 'test_value');

		$this->assertEquals(
			'test_value',
			$this->cache->get('test_key'),
			'File ACM put and get'
		);

		$command_tester = $this->get_command_tester();
		$exit_status = $command_tester->execute(array('command' => $this->command_name));

		$this->assertSame(false, $this->cache->get('test_key'));
		$this->assertSame(2, $this->config['assets_version']);
	}

	private function create_cache_dir()
	{
		$this->get_test_case_helpers()->makedirs($this->cache_dir);
	}

	private function remove_cache_dir()
	{
		$iterator = new DirectoryIterator($this->cache_dir);
		foreach ($iterator as $file)
		{
			if ($file != '.' && $file != '..')
			{
				unlink($this->cache_dir . '/' . $file);
			}
		}
		rmdir($this->cache_dir);
	}

	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new purge($this->user, $this->cache, $this->db, $this->getMock('\phpbb\auth\auth'), new \phpbb\log\dummy(), $this->config));

		$command = $application->find('cache:purge');
		$this->command_name = $command->getName();
		return new CommandTester($command);
	}
}
