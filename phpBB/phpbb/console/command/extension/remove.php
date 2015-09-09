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

use phpbb\composer\manager;
use phpbb\composer\manager_interface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class remove extends \phpbb\console\command\command
{
	/**
	 * @var manager_interface Composer extensions manager
	 */
	protected $manager;

	public function __construct(\phpbb\user $user, manager_interface $manager)
	{
		$this->manager = $manager;

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
			->setDescription($this->user->lang('CLI_DESCRIPTION_EXTENSION_REMOVE'))
			->addArgument(
				'extensions',
				InputArgument::IS_ARRAY | InputArgument::REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_EXTENSION_REMOVE'))
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

		$extensions = $input->getArgument('extensions');

		$this->manager->remove($extensions);

		$io->success('All extensions removed');

		return 0;
	}
}
