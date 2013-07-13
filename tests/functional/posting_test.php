<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @group functional
*/
class phpbb_functional_posting_test extends phpbb_functional_test_case
{
	public function test_post_new_topic()
	{
		$this->login();

		// Test creating topic
		$post = $this->create_topic(2, 'Test Topic 1', 'This is a test topic posted by the testing framework.');

		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");
		$this->assertContains('This is a test topic posted by the testing framework.', $crawler->filter('html')->text());

		// Test creating a reply
		$post2 = $this->create_post(2, $post['topic_id'], 'Re: Test Topic 1', 'This is a test post posted by the testing framework.');

		$crawler = self::request('GET', "viewtopic.php?t={$post2['topic_id']}&sid={$this->sid}");
		$this->assertContains('This is a test post posted by the testing framework.', $crawler->filter('html')->text());

		// Test quoting a message
		$crawler = self::request('GET', "posting.php?mode=quote&f=2&t={$post2['topic_id']}&p={$post2['post_id']}&sid={$this->sid}");
		$this->assertContains('This is a test post posted by the testing framework.', $crawler->filter('html')->text());
	}
}
