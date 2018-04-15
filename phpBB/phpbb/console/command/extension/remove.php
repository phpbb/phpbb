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
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class remove extends \phpbb\console\command\command
{
	/**
	 * @var extension_manager Composer extensions manager
	 */
	protected $manager;

	/**
	 * @var \phpbb\language\language
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
	* @return null
	*/
	protected function configure()
	{
		$this
			->setName('extension:remove')
			->setDescription($this->language->lang('CLI_DESCRIPTION_EXTENSION_REMOVE'))
			->addOption(
				'purge',
				null,
				InputOption::VALUE_NONE,
				$this->language->lang('CLI_DESCRIPTION_EXTENSION_REMOVE_OPTION_PURGE'))
			->addArgument(
				'extensions',
				InputArgument::IS_ARRAY | InputArgument::REQUIRED,
				$this->language->lang('CLI_DESCRIPTION_EXTENSION_REMOVE_ARGUMENT'))
		;
	}

	/**
	* Executes the command extension:install
	*
	* @param InputInterface $input
	* @param OutputInterface $output
	* @return integer
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->getFormatter()->setStyle('warning', new OutputFormatterStyle('black', 'yellow'));

		$io = new SymfonyStyle($input, $output);

		if (!$this->manager->check_requirements())
		{
			$io->error($this->language->lang('EXTENSIONS_COMPOSER_NOT_WRITABLE'));
			return 1;
		}

		$composer_io = new console_io($input, $output, $this->getHelperSet(), $this->language);
		$extensions = $input->getArgument('extensions');

		if ($input->getOption('purge'))
		{
			$this->manager->set_purge_on_remove(true);
		}

		$this->manager->remove($extensions, $composer_io);

		$io->success($this->language->lang('EXTENSIONS_REMOVED'));

		return 0;
	}
}
