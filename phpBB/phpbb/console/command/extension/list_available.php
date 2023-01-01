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

use phpbb\composer\manager_interface;
use Symfony\Component\Console\Command\Command as symfony_command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class list_available extends \phpbb\console\command\command
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
	* @return void
	*/
	protected function configure()
	{
		$this
			->setName('extension:list-available')
			->setDescription($this->user->lang('CLI_DESCRIPTION_EXTENSION_LIST_AVAILABLE'))
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
		$io = new SymfonyStyle($input, $output);

		$extensions = [];

		foreach ($this->manager->get_available_packages() as $package)
		{
			$extensions[] = '<info>' . $package['name'] . '</info> (<comment>' . $package['version'] . '</comment>) ' . $package['url'] .
							($package['description'] ? "\n" . $package['description'] : '');
		}

		$io->listing($extensions);

		return symfony_command::SUCCESS;
	}
}
