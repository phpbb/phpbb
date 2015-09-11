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
use phpbb\composer\manager;
use phpbb\composer\manager_interface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class start_managing extends \phpbb\console\command\command
{
	/**
	 * @var manager_interface Composer extensions manager
	 */
	protected $manager;

	public function __construct(\phpbb\user $user, manager_interface $manager)
	{
		$this->manager = $manager;

		$user->add_lang('acp/extensions');

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
			->setName('extension:start-managing')
			->setDescription($this->user->lang('CLI_DESCRIPTION_EXTENSION_START_MANAGING'))
			->addArgument(
				'extension',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_EXTENSION_START_MANAGING'))
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

		$extension = $input->getArgument('extension');

		try
		{
			$this->manager->start_managing($extension);
		}
		catch (managed_with_error_exception $e)
		{
			$io->warning(call_user_func_array([$this->user, 'lang'], [$e->getMessage(), $e->get_parameters()]));
			return 1;
		}

		$io->success('The extension ' . $extension . ' is now managed automatically.');

		return 0;
	}
}
