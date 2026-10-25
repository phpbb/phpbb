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

use phpbb\install\exception\user_interaction_required_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\module\obtain_data\task\obtain_server_data;

class phpbb_installer_obtain_server_data_test extends phpbb_test_case
{
	/**
	 * @var \phpbb\install\helper\config
	 */
	private $config;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	private $io_handler;

	/**
	 * @var array
	 */
	private $input;

	/**
	 * @var array
	 */
	private $errors;

	protected function setUp(): void
	{
		$phpbb_root_path = __DIR__ . './../../phpBB/';
		$filesystem = $this->createMock('\phpbb\filesystem\filesystem');
		$php_ini = $this->getMockBuilder('\bantu\IniGetWrapper\IniGetWrapper')
			->setMethods(array('getInt', 'getBytes'))
			->getMock();
		$php_ini->method('getInt')
			->willReturn(-1);
		$php_ini->method('getBytes')
			->willReturn(-1);

		$this->config = new config($filesystem, $php_ini, $phpbb_root_path);

		$this->input = array();
		$this->errors = array();

		$this->io_handler = $this->createMock(iohandler_interface::class);
		$this->io_handler->method('is_secure')
			->willReturn(false);
		$this->io_handler->method('get_server_variable')
			->willReturnCallback(function ($name, $default = '') {
				return $default;
			});
		$this->io_handler->method('get_header_variable')
			->willReturnCallback(function ($name, $default = '') {
				return $default;
			});
		$this->io_handler->method('get_input')
			->willReturnCallback(function ($name, $default, $multibyte = false) {
				return array_key_exists($name, $this->input) ? $this->input[$name] : $default;
			});
		$this->io_handler->method('add_error_message')
			->willReturnCallback(function ($error_title) {
				$this->errors[] = $error_title;
			});
	}

	/**
	 * Set the input values returned by the IO handler.
	 *
	 * @param array $input Input values
	 */
	private function set_input(array $input)
	{
		$this->input = $input;
	}

	public function test_run_rejects_whitespace_only_server_name()
	{
		$this->io_handler->expects($this->once())
			->method('add_user_form_group')
			->with('SERVER_CONFIG', $this->isType('array'));

		$this->set_input(array(
			'cookie_secure'		=> 0,
			'force_server_vars'	=> 0,
			'script_path'		=> '/',
			'server_name'		=> " \t ",
			'server_port'		=> 80,
			'server_protocol'	=> 'http://',
			'submit_server'		=> true,
		));

		$task = new obtain_server_data($this->config, $this->io_handler);

		try
		{
			$task->run();
			$this->fail('Expected a user interaction required exception.');
		}
		catch (user_interaction_required_exception $e)
		{
		}

		$this->assertSame(array('INST_ERR_MISSING_SERVER_NAME'), $this->errors);
		$this->assertFalse($this->config->get('server_name'));
	}

	public function test_run_trims_server_name()
	{
		$this->io_handler->expects($this->never())
			->method('add_error_message');
		$this->io_handler->expects($this->never())
			->method('add_user_form_group');

		$this->set_input(array(
			'cookie_secure'		=> 0,
			'force_server_vars'	=> 0,
			'script_path'		=> '/',
			'server_name'		=> '  example.com  ',
			'server_port'		=> 80,
			'server_protocol'	=> 'http://',
			'submit_server'		=> true,
		));

		$task = new obtain_server_data($this->config, $this->io_handler);
		$task->run();

		$this->assertSame('example.com', $this->config->get('server_name'));
		$this->assertSame('http://', $this->config->get('server_protocol'));
		$this->assertSame(80, $this->config->get('server_port'));
		$this->assertSame('/', $this->config->get('script_path'));
	}
}
