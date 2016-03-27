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
use phpbb\user;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class reclean extends command
{
	/** @var driver_interface */
	protected $db;

	/** @var language */
	protected $language;

	/** @var int A count of the number of re-cleaned user names */
	protected $processed;

	/**
	 * Construct method
	 *
	 * @param user             $user
	 * @param driver_interface $db
	 * @param language         $language
	 */
	public function __construct(user $user, driver_interface $db, language $language)
	{
		$this->db = $db;
		$this->language = $language;

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
			->setName('user:reclean')
			->setDescription($this->language->lang('CLI_DESCRIPTION_USER_RECLEAN'))
		;
	}

	/**
	 * Executes the command user:reclean
	 *
	 * Cleans user names that are unclean.
	 *
	 * @param InputInterface  $input  The input stream used to get the options
	 * @param OutputInterface $output The output stream, used to print messages
	 *
	 * @return int 0 if all is well, 1 if any errors occurred
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->processed = 0;

		$stage = 0;
		while ($stage !== true)
		{
			$stage = $this->reclean_usernames($stage);
		}

		$io = new SymfonyStyle($input, $output);
		$io->success($this->language->lang('CLI_USER_RECLEAN_SUCCESS', $this->processed));
		return 0;
	}

	/**
	 * Re-clean user names
	 * Only user names that are unclean will be re-cleaned
	 *
	 * @param int $start An offset index
	 * @return bool|int Return the next offset index or true if all records have been processed.
	 */
	protected function reclean_usernames($start = 0)
	{
		$limit = 500;
		$i = 0;

		$this->db->sql_transaction('begin');

		$sql = 'SELECT user_id, username, username_clean FROM ' . USERS_TABLE;
		$result = $this->db->sql_query_limit($sql, $limit, $start);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$i++;
			$username_clean = $this->db->sql_escape(utf8_clean_string($row['username']));

			if ($username_clean != $row['username_clean'])
			{
				$sql = 'UPDATE ' . USERS_TABLE . "
					SET username_clean = '$username_clean'
					WHERE user_id = {$row['user_id']}";
				$this->db->sql_query($sql);

				$this->processed++;
			}
		}
		$this->db->sql_freeresult($result);

		$this->db->sql_transaction('commit');

		return ($i < $limit) ? true : $start + $i;
	}
}
