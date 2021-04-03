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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class migrate extends \phpbb\console\command\db\migration_command
{
	/** @var \phpbb\log\log */
	protected $log;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var  \phpbb\filesystem\filesystem_interface */
	protected $filesystem;

	/** @var \phpbb\language\language */
	protected $language;

	public function __construct(\phpbb\user $user, \phpbb\language\language $language, \phpbb\db\migrator $migrator, \phpbb\extension\manager $extension_manager, \phpbb\config\config $config, \phpbb\cache\service $cache, \phpbb\log\log $log, \phpbb\filesystem\filesystem_interface $filesystem, $phpbb_root_path)
	{
		$this->language = $language;
		$this->log = $log;
		$this->filesystem = $filesystem;
		$this->phpbb_root_path = $phpbb_root_path;
		parent::__construct($user, $migrator, $extension_manager, $config, $cache);
		$this->language->add_lang(array('common', 'install', 'migrator'));
	}

	protected function configure()
	{
		$this
			->setName('db:migrate')
			->setDescription($this->language->lang('CLI_DESCRIPTION_DB_MIGRATE'))
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$this->migrator->set_output_handler(new log_wrapper_migrator_output_handler($this->language, new console_migrator_output_handler($this->user, $output), $this->phpbb_root_path . 'store/migrations_' . time() . '.log', $this->filesystem));

		$this->migrator->create_migrations_table();

		$this->cache->purge();
		if ($this->config instanceof \phpbb\config\db)
		{
			$this->config->initialise($this->cache->get_driver());
		}

		$this->load_migrations();
		$orig_version = $this->config['version'];
		while (!$this->migrator->finished())
		{
			try
			{
				$this->migrator->update();
			}
			catch (\phpbb\db\migration\exception $e)
			{
				$io->error($e->getLocalisedMessage($this->user));
				$this->finalise_update();
				return 1;
			}
		}

		if ($orig_version != $this->config['version'])
		{
			$this->log->add('admin', ANONYMOUS, '', 'LOG_UPDATE_DATABASE', time(), array($orig_version, $this->config['version']));
		}

		$this->finalise_update();
		$io->success($this->language->lang('INLINE_UPDATE_SUCCESSFUL'));
	}
}
