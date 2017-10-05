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

use phpbb\config\config;
use phpbb\exception\exception_interface;
use phpbb\language\language;
use phpbb\user;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class check extends \phpbb\console\command\command
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \Symfony\Component\DependencyInjection\ContainerBuilder */
	protected $phpbb_container;
	/**
	 * @var language
	 */
	private $language;

	/**
	* Construct method
	*/
	public function __construct(user $user, config $config, ContainerInterface $phpbb_container, language $language)
	{
		$this->config = $config;
		$this->phpbb_container = $phpbb_container;
		$this->language = $language;

		$this->language->add_lang(array('acp/common', 'acp/extensions'));

		parent::__construct($user);
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
			->setDescription($this->language->lang('CLI_DESCRIPTION_UPDATE_CHECK'))
			->addArgument('ext-name', InputArgument::OPTIONAL, $this->language->lang('CLI_DESCRIPTION_UPDATE_CHECK_ARGUMENT_1'))
			->addOption('stability', null, InputOption::VALUE_REQUIRED, $this->language->lang('CLI_DESCRIPTION_UPDATE_CHECK_OPTION_STABILITY'))
			->addOption('cache', 'c', InputOption::VALUE_NONE, $this->language->lang('CLI_DESCRIPTION_UPDATE_CHECK_OPTION_CACHE'))
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
		$io = new SymfonyStyle($input, $output);

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
				$io->error($this->language->lang('CLI_ERROR_INVALID_STABILITY', $stability));
				return 3;
			}
		}

		$ext_name = $input->getArgument('ext-name');
		if ($ext_name != null)
		{
			if ($ext_name == 'all')
			{
				return $this->check_all_ext($io, $stability, $recheck);
			}
			else
			{
				return $this->check_ext($input, $io, $stability, $recheck, $ext_name);
			}
		}
		else
		{
			return $this->check_core($input, $io, $stability, $recheck);
		}
	}

	/**
	 * Check if a given extension is up to date
	 *
	 * @param InputInterface	$input		Input stream, used to get the options.
	 * @param SymfonyStyle		$io			IO handler, for formatted and unified IO
	 * @param string			$stability	Force a given stability
	 * @param bool				$recheck	Disallow the use of the cache
	 * @param string			$ext_name	The extension name
	 * @return int
	 */
	protected function check_ext(InputInterface $input, SymfonyStyle $io, $stability, $recheck, $ext_name)
	{
		try
		{
			$ext_manager = $this->phpbb_container->get('ext.manager');
			$md_manager = $ext_manager->create_extension_metadata_manager($ext_name);
			$updates_available = $ext_manager->version_check($md_manager, $recheck, false, $stability);

			$metadata = $md_manager->get_metadata('all');
			if ($input->getOption('verbose'))
			{
				$io->title($md_manager->get_metadata('display-name'));

				$io->note($this->language->lang('CURRENT_VERSION') . $this->language->lang('COLON') . ' ' . $metadata['version']);
			}

			if (!empty($updates_available))
			{
				if ($input->getOption('verbose'))
				{
					$io->caution($this->language->lang('NOT_UP_TO_DATE', $metadata['name']));

					$this->display_versions($io, $updates_available);
				}

				return 1;
			}
			else
			{
				if ($input->getOption('verbose'))
				{
					$io->success($this->language->lang('UPDATE_NOT_NEEDED'));
				}

				return 0;
			}
		}
		catch (\RuntimeException $e)
		{
			$io->error($this->language->lang('EXTENSION_NOT_INSTALLED', $ext_name));

			return 1;
		}
	}

	/**
	 * Check if the core is up to date
	 *
	 * @param InputInterface	$input		Input stream, used to get the options.
	 * @param SymfonyStyle		$io			IO handler, for formatted and unified IO
	 * @param string			$stability	Force a given stability
	 * @param bool				$recheck	Disallow the use of the cache
	 * @return int
	 */
	protected function check_core(InputInterface $input, SymfonyStyle $io, $stability, $recheck)
	{
		$version_helper = $this->phpbb_container->get('version_helper');
		$version_helper->force_stability($stability);

		$updates_available = $version_helper->get_suggested_updates($recheck);

		if ($input->getOption('verbose'))
		{
			$io->title('phpBB core');

			$io->note( $this->language->lang('CURRENT_VERSION') . $this->language->lang('COLON') . ' ' . $this->config['version']);
		}

		if (!empty($updates_available))
		{
			$io->caution($this->language->lang('UPDATE_NEEDED'));

			if ($input->getOption('verbose'))
			{
				$this->display_versions($io, $updates_available);
			}

			return 1;
		}
		else
		{
			if ($input->getOption('verbose'))
			{
				$io->success($this->language->lang('UPDATE_NOT_NEEDED'));
			}

			return 0;
		}
	}

	/**
	* Check if all the available extensions are up to date
	*
	* @param SymfonyStyle	$io			IO handler, for formatted and unified IO
	* @param bool			$recheck	Disallow the use of the cache
	* @return int
	*/
	protected function check_all_ext(SymfonyStyle $io, $stability, $recheck)
	{
		/** @var \phpbb\extension\manager $ext_manager */
		$ext_manager = $this->phpbb_container->get('ext.manager');

		$rows = [];

		foreach ($ext_manager->all_available() as $ext_name => $ext_path)
		{
			$row = [];
			$row[] = sprintf("<info>%s</info>", $ext_name);
			$md_manager = $ext_manager->create_extension_metadata_manager($ext_name);
			try
			{
				$metadata = $md_manager->get_metadata('all');
				if (isset($metadata['extra']['version-check']))
				{
					try {
						$updates_available = $ext_manager->version_check($md_manager, $recheck, false, $stability);
						if (!empty($updates_available))
						{
							$versions = array_map(function($entry)
							{
								return $entry['current'];
							}, $updates_available);

							$row[] = sprintf("<comment>%s</comment>", $metadata['version']);
							$row[] = implode(', ', $versions);
						}
						else
						{
							$row[] = sprintf("<info>%s</info>", $metadata['version']);
							$row[] = '';
						}
					} catch (\RuntimeException $e) {
						$row[] = $metadata['version'];
						$row[] = '';
					}
				}
				else
				{
					$row[] = $metadata['version'];
					$row[] = '';
				}
			}
			catch (exception_interface $e)
			{
				$exception_message = call_user_func_array(array($this->user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
				$row[] = '<error>' . $exception_message . '</error>';
			}
			catch (\RuntimeException $e)
			{
				$row[] = '<error>' . $e->getMessage() . '</error>';
			}

			$rows[] = $row;
		}

		$io->table([
			$this->language->lang('EXTENSION_NAME'),
			$this->language->lang('CURRENT_VERSION'),
			$this->language->lang('LATEST_VERSION'),
		], $rows);

		return 0;
	}

	/**
	* Display the details of the available updates
	*
	* @param SymfonyStyle	$io					IO handler, for formatted and unified IO
	* @param array			$updates_available	The list of the available updates
	*/
	protected function display_versions(SymfonyStyle $io, $updates_available)
	{
		$io->section($this->language->lang('UPDATES_AVAILABLE'));

		$rows = [];
		foreach ($updates_available as $version_data)
		{
			$row = ['', '', ''];
			$row[0] = $version_data['current'];

			if (isset($version_data['announcement']))
			{
				$row[1] = $version_data['announcement'];
			}

			if (isset($version_data['download']))
			{
				$row[2] = $version_data['download'];
			}

			$rows[] = $row;
		}

		$io->table([
			$this->language->lang('VERSION'),
			$this->language->lang('ANNOUNCEMENT_TOPIC'),
			$this->language->lang('DOWNLOAD_LATEST'),
		], $rows);
	}
}
