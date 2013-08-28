<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
		$form = $crawler->selectButton('Go')->eq(1)->form();
		$form['action']->select('merge');
		$crawler = self::submit($form);

		return $crawler;
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
}
