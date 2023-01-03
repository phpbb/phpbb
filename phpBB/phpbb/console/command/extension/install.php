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

namespace phpbb\console\command\extension;

use phpbb\composer\extension_manager;
use phpbb\composer\io\console_io;
use phpbb\language\language;
use Symfony\Component\Console\Command\Command as symfony_command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class install extends \phpbb\console\command\command
{
	/**
	 * @var extension_manager Composer extensions manager
	 */
	protected $manager;

	/**
	 * @var language
	 */
	protected $language;

	public function __construct(\phpbb\user $user, extension_manager $manager, language $language)
	{
		$this->manager = $manager;
		$this->language = $language;

		$language->add_lang('acp/extensions');

		parent::__construct($user);
	}

	/**
	* Sets the command name and description
	*
	* @return void
	*/
	protected function configure()
	{
		$this
			->setName('extension:install')
			->setDescription($this->language->lang('CLI_DESCRIPTION_EXTENSION_INSTALL'))
			->addOption(
				'enable',
				null,
				InputOption::VALUE_NONE,
				$this->language->lang('CLI_DESCRIPTION_EXTENSION_INSTALL_OPTION_ENABLE'))
			->addArgument(
				'extensions',
				InputArgument::IS_ARRAY | InputArgument::REQUIRED,
				$this->language->lang('CLI_DESCRIPTION_EXTENSION_INSTALL_ARGUMENT'))
		;
	}

	/**
	* Executes the command extension:install
	*
	* @param InputInterface $input
	* @param OutputInterface $output
	* @return int
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->getFormatter()->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));

		$io = new SymfonyStyle($input, $output);

		if (!$this->manager->check_requirements())
		{
			$io->error($this->language->lang('EXTENSIONS_COMPOSER_NOT_WRITABLE'));
			return symfony_command::FAILURE;
		}

		$composer_io = new console_io($input, $output, $this->getHelperSet(), $this->language);
		$extensions = $input->getArgument('extensions');

		if ($input->getOption('enable'))
		{
			$this->manager->set_enable_on_install(true);
		}

		$this->manager->install($extensions, $composer_io);

		$io->success($this->language->lang('EXTENSIONS_INSTALLED'));

		return symfony_command::SUCCESS;
	}
}
