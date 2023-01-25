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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use phpbb\console\command\user\reclean;

require_once __DIR__ . '/base.php';

class phpbb_console_user_reclean_test extends phpbb_console_user_base
{
	public function get_command_tester()
	{
		$application = new Application();
		$application->add(new reclean(
			$this->user,
			$this->db,
			$this->language
		));

		$command = $application->find('user:reclean');

		return new CommandTester($command);
	}

	public function test_reclean()
	{
		$command_tester = $this->get_command_tester();

		$exit_status = $command_tester->execute([]);
		$this->assertSame(0, $exit_status);

		$result = $this->db->sql_query('SELECT user_id FROM ' . USERS_TABLE . " WHERE username_clean = 'test unclean'");
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		$this->assertNotNull($row['user_id']);
	}
}
