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

namespace phpbb\install\console\command\update;

use phpbb\install\exception\installer_exception;
use phpbb\install\helper\install_helper;
use phpbb\install\helper\iohandler\cli_iohandler;
use phpbb\install\helper\iohandler\factory;
use phpbb\install\installer;
use phpbb\install\updater_configuration;
use phpbb\language\language;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class update extends \phpbb\console\command\command
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
			->setName('update')
			->addArgument(
				'config-file',
				InputArgument::REQUIRED,
				$this->language->lang('CLI_CONFIG_FILE'))
			->setDescription($this->language->lang('CLI_UPDATE_BOARD'))
		;
	}

	/**
	 * Executes the command update.
	 *
	 * Update the board
	 *
	 * @param InputInterface  $input  An InputInterface instance
	 * @param OutputInterface $output An OutputInterface instance
	 *
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->iohandler_factory->set_environment('cli');

		/** @var \phpbb\install\helper\iohandler\cli_iohandler $iohandler */
		$iohandler = $this->iohandler_factory->get();
		$style = new SymfonyStyle($input, $output);
		$iohandler->set_style($style, $output);

		$this->installer->set_iohandler($iohandler);

		$config_file = $input->getArgument('config-file');

		if (!$this->install_helper->is_phpbb_installed())
		{
			$iohandler->add_error_message('INSTALL_PHPBB_NOT_INSTALLED');

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
		$configuration = new updater_configuration();

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
		$iohandler->set_input('update_type', $config['type']);
		$iohandler->set_input('submit_update', 'submit');

		$iohandler->set_input('compression_method', '.tar');
		$iohandler->set_input('method', 'direct_file');
		$iohandler->set_input('submit_update_file', 'submit');

		$iohandler->set_input('submit_continue_file_update', 'submit');

		$iohandler->set_input('update-extensions', $config['extensions']);
	}
}
