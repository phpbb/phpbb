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
use phpbb\console\command\user\reclean;

class phpbb_console_command_user_reclean_test extends phpbb_database_test_case
{
	protected $db;
	protected $user;
	protected $language;
	protected $command_name;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	public function setUp()
	{
		$this->db = $this->new_dbal();

		$this->language = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$this->language->expects($this->any())
			->method('lang')
			->will($this->returnArgument(0));
		$this->user = $this->getMock('\phpbb\user', array(), array(
			$this->language,
			'\phpbb\datetime'
		));

		parent::setUp();
	}

	public function test_reclean()
	{
		$command_tester = $this->get_command_tester();

		$command_tester->execute(array(
			'command' => $this->command_name,
		));

		$this->assertContains('CLI_USER_RECLEAN_SUCCESS', $command_tester->getDisplay());

		$result = $this->db->sql_query('SELECT user_id FROM ' . USERS_TABLE . " WHERE username_clean = 'test unclean'");
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		$this->assertNotNull($row['user_id']);
	}

	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new reclean(
			$this->user,
			$this->db,
			$this->language
		));

		$command = $application->find('user:reclean');
		$this->command_name = $command->getName();

		return new CommandTester($command);
	}
}
