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
namespace phpbb\console\command\fixup;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class recalculate_email_hash extends \phpbb\console\command\command
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	public function __construct(\phpbb\user $user, \phpbb\db\driver\driver_interface $db)
	{
		$this->db = $db;

		parent::__construct($user);
	}

	protected function configure()
	{
		$this
			->setName('fixup:recalculate-email-hash')
			->setDescription($this->user->lang('CLI_DESCRIPTION_RECALCULATE_EMAIL_HASH'))
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$sql = 'SELECT user_id, user_email, user_email_hash
			FROM ' . USERS_TABLE . '
			WHERE user_type <> ' . USER_IGNORE . "
				AND user_email <> ''";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_email_hash = phpbb_email_hash($row['user_email']);
			if ($user_email_hash !== $row['user_email_hash'])
			{
				$sql_ary = array(
					'user_email_hash'	=> $user_email_hash,
				);

				$sql = 'UPDATE ' . USERS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE user_id = ' . (int) $row['user_id'];
				$this->db->sql_query($sql);

				if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG)
				{
					$io->table(
						array('user_id', 'user_email', 'user_email_hash'),
						array(array($row['user_id'], $row['user_email'], $user_email_hash))
					);
				}
			}
		}
		$this->db->sql_freeresult($result);

		$io->success($this->user->lang('CLI_FIXUP_RECALCULATE_EMAIL_HASH_SUCCESS'));
	}
}
