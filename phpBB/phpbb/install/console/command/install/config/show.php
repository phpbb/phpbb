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

namespace phpbb\install\console\command\install\config;

use phpbb\install\helper\iohandler\factory;
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

class show extends \phpbb\console\command\command
{
	/**
	 * @var factory
	 */
	protected $iohandler_factory;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * Constructor
	 *
	 * @param language $language
	 * @param factory $factory
	 */
	public function __construct(language $language, factory $factory)
	{
		$this->iohandler_factory = $factory;
		$this->language = $language;

		parent::__construct(new \phpbb\user($language, 'datetime'));
	}

	/**
	 *
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('install:config:show')
			->addArgument(
				'config-file',
				InputArgument::REQUIRED,
				$this->language->lang('CLI_CONFIG_FILE'))
			->setDescription($this->language->lang('CLI_INSTALL_SHOW_CONFIG'))
		;
	}

	/**
	 * Show the validated configuration
	 *
	 * @param InputInterface  $input  An InputInterface instance
	 * @param OutputInterface $output An OutputInterface instance
	 *
	 * @return null
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->iohandler_factory->set_environment('cli');

		/** @var \phpbb\install\helper\iohandler\cli_iohandler $iohandler */
		$iohandler = $this->iohandler_factory->get();
		$style = new SymfonyStyle($input, $output);
		$iohandler->set_style($style, $output);

		$config_file = $input->getArgument('config-file');

		if (!is_file($config_file))
		{
			$iohandler->add_error_message(array('MISSING_FILE', $config_file));

			return;
		}

		try
		{
			$config = Yaml::parse(file_get_contents($config_file), true, false);
		}
		catch (ParseException $e)
		{
			$iohandler->add_error_message('INVALID_YAML_FILE');

			return;
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

			return;
		}

		$style->block(Yaml::dump(array('installer' => $config), 10, 4, true, false));
	}
}
