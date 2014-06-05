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
namespace phpbb\console\command\config;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class get extends command
{
	protected $user;

	/**
	* Construct method
	*
	* @param \phpbb\config\config $config Config object
	* @param \phpbb\user $user User object
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\user $user)
	{
		$this->user = $user;
		parent::__construct($config);
	}

	protected function configure()
	{
		$this
			->setName('config:get')
			->setDescription($this->user->lang('CLI_DESCRIPTION_CONFIG_GET'))
			->addArgument(
				'key',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_DESCRIPTION_CONFIG_OPTION_NAME')
			)
			->addOption(
				'no-newline',
				null,
				InputOption::VALUE_NONE,
				$this->user->lang('CLI_DESCRIPTION_CONFIG_GET_OPTION')
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$key = $input->getArgument('key');

		if (isset($this->config[$key]) && $input->getOption('no-newline'))
		{
			$output->write($this->config[$key]);
		}
		elseif (isset($this->config[$key]))
		{
			$output->writeln($this->config[$key]);
		}
		else
		{
			$output->writeln('<error>' . $this->user->lang('CONFIG_GET_FAIL' , $key) . '</error>');
		}
	}
}
