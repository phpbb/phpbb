<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\install\console\command\converter;

use phpbb\install\exception\installer_exception;
use phpbb\install\converter\module\converter_obtain_data\module;
use phpbb\install\converter\controller\helper;
use phpbb\install\helper\config;
use phpbb\install\helper\install_helper;
use phpbb\install\helper\iohandler\cli_iohandler;
use phpbb\install\helper\iohandler\factory;
use phpbb\install\helper\container_factory;
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

class convert extends \phpbb\console\command\command
{
	/**
	 * @var factory
	 */
	protected $iohandler_factory;

	/**
	 * @var install_helper
	 */
	protected $helper;

	/**
	 * @var language
	 */
	protected $language;

	protected $obtain_data_module;

	protected $container_factory;

	protected $install_config;
	/**
	 * Constructor
	 *
	 * @param language			$language
	 * @param factory			$factory
	 * @param installer			$installer
	 * @param install_helper	$install_helper
	 */
	public function __construct(config $install_config, container_factory $container, language $language, factory $factory, module $obtain_data,  helper $helper)
	{
		$this->iohandler_factory = $factory;
		$this->install_config = $install_config;
		$this->container_factory = $container;
		$this->language = $language;
		$this->obtain_data_module = $obtain_data;
		$this->helper = $helper;

		parent::__construct(new \phpbb\user($language, 'datetime'));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('convert')
			->addArgument(
				'config-file',
				InputArgument::REQUIRED,
				$this->language->lang('CLI_CONFIG_FILE'))
			->setDescription($this->language->lang('CLI_CF_CONVERT'))
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
		//ALWAYS PURGE CACHES ELSE DI CONTAINER WONT BE REFRESHED
		if (!$this->install_config->get('cache_purged_before', false))
		{
			// DO NOT REMOVE THIS LINE. OLD installer_config.php might completely mess the converter framework
			$this->install_config->clean_up_config_file(); //Get rid of any accidental store/installer_config.php left over by previous commands
			/** @var \phpbb\cache\driver\driver_interface $cache */
			$cache = $this->container_factory->get('cache.driver');
			$cache->purge();
			$this->install_config->set('cache_purged_before', true);
			$this->install_config->save_config();

		}

		$this->iohandler_factory->set_environment('cli');

		/** @var \phpbb\install\helper\iohandler\cli_iohandler $iohandler */
		$iohandler = $this->iohandler_factory->get();
		$style = new SymfonyStyle($input, $output);
		$iohandler->set_style($style, $output);

		$config_file = $input->getArgument('config-file');

		try
		{
			$config = Yaml::parse(file_get_contents($config_file), true, false);
		}
		catch (ParseException $e)
		{
			$iohandler->add_error_message(array('INVALID_YAML_FILE', $config_file));

			return 1;
		}
		$this->register_configuration($iohandler,$config['database']);
		$this->obtain_data_module->setup($this->install_config,$iohandler);
		$this->obtain_data_module->run();
		//cleanup
		$this->install_config->clean_up_config_file();
		$cache = $this->container_factory->get('cache.driver');
		$cache->purge();

//		if ($this->install_helper->is_phpbb_installed())
//		{
//			$iohandler->add_error_message('INSTALL_PHPBB_INSTALLED');
//
//			return 1;
//		}
//
//		if (!is_file($config_file))
//		{
//			$iohandler->add_error_message(array('MISSING_FILE', $config_file));
//
//			return 1;
//		}
//
//		try
//		{
//			$config = Yaml::parse(file_get_contents($config_file), true, false);
//		}
//		catch (ParseException $e)
//		{
//			$iohandler->add_error_message(array('INVALID_YAML_FILE', $config_file));
//
//			return 1;
//		}
//
//		$processor = new Processor();
//		$configuration = new installer_configuration();
//
//		try
//		{
//			$config = $processor->processConfiguration($configuration, $config);
//		}
//		catch (Exception $e)
//		{
//			$iohandler->add_error_message('INVALID_CONFIGURATION', $e->getMessage());
//
//			return 1;
//		}
//
//		$this->register_configuration($iohandler, $config);
//
//		try
//		{
//			$this->installer->run();
//			return 0;
//		}
//		catch (installer_exception $e)
//		{
//			$iohandler->add_error_message($e->getMessage());
//			return 1;
//		}
	}

	/**
	 * Register the configuration to simulate the forms.
	 *
	 * @param cli_iohandler $iohandler
	 * @param array $config
	 */
	private function register_configuration(cli_iohandler $iohandler, $database_config)
	{
		$iohandler->set_input('db_name', $database_config['db_name']);
		$iohandler->set_input('db_pass', $database_config['db_pass']);
		$iohandler->set_input('db_user', $database_config['db_user']);
		$iohandler->set_input('db_host', $database_config['db_host']);
	}
}
