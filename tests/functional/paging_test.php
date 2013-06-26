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
class phpbb_functional_paging_test extends phpbb_functional_test_case
{

	public function test_pagination()
	{
		$this->login();

		$post = $this->create_topic(2, 'Test Topic 1', 'This is a test topic posted by the testing framework.');
		for ($post_id = 1; $post_id < 20; $post_id++)
		{
			$this->create_post(2, $post['topic_id'], 'Re: Test Topic 1', 'This is a test post no' . $post_id . ' posted by the testing framework.');
		}
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");
		$link = $crawler->filter('#viewtopic > fieldset > a')->attr('href');
		$crawler = self::request('GET', $link);

		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}&start=10");
		$link = $crawler->filter('#viewtopic > fieldset > a')->attr('href');
		$crawler = self::request('GET', $link);
	}
}
