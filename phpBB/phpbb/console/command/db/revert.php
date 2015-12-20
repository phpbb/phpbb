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
namespace phpbb\console\command\db;

use phpbb\db\output_handler\log_wrapper_migrator_output_handler;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class revert extends \phpbb\console\command\db\migration_command
{
	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var  \phpbb\filesystem\filesystem_interface */
	protected $filesystem;

	/** @var \phpbb\language\language */
	protected $language;

	function __construct(\phpbb\user $user, \phpbb\language\language $language, \phpbb\db\migrator $migrator, \phpbb\extension\manager $extension_manager, \phpbb\config\config $config, \phpbb\cache\service $cache, \phpbb\filesystem\filesystem_interface $filesystem, $phpbb_root_path)
	{
		$this->filesystem = $filesystem;
		$this->language = $language;
		$this->phpbb_root_path = $phpbb_root_path;
		parent::__construct($user, $migrator, $extension_manager, $config, $cache);
		$this->user->add_lang(array('common', 'migrator'));
	}

	protected function configure()
	{
		$this
			->setName('db:revert')
			->setDescription($this->user->lang('CLI_DESCRIPTION_DB_REVERT'))
			->addArgument(
				'name',
				InputArgument::REQUIRED,
				$this->user->lang('CLI_MIGRATION_NAME')
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = str_replace('/', '\\', $input->getArgument('name'));

		$this->migrator->set_output_handler(new log_wrapper_migrator_output_handler($this->language, new console_migrator_output_handler($this->user, $output), $this->phpbb_root_path . 'store/migrations_' . time() . '.log', $this->filesystem));

		$this->cache->purge();

		if (!in_array($name, $this->load_migrations()))
		{
			$output->writeln('<error>' . $this->user->lang('MIGRATION_NOT_VALID', $name) . '</error>');
			return 1;
		}
		else if ($this->migrator->migration_state($name) === false)
		{
			$output->writeln('<error>' . $this->user->lang('MIGRATION_NOT_INSTALLED', $name) . '</error>');
			return 1;
		}

		try
		{
			while ($this->migrator->migration_state($name) !== false)
			{
				$this->migrator->revert($name);
			}
		}
		catch (\phpbb\db\migration\exception $e)
		{
			$output->writeln('<error>' . $e->getLocalisedMessage($this->user) . '</error>');
			$this->finalise_update();
			return 1;
		}

		$this->finalise_update();
	}
}
