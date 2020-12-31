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

namespace phpbb\install\console\command\install;

use phpbb\install\exception\installer_exception;
use phpbb\install\helper\install_helper;
use phpbb\install\helper\iohandler\cli_iohandler;
use phpbb\install\helper\iohandler\factory;
use phpbb\install\installer;
use phpbb\install\installer_configuration;
use phpbb\language\language;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class install extends \phpbb\console\command\command
{
	/**
	 * @var factory
	 */
	protected $iohandler_factory;

	/**
	 * @var installer
	 */
	protected $installer;

	/**
	 * @var install_helper
	 */
	protected $install_helper;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * Constructor
	 *
	 * @param language			$language
	 * @param factory			$factory
	 * @param installer			$installer
	 * @param install_helper	$install_helper
	 */
	public function __construct(language $language, factory $factory, installer $installer, install_helper $install_helper)
	{
		$this->iohandler_factory = $factory;
		$this->installer = $installer;
		$this->language = $language;
		$this->install_helper = $install_helper;

		parent::__construct(new \phpbb\user($language, 'datetime'));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('install')
			->addArgument(
				'config-file',
				InputArgument::REQUIRED,
				$this->language->lang('CLI_CONFIG_FILE'))
			->setDescription($this->language->lang('CLI_INSTALL_BOARD'))
		;
	}

	/**
	 * Executes the command install.
	 *
	 * Install the board
	 *
	 * @param InputInterface  $input  An InputInterface instance
	 * @param OutputInterface $output An OutputInterface instance
	 *
	 * @return null
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->iohandler_factory->set_environment('cli');

		/** @var cli_iohandler $iohandler */
		$iohandler = $this->iohandler_factory->get();
		$style = new SymfonyStyle($input, $output);
		$iohandler->set_style($style, $output);

		$this->installer->set_iohandler($iohandler);

		$config_file = $input->getArgument('config-file');

		if ($this->install_helper->is_phpbb_installed())
		{
			$iohandler->add_error_message('INSTALL_PHPBB_INSTALLED');

			return 1;
		}

		if (!is_file($config_file))
		{
			$iohandler->add_error_message(array('MISSING_FILE', $config_file));

			return 1;
		}

		try
		{
			$config = Yaml::parse(file_get_contents($config_file), true, false);
		}
		catch (ParseException $e)
		{
			$iohandler->add_error_message(array('INVALID_YAML_FILE', $config_file));

			return 1;
		}

		$processor = new Processor();
		$configuration = new installer_configuration();

		try
		{
			$config = $processor->processConfiguration($configuration, $config);
		}
		catch (Exception $e)
		{
			$iohandler->add_error_message('INVALID_CONFIGURATION', $e->getMessage());

			return 1;
		}

		$this->register_configuration($iohandler, $config);

		try
		{
			$this->installer->run();
			return 0;
		}
		catch (installer_exception $e)
		{
			$iohandler->add_error_message($e->getMessage());
			return 1;
		}
	}

	/**
	 * Register the configuration to simulate the forms.
	 *
	 * @param cli_iohandler $iohandler
	 * @param array $config
	 */
	private function register_configuration(cli_iohandler $iohandler, $config)
	{
		$iohandler->set_input('admin_name', $config['admin']['name']);
		$iohandler->set_input('admin_pass1', $config['admin']['password']);
		$iohandler->set_input('admin_pass2', $config['admin']['password']);
		$iohandler->set_input('board_email', $config['admin']['email']);
		$iohandler->set_input('submit_admin', 'submit');

		$iohandler->set_input('default_lang', $config['board']['lang']);
		$iohandler->set_input('board_name', $config['board']['name']);
		$iohandler->set_input('board_description', $config['board']['description']);
		$iohandler->set_input('submit_board', 'submit');

		$iohandler->set_input('dbms', $config['database']['dbms']);
		$iohandler->set_input('dbhost', $config['database']['dbhost']);
		$iohandler->set_input('dbport', $config['database']['dbport']);
		$iohandler->set_input('dbuser', $config['database']['dbuser']);
		$iohandler->set_input('dbpasswd', $config['database']['dbpasswd']);
		$iohandler->set_input('dbname', $config['database']['dbname']);
		$iohandler->set_input('table_prefix', $config['database']['table_prefix']);
		$iohandler->set_input('submit_database', 'submit');

		$iohandler->set_input('email_enable', $config['email']['enabled']);
		$iohandler->set_input('smtp_delivery', $config['email']['smtp_delivery']);
		$iohandler->set_input('smtp_host', $config['email']['smtp_host']);
		$iohandler->set_input('smtp_port', $config['email']['smtp_port']);
		$iohandler->set_input('smtp_auth', $config['email']['smtp_auth']);
		$iohandler->set_input('smtp_user', $config['email']['smtp_user']);
		$iohandler->set_input('smtp_pass', $config['email']['smtp_pass']);
		$iohandler->set_input('submit_email', 'submit');

		$iohandler->set_input('cookie_secure', $config['server']['cookie_secure']);
		$iohandler->set_input('server_protocol', $config['server']['server_protocol']);
		$iohandler->set_input('force_server_vars', $config['server']['force_server_vars']);
		$iohandler->set_input('server_name', $config['server']['server_name']);
		$iohandler->set_input('server_port', $config['server']['server_port']);
		$iohandler->set_input('script_path', $config['server']['script_path']);
		$iohandler->set_input('submit_server', 'submit');

		$iohandler->set_input('install-extensions', $config['extensions']);
	}
}
