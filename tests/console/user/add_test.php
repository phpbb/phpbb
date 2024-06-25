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
use phpbb\console\command\user\add;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

require_once __DIR__ . '/base.php';

class phpbb_console_user_add_test extends phpbb_console_user_base
{
	public function get_command_tester($question_answers = [])
	{
		$application = new Application();
		$application->add(new add(
			$this->user,
			$this->db,
			$this->config,
			$this->language,
			$this->passwords_manager,
			$this->phpbb_root_path,
			$this->php_ext
		));

		$command = $application->find('user:add');

		if (!empty($question_answers))
		{
			$ask = function(InputInterface $input, OutputInterface $output, Question $question) use ($question_answers)
			{
				$text = $question->getQuestion();

				// handle a question
				foreach ($question_answers as $expected_question => $answer)
				{
					if (strpos($text, $expected_question) !== false)
					{
						$response = $answer;
					}
				}

				if (!isset($response))
				{
					throw new \RuntimeException('Was asked for input on an unhandled question: ' . $text);
				}

				$output->writeln(print_r($response, true));
				return $response;
			};
			$helper = $this->getMockBuilder('\Symfony\Component\Console\Helper\QuestionHelper')
				->setMethods(['ask'])
				->disableOriginalConstructor()
				->getMock();
			$helper->expects($this->any())
				->method('ask')
				->will($this->returnCallback($ask));
			$this->question = $helper;
			$command->getHelperSet()->set($helper, 'question');
		}
		else
		{
			$this->question = $command->getHelper('question');
		}

		return new CommandTester($command);
	}

	public function test_add_no_dialog()
	{
		$command_tester = $this->get_command_tester();

		$this->assertEquals(2, $this->get_user_id('Admin'));

		$command_tester->execute(array(
			'--username'	=> 'foo',
			'--password'	=> 'bar',
			'--email'		=> 'foo@test.com'
		));

		$this->assertNotEquals(null, $this->get_user_id('foo'));
		$this->assertStringContainsString('CLI_USER_ADD_SUCCESS', $command_tester->getDisplay());
	}

	public function test_add_dialog()
	{
		$command_tester = $this->get_command_tester([
			'USERNAME'		=> 'bar',
			'PASSWORD'		=> 'password',
			'EMAIL_ADDRESS'	=> 'bar@test.com',
		]);

		$this->assertEquals(2, $this->get_user_id('Admin'));

		$command_tester->setInputs(['bar', 'password', 'password', 'bar@test.com']);

		$command_tester->execute([]);

		$this->assertNotEquals(null, $this->get_user_id('bar'));
		$this->assertStringContainsString('CLI_USER_ADD_SUCCESS', $command_tester->getDisplay());

	}

	public function test_add_no_dialog_invalid()
	{
		$command_tester = $this->get_command_tester();

		$this->assertEquals(3, $this->get_user_id('Test'));

		$command_tester->execute(array(
			'--username'	=> 'Test',
			'--password'	=> '1',
			'--email'		=> 'foo'
		));

		$this->assertStringContainsString('USERNAME_TAKEN', $command_tester->getDisplay());
		$this->assertStringContainsString('TOO_SHORT', $command_tester->getDisplay());
		$this->assertStringContainsString('EMAIL_INVALID', $command_tester->getDisplay());
	}
}
