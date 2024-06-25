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

use phpbb\composer\io\console_io;
use phpbb\composer\manager_interface;
use phpbb\language\language;
use Symfony\Component\Console\Command\Command as symfony_command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class update extends \phpbb\console\command\command
{
	/**
	 * @var manager_interface Composer extensions manager
	 */
	protected $manager;

	/**
	 * @var language
	 */
	protected $language;

	public function __construct(\phpbb\user $user, manager_interface $manager, language $language)
	{
		$this->manager = $manager;
		$this->language = $language;

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
			->setName('extension:update')
			->setDescription($this->user->lang('CLI_DESCRIPTION_EXTENSION_UPDATE'))
			->addArgument(
				'extensions',
				InputArgument::IS_ARRAY | InputArgument::REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_EXTENSION_UPDATE_ARGUMENT'))
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
			return symfony_command::FAILURE;
		}

		$composer_io = new console_io($input, $output, $this->getHelperSet(), $this->language);
		$extensions = $input->getArgument('extensions');

		$this->manager->update($extensions, $composer_io);

		$io->success($this->language->lang('EXTENSIONS_UPDATED'));

		return symfony_command::SUCCESS;
	}
}
