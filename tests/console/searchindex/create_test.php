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

use phpbb\console\command\searchindex\create;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

require_once __DIR__ . '/phpbb_console_searchindex_base.php';
require_once __DIR__ . '/../../mock/search_backend_mock.php';

class phpbb_console_searchindex_create_test extends phpbb_console_searchindex_base
{
	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new create(
			$this->language,
			$this->log,
			$this->post_helper,
			$this->search_backend_factory,
			$this->state_helper,
			$this->user
		));

		$command = $application->find('searchindex:create');

		return new CommandTester($command);
	}

	public function test_create()
	{
		$command_tester = $this->get_command_tester();

		$command_tester->execute([
			'search-backend' => 'search_backend_mock',
		]);

		$this->assertEquals(Command::SUCCESS, $command_tester->getStatusCode());
		$this->assertStringContainsString('CLI_SEARCHINDEX_CREATE_SUCCESS', $command_tester->getDisplay());
	}

	public function test_create_when_search_backend_dont_exist()
	{
		$command_tester = $this->get_command_tester();

		$command_tester->execute([
			'search-backend' => 'missing',
		]);

		$this->assertEquals(Command::FAILURE, $command_tester->getStatusCode());
		$this->assertStringContainsString('CLI_SEARCHINDEX_BACKEND_NOT_FOUND', $command_tester->getDisplay());
	}

	public function test_create_when_action_in_progress()
	{
		$this->config['search_indexing_state'] = ['not', 'empty'];

		$command_tester = $this->get_command_tester();

		$command_tester->execute([
			'search-backend' => 'search_backend_mock',
		]);

		$this->assertEquals(Command::FAILURE, $command_tester->getStatusCode());
		$this->assertStringContainsString('CLI_SEARCHINDEX_ACTION_IN_PROGRESS', $command_tester->getDisplay());

		$this->config['search_indexing_state'] = [];
	}

	public function test_create_when_search_backend_not_available()
	{
		$command_tester = $this->get_command_tester();

		$command_tester->execute([
			'search-backend' => 'search_backend_mock_not_available',
		]);

		$this->assertEquals(Command::FAILURE, $command_tester->getStatusCode());
		$this->assertStringContainsString('CLI_SEARCHINDEX_BACKEND_NOT_AVAILABLE', $command_tester->getDisplay());
	}
}
