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
class phpbb_functional_subforum_test extends phpbb_functional_test_case
{
	public function test_setup_forums()
	{
		$this->login();
		$this->admin_login();

		$forum_name = 'Subforum Test #1';
		$crawler = self::request('GET', "adm/index.php?i=acp_forums&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton('addforum')->form([
			'forum_name'	=> $forum_name,
		]);
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form([
			'forum_perm_from'	=> 2,
		]);
		self::submit($form);
		$forum_id = self::get_forum_id($forum_name);

		// 'Feeds #1.1' is a sub-forum of 'Feeds #1'
		$forum_name = 'Subforum Test #1.1';
		$crawler = self::request('GET', "adm/index.php?i=acp_forums&sid={$this->sid}&icat=6&mode=manage&parent_id={$forum_id}");
		$form = $crawler->selectButton('addforum')->form([
			'forum_name'	=> $forum_name,
		]);
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form([
			'forum_perm_from'	=> 2,
		]);
		self::submit($form);
		$forum_id = self::get_forum_id('Subforum Test #1.1');

		// 'Feeds #news' will be used for feed.php?mode=news
		$crawler = self::request('GET', "adm/index.php?i=acp_forums&sid={$this->sid}&icat=6&mode=manage&parent_id={$forum_id}");
		$form = $crawler->selectButton('addforum')->form([
			'forum_name'	=> 'Subforum Test #1.1.1',
		]);
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form([
			'forum_perm_from'	=> 2,
		]);
		self::submit($form);
	}

	/**
	 * @depends test_setup_forums
	 */
	public function test_display_subforums()
	{
		$crawler = self::request('GET', "index.php?sid={$this->sid}");
		$this->assertStringContainsString('Subforum Test #1.1', $crawler->html());
		$this->assertStringContainsString('Subforum Test #1.1.1', $crawler->html());
	}

	/**
	 * @depends test_display_subforums
	 */
	public function test_display_subforums_limit()
	{
		$this->login();
		$this->admin_login();

		// Disable listing subforums
		$forum_id = $this->get_forum_id('Subforum Test #1');
		$crawler = self::request('GET', "adm/index.php?i=acp_forums&sid={$this->sid}&icat=7&mode=manage&parent_id=0&f={$forum_id}&action=edit");
		$form = $crawler->selectButton('submit')->form([
			'display_subforum_limit' => 1,
		]);
		self::submit($form);

		$crawler = self::request('GET', "index.php?sid={$this->sid}");
		$this->assertStringContainsString('Subforum Test #1.1', $crawler->html());
		$this->assertStringNotContainsString('Subforum Test #1.1.1', $crawler->html());
	}

	protected function get_forum_id($forum_name)
	{
		$this->db = $this->get_db();
		$forum_id = 0;

		$sql = 'SELECT *
				FROM ' . FORUMS_TABLE . '
				WHERE ' . $this->db->sql_in_set('forum_name', $forum_name);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['forum_name'] == $forum_name)
			{
				$forum_id = (int) $row['forum_id'];
				break;
			}
		}
		$this->db->sql_freeresult($result);

		return $forum_id;
	}
}
