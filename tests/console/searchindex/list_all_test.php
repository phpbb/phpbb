<?php

use phpbb\console\command\searchindex\list_all;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

require_once __DIR__ . '/base.php';
require_once __DIR__ . '/mock/search_backend_mock.php';

class list_all_test extends base
{
	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new list_all(
			$this->config,
			$this->language,
			$this->search_backend_collection,
			$this->user
		));

		$command = $application->find('searchindex:list');

		return new CommandTester($command);
	}

	public function test_list()
	{
		$command_tester = $this->get_command_tester();

		$command_tester->execute([]);

		$this->assertEquals(Command::SUCCESS, $command_tester->getStatusCode());
		$this->assertStringContainsString('Mock search backend', $command_tester->getDisplay());
		$this->assertStringContainsString('ACTIVE', $command_tester->getDisplay());
	}
}
