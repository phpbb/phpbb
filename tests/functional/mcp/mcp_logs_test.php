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

/**
* @group functional
*/
class phpbb_functional_mcp_logs_test extends phpbb_functional_test_case
{
	public function test_delete_logs()
	{
		$this->add_lang(['mcp', 'common']);
		$db = self::get_db();
		$sql_ary = array(
			'log_type'		=> LOG_MOD,
			'user_id'		=> 2,
			'forum_id'		=> 0,
			'topic_id'		=> 0,
			'post_id'		=> 0,
			'reportee_id'	=> 0,
			'log_ip'		=> '127.0.0.1',
			'log_time'		=> time(),
			'log_operation'	=> 'LOG_CLEAR_MOD',
			'log_data'		=> '',
		);
		$db->sql_query('INSERT INTO ' . LOG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

		$this->login();
		$crawler = self::request('GET', "mcp.php?i=mcp_logs&mode=front&sid={$this->sid}");
		$this->assertGreaterThanOrEqual(1, $crawler->filter('input[type=checkbox]')->count());

		$form = $crawler->selectButton($this->lang('DELETE_ALL'))->form();
		$crawler = self::submit($form);

		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);

		$this->assertCount(0, $crawler->filter('input[type=checkbox]'));
	}
}
