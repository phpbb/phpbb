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

		$crawler = $this->request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");
		$this->assertContains('This is a test topic posted by the testing framework.', $crawler->filter('html')->text());

		// Test creating a reply
		$post2 = $this->create_post(2, $post['topic_id'], 'Re: Test Topic 1', 'This is a test post posted by the testing framework.');

		$crawler = $this->request('GET', "viewtopic.php?t={$post2['topic_id']}&sid={$this->sid}");
		$this->assertContains('This is a test post posted by the testing framework.', $crawler->filter('html')->text());

		// Test quoting a message
		$crawler = $this->request('GET', "posting.php?mode=quote&f=2&t={$post2['topic_id']}&p={$post2['post_id']}&sid={$this->sid}");
		$this->assert_response_success();
		$this->assertContains('This is a test post posted by the testing framework.', $crawler->filter('html')->text());
	}

	/**
	* Creates a topic
	* 
	* Be sure to login before creating
	* 
	* @param int $forum_id
	* @param string $subject
	* @param string $message
	* @param array $additional_form_data Any additional form data to be sent in the request
	* @return array post_id, topic_id
	*/
	public function create_topic($forum_id, $subject, $message, $additional_form_data = array())
	{
		$posting_url = "posting.php?mode=post&f={$forum_id}&sid={$this->sid}";

		$form_data = array_merge(array(
			'subject'		=> $subject,
			'message'		=> $message,
			'post'			=> true,
		), $additional_form_data);

		return $this->submit_post($posting_url, 'POST_TOPIC', $form_data);
	}

	/**
	* Creates a post
	* 
	* Be sure to login before creating
	* 
	* @param int $forum_id
	* @param string $subject
	* @param string $message
	* @param array $additional_form_data Any additional form data to be sent in the request
	* @return array post_id, topic_id
	*/
	public function create_post($forum_id, $topic_id, $subject, $message, $additional_form_data = array())
	{
		$posting_url = "posting.php?mode=reply&f={$forum_id}&t={$topic_id}&sid={$this->sid}";

		$form_data = array_merge(array(
			'subject'		=> $subject,
			'message'		=> $message,
			'post'			=> true,
		), $additional_form_data);

		return $this->submit_post($posting_url, 'POST_REPLY', $form_data);
	}
}
