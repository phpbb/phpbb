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

		$this->assertSame($this->config['test_key'],'test_value');
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

		$this->assertSame($this->config['test_key'],'test_value');
	}

	public function test_set_atomic_dynamic()
	{
		$this->assertEmpty($this->config);

		$this->config->set('test_key', 'old_value', true);
		$this->assertSame($this->config['test_key'],'old_value');

		$this->class_name = 'set_atomic';
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'	=> $this->command_name,
			'key'		=> 'test_key',
			'old'		=> 'old_value',
			'new'		=> 'new_value',
			'--dynamic'	=> true,
		));

		$this->assertSame($this->config['test_key'],'new_value');
	}

	public function test_set_atomic_no_dynamic()
	{
		$this->assertEmpty($this->config);

		$this->config->set('test_key', 'old_value', false);
		$this->assertSame($this->config['test_key'],'old_value');

		$this->class_name = 'set_atomic';
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'	=> $this->command_name,
			'key'		=> 'test_key',
			'old'		=> 'old_value',
			'new'		=> 'new_value',
			'--dynamic'	=> false
		));

		$this->assertSame($this->config['test_key'],'new_value');
	}

	public function test_set_atomic_error_dynamic()
	{
		$this->assertEmpty($this->config);

		$this->config->set('test_key', 'wrong_value', true);
		$this->assertSame($this->config['test_key'],'wrong_value');

		$this->class_name = 'set_atomic';
		$command_tester = $this->get_command_tester();
		$command_tester->execute(array(
			'command'	=> $this->command_name,
			'key'		=> 'test_key',
			'old'		=> 'old_value',
			'new'		=> 'new_value',
			'--dynamic'	=> true,
		));

		$this->assertSame($this->config['test_key'],'wrong_value');
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
