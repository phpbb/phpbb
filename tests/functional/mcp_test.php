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
class phpbb_functional_mcp_test extends phpbb_functional_test_case
{
	public function test_post_new_topic()
	{
		$this->login();

		// Test creating topic
		$post = $this->create_topic(2, 'Test Topic 2', 'Testing move post with "Move posts" option from Quick-Moderator Tools.');

		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");
		$this->assertContains('Testing move post with "Move posts" option from Quick-Moderator Tools.', $crawler->filter('html')->text());

		return $crawler;
	}

	/**
	* @depends test_post_new_topic
	*/
	public function test_handle_quickmod($crawler)
	{
		// Test moving a post
		return $this->get_quickmod_page(0, 'MERGE_POSTS', $crawler);
	}

	/**
	* @depends test_handle_quickmod
	*/
	public function test_move_post_to_topic($crawler)
	{
		// Select the post in MCP
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form(array(
			'to_topic_id'	=> 1,
		));
		$form['post_id_list'][0]->tick();
		$crawler = self::submit($form);
		$this->assertContains($this->lang('MERGE_POSTS'), $crawler->filter('html')->text());

		return $crawler;
	}

	/**
	* @depends test_move_post_to_topic
	*/
	public function test_confirm_result($crawler)
	{
		$this->add_lang('mcp');
		$form = $crawler->selectButton('Yes')->form();
		$crawler = self::submit($form);
		$this->assertContains($this->lang('POSTS_MERGED_SUCCESS'), $crawler->text());
	}

	public function test_delete_logs()
	{
		$this->login();
		$crawler = self::request('GET', "mcp.php?i=mcp_logs&mode=front&sid={$this->sid}");
		$this->assertGreaterThanOrEqual(1, $crawler->filter('input[type=checkbox]')->count());

		$this->add_lang('mcp');
		$form = $crawler->selectButton($this->lang('DELETE_ALL'))->form();
		$crawler = self::submit($form);

		$form = $crawler->selectButton('Yes')->form();
		$crawler = self::submit($form);

		$this->assertCount(0, $crawler->filter('input[type=checkbox]'));
	}
}
