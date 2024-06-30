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

namespace phpbb\console\command\user;

use phpbb\console\command\command;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\user;
use phpbb\user_loader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class delete_id extends command
{
	/** @var driver_interface */
	protected $db;

	/** @var language */
	protected $language;

	/** @var log_interface */
	protected $log;

	/** @var user_loader */
	protected $user_loader;

	/** @var string Bots table */
	protected $bots_table;

	/** @var string User group table */
	protected $user_group_table;

	/** @var string Users table */
	protected $users_table;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string PHP extension */
	protected $php_ext;

	/**
	 * Construct method
	 *
	 * @param driver_interface $db
	 * @param language         $language
	 * @param log_interface    $log
	 * @param user             $user
	 * @param user_loader      $user_loader
	 * @param string           $bots_table
	 * @param string           $user_group_table
	 * @param string           $users_table
	 * @param string           $phpbb_root_path
	 * @param string           $php_ext
	 */
	public function __construct(driver_interface $db, language $language, log_interface $log, user $user, user_loader $user_loader,
								string $bots_table, string $user_group_table, string $users_table, string $phpbb_root_path, string $php_ext)
	{
		$this->db = $db;
		$this->language = $language;
		$this->log = $log;
		$this->user_loader = $user_loader;
		$this->bots_table = $bots_table;
		$this->user_group_table = $user_group_table;
		$this->users_table = $users_table;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->language->add_lang('acp/users');
		parent::__construct($user);
	}

	/**
	 * Sets the command name and description
	 *
	 * @return void
	 */
	protected function configure(): void
	{
		$this
			->setName('user:delete_id')
			->setDescription($this->language->lang('CLI_DESCRIPTION_USER_DELETE_ID'))
			->addArgument(
				'user_ids',
				InputArgument::REQUIRED | InputArgument::IS_ARRAY,
				$this->language->lang('CLI_DESCRIPTION_USER_DELETE_ID_OPTION_ID')
			)
			->addOption(
				'delete-posts',
				null,
				InputOption::VALUE_NONE,
				$this->language->lang('CLI_DESCRIPTION_USER_DELETE_OPTION_POSTS')
			)
		;
	}

	/**
	 * Executes the command user:delete_ids
	 *
	 * Deletes a list of user ids from the database. An option to delete the users' posts
	 * is available, by default posts will be retained.
	 *
	 * @param InputInterface  $input  The input stream used to get the options
	 * @param OutputInterface $output The output stream, used to print messages
	 *
	 * @return int 0 if all is well, 1 if any errors occurred
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$user_ids = $input->getArgument('user_ids');
		$mode = ($input->getOption('delete-posts')) ? 'remove' : 'retain';
		$deleted_users = 0;
		$io = new SymfonyStyle($input, $output);

		if (count($user_ids) > 0)
		{
			$this->user_loader->load_users($user_ids);

			$progress = $this->create_progress_bar(count($user_ids), $io, $output);
			$progress->setMessage($this->language->lang('CLI_USER_DELETE_ID_START'));
			$progress->start();

			foreach ($user_ids as $user_id)
			{
				$user_row = $this->user_loader->get_user($user_id);

				// Skip anonymous user
				if ($user_row['user_id'] == ANONYMOUS)
				{
					$progress->advance();
					continue;
				}
				else if ($user_row['user_type'] == USER_IGNORE)
				{
					$this->delete_bot_user($user_row);
				}
				else
				{
					if (!function_exists('user_delete'))
					{
						require($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
					}

					user_delete($mode, $user_row['user_id'], $user_row['username']);

					$this->log->add('admin', ANONYMOUS, '', 'LOG_USER_DELETED', false, array($user_row['username']));
				}

				$progress->advance();
				$deleted_users++;
			}

			$progress->finish();

			if ($deleted_users > 0)
			{
				$io->success($this->language->lang('CLI_USER_DELETE_ID_SUCCESS'));
			}
		}

		if (!$deleted_users)
		{
			$io->note($this->language->lang('CLI_USER_DELETE_NONE'));
		}

		return 0;
	}

	/**
	 * Interacts with the user.
	 * Confirm they really want to delete the account...last chance!
	 *
	 * @param InputInterface  $input  An InputInterface instance
	 * @param OutputInterface $output An OutputInterface instance
	 */
	protected function interact(InputInterface $input, OutputInterface $output): void
	{
		$helper = $this->getHelper('question');

		$user_ids = $input->getArgument('user_ids');
		if (count($user_ids) > 0)
		{
			$question = new ConfirmationQuestion(
				$this->language->lang('CLI_USER_DELETE_ID_CONFIRM', implode(',', $user_ids)),
				false
			);

			if (!$helper->ask($input, $output, $question))
			{
				$input->setArgument('user_ids', []);
			}
		}
	}

	/**
	 * Deletes a bot user
	 *
	 * @param array $user_row
	 * @return void
	 */
	protected function delete_bot_user(array $user_row): void
	{
		$delete_tables = [$this->bots_table, $this->user_group_table, $this->users_table];
		foreach ($delete_tables as $table)
		{
			$sql = "DELETE FROM $table
				WHERE user_id = " . (int) $user_row['user_id'];
			$this->db->sql_query($sql);
		}
	}
}
