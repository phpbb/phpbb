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

class phpbb_console_command_config_test extends phpbb_test_case
{
	protected $config;
	protected $command_name;
	protected $class_name;
	protected $comand_namespace;

	public function setUp()
	{
		$this->config = new \phpbb\config\config(array());
		$this->command_namespace = '\phpbb\console\command\config';
	}

	public function test_set_dynamic()
	{
		$this->assertEmpty($this->config);

		$this->class_name = 'set';
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'	=> $this->command_name,
			'key'		=> 'test_key',
			'value'		=> 'test_value',
			'--dynamic'	=> true,
		));

		$this->assertSame($this->config['test_key'], 'test_value');
	}

	public function test_set_no_dynamic()
	{
		$this->assertEmpty($this->config);

		$this->class_name = 'set';
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'	=> $this->command_name,
			'key'		=> 'test_key',
			'value'		=> 'test_value',
			'--dynamic'	=> false,
		));

		$this->assertSame($this->config['test_key'], 'test_value');
	}

	public function test_set_atomic_dynamic()
	{
		$this->assertEmpty($this->config);

		$this->config->set('test_key', 'old_value', true);
		$this->assertSame($this->config['test_key'], 'old_value');

		$this->class_name = 'set_atomic';
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'	=> $this->command_name,
			'key'		=> 'test_key',
			'old'		=> 'old_value',
			'new'		=> 'new_value',
			'--dynamic'	=> true,
		));

		$this->assertSame($this->config['test_key'], 'new_value');
	}

	public function test_set_atomic_no_dynamic()
	{
		$this->assertEmpty($this->config);

		$this->config->set('test_key', 'old_value', false);
		$this->assertSame($this->config['test_key'], 'old_value');

		$this->class_name = 'set_atomic';
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'	=> $this->command_name,
			'key'		=> 'test_key',
			'old'		=> 'old_value',
			'new'		=> 'new_value',
			'--dynamic'	=> false
		));

		$this->assertSame($this->config['test_key'], 'new_value');
	}

	public function test_set_atomic_error_dynamic()
	{
		$this->assertEmpty($this->config);

		$this->config->set('test_key', 'wrong_value', true);
		$this->assertSame($this->config['test_key'], 'wrong_value');

		$this->class_name = 'set_atomic';
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'	=> $this->command_name,
			'key'		=> 'test_key',
			'old'		=> 'old_value',
			'new'		=> 'new_value',
			'--dynamic'	=> true,
		));

		$this->assertSame($this->config['test_key'], 'wrong_value');
	}

	public function test_get_no_new_line()
	{
		$this->config->set('test_key', 'test_value', false);
		$this->assertSame($this->config['test_key'], 'test_value');

		$this->class_name = 'get';
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'		=> $this->command_name,
			'key'			=> 'test_key',
			'--no-newline'	=> true,
		));

		$this->assertSame($this->config['test_key'], $command_tester->getDisplay());
	}

	public function test_get_new_line()
	{
		$this->config->set('test_key', 'test_value', false);
		$this->assertSame($this->config['test_key'], 'test_value');

		$this->class_name = 'get';
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'		=> $this->command_name,
			'key'			=> 'test_key',
			'--no-newline'	=> false,
		));

		$this->assertSame($this->config['test_key'] . "\n", $command_tester->getDisplay());
	}

	public function test_get_error()
	{
		$this->config->set('test_key', 'test_value', false);
		$this->assertSame($this->config['test_key'], 'test_value');

		$this->class_name = 'get';
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'		=> $this->command_name,
			'key'			=> 'wrong_key',
			'--no-newline'	=> false,
		));

		$this->assertContains('Could not get config', $command_tester->getDisplay());
	}

	public function test_increment_dynamic()
	{
		$this->config->set('test_key', 0, false);
		$this->assertSame($this->config['test_key'], 0);

		$this->class_name = 'increment';
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'		=> $this->command_name,
			'key'			=> 'test_key',
			'increment'		=> 2,
			'--dynamic'		=> true,
		));

		$this->assertContains('Successfully incremented config test_key', $command_tester->getDisplay());
		$this->assertSame(2, $this->config['test_key']);
	}

	public function test_increment_no_dynamic()
	{
		$this->config->set('test_key', 0, false);
		$this->assertSame($this->config['test_key'], 0);

		$this->class_name = 'increment';
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'		=> $this->command_name,
			'key'			=> 'test_key',
			'increment'		=> 2,
			'--dynamic'		=> false,
		));

		$this->assertContains('Successfully incremented config test_key', $command_tester->getDisplay());
		$this->assertSame(2, $this->config['test_key']);
	}

	public function test_increment_no_set()
	{
		$this->assertEmpty($this->config);

		$this->class_name = 'increment';
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'		=> $this->command_name,
			'key'			=> 'test_key',
			'increment'		=> 2,
			'--dynamic'		=> true,
		));

		$this->assertContains('Successfully incremented config test_key', $command_tester->getDisplay());
		$this->assertSame(2, $this->config['test_key']);
	}

	public function test_delete_ok()
	{
		$this->config->set('test_key', 'test_value', false);
		$this->assertSame($this->config['test_key'], 'test_value');

		$this->class_name = 'delete';

		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'		=> $this->command_name,
			'key'			=> 'test_key',
		));

		$this->assertContains('Successfully deleted config test_key', $command_tester->getDisplay());
		$this->assertEmpty($this->config);
	}

	public function test_delete_error()
	{
		$this->assertEmpty($this->config);

		$this->class_name = 'delete';

		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'		=> $this->command_name,
			'key'			=> 'wrong_key',
		));

		$this->assertContains('Config wrong_key does not exist', $command_tester->getDisplay());
		$this->assertEmpty($this->config);
	}

	public function get_command_tester()
	{
		$command_complete_name = $this->command_namespace . '\\' . $this->class_name;
		$application = new Application();
		$application->add(new $command_complete_name($this->config));
		$command = $application->find('config:' . $this->command_name);
		$this->command_name = $command->getName();
		return new CommandTester($command);
	}
}
