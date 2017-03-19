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
use phpbb\console\command\update\check;

require_once dirname(__FILE__) . '/../../../phpBB/includes/functions_admin.php';
require_once dirname(__FILE__) . '/../../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../../phpBB/includes/utf/utf_tools.php';

/**
* @slow
*/
class phpbb_console_command_check_test extends phpbb_test_case
{
	protected $command_name;

	protected $version_helper;

	/** @var \phpbb\language\language */
	protected $language;

	public function test_up_to_date()
	{
		$command_tester = $this->get_command_tester('100000');
		$status = $command_tester->execute(array('command' => $this->command_name, '--no-ansi' => true));
		$this->assertSame('', $command_tester->getDisplay());
		$this->assertSame($status, 0);
	}

	public function test_up_to_date_verbose()
	{
		$command_tester = $this->get_command_tester('100000');
		$status = $command_tester->execute(array('command' => $this->command_name, '--no-ansi' => true, '--verbose' => true));
		$this->assertContains($this->language->lang('UPDATE_NOT_NEEDED'), $command_tester->getDisplay());
		$this->assertSame($status, 0);
	}


	public function test_not_up_to_date()
	{
		$command_tester = $this->get_command_tester('0');
		$status = $command_tester->execute(array('command' => $this->command_name, '--no-ansi' => true));
		$this->assertContains($this->language->lang('UPDATE_NEEDED'), $command_tester->getDisplay());
		$this->assertSame($status, 1);
	}

	public function test_not_up_to_date_verbose()
	{
		$command_tester = $this->get_command_tester('0');
		$status = $command_tester->execute(array('command' => $this->command_name, '--no-ansi' => true, '--verbose' => true));
		$this->assertContains($this->language->lang('UPDATE_NEEDED'), $command_tester->getDisplay());
		$this->assertContains($this->language->lang('UPDATES_AVAILABLE'), $command_tester->getDisplay());
		$this->assertSame($status, 1);
	}

	/**
	 * @expectedException phpbb\exception\runtime_exception
	 */
	public function test_error()
	{
		$command_tester = $this->get_command_tester('1');
		$this->version_helper->set_file_location('acme.corp','foo', 'bar.json');

		$status = $command_tester->execute(array('command' => $this->command_name, '--no-ansi' => true));
		$this->assertContains('VERSIONCHECK_FAIL', $command_tester->getDisplay());
		$this->assertSame($status, 2);
	}

	public function get_command_tester($current_version)
	{
		global $user, $phpbb_root_path, $phpEx;

		$this->language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));

		$user = $this->createMock('\phpbb\user', array(), array(
			$this->language,
			'\phpbb\datetime'
		));
		$user->method('lang')->will($this->returnArgument(0));

		$cache = $this->getMockBuilder('\phpbb\cache\service')
			->disableOriginalConstructor()
			->getMock();

		$config = new \phpbb\config\config(array('version' => $current_version));
		$this->version_helper = new \phpbb\version_helper($cache, $config, new \phpbb\file_downloader());

		$container = new phpbb_mock_container_builder;
		$container->set('version_helper', $this->version_helper);

		$application = new Application();
		$application->add(new check($user, $config, $container, $this->language));

		$command = $application->find('update:check');
		$this->command_name = $command->getName();
		return new CommandTester($command);
	}
}
