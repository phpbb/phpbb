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

namespace phpbb\console\command\update;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class check extends \phpbb\console\command\command
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \Symfony\Component\DependencyInjection\ContainerBuilder */
	protected $phpbb_container;

	/**
	* Construct method
	*/
	public function __construct(\phpbb\user $user, \phpbb\config\config $config, \Symfony\Component\DependencyInjection\ContainerInterface $phpbb_container)
	{
		parent::__construct($user);

		$this->config = $config;
		$this->phpbb_container = $phpbb_container;
		$this->user->add_lang(array('acp/common', 'acp/extensions'));
	}

	/**
	* Configures the service.
	*
	* Sets the name and description of the command.
	*
	* @return null
	*/
	protected function configure()
	{
		$this
			->setName('update:check')
			->setDescription($this->user->lang('CLI_DESCRIPTION_UPDATE_CHECK'))
			->addArgument('ext-name', InputArgument::OPTIONAL, $this->user->lang('CLI_DESCRIPTION_UPDATE_CHECK_ARGUMENT_1'))
			->addOption('stability', null, InputOption::VALUE_REQUIRED, 'CLI_DESCRIPTION_CRON_RUN_OPTION_STABILITY')
			->addOption('cache', 'c', InputOption::VALUE_NONE, 'CLI_DESCRIPTION_CRON_RUN_OPTION_CACHE')
		;
	}

	/**
	* Executes the command.
	*
	* Checks if an update is available.
	* If at least one is available, a message is printed and if verbose mode is set the list of possible updates is printed.
	* If their is none, nothing is printed unless verbose mode is set.
	*
	* @param InputInterface $input Input stream, used to get the options.
	* @param OutputInterface $output Output stream, used to print messages.
	* @return int 0 if the board is up to date, 1 if it is not and 2 if an error occured.
	* @throws \RuntimeException
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$recheck = true;
		if ($input->getOption('cache'))
		{
			$recheck = false;
		}

		$stability = null;
		if ($input->getOption('stability'))
		{
			$stability = $input->getOption('stability');
			if (!($stability == 'stable') && !($stability == 'unstable'))
			{
				throw new \RuntimeException($this->user->lang('CLI_ERROR_INVALID_STABILITY', $stability));
			}
		}

		$ext_name = $input->getArgument('ext-name');
		if ($ext_name != null)
		{
			if ($ext_name == 'all')
			{
				return $this->check_all_ext($input, $output, $stability, $recheck);
			}
			else
			{
				return $this->check_ext($input, $output, $stability, $recheck, $ext_name);
			}
		}
		else
		{
			return $this->check_core($input, $output, $stability, $recheck);
		}
	}

	/**
	* Check if a given extension is up to date
	*
	* @param InputInterface 		$input 		Input stream, used to get the options.
	* @param OutputInterface 		$output		Output stream, used to print messages.
	* @param OutputInterface		$stability	Force a given stability
	* @param bool					$recheck	Disallow the use of the cache
	* @param string					$ext_name	The extension name
	* @return int
	*/
	protected function check_ext(InputInterface $input, OutputInterface $output, $stability, $recheck, $ext_name)
	{
		try
		{
			$ext_manager = $this->phpbb_container->get('ext.manager');
			$md_manager = $ext_manager->create_extension_metadata_manager($ext_name, null);
			$updates_available = $ext_manager->version_check($md_manager, $recheck, false, $stability);
		}
		catch (\RuntimeException $e)
		{
			$output->writeln('<error>' . $e->getMessage() . '</error>');

			return 2;
		}

		$metadata = $md_manager->get_metadata('all');
		if ($input->getOption('verbose'))
		{
			$output->writeln('<info>' . $md_manager->get_metadata('display-name') . '</info>');
			$output->writeln('');

			$output->writeln('<comment>' . $this->user->lang('CURRENT_VERSION') . $this->user->lang('COLON') . '</comment> ' . $metadata['version']);
		}

		if (!empty($updates_available))
		{
			$output->writeln('');
			$output->writeln('<question>' . $this->user->lang('NOT_UP_TO_DATE', $metadata['name']) . '</question>');

			if ($input->getOption('verbose'))
			{
				$this->display_versions($output, $updates_available);
			}

			return 1;
		}
		else
		{
			$output->writeln('');
			$output->writeln('<question>' . $this->user->lang('NOT_UP_TO_DATE', $metadata['name']) . '</question>');

			if ($input->getOption('verbose'))
			{
				$output->writeln('<info>' . $this->user->lang('UPDATE_NOT_NEEDED') . '</info>');
			}

			return 0;
		}
	}

	/**
	* Check if the core is up to date
	*
	* @param InputInterface 		$input 		Input stream, used to get the options.
	* @param OutputInterface 		$output		Output stream, used to print messages.
	* @param OutputInterface		$stability	Force a given stability
	* @param bool					$recheck	Disallow the use of the cache
	* @return int
	*/
	protected function check_core(InputInterface $input, OutputInterface $output, $stability, $recheck)
	{
		$version_helper = $this->phpbb_container->get('version_helper');
		$version_helper->force_stability($stability);

		try
		{
			$updates_available = $version_helper->get_suggested_updates($recheck);
		}
		catch (\RuntimeException $e)
		{
			$output->writeln('<error>' . $this->user->lang('VERSIONCHECK_FAIL') . '</error>');

			return 2;
		}

		if ($input->getOption('verbose'))
		{
			$output->writeln('<info>phpBB core</info>');
			$output->writeln('');

			$output->writeln('<comment>' . $this->user->lang('CURRENT_VERSION') . $this->user->lang('COLON') . '</comment> ' . $this->config['version']);
		}

		if (!empty($updates_available))
		{
			$output->writeln('');
			$output->writeln('<question>' . $this->user->lang('UPDATE_NEEDED') . '</question>');

			if ($input->getOption('verbose'))
			{
				$this->display_versions($output, $updates_available);
			}

			return 1;
		}
		else
		{
			if ($input->getOption('verbose'))
			{
				$output->writeln('');
				$output->writeln('<question>' . $this->user->lang('UPDATE_NOT_NEEDED') . '</question>');
			}

			return 0;
		}
	}

	/**
	* Check if all the available extensions are up to date
	*
	* @param InputInterface 		$input 		Input stream, used to get the options.
	* @param OutputInterface 		$output		Output stream, used to print messages.
	* @param OutputInterface		$stability	Force a given stability
	* @param bool					$recheck	Disallow the use of the cache
	* @return int
	*/
	protected function check_all_ext(InputInterface $input, OutputInterface $output, $stability, $recheck)
	{
		$ext_manager = $this->phpbb_container->get('ext.manager');

		$ext_name_length = max(30, strlen($this->user->lang('EXTENSION_NAME')));
		$current_version_length = max(15, strlen($this->user->lang('CURRENT_VERSION')));
		$latest_version_length = max(15, strlen($this->user->lang('LATEST_VERSION')));

		$output->writeln(sprintf("%-{$ext_name_length}s | %-{$current_version_length}s | %s", $this->user->lang('EXTENSION_NAME'), $this->user->lang('CURRENT_VERSION'), $this->user->lang('LATEST_VERSION')));
		$output->writeln(sprintf("%'-{$ext_name_length}s-+-%'-{$current_version_length}s-+-%'-{$latest_version_length}s", '', '', ''));
		foreach ($ext_manager->all_available() as $ext_name => $ext_path)
		{
			$message = sprintf("<info>%-{$ext_name_length}s</info>", $ext_name);
			$md_manager = $ext_manager->create_extension_metadata_manager($ext_name, null);
			try
			{
				$metadata = $md_manager->get_metadata('all');
				$message .= sprintf(" | <info>%-{$current_version_length}s</info>", $metadata['version']);
				try
				{
					$updates_available = $ext_manager->version_check($md_manager, $recheck, false, $stability);
					$message .= sprintf(" | <comment>%s</comment>", implode(', ', array_keys($updates_available)));
				}
				catch (\RuntimeException $e)
				{
					$message .= ' | ';
				}
			}
			catch (\RuntimeException $e)
			{
				$message .= ('<error>' . $e->getMessage() . '</error>');
			}

			$output->writeln($message);
		}

		return 0;
	}

	/**
	* Display the details of the available updates
	*
	* @param OutputInterface	$output				Output stream, used to print messages.
	* @param array				$updates_available	The list of the available updates
	*/
	protected function display_versions(OutputInterface $output, $updates_available)
	{
		$output->writeln('');
		$output->writeln('<comment>' . $this->user->lang('UPDATES_AVAILABLE') . '</comment>');
		foreach ($updates_available as $version_data)
		{
			$messages = array();
			$messages[] = sprintf("\t%-30s| %s", $this->user->lang('VERSION'), $version_data['current']);

			if (isset($version_data['announcement']))
			{
				$messages[] = sprintf("\t%-30s| %s", $this->user->lang('ANNOUNCEMENT_TOPIC'), $version_data['announcement']);
			}

			if (isset($version_data['download']))
			{
				$messages[] = sprintf("\t%-30s| %s", $this->user->lang('DOWNLOAD_LATEST'), $version_data['download']);
			}

			$messages[] = '';

			$output->writeln(implode("\n", $messages));
		}
	}
}
