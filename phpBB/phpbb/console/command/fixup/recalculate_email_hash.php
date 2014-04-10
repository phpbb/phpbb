<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace phpbb\console\command\fixup;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class recalculate_email_hash extends \phpbb\console\command\command
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	function __construct(\phpbb\db\driver\driver_interface $db)
	{
		$this->db = $db;

		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName('fixup:recalculate-email-hash')
			->setDescription('Recalculates the user_email_hash column of the users table.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
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
					$output->writeln(sprintf(
						'user_id %d, email %s => %s',
						$row['user_id'],
						$row['user_email'],
						$user_email_hash
					));
				}
			}
		}
		$this->db->sql_freeresult($result);

		$output->writeln('<info>Successfully recalculated all email hashes.</info>');
	}
}
