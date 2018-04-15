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

use phpbb\composer\exception\managed_with_error_exception;
use phpbb\composer\io\console_io;
use phpbb\composer\manager_interface;
use phpbb\language\language;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class manage extends \phpbb\console\command\command
{
	/**
	 * @var manager_interface Composer extensions manager
	 */
	protected $manager;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	public function __construct(\phpbb\user $user, manager_interface $manager, language $language)
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
			->setName('extension:manage')
			->setDescription($this->language->lang('CLI_DESCRIPTION_EXTENSION_MANAGE'))
			->addArgument(
				'extension',
				InputArgument::REQUIRED,
				$this->language->lang('CLI_DESCRIPTION_EXTENSION_MANAGE_ARGUMENT'))
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
		$io = new SymfonyStyle($input, $output);

		if (!$this->manager->check_requirements())
		{
			$io->error($this->language->lang('EXTENSIONS_COMPOSER_NOT_WRITABLE'));
			return 1;
		}

		$composer_io = new console_io($input, $output, $this->getHelperSet(), $this->language);

		$extension = $input->getArgument('extension');

		try
		{
			$this->manager->start_managing($extension, $composer_io);
		}
		catch (managed_with_error_exception $e)
		{
			$io->warning($this->language->lang_array($e->getMessage(), $e->get_parameters()));
			return 1;
		}

		$io->success($this->language->lang('EXTENSION_MANAGED_SUCCESS', $extension));

		return 0;
	}
}
